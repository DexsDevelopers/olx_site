<?php
/**
 * Página Inicial - Versão PHP Dinâmica
 * Carrega produtos do banco de dados
 */

// Headers para evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';
require_once __DIR__ . '/includes/produtos_template.php';

// Buscar produtos ativos do banco
$produtos = new Produtos();
$listaProdutos = $produtos->listar(true); // apenas ativos

// Debug: verificar se há produtos
if (empty($listaProdutos)) {
    error_log("AVISO: Nenhum produto ativo encontrado no banco de dados!");
}

// Carregar o HTML original
$htmlFile = __DIR__ . '/index-inicio.html';
if (!file_exists($htmlFile)) {
    die("Erro: Arquivo index-inicio.html não encontrado!");
}
$htmlContent = file_get_contents($htmlFile);

// Gerar a seção de produtos dinâmica
$produtosHTML = renderProdutosCards($listaProdutos);

// Encontrar a posição da seção de produtos
$startComment = '<!-- =======================================================';
$endSection = '</section>';
$sectionId = 'id="produtos-lucas-template"';

// Método 1: Procurar pelo comentário até o fechamento da section
$commentPos = strpos($htmlContent, $startComment);
$sectionStartPos = strpos($htmlContent, '<section', $commentPos !== false ? $commentPos : 0);

if ($sectionStartPos !== false) {
    // Encontrar o fechamento da section correspondente
    $sectionEndPos = strpos($htmlContent, $endSection, $sectionStartPos);
    if ($sectionEndPos !== false) {
        $sectionEndPos += strlen($endSection);
        // Substituir toda a seção
        $htmlContent = substr_replace($htmlContent, $produtosHTML, $sectionStartPos, $sectionEndPos - $sectionStartPos);
    }
}

// Método 2: Se não encontrou, tentar pelo ID diretamente
if (strpos($htmlContent, $sectionId) !== false && strpos($htmlContent, 'Produtos da Bianca Moraes') !== false) {
    // Procurar pela section com o ID específico
    $pattern = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
    $matches = [];
    if (preg_match($pattern, $htmlContent, $matches)) {
        $htmlContent = preg_replace($pattern, $produtosHTML, $htmlContent, 1);
    }
}

// Método 3: Substituição mais agressiva - procurar qualquer section com produtos estáticos
if (strpos($htmlContent, 'Máquina de lavar e secar Samsung') !== false || 
    strpos($htmlContent, 'Geladeira Brastemp Frost Free') !== false) {
    // Encontrar a section que contém produtos estáticos
    $pattern = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
    $htmlContent = preg_replace($pattern, $produtosHTML, $htmlContent, 1);
}

// Output do HTML final
echo $htmlContent;

