<?php
/**
 * Página Inicial - Versão PHP Dinâmica
 * Carrega produtos do banco de dados
 */

// Headers para evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';
require_once __DIR__ . '/includes/produtos_template.php';

// Buscar produtos ativos do banco
$produtos = new Produtos();
$listaProdutos = $produtos->listar(true); // apenas ativos

// Carregar o HTML original
$htmlContent = file_get_contents(__DIR__ . '/index-inicio.html');

// Gerar a seção de produtos dinâmica
$produtosHTML = renderProdutosCards($listaProdutos);

// Encontrar e substituir a seção de produtos estática
// Procurar pela seção completa desde o comentário até o fechamento </section>
// Padrão 1: Comentário + section completa
$pattern1 = '/<!-- =======================================================\s+BLOCO PERSONALIZADO DE PRODUTOS.*?<\/section>/s';
$htmlContent = preg_replace($pattern1, $produtosHTML, $htmlContent, 1);

// Padrão 2: Se não encontrou, procura apenas pela section com id
if (strpos($htmlContent, 'id="produtos-lucas-template"') !== false) {
    $pattern2 = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
    $htmlContent = preg_replace($pattern2, $produtosHTML, $htmlContent, 1);
}

// Output do HTML final
echo $htmlContent;

