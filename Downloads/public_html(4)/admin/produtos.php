<?php
/**
 * CRUD de Produtos - Painel Admin
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/produtos.php';

$auth = new Auth();
$auth->requireLogin();

$produtos = new Produtos();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$mensagem = '';
$tipoMensagem = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'edit') {
        $dados = [
            'titulo' => $_POST['titulo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'preco' => $_POST['preco'] ?? 0,
            'imagem_principal' => $_POST['imagem_principal'] ?? '',
            'localizacao' => $_POST['localizacao'] ?? 'São Paulo - SP',
            'data_publicacao' => $_POST['data_publicacao'] ?? date('Y-m-d'),
            'hora_publicacao' => $_POST['hora_publicacao'] ?? '00:15:00',
            'garantia_olx' => isset($_POST['garantia_olx']),
            'link_pagina' => $_POST['link_pagina'] ?? '',
            'ordem' => $_POST['ordem'] ?? 0,
            'ativo' => isset($_POST['ativo'])
        ];

        if ($action === 'create') {
            $produtos->criar($dados);
            $mensagem = 'Produto criado com sucesso!';
            $tipoMensagem = 'success';
            $action = 'list';
        } else {
            $produtos->atualizar($id, $dados);
            $mensagem = 'Produto atualizado com sucesso!';
            $tipoMensagem = 'success';
            $action = 'list';
        }
    }
}

if ($action === 'delete' && $id) {
    $produtos->deletar($id);
    $mensagem = 'Produto excluído com sucesso!';
    $tipoMensagem = 'success';
    $action = 'list';
}

$produto = null;
if ($action === 'edit' && $id) {
    $produto = $produtos->buscar($id);
    if (!$produto) {
        $action = 'list';
        $mensagem = 'Produto não encontrado!';
        $tipoMensagem = 'error';
    }
}

if ($action === 'list') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'create' ? 'Novo Produto' : 'Editar Produto' ?> | Painel Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
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
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-checkbox input[type="checkbox"] {
            width: auto;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?= $action === 'create' ? '➕ Novo Produto' : '✏️ Editar Produto' ?></h1>
            <a href="index.php" class="btn" style="background: white; color: #667eea; text-decoration: none;">← Voltar</a>
        </div>
    </div>

    <div class="container">
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipoMensagem ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="titulo">Título do Produto *</label>
                    <input type="text" id="titulo" name="titulo" 
                           value="<?= htmlspecialchars($produto['titulo'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="preco">Preço (R$) *</label>
                        <input type="number" id="preco" name="preco" step="0.01" min="0"
                               value="<?= $produto['preco'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="ordem">Ordem de Exibição</label>
                        <input type="number" id="ordem" name="ordem" min="0"
                               value="<?= $produto['ordem'] ?? 0 ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagem_principal">URL da Imagem Principal *</label>
                    <input type="text" id="imagem_principal" name="imagem_principal"
                           value="<?= htmlspecialchars($produto['imagem_principal'] ?? '') ?>" required
                           placeholder="https://img.olx.com.br/images/... ou images/produto.jpg">
                </div>

                <div class="form-group">
                    <label for="link_pagina">Link da Página do Produto</label>
                    <input type="text" id="link_pagina" name="link_pagina"
                           value="<?= htmlspecialchars($produto['link_pagina'] ?? '') ?>"
                           placeholder="index-produto.html">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="localizacao">Localização</label>
                        <input type="text" id="localizacao" name="localizacao"
                               value="<?= htmlspecialchars($produto['localizacao'] ?? 'São Paulo - SP') ?>">
                    </div>

                    <div class="form-group">
                        <label for="data_publicacao">Data de Publicação</label>
                        <input type="date" id="data_publicacao" name="data_publicacao"
                               value="<?= $produto['data_publicacao'] ?? date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="hora_publicacao">Hora de Publicação</label>
                    <input type="time" id="hora_publicacao" name="hora_publicacao"
                           value="<?= $produto['hora_publicacao'] ?? '00:15:00' ?>">
                </div>

                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" id="garantia_olx" name="garantia_olx" value="1"
                               <?= ($produto['garantia_olx'] ?? 1) ? 'checked' : '' ?>>
                        <label for="garantia_olx" style="margin: 0; font-weight: normal;">Exibir "Garantia da OLX"</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" id="ativo" name="ativo" value="1"
                               <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>>
                        <label for="ativo" style="margin: 0; font-weight: normal;">Produto Ativo</label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $action === 'create' ? 'Criar Produto' : 'Salvar Alterações' ?>
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

