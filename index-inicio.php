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

// Verificar se há produtos para exibir
if (!empty($listaProdutos)) {
    // Método simples e direto: substituir a section completa
    // O template já gera a section completa com o ID correto
    $pattern = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
    
    // Verificar se encontrou a section
    if (preg_match($pattern, $htmlContent)) {
        // Substituir
        $htmlContent = preg_replace($pattern, $produtosHTML, $htmlContent, 1);
    } else {
        // Se não encontrou pelo padrão, procurar pelo comentário
        $commentPattern = '/<!-- =======================================================\s+BLOCO PERSONALIZADO DE PRODUTOS.*?<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
        if (preg_match($commentPattern, $htmlContent)) {
            $htmlContent = preg_replace($commentPattern, $produtosHTML, $htmlContent, 1);
        } else {
            // Último recurso: encontrar a posição e inserir
            $commentPos = strpos($htmlContent, '<!-- =======================================================');
            if ($commentPos !== false) {
                $sectionPos = strpos($htmlContent, '<section', $commentPos);
                if ($sectionPos !== false) {
                    // Encontrar o fechamento
                    $closePos = strpos($htmlContent, '</section>', $sectionPos);
                    if ($closePos !== false) {
                        $closePos += strlen('</section>');
                        $htmlContent = substr_replace($htmlContent, $produtosHTML, $sectionPos, $closePos - $sectionPos);
                    }
                }
            }
        }
    }
}

// Output do HTML final
echo $htmlContent;

