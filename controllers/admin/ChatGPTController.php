<?php
/**
 * Admin ChatGPTController - RZDK Store
 * 
 * Manages ChatGPT Business head accounts and their member slots.
 * Each head account can invite up to max_members (default 4).
 */

class ChatGPTController extends Controller
{
    /**
     * ChatGPT management dashboard
     */
    public function index(): void
    {
        $db = Model::getConnection();

        // Get all ChatGPT accounts with member stats
        $accounts = $db->query("
            SELECT ca.*,
                   COUNT(cm.id) as total_slots,
                   SUM(CASE WHEN cm.status = 'available' THEN 1 ELSE 0 END) as available_slots,
                   SUM(CASE WHEN cm.status = 'occupied' THEN 1 ELSE 0 END) as occupied_slots,
                   SUM(CASE WHEN cm.status = 'expired' THEN 1 ELSE 0 END) as expired_slots
            FROM chatgpt_accounts ca
            LEFT JOIN chatgpt_members cm ON cm.account_id = ca.id
            GROUP BY ca.id
            ORDER BY ca.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // All members overview
        $allMembers = $db->query("
            SELECT cm.*, ca.head_email, ca.workspace_name, u.username as customer_username
            FROM chatgpt_members cm
            JOIN chatgpt_accounts ca ON ca.id = cm.account_id
            LEFT JOIN users u ON u.id = cm.customer_id
            WHERE cm.status IN ('occupied', 'expired', 'pending_invite')
            ORDER BY cm.expired_date ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Summary stats
        $stats = $db->query("
            SELECT 
                COUNT(DISTINCT ca.id) as total_accounts,
                COUNT(cm.id) as total_slots,
                SUM(CASE WHEN cm.status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN cm.status = 'occupied' THEN 1 ELSE 0 END) as occupied,
                SUM(CASE WHEN cm.status = 'expired' THEN 1 ELSE 0 END) as expired
            FROM chatgpt_accounts ca
            LEFT JOIN chatgpt_members cm ON cm.account_id = ca.id
            WHERE ca.is_active = 1
        ")->fetch(PDO::FETCH_ASSOC);

        $this->view('admin/chatgpt', [
            'pageTitle' => 'ChatGPT Management',
            'accounts' => $accounts,
            'allMembers' => $allMembers,
            'stats' => $stats,
        ], 'admin');
    }

    /**
     * Create new ChatGPT head account with member slots
     */
    public function storeAccount(): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'head_email' => 'required|email|max:100',
            'head_password' => 'required|max:255',
            'workspace_name' => 'required|max:100',
            'max_members' => 'required|numeric|min_value:1|max_value:10',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/chatgpt');
            return;
        }

        $db = Model::getConnection();
        $maxMembers = (int) $this->input('max_members', 4);

        // Create account
        $stmt = $db->prepare("INSERT INTO chatgpt_accounts (head_email, head_password, workspace_name, max_members, notes, is_active, created_at, updated_at) VALUES (:head_email, :head_password, :workspace_name, :max_members, :notes, 1, NOW(), NOW())");
        $stmt->execute([
            ':head_email' => trim($this->input('head_email')),
            ':head_password' => Helper::encrypt(trim($this->input('head_password'))),
            ':workspace_name' => trim($this->input('workspace_name')),
            ':max_members' => $maxMembers,
            ':notes' => trim($this->input('notes', '')),
        ]);

        $accountId = $db->lastInsertId();

        // Create empty member slots
        $stmt = $db->prepare("INSERT INTO chatgpt_members (account_id, slot_number, status, created_at, updated_at) VALUES (:account_id, :slot_number, 'available', NOW(), NOW())");
        for ($i = 1; $i <= $maxMembers; $i++) {
            $stmt->execute([':account_id' => $accountId, ':slot_number' => $i]);
        }

        Helper::logActivity('chatgpt_account_created', "ChatGPT account created: " . trim($this->input('workspace_name')));
        Session::flash('success', 'Akun ChatGPT berhasil ditambahkan dengan ' . $maxMembers . ' slot member.');
        $this->redirect('/admin/chatgpt');
    }

    /**
     * Update ChatGPT account
     */
    public function updateAccount(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $updateFields = [
            ':head_email' => trim($this->input('head_email')),
            ':workspace_name' => trim($this->input('workspace_name')),
            ':notes' => trim($this->input('notes', '')),
            ':is_active' => $this->input('is_active') ? 1 : 0,
            ':id' => $id,
        ];

        $sql = "UPDATE chatgpt_accounts SET head_email = :head_email, workspace_name = :workspace_name, notes = :notes, is_active = :is_active, updated_at = NOW()";

        $newPassword = trim($this->input('head_password', ''));
        if ($newPassword) {
            $sql .= ", head_password = :head_password";
            $updateFields[':head_password'] = Helper::encrypt($newPassword);
        }

        $sql .= " WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute($updateFields);

        Helper::logActivity('chatgpt_account_updated', "ChatGPT account #{$id} updated");
        Session::flash('success', 'Akun ChatGPT berhasil diupdate.');
        $this->redirect('/admin/chatgpt');
    }

    /**
     * Assign member to a ChatGPT slot
     */
    public function assignMember(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        // Verify slot exists and is available/expired
        $stmt = $db->prepare("SELECT * FROM chatgpt_members WHERE id = :id AND status IN ('available', 'expired') LIMIT 1");
        $stmt->execute([':id' => $id]);
        $slot = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$slot) {
            Session::flash('error', 'Slot tidak tersedia.');
            $this->redirect('/admin/chatgpt');
            return;
        }

        $customerEmail = trim($this->input('customer_email', ''));
        $expiredDate = trim($this->input('expired_date', ''));
        $customerId = $this->input('customer_id') ? (int) $this->input('customer_id') : null;
        $orderId = $this->input('order_id') ? (int) $this->input('order_id') : null;

        $stmt = $db->prepare("
            UPDATE chatgpt_members SET 
                customer_id = :customer_id, customer_email = :customer_email,
                order_id = :order_id, invite_date = CURDATE(),
                expired_date = :expired_date, status = 'occupied',
                notes = :notes, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':customer_id' => $customerId,
            ':customer_email' => $customerEmail,
            ':order_id' => $orderId,
            ':expired_date' => $expiredDate ?: null,
            ':notes' => trim($this->input('notes', '')),
            ':id' => $id,
        ]);

        Helper::logActivity('chatgpt_member_assigned', "ChatGPT slot #{$id} assigned to: {$customerEmail}");
        Session::flash('success', "Member berhasil di-assign: {$customerEmail}");
        $this->redirect('/admin/chatgpt');
    }

    /**
     * Release a ChatGPT member slot
     */
    public function releaseMember(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("
            UPDATE chatgpt_members SET 
                customer_id = NULL, customer_email = NULL,
                order_id = NULL, invite_date = NULL,
                expired_date = NULL, status = 'available',
                notes = NULL, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);

        Helper::logActivity('chatgpt_member_released', "ChatGPT slot #{$id} released");
        Session::flash('success', 'Slot member berhasil dirilis.');
        $this->redirect('/admin/chatgpt');
    }
}
