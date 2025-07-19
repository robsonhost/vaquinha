# Integrações WhatsApp e Mercado Pago v2.0

## 📋 Visão Geral

Sistema completo de integrações com **WhatsApp** e **Mercado Pago** para o sistema de vaquinhas online. Esta versão 2.0 inclui validações robustas, logs detalhados, suporte a múltiplos provedores e sistema de testes automatizados.

## ✨ Principais Melhorias Implementadas

### 🔐 Segurança e Confiabilidade
- **Validação de credenciais**: Verificação automática do formato das chaves
- **Logs detalhados**: Registro completo de todas as operações
- **Tratamento de erros**: Sistema robusto de recuperação de falhas
- **Tentativas múltiplas**: Reenvio automático em caso de falha

### 📊 Monitoramento Avançado
- **Dashboard de status**: Visualização em tempo real das integrações
- **Sistema de testes**: Verificação automática das configurações
- **Logs em arquivo**: Histórico completo para debugging
- **Notificações por email**: Alertas sobre falhas nas integrações

### 🤖 Automação Inteligente
- **Detecção automática**: Identificação do provedor de WhatsApp
- **Webhooks otimizados**: Processamento eficiente de notificações
- **Mensagens personalizadas**: Templates inteligentes para diferentes situações
- **Retry automático**: Tentativas múltiplas com intervalos crescentes

## 📁 Estrutura dos Arquivos

```
includes/
├── whatsapp.php                    # Sistema WhatsApp melhorado
├── mercado_pago.php               # Sistema Mercado Pago melhorado
└── whatsapp-float.php             # Widget flutuante do WhatsApp

admin/
├── integracoes.php                # Interface de configuração
├── testar_integracoes.php         # Sistema de testes
└── db.php                         # Conexão com banco

logs/                              # Logs das integrações
├── whatsapp.log                   # Logs do WhatsApp
└── mercado_pago.log              # Logs do Mercado Pago

webhook_mercado_pago.php           # Webhook do Mercado Pago
```

## 🚀 Configuração das Integrações

### 1. WhatsApp API

#### Provedores Suportados

**Evolution API**
```bash
URL: https://evolution.seuprovedor.com
Token: sua_api_key
Instância: nome_da_instancia
```

**WPPConnect**
```bash
URL: https://wppconnect.seuprovedor.com
Token: seu_bearer_token
Sessão: nome_da_sessao
```

**Baileys API**
```bash
URL: https://baileys.seuprovedor.com
Token: sua_x_api_key
Sessão: nome_da_sessao
```

**API Genérica**
```bash
URL: https://api.seuprovedor.com
Token: seu_token
Nome: identificador
```

#### Configuração Passo a Passo

1. **Acesse o painel administrativo**
   - Vá para `admin/integracoes.php`
   - Ou use `admin/testar_integracoes.php` para verificar

2. **Configure os dados do WhatsApp**
   ```
   Nome da Instância: vaquinha_bot
   Token/API Key: sua_chave_de_acesso
   URL da API: https://api.seuprovedor.com
   ```

3. **Teste a conexão**
   - Clique em "Testar Conexão"
   - Informe um número de telefone de teste
   - Verifique se a mensagem foi recebida

### 2. Mercado Pago

#### Obter Credenciais

1. **Acesse o painel do Mercado Pago**
   - Vá para [developers.mercadopago.com](https://developers.mercadopago.com)
   - Faça login na sua conta

2. **Crie uma aplicação**
   - Clique em "Criar aplicação"
   - Escolha "Pagamentos online"
   - Preencha os dados solicitados

3. **Obtenha as credenciais**
   - **Produção**: Para ambiente real
   - **Teste**: Para desenvolvimento

#### Configuração no Sistema

1. **Configure as credenciais**
   ```
   Public Key: TEST-xxx ou APP_USR-xxx
   Access Token: TEST-xxx ou APP_USR-xxx
   ```

2. **Configure o webhook**
   - URL: `https://seudominio.com/webhook_mercado_pago.php`
   - Eventos: `payment`
   - Cole a URL no painel do Mercado Pago

3. **Teste a integração**
   - Use o botão "Testar Conexão"
   - Verifique se retorna dados da conta

## 🎯 Funcionalidades Principais

### WhatsApp Automático

#### Mensagem de Boas-vindas
Enviada automaticamente quando um usuário se cadastra:
```
🎉 Olá, {nome}! Seja bem-vindo(a) ao Vaquinha Online!

✅ Sua conta foi criada com sucesso!

📱 Seus dados de acesso:
E-mail: user@exemplo.com
Senha: suasenha

🔗 Acesse: https://seusite.com/entrar.php

💡 Dicas importantes:
• Compartilhe sua campanha nas redes sociais
• Conte sua história de forma emocionante
• Mantenha contato com seus doadores

🚀 Conte com a gente para transformar seus sonhos em realidade!
```

#### Notificação de Doação
Enviada quando uma campanha recebe uma nova doação:
```
💰 Oba! Nova doação recebida!

🎯 Campanha: Ajude o João
👤 Doador: Maria Silva
💵 Valor: R$ 50,00

📊 Situação atual:
• Arrecadado: R$ 350,00
• Meta: R$ 1.000,00
• Progresso: 35.0%

💪 Você já chegou na metade! Continue divulgando!

Continue engajando seus apoiadores! 💙
```

### Mercado Pago Integrado

#### Pagamentos PIX
- **Geração automática**: QR Code e código PIX
- **Expiração configurável**: 30 minutos por padrão
- **Status em tempo real**: Verificação automática

#### Pagamentos com Cartão
- **Tokenização segura**: Dados do cartão não ficam no servidor
- **Parcelas configuráveis**: Até 12x sem juros
- **Validação em tempo real**: Verificação imediata

#### Webhooks Inteligentes
- **Processamento automático**: Confirmação de pagamentos
- **Atualizações do banco**: Status sincronizado
- **Notificações automáticas**: WhatsApp e email

## 🔍 Sistema de Monitoramento

### Logs Detalhados

#### WhatsApp Log
```
[2024-12-19 14:30:15] Tentativa de envio para 5511999999999: 🎉 Olá, João! Seja bem-vindo...
[2024-12-19 14:30:16] Mensagem enviada com sucesso para 5511999999999 na tentativa 1
[2024-12-19 14:30:20] Erro na tentativa 1 para 5511888888888: Erro CURL: Connection timeout
```

#### Mercado Pago Log
```
[2024-12-19 14:30:15] Iniciando criação de pagamento: pix - R$ 50.00
[2024-12-19 14:30:16] Pagamento criado com sucesso: ID 12345678901
[2024-12-19 14:30:45] Processando webhook: {"type":"payment","data":{"id":"12345678901"}}
```

### Dashboard de Status

O painel administrativo mostra o status em tempo real:
- ✅ **Configurado e Funcionando**
- ⚠️ **Configurado** (não testado)
- ❌ **Não Configurado**

### Sistema de Testes

Execute `admin/testar_integracoes.php` para verificar:

1. **Conexão com banco de dados**
2. **Configurações do WhatsApp**
3. **Conectividade da API WhatsApp**
4. **Configurações do Mercado Pago**
5. **Conectividade da API Mercado Pago**
6. **Estrutura de diretórios**
7. **Extensões PHP necessárias**
8. **Permissões de arquivos**
9. **Tabelas do banco de dados**
10. **Sistema de logs**

## 🛠️ Troubleshooting

### Problemas Comuns WhatsApp

#### Erro: "Configurações não encontradas"
**Solução**: 
- Verifique se preencheu todos os campos obrigatórios
- Acesse `admin/integracoes.php` e configure

#### Erro: "Erro de conectividade"
**Solução**:
- Verifique a URL da API
- Teste se o servidor está acessível
- Verifique o token de acesso

#### Erro: "Telefone inválido"
**Solução**:
- Use formato internacional: 5511999999999
- Inclua código do país (55 para Brasil)
- Verifique se tem pelo menos 10 dígitos

### Problemas Comuns Mercado Pago

#### Erro: "Formato do Access Token inválido"
**Solução**:
- Verifique se o token começa com TEST- ou APP_USR-
- Copie o token completo do painel do Mercado Pago
- Não misture credenciais de teste com produção

#### Erro: "Webhook não está sendo chamado"
**Solução**:
- Configure a URL no painel do Mercado Pago
- Use HTTPS em produção
- Verifique se o arquivo webhook_mercado_pago.php está acessível

#### Erro: "Pagamento não foi processado"
**Solução**:
- Verifique os logs em `logs/mercado_pago.log`
- Confirme se o webhook está configurado
- Teste com credenciais de sandbox primeiro

## 📋 Checklist de Configuração

### WhatsApp
- [ ] Provedor de API escolhido e configurado
- [ ] Credenciais inseridas no sistema
- [ ] Teste de envio realizado com sucesso
- [ ] Logs sendo gerados corretamente
- [ ] Número de telefone do admin configurado

### Mercado Pago
- [ ] Aplicação criada no painel do Mercado Pago
- [ ] Public Key e Access Token configurados
- [ ] Webhook URL configurada no painel
- [ ] Teste de conexão realizado com sucesso
- [ ] Ambiente (teste/produção) definido

### Sistema
- [ ] Diretório `logs/` criado e gravável
- [ ] Extensões PHP necessárias instaladas
- [ ] Banco de dados atualizado
- [ ] Permissões de arquivo configuradas
- [ ] Testes automatizados executados

## 🔧 Configurações Avançadas

### Personalização de Mensagens

Edite as funções em `includes/whatsapp.php`:

```php
// Personalizar mensagem de boas-vindas
function enviar_boas_vindas_whatsapp($telefone, $nome, $email, $senha) {
    $mensagem = "Sua mensagem personalizada aqui...";
    return enviar_whatsapp($telefone, $mensagem);
}

// Personalizar notificação de doação
function enviar_notificacao_doacao($telefone, $nome_campanha, $nome_doador, $valor_doacao, $total_arrecadado, $meta) {
    $mensagem = "Sua notificação personalizada aqui...";
    return enviar_whatsapp($telefone, $mensagem);
}
```

### Configuração de Webhook

Para configurar manualmente o webhook do Mercado Pago:

```bash
curl -X POST \
  https://api.mercadopago.com/v1/webhooks \
  -H 'Authorization: Bearer SEU_ACCESS_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{
    "url": "https://seusite.com/webhook_mercado_pago.php",
    "events": ["payment"]
  }'
```

### Logs Personalizados

Para logs mais detalhados, edite as funções:

```php
// WhatsApp
function log_whatsapp($mensagem) {
    $log_file = __DIR__ . '/../logs/whatsapp.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$mensagem}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Mercado Pago
function log_mercado_pago($mensagem) {
    $log_file = __DIR__ . '/../logs/mercado_pago.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$mensagem}\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
```

## 🚀 Próximos Passos

### Funcionalidades Futuras
- [ ] Integração com outros gateways de pagamento
- [ ] Suporte a mais provedores de WhatsApp
- [ ] Sistema de templates de mensagens
- [ ] Agendamento de mensagens
- [ ] Relatórios de entrega WhatsApp
- [ ] Dashboard de métricas avançadas

### Melhorias Técnicas
- [ ] Cache de configurações
- [ ] Pool de conexões para APIs
- [ ] Rate limiting para WhatsApp
- [ ] Retry exponencial automático
- [ ] Métricas de performance
- [ ] Monitoramento em tempo real

## 📞 Suporte

Para problemas ou dúvidas:

1. **Verifique os logs**: `logs/whatsapp.log` e `logs/mercado_pago.log`
2. **Execute os testes**: `admin/testar_integracoes.php`
3. **Consulte a documentação**: Este arquivo e comentários no código
4. **Teste em ambiente de desenvolvimento** antes de produção

## 📄 Licença

Sistema desenvolvido para Vaquinha Online - Todos os direitos reservados.

---

**Versão**: 2.0  
**Data**: Dezembro 2024  
**Autor**: Sistema de Integrações Avançadas

## 🔗 Links Úteis

### WhatsApp APIs
- [Evolution API](https://doc.evolution-api.com/)
- [WPPConnect](https://wppconnect.io/docs/)
- [Baileys](https://github.com/adiwajshing/Baileys)

### Mercado Pago
- [Portal do Desenvolvedor](https://www.mercadopago.com.br/developers)
- [Documentação de APIs](https://www.mercadopago.com.br/developers/pt/docs)
- [Painel de Aplicações](https://www.mercadopago.com.br/developers/panel/app)
- [Webhooks](https://www.mercadopago.com.br/developers/pt/docs/webhooks)

### Recursos Adicionais
- [PHP cURL](https://www.php.net/manual/pt_BR/book.curl.php)
- [JSON em PHP](https://www.php.net/manual/pt_BR/book.json.php)
- [PDO MySQL](https://www.php.net/manual/pt_BR/ref.pdo-mysql.php)