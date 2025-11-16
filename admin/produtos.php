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
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?= $action === 'create' ? '➕ Novo Produto' : '✏️ Editar Produto' ?></h1>
            <a href="index.php" class="btn btn-primary">← Voltar</a>
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
                        <label for="garantia_olx">Exibir "Garantia da OLX"</label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" id="ativo" name="ativo" value="1"
                               <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>>
                        <label for="ativo">Produto Ativo</label>
                    </div>
                </div>

                <hr>
                <h2>💳 Informações de Pagamento</h2>

                <div class="form-group">
                    <label for="qr_code_file">QR Code - Upload de Imagem</label>
                    <input type="file" id="qr_code_file" name="qr_code_file" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small>Faça upload de uma imagem do QR Code (máx. 5MB)</small>
                    <?php if (!empty($produto['qr_code']) && !preg_match('/^https?:\/\//', $produto['qr_code'])): ?>
                        <div style="margin-top: 1rem;">
                            <p style="font-size: 0.8125rem; color: var(--text-light); margin-bottom: 0.5rem;">Imagem atual:</p>
                            <img src="../<?= htmlspecialchars($produto['qr_code']) ?>" 
                                 alt="QR Code atual" 
                                 style="max-width: 200px; max-height: 200px; border: 2px solid var(--border); border-radius: var(--radius-sm); padding: 0.5rem; background: var(--light);">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="qr_code">QR Code - Ou insira URL/Link</label>
                    <input type="text" id="qr_code" name="qr_code"
                           value="<?= htmlspecialchars($produto['qr_code'] ?? '') ?>"
                           placeholder="https://exemplo.com/qr.png ou 5/4/checkout/650.png">
                    <small>Se não fizer upload, use este campo para URL ou caminho relativo</small>
                </div>

                <div class="form-group">
                    <label for="chave_pix">Chave PIX (Código Copia e Cola)</label>
                    <textarea id="chave_pix" name="chave_pix" rows="3"
                              placeholder="00020101021226800014br.gov.bcb.pix2558pix.asaas.com/qr/cobv/..."><?= htmlspecialchars($produto['chave_pix'] ?? '') ?></textarea>
                    <small>Código PIX completo para copia e cola</small>
                </div>

                <div class="form-group">
                    <label for="link_cartao">Link de Pagamento com Cartão</label>
                    <input type="text" id="link_cartao" name="link_cartao"
                           value="<?= htmlspecialchars($produto['link_cartao'] ?? '') ?>"
                           placeholder="https://pagamento.com/cartao?id=123">
                    <small>Link para página de pagamento com cartão de crédito/débito</small>
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

