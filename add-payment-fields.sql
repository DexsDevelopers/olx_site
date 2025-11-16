-- Adicionar campos de pagamento na tabela produtos
ALTER TABLE produtos 
ADD COLUMN IF NOT EXISTS qr_code VARCHAR(500) DEFAULT NULL COMMENT 'URL ou caminho da imagem do QR Code',
ADD COLUMN IF NOT EXISTS link_cartao VARCHAR(500) DEFAULT NULL COMMENT 'Link para pagamento com cartão',
ADD COLUMN IF NOT EXISTS chave_pix VARCHAR(500) DEFAULT NULL COMMENT 'Chave PIX (código copia e cola)';

