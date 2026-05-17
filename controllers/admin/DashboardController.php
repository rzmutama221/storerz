<?php
/**
 * Admin DashboardController - RZDK Store
 * 
 * Shows admin overview with key metrics and quick actions.
 */

class DashboardController extends Controller
{
    /**
     * Admin dashboard homepage
     */
    public function index(): void
    {
        $db = Model::getConnection();

        // Today's stats
        $today = date('Y-m-d');

        $todayOrders = $db->query("SELECT COUNT(*) as total FROM orders WHERE DATE(created_at) = '{$today}'")->fetch(PDO::FETCH_ASSOC)['total'];
        $todayRevenue = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income' AND DATE(recorded_at) = '{$today}'")->fetch(PDO::FETCH_ASSOC)['total'];
        $pendingOrders = $db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending_approval'")->fetch(PDO::FETCH_ASSOC)['total'];
        $awaitingPayment = $db->query("SELECT COUNT(*) as total FROM orders WHERE status IN ('approved', 'awaiting_payment')")->fetch(PDO::FETCH_ASSOC)['total'];

        // Monthly stats
        $monthStart = date('Y-m-01');
        $monthRevenue = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income' AND recorded_at >= '{$monthStart}'")->fetch(PDO::FETCH_ASSOC)['total'];
        $monthOrders = $db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'completed' AND completed_at >= '{$monthStart}'")->fetch(PDO::FETCH_ASSOC)['total'];
        $newCustomers = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer' AND created_at >= '{$monthStart}'")->fetch(PDO::FETCH_ASSOC)['total'];
        $totalCustomers = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'")->fetch(PDO::FETCH_ASSOC)['total'];

        // Recent orders (last 10)
        $recentOrders = $db->query("
            SELECT o.*, u.username, p.name as product_name, pv.name as variant_name
            FROM orders o
            JOIN users u ON u.id = o.user_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            ORDER BY o.created_at DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Low stock alerts
        $lowStock = $db->query("
            SELECT pv.*, p.name as product_name 
            FROM product_variants pv
            JOIN products p ON p.id = pv.product_id
            WHERE pv.fulfillment_mode = 'auto_stock' AND pv.stock_count <= 2 AND pv.is_active = 1
            ORDER BY pv.stock_count ASC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Expiring slots (Netflix + ChatGPT within 3 days)
        $expiringDate = date('Y-m-d', strtotime('+3 days'));
        $expiringSlots = $db->query("
            (SELECT 'netflix' as type, ns.profile_name as slot_name, ns.customer_name, ns.expired_date, na.email as account_email
             FROM netflix_slots ns
             JOIN netflix_accounts na ON na.id = ns.account_id
             WHERE ns.status = 'occupied' AND ns.expired_date <= '{$expiringDate}' AND ns.expired_date >= '{$today}')
            UNION ALL
            (SELECT 'chatgpt' as type, CONCAT('Slot ', cm.slot_number) as slot_name, cm.customer_email as customer_name, cm.expired_date, ca.head_email as account_email
             FROM chatgpt_members cm
             JOIN chatgpt_accounts ca ON ca.id = cm.account_id
             WHERE cm.status = 'occupied' AND cm.expired_date <= '{$expiringDate}' AND cm.expired_date >= '{$today}')
            ORDER BY expired_date ASC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Pending warranties
        $pendingWarranties = $db->query("SELECT COUNT(*) as total FROM warranties WHERE status IN ('submitted', 'reviewing')")->fetch(PDO::FETCH_ASSOC)['total'];

        $this->view('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'todayOrders' => $todayOrders,
            'todayRevenue' => $todayRevenue,
            'pendingOrders' => $pendingOrders,
            'awaitingPayment' => $awaitingPayment,
            'monthRevenue' => $monthRevenue,
            'monthOrders' => $monthOrders,
            'newCustomers' => $newCustomers,
            'totalCustomers' => $totalCustomers,
            'recentOrders' => $recentOrders,
            'lowStock' => $lowStock,
            'expiringSlots' => $expiringSlots,
            'pendingWarranties' => $pendingWarranties,
        ], 'admin');
    }
}
