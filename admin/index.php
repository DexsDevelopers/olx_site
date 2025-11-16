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
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📦 Painel Admin</h1>
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
                            <td colspan="7" class="empty-state">
                                <p>Nenhum produto cadastrado ainda.</p>
                                <a href="produtos.php?action=create">Criar primeiro produto</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaProdutos as $produto): ?>
                            <tr>
                                <td><?= $produto['id'] ?></td>
                                <td>
                                    <img src="../<?= htmlspecialchars($produto['imagem_principal']) ?>" 
                                         alt="<?= htmlspecialchars($produto['titulo']) ?>"
                                         class="product-image">
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

