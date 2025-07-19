<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Função para registrar logs de backup
function registrarLogBackup($pdo, $tipo, $status, $arquivo = null, $tamanho = null, $erro = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_backup (tipo, status, arquivo, tamanho, erro, data_criacao, admin_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([$tipo, $status, $arquivo, $tamanho, $erro, $_SESSION['admin_id']]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Erro ao registrar log de backup: " . $e->getMessage());
        return false;
    }
}

// Função para verificar integridade do backup
function verificarIntegridade($arquivo) {
    if (!file_exists($arquivo)) {
        return false;
    }
    
    $hash = hash_file('sha256', $arquivo);
    $tamanho = filesize($arquivo);
    
    return ['hash' => $hash, 'tamanho' => $tamanho];
}

// Função para limpar backups antigos
function limparBackupsAntigos($dias = 30) {
    $diretorio = '../backups/';
    if (!is_dir($diretorio)) return;
    
    $arquivos = glob($diretorio . '*');
    $agora = time();
    $limiteTempo = $agora - ($dias * 24 * 60 * 60);
    
    foreach ($arquivos as $arquivo) {
        if (is_file($arquivo) && filemtime($arquivo) < $limiteTempo) {
            unlink($arquivo);
        }
    }
}

// Função melhorada para criar backup do banco
function criarBackupBanco($pdo) {
    $tables = [];
    $result = $pdo->query('SHOW TABLES');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $backup = "-- Backup do banco de dados\n";
    $backup .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
    $backup .= "-- Sistema: Vaquinha Online\n";
    $backup .= "-- Versão: 2.0\n";
    $backup .= "-- Hash de verificação será adicionado no final\n\n";
    
    $backup .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $backup .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $backup .= "SET AUTOCOMMIT = 0;\n";
    $backup .= "START TRANSACTION;\n\n";
    
    foreach ($tables as $table) {
        // Estrutura da tabela
        $result = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch(PDO::FETCH_NUM);
        $backup .= "DROP TABLE IF EXISTS `$table`;\n";
        $backup .= $row[1] . ";\n\n";
        
        // Dados da tabela com prepared statements para segurança
        $stmt = $pdo->prepare("SELECT * FROM `$table`");
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns = array_keys($row);
            $values = array_values($row);
            
            $backup .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (";
            
            foreach ($values as $value) {
                if ($value === null) {
                    $backup .= "NULL,";
                } else {
                    $backup .= "'" . addslashes($value) . "',";
                }
            }
            $backup = rtrim($backup, ',');
            $backup .= ");\n";
        }
        $backup .= "\n";
    }
    
    $backup .= "COMMIT;\n";
    $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    // Adicionar hash de verificação
    $hash = hash('sha256', $backup);
    $backup .= "\n-- Hash SHA256: " . $hash . "\n";
    
    return $backup;
}

// Função melhorada para criar backup dos arquivos
function criarBackupArquivos($pasta) {
    $zip = new ZipArchive();
    $filename = 'backup_arquivos_' . date('Y-m-d_H-i-s') . '.zip';
    $caminho = '../backups/' . $filename;
    
    if (!is_dir('../backups')) {
        mkdir('../backups', 0755, true);
    }
    
    if ($zip->open($caminho, ZipArchive::CREATE) !== TRUE) {
        throw new Exception('Não foi possível criar o arquivo ZIP');
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($pasta, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    $arquivosAdicionados = 0;
    foreach ($iterator as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(realpath($pasta)) + 1);
            
            // Filtrar arquivos por extensão se necessário
            $extensao = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx'];
            
            if (in_array($extensao, $extensoesPermitidas)) {
                $zip->addFile($filePath, $relativePath);
                $arquivosAdicionados++;
            }
        }
    }
    
    // Adicionar informações do backup
    $info = "Backup de Arquivos\n";
    $info .= "Data: " . date('Y-m-d H:i:s') . "\n";
    $info .= "Arquivos incluídos: " . $arquivosAdicionados . "\n";
    $info .= "Pasta origem: " . realpath($pasta) . "\n";
    
    $zip->addFromString('backup_info.txt', $info);
    $zip->close();
    
    return $filename;
}

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
            'tipos' => ['completo']
        ];
    } catch (Exception $e) {
        return [
            'ativo' => false,
            'frequencia' => 'semanal',
            'manter_dias' => 30,
            'tipos' => ['completo']
        ];
    }
}

// Criar tabela de logs de backup se não existir
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS logs_backup (
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
    )");
} catch (Exception $e) {
    // Tabela já existe ou erro na criação
    error_log("Erro ao criar tabela logs_backup: " . $e->getMessage());
}

// Processar ações
if (isset($_GET['acao'])) {
    $logId = null;
    try {
        switch ($_GET['acao']) {
            case 'backup_banco':
                $logId = registrarLogBackup($pdo, 'banco', 'em_andamento');
                
                $backup = criarBackupBanco($pdo);
                $filename = 'backup_banco_' . date('Y-m-d_H-i-s') . '.sql';
                
                if (!is_dir('../backups')) {
                    mkdir('../backups', 0755, true);
                }
                
                $caminho = '../backups/' . $filename;
                file_put_contents($caminho, $backup);
                
                $integridade = verificarIntegridade($caminho);
                
                // Atualizar log com sucesso
                if ($logId) {
                    $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'sucesso', arquivo = ?, tamanho = ?, hash_verificacao = ? WHERE id = ?");
                    $stmt->execute([$filename, $integridade['tamanho'], $integridade['hash'], $logId]);
                }
                
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($caminho));
                readfile($caminho);
                
                // Limpar arquivo temporário após download
                register_shutdown_function(function() use ($caminho) {
                    if (file_exists($caminho)) {
                        unlink($caminho);
                    }
                });
                exit;
                
            case 'backup_arquivos':
                $logId = registrarLogBackup($pdo, 'arquivos', 'em_andamento');
                
                $filename = criarBackupArquivos('../images');
                $caminho = '../backups/' . $filename;
                
                $integridade = verificarIntegridade($caminho);
                
                // Atualizar log com sucesso
                if ($logId) {
                    $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'sucesso', arquivo = ?, tamanho = ?, hash_verificacao = ? WHERE id = ?");
                    $stmt->execute([$filename, $integridade['tamanho'], $integridade['hash'], $logId]);
                }
                
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($caminho));
                readfile($caminho);
                
                register_shutdown_function(function() use ($caminho) {
                    if (file_exists($caminho)) {
                        unlink($caminho);
                    }
                });
                exit;
                
            case 'backup_completo':
                $logId = registrarLogBackup($pdo, 'completo', 'em_andamento');
                
                // Backup do banco
                $backup = criarBackupBanco($pdo);
                $sql_filename = 'backup_banco_' . date('Y-m-d_H-i-s') . '.sql';
                
                if (!is_dir('../backups')) {
                    mkdir('../backups', 0755, true);
                }
                
                file_put_contents('../backups/' . $sql_filename, $backup);
                
                // Backup dos arquivos
                $zip_filename = criarBackupArquivos('../images');
                
                // Criar ZIP com ambos
                $zip = new ZipArchive();
                $final_filename = 'backup_completo_' . date('Y-m-d_H-i-s') . '.zip';
                $caminho_final = '../backups/' . $final_filename;
                
                if ($zip->open($caminho_final, ZipArchive::CREATE) !== TRUE) {
                    throw new Exception('Não foi possível criar o arquivo ZIP final');
                }
                
                $zip->addFile('../backups/' . $sql_filename, 'banco/' . $sql_filename);
                $zip->addFile('../backups/' . $zip_filename, 'arquivos/' . $zip_filename);
                
                // Adicionar informações do backup completo
                $info = "Backup Completo\n";
                $info .= "Data: " . date('Y-m-d H:i:s') . "\n";
                $info .= "Banco: " . $sql_filename . "\n";
                $info .= "Arquivos: " . $zip_filename . "\n";
                $info .= "Sistema: Vaquinha Online v2.0\n";
                
                $zip->addFromString('backup_info.txt', $info);
                $zip->close();
                
                $integridade = verificarIntegridade($caminho_final);
                
                // Limpar arquivos temporários
                unlink('../backups/' . $sql_filename);
                unlink('../backups/' . $zip_filename);
                
                // Atualizar log com sucesso
                if ($logId) {
                    $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'sucesso', arquivo = ?, tamanho = ?, hash_verificacao = ? WHERE id = ?");
                    $stmt->execute([$final_filename, $integridade['tamanho'], $integridade['hash'], $logId]);
                }
                
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $final_filename . '"');
                header('Content-Length: ' . filesize($caminho_final));
                readfile($caminho_final);
                
                register_shutdown_function(function() use ($caminho_final) {
                    if (file_exists($caminho_final)) {
                        unlink($caminho_final);
                    }
                });
                exit;
                
            case 'limpar_antigos':
                $diasManter = isset($_POST['dias']) ? (int)$_POST['dias'] : 30;
                if ($diasManter < 1) $diasManter = 30;
                
                limparBackupsAntigos($diasManter);
                
                // Limpar logs antigos também
                $stmt = $pdo->prepare("DELETE FROM logs_backup WHERE data_criacao < DATE_SUB(NOW(), INTERVAL ? DAY)");
                $stmt->execute([$diasManter]);
                
                $sucesso = "Backups antigos (mais de {$diasManter} dias) foram removidos com sucesso.";
                break;
        }
    } catch (Exception $e) {
        $erro = 'Erro ao criar backup: ' . $e->getMessage();
        
        // Atualizar log com erro
        if ($logId) {
            $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'erro', erro = ? WHERE id = ?");
            $stmt->execute([$e->getMessage(), $logId]);
        }
    }
}

// Executar limpeza automática se configurado
$configBackup = obterConfiguracaoBackup($pdo);
if ($configBackup['ativo']) {
    limparBackupsAntigos($configBackup['manter_dias']);
}

// Estatísticas do sistema
$totalCampanhas = $pdo->query('SELECT COUNT(*) FROM campanhas')->fetchColumn();
$totalDoacoes = $pdo->query('SELECT COUNT(*) FROM doacoes')->fetchColumn();
$totalUsuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$totalArrecadado = $pdo->query('SELECT SUM(arrecadado) FROM campanhas')->fetchColumn() ?: 0;

// Tamanho do banco
$tamanhoBanco = 0;
$result = $pdo->query("SELECT 
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'tamanho_mb'
    FROM information_schema.tables 
    WHERE table_schema = DATABASE()");
$tamanhoBanco = $result->fetchColumn();

// Tamanho dos arquivos
$tamanhoArquivos = 0;
if (is_dir('../images')) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator('../images', RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $file) {
        $tamanhoArquivos += $file->getSize();
    }
    $tamanhoArquivos = round($tamanhoArquivos / 1024 / 1024, 2); // MB
}

// Últimos backups reais do banco
$ultimosBackups = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM logs_backup ORDER BY data_criacao DESC LIMIT 10");
    $stmt->execute();
    $ultimosBackups = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Se não conseguir buscar, usar array vazio
    $ultimosBackups = [];
}

// Estatísticas de backup
$totalBackups = count($ultimosBackups);
$backupsSucesso = count(array_filter($ultimosBackups, function($b) { return $b['status'] === 'sucesso'; }));
$ultimoBackup = !empty($ultimosBackups) ? $ultimosBackups[0] : null;
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
                        <h1><i class="fas fa-database"></i> Sistema de Backup</h1>
                        <p class="text-muted">Versão 2.0 - Sistema melhorado com logs e verificação de integridade</p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Backup</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?></div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($sucesso) ?></div><?php endif; ?>
                
                <!-- Status do Sistema de Backup -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= number_format($totalBackups, 0, ',', '.') ?></h3>
                                <p>Total de Backups</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-archive"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= number_format($backupsSucesso, 0, ',', '.') ?></h3>
                                <p>Backups com Sucesso</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $ultimoBackup ? date('d/m', strtotime($ultimoBackup['data_criacao'])) : 'N/A' ?></h3>
                                <p>Último Backup</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box <?= $configBackup['ativo'] ? 'bg-primary' : 'bg-secondary' ?>">
                            <div class="inner">
                                <h3><?= $configBackup['ativo'] ? 'ON' : 'OFF' ?></h3>
                                <p>Backup Automático</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-robot"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas do Sistema -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-info">
                            <div class="inner">
                                <h3><?= number_format($totalCampanhas, 0, ',', '.') ?></h3>
                                <p>Campanhas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3><?= number_format($totalDoacoes, 0, ',', '.') ?></h3>
                                <p>Doações</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-heart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3><?= number_format($totalUsuarios, 0, ',', '.') ?></h3>
                                <p>Usuários</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-danger">
                            <div class="inner">
                                <h3>R$ <?= number_format($totalArrecadado, 0, ',', '.') ?></h3>
                                <p>Arrecadado</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações de Armazenamento -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-database"></i> Banco de Dados
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-server"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tamanho do Banco</span>
                                        <span class="info-box-number"><?= $tamanhoBanco ?> MB</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?= min(($tamanhoBanco / 100) * 100, 100) ?>%"></div>
                                        </div>
                                        <span class="progress-description">
                                            <?= $totalCampanhas ?> campanhas, <?= $totalDoacoes ?> doações
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-images"></i> Arquivos de Mídia
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-file-image"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tamanho dos Arquivos</span>
                                        <span class="info-box-number"><?= $tamanhoArquivos ?> MB</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: <?= min(($tamanhoArquivos / 500) * 100, 100) ?>%"></div>
                                        </div>
                                        <span class="progress-description">
                                            Imagens de campanhas e uploads
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opções de Backup -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-download"></i> Criar Backup
                                </h3>
                                <div class="card-tools">
                                    <a href="configuracoes_backup.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-cogs"></i> Configurações
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#modalLimparBackups">
                                        <i class="fas fa-trash"></i> Limpar Antigos
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="backup-option text-center p-4 border rounded h-100">
                                            <i class="fas fa-database fa-3x text-info mb-3"></i>
                                            <h4>Backup do Banco</h4>
                                            <p class="text-muted">Exporta todos os dados do banco de dados em formato SQL com verificação de integridade</p>
                                            <p><strong>Tamanho estimado:</strong> <?= $tamanhoBanco ?> MB</p>
                                            <a href="?acao=backup_banco" class="btn btn-info btn-lg">
                                                <i class="fas fa-download"></i> Baixar Backup do Banco
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="backup-option text-center p-4 border rounded h-100">
                                            <i class="fas fa-images fa-3x text-success mb-3"></i>
                                            <h4>Backup dos Arquivos</h4>
                                            <p class="text-muted">Exporta todas as imagens e arquivos do sistema com filtragem por tipo</p>
                                            <p><strong>Tamanho estimado:</strong> <?= $tamanhoArquivos ?> MB</p>
                                            <a href="?acao=backup_arquivos" class="btn btn-success btn-lg">
                                                <i class="fas fa-download"></i> Baixar Backup dos Arquivos
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="backup-option text-center p-4 border rounded bg-primary text-white h-100">
                                            <i class="fas fa-archive fa-3x text-white mb-3"></i>
                                            <h4>Backup Completo</h4>
                                            <p>Exporta banco de dados e arquivos em um único arquivo ZIP com verificação de integridade</p>
                                            <p><strong>Tamanho estimado:</strong> <?= $tamanhoBanco + $tamanhoArquivos ?> MB</p>
                                            <a href="?acao=backup_completo" class="btn btn-light btn-lg">
                                                <i class="fas fa-download"></i> Baixar Backup Completo
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Histórico de Backups -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history"></i> Histórico de Backups
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($ultimosBackups)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Nenhum backup foi realizado ainda. Crie seu primeiro backup usando as opções acima.
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Data/Hora</th>
                                                <th>Arquivo</th>
                                                <th>Tamanho</th>
                                                <th>Status</th>
                                                <th>Hash</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimosBackups as $backup): ?>
                                            <tr>
                                                <td>
                                                    <?php
                                                    $icon = '';
                                                    $color = '';
                                                    switch ($backup['tipo']) {
                                                        case 'completo':
                                                            $icon = 'fas fa-archive';
                                                            $color = 'primary';
                                                            break;
                                                        case 'banco':
                                                            $icon = 'fas fa-database';
                                                            $color = 'info';
                                                            break;
                                                        case 'arquivos':
                                                            $icon = 'fas fa-images';
                                                            $color = 'success';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?= $icon ?> text-<?= $color ?>"></i>
                                                    <strong><?= ucfirst($backup['tipo']) ?></strong>
                                                </td>
                                                <td>
                                                    <?= date('d/m/Y H:i:s', strtotime($backup['data_criacao'])) ?>
                                                    <br><small class="text-muted"><?= date('D', strtotime($backup['data_criacao'])) ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($backup['arquivo']): ?>
                                                        <code><?= htmlspecialchars($backup['arquivo']) ?></code>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($backup['tamanho']): ?>
                                                        <?= number_format($backup['tamanho'] / 1024 / 1024, 2) ?> MB
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = '';
                                                    $statusIcon = '';
                                                    switch ($backup['status']) {
                                                        case 'sucesso':
                                                            $statusClass = 'badge-success';
                                                            $statusIcon = 'fas fa-check';
                                                            break;
                                                        case 'erro':
                                                            $statusClass = 'badge-danger';
                                                            $statusIcon = 'fas fa-times';
                                                            break;
                                                        case 'em_andamento':
                                                            $statusClass = 'badge-warning';
                                                            $statusIcon = 'fas fa-clock';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <i class="<?= $statusIcon ?>"></i> <?= ucfirst($backup['status']) ?>
                                                    </span>
                                                    <?php if ($backup['erro']): ?>
                                                        <br><small class="text-danger" title="<?= htmlspecialchars($backup['erro']) ?>">
                                                            <i class="fas fa-exclamation-triangle"></i> Ver erro
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($backup['hash_verificacao']): ?>
                                                        <small class="text-monospace" title="<?= htmlspecialchars($backup['hash_verificacao']) ?>">
                                                            <?= substr($backup['hash_verificacao'], 0, 8) ?>...
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dicas de Segurança -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-shield-alt"></i> Dicas de Segurança e Melhorias
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <h5><i class="fas fa-info-circle"></i> Recomendações para Backup</h5>
                                            <ul class="mb-0">
                                                <li>Faça backups regulares (recomendado: semanalmente)</li>
                                                <li>Armazene os backups em local seguro e separado do servidor</li>
                                                <li>Teste a restauração dos backups periodicamente</li>
                                                <li>Mantenha múltiplas versões de backup</li>
                                                <li>Use criptografia para backups sensíveis</li>
                                                <li>Documente o processo de restauração</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-success">
                                            <h5><i class="fas fa-check-circle"></i> Melhorias Implementadas</h5>
                                            <ul class="mb-0">
                                                <li><strong>Logs reais:</strong> Histórico real de backups</li>
                                                <li><strong>Verificação de integridade:</strong> Hash SHA256</li>
                                                <li><strong>Segurança:</strong> Prepared statements</li>
                                                <li><strong>Limpeza automática:</strong> Remove backups antigos</li>
                                                <li><strong>Filtros de arquivo:</strong> Apenas arquivos permitidos</li>
                                                <li><strong>Tratamento de erros:</strong> Logs de erro detalhados</li>
                                            </ul>
                                        </div>
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

<!-- Modal para Limpar Backups Antigos -->
<div class="modal fade" id="modalLimparBackups" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-trash"></i> Limpar Backups Antigos
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="?acao=limpar_antigos">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="dias">Manter backups dos últimos (dias):</label>
                        <input type="number" class="form-control" id="dias" name="dias" value="30" min="1" max="365">
                        <small class="form-text text-muted">
                            Backups mais antigos que este período serão removidos permanentemente.
                        </small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atenção:</strong> Esta ação não pode ser desfeita. Certifique-se de que possui backups seguros em outro local.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Limpar Backups Antigos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>

<style>
.backup-option {
    transition: all 0.3s ease;
}

.backup-option:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.backup-option.bg-primary {
    background: linear-gradient(135deg, #007bff, #0056b3) !important;
}

.small-box.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8, #138496) !important;
}

.small-box.bg-gradient-success {
    background: linear-gradient(135deg, #28a745, #1e7e34) !important;
}

.small-box.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800) !important;
}

.small-box.bg-gradient-danger {
    background: linear-gradient(135deg, #dc3545, #bd2130) !important;
}

.text-monospace {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.table th {
    border-top: none;
}

.badge {
    font-size: 0.8em;
}

.progress {
    height: 8px;
}
</style>

<script>
// Adicionar confirmação para ações de backup
document.addEventListener('DOMContentLoaded', function() {
    const backupLinks = document.querySelectorAll('a[href*="acao=backup"]');
    
    backupLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const tipo = this.href.includes('completo') ? 'completo' : 
                        this.href.includes('banco') ? 'do banco' : 'dos arquivos';
            
            if (!confirm(`Tem certeza que deseja criar o backup ${tipo}? Esta operação pode levar alguns minutos.`)) {
                e.preventDefault();
            } else {
                // Mostrar indicador de loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gerando backup...';
                this.classList.add('disabled');
            }
        });
    });
});
</script> 