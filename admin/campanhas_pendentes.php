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
                $stmt = $pdo->prepare('UPDATE campanhas SET status = "aprovada", aprovado_em = NOW() WHERE id = ?');
                $stmt->execute([$id]);
                
                // Buscar dados da campanha para notificação
                $stmt = $pdo->prepare('SELECT c.titulo, u.nome as criador, u.email FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?');
                $stmt->execute([$id]);
                $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Criar notificação
                if ($campanha) {
                    $stmt = $pdo->prepare('INSERT INTO notificacoes (titulo, mensagem, tipo, destinatario, criado_em) VALUES (?, ?, ?, ?, NOW())');
                    $stmt->execute([
                        'Campanha Aprovada',
                        "Sua campanha '{$campanha['titulo']}' foi aprovada e está agora ativa!",
                        'sucesso',
                        $campanha['email']
                    ]);
                }
                
                // Buscar dados da campanha e do criador
                $stmt = $pdo->prepare('SELECT c.titulo, u.nome as criador, u.telefone FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?');
                $stmt->execute([$id]);
                $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
                // Notificação WhatsApp
                if ($campanha && $campanha['telefone']) {
                    $msg = "✅ Olá, {$campanha['criador']}! Sua campanha ‘{$campanha['titulo']}’ foi aprovada e já está no ar!\nAgora é só compartilhar e arrecadar!\nBoa sorte! 🍀";
                    enviar_whatsapp($campanha['telefone'], $msg);
                }
                
                echo json_encode(['success' => true, 'message' => 'Campanha aprovada com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao aprovar campanha']);
            }
            break;
            
        case 'reprovar':
            $id = intval($_POST['id']);
            $motivo = trim($_POST['motivo'] ?? '');
            
            try {
                $stmt = $pdo->prepare('UPDATE campanhas SET status = "rejeitada", motivo_rejeicao = ?, aprovado_em = NOW() WHERE id = ?');
                $stmt->execute([$motivo, $id]);
                
                // Buscar dados da campanha para notificação
                $stmt = $pdo->prepare('SELECT c.titulo, u.nome as criador, u.email FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?');
                $stmt->execute([$id]);
                $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Criar notificação
                if ($campanha) {
                    $mensagem = "Sua campanha '{$campanha['titulo']}' foi reprovada.";
                    if ($motivo) {
                        $mensagem .= " Motivo: $motivo";
                    }
                    
                    $stmt = $pdo->prepare('INSERT INTO notificacoes (titulo, mensagem, tipo, destinatario, criado_em) VALUES (?, ?, ?, ?, NOW())');
                    $stmt->execute([
                        'Campanha Reprovada',
                        $mensagem,
                        'erro',
                        $campanha['email']
                    ]);
                }
                
                // Buscar dados da campanha e do criador
                $stmt = $pdo->prepare('SELECT c.titulo, u.nome as criador, u.telefone FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?');
                $stmt->execute([$id]);
                $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
                // Notificação WhatsApp
                if ($campanha && $campanha['telefone']) {
                    $msg = "⚠️ Olá, {$campanha['criador']}! Sua campanha ‘{$campanha['titulo']}’ não foi aprovada.\nMotivo: $motivo\nRevise as informações e tente novamente. Estamos aqui para ajudar!";
                    enviar_whatsapp($campanha['telefone'], $msg);
                }
                
                echo json_encode(['success' => true, 'message' => 'Campanha reprovada com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao reprovar campanha']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
    exit;
}

// Filtros
$status_filter = $_GET['status'] ?? 'pendente';
$categoria_filter = $_GET['categoria'] ?? '';
$busca = trim($_GET['busca'] ?? '');

// Construir query com filtros
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = 'c.status = ?';
    $params[] = $status_filter;
}

if ($categoria_filter) {
    $where_conditions[] = 'c.categoria_id = ?';
    $params[] = $categoria_filter;
}

if ($busca) {
    $where_conditions[] = '(c.titulo LIKE ? OR c.descricao LIKE ? OR u.nome LIKE ?)';
    $params[] = "%$busca%";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Buscar campanhas
$query = "SELECT c.*, u.nome as criador, u.email as email_criador, cat.nome as categoria 
          FROM campanhas c 
          JOIN usuarios u ON c.usuario_id = u.id 
          LEFT JOIN categorias cat ON c.categoria_id = cat.id 
          $where_clause 
          ORDER BY c.criado_em DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$totalPendentes = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE status = "pendente"')->fetchColumn();
$totalAprovadas = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE status = "aprovada"')->fetchColumn();
$totalRejeitadas = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE status = "rejeitada"')->fetchColumn();

// Buscar categorias para filtro
$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1><i class="fas fa-clock"></i> Campanhas Pendentes</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Campanhas Pendentes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Cards de Estatísticas -->
                <div class="row">
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $totalPendentes ?></h3>
                                <p>Pendentes de Aprovação</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $totalAprovadas ?></h3>
                                <p>Aprovadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $totalRejeitadas ?></h3>
                                <p>Rejeitadas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Principal -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Lista de Campanhas
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
                                        <option value="aprovada" <?= $status_filter === 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                                        <option value="rejeitada" <?= $status_filter === 'rejeitada' ? 'selected' : '' ?>>Rejeitada</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Categoria</label>
                                    <select name="categoria" class="form-control">
                                        <option value="">Todas</option>
                                        <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $categoria_filter == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nome']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Buscar</label>
                                    <input type="text" name="busca" class="form-control" placeholder="Título, descrição ou criador..." value="<?= htmlspecialchars($busca) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                        <a href="campanhas_pendentes.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times"></i> Limpar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tabelaCampanhas">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="80">Imagem</th>
                                        <th>Campanha</th>
                                        <th>Criador</th>
                                        <th>Categoria</th>
                                        <th>Meta</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th width="150">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($campanhas)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            Nenhuma campanha encontrada
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($campanhas as $camp): ?>
                                        <tr data-id="<?= $camp['id'] ?>">
                                            <td class="text-center">
                                                <?php if ($camp['imagem']): ?>
                                                    <img src="../<?= htmlspecialchars($camp['imagem']) ?>" 
                                                         alt="<?= htmlspecialchars($camp['titulo']) ?>" 
                                                         class="img-thumbnail" 
                                                         style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px; border-radius: 4px;">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($camp['titulo']) ?></strong>
                                                <?php if ($camp['destaque']): ?>
                                                    <span class="badge badge-warning">Destaque</span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars(substr($camp['descricao'], 0, 100)) ?>...</small>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($camp['criador']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($camp['email_criador']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info"><?= htmlspecialchars($camp['categoria'] ?? 'Sem categoria') ?></span>
                                            </td>
                                            <td>
                                                <strong class="text-success">R$ <?= number_format($camp['meta'], 2, ',', '.') ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = ucfirst($camp['status']);
                                                switch ($camp['status']) {
                                                    case 'aprovada':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'rejeitada':
                                                        $status_class = 'danger';
                                                        break;
                                                    default:
                                                        $status_class = 'warning';
                                                }
                                                ?>
                                                <span class="badge badge-<?= $status_class ?>"><?= $status_text ?></span>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i', strtotime($camp['criado_em'])) ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            onclick="verDetalhes(<?= $camp['id'] ?>, '<?= htmlspecialchars($camp['titulo']) ?>', '<?= htmlspecialchars($camp['descricao']) ?>', '<?= htmlspecialchars($camp['criador']) ?>', '<?= $camp['meta'] ?>', '<?= $camp['status'] ?>', '<?= htmlspecialchars($camp['imagem'] ?? '') ?>', '<?= $camp['criado_em'] ?>')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($camp['status'] === 'pendente'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success" 
                                                            onclick="aprovarCampanha(<?= $camp['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="reprovarCampanha(<?= $camp['id'] ?>)">
                                                        <i class="fas fa-times"></i>
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

<!-- Modal Detalhes da Campanha -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalhes da Campanha
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-info"></i> Informações Básicas</h6>
                        <p><strong>Título:</strong> <span id="detalhe_titulo"></span></p>
                        <p><strong>Criador:</strong> <span id="detalhe_criador"></span></p>
                        <p><strong>Meta:</strong> <span id="detalhe_meta" class="text-success"></span></p>
                        <p><strong>Status:</strong> <span id="detalhe_status"></span></p>
                        <p><strong>Data:</strong> <span id="detalhe_data"></span></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-file-text"></i> Descrição</h6>
                        <div id="detalhe_descricao" class="border p-3 bg-light rounded">
                            <!-- Descrição será inserida aqui -->
                        </div>
                        
                        <h6 class="mt-3"><i class="fas fa-image"></i> Imagem</h6>
                        <div id="detalhe_imagem">
                            <!-- Imagem será inserida aqui -->
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

<!-- Modal Reprovar Campanha -->
<div class="modal fade" id="modalReprovar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Reprovar Campanha
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
                        <i class="fas fa-times"></i> Reprovar Campanha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>

<script>
// Ver detalhes da campanha
function verDetalhes(id, titulo, descricao, criador, meta, status, imagem, data) {
    $('#detalhe_titulo').text(titulo);
    $('#detalhe_criador').text(criador);
    $('#detalhe_meta').text('R$ ' + parseFloat(meta).toLocaleString('pt-BR', {minimumFractionDigits: 2}));
    
    let statusClass = '';
    switch (status) {
        case 'aprovada': statusClass = 'success'; break;
        case 'rejeitada': statusClass = 'danger'; break;
        default: statusClass = 'warning';
    }
    $('#detalhe_status').html('<span class="badge badge-' + statusClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>');
    
    $('#detalhe_data').text(new Date(data).toLocaleString('pt-BR'));
    $('#detalhe_descricao').html('<p class="mb-0">' + descricao + '</p>');
    
    if (imagem) {
        $('#detalhe_imagem').html('<img src="../' + imagem + '" alt="Imagem da campanha" class="img-fluid rounded">');
    } else {
        $('#detalhe_imagem').html('<p class="text-muted">Nenhuma imagem</p>');
    }
    
    $('#modalDetalhes').modal('show');
}

// Aprovar campanha
function aprovarCampanha(id) {
    if (confirm('Tem certeza que deseja aprovar esta campanha?')) {
        $.ajax({
            url: 'campanhas_pendentes.php',
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

// Reprovar campanha
function reprovarCampanha(id) {
    $('#reprovar_id').val(id);
    $('#modalReprovar').modal('show');
}

$('#formReprovar').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'campanhas_pendentes.php',
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
            $('#formReprovar button[type="submit"]').prop('disabled', false).html('<i class="fas fa-times"></i> Reprovar Campanha');
        }
    });
});

// Limpar formulário ao fechar modal
$('#modalReprovar').on('hidden.bs.modal', function() {
    $('#formReprovar')[0].reset();
});
</script> 