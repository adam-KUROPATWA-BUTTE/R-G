<?php

namespace App\Core;

/**
 * View class - handles view rendering
 */
class View {
    protected $viewsPath;
    protected $layoutsPath;
    
    public function __construct() {
        $this->viewsPath = __DIR__ . '/../Views/';
        $this->layoutsPath = $this->viewsPath . 'layouts/';
    }
    
    /**
     * Render a view with optional layout
     */
    public function render(string $viewPath, array $data = [], ?string $layout = 'main'): void {
        // Extract data to variables
        extract($data);
        
        // Start output buffering for content
        ob_start();
        
        // Include the view file
        $viewFile = $this->viewsPath . $viewPath . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: $viewPath");
        }
        
        // Get the rendered content
        $content = ob_get_clean();
        
        // If layout is specified, wrap content in layout
        if ($layout !== null) {
            $layoutFile = $this->layoutsPath . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }
    
    /**
     * Render a partial view (no layout)
     */
    public function partial(string $partialPath, array $data = []): void {
        $this->render($partialPath, $data, null);
    }
    
    /**
     * Include a view and return its output
     */
    public function include(string $viewPath, array $data = []): string {
        extract($data);
        ob_start();
        $viewFile = $this->viewsPath . $viewPath . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        }
        return ob_get_clean();
    }
}
