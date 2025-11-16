<?php
/**
 * Página Dinâmica de Produto Individual
 * Busca produto do banco de dados baseado no link_pagina ou parâmetro
 */

// Headers para evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';

$produtos = new Produtos();

// Buscar produto pelo link_pagina ou pelo parâmetro 'id'
$produto = null;
$linkPagina = $_GET['p'] ?? basename($_SERVER['PHP_SELF'], '.php') . '.html';

// Tentar buscar pelo link_pagina primeiro
if ($linkPagina) {
    $sql = "SELECT * FROM produtos WHERE link_pagina = :link AND ativo = 1 LIMIT 1";
    $db = Database::getInstance();
    $produto = $db->fetchOne($sql, ['link' => $linkPagina]);
}

// Se não encontrou, tentar pelo ID
if (!$produto && isset($_GET['id'])) {
    $produto = $produtos->buscar(intval($_GET['id']));
}

// Se ainda não encontrou, buscar o primeiro produto ativo
if (!$produto) {
    $lista = $produtos->listar(true);
    $produto = $lista[0] ?? null;
}

// Se não há produto, redirecionar para home
if (!$produto) {
    header('Location: index-inicio.php');
    exit;
}

// Determinar qual arquivo HTML usar baseado no link_pagina
$htmlFile = $produto['link_pagina'] ?? 'index.html';
if (!file_exists($htmlFile)) {
    // Tentar variações comuns
    $baseName = pathinfo($htmlFile, PATHINFO_FILENAME);
    $possibleFiles = [
        $htmlFile,
        $baseName . '.html',
        'index-' . $baseName . '.html',
        'index.html'
    ];
    
    foreach ($possibleFiles as $file) {
        if (file_exists($file)) {
            $htmlFile = $file;
            break;
        }
    }
}

// Se ainda não encontrou, usar index.html como fallback
if (!file_exists($htmlFile)) {
    $htmlFile = 'index.html';
}

// Carregar o HTML
$htmlContent = file_get_contents($htmlFile);

// Substituir valores dinâmicos
$replacements = [
    // Título
    '/<title>.*?<\/title>/i' => '<title>' . htmlspecialchars($produto['titulo']) . ' | OLX</title>',
    '/Iphone 13 Meia Noite|iPhone 13/i' => htmlspecialchars($produto['titulo']),
    
    // Preço - múltiplos formatos
    '/R\$[\s]*1\.?800/i' => 'R$ ' . number_format($produto['preco'], 2, ',', '.'),
    '/R\$[\s]*2\.?250/i' => 'R$ ' . number_format($produto['preco'], 2, ',', '.'),
    '/R\$[\s]*1\.?150/i' => 'R$ ' . number_format($produto['preco'], 2, ',', '.'),
    '/R\$[\s]*([0-9]+\.?[0-9]*)/i' => 'R$ ' . number_format($produto['preco'], 2, ',', '.'),
    
    // Descrição
    '/Ele possui marcas leves de uso nas laterais.*?iPhone 13\./i' => htmlspecialchars($produto['descricao'] ?: $produto['titulo']),
    
    // Meta description
    '/<meta\s+name=["\']description["\']\s+content=["\'][^"\']*["\']/i' => '<meta name="description" content="' . htmlspecialchars($produto['descricao'] ?: $produto['titulo']) . '"',
    
    // Open Graph
    '/<meta\s+property=["\']og:title["\']\s+content=["\'][^"\']*["\']/i' => '<meta property="og:title" content="' . htmlspecialchars($produto['titulo']) . '"',
    '/<meta\s+property=["\']og:description["\']\s+content=["\'][^"\']*["\']/i' => '<meta property="og:description" content="' . htmlspecialchars($produto['descricao'] ?: $produto['titulo']) . '"',
    
    // Imagem principal
    '/<meta\s+property=["\']og:image["\']\s+content=["\'][^"\']*["\']/i' => '<meta property="og:image" content="' . htmlspecialchars($produto['imagem_principal']) . '"',
];

// Aplicar substituições
foreach ($replacements as $pattern => $replacement) {
    $htmlContent = preg_replace($pattern, $replacement, $htmlContent);
}

// Substituir preços em spans específicos (formato mais preciso)
$htmlContent = preg_replace_callback(
    '/<span[^>]*class="[^"]*ad__sc-1wimjbb-1[^"]*"[^>]*>R\$[\s]*[0-9.,]+<\/span>/i',
    function($matches) use ($produto) {
        return '<span class="' . (preg_match('/class="([^"]*)"/', $matches[0], $classMatch) ? $classMatch[1] : '') . '">R$ ' . number_format($produto['preco'], 2, ',', '.') . '</span>';
    },
    $htmlContent
);

// Substituir preço no formato do botão de compra
$htmlContent = preg_replace_callback(
    '/<h2[^>]*data-testid="currencySymbol"[^>]*>R\$<\/h2>\s*<h2[^>]*>([0-9.,]+)<\/h2>/i',
    function($matches) use ($produto) {
        $precoFormatado = number_format($produto['preco'], 0, '', '.');
        return '<h2 data-testid="currencySymbol">R$</h2><h2>' . $precoFormatado . '</h2>';
    },
    $htmlContent
);

// Output
echo $htmlContent;

