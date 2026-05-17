<?php
/**
 * Auth - Authentication Handler for RZDK Store
 * 
 * Handles user authentication, registration verification,
 * password hashing, login attempts tracking, and session management.
 */

class Auth
{
    /**
     * Attempt to log in a user with username and password
     * Returns user data on success, null on failure
     */
    public static function attempt(string $username, string $password): ?array
    {
        $db = Model::getConnection();

        // Find user by username
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return null;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            self::incrementFailedAttempts($user['id']);
            return null;
        }

        // Check if account is verified
        if (!$user['is_verified']) {
            return null;
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            return null;
        }

        // Success - reset failed attempts and update last login
        self::resetFailedAttempts($user['id']);
        self::updateLastLogin($user['id']);

        // Remove sensitive data before returning
        unset($user['password_hash'], $user['verification_token'], $user['reset_token']);

        return $user;
    }

    /**
     * Log in a user (set session)
     */
    public static function login(array $user): void
    {
        Session::start();
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'full_name' => $user['full_name'],
            'status' => $user['status'],
        ];
        $_SESSION['logged_in_at'] = time();
    }

    /**
     * Log out the current user
     */
    public static function logout(): void
    {
        Session::start();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Check if a user is currently logged in
     */
    public static function check(): bool
    {
        Session::start();
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    /**
     * Get current authenticated user data from session
     */
    public static function user(): ?array
    {
        Session::start();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Check if current user has a specific role
     */
    public static function hasRole(string $role): bool
    {
        $user = self::user();
        return $user && $user['role'] === $role;
    }

    /**
     * Check if current user is admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }

    /**
     * Hash a password using bcrypt
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Generate a secure random token (for verification/reset)
     */
    public static function generateToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Increment failed login attempts and lock if threshold reached
     */
    private static function incrementFailedAttempts(int $userId): void
    {
        $db = Model::getConnection();
        $maxAttempts = 5;
        $lockoutMinutes = 15;

        $stmt = $db->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = :id");
        $stmt->execute([':id' => $userId]);

        // Check if we need to lock
        $stmt = $db->prepare("SELECT failed_login_attempts FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['failed_login_attempts'] >= $maxAttempts) {
            $lockUntil = date('Y-m-d H:i:s', strtotime("+{$lockoutMinutes} minutes"));
            $stmt = $db->prepare("UPDATE users SET locked_until = :locked_until WHERE id = :id");
            $stmt->execute([':locked_until' => $lockUntil, ':id' => $userId]);
        }
    }

    /**
     * Reset failed login attempts after successful login
     */
    private static function resetFailedAttempts(int $userId): void
    {
        $db = Model::getConnection();
        $stmt = $db->prepare("UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

    /**
     * Update last login timestamp
     */
    private static function updateLastLogin(int $userId): void
    {
        $db = Model::getConnection();
        $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmt->execute([':id' => $userId]);
    }

    /**
     * Check if an account is locked
     */
    public static function isLocked(string $username): bool
    {
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT locked_until FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['locked_until']) {
            return false;
        }

        return strtotime($user['locked_until']) > time();
    }

    /**
     * Check if an account is verified
     */
    public static function isVerified(string $username): ?bool
    {
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT is_verified FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        return (bool) $user['is_verified'];
    }

    /**
     * Get remaining lockout time in seconds
     */
    public static function getLockoutRemaining(string $username): int
    {
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT locked_until FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['locked_until']) {
            return 0;
        }

        $remaining = strtotime($user['locked_until']) - time();
        return max(0, $remaining);
    }
}
