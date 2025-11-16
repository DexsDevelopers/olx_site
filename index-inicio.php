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
if (empty($listaProdutos)) {
    // Se não há produtos, manter o HTML original
    error_log("AVISO: Nenhum produto ativo - mantendo HTML original");
} else {
    // Método mais seguro: encontrar a section pelo ID e substituir apenas o conteúdo interno
    $sectionId = 'id="produtos-lucas-template"';
    $sectionStart = strpos($htmlContent, '<section', strpos($htmlContent, $sectionId) - 100);
    
    if ($sectionStart !== false) {
        // Encontrar onde começa o conteúdo da section (após a tag de abertura)
        $sectionTagEnd = strpos($htmlContent, '>', $sectionStart);
        if ($sectionTagEnd !== false) {
            $sectionTagEnd++; // Incluir o >
            
            // Encontrar o fechamento da section
            // Procurar pela tag </section> que fecha esta section específica
            $searchStart = $sectionTagEnd;
            $sectionEnd = false;
            $depth = 1; // Já estamos dentro de uma section
            
            // Procurar por todas as sections até encontrar o fechamento correto
            while ($searchStart < strlen($htmlContent) && $depth > 0) {
                $nextOpen = strpos($htmlContent, '<section', $searchStart);
                $nextClose = strpos($htmlContent, '</section>', $searchStart);
                
                if ($nextClose === false) break;
                
                // Se encontrou uma section aberta antes do fechamento, aumenta a profundidade
                if ($nextOpen !== false && $nextOpen < $nextClose) {
                    $depth++;
                    $searchStart = $nextOpen + 1;
                } else {
                    $depth--;
                    if ($depth === 0) {
                        $sectionEnd = $nextClose;
                        break;
                    }
                    $searchStart = $nextClose + 1;
                }
            }
            
            if ($sectionEnd !== false) {
                // Substituir apenas o conteúdo interno da section, mantendo a tag de abertura e fechamento
                $sectionOpening = substr($htmlContent, $sectionStart, $sectionTagEnd - $sectionStart);
                $sectionClosing = '</section>';
                
                // Extrair apenas o conteúdo interno do HTML gerado (sem a tag section)
                $produtosContent = $produtosHTML;
                // Se o HTML gerado já tem a tag section, remover
                if (strpos($produtosContent, '<section') === 0) {
                    $produtosContent = preg_replace('/^<section[^>]*>/', '', $produtosContent);
                    $produtosContent = preg_replace('/<\/section>$/', '', $produtosContent);
                }
                
                // Montar a nova section completa
                $newSection = $sectionOpening . $produtosContent . $sectionClosing;
                
                // Substituir toda a section antiga pela nova
                $htmlContent = substr_replace($htmlContent, $newSection, $sectionStart, $sectionEnd + strlen('</section>') - $sectionStart);
            } else {
                // Fallback: usar regex simples
                $pattern = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
                $htmlContent = preg_replace($pattern, $produtosHTML, $htmlContent, 1);
            }
        }
    } else {
        // Se não encontrou, tentar inserir antes do script
        $scriptPos = strpos($htmlContent, '<script>', strpos($htmlContent, 'produtos-lucas-template'));
        if ($scriptPos !== false) {
            $htmlContent = substr_replace($htmlContent, $produtosHTML . "\n  ", $scriptPos, 0);
        }
    }
}

// Output do HTML final
echo $htmlContent;

