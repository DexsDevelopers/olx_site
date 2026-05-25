<?php
/**
 * Configurações do Sistema
 * Painel Admin - Produtos Bianca Moraes
 */

// Configurações de Banco de Dados (Hostinger)
// Credenciais atualizadas após instalação bem-sucedida
define('DB_HOST', 'localhost');
define('DB_NAME', 'u853242961_teste_site');
define('DB_USER', 'u853242961_usuario2');
define('DB_PASS', 'Lucastav8012@');
define('DB_CHARSET', 'utf8mb4');

// Configurações do Sistema
define('SITE_NAME', 'Bianca Moraes');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@biancamoraes.com.br');

// Configurações de Sessão
define('SESSION_NAME', 'BIANCA_ADMIN');
define('SESSION_LIFETIME', 3600); // 1 hora

// Configurações de Upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Autoload simples (se necessário)
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/includes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

