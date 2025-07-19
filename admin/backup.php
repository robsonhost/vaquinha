<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Função para criar backup do banco
function criarBackupBanco($pdo) {
    $tables = [];
    $result = $pdo->query('SHOW TABLES');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $backup = "-- Backup do banco de dados\n";
    $backup .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
    $backup .= "-- Sistema: Vaquinha Online\n\n";
    
    foreach ($tables as $table) {
        // Estrutura da tabela
        $result = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch(PDO::FETCH_NUM);
        $backup .= "\n\n" . $row[1] . ";\n\n";
        
        // Dados da tabela
        $result = $pdo->query("SELECT * FROM `$table`");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $backup .= "INSERT INTO `$table` VALUES (";
            foreach ($row as $data) {
                $backup .= "'" . addslashes($data) . "',";
            }
            $backup = rtrim($backup, ',');
            $backup .= ");\n";
        }
    }
    
    return $backup;
}

// Função para criar backup dos arquivos
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
        new RecursiveDirectoryIterator($pasta),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($pasta) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    $zip->close();
    return $filename;
}

// Processar ações
if (isset($_GET['acao'])) {
    try {
        switch ($_GET['acao']) {
            case 'backup_banco':
                $backup = criarBackupBanco($pdo);
                $filename = 'backup_banco_' . date('Y-m-d_H-i-s') . '.sql';
                
                if (!is_dir('../backups')) {
                    mkdir('../backups', 0755, true);
                }
                
                file_put_contents('../backups/' . $filename, $backup);
                
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize('../backups/' . $filename));
                readfile('../backups/' . $filename);
                unlink('../backups/' . $filename);
                exit;
                
            case 'backup_arquivos':
                $filename = criarBackupArquivos('../images');
                
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize('../backups/' . $filename));
                readfile('../backups/' . $filename);
                unlink('../backups/' . $filename);
                exit;
                
            case 'backup_completo':
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
                
                $zip->addFile('../backups/' . $sql_filename, $sql_filename);
                $zip->addFile('../backups/' . $zip_filename, 'arquivos/' . $zip_filename);
                $zip->close();
                
                // Limpar arquivos temporários
                unlink('../backups/' . $sql_filename);
                unlink('../backups/' . $zip_filename);
                
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $final_filename . '"');
                header('Content-Length: ' . filesize($caminho_final));
                readfile($caminho_final);
                unlink($caminho_final);
                exit;
        }
    } catch (Exception $e) {
        $erro = 'Erro ao criar backup: ' . $e->getMessage();
    }
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
        new RecursiveDirectoryIterator('../images'),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $file) {
        $tamanhoArquivos += $file->getSize();
    }
    $tamanhoArquivos = round($tamanhoArquivos / 1024 / 1024, 2); // MB
}

// Últimos backups (simulado)
$ultimosBackups = [
    ['tipo' => 'Completo', 'data' => date('d/m/Y H:i', strtotime('-2 days')), 'tamanho' => '15.2 MB'],
    ['tipo' => 'Banco', 'data' => date('d/m/Y H:i', strtotime('-5 days')), 'tamanho' => '2.1 MB'],
    ['tipo' => 'Arquivos', 'data' => date('d/m/Y H:i', strtotime('-1 week')), 'tamanho' => '13.1 MB']
];
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
                <?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"><?= $sucesso ?></div><?php endif; ?>
                
                <!-- Estatísticas do Sistema -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
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
                        <div class="small-box bg-success">
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
                        <div class="small-box bg-warning">
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
                        <div class="small-box bg-danger">
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
                                            <div class="progress-bar" style="width: 70%"></div>
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
                                            <div class="progress-bar bg-success" style="width: 45%"></div>
                                        </div>
                                        <span class="progress-description">
                                            Imagens de campanhas e logos
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
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="backup-option text-center p-4 border rounded">
                                            <i class="fas fa-database fa-3x text-info mb-3"></i>
                                            <h4>Backup do Banco</h4>
                                            <p class="text-muted">Exporta todos os dados do banco de dados em formato SQL</p>
                                            <p><strong>Tamanho estimado:</strong> <?= $tamanhoBanco ?> MB</p>
                                            <a href="?acao=backup_banco" class="btn btn-info btn-lg">
                                                <i class="fas fa-download"></i> Baixar Backup do Banco
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="backup-option text-center p-4 border rounded">
                                            <i class="fas fa-images fa-3x text-success mb-3"></i>
                                            <h4>Backup dos Arquivos</h4>
                                            <p class="text-muted">Exporta todas as imagens e arquivos do sistema</p>
                                            <p><strong>Tamanho estimado:</strong> <?= $tamanhoArquivos ?> MB</p>
                                            <a href="?acao=backup_arquivos" class="btn btn-success btn-lg">
                                                <i class="fas fa-download"></i> Baixar Backup dos Arquivos
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="backup-option text-center p-4 border rounded bg-primary text-white">
                                            <i class="fas fa-archive fa-3x text-white mb-3"></i>
                                            <h4>Backup Completo</h4>
                                            <p>Exporta banco de dados e arquivos em um único arquivo ZIP</p>
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
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Data</th>
                                                <th>Tamanho</th>
                                                <th>Status</th>
                                                <th>Ações</th>
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
                                                        case 'Completo':
                                                            $icon = 'fas fa-archive';
                                                            $color = 'primary';
                                                            break;
                                                        case 'Banco':
                                                            $icon = 'fas fa-database';
                                                            $color = 'info';
                                                            break;
                                                        case 'Arquivos':
                                                            $icon = 'fas fa-images';
                                                            $color = 'success';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?= $icon ?> text-<?= $color ?>"></i>
                                                    <strong><?= $backup['tipo'] ?></strong>
                                                </td>
                                                <td><?= $backup['data'] ?></td>
                                                <td><?= $backup['tamanho'] ?></td>
                                                <td>
                                                    <span class="badge badge-success">Concluído</span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                                        <i class="fas fa-download"></i> Baixar
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" disabled>
                                                        <i class="fas fa-trash"></i> Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dicas de Segurança -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-shield-alt"></i> Dicas de Segurança
                                </h3>
                            </div>
                            <div class="card-body">
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
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php include '_footer.php'; ?>

<style>
.backup-option {
    transition: all 0.3s ease;
    height: 100%;
}

.backup-option:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.backup-option.bg-primary {
    background: linear-gradient(135deg, #007bff, #0056b3) !important;
}
</style> 