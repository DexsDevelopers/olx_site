<?php
/**
 * Página de Login - Painel Admin
 */
// Ativar exibição de erros temporariamente para debug
error_reporting(E_ALL);
ini_set('display_errors', 1); // Ativar para ver o erro
ini_set('log_errors', 1);

// Iniciar output buffering para evitar problemas com headers
ob_start();

try {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../database.php';
    require_once __DIR__ . '/../includes/auth.php';

    $auth = new Auth();
    $erro = '';

    // Se já estiver logado, redirecionar
    if ($auth->isLogged()) {
        ob_end_clean();
        header('Location: index.php');
        exit;
    }

    // Processar login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($auth->login($username, $password)) {
            ob_end_clean();
            header('Location: index.php');
            exit;
        } else {
            $erro = 'Usuário ou senha incorretos!';
        }
    }
} catch (Exception $e) {
    // Log do erro
    error_log("Erro em login.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Mostrar erro detalhado para debug
    $erro = 'Erro ao carregar o sistema: ' . htmlspecialchars($e->getMessage());
    $erro .= '<br><small>Arquivo: ' . htmlspecialchars($e->getFile()) . ' (linha ' . $e->getLine() . ')</small>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Admin | <?= defined('SITE_NAME') ? SITE_NAME : 'Admin' ?></title>
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .login-container {
            background: var(--bg-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.3s ease;
            border: 1px solid var(--border);
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: var(--text);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: var(--text-light);
            font-size: 0.875rem;
        }
        .btn-login {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .info-box {
            background: var(--bg-card);
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-top: 1.5rem;
            font-size: 0.8125rem;
            color: var(--text-light);
            border: 1px solid var(--border);
        }
        .info-box strong {
            color: var(--text);
        }
        .info-box code {
            background: var(--dark-tertiary);
            padding: 0.125rem 0.375rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Painel Admin</h1>
            <p><?= defined('SITE_NAME') ? SITE_NAME : 'Bianca Moraes' ?></p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Usuário ou E-mail</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <div class="info-box">
            <strong>Credenciais padrão:</strong><br>
            Usuário: <code>admin</code><br>
            Senha: <code>admin123</code>
        </div>
    </div>
</body>
</html>
<?php
ob_end_flush();
?>
