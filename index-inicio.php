<?php
/**
 * Página Inicial - Versão PHP Dinâmica
 * Carrega produtos do banco de dados
 */
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
// Procurar pelo início da seção (comentário + section)
$pattern = '/<!-- =======================================================\s+BLOCO PERSONALIZADO DE PRODUTOS.*?<\/section>/s';
$htmlContent = preg_replace($pattern, $produtosHTML, $htmlContent, 1);

// Output do HTML final
echo $htmlContent;

