<?php
/**
 * Teste rápido para verificar se checkout.php está funcionando
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';

$produtos = new Produtos();
$produto = $produtos->buscar(2); // Produto ID 2 (Geladeira)

if (!$produto) {
    die("Produto ID 2 não encontrado!");
}

echo "<h1>Teste de Checkout - Produto ID 2</h1>";
echo "<h2>Dados do Produto:</h2>";
echo "<p><strong>Título:</strong> " . htmlspecialchars($produto['titulo']) . "</p>";
echo "<p><strong>Link Página:</strong> " . htmlspecialchars($produto['link_pagina']) . "</p>";
echo "<p><strong>QR Code:</strong> " . (!empty($produto['qr_code']) ? htmlspecialchars($produto['qr_code']) : '<span style="color:red;">NÃO DEFINIDO</span>') . "</p>";
echo "<p><strong>Chave PIX:</strong> " . (!empty($produto['chave_pix']) ? htmlspecialchars(substr($produto['chave_pix'], 0, 50)) . "..." : '<span style="color:red;">NÃO DEFINIDA</span>') . "</p>";

echo "<hr>";
echo "<h2>Teste de Substituição:</h2>";

// Simular o que o checkout.php faz
$htmlTeste = file_get_contents(__DIR__ . '/5/4/checkout/index2.html');

echo "<h3>1. Verificar se HTML contém elementos:</h3>";
$hasPixQr = preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*>/i', $htmlTeste);
$hasPixCode = preg_match('/<span[^>]*id=["\']pix-code["\'][^>]*>/i', $htmlTeste);
echo "<p>HTML contém id='pix-qr': " . ($hasPixQr ? '<span style="color:green;">✓ SIM</span>' : '<span style="color:red;">✗ NÃO</span>') . "</p>";
echo "<p>HTML contém id='pix-code': " . ($hasPixCode ? '<span style="color:green;">✓ SIM</span>' : '<span style="color:red;">✗ NÃO</span>') . "</p>";

if (!empty($produto['qr_code'])) {
    echo "<h3>2. Teste de Substituição QR Code:</h3>";
    $qrCodePath = trim($produto['qr_code']);
    if (strpos($qrCodePath, '5/4/checkout/') === 0) {
        $qrCodePath = str_replace('5/4/checkout/', '', $qrCodePath);
    }
    $qrCodePath = ltrim($qrCodePath, '/');
    
    echo "<p><strong>QR Code processado:</strong> " . htmlspecialchars($qrCodePath) . "</p>";
    
    $htmlAntes = $htmlTeste;
    $htmlTeste = preg_replace(
        '/<img[^>]*id=["\']pix-qr["\'][^>]*>/i',
        '<img src="' . htmlspecialchars($qrCodePath) . '" alt="QR Code Pix" id="pix-qr" width="220">',
        $htmlTeste
    );
    
    $substituido = ($htmlTeste !== $htmlAntes);
    echo "<p>Substituição realizada: " . ($substituido ? '<span style="color:green;">✓ SIM</span>' : '<span style="color:red;">✗ NÃO</span>') . "</p>";
    
    if ($substituido) {
        preg_match('/<img[^>]*id=["\']pix-qr["\'][^>]*src=["\']([^"\']*)["\']/i', $htmlTeste, $matches);
        echo "<p><strong>Src após substituição:</strong> " . htmlspecialchars($matches[1] ?? 'NÃO ENCONTRADO') . "</p>";
    }
}

if (!empty($produto['chave_pix'])) {
    echo "<h3>3. Teste de Substituição Chave PIX:</h3>";
    $chavePix = htmlspecialchars(trim($produto['chave_pix']));
    
    echo "<p><strong>Chave PIX (primeiros 50 chars):</strong> " . htmlspecialchars(substr($produto['chave_pix'], 0, 50)) . "...</p>";
    
    $htmlAntes = $htmlTeste;
    $htmlTeste = preg_replace(
        '/<span[^>]*id=["\']pix-code["\'][^>]*>.*?<\/span>/is',
        '<span id="pix-code">' . $chavePix . '</span>',
        $htmlTeste
    );
    
    $substituido = ($htmlTeste !== $htmlAntes);
    echo "<p>Substituição realizada: " . ($substituido ? '<span style="color:green;">✓ SIM</span>' : '<span style="color:red;">✗ NÃO</span>') . "</p>";
    
    if ($substituido) {
        preg_match('/<span[^>]*id=["\']pix-code["\'][^>]*>([^<]*)<\/span>/i', $htmlTeste, $matches);
        $conteudo = $matches[1] ?? 'NÃO ENCONTRADO';
        echo "<p><strong>Conteúdo após substituição (primeiros 50 chars):</strong> " . htmlspecialchars(substr($conteudo, 0, 50)) . "...</p>";
    }
}

echo "<hr>";
echo "<h2>Links de Teste:</h2>";
echo "<ul>";
echo "<li><a href='checkout.php?p=index2.html' target='_blank'>checkout.php?p=index2.html</a></li>";
echo "<li><a href='5/4/checkout/index2.html' target='_blank'>5/4/checkout/index2.html</a> (deve redirecionar para checkout.php)</li>";
echo "<li><a href='5/4/index.html' target='_blank'>5/4/index.html</a> (deve redirecionar para checkout.php)</li>";
echo "</ul>";

echo "<hr>";
echo "<h2>Como verificar se está funcionando:</h2>";
echo "<ol>";
echo "<li>Abra o código fonte da página (Ctrl+U)</li>";
echo "<li>Procure por: <code>&lt;!-- Checkout processado em</code></li>";
echo "<li>Se encontrar, o checkout.php está sendo executado</li>";
echo "<li>Verifique se o QR Code e Chave PIX foram substituídos</li>";
echo "</ol>";
?>

