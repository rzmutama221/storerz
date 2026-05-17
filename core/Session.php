<?php
/**
 * Session - Secure Session Management for RZDK Store
 * 
 * Configures PHP sessions with security best practices:
 * - HttpOnly cookies
 * - Secure flag (HTTPS)
 * - Strict mode
 * - Inactivity timeout (2 hours)
 */

class Session
{
    private static bool $started = false;
    private static int $timeout = 7200; // 2 hours in seconds

    /**
     * Start session with secure configuration
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        // Configure session before starting
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        // Set secure flag if HTTPS
        if (self::isHttps()) {
            ini_set('session.cookie_secure', '1');
        }

        // Set session lifetime
        ini_set('session.gc_maxlifetime', (string) self::$timeout);

        // Session name
        session_name('RZDK_SESSION');

        session_start();
        self::$started = true;

        // Check for session timeout
        self::checkTimeout();
    }

    /**
     * Check if session has timed out due to inactivity
     */
    private static function checkTimeout(): void
    {
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            if ($elapsed > self::$timeout) {
                // Session expired
                self::destroy();
                return;
            }
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Destroy the session completely
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        self::$started = false;
    }

    /**
     * Set a session value
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Set a flash message (available only for next request)
     */
    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Get and clear all flash messages
     */
    public static function getFlash(): array
    {
        self::start();
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Check if running over HTTPS
     */
    private static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        }
        if (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
            return true;
        }
        return false;
    }

    /**
     * Regenerate session ID (call after login)
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }
}
