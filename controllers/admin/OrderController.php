<?php
/**
 * Admin OrderController - RZDK Store
 * 
 * Handles order management: listing, approval/rejection, payment verification,
 * fulfillment, and warranty management from admin side.
 */

class OrderController extends Controller
{
    /**
     * List all orders with filters
     */
    public function index(): void
    {
        $db = Model::getConnection();
        $statusFilter = trim($_GET['status'] ?? '');
        $search = trim($_GET['q'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $sql = "
            SELECT o.*, u.username, p.name as product_name, pv.name as variant_name
            FROM orders o
            JOIN users u ON u.id = o.user_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE 1=1
        ";
        $countSql = "SELECT COUNT(*) as total FROM orders o JOIN users u ON u.id = o.user_id WHERE 1=1";
        $params = [];

        if ($statusFilter) {
            $sql .= " AND o.status = :status";
            $countSql .= " AND o.status = :status";
            $params[':status'] = $statusFilter;
        }

        if ($search) {
            $sql .= " AND (o.order_number LIKE :search OR u.username LIKE :search2)";
            $countSql .= " AND (o.order_number LIKE :search OR u.username LIKE :search2)";
            $params[':search'] = "%{$search}%";
            $params[':search2'] = "%{$search}%";
        }

        // Count total
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = (int) ceil($total / $perPage);

        // Get paginated data
        $offset = ($page - 1) * $perPage;
        $sql .= " ORDER BY o.created_at DESC LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count by status for tabs
        $statusCounts = $db->query("
            SELECT status, COUNT(*) as total FROM orders GROUP BY status
        ")->fetchAll(PDO::FETCH_ASSOC);
        $counts = [];
        foreach ($statusCounts as $sc) {
            $counts[$sc['status']] = (int) $sc['total'];
        }

        $this->view('admin/orders/index', [
            'pageTitle' => 'Manajemen Order',
            'orders' => $orders,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'counts' => $counts,
        ], 'admin');
    }

    /**
     * Show single order detail
     */
    public function show(string $id): void
    {
        $db = Model::getConnection();

        $stmt = $db->prepare("
            SELECT o.*, u.username, u.email as customer_email, u.phone as customer_phone, u.full_name as customer_name,
                   p.name as product_name, p.logo_path, p.slot_type,
                   pv.name as variant_name, pv.duration_days, pv.type as variant_type,
                   pv.fulfillment_mode, pv.platform, pv.warranty_days,
                   v.code as voucher_code
            FROM orders o
            JOIN users u ON u.id = o.user_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            LEFT JOIN vouchers v ON v.id = o.voucher_id
            WHERE o.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak ditemukan.');
            $this->redirect('/admin/orders');
            return;
        }

        // Decrypt fulfillment data for admin view
        $fulfillmentData = null;
        if ($order['fulfillment_data']) {
            $fulfillmentData = Helper::decrypt($order['fulfillment_data']);
        }

        $this->view('admin/orders/show', [
            'pageTitle' => 'Order ' . $order['order_number'],
            'order' => $order,
            'fulfillmentData' => $fulfillmentData,
        ], 'admin');
    }

    /**
     * Approve an order (allow customer to proceed to payment)
     */
    public function approve(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND status = 'pending_approval' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak ditemukan atau sudah diproses.');
            $this->redirect('/admin/orders');
            return;
        }

        // Set payment deadline (configurable, default 2 hours)
        $timeoutHours = (int) (Helper::setting('payment_timeout_hours') ?: 2);
        $paymentDeadline = date('Y-m-d H:i:s', strtotime("+{$timeoutHours} hours"));

        $stmt = $db->prepare("UPDATE orders SET status = 'approved', approved_at = NOW(), payment_deadline = :deadline, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':deadline' => $paymentDeadline, ':id' => $id]);

        Helper::logActivity('order_approved', "Order {$order['order_number']} approved", Auth::id());
        Session::flash('success', "Order {$order['order_number']} berhasil di-approve. Customer memiliki {$timeoutHours} jam untuk melakukan pembayaran.");
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * Reject an order
     */
    public function reject(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $reason = trim($this->input('rejection_reason', ''));

        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND status = 'pending_approval' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak ditemukan atau sudah diproses.');
            $this->redirect('/admin/orders');
            return;
        }

        $stmt = $db->prepare("UPDATE orders SET status = 'rejected', rejection_reason = :reason, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':reason' => $reason ?: 'Stok tidak tersedia', ':id' => $id]);

        Helper::logActivity('order_rejected', "Order {$order['order_number']} rejected: {$reason}", Auth::id());
        Session::flash('success', "Order {$order['order_number']} ditolak.");
        $this->redirect('/admin/orders');
    }

    /**
     * Verify payment (confirm or reject payment proof)
     */
    public function verifyPayment(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $action = $this->input('action'); // 'confirm' or 'reject'

        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND status IN ('approved', 'awaiting_payment') LIMIT 1");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak ditemukan atau status tidak valid.');
            $this->redirect('/admin/orders');
            return;
        }

        if ($action === 'confirm') {
            $stmt = $db->prepare("UPDATE orders SET status = 'paid', paid_at = NOW(), updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);

            Helper::logActivity('payment_verified', "Payment verified for order {$order['order_number']}", Auth::id());
            Session::flash('success', "Pembayaran order {$order['order_number']} berhasil dikonfirmasi.");
        } else {
            $stmt = $db->prepare("UPDATE orders SET status = 'approved', payment_proof_path = NULL, paid_at = NULL, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id]);

            Session::flash('warning', "Bukti bayar order {$order['order_number']} ditolak. Customer perlu upload ulang.");
        }

        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * Fulfill order (input credential/data manually or assign from stock)
     */
    public function fulfill(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("
            SELECT o.*, pv.fulfillment_mode, pv.duration_days, pv.warranty_days
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            WHERE o.id = :id AND o.status = 'paid' LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak ditemukan atau belum dibayar.');
            $this->redirect('/admin/orders');
            return;
        }

        $fulfillmentData = trim($this->input('fulfillment_data', ''));
        $fulfillmentNotes = trim($this->input('fulfillment_notes', ''));

        if ($order['fulfillment_mode'] === 'auto_stock') {
            // Auto-assign from stock
            $stmt = $db->prepare("SELECT * FROM stock_items WHERE variant_id = :vid AND is_sold = 0 ORDER BY created_at ASC LIMIT 1");
            $stmt->execute([':vid' => $order['variant_id']]);
            $stockItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stockItem) {
                Session::flash('error', 'Stok habis! Tidak ada item tersedia untuk di-assign. Tambahkan stok atau fulfill secara manual.');
                $this->redirect('/admin/orders/' . $id);
                return;
            }

            // Mark stock item as sold
            $stmt = $db->prepare("UPDATE stock_items SET is_sold = 1, sold_to_order_id = :oid, sold_at = NOW() WHERE id = :id");
            $stmt->execute([':oid' => $id, ':id' => $stockItem['id']]);

            // Update variant stock count
            $stmt = $db->prepare("UPDATE product_variants SET stock_count = GREATEST(0, stock_count - 1), updated_at = NOW() WHERE id = :vid");
            $stmt->execute([':vid' => $order['variant_id']]);

            $fulfillmentData = $stockItem['data_content']; // Already encrypted
        } else {
            // Manual fulfillment - encrypt the data
            if (empty($fulfillmentData)) {
                Session::flash('error', 'Data fulfillment tidak boleh kosong.');
                $this->redirect('/admin/orders/' . $id);
                return;
            }
            $fulfillmentData = Helper::encrypt($fulfillmentData);
        }

        // Update order: set to processing
        $stmt = $db->prepare("UPDATE orders SET status = 'processing', fulfillment_data = :data, fulfillment_notes = :notes, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':data' => $fulfillmentData,
            ':notes' => $fulfillmentNotes ?: null,
            ':id' => $id,
        ]);

        Helper::logActivity('order_fulfilled', "Order {$order['order_number']} fulfilled", Auth::id());
        Session::flash('success', "Data produk berhasil di-input. Klik 'Selesaikan Order' untuk mengirim ke customer.");
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * Complete order (final step - mark as completed, set active_until, record transaction)
     */
    public function complete(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("
            SELECT o.*, pv.duration_days, pv.warranty_days, p.name as product_name, pv.name as variant_name, u.email as customer_email, u.username
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            JOIN users u ON u.id = o.user_id
            WHERE o.id = :id AND o.status = 'processing' LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak ditemukan atau status tidak valid.');
            $this->redirect('/admin/orders');
            return;
        }

        // Calculate active_until
        $activeUntil = date('Y-m-d H:i:s', strtotime("+{$order['duration_days']} days"));

        // Update order
        $stmt = $db->prepare("UPDATE orders SET status = 'completed', completed_at = NOW(), active_until = :active_until, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':active_until' => $activeUntil, ':id' => $id]);

        // Record transaction (income)
        $stmt = $db->prepare("INSERT INTO transactions (order_id, type, amount, description, recorded_at) VALUES (:oid, 'income', :amount, :desc, NOW())");
        $stmt->execute([
            ':oid' => $id,
            ':amount' => $order['final_price'],
            ':desc' => "Order {$order['order_number']} - {$order['product_name']} ({$order['variant_name']})",
        ]);

        // Send completion email to customer
        Mailer::quickSend(
            $order['customer_email'],
            'Order Selesai — ' . $order['order_number'],
            'order-completed',
            [
                'username' => $order['username'],
                'order_number' => $order['order_number'],
                'product_name' => $order['product_name'],
                'variant_name' => $order['variant_name'],
                'dashboard_url' => Helper::url('dashboard/orders/' . $id),
            ]
        );

        Helper::logActivity('order_completed', "Order {$order['order_number']} completed. Active until: {$activeUntil}", Auth::id());
        Session::flash('success', "Order {$order['order_number']} berhasil diselesaikan! Email notifikasi telah dikirim ke customer.");
        $this->redirect('/admin/orders/' . $id);
    }

    /**
     * List warranty claims
     */
    public function warranties(): void
    {
        $db = Model::getConnection();
        $statusFilter = trim($_GET['status'] ?? '');

        $sql = "
            SELECT w.*, u.username, o.order_number, p.name as product_name, pv.name as variant_name
            FROM warranties w
            JOIN users u ON u.id = w.user_id
            JOIN orders o ON o.id = w.order_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE 1=1
        ";
        $params = [];

        if ($statusFilter) {
            $sql .= " AND w.status = :status";
            $params[':status'] = $statusFilter;
        }

        $sql .= " ORDER BY w.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $warranties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/orders/warranties', [
            'pageTitle' => 'Klaim Garansi',
            'warranties' => $warranties,
            'statusFilter' => $statusFilter,
        ], 'admin');
    }

    /**
     * Respond to warranty claim
     */
    public function respondWarranty(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $action = $this->input('action'); // 'resolve' or 'reject'
        $response = trim($this->input('admin_response', ''));

        $newStatus = ($action === 'resolve') ? 'resolved' : 'rejected';

        $stmt = $db->prepare("UPDATE warranties SET status = :status, admin_response = :response, resolved_at = NOW(), updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':status' => $newStatus,
            ':response' => $response,
            ':id' => $id,
        ]);

        Helper::logActivity('warranty_responded', "Warranty #{$id} {$newStatus}", Auth::id());
        Session::flash('success', "Klaim garansi berhasil di-" . ($action === 'resolve' ? 'resolve' : 'tolak') . ".");
        $this->redirect('/admin/warranties');
    }
}
