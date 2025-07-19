<?php
require 'db.php';
require_once '../includes/whatsapp.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Processar ações via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'aprovar':
            $id = intval($_POST['id']);
            
            try {
                $pdo->beginTransaction();
                
                // Atualizar status da doação
                $stmt = $pdo->prepare('UPDATE doacoes SET status = "confirmada", aprovado_em = NOW() WHERE id = ?');
                $stmt->execute([$id]);
                
                // Pegar dados da doação
                $stmt = $pdo->prepare('SELECT d.valor, d.usuario_id as doador_id, c.id as campanha_id, c.titulo, c.usuario_id as criador_id, c.arrecadado, c.meta, u.nome as doador_nome FROM doacoes d JOIN campanhas c ON d.campanha_id = c.id JOIN usuarios u ON d.usuario_id = u.id WHERE d.id = ?');
                $stmt->execute([$id]);
                $doacao = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($doacao) {
                    // Atualizar arrecadado na campanha
                    $stmt = $pdo->prepare('UPDATE campanhas SET arrecadado = arrecadado + ? WHERE id = ?');
                    $stmt->execute([$doacao['valor'], $doacao['campanha_id']]);
                    // Buscar telefone do criador
                    $stmt = $pdo->prepare('SELECT nome, telefone FROM usuarios WHERE id = ?');
                    $stmt->execute([$doacao['criador_id']]);
                    $criador = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($criador && $criador['telefone']) {
                        $msg = "💰 Parabéns, {$criador['nome']}! Você recebeu uma nova doação de R$ " . number_format($doacao['valor'], 2, ',', '.') . " em sua campanha ‘{$doacao['titulo']}’.\nSeu total arrecadado agora é R$ " . number_format($doacao['arrecadado'] + $doacao['valor'], 2, ',', '.') . ".\nContinue divulgando e inspire mais pessoas! 🙌";
                        enviar_whatsapp($criador['telefone'], $msg);
                        // Se atingiu a meta, enviar parabéns
                        if ($doacao['arrecadado'] + $doacao['valor'] >= $doacao['meta']) {
                            $msg_meta = "🎯 Uau, {$criador['nome']}! Sua campanha ‘{$doacao['titulo']}’ atingiu a meta!\nParabéns por essa conquista! 🏆";
                            enviar_whatsapp($criador['telefone'], $msg_meta);
                        }
                    }
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Doação aprovada com sucesso']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Erro ao aprovar doação']);
            }
            break;
            
        case 'reprovar':
            $id = intval($_POST['id']);
            $motivo = trim($_POST['motivo'] ?? '');
            
            try {
                $stmt = $pdo->prepare('UPDATE doacoes SET status = "cancelada", motivo_rejeicao = ?, aprovado_em = NOW() WHERE id = ?');
                $stmt->execute([$motivo, $id]);
                echo json_encode(['success' => true, 'message' => 'Doação reprovada com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao reprovar doação']);
            }
            break;
            
        case 'excluir':
            $id = intval($_POST['id']);
            
            try {
                // Verificar se pode excluir (apenas canceladas)
                $stmt = $pdo->prepare('SELECT status FROM doacoes WHERE id = ?');
                $stmt->execute([$id]);
                $doacao = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$doacao) {
                    echo json_encode(['success' => false, 'message' => 'Doação não encontrada']);
                    exit;
                }
                
                if ($doacao['status'] !== 'cancelada') {
                    echo json_encode(['success' => false, 'message' => 'Apenas doações canceladas podem ser excluídas']);
                    exit;
                }
                
                $stmt = $pdo->prepare('DELETE FROM doacoes WHERE id = ?');
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Doação excluída com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir doação']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
    exit;
}

// Filtros
$status_filter = $_GET['status'] ?? '';
$campanha_filter = $_GET['campanha'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Construir query com filtros
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = 'd.status = ?';
    $params[] = $status_filter;
}

if ($campanha_filter) {
    $where_conditions[] = 'c.titulo LIKE ?';
    $params[] = '%' . $campanha_filter . '%';
}

if ($data_inicio) {
    $where_conditions[] = 'DATE(d.criado_em) >= ?';
    $params[] = $data_inicio;
}

if ($data_fim) {
    $where_conditions[] = 'DATE(d.criado_em) <= ?';
    $params[] = $data_fim;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Buscar doações
$query = "SELECT d.*, u.nome as usuario, u.email as usuario_email, c.titulo as campanha, c.meta as campanha_meta 
          FROM doacoes d 
          JOIN usuarios u ON d.usuario_id = u.id 
          JOIN campanhas c ON d.campanha_id = c.id 
          $where_clause 
          ORDER BY d.criado_em DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$doacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$total_doacoes = count($doacoes);
$total_confirmadas = 0;
$total_pendentes = 0;
$total_canceladas = 0;
$valor_total = 0;

foreach ($doacoes as $d) {
    if ($d['status'] === 'confirmada') {
        $total_confirmadas++;
        $valor_total += $d['valor'];
    } elseif ($d['status'] === 'pendente') {
        $total_pendentes++;
    } elseif ($d['status'] === 'cancelada') {
        $total_canceladas++;
    }
}

// Buscar campanhas para filtro
$campanhas = $pdo->query('SELECT id, titulo FROM campanhas ORDER BY titulo')->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1><i class="fas fa-hand-holding-heart"></i> Gerenciar Doações</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Doações</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Cards de Estatísticas -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $total_doacoes ?></h3>
                                <p>Total de Doações</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $total_confirmadas ?></h3>
                                <p>Confirmadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $total_pendentes ?></h3>
                                <p>Pendentes</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>R$ <?= number_format($valor_total, 2, ',', '.') ?></h3>
                                <p>Total Arrecadado</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Principal -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Lista de Doações
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="collapse" data-target="#filtros">
                                <i class="fas fa-filter"></i> Filtros
                            </button>
                        </div>
                    </div>
                    
                    <!-- Filtros -->
                    <div class="collapse" id="filtros">
                        <div class="card-body border-bottom">
                            <form method="GET" class="row">
                                <div class="col-md-3">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="pendente" <?= $status_filter === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="confirmada" <?= $status_filter === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                        <option value="cancelada" <?= $status_filter === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Campanha</label>
                                    <select name="campanha" class="form-control">
                                        <option value="">Todas</option>
                                        <?php foreach ($campanhas as $camp): ?>
                                        <option value="<?= htmlspecialchars($camp['titulo']) ?>" <?= $campanha_filter === $camp['titulo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($camp['titulo']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Data Início</label>
                                    <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio ?>">
                                </div>
                                <div class="col-md-2">
                                    <label>Data Fim</label>
                                    <input type="date" name="data_fim" class="form-control" value="<?= $data_fim ?>">
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                        <a href="doacoes.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times"></i> Limpar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tabelaDoacoes">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuário</th>
                                        <th>Campanha</th>
                                        <th>Valor</th>
                                        <th>Método</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th width="150">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($doacoes)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            Nenhuma doação encontrada
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($doacoes as $d): ?>
                                        <tr data-id="<?= $d['id'] ?>">
                                            <td>#<?= $d['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($d['usuario']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($d['usuario_email']) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($d['campanha']) ?></strong><br>
                                                <small class="text-muted">Meta: R$ <?= number_format($d['campanha_meta'], 2, ',', '.') ?></small>
                                            </td>
                                            <td>
                                                <strong class="text-success">R$ <?= number_format($d['valor'], 2, ',', '.') ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= ucfirst($d['metodo_pagamento']) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = ucfirst($d['status']);
                                                switch ($d['status']) {
                                                    case 'confirmada':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'cancelada':
                                                        $status_class = 'danger';
                                                        break;
                                                    default:
                                                        $status_class = 'warning';
                                                }
                                                ?>
                                                <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i', strtotime($d['criado_em'])) ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalhes(<?= $d['id'] ?>, '<?= htmlspecialchars($d['usuario']) ?>', '<?= htmlspecialchars($d['campanha']) ?>', '<?= $d['valor'] ?>', '<?= $d['metodo_pagamento'] ?>', '<?= $d['status'] ?>', '<?= htmlspecialchars($d['mensagem'] ?? '') ?>', '<?= htmlspecialchars($d['comprovante'] ?? '') ?>', '<?= $d['criado_em'] ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($d['status'] === 'pendente'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="aprovarDoacao(<?= $d['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="reprovarDoacao(<?= $d['id'] ?>)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($d['status'] === 'cancelada'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="excluirDoacao(<?= $d['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal Detalhes da Doação -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalhes da Doação
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-user"></i> Informações do Doador</h6>
                        <p><strong>Nome:</strong> <span id="detalhe_usuario"></span></p>
                        <p><strong>Campanha:</strong> <span id="detalhe_campanha"></span></p>
                        <p><strong>Valor:</strong> <span id="detalhe_valor" class="text-success"></span></p>
                        <p><strong>Método:</strong> <span id="detalhe_metodo"></span></p>
                        <p><strong>Status:</strong> <span id="detalhe_status"></span></p>
                        <p><strong>Data:</strong> <span id="detalhe_data"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-comment"></i> Mensagem</h6>
                        <div id="detalhe_mensagem" class="border p-3 bg-light rounded">
                            <!-- Mensagem será inserida aqui -->
                        </div>
                        
                        <h6 class="mt-3"><i class="fas fa-file-image"></i> Comprovante</h6>
                        <div id="detalhe_comprovante">
                            <!-- Comprovante será inserido aqui -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reprovar Doação -->
<div class="modal fade" id="modalReprovar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Reprovar Doação
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formReprovar">
                <div class="modal-body">
                    <input type="hidden" id="reprovar_id" name="id">
                    <div class="form-group">
                        <label for="motivo">Motivo da Reprovação</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" placeholder="Informe o motivo da reprovação (opcional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reprovar Doação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>

<script>
// Ver detalhes da doação
function verDetalhes(id, usuario, campanha, valor, metodo, status, mensagem, comprovante, data) {
    $('#detalhe_usuario').text(usuario);
    $('#detalhe_campanha').text(campanha);
    $('#detalhe_valor').text('R$ ' + parseFloat(valor).toLocaleString('pt-BR', {minimumFractionDigits: 2}));
    $('#detalhe_metodo').text(metodo.charAt(0).toUpperCase() + metodo.slice(1));
    
    let statusClass = '';
    switch (status) {
        case 'confirmada': statusClass = 'success'; break;
        case 'cancelada': statusClass = 'danger'; break;
        default: statusClass = 'warning';
    }
    $('#detalhe_status').html('<span class="badge badge-' + statusClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>');
    
    $('#detalhe_data').text(new Date(data).toLocaleString('pt-BR'));
    
    if (mensagem) {
        $('#detalhe_mensagem').html('<p class="mb-0">' + mensagem + '</p>');
    } else {
        $('#detalhe_mensagem').html('<p class="text-muted mb-0">Nenhuma mensagem</p>');
    }
    
    if (comprovante) {
        $('#detalhe_comprovante').html('<a href="../' + comprovante + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt"></i> Ver Comprovante</a>');
    } else {
        $('#detalhe_comprovante').html('<p class="text-muted">Nenhum comprovante</p>');
    }
    
    $('#modalDetalhes').modal('show');
}

// Aprovar doação
function aprovarDoacao(id) {
    if (confirm('Tem certeza que deseja aprovar esta doação?')) {
        $.ajax({
            url: 'doacoes.php',
            type: 'POST',
            data: {
                action: 'aprovar',
                id: id
            },
            beforeSend: function() {
                // Mostrar loading
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Erro ao processar requisição');
            }
        });
    }
}

// Reprovar doação
function reprovarDoacao(id) {
    $('#reprovar_id').val(id);
    $('#modalReprovar').modal('show');
}

$('#formReprovar').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'doacoes.php',
        type: 'POST',
        data: {
            action: 'reprovar',
            id: $('#reprovar_id').val(),
            motivo: $('#motivo').val()
        },
        beforeSend: function() {
            $('#formReprovar button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processando...');
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#modalReprovar').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Erro ao processar requisição');
        },
        complete: function() {
            $('#formReprovar button[type="submit"]').prop('disabled', false).html('<i class="fas fa-times"></i> Reprovar Doação');
        }
    });
});

// Excluir doação
function excluirDoacao(id) {
    if (confirm('Tem certeza que deseja excluir esta doação? Esta ação não pode ser desfeita.')) {
        $.ajax({
            url: 'doacoes.php',
            type: 'POST',
            data: {
                action: 'excluir',
                id: id
            },
            beforeSend: function() {
                // Mostrar loading
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Erro ao processar requisição');
            }
        });
    }
}

// Limpar formulário ao fechar modal
$('#modalReprovar').on('hidden.bs.modal', function() {
    $('#formReprovar')[0].reset();
});
</script> 