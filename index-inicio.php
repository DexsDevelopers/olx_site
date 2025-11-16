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

// Método mais robusto: usar regex para encontrar toda a section desde o comentário até o fechamento
// Padrão: comentário + section completa (incluindo todo conteúdo até </section>)
$pattern = '/<!-- =======================================================\s+BLOCO PERSONALIZADO DE PRODUTOS.*?<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';

// Tentar substituir
$replaced = preg_replace($pattern, $produtosHTML, $htmlContent, 1);

// Se a substituição funcionou (string mudou), usar o resultado
if ($replaced !== null && $replaced !== $htmlContent) {
    $htmlContent = $replaced;
} else {
    // Método alternativo: procurar apenas pela section com ID
    $pattern2 = '/<section[^>]*id="produtos-lucas-template"[^>]*>.*?<\/section>/s';
    $replaced2 = preg_replace($pattern2, $produtosHTML, $htmlContent, 1);
    if ($replaced2 !== null && $replaced2 !== $htmlContent) {
        $htmlContent = $replaced2;
    } else {
        // Último recurso: procurar pelo comentário e substituir tudo até a próxima section ou script
        $commentStart = strpos($htmlContent, '<!-- =======================================================');
        if ($commentStart !== false) {
            $sectionStart = strpos($htmlContent, '<section', $commentStart);
            if ($sectionStart !== false) {
                // Encontrar o fechamento correto da section (pode ter múltiplas sections aninhadas)
                $depth = 0;
                $pos = $sectionStart;
                $sectionEnd = false;
                while ($pos < strlen($htmlContent)) {
                    $nextOpen = strpos($htmlContent, '<section', $pos + 1);
                    $nextClose = strpos($htmlContent, '</section>', $pos);
                    
                    if ($nextClose === false) break;
                    
                    if ($nextOpen !== false && $nextOpen < $nextClose) {
                        $depth++;
                        $pos = $nextOpen;
                    } else {
                        if ($depth === 0) {
                            $sectionEnd = $nextClose + strlen('</section>');
                            break;
                        }
                        $depth--;
                        $pos = $nextClose;
                    }
                }
                
                if ($sectionEnd !== false) {
                    $htmlContent = substr_replace($htmlContent, $produtosHTML, $sectionStart, $sectionEnd - $sectionStart);
                }
            }
        }
    }
}

// Output do HTML final
echo $htmlContent;

