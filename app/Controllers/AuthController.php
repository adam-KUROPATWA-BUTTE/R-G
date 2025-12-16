<?php
namespace Controllers;

/**
 * Authentication Controller
 * Handles user login, registration, and logout
 */
class AuthController extends Controller
{
    /**
     * Show and handle login
     */
    public function login(): void
    {
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if ($email && $password) {
                if (login_user($email, $password)) {
                    $this->redirect('/');
                    return;
                } else {
                    $error = 'Email ou mot de passe incorrect.';
                }
            } else {
                $error = 'Veuillez remplir tous les champs.';
            }
        }
        
        $this->view('auth.login', [
            'error' => $error,
            'email' => $_POST['email'] ?? ''
        ]);
    }
    
    /**
     * Show and handle registration
     */
    public function register(): void
    {
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            
            if (!$email || !$password) {
                $error = 'Veuillez remplir tous les champs obligatoires.';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                [$success, $message] = register_user($email, $password, $firstName, $lastName);
                if ($success) {
                    $success = $message;
                    // Optionally auto-login and redirect
                    // login_user($email, $password);
                    // $this->redirect('/');
                } else {
                    $error = $message;
                }
            }
        }
        
        $this->view('auth.register', [
            'error' => $error,
            'success' => $success,
            'email' => $_POST['email'] ?? '',
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? ''
        ]);
    }
    
    /**
     * Handle logout
     */
    public function logout(): void
    {
        // For POST requests with CSRF, logout
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrf();
            logout_user();
        }
        
        // Redirect to home
        $this->redirect('/');
    }
}
