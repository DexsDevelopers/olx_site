<?php
/**
 * Script de Debug - Verificar produtos no banco
 */

// Verificar se os arquivos existem antes de incluir
$configFile = __DIR__ . '/config.php';
$databaseFile = __DIR__ . '/database.php';
$produtosFile = __DIR__ . '/includes/produtos.php';

header('Content-Type: text/html; charset=utf-8');

// Verificação de arquivos
if (!file_exists($configFile)) {
    die("ERRO: config.php não encontrado em: $configFile");
}
if (!file_exists($databaseFile)) {
    die("ERRO: database.php não encontrado em: $databaseFile");
}
if (!file_exists($produtosFile)) {
    die("ERRO: includes/produtos.php não encontrado em: $produtosFile");
}

require_once $configFile;
require_once $databaseFile;
require_once $produtosFile;

$produtos = new Produtos();
$listaProdutos = $produtos->listar(false); // todos os produtos

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Produtos</title>";
echo "<style>body{font-family:Arial;padding:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";
echo "</head><body>";
echo "<h1>Debug - Produtos no Banco de Dados</h1>";
echo "<p>Total de produtos: " . count($listaProdutos) . "</p>";

if (empty($listaProdutos)) {
    echo "<p style='color:red;'><strong>NENHUM PRODUTO ENCONTRADO NO BANCO!</strong></p>";
} else {
    echo "<table>";
    echo "<tr><th>ID</th><th>Título</th><th>Preço</th><th>Ativo</th><th>Link Página</th><th>Última Atualização</th></tr>";
    foreach ($listaProdutos as $produto) {
        echo "<tr>";
        echo "<td>" . $produto['id'] . "</td>";
        echo "<td>" . htmlspecialchars($produto['titulo']) . "</td>";
        echo "<td>R$ " . number_format($produto['preco'], 2, ',', '.') . "</td>";
        echo "<td>" . ($produto['ativo'] ? '<span style="color:green;">✓ Ativo</span>' : '<span style="color:red;">✗ Inativo</span>') . "</td>";
        echo "<td>" . htmlspecialchars($produto['link_pagina'] ?? 'N/A') . "</td>";
        echo "<td>" . ($produto['data_publicacao'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<h2>Produtos Ativos (que devem aparecer no site):</h2>";
$produtosAtivos = $produtos->listar(true);
echo "<p>Total: " . count($produtosAtivos) . "</p>";
if (!empty($produtosAtivos)) {
    echo "<ul>";
    foreach ($produtosAtivos as $p) {
        echo "<li><strong>" . htmlspecialchars($p['titulo']) . "</strong> - R$ " . number_format($p['preco'], 2, ',', '.') . "</li>";
    }
    echo "</ul>";
}

echo "</body></html>";

