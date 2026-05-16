<?php
/**
 * Model - Base Model with PDO Query Builder for RZDK Store
 * 
 * Provides a simple, secure query builder using prepared statements.
 * All models extend this class.
 * 
 * Usage:
 *   class User extends Model {
 *       protected string $table = 'users';
 *   }
 *   
 *   $user = new User();
 *   $user->find(1);
 *   $user->where('role', 'admin')->get();
 *   $user->create(['username' => 'john', ...]);
 */

class Model
{
    protected static ?PDO $pdo = null;
    protected string $table = '';
    protected string $primaryKey = 'id';

    // Query builder state
    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderBy = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private ?string $selectColumns = '*';

    /**
     * Get PDO connection (singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $config = require BASE_PATH . '/config/database.php';
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'],
                $config['database']
            );

            self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$pdo;
    }

    /**
     * Get PDO instance
     */
    protected function db(): PDO
    {
        return self::getConnection();
    }

    /**
     * Reset query builder state
     */
    private function reset(): void
    {
        $this->wheres = [];
        $this->bindings = [];
        $this->orderBy = null;
        $this->limit = null;
        $this->offset = null;
        $this->selectColumns = '*';
    }

    /**
     * Set columns to select
     */
    public function select(string $columns): static
    {
        $this->selectColumns = $columns;
        return $this;
    }

    /**
     * Add WHERE clause
     */
    public function where(string $column, mixed $value, string $operator = '='): static
    {
        $placeholder = ':w' . count($this->wheres);
        $this->wheres[] = "{$column} {$operator} {$placeholder}";
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    /**
     * Add WHERE IS NULL clause
     */
    public function whereNull(string $column): static
    {
        $this->wheres[] = "{$column} IS NULL";
        return $this;
    }

    /**
     * Add WHERE IS NOT NULL clause
     */
    public function whereNotNull(string $column): static
    {
        $this->wheres[] = "{$column} IS NOT NULL";
        return $this;
    }

    /**
     * Add WHERE IN clause
     */
    public function whereIn(string $column, array $values): static
    {
        if (empty($values)) {
            $this->wheres[] = "1 = 0"; // Always false
            return $this;
        }
        $placeholders = [];
        foreach ($values as $i => $value) {
            $key = ':win' . count($this->wheres) . '_' . $i;
            $placeholders[] = $key;
            $this->bindings[$key] = $value;
        }
        $this->wheres[] = "{$column} IN (" . implode(', ', $placeholders) . ")";
        return $this;
    }

    /**
     * Add ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy = "{$column} {$direction}";
        return $this;
    }

    /**
     * Set LIMIT
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set OFFSET
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Execute SELECT and return all results
     */
    public function get(): array
    {
        $sql = "SELECT {$this->selectColumns} FROM {$this->table}";
        $sql .= $this->buildWhere();
        $sql .= $this->buildOrderBy();
        $sql .= $this->buildLimit();

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($this->bindings);
        $results = $stmt->fetchAll();

        $this->reset();
        return $results;
    }

    /**
     * Execute SELECT and return first result
     */
    public function first(): ?array
    {
        $this->limit = 1;
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Find a record by primary key
     */
    public function find(int $id): ?array
    {
        return $this->where($this->primaryKey, $id)->first();
    }

    /**
     * Count records
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $sql .= $this->buildWhere();

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetch();

        $this->reset();
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Insert a new record
     * Returns the last insert ID
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->db()->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        return (int) $this->db()->lastInsertId();
    }

    /**
     * Update records matching current WHERE conditions
     * Returns number of affected rows
     */
    public function update(array $data): int
    {
        $setClauses = [];
        $setBindings = [];
        foreach ($data as $key => $value) {
            $placeholder = ':set_' . $key;
            $setClauses[] = "{$key} = {$placeholder}";
            $setBindings[$placeholder] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);
        $sql .= $this->buildWhere();

        $allBindings = array_merge($setBindings, $this->bindings);

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($allBindings);
        $affectedRows = $stmt->rowCount();

        $this->reset();
        return $affectedRows;
    }

    /**
     * Delete records matching current WHERE conditions
     * Returns number of affected rows
     */
    public function delete(): int
    {
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhere();

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($this->bindings);
        $affectedRows = $stmt->rowCount();

        $this->reset();
        return $affectedRows;
    }

    /**
     * Execute a raw SQL query
     */
    public function raw(string $sql, array $bindings = []): array
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /**
     * Execute raw SQL (INSERT/UPDATE/DELETE) and return affected rows
     */
    public function rawExecute(string $sql, array $bindings = []): int
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /**
     * Paginate results
     * Returns ['data' => [...], 'total' => int, 'page' => int, 'per_page' => int, 'total_pages' => int]
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        // Clone where conditions for count
        $countWheres = $this->wheres;
        $countBindings = $this->bindings;

        // Get total count
        $total = $this->count();

        // Restore wheres for actual query
        $this->wheres = $countWheres;
        $this->bindings = $countBindings;

        // Calculate offset
        $page = max(1, $page);
        $totalPages = (int) ceil($total / $perPage);
        $this->limit = $perPage;
        $this->offset = ($page - 1) * $perPage;

        $data = $this->get();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Build WHERE clause string
     */
    private function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }
        return ' WHERE ' . implode(' AND ', $this->wheres);
    }

    /**
     * Build ORDER BY clause
     */
    private function buildOrderBy(): string
    {
        if (!$this->orderBy) {
            return '';
        }
        return ' ORDER BY ' . $this->orderBy;
    }

    /**
     * Build LIMIT/OFFSET clause
     */
    private function buildLimit(): string
    {
        $sql = '';
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }
        return $sql;
    }
}
