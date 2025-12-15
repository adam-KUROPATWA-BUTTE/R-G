<?php
namespace Controllers;

/**
 * Home Controller
 * Handles the home page display
 */
class HomeController extends Controller
{
    /**
     * Display home page
     */
    public function index(): void
    {
        $this->view('home.index');
    }
}
