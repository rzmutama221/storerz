<?php
/**
 * Middleware - Route Guard & Access Control for RZDK Store
 * 
 * Provides middleware functions that can be applied to routes:
 * - auth: Requires user to be logged in
 * - guest: Requires user to NOT be logged in
 * - admin: Requires user to be admin
 * - customer: Requires user to be customer
 * - verified: Requires user's email to be verified
 * 
 * Usage in routes:
 *   $router->get('/admin/dashboard', 'admin/DashboardController', 'index', ['auth', 'admin']);
 *   $router->get('/login', 'AuthController', 'showLogin', ['guest']);
 */

class Middleware
{
    /**
     * Handle a middleware check
     * Returns true if passed, performs redirect and returns false if blocked
     */
    public static function handle(string $middleware): bool
    {
        return match ($middleware) {
            'auth' => self::auth(),
            'guest' => self::guest(),
            'admin' => self::admin(),
            'customer' => self::customer(),
            'verified' => self::verified(),
            default => true,
        };
    }

    /**
     * Require authentication (must be logged in)
     */
    private static function auth(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            header('Location: /login');
            exit;
        }
        return true;
    }

    /**
     * Require guest (must NOT be logged in)
     * Redirects authenticated users to their dashboard
     */
    private static function guest(): bool
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard');
            } else {
                header('Location: /dashboard');
            }
            exit;
        }
        return true;
    }

    /**
     * Require admin role
     */
    private static function admin(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            header('Location: /login');
            exit;
        }

        if (!Auth::isAdmin()) {
            Session::flash('error', 'Anda tidak memiliki akses ke halaman ini.');
            header('Location: /dashboard');
            exit;
        }

        return true;
    }

    /**
     * Require customer role
     */
    private static function customer(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            header('Location: /login');
            exit;
        }

        if (Auth::isAdmin()) {
            // Admin trying to access customer panel, redirect to admin
            header('Location: /admin/dashboard');
            exit;
        }

        return true;
    }

    /**
     * Require email verification
     */
    private static function verified(): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu.');
            header('Location: /login');
            exit;
        }

        $user = Auth::user();
        if (!$user) {
            header('Location: /login');
            exit;
        }

        // Check verified status from database (session might be stale)
        $db = Model::getConnection();
        $stmt = $db->prepare("SELECT is_verified FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $user['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || !$result['is_verified']) {
            Session::flash('warning', 'Silakan verifikasi email Anda terlebih dahulu.');
            header('Location: /verify-notice');
            exit;
        }

        return true;
    }

    /**
     * Rate limiting check (generic)
     * Uses session-based tracking for simplicity on shared hosting
     */
    public static function rateLimit(string $key, int $maxAttempts = 10, int $windowSeconds = 60): bool
    {
        Session::start();
        $rateLimitKey = 'rate_limit_' . $key;
        $now = time();

        if (!isset($_SESSION[$rateLimitKey])) {
            $_SESSION[$rateLimitKey] = [];
        }

        // Remove expired entries
        $_SESSION[$rateLimitKey] = array_filter(
            $_SESSION[$rateLimitKey],
            fn($timestamp) => ($now - $timestamp) < $windowSeconds
        );

        // Check if limit exceeded
        if (count($_SESSION[$rateLimitKey]) >= $maxAttempts) {
            return false;
        }

        // Record this attempt
        $_SESSION[$rateLimitKey][] = $now;
        return true;
    }
}
