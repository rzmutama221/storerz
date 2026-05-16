<?php
/**
 * Validator - Input Validation for RZDK Store
 * 
 * Server-side validation with common rules.
 * 
 * Usage:
 *   $validator = new Validator($_POST);
 *   $validator->rules([
 *       'username' => 'required|min:3|max:50|alpha_num',
 *       'email' => 'required|email|max:100',
 *       'password' => 'required|min:8',
 *       'password_confirm' => 'required|same:password',
 *   ]);
 *   
 *   if (!$validator->validate()) {
 *       $errors = $validator->errors();
 *   }
 */

class Validator
{
    private array $data;
    private array $rules = [];
    private array $errors = [];
    private array $customMessages = [];

    // Default error messages
    private array $messages = [
        'required' => ':field wajib diisi.',
        'email' => ':field harus berupa alamat email yang valid.',
        'min' => ':field minimal :param karakter.',
        'max' => ':field maksimal :param karakter.',
        'numeric' => ':field harus berupa angka.',
        'alpha_num' => ':field hanya boleh berisi huruf dan angka.',
        'alpha_dash' => ':field hanya boleh berisi huruf, angka, dash, dan underscore.',
        'same' => ':field harus sama dengan :param.',
        'unique' => ':field sudah digunakan.',
        'exists' => ':field tidak ditemukan.',
        'in' => ':field harus salah satu dari: :param.',
        'min_value' => ':field minimal bernilai :param.',
        'max_value' => ':field maksimal bernilai :param.',
        'image' => ':field harus berupa file gambar (jpg, jpeg, png, gif, webp).',
        'max_size' => ':field maksimal :param KB.',
        'date' => ':field harus berupa tanggal yang valid.',
        'url' => ':field harus berupa URL yang valid.',
        'phone' => ':field harus berupa nomor telepon yang valid.',
    ];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Set validation rules
     */
    public function rules(array $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Set custom error messages
     */
    public function messages(array $messages): static
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * Run validation
     * Returns true if all rules pass
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->getValue($field);

            foreach ($rules as $rule) {
                $param = null;
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }

                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $value, $param)) {
                        // Only add first error per field
                        if (!isset($this->errors[$field])) {
                            $this->errors[$field] = $this->getErrorMessage($field, $rule, $param);
                        }
                        break; // Stop validating this field after first error
                    }
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Get all validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error message (useful for flash)
     */
    public function firstError(): ?string
    {
        return !empty($this->errors) ? array_values($this->errors)[0] : null;
    }

    /**
     * Check if a specific field has an error
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * Get error for a specific field
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Get a value from data (supports dot notation for nested)
     */
    private function getValue(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /**
     * Get formatted error message
     */
    private function getErrorMessage(string $field, string $rule, ?string $param): string
    {
        // Check custom messages first
        $customKey = "{$field}.{$rule}";
        if (isset($this->customMessages[$customKey])) {
            return $this->customMessages[$customKey];
        }

        $message = $this->messages[$rule] ?? ':field tidak valid.';
        $fieldLabel = ucfirst(str_replace('_', ' ', $field));

        $message = str_replace(':field', $fieldLabel, $message);
        $message = str_replace(':param', $param ?? '', $message);

        return $message;
    }

    // ============================================================
    // VALIDATION RULES
    // ============================================================

    private function validateRequired(string $field, mixed $value, ?string $param): bool
    {
        if (is_null($value)) return false;
        if (is_string($value) && trim($value) === '') return false;
        if (is_array($value) && empty($value)) return false;
        return true;
    }

    private function validateEmail(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true; // Skip if empty (use required rule to enforce)
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return mb_strlen((string) $value) >= (int) $param;
    }

    private function validateMax(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return mb_strlen((string) $value) <= (int) $param;
    }

    private function validateNumeric(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return is_numeric($value);
    }

    private function validateAlpha_num(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return preg_match('/^[a-zA-Z0-9]+$/', $value) === 1;
    }

    private function validateAlpha_dash(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return preg_match('/^[a-zA-Z0-9_-]+$/', $value) === 1;
    }

    private function validateSame(string $field, mixed $value, ?string $param): bool
    {
        return $value === ($this->data[$param] ?? null);
    }

    private function validateUnique(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;

        // Format: table,column or table,column,except_id
        $parts = explode(',', $param);
        $table = $parts[0];
        $column = $parts[1] ?? $field;
        $exceptId = $parts[2] ?? null;

        $db = Model::getConnection();
        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$column} = :value";
        $bindings = [':value' => $value];

        if ($exceptId) {
            $sql .= " AND id != :except_id";
            $bindings[':except_id'] = $exceptId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $result['total'] === 0;
    }

    private function validateExists(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;

        // Format: table,column
        $parts = explode(',', $param);
        $table = $parts[0];
        $column = $parts[1] ?? 'id';

        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM {$table} WHERE {$column} = :value");
        $stmt->execute([':value' => $value]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $result['total'] > 0;
    }

    private function validateIn(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        $allowed = explode(',', $param);
        return in_array($value, $allowed, true);
    }

    private function validateMin_value(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return (float) $value >= (float) $param;
    }

    private function validateMax_value(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return (float) $value <= (float) $param;
    }

    private function validateDate(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        $date = date_parse($value);
        return $date['error_count'] === 0 && $date['warning_count'] === 0;
    }

    private function validateUrl(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validatePhone(string $field, mixed $value, ?string $param): bool
    {
        if (empty($value)) return true;
        // Indonesian phone format: starts with 08 or +62, 10-15 digits
        return preg_match('/^(\+62|62|08)[0-9]{8,13}$/', preg_replace('/[\s\-]/', '', $value)) === 1;
    }

    private function validateImage(string $field, mixed $value, ?string $param): bool
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($_FILES[$field]['type'], $allowed, true);
    }

    private function validateMax_size(string $field, mixed $value, ?string $param): bool
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        $maxKb = (int) $param;
        return ($_FILES[$field]['size'] / 1024) <= $maxKb;
    }
}
