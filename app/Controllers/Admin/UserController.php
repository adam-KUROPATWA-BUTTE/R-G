<?php
namespace Controllers\Admin;

use Controllers\Controller;
use Models\User;

/**
 * Admin User Controller
 * Handles user management in admin panel
 */
class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->userModel = new User();
    }

    /**
     * Display all users
     */
    public function index(): void
    {
        $users = $this->userModel->getAll();
        
        $this->view('admin.users.index', [
            'users' => $users
        ]);
    }
}
