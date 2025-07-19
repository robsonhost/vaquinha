<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Carregar integrações do banco
$chaves = [
    'mercado_pago_public_key',
    'mercado_pago_access_token',
    'whatsapp_name',
    'whatsapp_token',
    'whatsapp_api_url'
];
$integracoes = [];
foreach ($chaves as $chave) {
    $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
    $stmt->execute([$chave]);
    $integracoes[$chave] = $stmt->fetchColumn() ?: '';
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'salvar') {
        try {
            foreach ($chaves as $chave) {
                $valor = trim($_POST[$chave] ?? '');
                $stmt = $pdo->prepare('INSERT INTO textos (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?');
                $stmt->execute([$chave, $valor, $valor]);
            }
            $sucesso = 'Credenciais atualizadas com sucesso!';
            
            // Recarregar valores
            foreach ($chaves as $chave) {
                $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
                $stmt->execute([$chave]);
                $integracoes[$chave] = $stmt->fetchColumn() ?: '';
            }
        } catch (Exception $e) {
            $erro = 'Erro ao salvar: ' . $e->getMessage();
        }
    } 
    elseif ($acao === 'testar_whatsapp') {
        try {
            require_once '../includes/whatsapp.php';
            $telefone_teste = $_POST['telefone_teste'] ?? '';
            
            if (empty($telefone_teste)) {
                throw new Exception('Informe um número de telefone para teste');
            }
            
            $resultado = testar_whatsapp($telefone_teste);
            
            if ($resultado['sucesso']) {
                $sucesso = 'Teste WhatsApp realizado com sucesso! Verifique se a mensagem foi recebida.';
            } else {
                $erro = 'Erro no teste WhatsApp: ' . $resultado['erro'];
            }
        } catch (Exception $e) {
            $erro = 'Erro ao testar WhatsApp: ' . $e->getMessage();
        }
    }
    elseif ($acao === 'testar_mercado_pago') {
        try {
            require_once '../includes/mercado_pago.php';
            
            $resultado = testar_mercado_pago();
            
            if ($resultado['sucesso']) {
                $dados = $resultado['dados'];
                $sucesso = 'Teste Mercado Pago realizado com sucesso! Site ID: ' . $dados['site_id'] . 
                          ', Categoria: ' . $dados['category_id'] . 
                          ', ' . $dados['payment_methods'];
            } else {
                $erro = 'Erro no teste Mercado Pago: ' . $resultado['erro'];
            }
        } catch (Exception $e) {
            $erro = 'Erro ao testar Mercado Pago: ' . $e->getMessage();
        }
    }
}

// Verificar status das integrações
$status_whatsapp = 'danger';
$status_mercado_pago = 'danger';

if (!empty($integracoes['whatsapp_api_url']) && !empty($integracoes['whatsapp_token'])) {
    $status_whatsapp = 'warning'; // Configurado mas não testado
}

if (!empty($integracoes['mercado_pago_public_key']) && !empty($integracoes['mercado_pago_access_token'])) {
    $status_mercado_pago = 'warning'; // Configurado mas não testado
}

?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1><i class="fas fa-plug"></i> Integrações e APIs</h1>
                        <p class="text-muted">Configure as integrações com WhatsApp e Mercado Pago</p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Integrações</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($sucesso) ?>
                </div>
                <?php endif; ?>

                <!-- Status das Integrações -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-<?= $status_whatsapp ?>">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">WhatsApp API</span>
                                <span class="info-box-number">
                                    <?php if ($status_whatsapp === 'danger'): ?>
                                        Não Configurado
                                    <?php elseif ($status_whatsapp === 'warning'): ?>
                                        Configurado
                                    <?php else: ?>
                                        Funcionando
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-<?= $status_mercado_pago ?>">
                                <i class="fab fa-cc-visa"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Mercado Pago</span>
                                <span class="info-box-number">
                                    <?php if ($status_mercado_pago === 'danger'): ?>
                                        Não Configurado
                                    <?php elseif ($status_mercado_pago === 'warning'): ?>
                                        Configurado
                                    <?php else: ?>
                                        Funcionando
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="post">
                    <input type="hidden" name="acao" value="salvar">
                    <div class="row">
                        <!-- WhatsApp -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h3 class="card-title">
                                        <i class="fab fa-whatsapp"></i> WhatsApp API
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Provedores Suportados:</h6>
                                        <ul class="mb-0 small">
                                            <li><strong>Evolution API:</strong> https://api.evolutionapi.com</li>
                                            <li><strong>WPPConnect:</strong> https://api.wppconnect.io</li>
                                            <li><strong>Baileys:</strong> API customizada com Baileys</li>
                                            <li><strong>Outro:</strong> API genérica compatível</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="whatsapp_name">Nome da Instância/Sessão *</label>
                                        <input type="text" 
                                               id="whatsapp_name"
                                               name="whatsapp_name" 
                                               class="form-control" 
                                               value="<?= htmlspecialchars($integracoes['whatsapp_name']) ?>" 
                                               placeholder="Ex: vaquinha_bot"
                                               required>
                                        <small class="form-text text-muted">Nome da instância/sessão no seu provedor de API</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="whatsapp_token">Token/API Key *</label>
                                        <input type="password" 
                                               id="whatsapp_token"
                                               name="whatsapp_token" 
                                               class="form-control" 
                                               value="<?= htmlspecialchars($integracoes['whatsapp_token']) ?>" 
                                               placeholder="Seu token de acesso"
                                               required>
                                        <small class="form-text text-muted">Token de autenticação da API</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="whatsapp_api_url">URL da API *</label>
                                        <input type="url" 
                                               id="whatsapp_api_url"
                                               name="whatsapp_api_url" 
                                               class="form-control" 
                                               value="<?= htmlspecialchars($integracoes['whatsapp_api_url']) ?>" 
                                               placeholder="https://api.seuprovedor.com"
                                               required>
                                        <small class="form-text text-muted">URL base da API (sem /send-message)</small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-outline-success" onclick="testarWhatsApp()">
                                        <i class="fas fa-vial"></i> Testar Conexão
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mercado Pago -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="card-title">
                                        <i class="fab fa-cc-visa"></i> Mercado Pago
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle"></i> Importante:</h6>
                                        <ul class="mb-0 small">
                                            <li>Use credenciais de <strong>produção</strong> em ambiente real</li>
                                            <li>Use credenciais de <strong>teste</strong> para desenvolvimento</li>
                                            <li>Configure o webhook no painel do Mercado Pago</li>
                                            <li>URL do webhook: <code><?= $_SERVER['HTTP_HOST'] ?>/webhook_mercado_pago.php</code></li>
                                        </ul>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mercado_pago_public_key">Public Key *</label>
                                        <input type="text" 
                                               id="mercado_pago_public_key"
                                               name="mercado_pago_public_key" 
                                               class="form-control" 
                                               value="<?= htmlspecialchars($integracoes['mercado_pago_public_key']) ?>" 
                                               placeholder="TEST-xxx ou APP_USR-xxx"
                                               required>
                                        <small class="form-text text-muted">Chave pública para pagamentos com cartão</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mercado_pago_access_token">Access Token *</label>
                                        <input type="password" 
                                               id="mercado_pago_access_token"
                                               name="mercado_pago_access_token" 
                                               class="form-control" 
                                               value="<?= htmlspecialchars($integracoes['mercado_pago_access_token']) ?>" 
                                               placeholder="TEST-xxx ou APP_USR-xxx"
                                               required>
                                        <small class="form-text text-muted">Token de acesso para transações</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Webhook URL</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" 
                                                   value="<?= $_SERVER['HTTP_HOST'] ?>/webhook_mercado_pago.php" 
                                                   readonly>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-secondary" onclick="copiarWebhook()">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Configure esta URL no painel do Mercado Pago</small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-outline-primary" onclick="testarMercadoPago()">
                                        <i class="fas fa-vial"></i> Testar Conexão
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões de Ação -->
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Salvar Todas as Configurações
                            </button>
                            <a href="index.php" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Logs Recentes -->
                <div class="row mt-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fab fa-whatsapp"></i> Logs WhatsApp Recentes
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php
                                $log_whatsapp = '../logs/whatsapp.log';
                                if (file_exists($log_whatsapp)) {
                                    $lines = file($log_whatsapp);
                                    $recent_lines = array_slice($lines, -5);
                                    echo '<pre style="font-size: 12px; max-height: 200px; overflow-y: auto;">';
                                    foreach ($recent_lines as $line) {
                                        echo htmlspecialchars($line);
                                    }
                                    echo '</pre>';
                                } else {
                                    echo '<p class="text-muted">Nenhum log disponível</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fab fa-cc-visa"></i> Logs Mercado Pago Recentes
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php
                                $log_mp = '../logs/mercado_pago.log';
                                if (file_exists($log_mp)) {
                                    $lines = file($log_mp);
                                    $recent_lines = array_slice($lines, -5);
                                    echo '<pre style="font-size: 12px; max-height: 200px; overflow-y: auto;">';
                                    foreach ($recent_lines as $line) {
                                        echo htmlspecialchars($line);
                                    }
                                    echo '</pre>';
                                } else {
                                    echo '<p class="text-muted">Nenhum log disponível</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentação -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-book"></i> Documentação e Links Úteis
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>WhatsApp APIs</h5>
                                        <ul>
                                            <li><a href="https://doc.evolution-api.com/" target="_blank">Evolution API</a></li>
                                            <li><a href="https://wppconnect.io/docs/" target="_blank">WPPConnect</a></li>
                                            <li><a href="https://github.com/adiwajshing/Baileys" target="_blank">Baileys</a></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Mercado Pago</h5>
                                        <ul>
                                            <li><a href="https://www.mercadopago.com.br/developers" target="_blank">Portal do Desenvolvedor</a></li>
                                            <li><a href="https://www.mercadopago.com.br/developers/pt/docs" target="_blank">Documentação</a></li>
                                            <li><a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank">Suas Aplicações</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal de Teste WhatsApp -->
<div class="modal fade" id="modalTesteWhatsApp" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-whatsapp"></i> Testar WhatsApp
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" name="acao" value="testar_whatsapp">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="telefone_teste">Número de Telefone para Teste</label>
                        <input type="text" 
                               class="form-control" 
                               id="telefone_teste"
                               name="telefone_teste" 
                               placeholder="5511999999999"
                               required>
                        <small class="form-text text-muted">
                            Inclua o código do país (55 para Brasil)
                        </small>
                    </div>
                    <div class="alert alert-info">
                        <strong>Atenção:</strong> Uma mensagem de teste será enviada para este número.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> Enviar Teste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Teste Mercado Pago -->
<div class="modal fade" id="modalTesteMercadoPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fab fa-cc-visa"></i> Testar Mercado Pago
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <input type="hidden" name="acao" value="testar_mercado_pago">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Teste de Conexão:</strong> Este teste irá verificar se suas credenciais estão válidas e funcionando corretamente.
                    </div>
                    <p>O teste irá:</p>
                    <ul>
                        <li>Validar formato das credenciais</li>
                        <li>Verificar conexão com a API</li>
                        <li>Buscar informações da conta</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-vial"></i> Executar Teste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>

<script>
function testarWhatsApp() {
    $('#modalTesteWhatsApp').modal('show');
}

function testarMercadoPago() {
    $('#modalTesteMercadoPago').modal('show');
}

function copiarWebhook() {
    const input = document.querySelector('input[value*="webhook_mercado_pago.php"]');
    input.select();
    document.execCommand('copy');
    
    // Feedback visual
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}

// Máscaras para campos
document.getElementById('telefone_teste').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 13) value = value.substr(0, 13);
    e.target.value = value;
});

// Mostrar/ocultar senhas
document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        const wrapper = field.parentNode;
        if (!wrapper.querySelector('.password-toggle')) {
            const toggle = document.createElement('div');
            toggle.className = 'input-group-append password-toggle';
            toggle.innerHTML = `
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword(this)">
                    <i class="fas fa-eye"></i>
                </button>
            `;
            wrapper.classList.add('input-group');
            wrapper.appendChild(toggle);
        }
    });
});

function togglePassword(btn) {
    const field = btn.closest('.input-group').querySelector('input');
    const icon = btn.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<style>
.info-box {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #fff;
    border-radius: 0.25rem;
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
}

.info-box-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    font-size: 2rem;
    color: white;
    margin-right: 1rem;
}

.info-box-content {
    flex: 1;
}

.info-box-text {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-box-number {
    display: block;
    font-size: 1.125rem;
    font-weight: 600;
    color: #495057;
}

.card-header.bg-success {
    background-color: #25d366 !important;
}

.card-header.bg-primary {
    background-color: #009ee3 !important;
}

pre {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.75rem;
    margin: 0;
}

.password-toggle {
    cursor: pointer;
}

.input-group .form-control:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
</style>
</body>
</html> 