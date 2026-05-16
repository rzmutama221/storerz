<?php
/**
 * Router - Simple URL Router for RZDK Store
 * 
 * Handles URL parsing and route matching.
 * Supports: static routes, parameterized routes, HTTP method filtering.
 * 
 * Usage:
 *   $router = new Router();
 *   $router->get('/login', 'AuthController', 'showLogin');
 *   $router->post('/login', 'AuthController', 'processLogin');
 *   $router->get('/admin/products/{id}', 'admin/ProductController', 'show');
 *   $router->dispatch();
 */

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Register a GET route
     */
    public function get(string $path, string $controller, string $method, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $controller, $method, $middleware);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, string $controller, string $method, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $controller, $method, $middleware);
    }

    /**
     * Register route for both GET and POST
     */
    public function any(string $path, string $controller, string $method, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $controller, $method, $middleware);
        $this->addRoute('POST', $path, $controller, $method, $middleware);
    }

    /**
     * Add a route to the collection
     */
    private function addRoute(string $httpMethod, string $path, string $controller, string $method, array $middleware): void
    {
        $this->routes[] = [
            'http_method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'method' => $method,
            'middleware' => $middleware,
        ];
    }

    /**
     * Dispatch the current request to matching route
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $this->getRequestUri();

        foreach ($this->routes as $route) {
            if ($route['http_method'] !== $requestMethod) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $requestUri);
            if ($params !== false) {
                // Run middleware
                foreach ($route['middleware'] as $middlewareName) {
                    $middlewareResult = Middleware::handle($middlewareName);
                    if ($middlewareResult !== true) {
                        return; // Middleware blocked the request (redirect already done)
                    }
                }

                // Resolve controller
                $this->callController($route['controller'], $route['method'], $params);
                return;
            }
        }

        // No route matched - 404
        $this->notFound();
    }

    /**
     * Get the clean request URI
     */
    private function getRequestUri(): string
    {
        $uri = $_GET['url'] ?? '';
        $uri = '/' . trim($uri, '/');
        return $uri;
    }

    /**
     * Match a route pattern against the request URI
     * Returns parameters array on match, false on no match
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert pattern to regex
        // e.g., /admin/products/{id} -> /^\/admin\/products\/([^\/]+)$/
        $patternParts = explode('/', trim($pattern, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($patternParts) !== count($uriParts)) {
            return false;
        }

        $params = [];

        for ($i = 0; $i < count($patternParts); $i++) {
            if (preg_match('/^\{(\w+)\}$/', $patternParts[$i], $matches)) {
                // This is a parameter placeholder
                $params[$matches[1]] = urldecode($uriParts[$i]);
            } elseif ($patternParts[$i] !== $uriParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    /**
     * Instantiate controller and call method
     */
    private function callController(string $controllerPath, string $method, array $params): void
    {
        // Determine file path: 'admin/ProductController' -> 'controllers/admin/ProductController.php'
        $filePath = BASE_PATH . '/controllers/' . $controllerPath . '.php';

        if (!file_exists($filePath)) {
            $this->notFound();
            return;
        }

        require_once $filePath;

        // Get class name (last part of path)
        $parts = explode('/', $controllerPath);
        $className = end($parts);

        if (!class_exists($className)) {
            $this->notFound();
            return;
        }

        $controller = new $className();

        if (!method_exists($controller, $method)) {
            $this->notFound();
            return;
        }

        // Call the method with params
        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Show 404 page
     */
    private function notFound(): void
    {
        http_response_code(404);
        if (file_exists(BASE_PATH . '/views/errors/404.php')) {
            require_once BASE_PATH . '/views/errors/404.php';
        } else {
            echo '<h1>404 - Halaman Tidak Ditemukan</h1>';
            echo '<p>Maaf, halaman yang Anda cari tidak tersedia.</p>';
            echo '<a href="/">Kembali ke Beranda</a>';
        }
        exit;
    }
}
