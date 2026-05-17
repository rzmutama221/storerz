<?php
/**
 * Admin VoucherController - RZDK Store
 * 
 * CRUD for discount vouchers with rules:
 * - Type: percentage or fixed amount
 * - Limits: total usage, per-user usage
 * - Scope: all products or specific products
 * - Date range: start_date to end_date
 */

class VoucherController extends Controller
{
    /**
     * List all vouchers
     */
    public function index(): void
    {
        $db = Model::getConnection();

        $vouchers = $db->query("
            SELECT v.*,
                   (SELECT COUNT(*) FROM voucher_usage vu WHERE vu.voucher_id = v.id) as total_used
            FROM vouchers v
            ORDER BY v.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Get products for applicable_products selector
        $products = $db->query("SELECT id, name FROM products WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/vouchers', [
            'pageTitle' => 'Voucher',
            'vouchers' => $vouchers,
            'products' => $products,
        ], 'admin');
    }

    /**
     * Store new voucher
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'code' => 'required|min:3|max:50|unique:vouchers,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min_value:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/vouchers');
            return;
        }

        $code = strtoupper(trim($this->input('code')));
        $applicableProducts = $this->input('applicable_products');
        $applicableJson = null;

        if ($applicableProducts && is_array($applicableProducts)) {
            $applicableJson = json_encode(array_map('intval', $applicableProducts));
        }

        $db = Model::getConnection();
        $stmt = $db->prepare("
            INSERT INTO vouchers (code, type, value, min_order, max_discount, applicable_products, usage_limit, usage_per_user, used_count, start_date, end_date, is_active, created_at, updated_at)
            VALUES (:code, :type, :value, :min_order, :max_discount, :applicable_products, :usage_limit, :usage_per_user, 0, :start_date, :end_date, 1, NOW(), NOW())
        ");
        $stmt->execute([
            ':code' => $code,
            ':type' => $this->input('type'),
            ':value' => (float) $this->input('value'),
            ':min_order' => (float) $this->input('min_order', 0),
            ':max_discount' => $this->input('max_discount') ? (float) $this->input('max_discount') : null,
            ':applicable_products' => $applicableJson,
            ':usage_limit' => $this->input('usage_limit') ? (int) $this->input('usage_limit') : null,
            ':usage_per_user' => (int) $this->input('usage_per_user', 1),
            ':start_date' => $this->input('start_date'),
            ':end_date' => $this->input('end_date'),
        ]);

        Helper::logActivity('voucher_created', "Voucher '{$code}' created");
        Session::flash('success', "Voucher '{$code}' berhasil dibuat.");
        $this->redirect('/admin/vouchers');
    }

    /**
     * Update voucher
     */
    public function update(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min_value:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/vouchers');
            return;
        }

        $applicableProducts = $this->input('applicable_products');
        $applicableJson = null;
        if ($applicableProducts && is_array($applicableProducts)) {
            $applicableJson = json_encode(array_map('intval', $applicableProducts));
        }

        $isActive = $this->input('is_active') ? 1 : 0;

        $db = Model::getConnection();
        $stmt = $db->prepare("
            UPDATE vouchers SET type = :type, value = :value, min_order = :min_order, max_discount = :max_discount,
            applicable_products = :applicable_products, usage_limit = :usage_limit, usage_per_user = :usage_per_user,
            start_date = :start_date, end_date = :end_date, is_active = :is_active, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':type' => $this->input('type'),
            ':value' => (float) $this->input('value'),
            ':min_order' => (float) $this->input('min_order', 0),
            ':max_discount' => $this->input('max_discount') ? (float) $this->input('max_discount') : null,
            ':applicable_products' => $applicableJson,
            ':usage_limit' => $this->input('usage_limit') ? (int) $this->input('usage_limit') : null,
            ':usage_per_user' => (int) $this->input('usage_per_user', 1),
            ':start_date' => $this->input('start_date'),
            ':end_date' => $this->input('end_date'),
            ':is_active' => $isActive,
            ':id' => $id,
        ]);

        Helper::logActivity('voucher_updated', "Voucher #{$id} updated");
        Session::flash('success', 'Voucher berhasil diupdate.');
        $this->redirect('/admin/vouchers');
    }

    /**
     * Delete voucher
     */
    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("SELECT code FROM vouchers WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $db->prepare("DELETE FROM vouchers WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Helper::logActivity('voucher_deleted', "Voucher '{$voucher['code']}' deleted");
        Session::flash('success', "Voucher berhasil dihapus.");
        $this->redirect('/admin/vouchers');
    }
}
