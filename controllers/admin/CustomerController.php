<?php
/**
 * Admin CustomerController - RZDK Store
 * 
 * Manage customers: list, detail view, suspend/activate.
 */

class CustomerController extends Controller
{
    /**
     * List all customers
     */
    public function index(): void
    {
        $db = Model::getConnection();
        $search = trim($_GET['q'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');

        $sql = "
            SELECT u.*,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) as total_orders,
                   (SELECT COALESCE(SUM(final_price), 0) FROM orders o WHERE o.user_id = u.id AND o.status = 'completed') as total_spend
            FROM users u
            WHERE u.role = 'customer'
        ";
        $params = [];

        if ($search) {
            $sql .= " AND (u.username LIKE :search OR u.email LIKE :search2 OR u.full_name LIKE :search3)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
            $params[':search3'] = "%{$search}%";
        }

        if ($statusFilter) {
            $sql .= " AND u.status = :status";
            $params[':status'] = $statusFilter;
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/customers', [
            'pageTitle' => 'Customer',
            'customers' => $customers,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ], 'admin');
    }

    /**
     * Show customer detail
     */
    public function show(string $id): void
    {
        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND role = 'customer' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            Session::flash('error', 'Customer tidak ditemukan.');
            $this->redirect('/admin/customers');
            return;
        }

        // Get customer's orders
        $stmt = $db->prepare("
            SELECT o.*, p.name as product_name, pv.name as variant_name
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([':user_id' => $id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats
        $stats = $db->prepare("
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN final_price ELSE 0 END), 0) as total_spend
            FROM orders WHERE user_id = :user_id
        ");
        $stats->execute([':user_id' => $id]);
        $stats = $stats->fetch(PDO::FETCH_ASSOC);

        $this->view('admin/customers/show', [
            'pageTitle' => 'Customer: ' . $customer['username'],
            'customer' => $customer,
            'orders' => $orders,
            'stats' => $stats,
        ], 'admin');
    }

    /**
     * Suspend customer
     */
    public function suspend(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $reason = trim($this->input('reason', ''));

        $stmt = $db->prepare("SELECT username FROM users WHERE id = :id AND role = 'customer' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'Customer tidak ditemukan.');
            $this->redirect('/admin/customers');
            return;
        }

        $stmt = $db->prepare("UPDATE users SET status = 'suspended', notes_admin = :notes, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':notes' => $reason ?: 'Suspended by admin', ':id' => $id]);

        Helper::logActivity('customer_suspended', "Customer '{$user['username']}' suspended: {$reason}", Auth::id());
        Session::flash('success', "Customer '{$user['username']}' berhasil di-suspend.");
        $this->redirect('/admin/customers/' . $id);
    }

    /**
     * Activate/reactivate customer
     */
    public function activate(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT username FROM users WHERE id = :id AND role = 'customer' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'Customer tidak ditemukan.');
            $this->redirect('/admin/customers');
            return;
        }

        $stmt = $db->prepare("UPDATE users SET status = 'active', notes_admin = NULL, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Helper::logActivity('customer_activated', "Customer '{$user['username']}' reactivated", Auth::id());
        Session::flash('success', "Customer '{$user['username']}' berhasil diaktifkan kembali.");
        $this->redirect('/admin/customers/' . $id);
    }
}
