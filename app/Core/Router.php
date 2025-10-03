<?php

namespace App\Core;

/**
 * Router - Gestion du routage des URLs
 */
class Router {
    protected $routes = [];

    /**
     * Enregistrer une route GET
     */
    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    /**
     * Enregistrer une route POST
     */
    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    /**
     * Dispatcher la requête vers le bon contrôleur
     */
    public function dispatch($uri, $method) {
        $uri = trim($uri, '/');
        if ($uri === '') $uri = '/';
        
        if (isset($this->routes[$method][$uri])) {
            return $this->executeController($this->routes[$method][$uri]);
        }

        // Route dynamique produits
        if (preg_match('#^product/(\d+)$#', $uri, $matches)) {
            return $this->executeController('ProductController@show', ['id' => $matches[1]]);
        }

        return $this->handleNotFound();
    }

    protected function executeController($controller, $params = []) {
        list($controllerName, $action) = explode('@', $controller);
        $controllerClass = "App\\Controllers\\$controllerName";
        
        if (!class_exists($controllerClass)) {
            return $this->handleNotFound();
        }
        
        $instance = new $controllerClass();
        return call_user_func_array([$instance, $action], $params);
    }

    protected function handleNotFound() {
        http_response_code(404);
        echo "404 - Page non trouvée";
    }
}