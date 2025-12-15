<?php
namespace Controllers;

use Models\User;
use Models\Order;

/**
 * User Controller
 * Handles user authentication and account management
 */
class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Display login page
     */
    public function login(): void
    {
        // If already logged in, redirect to home
        if ($this->currentUser()) {
            $this->redirect('/');
            return;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $email = trim($this->post('email') ?? '');
            $password = $this->post('password') ?? '';

            if ($email && $password) {
                $user = $this->userModel->authenticate($email, $password);
                if ($user) {
                    $_SESSION['user_id'] = (int)$user['id'];
                    $this->redirect('/');
                    return;
                } else {
                    $error = 'Email ou mot de passe incorrect.';
                }
            } else {
                $error = 'Veuillez remplir tous les champs.';
            }
        }

        $this->view('user.login', ['error' => $error]);
    }

    /**
     * Display registration page
     */
    public function register(): void
    {
        // If already logged in, redirect to home
        if ($this->currentUser()) {
            $this->redirect('/');
            return;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();

            $email = trim($this->post('email') ?? '');
            $password = $this->post('password') ?? '';
            $passwordConfirm = $this->post('password_confirm') ?? '';
            $fullName = trim($this->post('full_name') ?? '');

            if ($password !== $passwordConfirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                // Split full name into first and last
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                list($ok, $message) = $this->userModel->register($email, $password, $firstName, $lastName);
                
                if ($ok) {
                    $success = $message;
                } else {
                    $error = $message;
                }
            }
        }

        $this->view('user.register', [
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        unset($_SESSION['user_id']);
        session_destroy();
        
        $this->redirect('/');
    }

    /**
     * Display user account page
     */
    public function account(): void
    {
        $this->requireAuth();

        $user = $this->currentUser();
        
        // Get user's orders
        $orderModel = new Order();
        $orders = $orderModel->getByUser($user['id']);

        $this->view('user.account', [
            'user' => $user,
            'orders' => $orders
        ]);
    }
}
