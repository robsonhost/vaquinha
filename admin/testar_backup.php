<?php
/**
 * Script de Teste do Sistema de Backup v2.0
 * 
 * Verifica se todas as funcionalidades estão operacionais
 */

require 'db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$resultados = [];

function testar($nome, $funcao) {
    global $resultados;
    try {
        $resultado = $funcao();
        $resultados[] = [
            'teste' => $nome,
            'status' => $resultado ? 'sucesso' : 'falha',
            'mensagem' => $resultado === true ? 'OK' : $resultado
        ];
        return $resultado;
    } catch (Exception $e) {
        $resultados[] = [
            'teste' => $nome,
            'status' => 'erro',
            'mensagem' => $e->getMessage()
        ];
        return false;
    }
}

// Testes
testar('Conexão com Banco de Dados', function() use ($pdo) {
    $pdo->query('SELECT 1');
    return true;
});

testar('Tabela logs_backup existe', function() use ($pdo) {
    $result = $pdo->query("SHOW TABLES LIKE 'logs_backup'");
    return $result->rowCount() > 0;
});

testar('Tabela configuracoes existe', function() use ($pdo) {
    $result = $pdo->query("SHOW TABLES LIKE 'configuracoes'");
    return $result->rowCount() > 0;
});

testar('Diretório de backups', function() {
    $dir = '../backups/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return is_writable($dir) ? true : 'Diretório não é gravável';
});

testar('Extensão ZipArchive', function() {
    return class_exists('ZipArchive') ? true : 'Extensão ZipArchive não encontrada';
});

testar('Função hash_file', function() {
    return function_exists('hash_file') ? true : 'Função hash_file não disponível';
});

testar('Configurações de backup', function() use ($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM configuracoes WHERE config_key = 'backup_automatico'");
    return $stmt->fetchColumn() > 0 ? true : 'Configurações não encontradas';
});

testar('Permissões do cron_backup.php', function() {
    $arquivo = 'cron_backup.php';
    return is_readable($arquivo) ? true : 'Arquivo cron_backup.php não encontrado ou sem permissão';
});

testar('Limites PHP', function() {
    $memory = ini_get('memory_limit');
    $execution = ini_get('max_execution_time');
    
    $memoryBytes = 0;
    if (preg_match('/^(\d+)(.)$/', $memory, $matches)) {
        $value = (int)$matches[1];
        switch(strtoupper($matches[2])) {
            case 'G': $memoryBytes = $value * 1024 * 1024 * 1024; break;
            case 'M': $memoryBytes = $value * 1024 * 1024; break;
            case 'K': $memoryBytes = $value * 1024; break;
            default: $memoryBytes = $value; break;
        }
    }
    
    $warnings = [];
    if ($memoryBytes < 256 * 1024 * 1024) {
        $warnings[] = "Memory limit baixo: {$memory}";
    }
    if ($execution < 300 && $execution != 0) {
        $warnings[] = "Max execution time baixo: {$execution}s";
    }
    
    return empty($warnings) ? true : implode(', ', $warnings);
});

testar('Teste de Backup Mínimo', function() use ($pdo) {
    // Criar um backup de teste pequeno
    try {
        $backup = "-- Teste de backup\n";
        $backup .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
        $backup .= "SELECT 'teste' as resultado;\n";
        
        $filename = 'teste_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $caminho = '../backups/' . $filename;
        
        file_put_contents($caminho, $backup);
        
        if (file_exists($caminho)) {
            $hash = hash_file('sha256', $caminho);
            unlink($caminho); // Limpar arquivo de teste
            return "OK - Hash: " . substr($hash, 0, 8) . "...";
        }
        
        return 'Falha ao criar arquivo de teste';
    } catch (Exception $e) {
        return 'Erro: ' . $e->getMessage();
    }
});

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
                        <h1><i class="fas fa-vial"></i> Teste do Sistema de Backup</h1>
                        <p class="text-muted">Verificação da integridade e funcionamento do sistema</p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="backup.php">Backup</a></li>
                            <li class="breadcrumb-item active">Teste</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-check"></i> Resultados dos Testes
                                </h3>
                                <div class="card-tools">
                                    <a href="?" class="btn btn-sm btn-primary">
                                        <i class="fas fa-redo"></i> Executar Novamente
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php 
                                $sucessos = 0;
                                $falhas = 0;
                                $erros = 0;
                                
                                foreach($resultados as $resultado) {
                                    switch($resultado['status']) {
                                        case 'sucesso': $sucessos++; break;
                                        case 'falha': $falhas++; break;
                                        case 'erro': $erros++; break;
                                    }
                                }
                                ?>
                                
                                <!-- Resumo -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="info-box bg-success">
                                            <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Sucessos</span>
                                                <span class="info-box-number"><?= $sucessos ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-warning">
                                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Falhas</span>
                                                <span class="info-box-number"><?= $falhas ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-danger">
                                            <span class="info-box-icon"><i class="fas fa-times"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Erros</span>
                                                <span class="info-box-number"><?= $erros ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detalhes dos Testes -->
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="40%">Teste</th>
                                                <th width="15%">Status</th>
                                                <th width="45%">Detalhes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($resultados as $resultado): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($resultado['teste']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $badgeClass = '';
                                                    $icon = '';
                                                    switch($resultado['status']) {
                                                        case 'sucesso':
                                                            $badgeClass = 'badge-success';
                                                            $icon = 'fas fa-check';
                                                            break;
                                                        case 'falha':
                                                            $badgeClass = 'badge-warning';
                                                            $icon = 'fas fa-exclamation-triangle';
                                                            break;
                                                        case 'erro':
                                                            $badgeClass = 'badge-danger';
                                                            $icon = 'fas fa-times';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>">
                                                        <i class="<?= $icon ?>"></i>
                                                        <?= ucfirst($resultado['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($resultado['mensagem'] === 'OK'): ?>
                                                        <span class="text-success">✅ Funcionando corretamente</span>
                                                    <?php else: ?>
                                                        <code><?= htmlspecialchars($resultado['mensagem']) ?></code>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Recomendações -->
                                <div class="mt-4">
                                    <?php if($erros > 0 || $falhas > 0): ?>
                                    <div class="alert alert-warning">
                                        <h5><i class="fas fa-exclamation-triangle"></i> Ações Recomendadas</h5>
                                        <ul class="mb-0">
                                            <?php if($erros > 0): ?>
                                            <li>Existem <strong><?= $erros ?> erro(s)</strong> que precisam ser corrigidos</li>
                                            <?php endif; ?>
                                            <?php if($falhas > 0): ?>
                                            <li>Existem <strong><?= $falhas ?> falha(s)</strong> que podem afetar o funcionamento</li>
                                            <?php endif; ?>
                                            <li>Execute o script de atualização do banco: <code>atualizar_banco_backup.sql</code></li>
                                            <li>Verifique as permissões do diretório de backups</li>
                                            <li>Consulte a documentação: <code>README_BACKUP.md</code></li>
                                        </ul>
                                    </div>
                                    <?php else: ?>
                                    <div class="alert alert-success">
                                        <h5><i class="fas fa-check-circle"></i> Sistema Funcionando Perfeitamente!</h5>
                                        <p class="mb-0">
                                            Todos os testes passaram com sucesso. O sistema de backup está pronto para uso.
                                            Você pode <a href="backup.php">fazer um backup</a> ou 
                                            <a href="configuracoes_backup.php">configurar backups automáticos</a>.
                                        </p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Informações do Sistema -->
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <h5>Informações do Sistema</h5>
                                        <ul class="list-unstyled">
                                            <li><strong>PHP Version:</strong> <?= PHP_VERSION ?></li>
                                            <li><strong>Memory Limit:</strong> <?= ini_get('memory_limit') ?></li>
                                            <li><strong>Max Execution Time:</strong> <?= ini_get('max_execution_time') ?>s</li>
                                            <li><strong>Upload Max Size:</strong> <?= ini_get('upload_max_filesize') ?></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Extensões Necessárias</h5>
                                        <ul class="list-unstyled">
                                            <li>
                                                <i class="fas fa-<?= class_exists('ZipArchive') ? 'check text-success' : 'times text-danger' ?>"></i>
                                                ZipArchive
                                            </li>
                                            <li>
                                                <i class="fas fa-<?= function_exists('hash_file') ? 'check text-success' : 'times text-danger' ?>"></i>
                                                hash_file
                                            </li>
                                            <li>
                                                <i class="fas fa-<?= extension_loaded('pdo') ? 'check text-success' : 'times text-danger' ?>"></i>
                                                PDO
                                            </li>
                                            <li>
                                                <i class="fas fa-<?= extension_loaded('pdo_mysql') ? 'check text-success' : 'times text-danger' ?>"></i>
                                                PDO MySQL
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-center">
                                    <a href="backup.php" class="btn btn-primary">
                                        <i class="fas fa-database"></i> Ir para Backup
                                    </a>
                                    <a href="configuracoes_backup.php" class="btn btn-secondary">
                                        <i class="fas fa-cogs"></i> Configurações
                                    </a>
                                    <a href="../README_BACKUP.md" class="btn btn-info" target="_blank">
                                        <i class="fas fa-book"></i> Documentação
                                    </a>
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
.info-box {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.table th {
    border-top: none;
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.9em;
    padding: 0.4em 0.8em;
}

code {
    font-size: 0.9em;
    color: #e83e8c;
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>