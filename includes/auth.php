<?php
/**
 * Sistema de Autenticação
 */
require_once __DIR__ . '/../database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->ensureDefaultAdmin();
    }

    /**
     * Garante que exista um administrador padrão configurado no sistema
     */
    private function ensureDefaultAdmin() {
        if (!defined('DEFAULT_ADMIN_USERNAME') || !defined('DEFAULT_ADMIN_PASSWORD')) {
            return;
        }

        try {
            $defaultUser = DEFAULT_ADMIN_USERNAME;
            $defaultPass = DEFAULT_ADMIN_PASSWORD;
            $defaultEmail = defined('DEFAULT_ADMIN_EMAIL') ? DEFAULT_ADMIN_EMAIL : ($defaultUser . '@example.com');
            $defaultName = defined('DEFAULT_ADMIN_NAME') ? DEFAULT_ADMIN_NAME : $defaultUser;

            $admin = $this->db->fetchOne(
                "SELECT id, email, password FROM admins WHERE username = :username LIMIT 1",
                ['username' => $defaultUser]
            );

            if (!$admin) {
                $this->db->query(
                    "INSERT INTO admins (username, email, password, nome_completo, active) VALUES (:username, :email, :password, :nome, 1)",
                    [
                        'username' => $defaultUser,
                        'email' => $defaultEmail,
                        'password' => password_hash($defaultPass, PASSWORD_DEFAULT),
                        'nome' => $defaultName
                    ]
                );
                return;
            }

            $updates = [];
            $params = ['id' => $admin['id']];

            if ($admin['email'] !== $defaultEmail) {
                $updates[] = 'email = :email';
                $params['email'] = $defaultEmail;
            }

            if (!password_verify($defaultPass, $admin['password'])) {
                $updates[] = 'password = :password';
                $params['password'] = password_hash($defaultPass, PASSWORD_DEFAULT);
            }

            if ($updates) {
                $this->db->query(
                    'UPDATE admins SET ' . implode(', ', $updates) . ' WHERE id = :id',
                    $params
                );
            }
        } catch (Exception $e) {
            error_log('Falha ao garantir admin padrão: ' . $e->getMessage());
        }
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

