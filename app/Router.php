<?php
/**
 * Simple Router for R&G Application
 * Handles URL routing to controllers and methods
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
     * Add a GET route
     */
    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add a route for any method
     */
    public function any(string $path, string $handler): void
    {
        $this->addRoute('ANY', $path, $handler);
    }

    /**
     * Add a route
     */
    private function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        $requestUri = strtok($requestUri, '?');
        
        // Remove base path
        if ($this->basePath !== '' && strpos($requestUri, $this->basePath) === 0) {
            $requestUri = substr($requestUri, strlen($this->basePath));
        }
        
        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri !== '/') {
            $requestUri = rtrim($requestUri, '/');
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== 'ANY' && $route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = $this->convertPathToRegex($route['path']);
            
            if (preg_match($pattern, $requestUri, $matches)) {
                // Extract parameters
                array_shift($matches); // Remove full match
                $this->callHandler($route['handler'], $matches);
                return;
            }
        }

        // No route found - 404
        $this->notFound();
    }

    /**
     * Convert route path to regex pattern
     */
    private function convertPathToRegex(string $path): string
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Call the route handler
     */
    private function callHandler(string $handler, array $params = []): void
    {
        // Parse handler (ControllerName@methodName)
        if (strpos($handler, '@') === false) {
            throw new \RuntimeException("Invalid handler format: {$handler}");
        }

        list($controllerName, $methodName) = explode('@', $handler, 2);
        
        // Build full controller class name
        $controllerClass = "Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller not found: {$controllerClass}");
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $methodName)) {
            throw new \RuntimeException("Method not found: {$controllerClass}::{$methodName}");
        }

        // Call controller method with parameters
        call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * Handle 404 - Not Found
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The page you are looking for does not exist.</p>";
        exit;
    }
}
