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
// Priorizar produtos que têm QR Code e PIX definidos
if ($linkPagina) {
    // Primeiro, tentar encontrar produto com QR Code e PIX
    $sql = "SELECT * FROM produtos 
            WHERE link_pagina = :link 
            AND ativo = 1 
            AND qr_code IS NOT NULL 
            AND qr_code != '' 
            AND chave_pix IS NOT NULL 
            AND chave_pix != ''
            ORDER BY id DESC 
            LIMIT 1";
    $db = Database::getInstance();
    $produto = $db->fetchOne($sql, ['link' => $linkPagina]);
    
    // Se não encontrou, buscar qualquer produto com esse link_pagina
    if (!$produto) {
        $sql = "SELECT * FROM produtos 
                WHERE link_pagina = :link 
                AND ativo = 1 
                ORDER BY id DESC 
                LIMIT 1";
        $produto = $db->fetchOne($sql, ['link' => $linkPagina]);
    }
}

// Se não encontrou, tentar pelo ID
if (!$produto && isset($_GET['id'])) {
    $produto = $produtos->buscar(intval($_GET['id']));
}

// Se ainda não encontrou, buscar produto com QR Code e PIX (qualquer um)
if (!$produto) {
    $sql = "SELECT * FROM produtos 
            WHERE ativo = 1 
            AND qr_code IS NOT NULL 
            AND qr_code != '' 
            AND chave_pix IS NOT NULL 
            AND chave_pix != ''
            ORDER BY id DESC 
            LIMIT 1";
    $db = Database::getInstance();
    $produto = $db->fetchOne($sql);
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

// Log para debug (verificar se está sendo processado)
error_log("=== CHECKOUT DEBUG ===");
error_log("Link Página buscado: " . ($linkPagina ?? 'NÃO DEFINIDO'));
error_log("Produto encontrado - ID: {$produto['id']}");
error_log("Produto Título: {$produto['titulo']}");
error_log("Link Página do produto: {$produto['link_pagina']}");
error_log("Preço: R$ " . number_format($produto['preco'], 2, ',', '.'));
error_log("Arquivo HTML: $htmlFile");
error_log("QR Code: " . (!empty($produto['qr_code']) ? $produto['qr_code'] : 'NÃO DEFINIDO'));
error_log("PIX: " . (!empty($produto['chave_pix']) ? substr($produto['chave_pix'], 0, 50) . '...' : 'NÃO DEFINIDO'));

// Formatar preço
$precoFormatado = 'R$ ' . number_format($produto['preco'], 2, ',', '.');
$precoSemFormatacao = number_format($produto['preco'], 2, ',', '.');
$precoNumero = number_format($produto['preco'], 0, '', '.');

// Substituir preços em múltiplos formatos e locais
// 1. Substituir "Pague por Pix" - formato mais comum (mais específico)
$htmlContent = preg_replace(
    '/(<strong>Pague por Pix<\/strong><br>)\s*R\$[\s]*[0-9.,]+/i',
    '$1 ' . $precoFormatado,
    $htmlContent
);

// 1.1. Substituir "Pague por Pix" sem <br>
$htmlContent = preg_replace(
    '/(<strong>Pague por Pix<\/strong>)\s*R\$[\s]*[0-9.,]+/i',
    '$1 ' . $precoFormatado,
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

// 5. Substituir preços genéricos (mais cuidadoso para não substituir tudo)
// Primeiro, substituir preços que estão sozinhos ou em contextos específicos
$htmlAntes = $htmlContent;
$htmlContent = preg_replace(
    '/R\$[\s]*([0-9]{1,3}(?:\.[0-9]{3})*(?:,[0-9]{2})?)/i',
    $precoFormatado,
    $htmlContent
);

// Log de quantas substituições foram feitas
$precoCount = substr_count($htmlContent, $precoFormatado);
$precoSubstituido = ($htmlContent !== $htmlAntes);
error_log("Preço '$precoFormatado' encontrado $precoCount vezes no HTML após substituição");
error_log("Preço foi substituído: " . ($precoSubstituido ? 'SIM' : 'NÃO'));

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
    
    // Log para debug
    error_log("=== QR CODE SUBSTITUIÇÃO ===");
    error_log("QR Code original: " . $qrCodePath);
    error_log("Produto ID: " . $produto['id']);
    
    // Se for caminho relativo, ajustar para funcionar no checkout
    if (!preg_match('/^https?:\/\//', $qrCodePath)) {
        // Se o arquivo está em 5/4/checkout/, remover o prefixo para caminho relativo
        if (strpos($qrCodePath, '5/4/checkout/') === 0) {
            $qrCodePath = str_replace('5/4/checkout/', '', $qrCodePath);
        } elseif (strpos($qrCodePath, 'checkout/') === 0) {
            $qrCodePath = str_replace('checkout/', '', $qrCodePath);
        }
        $qrCodePath = ltrim($qrCodePath, '/');
        
        // Verificar se o arquivo existe e ajustar caminho
        $qrCodeFullPath = __DIR__ . '/5/4/checkout/' . $qrCodePath;
        $arquivoEncontrado = false;
        
        if (file_exists($qrCodeFullPath)) {
            error_log("✓ QR Code encontrado em: $qrCodeFullPath");
            error_log("✓ Usando caminho relativo: $qrCodePath");
            $arquivoEncontrado = true;
        } else {
            error_log("AVISO: Arquivo QR Code não encontrado em: $qrCodeFullPath");
            // Tentar encontrar em outros locais
            $possiblePaths = [
                __DIR__ . '/' . $qrCodePath,
                __DIR__ . '/5/4/checkout/' . basename($qrCodePath),
                __DIR__ . '/5/4/checkout/qr-codes/' . basename($qrCodePath),
                __DIR__ . '/5/4/checkout/' . $qrCodePath,
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    // Calcular caminho relativo ao diretório checkout
                    $relativePath = str_replace(__DIR__ . '/5/4/checkout/', '', $path);
                    $qrCodePath = ltrim($relativePath, '/');
                    error_log("✓ QR Code encontrado em: $path");
                    error_log("✓ Usando caminho relativo: $qrCodePath");
                    $arquivoEncontrado = true;
                    break;
                }
            }
            
            if (!$arquivoEncontrado) {
                error_log("✗ ERRO: Arquivo QR Code não encontrado em nenhum local!");
                error_log("Tentando usar caminho original: $qrCodePath");
            }
        }
    }
    
    error_log("QR Code processado: " . $qrCodePath);
    
    // Verificar se o HTML contém o elemento antes de substituir
    $hasPixQr = preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*>/i', $htmlContent);
    error_log("HTML contém id='pix-qr': " . ($hasPixQr ? 'SIM' : 'NÃO'));
    
    // Capturar o HTML original da tag img para debug
    if ($hasPixQr) {
        preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*>/i', $htmlContent, $imgOriginal);
        error_log("HTML original da tag img: " . ($imgOriginal[0] ?? 'NÃO ENCONTRADO'));
    }
    
    // Substituição mais agressiva - substituir TUDO que tenha id="pix-qr"
    $htmlAntes = $htmlContent;
    
    // Garantir que o caminho está correto (sem barras no início)
    $qrCodePathFinal = ltrim($qrCodePath, '/');
    
    error_log("Tentando substituir QR Code com caminho: $qrCodePathFinal");
    
    $htmlContent = preg_replace(
        '/<img[^>]*id=["\']pix-qr["\'][^>]*>/i',
        '<img src="' . htmlspecialchars($qrCodePathFinal) . '" alt="QR Code Pix" id="pix-qr" width="220">',
        $htmlContent
    );
    
    // Verificar se houve mudança
    $mudou = ($htmlContent !== $htmlAntes);
    error_log("HTML mudou após substituição: " . ($mudou ? 'SIM' : 'NÃO'));
    
    // Verificar se a substituição funcionou (usar caminho final)
    $afterReplace = preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*src=["\']' . preg_quote(htmlspecialchars($qrCodePathFinal), '/') . '["\']/i', $htmlContent);
    error_log("Substituição QR Code bem-sucedida: " . ($afterReplace ? 'SIM' : 'NÃO'));
    
    if (!$afterReplace) {
        // Tentar verificar com caminho alternativo (com barra)
        $afterReplace = preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*src=["\']' . preg_quote(htmlspecialchars('/' . $qrCodePathFinal), '/') . '["\']/i', $htmlContent);
        if ($afterReplace) {
            error_log("Substituição encontrada com caminho com barra inicial");
        }
    }
    
    // Se não funcionou, tentar múltiplas estratégias
    if (!$afterReplace) {
        // Estratégia 1: Substituir qualquer img dentro de div.qr-code
        $htmlContent = preg_replace(
            '/(<div[^>]*class=["\'][^"\']*qr-code[^"\']*["\'][^>]*>.*?<img[^>]*src=["\'])[^"\']*(["\'][^>]*>)/is',
            '$1' . htmlspecialchars($qrCodePathFinal) . '$2',
            $htmlContent
        );
        error_log("Tentativa 1: Substituição QR Code via div.qr-code");
        
        // Estratégia 2: Substituir src diretamente na tag com id pix-qr
        $htmlContent = preg_replace(
            '/(<img[^>]*id=["\']pix-qr["\'][^>]*src=["\'])[^"\']*(["\'])/i',
            '$1' . htmlspecialchars($qrCodePathFinal) . '$2',
            $htmlContent
        );
        error_log("Tentativa 2: Substituição direta do src");
        
        // Estratégia 3: Substituir qualquer img que tenha src com extensão de imagem
        $htmlContent = preg_replace(
            '/(<img[^>]*id=["\']pix-qr["\'][^>]*src=["\'])[^"\']*\.(png|jpeg|jpg|webp)(["\'])/i',
            '$1' . htmlspecialchars($qrCodePathFinal) . '$3',
            $htmlContent
        );
        error_log("Tentativa 3: Substituição por extensão de arquivo");
    }
    
    // Verificar novamente após todas as tentativas (usar caminho final)
    $afterReplace = preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*src=["\']' . preg_quote(htmlspecialchars($qrCodePathFinal), '/') . '["\']/i', $htmlContent);
    if ($afterReplace) {
        error_log("✓ QR Code substituído com sucesso após tentativas!");
        // Capturar o HTML final para debug
        preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*>/i', $htmlContent, $imgFinal);
        error_log("HTML final da tag img: " . ($imgFinal[0] ?? 'NÃO ENCONTRADO'));
    } else {
        error_log("✗ ERRO: QR Code NÃO foi substituído após todas as tentativas!");
        // Última tentativa: substituir qualquer coisa que pareça um QR code
        $htmlContent = preg_replace(
            '/(<div[^>]*class=["\'][^"\']*qr-code[^"\']*["\'][^>]*>.*?<img[^>]*)(src=["\'][^"\']*["\'])([^>]*id=["\']pix-qr["\'][^>]*>)/is',
            '$1src="' . htmlspecialchars($qrCodePathFinal) . '"$3',
            $htmlContent
        );
        error_log("Tentativa final: Substituição completa da tag img com caminho: $qrCodePathFinal");
        
        // Verificar uma última vez
        $afterReplace = preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*src=["\']' . preg_quote(htmlspecialchars($qrCodePathFinal), '/') . '["\']/i', $htmlContent);
        if ($afterReplace) {
            error_log("✓ QR Code substituído na tentativa final!");
            preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*>/i', $htmlContent, $imgFinal);
            error_log("HTML final: " . ($imgFinal[0] ?? 'NÃO ENCONTRADO'));
        }
    }
}

// 8. Substituir Chave PIX se fornecida
if (!empty($produto['chave_pix'])) {
    $chavePix = trim($produto['chave_pix']);
    
    // Log para debug
    error_log("=== CHAVE PIX SUBSTITUIÇÃO ===");
    error_log("Chave PIX original (primeiros 50): " . substr($chavePix, 0, 50) . "...");
    error_log("Produto ID: " . $produto['id']);
    
    $chavePixEscaped = htmlspecialchars($chavePix);
    
    // Verificar se o HTML contém o elemento antes de substituir
    $hasPixCode = preg_match('/<span[^>]*id=["\']pix-code["\'][^>]*>/i', $htmlContent);
    error_log("HTML contém id='pix-code': " . ($hasPixCode ? 'SIM' : 'NÃO'));
    
    // Substituição mais agressiva - substituir TUDO que tenha id="pix-code"
    $htmlContent = preg_replace(
        '/<span[^>]*id=["\']pix-code["\'][^>]*>.*?<\/span>/is',
        '<span id="pix-code">' . $chavePixEscaped . '</span>',
        $htmlContent
    );
    
    // Verificar se a substituição funcionou
    $afterReplace = preg_match('/<span[^>]*id=["\']pix-code["\'][^>]*>' . preg_quote(substr($chavePixEscaped, 0, 30), '/') . '/i', $htmlContent);
    error_log("Substituição Chave PIX bem-sucedida: " . ($afterReplace ? 'SIM' : 'NÃO'));
    
    // Backup: substituir conteúdo dentro do span
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

// Adicionar parâmetro de versão para evitar cache
$htmlContent = str_replace('</head>', '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"><meta http-equiv="Pragma" content="no-cache"><meta http-equiv="Expires" content="0"></head>', $htmlContent);

// Adicionar comentário HTML para debug (remover em produção)
$debugComment = "<!-- 
CHECKOUT PROCESSADO
Data: " . date('Y-m-d H:i:s') . "
Produto ID: {$produto['id']}
Produto: " . htmlspecialchars($produto['titulo']) . "
Preço: R$ " . number_format($produto['preco'], 2, ',', '.') . "
QR Code: " . (!empty($produto['qr_code']) ? htmlspecialchars($produto['qr_code']) : 'NÃO DEFINIDO') . "
PIX: " . (!empty($produto['chave_pix']) ? 'SIM (' . strlen($produto['chave_pix']) . ' chars)' : 'NÃO DEFINIDO') . "
Arquivo: $htmlFile
-->";
$htmlContent = str_replace('</head>', $debugComment . '</head>', $htmlContent);

// Output
echo $htmlContent;

