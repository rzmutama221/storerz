<?php
/**
 * LandingController - Public Landing Page for RZDK Store
 * 
 * Handles public-facing pages: homepage, product catalog, product detail.
 * Pricing is displayed publicly (no login required).
 */

class LandingController extends Controller
{
    /**
     * Homepage / Landing page
     */
    public function index(): void
    {
        // Get active categories with product count
        $db = Model::getConnection();

        $categories = $db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
            WHERE c.is_active = 1 
            GROUP BY c.id 
            ORDER BY c.sort_order ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Get featured/popular products (first 8 active products)
        $featuredProducts = $db->query("
            SELECT p.*, c.name as category_name,
                   MIN(pv.price) as price_from,
                   COUNT(pv.id) as variant_count
            FROM products p
            JOIN categories c ON c.id = p.category_id
            JOIN product_variants pv ON pv.product_id = p.id AND pv.is_active = 1
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY p.sort_order ASC
            LIMIT 8
        ")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('landing/index', [
            'pageTitle' => 'RZDK Store — Premium Digital Store',
            'pageDescription' => 'Akses layanan premium streaming, AI, dan aplikasi kreatif dengan harga terjangkau. Garansi full, proses cepat, dashboard tracking.',
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
        ], 'landing');
    }

    /**
     * Products catalog page (public)
     */
    public function products(): void
    {
        $db = Model::getConnection();
        $categorySlug = trim($_GET['category'] ?? '');

        // Get all active categories
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

        $sql .= " GROUP BY p.id ORDER BY c.sort_order ASC, p.sort_order ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get current category name for title
        $currentCategory = null;
        if ($categorySlug) {
            foreach ($categories as $cat) {
                if ($cat['slug'] === $categorySlug) {
                    $currentCategory = $cat;
                    break;
                }
            }
        }

        $this->view('landing/products', [
            'pageTitle' => $currentCategory ? $currentCategory['name'] . ' — RZDK Store' : 'Semua Produk — RZDK Store',
            'categories' => $categories,
            'products' => $products,
            'currentCategory' => $currentCategory,
            'categorySlug' => $categorySlug,
        ], 'landing');
    }

    /**
     * Single product detail page (public)
     */
    public function productDetail(string $slug): void
    {
        $db = Model::getConnection();

        // Get product
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.slug = :slug AND p.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':slug' => $slug]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            require BASE_PATH . '/views/errors/404.php';
            return;
        }

        // Get variants
        $stmt = $db->prepare("
            SELECT * FROM product_variants 
            WHERE product_id = :product_id AND is_active = 1 
            ORDER BY sort_order ASC, price ASC
        ");
        $stmt->execute([':product_id' => $product['id']]);
        $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('landing/product-detail', [
            'pageTitle' => $product['name'] . ' — RZDK Store',
            'pageDescription' => $product['description'],
            'product' => $product,
            'variants' => $variants,
        ], 'landing');
    }
}
