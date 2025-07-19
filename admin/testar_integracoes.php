<?php
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
        $inicio = microtime(true);
        $resultado = $funcao();
        $tempo = round((microtime(true) - $inicio) * 1000, 2);
        
        $resultados[] = [
            'teste' => $nome,
            'status' => $resultado['sucesso'] ? 'sucesso' : 'falha',
            'mensagem' => $resultado['sucesso'] ? ($resultado['mensagem'] ?? 'OK') : $resultado['erro'],
            'tempo' => $tempo . 'ms',
            'dados' => $resultado['dados'] ?? null
        ];
        return $resultado['sucesso'];
    } catch (Exception $e) {
        $resultados[] = [
            'teste' => $nome,
            'status' => 'erro',
            'mensagem' => $e->getMessage(),
            'tempo' => '0ms',
            'dados' => null
        ];
        return false;
    }
}

// Executar testes se solicitado
if (isset($_GET['executar'])) {
    // Teste 1: Conexão com banco de dados
    testar('Conexão com Banco de Dados', function() use ($pdo) {
        $pdo->query('SELECT 1');
        return ['sucesso' => true, 'mensagem' => 'Conexão estabelecida'];
    });

    // Teste 2: Configurações do WhatsApp
    testar('Configurações WhatsApp', function() use ($pdo) {
        $chaves = ['whatsapp_name', 'whatsapp_token', 'whatsapp_api_url'];
        $config = [];
        foreach ($chaves as $chave) {
            $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
            $stmt->execute([$chave]);
            $config[$chave] = $stmt->fetchColumn() ?: '';
        }
        
        if (empty($config['whatsapp_api_url']) || empty($config['whatsapp_token'])) {
            return ['sucesso' => false, 'erro' => 'Configurações incompletas'];
        }
        
        return ['sucesso' => true, 'mensagem' => 'Configurações encontradas'];
    });

    // Teste 3: Conectividade WhatsApp API
    testar('Conectividade WhatsApp API', function() {
        require_once '../includes/whatsapp.php';
        $config = obter_configuracoes_whatsapp($GLOBALS['pdo']);
        
        if (!$config) {
            return ['sucesso' => false, 'erro' => 'Configurações não encontradas'];
        }
        
        // Testar conectividade básica
        $ch = curl_init($config['api_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['sucesso' => false, 'erro' => 'Erro de conectividade: ' . $error];
        }
        
        if ($httpcode >= 200 && $httpcode < 500) {
            return ['sucesso' => true, 'mensagem' => "HTTP {$httpcode} - Servidor acessível"];
        }
        
        return ['sucesso' => false, 'erro' => "HTTP {$httpcode} - Servidor inacessível"];
    });

    // Teste 4: Configurações do Mercado Pago
    testar('Configurações Mercado Pago', function() use ($pdo) {
        require_once '../includes/mercado_pago.php';
        
        try {
            $credenciais = validar_credenciais_mercado_pago();
            return ['sucesso' => true, 'mensagem' => 'Credenciais válidas'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    });

    // Teste 5: Conectividade Mercado Pago API
    testar('Conectividade Mercado Pago API', function() {
        require_once '../includes/mercado_pago.php';
        
        try {
            $resultado = testar_mercado_pago();
            if ($resultado['sucesso']) {
                return [
                    'sucesso' => true, 
                    'mensagem' => 'Conexão estabelecida com sucesso',
                    'dados' => $resultado['dados']
                ];
            } else {
                return ['sucesso' => false, 'erro' => $resultado['erro']];
            }
        } catch (Exception $e) {
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    });

    // Teste 6: Estrutura de diretórios
    testar('Estrutura de Diretórios', function() {
        $diretorios = ['../logs', '../backups', '../uploads'];
        $problemas = [];
        
        foreach ($diretorios as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    $problemas[] = "Não foi possível criar {$dir}";
                }
            } elseif (!is_writable($dir)) {
                $problemas[] = "Diretório {$dir} não é gravável";
            }
        }
        
        if (!empty($problemas)) {
            return ['sucesso' => false, 'erro' => implode(', ', $problemas)];
        }
        
        return ['sucesso' => true, 'mensagem' => 'Todos os diretórios OK'];
    });

    // Teste 7: Extensões PHP
    testar('Extensões PHP Necessárias', function() {
        $extensoes = ['curl', 'json', 'pdo', 'pdo_mysql'];
        $faltando = [];
        
        foreach ($extensoes as $ext) {
            if (!extension_loaded($ext)) {
                $faltando[] = $ext;
            }
        }
        
        if (!empty($faltando)) {
            return ['sucesso' => false, 'erro' => 'Extensões faltando: ' . implode(', ', $faltando)];
        }
        
        return ['sucesso' => true, 'mensagem' => 'Todas as extensões disponíveis'];
    });

    // Teste 8: Permissões de arquivo
    testar('Permissões de Arquivos', function() {
        $arquivos = [
            '../includes/whatsapp.php' => 'readable',
            '../includes/mercado_pago.php' => 'readable',
            '../webhook_mercado_pago.php' => 'readable'
        ];
        
        $problemas = [];
        
        foreach ($arquivos as $arquivo => $permissao) {
            if (!file_exists($arquivo)) {
                $problemas[] = "Arquivo {$arquivo} não encontrado";
                continue;
            }
            
            if ($permissao === 'readable' && !is_readable($arquivo)) {
                $problemas[] = "Arquivo {$arquivo} não é legível";
            } elseif ($permissao === 'writable' && !is_writable($arquivo)) {
                $problemas[] = "Arquivo {$arquivo} não é gravável";
            }
        }
        
        if (!empty($problemas)) {
            return ['sucesso' => false, 'erro' => implode(', ', $problemas)];
        }
        
        return ['sucesso' => true, 'mensagem' => 'Todas as permissões OK'];
    });

    // Teste 9: Tabelas do banco
    testar('Tabelas do Banco de Dados', function() use ($pdo) {
        $tabelas = ['textos', 'campanhas', 'doacoes', 'usuarios'];
        $faltando = [];
        
        foreach ($tabelas as $tabela) {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$tabela}'");
            if ($stmt->rowCount() === 0) {
                $faltando[] = $tabela;
            }
        }
        
        if (!empty($faltando)) {
            return ['sucesso' => false, 'erro' => 'Tabelas faltando: ' . implode(', ', $faltando)];
        }
        
        return ['sucesso' => true, 'mensagem' => 'Todas as tabelas encontradas'];
    });

    // Teste 10: Log de funcionamento
    testar('Sistema de Logs', function() {
        require_once '../includes/whatsapp.php';
        require_once '../includes/mercado_pago.php';
        
        $teste_whatsapp = log_whatsapp('Teste de log WhatsApp - ' . date('Y-m-d H:i:s'));
        $teste_mp = log_mercado_pago('Teste de log Mercado Pago - ' . date('Y-m-d H:i:s'));
        
        $log_whatsapp = '../logs/whatsapp.log';
        $log_mp = '../logs/mercado_pago.log';
        
        if (!file_exists($log_whatsapp) || !file_exists($log_mp)) {
            return ['sucesso' => false, 'erro' => 'Arquivos de log não foram criados'];
        }
        
        return ['sucesso' => true, 'mensagem' => 'Logs funcionando corretamente'];
    });
}

// Estatísticas dos resultados
$total = count($resultados);
$sucessos = count(array_filter($resultados, function($r) { return $r['status'] === 'sucesso'; }));
$falhas = count(array_filter($resultados, function($r) { return $r['status'] === 'falha'; }));
$erros = count(array_filter($resultados, function($r) { return $r['status'] === 'erro'; }));

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
                        <h1><i class="fas fa-vial"></i> Teste das Integrações</h1>
                        <p class="text-muted">Verificação completa do sistema WhatsApp e Mercado Pago</p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="integracoes.php">Integrações</a></li>
                            <li class="breadcrumb-item active">Teste</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Controles -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <?php if (empty($resultados)): ?>
                                <h4>Execute os testes para verificar as integrações</h4>
                                <p class="text-muted">Este teste irá verificar se WhatsApp e Mercado Pago estão configurados corretamente</p>
                                <a href="?executar=1" class="btn btn-primary btn-lg">
                                    <i class="fas fa-play"></i> Executar Todos os Testes
                                </a>
                                <?php else: ?>
                                <a href="?executar=1" class="btn btn-primary">
                                    <i class="fas fa-redo"></i> Executar Novamente
                                </a>
                                <a href="integracoes.php" class="btn btn-secondary">
                                    <i class="fas fa-cogs"></i> Configurar Integrações
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($resultados)): ?>
                <!-- Resumo dos Resultados -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $total ?></h3>
                                <p>Total de Testes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-vial"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $sucessos ?></h3>
                                <p>Sucessos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $falhas ?></h3>
                                <p>Falhas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $erros ?></h3>
                                <p>Erros</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resultados Detalhados -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clipboard-check"></i> Resultados Detalhados
                                </h3>
                                <div class="card-tools">
                                    <span class="badge badge-info"><?= date('d/m/Y H:i:s') ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="30%">Teste</th>
                                                <th width="15%">Status</th>
                                                <th width="10%">Tempo</th>
                                                <th width="40%">Resultado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($resultados as $i => $resultado): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($resultado['teste']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = '';
                                                    $icon = '';
                                                    switch ($resultado['status']) {
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
                                                    <small class="text-muted"><?= $resultado['tempo'] ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($resultado['status'] === 'sucesso'): ?>
                                                        <span class="text-success">
                                                            ✅ <?= htmlspecialchars($resultado['mensagem']) ?>
                                                        </span>
                                                        <?php if ($resultado['dados']): ?>
                                                            <br><small class="text-muted">
                                                                <?php foreach ($resultado['dados'] as $key => $value): ?>
                                                                    <strong><?= htmlspecialchars($key) ?>:</strong> <?= htmlspecialchars($value) ?> |
                                                                <?php endforeach; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-danger">
                                                            ❌ <?= htmlspecialchars($resultado['mensagem']) ?>
                                                        </span>
                                                    <?php endif; ?>
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

                <!-- Recomendações -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-lightbulb"></i> Recomendações
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php if ($erros > 0 || $falhas > 0): ?>
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-exclamation-triangle"></i> Ações Necessárias</h5>
                                    <ul class="mb-0">
                                        <?php if ($erros > 0): ?>
                                        <li><strong><?= $erros ?> erro(s)</strong> encontrado(s) - verifique a configuração do sistema</li>
                                        <?php endif; ?>
                                        <?php if ($falhas > 0): ?>
                                        <li><strong><?= $falhas ?> falha(s)</strong> encontrada(s) - configure as integrações</li>
                                        <?php endif; ?>
                                        <li>Acesse <a href="integracoes.php">Configurações de Integrações</a> para corrigir os problemas</li>
                                        <li>Verifique os logs em <code>logs/whatsapp.log</code> e <code>logs/mercado_pago.log</code></li>
                                        <li>Execute os testes novamente após as correções</li>
                                    </ul>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle"></i> Sistema Funcionando Perfeitamente!</h5>
                                    <p class="mb-0">
                                        Todas as integrações estão configuradas e funcionando corretamente. 
                                        O sistema está pronto para processar doações e enviar notificações.
                                    </p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Informações do Sistema -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-server"></i> Informações do Sistema
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>PHP Version:</strong></td>
                                        <td><?= PHP_VERSION ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Memory Limit:</strong></td>
                                        <td><?= ini_get('memory_limit') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Max Execution Time:</strong></td>
                                        <td><?= ini_get('max_execution_time') ?>s</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Upload Max Size:</strong></td>
                                        <td><?= ini_get('upload_max_filesize') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>cURL Version:</strong></td>
                                        <td><?= curl_version()['version'] ?? 'N/A' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-puzzle-piece"></i> Extensões PHP
                                </h3>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <?php
                                    $extensoes = ['curl', 'json', 'pdo', 'pdo_mysql', 'mbstring', 'openssl'];
                                    foreach ($extensoes as $ext):
                                    ?>
                                    <tr>
                                        <td><strong><?= $ext ?>:</strong></td>
                                        <td>
                                            <?php if (extension_loaded($ext)): ?>
                                                <span class="badge badge-success">✓ Carregada</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">✗ Não encontrada</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </table>
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
.small-box {
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table th {
    border-top: none;
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.9em;
    padding: 0.4em 0.8em;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-muted {
    color: #6c757d !important;
}
</style>

<script>
// Auto-refresh a cada 30 segundos se há testes em execução
<?php if (!empty($resultados) && ($erros > 0 || $falhas > 0)): ?>
setTimeout(function() {
    if (confirm('Deseja executar os testes novamente para verificar as correções?')) {
        window.location.href = '?executar=1';
    }
}, 30000);
<?php endif; ?>

// Scroll suave para resultados
<?php if (!empty($resultados)): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.table-responsive').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
});
<?php endif; ?>
</script>
</body>
</html>