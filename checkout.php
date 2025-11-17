<?php
/**
 * Página Dinâmica de Checkout/Pagamento
 * Busca produto do banco e substitui preços nas páginas de checkout
 */

// Headers para evitar cache
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';

$produtos = new Produtos();

// Buscar produto pelo link_pagina ou parâmetro
$produto = null;
$linkPagina = $_GET['p'] ?? $_GET['produto'] ?? '';

// Mapear nomes de arquivos de checkout para link_pagina
$fileMapping = [
    'index2.html' => 'index.html',
    'index2-iphone.html' => 'index-iphone.html',
    'index2-cama.html' => 'index-cama.html',
    'index-airfry.html' => 'index-airfry.html',
    'index-maquina-de-lavar.html' => 'index-maquina-de-lavar.html',
    'index.html' => 'index.html',
    'index-iphone.html' => 'index-iphone.html',
    'index-cama.html' => 'index-cama.html',
];

// Se veio de um arquivo de checkout, mapear para o link correto
$requestedFile = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
// Também verificar se veio via parâmetro p
if (!$linkPagina) {
    if (isset($fileMapping[$requestedFile])) {
        $linkPagina = $fileMapping[$requestedFile];
    } elseif (isset($_GET['p'])) {
        $linkPagina = $_GET['p'];
    }
}

// Buscar produto pelo link_pagina
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

// Se não há produto, redirecionar
if (!$produto) {
    header('Location: index-inicio.php');
    exit;
}

// Determinar qual arquivo HTML usar
$htmlFile = null;
$possibleFiles = [
    '5/4/checkout/index2.html',
    '5/4/checkout/index2-iphone.html',
    '5/4/checkout/index2-cama.html',
    '5/4/checkout/index-airfry.html',
    '5/4/checkout/index-maquina-de-lavar.html',
    '5/4/index.html',
    '5/4/index-iphone.html',
    '5/4/index-cama.html',
    '5/4/index-airfry.html',
    '5/4/index-maquina-de-lavar.html',
];

// Tentar encontrar o arquivo baseado no link_pagina ou arquivo solicitado
$linkToFile = [
    'index.html' => ['5/4/checkout/index2.html', '5/4/index.html'],
    'index-iphone.html' => ['5/4/checkout/index2-iphone.html', '5/4/index-iphone.html'],
    'index-cama.html' => ['5/4/checkout/index2-cama.html', '5/4/index-cama.html'],
    'index-airfry.html' => ['5/4/checkout/index-airfry.html', '5/4/index-airfry.html'],
    'index-maquina-de-lavar.html' => ['5/4/checkout/index-maquina-de-lavar.html', '5/4/index-maquina-de-lavar.html'],
];

// Verificar se o arquivo solicitado existe
$requestedPath = str_replace('/checkout.php', '', $_SERVER['REQUEST_URI']);
$requestedPath = ltrim($requestedPath, '/');
if (strpos($requestedPath, '5/4/') === 0) {
    if (file_exists(__DIR__ . '/' . $requestedPath)) {
        $htmlFile = $requestedPath;
    }
}

// Se não encontrou, tentar pelo link_pagina
if (!$htmlFile && isset($linkToFile[$produto['link_pagina']])) {
    foreach ($linkToFile[$produto['link_pagina']] as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            $htmlFile = $file;
            break;
        }
    }
}

// Se ainda não encontrou, tentar qualquer arquivo que exista
if (!$htmlFile) {
    foreach ($possibleFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            $htmlFile = $file;
            break;
        }
    }
}

// Se não encontrou, usar index2.html como padrão
if (!$htmlFile || !file_exists(__DIR__ . '/' . $htmlFile)) {
    $htmlFile = '5/4/checkout/index2.html';
    if (!file_exists(__DIR__ . '/' . $htmlFile)) {
        die("Erro: Arquivo de checkout não encontrado! Produto: " . htmlspecialchars($produto['titulo']));
    }
}

// Carregar o HTML
$htmlContent = file_get_contents(__DIR__ . '/' . $htmlFile);

// Formatar preço
$precoFormatado = 'R$ ' . number_format($produto['preco'], 2, ',', '.');
$precoSemFormatacao = number_format($produto['preco'], 2, ',', '.');
$precoNumero = number_format($produto['preco'], 0, '', '.');

// Substituir preços em múltiplos formatos e locais
// 1. Substituir "Pague por Pix" - formato mais comum
$htmlContent = preg_replace(
    '/<strong>Pague por Pix<\/strong><br>\s*R\$[\s]*[0-9.,]+/i',
    '<strong>Pague por Pix</strong><br> ' . $precoFormatado,
    $htmlContent
);

// 2. Substituir em spans com classe hoUCtC (páginas principais)
$htmlContent = preg_replace(
    '/<span[^>]*class="[^"]*hoUCtC[^"]*"[^>]*>R\$[\s]*[0-9.,]+<\/span>/i',
    '<span color="grayscale.darker" font-weight="400" class="sc-bdVaJa hoUCtC">' . $precoFormatado . '</span>',
    $htmlContent
);

// 3. Substituir "Total a pagar"
$htmlContent = preg_replace(
    '/(Total a pagar[^<]*)<span[^>]*>R\$[\s]*[0-9.,]+<\/span>/i',
    '$1<span color="dark" font-weight="400" class="sc-bdVaJa ePesmX">' . $precoFormatado . '</span>',
    $htmlContent
);

// 4. Substituir em data-valor
$htmlContent = preg_replace(
    '/data-valor="[0-9.,]+"/i',
    'data-valor="' . $precoNumero . '"',
    $htmlContent
);

// 5. Substituir preços genéricos (último, para pegar qualquer um que sobrou)
$htmlContent = preg_replace(
    '/R\$[\s]*([0-9]+\.?[0-9]*[\s,.]*[0-9]*)/i',
    $precoFormatado,
    $htmlContent
);

// 6. Substituir títulos de produtos
$tituloProduto = htmlspecialchars($produto['titulo']);
$htmlContent = preg_replace(
    '/Geladeira Brastemp[^<]*/i',
    $tituloProduto,
    $htmlContent
);
$htmlContent = preg_replace(
    '/Máquina de lavar[^<]*/i',
    $tituloProduto,
    $htmlContent
);
$htmlContent = preg_replace(
    '/iPhone 13[^<]*/i',
    $tituloProduto,
    $htmlContent
);
$htmlContent = preg_replace(
    '/Cama de solteiro[^<]*/i',
    $tituloProduto,
    $htmlContent
);
$htmlContent = preg_replace(
    '/Air Fryer[^<]*/i',
    $tituloProduto,
    $htmlContent
);

// 7. Substituir QR Code se fornecido
if (!empty($produto['qr_code'])) {
    $qrCodePath = trim($produto['qr_code']);
    
    // Log para debug (remover em produção)
    error_log("QR Code original: " . $qrCodePath);
    
    // Se for caminho relativo, ajustar para funcionar no checkout
    if (!preg_match('/^https?:\/\//', $qrCodePath)) {
        // Se o arquivo está em 5/4/checkout/, manter o caminho relativo ao diretório checkout
        if (strpos($qrCodePath, '5/4/checkout/') === 0) {
            // Remover apenas o prefixo 5/4/checkout/ para que fique relativo ao diretório do checkout
            $qrCodePath = str_replace('5/4/checkout/', '', $qrCodePath);
        } elseif (strpos($qrCodePath, 'checkout/') === 0) {
            $qrCodePath = str_replace('checkout/', '', $qrCodePath);
        }
        // Se começa com /, remover
        $qrCodePath = ltrim($qrCodePath, '/');
        
        // Se o caminho não começa com http e não está vazio, garantir que seja relativo
        // O arquivo HTML do checkout está em 5/4/checkout/, então caminhos relativos funcionam
    }
    
    error_log("QR Code processado: " . $qrCodePath);
    
    // Múltiplas tentativas de substituição com diferentes padrões
    // Padrão 1: ID exato - mais específico
    $htmlContent = preg_replace(
        '/<img[^>]*id=["\']pix-qr["\'][^>]*>/i',
        '<img src="' . htmlspecialchars($qrCodePath) . '" alt="QR Code Pix" id="pix-qr" width="220">',
        $htmlContent
    );
    
    // Padrão 2: Qualquer img dentro de .qr-code
    $htmlContent = preg_replace(
        '/(<div[^>]*class=["\'][^"\']*qr-code[^"\']*["\'][^>]*>.*?<img[^>]*src=["\'])[^"\']*(["\'][^>]*>)/is',
        '$1' . htmlspecialchars($qrCodePath) . '$2',
        $htmlContent
    );
    
    // Padrão 3: Substituir qualquer src que contenha .png, .jpeg, .jpg no contexto do QR code
    $htmlContent = preg_replace(
        '/(<img[^>]*src=["\'])[^"\']*\.(png|jpeg|jpg|webp)(["\'][^>]*id=["\']pix-qr[^>]*>)/i',
        '$1' . htmlspecialchars($qrCodePath) . '$3',
        $htmlContent
    );
    
    // Padrão 4: Substituir qualquer img com id pix-qr, independente do src atual
    $htmlContent = preg_replace(
        '/(<img[^>]*id=["\']pix-qr["\'][^>]*src=["\'])[^"\']*(["\'])/i',
        '$1' . htmlspecialchars($qrCodePath) . '$2',
        $htmlContent
    );
}

// 8. Substituir Chave PIX se fornecida
if (!empty($produto['chave_pix'])) {
    $chavePix = trim($produto['chave_pix']);
    
    // Log para debug (remover em produção)
    error_log("Chave PIX original: " . substr($chavePix, 0, 50) . "...");
    
    $chavePixEscaped = htmlspecialchars($chavePix);
    
    // Múltiplas tentativas de substituição com diferentes padrões
    // Padrão 1: ID exato com span - mais específico
    $htmlContent = preg_replace(
        '/<span[^>]*id=["\']pix-code["\'][^>]*>.*?<\/span>/is',
        '<span id="pix-code">' . $chavePixEscaped . '</span>',
        $htmlContent
    );
    
    // Padrão 2: Qualquer conteúdo dentro do span com id pix-code (não greedy)
    $htmlContent = preg_replace(
        '/(<span[^>]*id=["\']pix-code["\'][^>]*>)[^<]*(<\/span>)/i',
        '$1' . $chavePixEscaped . '$2',
        $htmlContent
    );
    
    // Padrão 3: Substituir dentro de .pix-code-box
    $htmlContent = preg_replace(
        '/(<div[^>]*class=["\'][^"\']*pix-code-box[^"\']*["\'][^>]*>.*?<span[^>]*id=["\']pix-code["\'][^>]*>)[^<]*(<\/span>)/is',
        '$1' . $chavePixEscaped . '$2',
        $htmlContent
    );
    
    // Padrão 4: Substituir qualquer conteúdo entre tags span com id pix-code
    $htmlContent = preg_replace(
        '/(<span[^>]*id=["\']pix-code["\'][^>]*>)([^<]+)(<\/span>)/i',
        '$1' . $chavePixEscaped . '$3',
        $htmlContent
    );
}

// 9. Substituir link de cartão se fornecido (se houver botão ou link de cartão no HTML)
if (!empty($produto['link_cartao'])) {
    $linkCartao = htmlspecialchars($produto['link_cartao']);
    // Procurar por links ou botões de cartão e substituir
    $htmlContent = preg_replace(
        '/(href|action)=["\'][^"\']*cart[ao][^"\']*["\']/i',
        '$1="' . $linkCartao . '"',
        $htmlContent
    );
}

// Output
echo $htmlContent;

