<?php
/**
 * Painel Administrativo - Dashboard
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/produtos.php';

$auth = new Auth();
$auth->requireLogin();

$produtos = new Produtos();
$listaProdutos = $produtos->listar(false);
$totalProdutos = count($listaProdutos);
$produtosAtivos = count(array_filter($listaProdutos, fn($p) => $p['ativo'] == 1));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin | <?= SITE_NAME ?></title>
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
        .header-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn-primary {
            background: white;
            color: #667eea;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-header {
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h2 {
            font-size: 20px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .actions {
            display: flex;
            gap: 8px;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        .btn-edit {
            background: #28a745;
            color: white;
        }
        .btn-edit:hover {
            background: #218838;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }
            th, td {
                padding: 10px 8px;
            }
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📦 Painel Admin - Produtos</h1>
            <div class="header-user">
                <span>Olá, <?= htmlspecialchars($auth->getAdminName()) ?></span>
                <a href="produtos.php?action=create" class="btn btn-primary">+ Novo Produto</a>
                <a href="logout.php" class="btn btn-danger">Sair</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Total de Produtos</h3>
                <div class="number"><?= $totalProdutos ?></div>
            </div>
            <div class="stat-card">
                <h3>Produtos Ativos</h3>
                <div class="number"><?= $produtosAtivos ?></div>
            </div>
            <div class="stat-card">
                <h3>Produtos Inativos</h3>
                <div class="number"><?= $totalProdutos - $produtosAtivos ?></div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Lista de Produtos</h2>
                <a href="produtos.php?action=create" class="btn btn-primary">+ Adicionar Produto</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Título</th>
                        <th>Preço</th>
                        <th>Localização</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaProdutos)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                Nenhum produto cadastrado ainda. <a href="produtos.php?action=create">Criar primeiro produto</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaProdutos as $produto): ?>
                            <tr>
                                <td><?= $produto['id'] ?></td>
                                <td>
                                    <img src="../<?= htmlspecialchars($produto['imagem_principal']) ?>" 
                                         alt="<?= htmlspecialchars($produto['titulo']) ?>"
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                                </td>
                                <td><strong><?= htmlspecialchars($produto['titulo']) ?></strong></td>
                                <td><?= $produtos->formatarPreco($produto['preco']) ?></td>
                                <td><?= htmlspecialchars($produto['localizacao']) ?></td>
                                <td>
                                    <span class="badge <?= $produto['ativo'] ? 'badge-success' : 'badge-danger' ?>">
                                        <?= $produto['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="produtos.php?action=edit&id=<?= $produto['id'] ?>" 
                                           class="btn btn-edit btn-sm">Editar</a>
                                        <a href="produtos.php?action=delete&id=<?= $produto['id'] ?>" 
                                           class="btn btn-delete btn-sm"
                                           onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

