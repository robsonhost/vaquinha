# Sistema de Backup Melhorado - Vaquinha Online v2.0

## 📋 Visão Geral

O sistema de backup foi completamente reformulado com foco em **segurança**, **confiabilidade** e **facilidade de uso**. Esta versão 2.0 inclui verificação de integridade, logs reais, limpeza automática e sistema de backup automático via cron job.

## ✨ Principais Melhorias Implementadas

### 🔐 Segurança
- **Prepared Statements**: Proteção contra SQL injection
- **Verificação de integridade**: Hash SHA256 para todos os backups
- **Filtragem de arquivos**: Apenas extensões permitidas são incluídas
- **Logs de auditoria**: Registro completo de todas as operações

### 📊 Monitoramento
- **Logs reais**: Histórico real de backups armazenado no banco
- **Dashboard atualizado**: Estatísticas em tempo real
- **Notificações por email**: Alertas sobre status dos backups
- **Interface moderna**: Design responsivo e intuitivo

### 🤖 Automação
- **Backup automático**: Configurável via interface web
- **Limpeza automática**: Remove backups antigos automaticamente
- **Cron job integrado**: Script para execução via sistema
- **Configurações flexíveis**: Múltiplas opções de frequência e tipos

### 🛠️ Funcionalidades Técnicas
- **Compressão otimizada**: Controle de nível de compressão
- **Gestão de tamanho**: Limite configurável por backup
- **Recuperação de erro**: Tratamento robusto de exceções
- **Performance melhorada**: Algoritmos otimizados

## 📁 Estrutura de Arquivos

```
admin/
├── backup.php                    # Interface principal de backup
├── configuracoes_backup.php      # Configurações avançadas
├── cron_backup.php              # Script para cron job
└── db.php                       # Conexão com banco de dados

backups/                         # Diretório de backups (criado automaticamente)
├── backup_banco_*.sql          # Backups do banco de dados
├── backup_arquivos_*.zip       # Backups dos arquivos
└── backup_completo_*.zip       # Backups completos

atualizar_banco_backup.sql      # Script de atualização do banco
README_BACKUP.md               # Esta documentação
```

## 🚀 Instalação e Configuração

### 1. Atualizar Banco de Dados

Execute o script SQL para criar as tabelas necessárias:

```bash
mysql -u usuario -p nome_banco < atualizar_banco_backup.sql
```

Ou execute no phpMyAdmin/interface SQL do seu servidor.

### 2. Configurar Permissões

```bash
# Criar diretório de backups
mkdir backups
chmod 755 backups
chown www-data:www-data backups

# Definir permissões para o script de cron
chmod +x admin/cron_backup.php
```

### 3. Configurar Backup Automático (Opcional)

#### Passo 1: Configurar via Interface Web
1. Acesse `admin/configuracoes_backup.php`
2. Configure frequência, tipos e notificações
3. Ative o backup automático

#### Passo 2: Configurar Cron Job
```bash
# Editar crontab
crontab -e

# Adicionar linha para backup diário às 2h
0 2 * * * /usr/bin/php /caminho/completo/para/admin/cron_backup.php

# Para backup semanal (segundas às 2h)
0 2 * * 1 /usr/bin/php /caminho/completo/para/admin/cron_backup.php

# Para backup mensal (dia 1 às 2h)
0 2 1 * * /usr/bin/php /caminho/completo/para/admin/cron_backup.php
```

## 🎯 Como Usar

### Interface Web

#### Backup Manual
1. Acesse `admin/backup.php`
2. Escolha o tipo de backup:
   - **Banco**: Apenas dados do banco
   - **Arquivos**: Apenas imagens/documentos
   - **Completo**: Banco + arquivos (recomendado)
3. Clique em "Baixar Backup"

#### Configurações Avançadas
1. Acesse `admin/configuracoes_backup.php`
2. Configure:
   - Ativação do backup automático
   - Frequência (diário/semanal/mensal)
   - Horário de execução
   - Tipos de backup
   - Email para notificações
   - Retenção de backups
   - Nível de compressão

#### Limpeza de Backups Antigos
1. Na interface principal, clique em "Limpar Antigos"
2. Defina quantos dias manter
3. Confirme a operação

### Linha de Comando

#### Backup Manual via CLI
```bash
# Executar backup automático
php admin/cron_backup.php

# Verificar logs
tail -f /var/log/cron
```

## 📊 Estrutura dos Backups

### Backup do Banco (.sql)
```sql
-- Backup do banco de dados
-- Data: 2024-12-19 14:30:00
-- Sistema: Vaquinha Online v2.0
-- Hash de verificação será adicionado no final

SET FOREIGN_KEY_CHECKS=0;
-- [Dados das tabelas]
-- Hash SHA256: [hash_verificacao]
```

### Backup de Arquivos (.zip)
```
backup_arquivos_2024-12-19_14-30-00.zip
├── image1.jpg
├── image2.png
├── document.pdf
└── backup_info.txt  # Informações do backup
```

### Backup Completo (.zip)
```
backup_completo_2024-12-19_14-30-00.zip
├── banco/
│   └── backup_banco_2024-12-19_14-30-00.sql
├── arquivos/
│   └── backup_arquivos_2024-12-19_14-30-00.zip
└── backup_info.txt  # Informações consolidadas
```

## 🔍 Monitoramento e Logs

### Logs no Banco de Dados
A tabela `logs_backup` registra:
- Tipo de backup executado
- Status (sucesso/erro/em_andamento)
- Nome do arquivo gerado
- Tamanho do backup
- Hash SHA256 para verificação
- Data/hora de execução
- Mensagens de erro (se houver)

### Verificação de Integridade
```bash
# Verificar hash de um backup SQL
sha256sum backup_banco_2024-12-19_14-30-00.sql

# Comparar com hash no final do arquivo SQL
tail -1 backup_banco_2024-12-19_14-30-00.sql
```

## 🚨 Resolução de Problemas

### Erro: "Tabela logs_backup não existe"
**Solução**: Execute o script `atualizar_banco_backup.sql`

### Erro: "Permissão negada ao criar backup"
**Solução**: 
```bash
chmod 755 backups/
chown www-data:www-data backups/
```

### Backup automático não executa
**Verificações**:
1. Cron job configurado corretamente
2. Backup automático ativado na interface
3. Caminho do PHP correto no crontab
4. Permissões do script cron_backup.php

### Backups muito grandes
**Soluções**:
1. Ajustar nível de compressão (1-9)
2. Configurar limite de tamanho
3. Fazer backup apenas do banco
4. Limpar arquivos desnecessários

### Email de notificação não enviado
**Verificações**:
1. Servidor configurado para envio de email
2. Função `mail()` habilitada no PHP
3. Email válido nas configurações
4. Verificar logs do servidor de email

## ⚙️ Configurações Recomendadas

### MySQL
```ini
# my.cnf
max_allowed_packet = 64M
innodb_buffer_pool_size = 256M
innodb_log_file_size = 128M
```

### PHP
```ini
# php.ini
max_execution_time = 300
memory_limit = 512M
upload_max_filesize = 64M
post_max_size = 64M
```

### Servidor Web
```apache
# .htaccess (para Apache)
<Files "cron_backup.php">
    Order deny,allow
    Deny from all
</Files>
```

## 📋 Checklist de Segurança

- [ ] Backups armazenados fora do servidor web
- [ ] Verificação periódica de integridade
- [ ] Teste de restauração mensal
- [ ] Monitoramento de espaço em disco
- [ ] Logs de backup revisados semanalmente
- [ ] Notificações de erro configuradas
- [ ] Acesso ao diretório de backup restrito
- [ ] Criptografia dos backups (se necessário)

## 🔄 Processo de Restauração

### Restaurar Banco de Dados
```bash
# Verificar integridade primeiro
sha256sum backup_banco_2024-12-19.sql

# Restaurar banco
mysql -u usuario -p nome_banco < backup_banco_2024-12-19.sql
```

### Restaurar Arquivos
```bash
# Extrair backup de arquivos
unzip backup_arquivos_2024-12-19.zip -d /caminho/destino/
```

### Restaurar Completo
```bash
# Extrair backup completo
unzip backup_completo_2024-12-19.zip

# Restaurar banco
mysql -u usuario -p nome_banco < banco/backup_banco_2024-12-19.sql

# Restaurar arquivos
unzip arquivos/backup_arquivos_2024-12-19.zip -d /caminho/destino/
```

## 📞 Suporte

Para problemas ou dúvidas:
1. Verifique os logs na interface web
2. Consulte a seção de resolução de problemas
3. Verifique os logs do servidor
4. Teste em ambiente de desenvolvimento primeiro

## 📄 Licença

Sistema desenvolvido para Vaquinha Online - Todos os direitos reservados.

---

**Versão**: 2.0  
**Data**: Dezembro 2024  
**Autor**: Sistema de Backup Automatizado