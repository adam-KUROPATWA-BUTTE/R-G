<?php

namespace App\Core;

/**
 * Base Controller class - provides common controller functionality
 */
abstract class Controller {
    protected $view;
    
    public function __construct() {
        $this->view = new View();
    }
    
    /**
     * Render a view
     */
    protected function render(string $viewPath, array $data = []): void {
        $this->view->render($viewPath, $data);
    }
    
    /**
     * Redirect to a URL
     */
    protected function redirect(string $url, int $statusCode = 302): void {
        header("Location: $url", true, $statusCode);
        exit;
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get request input
     */
    protected function input(string $key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Get all request inputs
     */
    protected function inputs(): array {
        return array_merge($_GET, $_POST);
    }
    
    /**
     * Require user to be logged in
     */
    protected function requireAuth(): void {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login.php');
        }
    }
    
    /**
     * Require user to be admin
     */
    protected function requireAdmin(): void {
        $user = $this->currentUser();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            exit('Accès refusé');
        }
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool {
        return \current_user() !== null;
    }
    
    /**
     * Get current user
     */
    protected function currentUser(): ?array {
        return \current_user();
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCSRF(): void {
        if (!csrf_verify($_POST['csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF token invalide');
        }
    }
}
