<?php
/**
 * Sistema de Autenticação
 */
require_once __DIR__ . '/../database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        $admin = $this->db->fetchOne(
            "SELECT * FROM admins WHERE (username = :user1 OR email = :user2) AND active = 1",
            ['user1' => $username, 'user2' => $username]
        );

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nome'] = $admin['nome_completo'] ?? $admin['username'];
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_login_time'] = time();

            // Atualizar último login
            $this->db->query(
                "UPDATE admins SET last_login = NOW() WHERE id = :id",
                ['id' => $admin['id']]
            );

            return true;
        }

        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
        session_start();
    }

    public function isLogged() {
        if (!isset($_SESSION['admin_logged']) || !$_SESSION['admin_logged']) {
            return false;
        }

        // Verificar timeout de sessão
        if (isset($_SESSION['admin_login_time']) && 
            (time() - $_SESSION['admin_login_time']) > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }

        // Renovar tempo de sessão
        $_SESSION['admin_login_time'] = time();
        return true;
    }

    public function requireLogin() {
        if (!$this->isLogged()) {
            header('Location: login.php');
            exit;
        }
    }

    public function getAdminId() {
        return $_SESSION['admin_id'] ?? null;
    }

    public function getAdminName() {
        return $_SESSION['admin_nome'] ?? 'Admin';
    }
}

