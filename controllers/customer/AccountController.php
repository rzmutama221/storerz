<?php
/**
 * Customer AccountController - RZDK Store
 * 
 * Manages: Active accounts/subscriptions, profile settings, password change.
 */

class AccountController extends Controller
{
    /**
     * List all active accounts (completed orders still within active_until)
     */
    public function index(): void
    {
        $db = Model::getConnection();
        $userId = Auth::id();

        // Get all active (non-expired) completed orders
        $activeAccounts = $db->prepare("
            SELECT o.*, p.name as product_name, p.logo_path, p.slug as product_slug,
                   pv.name as variant_name, pv.duration_days, pv.type as variant_type,
                   pv.platform, pv.warranty_days
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.user_id = :user_id AND o.status = 'completed' AND o.active_until >= NOW()
            ORDER BY o.active_until ASC
        ");
        $activeAccounts->execute([':user_id' => $userId]);
        $activeAccounts = $activeAccounts->fetchAll(PDO::FETCH_ASSOC);

        // Decrypt fulfillment data for each
        foreach ($activeAccounts as &$account) {
            if ($account['fulfillment_data']) {
                $account['decrypted_data'] = Helper::decrypt($account['fulfillment_data']);
            } else {
                $account['decrypted_data'] = null;
            }
            // Calculate days remaining
            $account['days_remaining'] = max(0, (int) ceil((strtotime($account['active_until']) - time()) / 86400));
        }
        unset($account);

        // Get expired accounts (last 10)
        $expiredAccounts = $db->prepare("
            SELECT o.id, o.order_number, o.active_until, o.completed_at,
                   p.name as product_name, p.slug as product_slug, pv.name as variant_name
            FROM orders o
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE o.user_id = :user_id AND o.status = 'completed' AND o.active_until < NOW()
            ORDER BY o.active_until DESC
            LIMIT 10
        ");
        $expiredAccounts->execute([':user_id' => $userId]);
        $expiredAccounts = $expiredAccounts->fetchAll(PDO::FETCH_ASSOC);

        $this->view('customer/accounts', [
            'pageTitle' => 'Akun Aktif',
            'activeAccounts' => $activeAccounts,
            'expiredAccounts' => $expiredAccounts,
        ], 'customer');
    }

    /**
     * Show profile page
     */
    public function profile(): void
    {
        $db = Model::getConnection();
        $userId = Auth::id();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('customer/profile', [
            'pageTitle' => 'Profil Saya',
            'user' => $user,
        ], 'customer');
    }

    /**
     * Update profile (username, full_name, phone)
     */
    public function updateProfile(): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $userId = Auth::id();

        $fullName = trim($this->input('full_name', ''));
        $phone = trim($this->input('phone', ''));

        $validator = new Validator($_POST);
        $validator->rules([
            'full_name' => 'max:100',
            'phone' => 'max:20',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/dashboard/profile');
            return;
        }

        $stmt = $db->prepare("UPDATE users SET full_name = :full_name, phone = :phone, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':full_name' => $fullName ?: null,
            ':phone' => $phone ?: null,
            ':id' => $userId,
        ]);

        // Update session
        $sessionUser = Auth::user();
        $sessionUser['full_name'] = $fullName;
        Session::set('user', $sessionUser);

        Session::flash('success', 'Profil berhasil diupdate.');
        $this->redirect('/dashboard/profile');
    }

    /**
     * Change password
     */
    public function changePassword(): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $userId = Auth::id();

        $currentPassword = $this->input('current_password', '');
        $newPassword = $this->input('new_password', '');
        $confirmPassword = $this->input('confirm_password', '');

        $validator = new Validator($_POST);
        $validator->rules([
            'current_password' => 'required',
            'new_password' => 'required|min:8|max:100',
            'confirm_password' => 'required|same:new_password',
        ]);
        $validator->messages([
            'confirm_password.same' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/dashboard/profile');
            return;
        }

        // Verify current password
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!password_verify($currentPassword, $user['password_hash'])) {
            Session::flash('error', 'Password lama tidak benar.');
            $this->redirect('/dashboard/profile');
            return;
        }

        // Update password
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':hash' => Auth::hashPassword($newPassword),
            ':id' => $userId,
        ]);

        Helper::logActivity('password_changed', 'Customer changed password', $userId);
        Session::flash('success', 'Password berhasil diubah.');
        $this->redirect('/dashboard/profile');
    }
}
