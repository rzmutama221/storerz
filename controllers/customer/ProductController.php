<?php
/**
 * Customer ProductController - RZDK Store
 * 
 * Browse products and view details from customer dashboard.
 */

class ProductController extends Controller
{
    /**
     * Browse all products (customer dashboard catalog)
     */
    public function index(): void
    {
        $db = Model::getConnection();
        $categorySlug = trim($_GET['category'] ?? '');
        $search = trim($_GET['q'] ?? '');

        // Categories for filter
        $categories = $db->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.sort_order ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Build products query
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   MIN(pv.price) as price_from,
                   COUNT(pv.id) as variant_count
            FROM products p
            JOIN categories c ON c.id = p.category_id
            JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
            WHERE p.is_active = 1
        ";
        $params = [];

        if ($categorySlug) {
            $sql .= " AND c.slug = :category_slug";
            $params[':category_slug'] = $categorySlug;
        }

        if ($search) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search2)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
        }

        $sql .= " GROUP BY p.id ORDER BY c.sort_order ASC, p.sort_order ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('customer/products', [
            'pageTitle' => 'Produk',
            'categories' => $categories,
            'products' => $products,
            'categorySlug' => $categorySlug,
            'search' => $search,
        ], 'customer');
    }

    /**
     * Show single product detail with variants
     */
    public function show(string $slug): void
    {
        $db = Model::getConnection();

        // Get product
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.slug = :slug AND p.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/dashboard/products');
            return;
        }

        // Get active variants
        $stmt = $db->prepare("
            SELECT pv.*,
                   (CASE WHEN pv.fulfillment_mode = 'auto_stock' THEN 
                       (SELECT COUNT(*) FROM stock_items si WHERE si.variant_id = pv.id AND si.is_sold = 0)
                    ELSE 999 END) as available_stock
            FROM product_variants pv
            WHERE pv.product_id = :product_id AND pv.is_active = 1
            ORDER BY pv.sort_order ASC, pv.price ASC
        ");
        $stmt->execute([':product_id' => $product['id']]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('customer/product-detail', [
            'pageTitle' => $product['name'],
            'product' => $product,
            'variants' => $variants,
        ], 'customer');
    }
}
