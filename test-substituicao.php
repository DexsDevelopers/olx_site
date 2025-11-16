<?php
/**
 * Teste de Substituição - Verificar se está funcionando
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';
require_once __DIR__ . '/includes/produtos_template.php';

$produtos = new Produtos();
$listaProdutos = $produtos->listar(true);

$htmlFile = __DIR__ . '/index-inicio.html';
$htmlContent = file_get_contents($htmlFile);

$produtosHTML = renderProdutosCards($listaProdutos);

echo "<h2>Teste de Substituição</h2>";
echo "<p>Produtos encontrados: " . count($listaProdutos) . "</p>";
echo "<p>Tamanho do HTML gerado: " . strlen($produtosHTML) . " caracteres</p>";

// Verificar se a section existe no HTML
$sectionExists = strpos($htmlContent, 'id="produtos-lucas-template"') !== false;
echo "<p>Section encontrada no HTML: " . ($sectionExists ? "SIM" : "NÃO") . "</p>";

// Testar o padrão
$pattern = '/<!-- =======================================================\s+BLOCO PERSONALIZADO DE PRODUTOS.*?<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
$matches = [];
if (preg_match($pattern, $htmlContent, $matches)) {
    echo "<p style='color:green;'>✓ Padrão encontrou a section!</p>";
    echo "<p>Tamanho da section encontrada: " . strlen($matches[0]) . " caracteres</p>";
} else {
    echo "<p style='color:red;'>✗ Padrão NÃO encontrou a section</p>";
}

// Testar padrão alternativo
$pattern2 = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
if (preg_match($pattern2, $htmlContent, $matches2)) {
    echo "<p style='color:green;'>✓ Padrão alternativo encontrou a section!</p>";
    echo "<p>Tamanho: " . strlen($matches2[0]) . " caracteres</p>";
} else {
    echo "<p style='color:red;'>✗ Padrão alternativo NÃO encontrou</p>";
}

echo "<hr>";
echo "<h3>Preview do HTML gerado (primeiros 500 caracteres):</h3>";
echo "<pre>" . htmlspecialchars(substr($produtosHTML, 0, 500)) . "...</pre>";

