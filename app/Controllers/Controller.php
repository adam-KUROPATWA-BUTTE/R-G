<?php
namespace Controllers;

/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
class Controller
{
    protected string $basePath = '';

    public function __construct()
    {
        // Compute base path for subdirectory deployments
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        $this->basePath = $scriptDir === '/' ? '' : rtrim($scriptDir, '/');
    }

    /**
     * Render a view
     */
    protected function view(string $view, array $data = []): void
    {
        // Extract data array to variables
        extract($data);
        
        // Set base_path for views
        $base_path = $this->basePath;
        
        // Build view path
        $viewPath = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        require $viewPath;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        // Add base path if URL is relative
        if (strpos($url, 'http') !== 0 && strpos($url, '/') === 0) {
            $url = $this->basePath . $url;
        }
        
        header("Location: {$url}");
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get current user from session
     */
    protected function currentUser(): ?array
    {
        if (function_exists('current_user')) {
            return current_user();
        }
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth(): void
    {
        if (!$this->currentUser()) {
            $this->redirect('/login');
        }
    }

    /**
     * Check if user is admin
     */
    protected function requireAdmin(): void
    {
        $user = $this->currentUser();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die('Access denied');
        }
    }

    /**
     * Get POST data
     */
    protected function post(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        if (function_exists('csrf_validate')) {
            csrf_validate();
            return true;
        }
        return true;
    }
}
