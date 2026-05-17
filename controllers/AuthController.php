<?php
/**
 * AuthController - Authentication for RZDK Store
 * 
 * Handles: Login, Register, Email Verification, Password Reset, Logout
 */

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        $this->view('auth/login', [
            'pageTitle' => 'Login',
        ], 'auth');
    }

    /**
     * Process login attempt
     */
    public function processLogin(): void
    {
        if (!$this->validateCsrf()) return;

        $username = trim($this->input('username', ''));
        $password = $this->input('password', '');

        // Validate input
        $validator = new Validator($_POST);
        $validator->rules([
            'username' => 'required|min:3',
            'password' => 'required|min:8',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            Session::set('old_input', ['username' => $username]);
            $this->redirect('/login');
            return;
        }

        // Check if account is locked
        if (Auth::isLocked($username)) {
            $remaining = Auth::getLockoutRemaining($username);
            $minutes = ceil($remaining / 60);
            Session::flash('error', "Akun terkunci karena terlalu banyak percobaan gagal. Coba lagi dalam {$minutes} menit.");
            Session::set('old_input', ['username' => $username]);
            $this->redirect('/login');
            return;
        }

        // Check verification status
        $isVerified = Auth::isVerified($username);
        if ($isVerified === false) {
            Session::flash('warning', 'Akun Anda belum diverifikasi. Silakan cek email untuk link verifikasi.');
            Session::set('old_input', ['username' => $username]);
            $this->redirect('/login');
            return;
        }

        // Attempt login
        $user = Auth::attempt($username, $password);

        if (!$user) {
            Session::flash('error', 'Username atau password salah.');
            Session::set('old_input', ['username' => $username]);
            $this->redirect('/login');
            return;
        }

        // Check account status
        if ($user['status'] !== 'active') {
            Session::flash('error', 'Akun Anda telah dinonaktifkan. Hubungi admin untuk informasi lebih lanjut.');
            $this->redirect('/login');
            return;
        }

        // Login success
        Auth::login($user);
        Session::remove('old_input');

        Helper::logActivity('login', "User {$user['username']} logged in", $user['id']);

        // Redirect based on role
        if ($user['role'] === 'admin') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/dashboard');
        }
    }

    /**
     * Show register form
     */
    public function showRegister(): void
    {
        $this->view('auth/register', [
            'pageTitle' => 'Daftar Akun',
        ], 'auth');
    }

    /**
     * Process registration
     */
    public function processRegister(): void
    {
        if (!$this->validateCsrf()) return;

        $username = trim($this->input('username', ''));
        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');
        $passwordConfirm = $this->input('password_confirm', '');

        // Validate input
        $validator = new Validator($_POST);
        $validator->rules([
            'username' => 'required|min:3|max:50|alpha_dash|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|min:8|max:100',
            'password_confirm' => 'required|same:password',
        ]);
        $validator->messages([
            'username.unique' => 'Username sudah digunakan.',
            'email.unique' => 'Email sudah terdaftar.',
            'password_confirm.same' => 'Konfirmasi password tidak cocok.',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            Session::set('old_input', ['username' => $username, 'email' => $email]);
            $this->redirect('/register');
            return;
        }

        // Rate limiting
        if (!Middleware::rateLimit('register', 3, 300)) {
            Session::flash('error', 'Terlalu banyak percobaan. Silakan tunggu beberapa menit.');
            $this->redirect('/register');
            return;
        }

        // Generate verification token
        $verificationToken = Auth::generateToken();
        $tokenExpires = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Create user
        $db = Model::getConnection();
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role, is_verified, verification_token, verification_token_expires, status, created_at, updated_at) VALUES (:username, :email, :password_hash, 'customer', 0, :token, :token_expires, 'active', NOW(), NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => Auth::hashPassword($password),
            ':token' => $verificationToken,
            ':token_expires' => $tokenExpires,
        ]);

        // Send verification email
        $verifyUrl = Helper::url('verify?token=' . $verificationToken);
        $emailSent = Mailer::quickSend(
            $email,
            'Verifikasi Akun RZDK Store',
            'verification',
            [
                'username' => $username,
                'verify_url' => $verifyUrl,
                'expires_in' => '24 jam',
            ]
        );

        Helper::logActivity('register', "New user registered: {$username} ({$email})");

        Session::remove('old_input');

        if ($emailSent) {
            Session::flash('success', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun.');
        } else {
            Session::flash('warning', 'Registrasi berhasil, namun email verifikasi gagal terkirim. Silakan hubungi admin.');
        }

        $this->redirect('/verify-notice');
    }

    /**
     * Verify email via token
     */
    public function verifyEmail(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            Session::flash('error', 'Token verifikasi tidak valid.');
            $this->redirect('/login');
            return;
        }

        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT id, username, verification_token_expires FROM users WHERE verification_token = :token AND is_verified = 0 LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'Token verifikasi tidak valid atau akun sudah terverifikasi.');
            $this->redirect('/login');
            return;
        }

        // Check token expiry
        if ($user['verification_token_expires'] && strtotime($user['verification_token_expires']) < time()) {
            Session::flash('error', 'Token verifikasi sudah expired. Silakan daftar ulang atau hubungi admin.');
            $this->redirect('/login');
            return;
        }

        // Verify the account
        $stmt = $db->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, verification_token_expires = NULL, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);

        Helper::logActivity('email_verified', "User {$user['username']} verified email", $user['id']);

        Session::flash('success', 'Email berhasil diverifikasi! Silakan login.');
        $this->redirect('/login');
    }

    /**
     * Show verification notice page
     */
    public function verifyNotice(): void
    {
        $this->view('auth/verify-notice', [
            'pageTitle' => 'Verifikasi Email',
        ], 'auth');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', [
            'pageTitle' => 'Lupa Password',
        ], 'auth');
    }

    /**
     * Process forgot password (send reset email)
     */
    public function processForgotPassword(): void
    {
        if (!$this->validateCsrf()) return;

        $email = trim($this->input('email', ''));

        $validator = new Validator($_POST);
        $validator->rules([
            'email' => 'required|email',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/forgot-password');
            return;
        }

        // Rate limiting
        if (!Middleware::rateLimit('forgot_password', 3, 300)) {
            Session::flash('error', 'Terlalu banyak percobaan. Silakan tunggu beberapa menit.');
            $this->redirect('/forgot-password');
            return;
        }

        // Find user by email
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT id, username, email FROM users WHERE email = :email AND is_verified = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Always show success message (prevent email enumeration)
        if ($user) {
            // Generate reset token
            $resetToken = Auth::generateToken();
            $tokenExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $db->prepare("UPDATE users SET reset_token = :token, reset_token_expires = :expires, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                ':token' => $resetToken,
                ':expires' => $tokenExpires,
                ':id' => $user['id'],
            ]);

            // Send reset email
            $resetUrl = Helper::url('reset-password?token=' . $resetToken);
            Mailer::quickSend(
                $user['email'],
                'Reset Password - RZDK Store',
                'reset-password',
                [
                    'username' => $user['username'],
                    'reset_url' => $resetUrl,
                    'expires_in' => '1 jam',
                ]
            );

            Helper::logActivity('password_reset_requested', "Reset password requested for {$user['email']}", $user['id']);
        }

        Session::flash('success', 'Jika email terdaftar, link reset password telah dikirim. Silakan cek inbox atau folder spam Anda.');
        $this->redirect('/forgot-password');
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            Session::flash('error', 'Token reset tidak valid.');
            $this->redirect('/login');
            return;
        }

        // Validate token exists and not expired
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_token_expires > NOW() LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'Token reset tidak valid atau sudah expired. Silakan request ulang.');
            $this->redirect('/forgot-password');
            return;
        }

        $this->view('auth/reset-password', [
            'pageTitle' => 'Reset Password',
            'token' => $token,
        ], 'auth');
    }

    /**
     * Process reset password
     */
    public function processResetPassword(): void
    {
        if (!$this->validateCsrf()) return;

        $token = $this->input('token', '');
        $password = $this->input('password', '');
        $passwordConfirm = $this->input('password_confirm', '');

        $validator = new Validator($_POST);
        $validator->rules([
            'token' => 'required',
            'password' => 'required|min:8|max:100',
            'password_confirm' => 'required|same:password',
        ]);
        $validator->messages([
            'password_confirm.same' => 'Konfirmasi password tidak cocok.',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        // Find user by valid token
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT id, username FROM users WHERE reset_token = :token AND reset_token_expires > NOW() LIMIT 1");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            Session::flash('error', 'Token reset tidak valid atau sudah expired.');
            $this->redirect('/forgot-password');
            return;
        }

        // Update password and clear token
        $stmt = $db->prepare("UPDATE users SET password_hash = :password_hash, reset_token = NULL, reset_token_expires = NULL, failed_login_attempts = 0, locked_until = NULL, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':password_hash' => Auth::hashPassword($password),
            ':id' => $user['id'],
        ]);

        Helper::logActivity('password_reset', "Password reset successful for {$user['username']}", $user['id']);

        Session::flash('success', 'Password berhasil direset! Silakan login dengan password baru.');
        $this->redirect('/login');
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $user = Auth::user();
        if ($user) {
            Helper::logActivity('logout', "User {$user['username']} logged out", $user['id']);
        }

        Auth::logout();
        Session::flash('success', 'Anda telah berhasil logout.');
        $this->redirect('/login');
    }
}
