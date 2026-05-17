<?php
/**
 * Customer WarrantyController - RZDK Store
 * 
 * Handles warranty claim submission and tracking from customer side.
 */

class WarrantyController extends Controller
{
    /**
     * List customer's warranty claims + form to submit new
     */
    public function index(): void
    {
        $db = Model::getConnection();
        $userId = Auth::id();

        // Get all claims by this customer
        $claims = $db->prepare("
            SELECT w.*, o.order_number, p.name as product_name, pv.name as variant_name
            FROM warranties w
            JOIN orders o ON o.id = w.order_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE w.user_id = :user_id
            ORDER BY w.created_at DESC
        ");
        $claims->execute([':user_id' => $userId]);
        $claims = $claims->fetchAll(PDO::FETCH_ASSOC);

        // Get eligible orders for warranty claim (completed, still within warranty period)
        $eligibleOrders = $db->prepare("
            SELECT o.id, o.order_number, o.completed_at, o.active_until,
                   p.name as product_name, pv.name as variant_name, pv.warranty_days
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.user_id = :user_id 
              AND o.status = 'completed'
              AND o.active_until >= NOW()
              AND (pv.warranty_days = 0 OR DATE_ADD(o.completed_at, INTERVAL pv.warranty_days DAY) >= NOW())
        ");
        $eligibleOrders->execute([':user_id' => $userId]);
        $eligibleOrders = $eligibleOrders->fetchAll(PDO::FETCH_ASSOC);

        $this->view('customer/warranty', [
            'pageTitle' => 'Garansi',
            'claims' => $claims,
            'eligibleOrders' => $eligibleOrders,
        ], 'customer');
    }

    /**
     * Submit a new warranty claim
     */
    public function create(): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $userId = Auth::id();

        $orderId = (int) $this->input('order_id');
        $issueType = $this->input('issue_type', '');
        $description = trim($this->input('description', ''));

        // Validate input
        $validator = new Validator($_POST);
        $validator->rules([
            'order_id' => 'required|numeric',
            'issue_type' => 'required|in:login_error,service_down,account_banned,not_working,other',
            'description' => 'required|min:10|max:2000',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/dashboard/warranty');
            return;
        }

        // Verify order belongs to user and is eligible
        $stmt = $db->prepare("
            SELECT o.* FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            WHERE o.id = :id AND o.user_id = :user_id AND o.status = 'completed' AND o.active_until >= NOW()
            LIMIT 1
        ");
        $stmt->execute([':id' => $orderId, ':user_id' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            Session::flash('error', 'Order tidak valid atau masa garansi sudah habis.');
            $this->redirect('/dashboard/warranty');
            return;
        }

        // Check if there's already an active claim for this order
        $stmt = $db->prepare("SELECT id FROM warranties WHERE order_id = :oid AND user_id = :uid AND status IN ('submitted', 'reviewing') LIMIT 1");
        $stmt->execute([':oid' => $orderId, ':uid' => $userId]);
        if ($stmt->fetch()) {
            Session::flash('warning', 'Sudah ada klaim garansi aktif untuk order ini. Tunggu respon admin.');
            $this->redirect('/dashboard/warranty');
            return;
        }

        // Handle screenshot upload
        $screenshotPath = null;
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $screenshotPath = Helper::uploadFile('screenshot', 'warranties', [
                'image/jpeg', 'image/jpg', 'image/png', 'image/webp'
            ], 3072);
        }

        // Create warranty claim
        $stmt = $db->prepare("
            INSERT INTO warranties (order_id, user_id, issue_type, description, screenshot_path, status, created_at, updated_at)
            VALUES (:order_id, :user_id, :issue_type, :description, :screenshot_path, 'submitted', NOW(), NOW())
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':user_id' => $userId,
            ':issue_type' => $issueType,
            ':description' => $description,
            ':screenshot_path' => $screenshotPath,
        ]);

        Helper::logActivity('warranty_claimed', "Warranty claim submitted for order {$order['order_number']}", $userId);
        Session::flash('success', 'Klaim garansi berhasil diajukan. Admin akan review dalam waktu dekat.');
        $this->redirect('/dashboard/warranty');
    }
}
