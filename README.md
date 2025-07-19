# Sistema de Vaquinhas Online

Um sistema completo e moderno para criação e gerenciamento de campanhas de arrecadação online, desenvolvido em PHP 8.1 com MySQL.

## 🚀 Características Principais

### Para Usuários
- ✅ Cadastro e login de usuários
- ✅ Criação de campanhas com upload de imagens
- ✅ Sistema de doações com comprovantes
- ✅ Dashboard pessoal com estatísticas
- ✅ Histórico de campanhas e doações
- ✅ Edição de perfil com foto
- ✅ Área do usuário moderna e responsiva

### Para Administradores
- ✅ Painel administrativo completo
- ✅ Dashboard com gráficos interativos
- ✅ Gerenciamento de campanhas e doações
- ✅ Sistema de notificações em tempo real
- ✅ Relatórios avançados com exportação
- ✅ Gerenciamento de usuários
- ✅ Configurações do sistema
- ✅ Sistema de temas dinâmicos
- ✅ Upload de logo e imagens

### Funcionalidades Avançadas
- ✅ Tema dinâmico com cores personalizáveis
- ✅ Sistema de categorias para campanhas
- ✅ Campanhas em destaque
- ✅ Taxas diferenciadas por campanha
- ✅ Modo manutenção
- ✅ Responsividade total
- ✅ SEO otimizado

## 📋 Requisitos do Sistema

- **PHP**: 8.1 ou superior
- **MySQL**: 5.7 ou superior
- **Servidor Web**: Apache/Nginx
- **Extensões PHP**:
  - PDO
  - PDO_MySQL
  - GD (para processamento de imagens)
  - JSON
  - cURL

## 🛠️ Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/vaquinha-online.git
cd vaquinha-online
```

### 2. Configure o banco de dados
```sql
-- Crie o banco de dados
CREATE DATABASE vaquinha_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Importe o arquivo SQL
mysql -u root -p vaquinha_online < database/schema.sql
```

### 3. Configure a conexão
Edite o arquivo `admin/db.php`:
```php
<?php
$host = 'localhost';
$dbname = 'vaquinha_online';
$username = 'seu_usuario';
$password = 'sua_senha';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
```

### 4. Configure as permissões
```bash
# Crie as pastas necessárias
mkdir -p uploads/campanhas
mkdir -p uploads/usuarios
mkdir -p uploads/comprovantes
mkdir -p images

# Configure as permissões
chmod 755 uploads/
chmod 755 images/
chmod 644 admin/db.php
```

### 5. Acesse o sistema
- **Frontend**: `http://localhost/vaquinha-online/`
- **Admin**: `http://localhost/vaquinha-online/admin/`
  - Usuário padrão: `admin`
  - Senha padrão: `admin123`

## 📁 Estrutura do Projeto

```
vaquinha-online/
├── admin/                     # Painel administrativo
│   ├── api/                   # APIs do sistema
│   ├── index.php             # Dashboard principal
│   ├── campanhas.php         # Gerenciamento de campanhas
│   ├── doacoes.php           # Gerenciamento de doações
│   ├── usuarios.php          # Gerenciamento de usuários
│   ├── relatorios.php        # Relatórios avançados
│   ├── notificacoes.php      # Sistema de notificações
│   ├── configuracoes.php     # Configurações gerais
│   ├── temas.php             # Gerenciamento de temas
│   └── db.php                # Conexão com banco
├── uploads/                   # Arquivos enviados
│   ├── campanhas/            # Imagens das campanhas
│   ├── usuarios/             # Fotos de perfil
│   └── comprovantes/         # Comprovantes de doação
├── images/                    # Imagens do sistema
├── js/                       # JavaScript
│   └── tema-dinamico.js      # Sistema de tema dinâmico
├── index.php                 # Página principal
├── login.php                 # Login de usuários
├── cadastro.php              # Cadastro de usuários
├── area_usuario.php          # Área do usuário
├── nova_campanha_usuario.php # Nova campanha
├── detalhes_campanha.php     # Detalhes da campanha
├── doacao.php                # Sistema de doações
└── README.md                 # Documentação
```

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

#### `usuarios`
- `id` (INT, PK)
- `nome` (VARCHAR)
- `email` (VARCHAR, UNIQUE)
- `senha` (VARCHAR)
- `tipo` (ENUM: 'usuario', 'admin')
- `status` (ENUM: 'ativo', 'inativo')
- `foto_perfil` (VARCHAR)
- `criado_em` (TIMESTAMP)

#### `campanhas`
- `id` (INT, PK)
- `usuario_id` (INT, FK)
- `categoria_id` (INT, FK)
- `titulo` (VARCHAR)
- `descricao` (TEXT)
- `meta` (DECIMAL)
- `arrecadado` (DECIMAL)
- `imagem` (VARCHAR)
- `status` (ENUM: 'pendente', 'aprovada', 'finalizada')
- `destaque` (BOOLEAN)
- `taxa_destaque` (DECIMAL)
- `criado_em` (TIMESTAMP)

#### `doacoes`
- `id` (INT, PK)
- `campanha_id` (INT, FK)
- `usuario_id` (INT, FK)
- `valor` (DECIMAL)
- `status` (ENUM: 'pendente', 'confirmada', 'cancelada')
- `comprovante` (VARCHAR)
- `criado_em` (TIMESTAMP)

#### `categorias`
- `id` (INT, PK)
- `nome` (VARCHAR)
- `imagem` (VARCHAR)
- `ativo` (BOOLEAN)

#### `temas`
- `id` (INT, PK)
- `nome` (VARCHAR)
- `cor_primaria` (VARCHAR)
- `cor_secundaria` (VARCHAR)
- `cor_terciaria` (VARCHAR)
- `ativo` (BOOLEAN)

#### `notificacoes`
- `id` (INT, PK)
- `titulo` (VARCHAR)
- `mensagem` (TEXT)
- `tipo` (ENUM: 'info', 'sucesso', 'aviso', 'erro')
- `destinatario` (ENUM: 'todos', 'admin', 'usuarios')
- `lida` (BOOLEAN)
- `criado_em` (TIMESTAMP)

## 🎨 Sistema de Temas

O sistema possui um tema dinâmico que pode ser personalizado através do painel administrativo:

### Cores Configuráveis
- **Cor Primária**: Cor principal do sistema
- **Cor Secundária**: Cor de destaque
- **Cor Terciária**: Cor de fundo

### Aplicação Automática
- As cores são aplicadas automaticamente via CSS custom properties
- JavaScript detecta mudanças e aplica em tempo real
- Cache local para melhor performance

## 📊 Relatórios

### Tipos de Relatórios Disponíveis
1. **Relatório Geral**: Visão geral do sistema
2. **Relatório de Campanhas**: Análise detalhada das campanhas
3. **Relatório de Doações**: Análise das doações
4. **Relatório de Usuários**: Estatísticas dos usuários

### Gráficos Disponíveis
- Gráfico de linha (doações por período)
- Gráfico de pizza (status das campanhas)
- Gráfico de barras (campanhas por categoria)
- Gráfico de rosca (distribuição de dados)

### Exportação
- Exportação em PDF (simulada)
- Dados em formato JSON
- Filtros avançados

## 🔔 Sistema de Notificações

### Características
- **Tempo Real**: Verificação automática a cada 10 segundos
- **Tipos**: Informação, Sucesso, Aviso, Erro
- **Destinatários**: Todos, Administradores, Usuários
- **Interface**: Toast notifications
- **Som**: Notificações sonoras opcionais

### Configurações
- Intervalo de verificação configurável
- Ativar/desativar notificações
- Configuração de som
- Notificações por e-mail (futuro)

## 🔧 Configurações do Sistema

### Configurações Gerais
- Nome do site
- Descrição
- Taxa padrão
- Modo manutenção
- Registro de usuários
- Aprovação manual de campanhas

### Configurações de Upload
- Tamanho máximo de arquivo
- Formatos permitidos
- Pasta de destino

### Informações de Contato
- WhatsApp
- E-mail
- Texto "Quem Somos"

## 🚀 Funcionalidades Futuras

### Planejadas
- [ ] Integração com gateways de pagamento
- [ ] Sistema de e-mail transacional
- [ ] API REST completa
- [ ] App mobile
- [ ] Sistema de recompensas
- [ ] Campanhas recorrentes
- [ ] Sistema de comentários
- [ ] Compartilhamento social avançado

### Melhorias Técnicas
- [ ] Cache Redis
- [ ] CDN para imagens
- [ ] Compressão de imagens automática
- [ ] Backup automático
- [ ] Logs avançados
- [ ] Monitoramento de performance

## 🛡️ Segurança

### Implementado
- ✅ Validação de entrada
- ✅ Prepared statements
- ✅ Hash de senhas (password_hash)
- ✅ Controle de sessão
- ✅ Verificação de permissões
- ✅ Sanitização de dados
- ✅ Proteção contra XSS

### Recomendações
- Use HTTPS em produção
- Configure firewall
- Mantenha o sistema atualizado
- Faça backups regulares
- Monitore logs de acesso

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:
- ✅ Desktop
- ✅ Tablet
- ✅ Smartphone
- ✅ Todos os navegadores modernos

## 🎯 SEO

### Otimizações Implementadas
- Meta tags dinâmicas
- URLs amigáveis
- Schema markup
- Sitemap automático
- Meta description personalizada
- Open Graph tags

## 📞 Suporte

### Contato
- **E-mail**: suporte@vaquinhaonline.com
- **WhatsApp**: (11) 99999-9999
- **Documentação**: [docs.vaquinhaonline.com](https://docs.vaquinhaonline.com)

### Comunidade
- **GitHub**: [github.com/vaquinha-online](https://github.com/vaquinha-online)
- **Issues**: [github.com/vaquinha-online/issues](https://github.com/vaquinha-online/issues)
- **Discord**: [discord.gg/vaquinha-online](https://discord.gg/vaquinha-online)

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🤝 Contribuição

Contribuições são bem-vindas! Por favor, leia o [CONTRIBUTING.md](CONTRIBUTING.md) para detalhes sobre nosso código de conduta e o processo para enviar pull requests.

## 🙏 Agradecimentos

- Bootstrap para o framework CSS
- Font Awesome para os ícones
- Chart.js para os gráficos
- Comunidade PHP
- Todos os contribuidores

---

**Desenvolvido com ❤️ para facilitar a solidariedade e ajudar pessoas através da tecnologia.** 