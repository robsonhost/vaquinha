# Painel do Usuário - Vaquinha Online

## Visão Geral

O painel do usuário foi completamente corrigido e atualizado para fornecer uma experiência completa e funcional para os usuários da plataforma de vaquinhas online.

## Funcionalidades Implementadas

### 1. Dashboard Principal (`area_usuario.php`)
- **Boas-vindas personalizadas** com nome do usuário e último acesso
- **Estatísticas em tempo real**:
  - Total de campanhas criadas
  - Campanhas aprovadas
  - Total arrecadado nas campanhas
  - Total doado pelo usuário
- **Lista das últimas 5 campanhas** com status e progresso
- **Lista das últimas 5 doações** realizadas
- **Gráficos interativos** (Chart.js):
  - Doações recebidas nos últimos 30 dias
  - Status das campanhas (pizza)
- **Notificações não lidas** em tempo real

### 2. Minhas Campanhas (`minhas_campanhas.php`)
- **Lista completa** de todas as campanhas do usuário
- **Filtros avançados**:
  - Por status (pendente, aprovada, rejeitada, finalizada)
  - Por categoria
  - Busca por título/descrição
- **Paginação** para performance
- **Cards responsivos** com:
  - Imagem da campanha
  - Progresso visual
  - Informações de arrecadação
  - Ações (ver, editar, compartilhar)
- **Compartilhamento nativo** com Web Share API

### 3. Nova Campanha (`nova_campanha_usuario.php`)
- **Formulário completo** de criação
- **Validações robustas**:
  - Título mínimo de 10 caracteres
  - Descrição mínima de 50 caracteres
  - Meta mínima de R$ 50
  - Upload de imagem com validação
- **Preview da imagem** antes do upload
- **Contador de caracteres** em tempo real
- **Notificação WhatsApp** após criação

### 4. Minhas Doações (`minhas_doacoes.php`)
- **Lista de todas as doações** realizadas pelo usuário
- **Filtros**:
  - Por status
  - Por campanha
  - Por data
- **Estatísticas** de doações
- **Informações detalhadas** de cada doação
- **Funcionalidade de recibo** (em desenvolvimento)

### 5. Sistema de Layout Unificado

#### Header (`_header_usuario.php`)
- **Navegação consistente** entre páginas
- **Menu dropdown** do usuário com:
  - Avatar personalizado
  - Link para perfil
  - Notificações
  - Logout
- **Sidebar de navegação** com:
  - Dashboard
  - Minhas Campanhas
  - Nova Campanha
  - Minhas Doações
  - Perfil
  - Notificações (com badge de contagem)
- **Design responsivo** Bootstrap 5

#### Footer (`_footer_usuario.php`)
- **Scripts comuns** do painel
- **Funções JavaScript** utilitárias:
  - Toast notifications
  - Confirmação de exclusão
  - Formatação de moeda
  - Contador de caracteres
  - Preview de arquivos
  - Auto-refresh de notificações

## Integração com APIs

### WhatsApp
- **Notificação automática** na criação de campanhas
- **Confirmação de doações** para criadores
- **Suporte a múltiplos provedores** (Evolution API, WPPConnect, etc.)

### Mercado Pago
- **Pagamentos PIX** completamente integrados
- **Pagamentos com cartão** via tokenização
- **Webhook** para confirmação automática
- **Status em tempo real** dos pagamentos

## Melhorias de UX/UI

### Design
- **Bootstrap 5** para interface moderna
- **FontAwesome 6** para ícones consistentes
- **Google Fonts (Inter)** para tipografia
- **Cores temáticas** configuráveis pelo admin
- **Gradientes e sombras** para profundidade
- **Responsividade completa** mobile-first

### Funcionalidades
- **Toast notifications** para feedback imediato
- **Auto-dismiss** de alertas
- **Loading states** em formulários
- **Validação em tempo real**
- **Breadcrumbs** e navegação intuitiva

## Segurança

### Autenticação
- **Verificação de sessão** em todas as páginas
- **Validação de status** do usuário (ativo/inativo)
- **Redirecionamento automático** se não logado
- **Atualização de último acesso**

### Validação
- **Sanitização** de todas as entradas
- **Prepared statements** para queries
- **Validação de upload** de arquivos
- **Controle de permissões** por status

## Estrutura de Arquivos

```
/
├── area_usuario.php          # Dashboard principal
├── minhas_campanhas.php      # Lista de campanhas
├── nova_campanha_usuario.php # Criação de campanhas
├── minhas_doacoes.php        # Lista de doações
├── _header_usuario.php       # Header do painel
├── _footer_usuario.php       # Footer do painel
├── api/
│   └── notificacoes_count.php # API para contar notificações
└── uploads/
    └── campanhas/           # Diretório de imagens
```

## API Endpoints

### `/api/notificacoes_count.php`
- **Método**: GET
- **Retorno**: JSON com contagem de notificações não lidas
- **Uso**: Auto-refresh da badge de notificações

## JavaScript Utilitários

### Funções Globais
- `showToast(message, type)` - Exibir notificações toast
- `confirmDelete(message)` - Confirmação de exclusão
- `formatCurrency(input)` - Formatação de campos monetários
- `setupCharacterCounters()` - Contadores de caracteres
- `setupFilePreview()` - Preview de arquivos
- `refreshNotificationsBadge()` - Atualizar badge de notificações

## Como Usar

### Para Usuários
1. **Login** na plataforma
2. **Acesse o dashboard** para visão geral
3. **Crie campanhas** via "Nova Campanha"
4. **Gerencie campanhas** em "Minhas Campanhas"
5. **Acompanhe doações** em "Minhas Doações"
6. **Receba notificações** em tempo real

### Para Desenvolvedores
1. **Inclua o header**: `include '_header_usuario.php';`
2. **Defina `$page_title`** antes do header
3. **Desenvolva o conteúdo** da página
4. **Inclua o footer**: `include '_footer_usuario.php';`
5. **Use funções JavaScript** disponíveis no footer

## Próximas Melhorias

1. **Sistema de recibos** para doações
2. **Relatórios avançados** de campanhas
3. **Sistema de mensagens** entre usuários
4. **Notificações push** no navegador
5. **Exportação de dados** em CSV/PDF
6. **Timeline** de atividades da campanha
7. **Sistema de avaliações** e comentários

## Suporte

O painel foi desenvolvido com foco na experiência do usuário e facilidade de uso. Todas as funcionalidades são intuitivas e incluem feedback visual apropriado.

Para questões técnicas, verifique:
- **Logs do sistema** em `/logs/`
- **Console do navegador** para erros JavaScript
- **Configurações do banco** em `/admin/db.php`
- **Credenciais das APIs** no painel administrativo