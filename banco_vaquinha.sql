-- Banco de Dados: vaquinha
CREATE DATABASE IF NOT EXISTS vaquinha;
USE vaquinha;

-- Tabela de Categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'usuario') DEFAULT 'usuario',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Campanhas
CREATE TABLE IF NOT EXISTS campanhas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    meta DECIMAL(10,2) NOT NULL,
    arrecadado DECIMAL(10,2) DEFAULT 0,
    imagem VARCHAR(255),
    usuario_id INT,
    categoria_id INT,
    status ENUM('pendente', 'aprovada', 'reprovada', 'finalizada') DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabela de Doações
CREATE TABLE IF NOT EXISTS doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    campanha_id INT,
    valor DECIMAL(10,2) NOT NULL,
    mensagem VARCHAR(255),
    comprovante VARCHAR(255),
    status ENUM('pendente', 'confirmada', 'cancelada', 'em_analise', 'falha') DEFAULT 'pendente',
    metodo_pagamento ENUM('pix', 'cartao', 'manual', 'boleto') DEFAULT 'manual',
    gateway_id VARCHAR(100),
    gateway_retorno TEXT,
    payment_id VARCHAR(100),
    etapa ENUM('iniciada', 'aguardando_pagamento', 'paga', 'rejeitada', 'estornada', 'cancelada') DEFAULT 'iniciada',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (campanha_id) REFERENCES campanhas(id)
);

-- Tabela de Logs de Transações
CREATE TABLE IF NOT EXISTS transacoes_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doacao_id INT,
    acao VARCHAR(100),
    detalhes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doacao_id) REFERENCES doacoes(id)
);

-- Tabela de Relatórios (logs de ações administrativas)
CREATE TABLE IF NOT EXISTS relatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    acao VARCHAR(255),
    detalhes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES usuarios(id)
);

-- Tabela de Notificações
CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    titulo VARCHAR(255),
    mensagem TEXT,
    lida BOOLEAN DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de Permissões (para admins avançados)
CREATE TABLE IF NOT EXISTS permissoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    permissao VARCHAR(100),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de Menus
CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL
);

-- Tabela de Textos Institucionais
CREATE TABLE IF NOT EXISTS textos (
    chave VARCHAR(50) PRIMARY KEY,
    valor TEXT
);

-- Inserir textos padrões
INSERT IGNORE INTO textos (chave, valor) VALUES
('whatsapp', ''),
('email', ''),
('taxa', ''),
('quem_somos', '');

-- Tabela de Redes Sociais
CREATE TABLE IF NOT EXISTS redes_sociais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    url VARCHAR(255) NOT NULL,
    icone VARCHAR(50) NOT NULL
);

-- Tabela de Logo do Site
CREATE TABLE IF NOT EXISTS logo_site (
    id INT PRIMARY KEY,
    caminho VARCHAR(255) NOT NULL
);
INSERT IGNORE INTO logo_site (id, caminho) VALUES (1, 'images/logo-default.png');

-- Tabela de Termos e Política
CREATE TABLE IF NOT EXISTS textos_legais (
    chave VARCHAR(50) PRIMARY KEY,
    valor TEXT
);
INSERT IGNORE INTO textos_legais (chave, valor) VALUES
('termos', ''),
('politica', '');

-- Adicionar campo imagem em categorias
ALTER TABLE categorias ADD COLUMN imagem VARCHAR(255) DEFAULT NULL;

-- Adicionar campo destaque e taxa_destaque em campanhas
ALTER TABLE campanhas ADD COLUMN destaque BOOLEAN DEFAULT 0;
ALTER TABLE campanhas ADD COLUMN taxa_destaque DECIMAL(5,2) DEFAULT NULL;

-- Tabela de temas/cores
CREATE TABLE IF NOT EXISTS temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cor_primaria VARCHAR(20) NOT NULL,
    cor_secundaria VARCHAR(20) NOT NULL,
    cor_terciaria VARCHAR(20) NOT NULL,
    ativo BOOLEAN DEFAULT 0
);

-- Inserir temas padrão
INSERT IGNORE INTO temas (id, nome, cor_primaria, cor_secundaria, cor_terciaria, ativo) VALUES
(1, 'Padrão', '#782F9B', '#65A300', '#F7F7F7', 1),
(2, 'Azul', '#007bff', '#6610f2', '#f8f9fa', 0),
(3, 'Verde', '#28a745', '#218838', '#e9f7ef', 0),
(4, 'Vermelho', '#dc3545', '#c82333', '#f8d7da', 0),
(5, 'Laranja', '#fd7e14', '#e8590c', '#fff4e6', 0),
(6, 'Amarelo', '#ffc107', '#e0a800', '#fffbe6', 0),
(7, 'Rosa', '#e83e8c', '#d63384', '#fff0f6', 0),
(8, 'Ciano', '#17a2b8', '#138496', '#e3f7fa', 0),
(9, 'Preto', '#343a40', '#23272b', '#f8f9fa', 0),
(10, 'Branco', '#ffffff', '#f8f9fa', '#343a40', 0);

-- Adicionar campo foto_perfil em usuarios
ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) DEFAULT NULL;

-- Usuário admin de teste
INSERT IGNORE INTO usuarios (nome, email, senha, tipo, status) VALUES ('Admin Teste', 'admin@teste.com', '$2y$10$wH6QwQwQwQwQwQwQwQwQOeQwQwQwQwQwQwQwQwQwQwQwQwQwQw', 'admin', 'ativo');
-- Senha: admin123

-- Usuário comum de teste
INSERT IGNORE INTO usuarios (nome, email, senha, tipo, status) VALUES ('Usuário Teste', 'usuario@teste.com', '$2y$10$wH6QwQwQwQwQwQwQwQwQOeQwQwQwQwQwQwQwQwQwQwQwQwQwQw', 'usuario', 'ativo');
-- Senha: usuario123 