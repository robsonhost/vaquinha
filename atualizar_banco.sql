-- Script para atualizar banco de dados com campos do Mercado Pago
USE vaquinha;

-- Adicionar campo payment_id na tabela doacoes
ALTER TABLE doacoes ADD COLUMN IF NOT EXISTS payment_id VARCHAR(100) DEFAULT NULL;

-- Adicionar campos de telefone e CPF na tabela usuarios
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS telefone VARCHAR(20) DEFAULT NULL;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS cpf VARCHAR(14) DEFAULT NULL;

-- Adicionar campos de aprovação na tabela campanhas
ALTER TABLE campanhas ADD COLUMN IF NOT EXISTS aprovado_em TIMESTAMP NULL;
ALTER TABLE campanhas ADD COLUMN IF NOT EXISTS motivo_rejeicao TEXT DEFAULT NULL;

-- Adicionar campo aprovado_em na tabela doacoes
ALTER TABLE doacoes ADD COLUMN IF NOT EXISTS aprovado_em TIMESTAMP NULL;
ALTER TABLE doacoes ADD COLUMN IF NOT EXISTS motivo_rejeicao TEXT DEFAULT NULL;

-- Adicionar campo ultimo_acesso na tabela usuarios
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS ultimo_acesso TIMESTAMP NULL;

-- Adicionar campo atualizado_em na tabela usuarios
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS atualizado_em TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP;

-- Adicionar campo criado_em na tabela usuarios se não existir
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Atualizar ENUM de métodos de pagamento para incluir cartão
ALTER TABLE doacoes MODIFY COLUMN metodo_pagamento ENUM('pix', 'cartao_debito', 'cartao_credito', 'manual', 'boleto') DEFAULT 'manual';

-- Inserir credenciais padrão do Mercado Pago (serão editadas pelo painel)
INSERT IGNORE INTO textos (chave, valor) VALUES
('mercado_pago_public_key', ''),
('mercado_pago_access_token', ''),
('whatsapp_name', ''),
('whatsapp_token', ''),
('whatsapp_api_url', '');

-- Criar tabela de admins se não existir
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Criar tabela de logs de acesso se não existir
CREATE TABLE IF NOT EXISTS logs_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    ip VARCHAR(45),
    data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Inserir admin padrão se não existir
INSERT IGNORE INTO admins (id, nome, email, senha) VALUES 
(1, 'Administrador', 'admin@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Senha: password 