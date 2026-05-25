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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
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
    </div>
</body>
</html>
<?php
ob_end_flush();
?>
