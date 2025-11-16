<?php
/**
 * CRUD de Produtos - Painel Admin
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/produtos.php';

$auth = new Auth();
$auth->requireLogin();

$produtos = new Produtos();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$mensagem = '';
$tipoMensagem = '';

// Processar upload de QR Code
function processarUploadQRCode($produtoId = null) {
    if (!isset($_FILES['qr_code_file']) || $_FILES['qr_code_file']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $file = $_FILES['qr_code_file'];
    
    // Validar tipo de arquivo
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return null;
    }
    
    // Validar tamanho (máximo 5MB)
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return null;
    }
    
    // Criar diretório de uploads se não existir
    $uploadDir = __DIR__ . '/../5/4/checkout/qr-codes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Gerar nome único para o arquivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'qr_' . ($produtoId ?? 'new') . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;
    
    // Mover arquivo
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Retornar caminho relativo
        return '5/4/checkout/qr-codes/' . $fileName;
    }
    
    return null;
}

// Buscar produto se estiver editando (antes de processar POST)
$produtoAtual = null;
if ($action === 'edit' && $id) {
    $produtoAtual = $produtos->buscar($id);
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create' || $action === 'edit') {
        // Processar upload de QR Code
        $qrCodePath = null;
        if (isset($_FILES['qr_code_file']) && $_FILES['qr_code_file']['error'] === UPLOAD_ERR_OK) {
            $produtoIdParaUpload = ($action === 'edit' && $id) ? $id : null;
            $qrCodePath = processarUploadQRCode($produtoIdParaUpload);
        }
        
        // Se não houve upload, usar URL se fornecida
        if (!$qrCodePath && !empty($_POST['qr_code'])) {
            $qrCodePath = $_POST['qr_code'];
        }
        
        // Se editando e não mudou nada, manter o valor atual
        if ($action === 'edit' && !$qrCodePath && $produtoAtual && !empty($produtoAtual['qr_code'])) {
            $qrCodePath = $produtoAtual['qr_code'];
        }
        
        $dados = [
            'titulo' => $_POST['titulo'] ?? '',
            'descricao' => $_POST['descricao'] ?? '',
            'preco' => $_POST['preco'] ?? 0,
            'imagem_principal' => $_POST['imagem_principal'] ?? '',
            'localizacao' => $_POST['localizacao'] ?? 'São Paulo - SP',
            'data_publicacao' => $_POST['data_publicacao'] ?? date('Y-m-d'),
            'hora_publicacao' => $_POST['hora_publicacao'] ?? '00:15:00',
            'garantia_olx' => isset($_POST['garantia_olx']),
            'link_pagina' => $_POST['link_pagina'] ?? '',
            'ordem' => $_POST['ordem'] ?? 0,
            'ativo' => isset($_POST['ativo']),
            'qr_code' => $qrCodePath,
            'link_cartao' => $_POST['link_cartao'] ?? null,
            'chave_pix' => $_POST['chave_pix'] ?? null
        ];

        if ($action === 'create') {
            $produtos->criar($dados);
            $mensagem = 'Produto criado com sucesso!';
            $tipoMensagem = 'success';
            $action = 'list';
        } else {
            $produtos->atualizar($id, $dados);
            $mensagem = 'Produto atualizado com sucesso!';
            $tipoMensagem = 'success';
            $action = 'list';
        }
    }
}

if ($action === 'delete' && $id) {
    $produtos->deletar($id);
    $mensagem = 'Produto excluído com sucesso!';
    $tipoMensagem = 'success';
    $action = 'list';
}

// Usar produto atual se já foi buscado, senão buscar novamente
$produto = $produtoAtual;
if (!$produto && $action === 'edit' && $id) {
    $produto = $produtos->buscar($id);
    if (!$produto) {
        $action = 'list';
        $mensagem = 'Produto não encontrado!';
        $tipoMensagem = 'error';
    }
}

if ($action === 'list') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'create' ? 'Novo Produto' : 'Editar Produto' ?> | Painel Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-checkbox input[type="checkbox"] {
            width: auto;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?= $action === 'create' ? '➕ Novo Produto' : '✏️ Editar Produto' ?></h1>
            <a href="index.php" class="btn" style="background: white; color: #667eea; text-decoration: none;">← Voltar</a>
        </div>
    </div>

    <div class="container">
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipoMensagem ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="titulo">Título do Produto *</label>
                    <input type="text" id="titulo" name="titulo" 
                           value="<?= htmlspecialchars($produto['titulo'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="preco">Preço (R$) *</label>
                        <input type="number" id="preco" name="preco" step="0.01" min="0"
                               value="<?= $produto['preco'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="ordem">Ordem de Exibição</label>
                        <input type="number" id="ordem" name="ordem" min="0"
                               value="<?= $produto['ordem'] ?? 0 ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagem_principal">URL da Imagem Principal *</label>
                    <input type="text" id="imagem_principal" name="imagem_principal"
                           value="<?= htmlspecialchars($produto['imagem_principal'] ?? '') ?>" required
                           placeholder="https://img.olx.com.br/images/... ou images/produto.jpg">
                </div>

                <div class="form-group">
                    <label for="link_pagina">Link da Página do Produto</label>
                    <input type="text" id="link_pagina" name="link_pagina"
                           value="<?= htmlspecialchars($produto['link_pagina'] ?? '') ?>"
                           placeholder="index-produto.html">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="localizacao">Localização</label>
                        <input type="text" id="localizacao" name="localizacao"
                               value="<?= htmlspecialchars($produto['localizacao'] ?? 'São Paulo - SP') ?>">
                    </div>

                    <div class="form-group">
                        <label for="data_publicacao">Data de Publicação</label>
                        <input type="date" id="data_publicacao" name="data_publicacao"
                               value="<?= $produto['data_publicacao'] ?? date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="hora_publicacao">Hora de Publicação</label>
                    <input type="time" id="hora_publicacao" name="hora_publicacao"
                           value="<?= $produto['hora_publicacao'] ?? '00:15:00' ?>">
                </div>

                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" id="garantia_olx" name="garantia_olx" value="1"
                               <?= ($produto['garantia_olx'] ?? 1) ? 'checked' : '' ?>>
                        <label for="garantia_olx" style="margin: 0; font-weight: normal;">Exibir "Garantia da OLX"</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" id="ativo" name="ativo" value="1"
                               <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>>
                        <label for="ativo" style="margin: 0; font-weight: normal;">Produto Ativo</label>
                    </div>
                </div>

                <hr style="margin: 30px 0; border: none; border-top: 2px solid #e0e0e0;">
                <h2 style="margin-bottom: 20px; color: #667eea;">💳 Informações de Pagamento</h2>

                <div class="form-group">
                    <label for="qr_code_file">QR Code - Upload de Imagem</label>
                    <input type="file" id="qr_code_file" name="qr_code_file" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small style="color: #666; font-size: 12px;">Faça upload de uma imagem do QR Code (máx. 5MB)</small>
                    <?php if (!empty($produto['qr_code']) && !preg_match('/^https?:\/\//', $produto['qr_code'])): ?>
                        <div style="margin-top: 10px;">
                            <p style="font-size: 12px; color: #666; margin-bottom: 5px;">Imagem atual:</p>
                            <img src="../<?= htmlspecialchars($produto['qr_code']) ?>" 
                                 alt="QR Code atual" 
                                 style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="qr_code">QR Code - Ou insira URL/Link</label>
                    <input type="text" id="qr_code" name="qr_code"
                           value="<?= htmlspecialchars($produto['qr_code'] ?? '') ?>"
                           placeholder="https://exemplo.com/qr.png ou 5/4/checkout/650.png">
                    <small style="color: #666; font-size: 12px;">Se não fizer upload, use este campo para URL ou caminho relativo</small>
                </div>

                <div class="form-group">
                    <label for="chave_pix">Chave PIX (Código Copia e Cola)</label>
                    <textarea id="chave_pix" name="chave_pix" rows="3"
                              placeholder="00020101021226800014br.gov.bcb.pix2558pix.asaas.com/qr/cobv/..."><?= htmlspecialchars($produto['chave_pix'] ?? '') ?></textarea>
                    <small style="color: #666; font-size: 12px;">Código PIX completo para copia e cola</small>
                </div>

                <div class="form-group">
                    <label for="link_cartao">Link de Pagamento com Cartão</label>
                    <input type="text" id="link_cartao" name="link_cartao"
                           value="<?= htmlspecialchars($produto['link_cartao'] ?? '') ?>"
                           placeholder="https://pagamento.com/cartao?id=123">
                    <small style="color: #666; font-size: 12px;">Link para página de pagamento com cartão de crédito/débito</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $action === 'create' ? 'Criar Produto' : 'Salvar Alterações' ?>
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

