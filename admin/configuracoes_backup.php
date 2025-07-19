<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Função para obter configurações de backup
function obterConfiguracaoBackup($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT config_value FROM configuracoes WHERE config_key = ?");
        $stmt->execute(['backup_automatico']);
        $result = $stmt->fetch();
        return $result ? json_decode($result['config_value'], true) : [
            'ativo' => false,
            'frequencia' => 'semanal',
            'manter_dias' => 30,
            'tipos' => ['completo'],
            'horario' => '02:00',
            'email_notificacao' => '',
            'compressao_nivel' => 6,
            'limite_tamanho_mb' => 1000
        ];
    } catch (Exception $e) {
        return [
            'ativo' => false,
            'frequencia' => 'semanal',
            'manter_dias' => 30,
            'tipos' => ['completo'],
            'horario' => '02:00',
            'email_notificacao' => '',
            'compressao_nivel' => 6,
            'limite_tamanho_mb' => 1000
        ];
    }
}

// Função para salvar configurações de backup
function salvarConfiguracaoBackup($pdo, $config) {
    try {
        $stmt = $pdo->prepare("INSERT INTO configuracoes (config_key, config_value, descricao) VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), data_atualizacao = NOW()");
        $stmt->execute([
            'backup_automatico', 
            json_encode($config), 
            'Configurações do sistema de backup automático'
        ]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $config = [
            'ativo' => isset($_POST['ativo']),
            'frequencia' => $_POST['frequencia'] ?? 'semanal',
            'manter_dias' => max(1, (int)($_POST['manter_dias'] ?? 30)),
            'tipos' => $_POST['tipos'] ?? ['completo'],
            'horario' => $_POST['horario'] ?? '02:00',
            'email_notificacao' => filter_var($_POST['email_notificacao'] ?? '', FILTER_SANITIZE_EMAIL),
            'compressao_nivel' => max(1, min(9, (int)($_POST['compressao_nivel'] ?? 6))),
            'limite_tamanho_mb' => max(10, (int)($_POST['limite_tamanho_mb'] ?? 1000))
        ];
        
        if (salvarConfiguracaoBackup($pdo, $config)) {
            $sucesso = 'Configurações de backup salvas com sucesso!';
        } else {
            $erro = 'Erro ao salvar configurações de backup.';
        }
    } catch (Exception $e) {
        $erro = 'Erro ao processar configurações: ' . $e->getMessage();
    }
}

$config = obterConfiguracaoBackup($pdo);

// Estatísticas de backup
$totalBackups = 0;
$ultimoBackup = null;
$tamanhoTotal = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total, MAX(data_criacao) as ultimo, SUM(tamanho) as tamanho_total FROM logs_backup WHERE status = 'sucesso'");
    $stats = $stmt->fetch();
    if ($stats) {
        $totalBackups = $stats['total'];
        $ultimoBackup = $stats['ultimo'];
        $tamanhoTotal = $stats['tamanho_total'] ?: 0;
    }
} catch (Exception $e) {
    // Ignorar erro se tabela não existir
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
                        <h1><i class="fas fa-cogs"></i> Configurações de Backup</h1>
                        <p class="text-muted">Configure o sistema de backup automático</p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="backup.php">Backup</a></li>
                            <li class="breadcrumb-item active">Configurações</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($sucesso) ?>
                </div>
                <?php endif; ?>

                <!-- Estatísticas Rápidas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $totalBackups ?></h3>
                                <p>Backups Realizados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-archive"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box <?= $config['ativo'] ? 'bg-success' : 'bg-secondary' ?>">
                            <div class="inner">
                                <h3><?= $config['ativo'] ? 'ATIVO' : 'INATIVO' ?></h3>
                                <p>Backup Automático</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-robot"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $ultimoBackup ? date('d/m', strtotime($ultimoBackup)) : 'N/A' ?></h3>
                                <p>Último Backup</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= number_format($tamanhoTotal / 1024 / 1024, 1) ?> MB</h3>
                                <p>Tamanho Total</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-database"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Configurações -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-sliders-h"></i> Configurações de Backup Automático
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="row">
                                        <!-- Configurações Básicas -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title">Configurações Básicas</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="ativo" name="ativo" <?= $config['ativo'] ? 'checked' : '' ?>>
                                                            <label class="custom-control-label" for="ativo">
                                                                <strong>Ativar Backup Automático</strong>
                                                            </label>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            Quando ativado, o sistema fará backups automaticamente conforme configurado
                                                        </small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="frequencia">Frequência dos Backups</label>
                                                        <select class="form-control" id="frequencia" name="frequencia">
                                                            <option value="diario" <?= $config['frequencia'] === 'diario' ? 'selected' : '' ?>>Diário</option>
                                                            <option value="semanal" <?= $config['frequencia'] === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                                                            <option value="mensal" <?= $config['frequencia'] === 'mensal' ? 'selected' : '' ?>>Mensal</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="horario">Horário dos Backups</label>
                                                        <input type="time" class="form-control" id="horario" name="horario" value="<?= htmlspecialchars($config['horario']) ?>">
                                                        <small class="form-text text-muted">
                                                            Recomendado: entre 02:00 e 05:00 (menor tráfego)
                                                        </small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="manter_dias">Manter Backups por (dias)</label>
                                                        <input type="number" class="form-control" id="manter_dias" name="manter_dias" 
                                                               value="<?= $config['manter_dias'] ?>" min="1" max="365">
                                                        <small class="form-text text-muted">
                                                            Backups mais antigos serão removidos automaticamente
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Configurações Avançadas -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h4 class="card-title">Configurações Avançadas</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label>Tipos de Backup</label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tipo_banco" name="tipos[]" value="banco" 
                                                                   <?= in_array('banco', $config['tipos']) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="tipo_banco">
                                                                Backup do Banco de Dados
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tipo_arquivos" name="tipos[]" value="arquivos" 
                                                                   <?= in_array('arquivos', $config['tipos']) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="tipo_arquivos">
                                                                Backup dos Arquivos
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tipo_completo" name="tipos[]" value="completo" 
                                                                   <?= in_array('completo', $config['tipos']) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="tipo_completo">
                                                                Backup Completo (Recomendado)
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="email_notificacao">Email para Notificações</label>
                                                        <input type="email" class="form-control" id="email_notificacao" name="email_notificacao" 
                                                               value="<?= htmlspecialchars($config['email_notificacao']) ?>" 
                                                               placeholder="admin@exemplo.com">
                                                        <small class="form-text text-muted">
                                                            Receba notificações sobre status dos backups (opcional)
                                                        </small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="compressao_nivel">Nível de Compressão (1-9)</label>
                                                        <input type="range" class="form-control-range" id="compressao_nivel" name="compressao_nivel" 
                                                               min="1" max="9" value="<?= $config['compressao_nivel'] ?>" 
                                                               oninput="document.getElementById('compressao_valor').innerText = this.value">
                                                        <small class="form-text text-muted">
                                                            Atual: <span id="compressao_valor"><?= $config['compressao_nivel'] ?></span> 
                                                            (1 = mais rápido, 9 = menor tamanho)
                                                        </small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="limite_tamanho_mb">Limite de Tamanho por Backup (MB)</label>
                                                        <input type="number" class="form-control" id="limite_tamanho_mb" name="limite_tamanho_mb" 
                                                               value="<?= $config['limite_tamanho_mb'] ?>" min="10" max="10000">
                                                        <small class="form-text text-muted">
                                                            Backups maiores que este limite serão divididos
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botões de Ação -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <button type="submit" class="btn btn-success btn-lg">
                                                        <i class="fas fa-save"></i> Salvar Configurações
                                                    </button>
                                                    <a href="backup.php" class="btn btn-secondary btn-lg ml-2">
                                                        <i class="fas fa-arrow-left"></i> Voltar ao Backup
                                                    </a>
                                                    <button type="button" class="btn btn-info btn-lg ml-2" onclick="testarBackup()">
                                                        <i class="fas fa-play"></i> Testar Configuração
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações e Dicas -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle"></i> Como Funciona
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-clock"></i> Agendamento</span>
                                        <h3 class="timeline-header">Sistema verifica configurações</h3>
                                        <div class="timeline-body">
                                            O sistema verifica diariamente se há backups programados para execução
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-database"></i> Execução</span>
                                        <h3 class="timeline-header">Backup é realizado</h3>
                                        <div class="timeline-body">
                                            Os tipos de backup selecionados são executados no horário configurado
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-shield-alt"></i> Verificação</span>
                                        <h3 class="timeline-header">Integridade é verificada</h3>
                                        <div class="timeline-body">
                                            O sistema verifica se o backup foi criado corretamente com hash SHA256
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <span class="time"><i class="fas fa-envelope"></i> Notificação</span>
                                        <h3 class="timeline-header">Status é reportado</h3>
                                        <div class="timeline-body">
                                            Se configurado, um email é enviado com o status do backup
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-lightbulb"></i> Dicas e Recomendações
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-thumbs-up"></i> Melhores Práticas</h5>
                                    <ul class="mb-0">
                                        <li><strong>Frequência:</strong> Para sites ativos, use backup diário</li>
                                        <li><strong>Horário:</strong> Execute entre 02:00-05:00 para menor impacto</li>
                                        <li><strong>Retenção:</strong> Mantenha pelo menos 30 dias de backups</li>
                                        <li><strong>Tipos:</strong> Use backup completo para maior segurança</li>
                                        <li><strong>Monitoramento:</strong> Configure notificações por email</li>
                                    </ul>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Atenção</h5>
                                    <ul class="mb-0">
                                        <li>Backups automáticos requerem configuração do cron job</li>
                                        <li>Verifique se há espaço suficiente no servidor</li>
                                        <li>Teste a restauração periodicamente</li>
                                        <li>Mantenha backups em local externo também</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include '_footer.php'; ?>

<style>
.timeline {
    position: relative;
    margin: 0;
    padding: 0;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
    border-left: 2px solid #dee2e6;
}

.timeline-item:last-child {
    border-left: 2px solid transparent;
}

.timeline-item .time {
    position: absolute;
    left: -60px;
    top: 0;
    font-weight: bold;
    color: #007bff;
    white-space: nowrap;
}

.timeline-header {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
}

.timeline-body {
    color: #6c757d;
    font-size: 14px;
}

.custom-control-label {
    line-height: 1.5;
}

.form-control-range {
    width: 100%;
}

.card-title {
    margin-bottom: 0;
    font-size: 18px;
}
</style>

<script>
function testarBackup() {
    if (confirm('Deseja executar um backup de teste agora? Esta operação pode levar alguns minutos.')) {
        // Redirecionar para backup com parâmetro de teste
        window.open('backup.php?acao=backup_banco&teste=1', '_blank');
    }
}

// Validação do formulário
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const checkboxes = document.querySelectorAll('input[name="tipos[]"]');
    
    form.addEventListener('submit', function(e) {
        let algumMarcado = false;
        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                algumMarcado = true;
            }
        });
        
        if (!algumMarcado) {
            e.preventDefault();
            alert('Selecione pelo menos um tipo de backup.');
            return false;
        }
        
        const ativo = document.getElementById('ativo').checked;
        if (ativo && !confirm('Tem certeza que deseja ativar o backup automático? Certifique-se de que o cron job está configurado corretamente.')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>