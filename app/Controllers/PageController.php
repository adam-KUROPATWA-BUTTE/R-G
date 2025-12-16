<?php
namespace Controllers;

/**
 * Page Controller
 * Handles static pages and informational content
 */
class PageController extends Controller
{
    /**
     * Display about page
     */
    public function about(): void
    {
        $this->view('pages.about');
    }
    
    /**
     * Display contact page
     */
    public function contact(): void
    {
        $this->view('pages.contact');
    }
    
    /**
     * Display terms and conditions (CGV)
     */
    public function cgv(): void
    {
        $this->view('pages.cgv');
    }
    
    /**
     * Display legal notice
     */
    public function mentionsLegales(): void
    {
        $this->view('pages.mentions-legales');
    }
}
