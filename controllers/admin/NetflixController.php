<?php
/**
 * Admin NetflixController - RZDK Store
 * 
 * Manages Netflix head accounts and their profile slots.
 * Each account has up to 5 profiles assignable to customers.
 */

class NetflixController extends Controller
{
    /**
     * Netflix management dashboard
     */
    public function index(): void
    {
        $db = Model::getConnection();

        // Get all Netflix accounts with slot stats
        $accounts = $db->query("
            SELECT na.*,
                   COUNT(ns.id) as total_slots,
                   SUM(CASE WHEN ns.status = 'available' THEN 1 ELSE 0 END) as available_slots,
                   SUM(CASE WHEN ns.status = 'occupied' THEN 1 ELSE 0 END) as occupied_slots,
                   SUM(CASE WHEN ns.status = 'expired' THEN 1 ELSE 0 END) as expired_slots
            FROM netflix_accounts na
            LEFT JOIN netflix_slots ns ON ns.account_id = na.id
            GROUP BY na.id
            ORDER BY na.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Get all slots with details for the overview
        $allSlots = $db->query("
            SELECT ns.*, na.email as account_email, u.username as customer_username
            FROM netflix_slots ns
            JOIN netflix_accounts na ON na.id = ns.account_id
            LEFT JOIN users u ON u.id = ns.customer_id
            WHERE ns.status IN ('occupied', 'expired')
            ORDER BY ns.expired_date ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Summary stats
        $stats = $db->query("
            SELECT 
                COUNT(DISTINCT na.id) as total_accounts,
                COUNT(ns.id) as total_slots,
                SUM(CASE WHEN ns.status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN ns.status = 'occupied' THEN 1 ELSE 0 END) as occupied,
                SUM(CASE WHEN ns.status = 'expired' THEN 1 ELSE 0 END) as expired
            FROM netflix_accounts na
            LEFT JOIN netflix_slots ns ON ns.account_id = na.id
            WHERE na.is_active = 1
        ")->fetch(PDO::FETCH_ASSOC);

        $this->view('admin/netflix', [
            'pageTitle' => 'Netflix Management',
            'accounts' => $accounts,
            'allSlots' => $allSlots,
            'stats' => $stats,
        ], 'admin');
    }

    /**
     * Create new Netflix account with slots
     */
    public function storeAccount(): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'email' => 'required|email|max:100',
            'password' => 'required|max:255',
            'plan' => 'required|in:basic,standard,premium',
            'max_profiles' => 'required|numeric|min_value:1|max_value:10',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/netflix');
            return;
        }

        $db = Model::getConnection();
        $maxProfiles = (int) $this->input('max_profiles', 5);

        // Create account
        $stmt = $db->prepare("INSERT INTO netflix_accounts (email, password, plan, max_profiles, notes, is_active, created_at, updated_at) VALUES (:email, :password, :plan, :max_profiles, :notes, 1, NOW(), NOW())");
        $stmt->execute([
            ':email' => trim($this->input('email')),
            ':password' => Helper::encrypt(trim($this->input('password'))),
            ':plan' => $this->input('plan'),
            ':max_profiles' => $maxProfiles,
            ':notes' => trim($this->input('notes', '')),
        ]);

        $accountId = $db->lastInsertId();

        // Create empty slots
        $stmt = $db->prepare("INSERT INTO netflix_slots (account_id, profile_name, status, created_at, updated_at) VALUES (:account_id, :profile_name, 'available', NOW(), NOW())");
        for ($i = 1; $i <= $maxProfiles; $i++) {
            $stmt->execute([
                ':account_id' => $accountId,
                ':profile_name' => 'Profile ' . $i,
            ]);
        }

        Helper::logActivity('netflix_account_created', "Netflix account created: " . trim($this->input('email')));
        Session::flash('success', 'Akun Netflix berhasil ditambahkan dengan ' . $maxProfiles . ' slot.');
        $this->redirect('/admin/netflix');
    }

    /**
     * Update Netflix account
     */
    public function updateAccount(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $updateFields = [
            ':email' => trim($this->input('email')),
            ':plan' => $this->input('plan'),
            ':notes' => trim($this->input('notes', '')),
            ':is_active' => $this->input('is_active') ? 1 : 0,
            ':id' => $id,
        ];

        $sql = "UPDATE netflix_accounts SET email = :email, plan = :plan, notes = :notes, is_active = :is_active, updated_at = NOW()";

        // Only update password if provided
        $newPassword = trim($this->input('password', ''));
        if ($newPassword) {
            $sql .= ", password = :password";
            $updateFields[':password'] = Helper::encrypt($newPassword);
        }

        $sql .= " WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute($updateFields);

        Helper::logActivity('netflix_account_updated', "Netflix account #{$id} updated");
        Session::flash('success', 'Akun Netflix berhasil diupdate.');
        $this->redirect('/admin/netflix');
    }

    /**
     * Assign customer to a Netflix slot
     */
    public function assignSlot(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        // Verify slot exists and is available
        $stmt = $db->prepare("SELECT * FROM netflix_slots WHERE id = :id AND status IN ('available', 'expired') LIMIT 1");
        $stmt->execute([':id' => $id]);
        $slot = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$slot) {
            Session::flash('error', 'Slot tidak tersedia.');
            $this->redirect('/admin/netflix');
            return;
        }

        $customerName = trim($this->input('customer_name', ''));
        $customerContact = trim($this->input('customer_contact', ''));
        $profileName = trim($this->input('profile_name', ''));
        $profilePin = trim($this->input('profile_pin', ''));
        $deviceType = $this->input('device_type', null);
        $expiredDate = trim($this->input('expired_date', ''));
        $customerId = $this->input('customer_id') ? (int) $this->input('customer_id') : null;
        $orderId = $this->input('order_id') ? (int) $this->input('order_id') : null;

        $stmt = $db->prepare("
            UPDATE netflix_slots SET 
                profile_name = :profile_name, profile_pin = :profile_pin,
                customer_id = :customer_id, customer_name = :customer_name,
                customer_contact = :customer_contact, order_id = :order_id,
                device_type = :device_type, order_date = CURDATE(),
                expired_date = :expired_date, status = 'occupied',
                notes = :notes, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':profile_name' => $profileName ?: $slot['profile_name'],
            ':profile_pin' => $profilePin ?: null,
            ':customer_id' => $customerId,
            ':customer_name' => $customerName,
            ':customer_contact' => $customerContact,
            ':order_id' => $orderId,
            ':device_type' => $deviceType,
            ':expired_date' => $expiredDate ?: null,
            ':notes' => trim($this->input('notes', '')),
            ':id' => $id,
        ]);

        Helper::logActivity('netflix_slot_assigned', "Netflix slot #{$id} assigned to: {$customerName}");
        Session::flash('success', "Slot berhasil di-assign ke {$customerName}.");
        $this->redirect('/admin/netflix');
    }

    /**
     * Release a Netflix slot (make available again)
     */
    public function releaseSlot(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();

        $stmt = $db->prepare("
            UPDATE netflix_slots SET 
                customer_id = NULL, customer_name = NULL, customer_contact = NULL,
                order_id = NULL, device_type = NULL, order_date = NULL,
                expired_date = NULL, profile_pin = NULL, status = 'available',
                notes = NULL, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);

        Helper::logActivity('netflix_slot_released', "Netflix slot #{$id} released");
        Session::flash('success', 'Slot berhasil dirilis.');
        $this->redirect('/admin/netflix');
    }
}
