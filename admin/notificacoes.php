<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Ações
if (isset($_GET['marcar_lida'])) {
    $id = intval($_GET['marcar_lida']);
    $pdo->prepare('UPDATE notificacoes SET lida = 1 WHERE id = ?')->execute([$id]);
    $sucesso = 'Notificação marcada como lida!';
}

if (isset($_GET['marcar_todas'])) {
    $pdo->prepare('UPDATE notificacoes SET lida = 1 WHERE lida = 0')->execute();
    $sucesso = 'Todas as notificações foram marcadas como lidas!';
}

if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $pdo->prepare('DELETE FROM notificacoes WHERE id = ?')->execute([$id]);
    $sucesso = 'Notificação excluída com sucesso!';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $tipo = $_POST['tipo'] ?? 'info';
    $destinatario = $_POST['destinatario'] ?? 'todos';
    
    if (!$titulo || !$mensagem) {
        $erro = 'Preencha todos os campos!';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO notificacoes (titulo, mensagem, tipo, destinatario, criado_em) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$titulo, $mensagem, $tipo, $destinatario]);
            $sucesso = 'Notificação enviada com sucesso!';
        } catch (Exception $e) {
            $erro = 'Erro ao enviar notificação: ' . $e->getMessage();
        }
    }
}

// Filtros
$filtro_tipo = $_GET['filtro_tipo'] ?? '';
$filtro_status = $_GET['filtro_status'] ?? '';
$busca = trim($_GET['busca'] ?? '');

// Query base
$sql = 'SELECT * FROM notificacoes WHERE 1=1';
$params = [];

if ($filtro_tipo) {
    $sql .= ' AND tipo = ?';
    $params[] = $filtro_tipo;
}

if ($filtro_status === 'lidas') {
    $sql .= ' AND lida = 1';
} elseif ($filtro_status === 'nao_lidas') {
    $sql .= ' AND lida = 0';
}

if ($busca) {
    $sql .= ' AND (titulo LIKE ? OR mensagem LIKE ?)';
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$sql .= ' ORDER BY criado_em DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$totalNotificacoes = $pdo->query('SELECT COUNT(*) FROM notificacoes')->fetchColumn();
$notificacoesNaoLidas = $pdo->query('SELECT COUNT(*) FROM notificacoes WHERE lida = 0')->fetchColumn();
$notificacoesHoje = $pdo->query('SELECT COUNT(*) FROM notificacoes WHERE DATE(criado_em) = CURDATE()')->fetchColumn();
$notificacoesSemana = $pdo->query('SELECT COUNT(*) FROM notificacoes WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
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
                        <h1>Gerenciar Notificações</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Notificações</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                
                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-primary">
                            <div class="inner">
                                <h3><?= number_format($totalNotificacoes, 0, ',', '.') ?></h3>
                                <p>Total de Notificações</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3><?= number_format($notificacoesNaoLidas, 0, ',', '.') ?></h3>
                                <p>Não Lidas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-info">
                            <div class="inner">
                                <h3><?= number_format($notificacoesHoje, 0, ',', '.') ?></h3>
                                <p>Hoje</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3><?= number_format($notificacoesSemana, 0, ',', '.') ?></h3>
                                <p>Esta Semana</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Lista de Notificações -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list"></i> Todas as Notificações
                                </h3>
                                <div class="card-tools">
                                    <a href="?marcar_todas=1" class="btn btn-sm btn-success">
                                        <i class="fas fa-check-double"></i> Marcar Todas como Lidas
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <!-- Filtros -->
                                <div class="p-3 border-bottom">
                                    <form method="get" class="row">
                                        <div class="col-md-3">
                                            <input type="text" name="busca" class="form-control" placeholder="Buscar..." value="<?= htmlspecialchars($busca) ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <select name="filtro_tipo" class="form-control">
                                                <option value="">Todos os tipos</option>
                                                <option value="info" <?= $filtro_tipo === 'info' ? 'selected' : '' ?>>Informação</option>
                                                <option value="sucesso" <?= $filtro_tipo === 'sucesso' ? 'selected' : '' ?>>Sucesso</option>
                                                <option value="aviso" <?= $filtro_tipo === 'aviso' ? 'selected' : '' ?>>Aviso</option>
                                                <option value="erro" <?= $filtro_tipo === 'erro' ? 'selected' : '' ?>>Erro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <select name="filtro_status" class="form-control">
                                                <option value="">Todos os status</option>
                                                <option value="nao_lidas" <?= $filtro_status === 'nao_lidas' ? 'selected' : '' ?>>Não lidas</option>
                                                <option value="lidas" <?= $filtro_status === 'lidas' ? 'selected' : '' ?>>Lidas</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-search"></i> Filtrar
                                            </button>
                                        </div>
                                        <div class="col-md-3">
                                            <a href="notificacoes.php" class="btn btn-secondary w-100">
                                                <i class="fas fa-times"></i> Limpar
                                            </a>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Lista -->
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Tipo</th>
                                                <th>Título</th>
                                                <th>Destinatário</th>
                                                <th>Data</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($notificacoes)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                                        <br>Nenhuma notificação encontrada
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($notificacoes as $notif): ?>
                                                <tr class="<?= !$notif['lida'] ? 'table-warning' : '' ?>">
                                                    <td>
                                                        <?php if (!$notif['lida']): ?>
                                                            <span class="badge badge-warning">Nova</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Lida</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?= $notif['tipo'] === 'info' ? 'info' : ($notif['tipo'] === 'sucesso' ? 'success' : ($notif['tipo'] === 'aviso' ? 'warning' : 'danger')) ?>">
                                                            <?= ucfirst($notif['tipo']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($notif['titulo']) ?></strong>
                                                        <br><small class="text-muted"><?= htmlspecialchars(substr($notif['mensagem'], 0, 50)) ?>...</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info"><?= ucfirst($notif['destinatario']) ?></span>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($notif['criado_em'])) ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalNotificacao" data-titulo="<?= htmlspecialchars($notif['titulo']) ?>" data-mensagem="<?= htmlspecialchars($notif['mensagem']) ?>" data-tipo="<?= $notif['tipo'] ?>" data-data="<?= date('d/m/Y H:i', strtotime($notif['criado_em'])) ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <?php if (!$notif['lida']): ?>
                                                                <a href="?marcar_lida=<?= $notif['id'] ?>" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-check"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <a href="?excluir=<?= $notif['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir esta notificação?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
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
                    
                    <!-- Nova Notificação -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus"></i> Nova Notificação
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="form-group">
                                        <label>Título</label>
                                        <input type="text" name="titulo" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Mensagem</label>
                                        <textarea name="mensagem" class="form-control" rows="4" required></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Tipo</label>
                                        <select name="tipo" class="form-control" required>
                                            <option value="info">Informação</option>
                                            <option value="sucesso">Sucesso</option>
                                            <option value="aviso">Aviso</option>
                                            <option value="erro">Erro</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Destinatário</label>
                                        <select name="destinatario" class="form-control" required>
                                            <option value="todos">Todos os usuários</option>
                                            <option value="admin">Apenas administradores</option>
                                            <option value="usuarios">Apenas usuários comuns</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-paper-plane"></i> Enviar Notificação
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Configurações -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-cog"></i> Configurações
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="notif_tempo_real" checked>
                                        <label class="custom-control-label" for="notif_tempo_real">Notificações em Tempo Real</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="notif_som" checked>
                                        <label class="custom-control-label" for="notif_som">Som de Notificação</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="notif_email">
                                        <label class="custom-control-label" for="notif_email">Notificações por E-mail</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Intervalo de Verificação (segundos)</label>
                                    <select class="form-control" id="intervalo_verificacao">
                                        <option value="5">5 segundos</option>
                                        <option value="10" selected>10 segundos</option>
                                        <option value="30">30 segundos</option>
                                        <option value="60">1 minuto</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal para visualizar notificação -->
<div class="modal fade" id="modalNotificacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo"></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Tipo:</strong> <span id="modalTipo"></span>
                </div>
                <div class="mb-3">
                    <strong>Data:</strong> <span id="modalData"></span>
                </div>
                <div class="mb-3">
                    <strong>Mensagem:</strong>
                    <p id="modalMensagem" class="mt-2"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>

<script>
// Sistema de notificações em tempo real
class SistemaNotificacoes {
    constructor() {
        this.intervalo = 10000; // 10 segundos
        this.ultimaVerificacao = new Date();
        this.iniciar();
    }
    
    iniciar() {
        this.verificarNovas();
        setInterval(() => this.verificarNovas(), this.intervalo);
        
        // Atualizar intervalo quando mudar
        document.getElementById('intervalo_verificacao').addEventListener('change', (e) => {
            this.intervalo = parseInt(e.target.value) * 1000;
        });
    }
    
    async verificarNovas() {
        try {
            const response = await fetch('api/notificacoes_novas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ultima_verificacao: this.ultimaVerificacao.toISOString()
                })
            });
            
            const data = await response.json();
            
            if (data.novas && data.novas.length > 0) {
                this.mostrarNotificacoes(data.novas);
                this.ultimaVerificacao = new Date();
                
                // Atualizar contador no sidebar
                this.atualizarContador(data.total_nao_lidas);
            }
        } catch (error) {
            console.error('Erro ao verificar notificações:', error);
        }
    }
    
    mostrarNotificacoes(notificacoes) {
        if (!document.getElementById('notif_tempo_real').checked) return;
        
        notificacoes.forEach(notif => {
            this.criarNotificacao(notif);
        });
        
        if (document.getElementById('notif_som').checked) {
            this.tocarSom();
        }
    }
    
    criarNotificacao(notif) {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-notification-${notif.tipo}`;
        toast.innerHTML = `
            <div class="toast-header">
                <strong>${notif.titulo}</strong>
                <button type="button" class="close" onclick="this.parentElement.parentElement.remove()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${notif.mensagem}
            </div>
        `;
        
        // Adicionar ao container de toasts
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999;';
            document.body.appendChild(container);
        }
        
        container.appendChild(toast);
        
        // Remover após 5 segundos
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }
    
    tocarSom() {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
        audio.play().catch(() => {}); // Ignorar erros de autoplay
    }
    
    atualizarContador(total) {
        const badge = document.querySelector('.navbar-badge');
        if (badge) {
            badge.textContent = total;
            if (total > 0) {
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }
}

// Modal para visualizar notificação
document.getElementById('modalNotificacao').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const titulo = button.getAttribute('data-titulo');
    const mensagem = button.getAttribute('data-mensagem');
    const tipo = button.getAttribute('data-tipo');
    const data = button.getAttribute('data-data');
    
    document.getElementById('modalTitulo').textContent = titulo;
    document.getElementById('modalMensagem').textContent = mensagem;
    document.getElementById('modalTipo').textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
    document.getElementById('modalData').textContent = data;
});

// Inicializar sistema de notificações
document.addEventListener('DOMContentLoaded', function() {
    new SistemaNotificacoes();
});

// Estilos para as notificações toast
const style = document.createElement('style');
style.textContent = `
    .toast-notification {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 10px;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    }
    
    .toast-notification-info {
        border-left: 4px solid #17a2b8;
    }
    
    .toast-notification-sucesso {
        border-left: 4px solid #28a745;
    }
    
    .toast-notification-aviso {
        border-left: 4px solid #ffc107;
    }
    
    .toast-notification-erro {
        border-left: 4px solid #dc3545;
    }
    
    .toast-header {
        padding: 10px 15px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .toast-body {
        padding: 10px 15px;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
</script>
</body>
</html> 