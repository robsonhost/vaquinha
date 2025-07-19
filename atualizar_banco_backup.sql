-- Script de atualização do banco para o sistema de backup melhorado
-- Data: 2024-12-19
-- Versão: 2.0

-- Criar tabela de logs de backup se não existir
CREATE TABLE IF NOT EXISTS logs_backup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('banco', 'arquivos', 'completo') NOT NULL,
    status ENUM('sucesso', 'erro', 'em_andamento') NOT NULL,
    arquivo VARCHAR(255),
    tamanho BIGINT,
    hash_verificacao VARCHAR(64),
    erro TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_id INT,
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_data_criacao (data_criacao)
);

-- Criar tabela de configurações se não existir
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    descricao TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir configurações padrão de backup
INSERT INTO configuracoes (config_key, config_value, descricao) VALUES 
('backup_automatico', '{"ativo": false, "frequencia": "semanal", "manter_dias": 30, "tipos": ["completo"]}', 'Configurações do sistema de backup automático')
ON DUPLICATE KEY UPDATE 
config_value = VALUES(config_value),
data_atualizacao = CURRENT_TIMESTAMP;

-- Criar tabela de administradores se não existir (necessária para foreign key)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir admin padrão se não existir
INSERT INTO admins (nome, email, senha) VALUES 
('Administrador', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE nome = nome;

-- Adicionar foreign key se a coluna admin_id existir na tabela logs_backup
-- (Isso será tratado de forma segura no código PHP)

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_logs_backup_admin ON logs_backup(admin_id);
CREATE INDEX IF NOT EXISTS idx_logs_backup_arquivo ON logs_backup(arquivo);

-- Limpar logs de backup muito antigos (mais de 1 ano)
DELETE FROM logs_backup WHERE data_criacao < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Adicionar trigger para limpar automaticamente logs antigos
DELIMITER $$

CREATE TRIGGER IF NOT EXISTS tr_cleanup_logs_backup
AFTER INSERT ON logs_backup
FOR EACH ROW
BEGIN
    -- Manter apenas os últimos 100 registros
    DELETE FROM logs_backup 
    WHERE id NOT IN (
        SELECT id FROM (
            SELECT id FROM logs_backup 
            ORDER BY data_criacao DESC 
            LIMIT 100
        ) as t
    );
END$$

DELIMITER ;

-- Criar diretório de backups (será criado pelo PHP se não existir)
-- Isso é apenas para documentação

-- Permissões recomendadas para o diretório de backups:
-- chmod 755 ../backups/
-- chown www-data:www-data ../backups/

-- Configurações de MySQL recomendadas para backup:
-- max_allowed_packet = 64M
-- innodb_buffer_pool_size = 256M (ou mais)
-- innodb_log_file_size = 128M

-- Configuração para logs de erro do MySQL
-- log_error = /var/log/mysql/error.log
-- general_log = ON
-- general_log_file = /var/log/mysql/mysql.log