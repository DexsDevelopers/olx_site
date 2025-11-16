<?php
/**
 * Script para adicionar campos de pagamento na tabela produtos
 * Execute este arquivo UMA VEZ para atualizar o banco de dados
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$db = Database::getInstance();

try {
    // Verificar se os campos já existem
    $sql = "SHOW COLUMNS FROM produtos LIKE 'qr_code'";
    $result = $db->fetchOne($sql);
    
    if ($result) {
        echo "✅ Campos de pagamento já existem no banco de dados!\n";
        exit;
    }
    
    // Adicionar campos
    $sql = "ALTER TABLE produtos 
            ADD COLUMN qr_code VARCHAR(500) DEFAULT NULL COMMENT 'URL ou caminho da imagem do QR Code',
            ADD COLUMN link_cartao VARCHAR(500) DEFAULT NULL COMMENT 'Link para pagamento com cartão',
            ADD COLUMN chave_pix VARCHAR(500) DEFAULT NULL COMMENT 'Chave PIX (código copia e cola)'";
    
    $db->query($sql);
    
    echo "✅ Campos de pagamento adicionados com sucesso!\n";
    echo "   - qr_code\n";
    echo "   - link_cartao\n";
    echo "   - chave_pix\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao adicionar campos: " . $e->getMessage() . "\n";
    echo "\nTente executar o SQL manualmente:\n";
    echo file_get_contents(__DIR__ . '/add-payment-fields.sql');
}

