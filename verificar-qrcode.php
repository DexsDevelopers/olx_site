<?php
/**
 * Verificar se o arquivo QR Code existe no servidor
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/includes/produtos.php';

$produtos = new Produtos();
$produto = $produtos->buscar(2); // Produto ID 2 (Geladeira)

if (!$produto) {
    die("Produto ID 2 não encontrado!");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar QR Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background: #f5f5f5;
        }
        .info {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        code {
            background: #f0f0f0;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <h1>🔍 Verificação de QR Code - Produto ID 2</h1>
    
    <div class="info">
        <h2>Dados do Produto:</h2>
        <p><strong>Título:</strong> <?= htmlspecialchars($produto['titulo']) ?></p>
        <p><strong>QR Code no banco:</strong> <code><?= htmlspecialchars($produto['qr_code'] ?? 'NÃO DEFINIDO') ?></code></p>
    </div>
    
    <?php if (!empty($produto['qr_code'])): ?>
        <?php
        $qrCodePath = trim($produto['qr_code']);
        $qrCodeProcessado = $qrCodePath;
        
        // Processar como no checkout.php
        if (!preg_match('/^https?:\/\//', $qrCodePath)) {
            if (strpos($qrCodePath, '5/4/checkout/') === 0) {
                $qrCodeProcessado = str_replace('5/4/checkout/', '', $qrCodePath);
            }
            $qrCodeProcessado = ltrim($qrCodeProcessado, '/');
        }
        
        // Verificar em múltiplos locais
        $possiblePaths = [
            __DIR__ . '/5/4/checkout/' . $qrCodeProcessado,
            __DIR__ . '/' . $qrCodePath,
            __DIR__ . '/5/4/checkout/' . basename($qrCodeProcessado),
            __DIR__ . '/5/4/checkout/qr-codes/' . basename($qrCodeProcessado),
        ];
        
        $arquivoEncontrado = false;
        $caminhoEncontrado = null;
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $arquivoEncontrado = true;
                $caminhoEncontrado = $path;
                break;
            }
        }
        ?>
        
        <div class="info <?= $arquivoEncontrado ? 'success' : 'error' ?>">
            <h2>Verificação de Arquivo:</h2>
            <p><strong>Status:</strong> <?= $arquivoEncontrado ? '✓ ARQUIVO ENCONTRADO' : '✗ ARQUIVO NÃO ENCONTRADO' ?></p>
            <p><strong>Caminho processado:</strong> <code><?= htmlspecialchars($qrCodeProcessado) ?></code></p>
            
            <?php if ($arquivoEncontrado): ?>
                <p><strong>Caminho completo encontrado:</strong> <code><?= htmlspecialchars($caminhoEncontrado) ?></code></p>
                <p><strong>Tamanho do arquivo:</strong> <?= number_format(filesize($caminhoEncontrado) / 1024, 2) ?> KB</p>
                <p><strong>Caminho relativo para usar:</strong> <code><?= htmlspecialchars(str_replace(__DIR__ . '/5/4/checkout/', '', $caminhoEncontrado)) ?></code></p>
                
                <h3>Preview da Imagem:</h3>
                <img src="../5/4/checkout/<?= htmlspecialchars(str_replace(__DIR__ . '/5/4/checkout/', '', $caminhoEncontrado)) ?>" 
                     alt="QR Code" 
                     style="max-width: 300px; border: 2px solid #28a745; padding: 1rem; background: white;">
            <?php else: ?>
                <h3>Locais verificados (não encontrado):</h3>
                <ul>
                    <?php foreach ($possiblePaths as $path): ?>
                        <li><code><?= htmlspecialchars($path) ?></code></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>⚠️ Ação necessária:</strong> Faça upload do arquivo QR Code para um dos locais acima.</p>
            <?php endif; ?>
        </div>
        
        <div class="info warning">
            <h2>Como corrigir:</h2>
            <ol>
                <li>Se o arquivo não existe, faça upload da imagem do QR Code</li>
                <li>Crie a pasta <code>5/4/checkout/qr-codes/</code> se não existir</li>
                <li>Faça upload do arquivo <code>qr_2_1763342017.jpeg</code> para essa pasta</li>
                <li>Ou ajuste o caminho no banco de dados para onde o arquivo realmente está</li>
            </ol>
        </div>
    <?php else: ?>
        <div class="info error">
            <p>⚠️ Produto não tem QR Code definido no banco de dados!</p>
            <p>Edite o produto no painel admin e adicione o QR Code.</p>
        </div>
    <?php endif; ?>
</body>
</html>

