<?php
/**
 * Controller - Base Controller for RZDK Store
 * 
 * All controllers extend this class to gain access to:
 * - View rendering with layouts
 * - Flash messages
 * - Redirect helpers
 * - Input retrieval
 * - JSON responses
 */

class Controller
{
    /**
     * Data to pass to views
     */
    protected array $viewData = [];

    /**
     * Render a view within a layout
     * 
     * @param string $view  View path relative to views/ (e.g., 'admin/dashboard')
     * @param array  $data  Data to pass to the view
     * @param string $layout Layout name (e.g., 'admin', 'customer', 'landing', 'auth')
     */
    protected function view(string $view, array $data = [], string $layout = ''): void
    {
        // Merge data
        $data = array_merge($this->viewData, $data);

        // Extract data so variables are accessible in view
        extract($data);

        // Path to view file
        $viewPath = BASE_PATH . '/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        if ($layout) {
            // Render view into $content variable, then include layout
            ob_start();
            require $viewPath;
            $content = ob_get_clean();

            $layoutPath = BASE_PATH . '/views/layouts/' . $layout . '.php';
            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout not found: {$layout}");
            }
            require $layoutPath;
        } else {
            // Render view directly without layout
            require $viewPath;
        }
    }

    /**
     * Render view without layout (partial/component)
     */
    protected function partial(string $view, array $data = []): void
    {
        $data = array_merge($this->viewData, $data);
        extract($data);
        $viewPath = BASE_PATH . '/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        }
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Redirect back to previous page
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Set a flash message (stored in session, shown once)
     */
    protected function flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_messages'][] = [
            'type' => $type, // success, error, warning, info
            'message' => $message,
        ];
    }

    /**
     * Get and clear flash messages
     */
    protected function getFlash(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Get input value (POST or GET)
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Get all POST data
     */
    protected function allInput(): array
    {
        return $_POST;
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Send JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $this->input('_csrf_token');
        if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->flash('error', 'Sesi tidak valid. Silakan coba lagi.');
            $this->back();
            return false;
        }
        return true;
    }

    /**
     * Get currently authenticated user data from session
     */
    protected function user(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin(): bool
    {
        $user = $this->user();
        return $user && $user['role'] === 'admin';
    }

    /**
     * Set shared view data (accessible in all views rendered by this controller)
     */
    protected function share(string $key, mixed $value): void
    {
        $this->viewData[$key] = $value;
    }
}
