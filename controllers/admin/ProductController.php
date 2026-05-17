<?php
/**
 * Admin ProductController - RZDK Store
 * 
 * Handles CRUD for: Categories, Products, Product Variants, Stock Items.
 */

class ProductController extends Controller
{
    // ============================================================
    // CATEGORIES
    // ============================================================

    /**
     * List all categories
     */
    public function categories(): void
    {
        $db = Model::getConnection();
        $categories = $db->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/categories', [
            'pageTitle' => 'Kategori',
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Store new category
     */
    public function storeCategory(): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'name' => 'required|min:2|max:100',
            'icon' => 'required|max:50',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/categories');
            return;
        }

        $name = trim($this->input('name'));
        $icon = trim($this->input('icon'));
        $slug = Helper::slug($name);
        $sortOrder = (int) $this->input('sort_order', 0);

        // Check slug uniqueness
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT id FROM categories WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        $stmt = $db->prepare("INSERT INTO categories (name, slug, icon, sort_order, is_active, created_at, updated_at) VALUES (:name, :slug, :icon, :sort_order, 1, NOW(), NOW())");
        $stmt->execute([
            ':name' => $name,
            ':slug' => $slug,
            ':icon' => $icon,
            ':sort_order' => $sortOrder,
        ]);

        Helper::logActivity('category_created', "Category '{$name}' created");
        Session::flash('success', "Kategori '{$name}' berhasil ditambahkan.");
        $this->redirect('/admin/categories');
    }

    /**
     * Update category
     */
    public function updateCategory(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'name' => 'required|min:2|max:100',
            'icon' => 'required|max:50',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/categories');
            return;
        }

        $name = trim($this->input('name'));
        $icon = trim($this->input('icon'));
        $sortOrder = (int) $this->input('sort_order', 0);
        $isActive = $this->input('is_active') ? 1 : 0;

        $db = Model::getConnection();
        $stmt = $db->prepare("UPDATE categories SET name = :name, icon = :icon, sort_order = :sort_order, is_active = :is_active, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':name' => $name,
            ':icon' => $icon,
            ':sort_order' => $sortOrder,
            ':is_active' => $isActive,
            ':id' => $id,
        ]);

        Helper::logActivity('category_updated', "Category '{$name}' updated (ID: {$id})");
        Session::flash('success', "Kategori '{$name}' berhasil diupdate.");
        $this->redirect('/admin/categories');
    }

    /**
     * Delete category (only if no products attached)
     */
    public function deleteCategory(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        // Check if category has products
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = :id");
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($count > 0) {
            Session::flash('error', "Tidak bisa menghapus kategori yang masih memiliki {$count} produk.");
            $this->redirect('/admin/categories');
            return;
        }

        $stmt = $db->prepare("SELECT name FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $cat = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Helper::logActivity('category_deleted', "Category '{$cat['name']}' deleted (ID: {$id})");
        Session::flash('success', "Kategori berhasil dihapus.");
        $this->redirect('/admin/categories');
    }

    // ============================================================
    // PRODUCTS
    // ============================================================

    /**
     * List all products
     */
    public function index(): void
    {
        $db = Model::getConnection();
        $categoryFilter = $this->input('category', '');

        $sql = "
            SELECT p.*, c.name as category_name,
                   COUNT(pv.id) as variant_count,
                   MIN(pv.price) as price_from,
                   MAX(pv.price) as price_to
            FROM products p
            JOIN categories c ON c.id = p.category_id
            LEFT JOIN product_variants pv ON pv.product_id = p.id
        ";
        $params = [];

        if ($categoryFilter) {
            $sql .= " WHERE p.category_id = :category_id";
            $params[':category_id'] = $categoryFilter;
        }

        $sql .= " GROUP BY p.id ORDER BY c.sort_order ASC, p.sort_order ASC, p.name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = $db->query("SELECT * FROM categories ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/products/index', [
            'pageTitle' => 'Produk',
            'products' => $products,
            'categories' => $categories,
            'categoryFilter' => $categoryFilter,
        ], 'admin');
    }

    /**
     * Show create product form
     */
    public function create(): void
    {
        $db = Model::getConnection();
        $categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/products/create', [
            'pageTitle' => 'Tambah Produk',
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Store new product
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'name' => 'required|min:2|max:100',
            'category_id' => 'required|exists:categories,id',
            'description' => 'max:5000',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/products/create');
            return;
        }

        $name = trim($this->input('name'));
        $categoryId = (int) $this->input('category_id');
        $description = trim($this->input('description', ''));
        $slotType = $this->input('slot_type', 'none');
        $sortOrder = (int) $this->input('sort_order', 0);

        $slug = Helper::slug($name);

        // Ensure unique slug
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT id FROM products WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        // Handle logo upload
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = Helper::uploadFile('logo', 'products', [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'
            ], 2048);
        }

        $hasSlotSystem = in_array($slotType, ['netflix', 'chatgpt']) ? 1 : 0;

        $stmt = $db->prepare("INSERT INTO products (category_id, name, slug, description, logo_path, has_slot_system, slot_type, is_active, sort_order, created_at, updated_at) VALUES (:category_id, :name, :slug, :description, :logo_path, :has_slot_system, :slot_type, 1, :sort_order, NOW(), NOW())");
        $stmt->execute([
            ':category_id' => $categoryId,
            ':name' => $name,
            ':slug' => $slug,
            ':description' => $description,
            ':logo_path' => $logoPath,
            ':has_slot_system' => $hasSlotSystem,
            ':slot_type' => $slotType,
            ':sort_order' => $sortOrder,
        ]);

        $productId = $db->lastInsertId();

        Helper::logActivity('product_created', "Product '{$name}' created (ID: {$productId})");
        Session::flash('success', "Produk '{$name}' berhasil ditambahkan. Silakan tambahkan varian produk.");
        $this->redirect('/admin/products/' . $productId . '/variants');
    }

    /**
     * Show edit product form
     */
    public function edit(string $id): void
    {
        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        $categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/products/edit', [
            'pageTitle' => 'Edit Produk',
            'product' => $product,
            'categories' => $categories,
        ], 'admin');
    }

    /**
     * Update product
     */
    public function update(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'name' => 'required|min:2|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/products/' . $id . '/edit');
            return;
        }

        $db = Model::getConnection();

        // Verify product exists
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        $name = trim($this->input('name'));
        $categoryId = (int) $this->input('category_id');
        $description = trim($this->input('description', ''));
        $slotType = $this->input('slot_type', 'none');
        $sortOrder = (int) $this->input('sort_order', 0);
        $isActive = $this->input('is_active') ? 1 : 0;

        $hasSlotSystem = in_array($slotType, ['netflix', 'chatgpt']) ? 1 : 0;

        // Handle logo upload
        $logoPath = $product['logo_path'];
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $newLogo = Helper::uploadFile('logo', 'products', [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'
            ], 2048);
            if ($newLogo) {
                $logoPath = $newLogo;
            }
        }

        $stmt = $db->prepare("UPDATE products SET category_id = :category_id, name = :name, description = :description, logo_path = :logo_path, has_slot_system = :has_slot_system, slot_type = :slot_type, is_active = :is_active, sort_order = :sort_order, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':category_id' => $categoryId,
            ':name' => $name,
            ':description' => $description,
            ':logo_path' => $logoPath,
            ':has_slot_system' => $hasSlotSystem,
            ':slot_type' => $slotType,
            ':is_active' => $isActive,
            ':sort_order' => $sortOrder,
            ':id' => $id,
        ]);

        Helper::logActivity('product_updated', "Product '{$name}' updated (ID: {$id})");
        Session::flash('success', "Produk '{$name}' berhasil diupdate.");
        $this->redirect('/admin/products');
    }

    /**
     * Delete product (and all its variants/stock)
     */
    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT name FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        // Check if product has active orders
        $stmt = $db->prepare("
            SELECT COUNT(*) as total FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            WHERE pv.product_id = :id AND o.status NOT IN ('completed', 'rejected', 'payment_expired', 'cancelled')
        ");
        $stmt->execute([':id' => $id]);
        $activeOrders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        if ($activeOrders > 0) {
            Session::flash('error', "Tidak bisa menghapus produk yang masih memiliki {$activeOrders} order aktif.");
            $this->redirect('/admin/products');
            return;
        }

        // Delete product (cascades to variants and stock via FK)
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Helper::logActivity('product_deleted', "Product '{$product['name']}' deleted (ID: {$id})");
        Session::flash('success', "Produk '{$product['name']}' berhasil dihapus.");
        $this->redirect('/admin/products');
    }

    // ============================================================
    // VARIANTS
    // ============================================================

    /**
     * Show variants for a product
     */
    public function variants(string $id): void
    {
        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        $stmt = $db->prepare("
            SELECT pv.*, 
                   (SELECT COUNT(*) FROM stock_items si WHERE si.variant_id = pv.id AND si.is_sold = 0) as available_stock
            FROM product_variants pv
            WHERE pv.product_id = :product_id
            ORDER BY pv.sort_order ASC, pv.price ASC
        ");
        $stmt->execute([':product_id' => $id]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/products/variants', [
            'pageTitle' => 'Varian — ' . $product['name'],
            'product' => $product,
            'variants' => $variants,
        ], 'admin');
    }

    /**
     * Store new variant
     */
    public function storeVariant(string $productId): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'name' => 'required|min:2|max:150',
            'price' => 'required|numeric|min_value:0',
            'duration_days' => 'required|numeric|min_value:1',
            'type' => 'required|in:sharing,private,semi_private,invite,redeem,service',
            'fulfillment_mode' => 'required|in:manual,auto_stock',
            'platform' => 'required|in:all,android,ios,web,apk,mobile,tv',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/products/' . $productId . '/variants');
            return;
        }

        $db = Model::getConnection();

        $stmt = $db->prepare("INSERT INTO product_variants (product_id, name, price, duration_days, type, fulfillment_mode, platform, max_users, stock_count, notes, warranty_days, is_active, sort_order, created_at, updated_at) VALUES (:product_id, :name, :price, :duration_days, :type, :fulfillment_mode, :platform, :max_users, 0, :notes, :warranty_days, 1, :sort_order, NOW(), NOW())");
        $stmt->execute([
            ':product_id' => $productId,
            ':name' => trim($this->input('name')),
            ':price' => (float) $this->input('price'),
            ':duration_days' => (int) $this->input('duration_days'),
            ':type' => $this->input('type'),
            ':fulfillment_mode' => $this->input('fulfillment_mode'),
            ':platform' => $this->input('platform'),
            ':max_users' => (int) $this->input('max_users', 1),
            ':notes' => trim($this->input('notes', '')),
            ':warranty_days' => (int) $this->input('warranty_days', 0),
            ':sort_order' => (int) $this->input('sort_order', 0),
        ]);

        Session::flash('success', "Varian berhasil ditambahkan.");
        $this->redirect('/admin/products/' . $productId . '/variants');
    }

    /**
     * Update variant
     */
    public function updateVariant(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'name' => 'required|min:2|max:150',
            'price' => 'required|numeric|min_value:0',
            'duration_days' => 'required|numeric|min_value:1',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->back();
            return;
        }

        $db = Model::getConnection();

        // Get variant to find product_id for redirect
        $stmt = $db->prepare("SELECT product_id FROM product_variants WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $variant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variant) {
            Session::flash('error', 'Varian tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        $stmt = $db->prepare("UPDATE product_variants SET name = :name, price = :price, duration_days = :duration_days, type = :type, fulfillment_mode = :fulfillment_mode, platform = :platform, max_users = :max_users, notes = :notes, warranty_days = :warranty_days, is_active = :is_active, sort_order = :sort_order, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':name' => trim($this->input('name')),
            ':price' => (float) $this->input('price'),
            ':duration_days' => (int) $this->input('duration_days'),
            ':type' => $this->input('type', 'private'),
            ':fulfillment_mode' => $this->input('fulfillment_mode', 'manual'),
            ':platform' => $this->input('platform', 'all'),
            ':max_users' => (int) $this->input('max_users', 1),
            ':notes' => trim($this->input('notes', '')),
            ':warranty_days' => (int) $this->input('warranty_days', 0),
            ':is_active' => $this->input('is_active') ? 1 : 0,
            ':sort_order' => (int) $this->input('sort_order', 0),
            ':id' => $id,
        ]);

        Session::flash('success', "Varian berhasil diupdate.");
        $this->redirect('/admin/products/' . $variant['product_id'] . '/variants');
    }

    /**
     * Delete variant
     */
    public function deleteVariant(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT pv.product_id, pv.name FROM product_variants pv WHERE pv.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $variant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variant) {
            Session::flash('error', 'Varian tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        // Check active orders
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE variant_id = :id AND status NOT IN ('completed','rejected','payment_expired','cancelled')");
        $stmt->execute([':id' => $id]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
            Session::flash('error', 'Tidak bisa menghapus varian yang masih memiliki order aktif.');
            $this->redirect('/admin/products/' . $variant['product_id'] . '/variants');
            return;
        }

        $stmt = $db->prepare("DELETE FROM product_variants WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Session::flash('success', "Varian '{$variant['name']}' berhasil dihapus.");
        $this->redirect('/admin/products/' . $variant['product_id'] . '/variants');
    }

    // ============================================================
    // STOCK MANAGEMENT
    // ============================================================

    /**
     * Show stock items for a variant
     */
    public function stock(string $variantId): void
    {
        $db = Model::getConnection();

        $stmt = $db->prepare("
            SELECT pv.*, p.name as product_name
            FROM product_variants pv
            JOIN products p ON p.id = pv.product_id
            WHERE pv.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $variantId]);
        $variant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variant) {
            Session::flash('error', 'Varian tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        $stmt = $db->prepare("
            SELECT si.*, o.order_number, u.username as sold_to_username
            FROM stock_items si
            LEFT JOIN orders o ON o.id = si.sold_to_order_id
            LEFT JOIN users u ON u.id = o.user_id
            WHERE si.variant_id = :variant_id
            ORDER BY si.is_sold ASC, si.created_at DESC
        ");
        $stmt->execute([':variant_id' => $variantId]);
        $stockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $availableCount = 0;
        $soldCount = 0;
        foreach ($stockItems as $item) {
            if ($item['is_sold']) $soldCount++;
            else $availableCount++;
        }

        $this->view('admin/products/stock', [
            'pageTitle' => 'Stok — ' . $variant['product_name'] . ' / ' . $variant['name'],
            'variant' => $variant,
            'stockItems' => $stockItems,
            'availableCount' => $availableCount,
            'soldCount' => $soldCount,
        ], 'admin');
    }

    /**
     * Add stock items (single or bulk)
     */
    public function addStock(string $variantId): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        // Verify variant exists and is auto_stock
        $stmt = $db->prepare("SELECT * FROM product_variants WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $variantId]);
        $variant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variant) {
            Session::flash('error', 'Varian tidak ditemukan.');
            $this->redirect('/admin/products');
            return;
        }

        $bulkData = trim($this->input('bulk_data', ''));

        if (empty($bulkData)) {
            Session::flash('error', 'Data stok tidak boleh kosong.');
            $this->redirect('/admin/stock/' . $variantId);
            return;
        }

        // Parse bulk data: one item per line
        $lines = array_filter(array_map('trim', explode("\n", $bulkData)));
        $insertCount = 0;

        $stmt = $db->prepare("INSERT INTO stock_items (variant_id, data_content, is_sold, created_at) VALUES (:variant_id, :data_content, 0, NOW())");

        foreach ($lines as $line) {
            if (empty($line)) continue;

            $encrypted = Helper::encrypt($line);
            $stmt->execute([
                ':variant_id' => $variantId,
                ':data_content' => $encrypted,
            ]);
            $insertCount++;
        }

        // Update stock count on variant
        $stmt = $db->prepare("UPDATE product_variants SET stock_count = (SELECT COUNT(*) FROM stock_items WHERE variant_id = :vid AND is_sold = 0), updated_at = NOW() WHERE id = :vid");
        $stmt->execute([':vid' => $variantId]);

        Helper::logActivity('stock_added', "Added {$insertCount} stock items to variant ID: {$variantId}");
        Session::flash('success', "{$insertCount} item stok berhasil ditambahkan.");
        $this->redirect('/admin/stock/' . $variantId);
    }
}
