<?php
/**
 * Script de Backup Automático via Cron Job
 * 
 * Este script deve ser executado pelo cron para realizar backups automáticos
 * Exemplo de configuração no crontab:
 * 0 2 * * * /usr/bin/php /caminho/para/admin/cron_backup.php
 */

// Definir que é execução via CLI
if (php_sapi_name() !== 'cli') {
    die('Este script deve ser executado apenas via linha de comando (CLI)');
}

// Configurações básicas
ini_set('max_execution_time', 0); // Sem limite de tempo para CLI
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

// Incluir arquivos necessários
require_once __DIR__ . '/db.php';

// Funções de backup (copiadas do backup.php)
function registrarLogBackup($pdo, $tipo, $status, $arquivo = null, $tamanho = null, $erro = null, $admin_id = null) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_backup (tipo, status, arquivo, tamanho, erro, data_criacao, admin_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->execute([$tipo, $status, $arquivo, $tamanho, $erro, $admin_id]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Erro ao registrar log de backup: " . $e->getMessage());
        return false;
    }
}

function verificarIntegridade($arquivo) {
    if (!file_exists($arquivo)) {
        return false;
    }
    
    $hash = hash_file('sha256', $arquivo);
    $tamanho = filesize($arquivo);
    
    return ['hash' => $hash, 'tamanho' => $tamanho];
}

function criarBackupBanco($pdo) {
    $tables = [];
    $result = $pdo->query('SHOW TABLES');
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $backup = "-- Backup automático do banco de dados\n";
    $backup .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
    $backup .= "-- Sistema: Vaquinha Online v2.0\n";
    $backup .= "-- Executado via cron job\n\n";
    
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
        
        // Dados da tabela
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

function criarBackupArquivos($pasta) {
    $zip = new ZipArchive();
    $filename = 'backup_auto_arquivos_' . date('Y-m-d_H-i-s') . '.zip';
    $caminho = __DIR__ . '/../backups/' . $filename;
    
    if (!is_dir(__DIR__ . '/../backups/')) {
        mkdir(__DIR__ . '/../backups/', 0755, true);
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
            
            // Filtrar arquivos por extensão
            $extensao = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx'];
            
            if (in_array($extensao, $extensoesPermitidas)) {
                $zip->addFile($filePath, $relativePath);
                $arquivosAdicionados++;
            }
        }
    }
    
    // Adicionar informações do backup
    $info = "Backup Automático de Arquivos\n";
    $info .= "Data: " . date('Y-m-d H:i:s') . "\n";
    $info .= "Arquivos incluídos: " . $arquivosAdicionados . "\n";
    $info .= "Pasta origem: " . realpath($pasta) . "\n";
    $info .= "Executado via cron job\n";
    
    $zip->addFromString('backup_info.txt', $info);
    $zip->close();
    
    return $filename;
}

function enviarEmailNotificacao($email, $assunto, $mensagem) {
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $headers = "From: sistema@vaquinha.com\r\n";
    $headers .= "Reply-To: sistema@vaquinha.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $htmlMensagem = "
    <html>
    <body>
        <h2>Sistema de Backup - Vaquinha Online</h2>
        <p>{$mensagem}</p>
        <hr>
        <p><small>Este é um email automático. Não responda.</small></p>
    </body>
    </html>";
    
    return mail($email, $assunto, $htmlMensagem, $headers);
}

function limparBackupsAntigos($dias = 30) {
    $diretorio = __DIR__ . '/../backups/';
    if (!is_dir($diretorio)) return;
    
    $arquivos = glob($diretorio . '*');
    $agora = time();
    $limiteTempo = $agora - ($dias * 24 * 60 * 60);
    $removidos = 0;
    
    foreach ($arquivos as $arquivo) {
        if (is_file($arquivo) && filemtime($arquivo) < $limiteTempo) {
            if (unlink($arquivo)) {
                $removidos++;
            }
        }
    }
    
    return $removidos;
}

// Função principal
function executarBackupAutomatico() {
    global $pdo;
    
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup automático...\n";
    
    try {
        // Obter configurações
        $stmt = $pdo->prepare("SELECT config_value FROM configuracoes WHERE config_key = ?");
        $stmt->execute(['backup_automatico']);
        $result = $stmt->fetch();
        
        if (!$result) {
            echo "Nenhuma configuração de backup encontrada.\n";
            return;
        }
        
        $config = json_decode($result['config_value'], true);
        
        if (!$config['ativo']) {
            echo "Backup automático está desativado.\n";
            return;
        }
        
        // Verificar se é hora de fazer backup
        $agora = new DateTime();
        $horario = new DateTime($config['horario']);
        
        // Verificar frequência
        $deveFazerBackup = false;
        
        switch ($config['frequencia']) {
            case 'diario':
                $deveFazerBackup = true;
                break;
                
            case 'semanal':
                $deveFazerBackup = ($agora->format('N') == 1); // Segunda-feira
                break;
                
            case 'mensal':
                $deveFazerBackup = ($agora->format('j') == 1); // Primeiro dia do mês
                break;
        }
        
        if (!$deveFazerBackup) {
            echo "Não é hora de fazer backup conforme configuração.\n";
            return;
        }
        
        $tiposErro = [];
        $tiposSucesso = [];
        
        // Executar tipos de backup configurados
        foreach ($config['tipos'] as $tipo) {
            $logId = null;
            
            try {
                echo "Executando backup tipo: {$tipo}\n";
                $logId = registrarLogBackup($pdo, $tipo, 'em_andamento');
                
                switch ($tipo) {
                    case 'banco':
                        $backup = criarBackupBanco($pdo);
                        $filename = 'backup_auto_banco_' . date('Y-m-d_H-i-s') . '.sql';
                        $caminho = __DIR__ . '/../backups/' . $filename;
                        
                        if (!is_dir(__DIR__ . '/../backups/')) {
                            mkdir(__DIR__ . '/../backups/', 0755, true);
                        }
                        
                        file_put_contents($caminho, $backup);
                        $integridade = verificarIntegridade($caminho);
                        
                        if ($logId) {
                            $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'sucesso', arquivo = ?, tamanho = ?, hash_verificacao = ? WHERE id = ?");
                            $stmt->execute([$filename, $integridade['tamanho'], $integridade['hash'], $logId]);
                        }
                        
                        $tiposSucesso[] = "Banco ({$filename})";
                        break;
                        
                    case 'arquivos':
                        $filename = criarBackupArquivos(__DIR__ . '/../images');
                        $caminho = __DIR__ . '/../backups/' . $filename;
                        $integridade = verificarIntegridade($caminho);
                        
                        if ($logId) {
                            $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'sucesso', arquivo = ?, tamanho = ?, hash_verificacao = ? WHERE id = ?");
                            $stmt->execute([$filename, $integridade['tamanho'], $integridade['hash'], $logId]);
                        }
                        
                        $tiposSucesso[] = "Arquivos ({$filename})";
                        break;
                        
                    case 'completo':
                        // Backup do banco
                        $backup = criarBackupBanco($pdo);
                        $sql_filename = 'backup_auto_banco_' . date('Y-m-d_H-i-s') . '.sql';
                        file_put_contents(__DIR__ . '/../backups/' . $sql_filename, $backup);
                        
                        // Backup dos arquivos
                        $zip_filename = criarBackupArquivos(__DIR__ . '/../images');
                        
                        // Criar ZIP com ambos
                        $zip = new ZipArchive();
                        $final_filename = 'backup_auto_completo_' . date('Y-m-d_H-i-s') . '.zip';
                        $caminho_final = __DIR__ . '/../backups/' . $final_filename;
                        
                        if ($zip->open($caminho_final, ZipArchive::CREATE) !== TRUE) {
                            throw new Exception('Não foi possível criar o arquivo ZIP final');
                        }
                        
                        $zip->addFile(__DIR__ . '/../backups/' . $sql_filename, 'banco/' . $sql_filename);
                        $zip->addFile(__DIR__ . '/../backups/' . $zip_filename, 'arquivos/' . $zip_filename);
                        
                        $info = "Backup Automático Completo\n";
                        $info .= "Data: " . date('Y-m-d H:i:s') . "\n";
                        $info .= "Banco: " . $sql_filename . "\n";
                        $info .= "Arquivos: " . $zip_filename . "\n";
                        $info .= "Sistema: Vaquinha Online v2.0\n";
                        $info .= "Executado via cron job\n";
                        
                        $zip->addFromString('backup_info.txt', $info);
                        $zip->close();
                        
                        $integridade = verificarIntegridade($caminho_final);
                        
                        // Limpar arquivos temporários
                        unlink(__DIR__ . '/../backups/' . $sql_filename);
                        unlink(__DIR__ . '/../backups/' . $zip_filename);
                        
                        if ($logId) {
                            $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'sucesso', arquivo = ?, tamanho = ?, hash_verificacao = ? WHERE id = ?");
                            $stmt->execute([$final_filename, $integridade['tamanho'], $integridade['hash'], $logId]);
                        }
                        
                        $tiposSucesso[] = "Completo ({$final_filename})";
                        break;
                }
                
                echo "Backup {$tipo} concluído com sucesso.\n";
                
            } catch (Exception $e) {
                echo "Erro no backup {$tipo}: " . $e->getMessage() . "\n";
                $tiposErro[] = $tipo . ": " . $e->getMessage();
                
                if ($logId) {
                    $stmt = $pdo->prepare("UPDATE logs_backup SET status = 'erro', erro = ? WHERE id = ?");
                    $stmt->execute([$e->getMessage(), $logId]);
                }
            }
        }
        
        // Limpar backups antigos
        $removidos = limparBackupsAntigos($config['manter_dias']);
        echo "Removidos {$removidos} backups antigos.\n";
        
        // Enviar email de notificação se configurado
        if (!empty($config['email_notificacao'])) {
            $assunto = "Backup Automático - " . (empty($tiposErro) ? "Sucesso" : "Erros");
            
            $mensagem = "<h3>Relatório de Backup Automático</h3>";
            $mensagem .= "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
            
            if (!empty($tiposSucesso)) {
                $mensagem .= "<p><strong>✅ Backups realizados com sucesso:</strong></p><ul>";
                foreach ($tiposSucesso as $sucesso) {
                    $mensagem .= "<li>{$sucesso}</li>";
                }
                $mensagem .= "</ul>";
            }
            
            if (!empty($tiposErro)) {
                $mensagem .= "<p><strong>❌ Erros encontrados:</strong></p><ul>";
                foreach ($tiposErro as $erro) {
                    $mensagem .= "<li>{$erro}</li>";
                }
                $mensagem .= "</ul>";
            }
            
            $mensagem .= "<p><strong>Backups antigos removidos:</strong> {$removidos}</p>";
            
            enviarEmailNotificacao($config['email_notificacao'], $assunto, $mensagem);
            echo "Email de notificação enviado.\n";
        }
        
        echo "[" . date('Y-m-d H:i:s') . "] Backup automático finalizado.\n";
        
    } catch (Exception $e) {
        echo "Erro fatal no backup automático: " . $e->getMessage() . "\n";
        error_log("Erro no backup automático: " . $e->getMessage());
    }
}

// Executar se chamado diretamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    executarBackupAutomatico();
}
?>