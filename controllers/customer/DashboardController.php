<?php
/**
 * Customer DashboardController - RZDK Store
 * 
 * Customer homepage showing active products, recent orders, and announcements.
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        $db = Model::getConnection();
        $userId = Auth::id();

        // Active products (orders that are completed and still within active_until)
        $activeProducts = $db->prepare("
            SELECT o.*, p.name as product_name, p.logo_path, pv.name as variant_name, pv.duration_days
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.user_id = :user_id AND o.status = 'completed' AND o.active_until >= NOW()
            ORDER BY o.active_until ASC
            LIMIT 5
        ");
        $activeProducts->execute([':user_id' => $userId]);
        $activeProducts = $activeProducts->fetchAll(PDO::FETCH_ASSOC);

        // Recent orders (last 5)
        $recentOrders = $db->prepare("
            SELECT o.*, p.name as product_name, pv.name as variant_name
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        $recentOrders->execute([':user_id' => $userId]);
        $recentOrders = $recentOrders->fetchAll(PDO::FETCH_ASSOC);

        // Announcements for products this customer has purchased
        $announcements = $db->prepare("
            SELECT a.*, p.name as product_name
            FROM announcements a
            JOIN products p ON p.id = a.product_id
            WHERE a.is_active = 1
              AND (a.expires_at IS NULL OR a.expires_at >= NOW())
              AND a.product_id IN (
                  SELECT DISTINCT pv.product_id 
                  FROM orders o 
                  JOIN product_variants pv ON pv.id = o.variant_id 
                  WHERE o.user_id = :user_id AND o.status = 'completed'
              )
              AND a.id NOT IN (
                  SELECT announcement_id FROM announcement_reads WHERE user_id = :user_id2
              )
            ORDER BY a.priority DESC, a.published_at DESC
            LIMIT 5
        ");
        $announcements->execute([':user_id' => $userId, ':user_id2' => $userId]);
        $announcements = $announcements->fetchAll(PDO::FETCH_ASSOC);

        // Quick stats
        $stats = $db->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN final_price ELSE 0 END), 0) as total_spend
            FROM orders WHERE user_id = :user_id
        ");
        $stats->execute([':user_id' => $userId]);
        $stats = $stats->fetch(PDO::FETCH_ASSOC);

        $this->view('customer/dashboard', [
            'pageTitle' => 'Dashboard',
            'activeProducts' => $activeProducts,
            'recentOrders' => $recentOrders,
            'announcements' => $announcements,
            'totalOrders' => (int) $stats['total_orders'],
            'totalSpend' => (float) $stats['total_spend'],
        ], 'customer');
    }
}
