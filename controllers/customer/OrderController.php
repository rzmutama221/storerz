<?php
/**
 * Customer OrderController - RZDK Store
 * 
 * Handles order creation, listing, detail view, and payment upload.
 */

class OrderController extends Controller
{
    /**
     * Create a new order
     */
    public function create(): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $userId = Auth::id();

        $variantId = (int) $this->input('variant_id');
        $voucherCode = trim($this->input('voucher_code', ''));
        $customerNotes = trim($this->input('customer_notes', ''));

        // Validate variant exists and is active
        $stmt = $db->prepare("
            SELECT pv.*, p.name as product_name, p.is_active as product_active
            FROM product_variants pv
            JOIN products p ON p.id = pv.product_id
            WHERE pv.id = :id AND pv.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':id' => $variantId]);
        $variant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variant || !$variant['product_active']) {
            Session::flash('error', 'Produk atau varian tidak tersedia.');
            $this->redirect('/dashboard/products');
            return;
        }

        // Check stock for auto_stock variants
        if ($variant['fulfillment_mode'] === 'auto_stock') {
            $stmt = $db->prepare("SELECT COUNT(*) as available FROM stock_items WHERE variant_id = :vid AND is_sold = 0");
            $stmt->execute([':vid' => $variantId]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);
            if ((int) $stock['available'] <= 0) {
                Session::flash('error', 'Maaf, stok untuk varian ini sedang habis.');
                $this->back();
                return;
            }
        }

        // Calculate price
        $originalPrice = (float) $variant['price'];
        $discountAmount = 0.00;
        $voucherId = null;

        // Apply voucher if provided
        if ($voucherCode) {
            $voucher = $this->validateVoucher($voucherCode, $userId, $variant['product_id'], $originalPrice);
            if ($voucher) {
                $discountAmount = $voucher['discount'];
                $voucherId = $voucher['id'];
            } else {
                Session::flash('warning', 'Kode voucher tidak valid atau tidak dapat digunakan.');
            }
        }

        $finalPrice = max(0, $originalPrice - $discountAmount);

        // Generate order number
        $orderNumber = Helper::generateOrderNumber();

        // Create order
        $stmt = $db->prepare("
            INSERT INTO orders (order_number, user_id, variant_id, quantity, original_price, discount_amount, final_price, voucher_id, status, payment_method, customer_notes, created_at, updated_at)
            VALUES (:order_number, :user_id, :variant_id, 1, :original_price, :discount_amount, :final_price, :voucher_id, 'pending_approval', 'qris_manual', :customer_notes, NOW(), NOW())
        ");
        $stmt->execute([
            ':order_number' => $orderNumber,
            ':user_id' => $userId,
            ':variant_id' => $variantId,
            ':original_price' => $originalPrice,
            ':discount_amount' => $discountAmount,
            ':final_price' => $finalPrice,
            ':voucher_id' => $voucherId,
            ':customer_notes' => $customerNotes ?: null,
        ]);

        $orderId = $db->lastInsertId();

        // Record voucher usage if applied
        if ($voucherId) {
            $db->prepare("INSERT INTO voucher_usage (voucher_id, user_id, order_id, used_at) VALUES (:vid, :uid, :oid, NOW())")->execute([
                ':vid' => $voucherId, ':uid' => $userId, ':oid' => $orderId
            ]);
            $db->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = :id")->execute([':id' => $voucherId]);
        }

        Helper::logActivity('order_created', "Order {$orderNumber} created for {$variant['product_name']} - {$variant['name']}", $userId);
        Session::flash('success', "Order {$orderNumber} berhasil dibuat! Menunggu approval dari admin.");
        $this->redirect('/dashboard/orders/' . $orderId);
    }

    /**
     * List customer's orders
     */
    public function index(): void
    {
        $db = Model::getConnection();
        $userId = Auth::id();
        $statusFilter = trim($_GET['status'] ?? '');

        $sql = "
            SELECT o.*, p.name as product_name, p.logo_path, pv.name as variant_name
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.user_id = :user_id
        ";
        $params = [':user_id' => $userId];

        if ($statusFilter) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $statusFilter;
        }

        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('customer/orders', [
            'pageTitle' => 'Transaksi Saya',
            'orders' => $orders,
            'statusFilter' => $statusFilter,
        ], 'customer');
    }

    /**
     * Show single order detail
     */
    public function show(string $id): void
    {
        $db = Model::getConnection();
        $userId = Auth::id();

        $stmt = $db->prepare("
            SELECT o.*, p.name as product_name, p.logo_path, pv.name as variant_name,
                   pv.duration_days, pv.type as variant_type, pv.platform, pv.notes as variant_notes,
                   v.code as voucher_code
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            LEFT JOIN vouchers v ON v.id = o.voucher_id
            WHERE o.id = :id AND o.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak ditemukan.');
            $this->redirect('/dashboard/orders');
            return;
        }

        // Decrypt fulfillment data if completed
        $fulfillmentData = null;
        if ($order['status'] === 'completed' && $order['fulfillment_data']) {
            $fulfillmentData = Helper::decrypt($order['fulfillment_data']);
        }

        $this->view('customer/order-detail', [
            'pageTitle' => 'Order ' . $order['order_number'],
            'order' => $order,
            'fulfillmentData' => $fulfillmentData,
        ], 'customer');
    }

    /**
     * Show payment page (after approval)
     */
    public function payment(string $id): void
    {
        $db = Model::getConnection();
        $userId = Auth::id();

        $stmt = $db->prepare("
            SELECT o.*, p.name as product_name, pv.name as variant_name
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.id = :id AND o.user_id = :user_id AND o.status IN ('approved', 'awaiting_payment')
            LIMIT 1
        ");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak tersedia untuk pembayaran.');
            $this->redirect('/dashboard/orders');
            return;
        }

        // Check if payment deadline passed
        if ($order['payment_deadline'] && strtotime($order['payment_deadline']) < time()) {
            Session::flash('error', 'Batas waktu pembayaran telah lewat. Order otomatis dibatalkan.');
            $this->redirect('/dashboard/orders/' . $id);
            return;
        }

        $this->view('customer/payment', [
            'pageTitle' => 'Pembayaran',
            'order' => $order,
        ], 'customer');
    }

    /**
     * Upload payment proof
     */
    public function uploadProof(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $userId = Auth::id();

        // Validate order
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id AND status IN ('approved', 'awaiting_payment') LIMIT 1");
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak valid.');
            $this->redirect('/dashboard/orders');
            return;
        }

        // Check deadline
        if ($order['payment_deadline'] && strtotime($order['payment_deadline']) < time()) {
            Session::flash('error', 'Batas waktu pembayaran telah lewat.');
            $this->redirect('/dashboard/orders/' . $id);
            return;
        }

        // Upload proof image
        $proofPath = Helper::uploadFile('payment_proof', 'payments', [
            'image/jpeg', 'image/jpg', 'image/png', 'image/webp'
        ], 3072); // Max 3MB

        if (!$proofPath) {
            Session::flash('error', 'Gagal mengupload bukti bayar. Pastikan file berupa gambar (JPG/PNG/WebP) dan ukuran maks 3MB.');
            $this->redirect('/dashboard/payment/' . $id);
            return;
        }

        // Update order
        $stmt = $db->prepare("UPDATE orders SET payment_proof_path = :proof, status = 'awaiting_payment', paid_at = NOW(), updated_at = NOW() WHERE id = :id");
        $stmt->execute([':proof' => $proofPath, ':id' => $id]);

        Helper::logActivity('payment_proof_uploaded', "Payment proof uploaded for order {$order['order_number']}", $userId);
        Session::flash('success', 'Bukti pembayaran berhasil diupload! Admin akan memverifikasi dalam waktu dekat.');
        $this->redirect('/dashboard/orders/' . $id);
    }

    /**
     * Validate a voucher code
     */
    private function validateVoucher(string $code, int $userId, int $productId, float $orderAmount): ?array
    {
        $db = Model::getConnection();

        $stmt = $db->prepare("
            SELECT * FROM vouchers 
            WHERE code = :code AND is_active = 1 
              AND start_date <= CURDATE() AND end_date >= CURDATE()
              AND (usage_limit IS NULL OR used_count < usage_limit)
            LIMIT 1
        ");
        $stmt->execute([':code' => $code]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$voucher) return null;

        // Check minimum order
        if ($orderAmount < (float) $voucher['min_order']) return null;

        // Check per-user usage limit
        $stmt = $db->prepare("SELECT COUNT(*) as used FROM voucher_usage WHERE voucher_id = :vid AND user_id = :uid");
        $stmt->execute([':vid' => $voucher['id'], ':uid' => $userId]);
        $usage = $stmt->fetch(PDO::FETCH_ASSOC);
        if ((int) $usage['used'] >= (int) $voucher['usage_per_user']) return null;

        // Check applicable products
        if ($voucher['applicable_products']) {
            $applicableIds = json_decode($voucher['applicable_products'], true);
            if (is_array($applicableIds) && !in_array($productId, $applicableIds)) {
                return null;
            }
        }

        // Calculate discount
        $discount = 0;
        if ($voucher['type'] === 'percentage') {
            $discount = $orderAmount * ((float) $voucher['value'] / 100);
            if ($voucher['max_discount'] && $discount > (float) $voucher['max_discount']) {
                $discount = (float) $voucher['max_discount'];
            }
        } else {
            $discount = (float) $voucher['value'];
        }

        return [
            'id' => $voucher['id'],
            'discount' => min($discount, $orderAmount),
        ];
    }
}
