<?php
namespace Controllers\Admin;

use Controllers\Controller;

/**
 * Admin Dashboard Controller
 * Handles the admin dashboard and main navigation
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
    }

    /**
     * Display admin dashboard
     */
    public function index(): void
    {
        $this->view('admin.dashboard');
    }
}
