<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Ações
$msg = '';
if (isset($_GET['ativar'])) {
    $id = intval($_GET['ativar']);
    $pdo->prepare('UPDATE usuarios SET status="ativo" WHERE id=?')->execute([$id]);
    $msg = 'Usuário ativado com sucesso!';
}
if (isset($_GET['desativar'])) {
    $id = intval($_GET['desativar']);
    $pdo->prepare('UPDATE usuarios SET status="inativo" WHERE id=?')->execute([$id]);
    $msg = 'Usuário desativado com sucesso!';
}
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $pdo->prepare('DELETE FROM usuarios WHERE id=? AND tipo="usuario"')->execute([$id]);
    $msg = 'Usuário excluído com sucesso!';
}

// Busca e filtros
$busca = trim($_GET['busca'] ?? '');
$tipo = $_GET['tipo'] ?? '';
$status = $_GET['status'] ?? '';

$sql = 'SELECT * FROM usuarios WHERE 1=1';
$params = [];

if ($busca) {
    $sql .= ' AND (nome LIKE ? OR email LIKE ?)';
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}
if ($tipo) {
    $sql .= ' AND tipo = ?';
    $params[] = $tipo;
}
if ($status) {
    $sql .= ' AND status = ?';
    $params[] = $status;
}

$sql .= ' ORDER BY criado_em DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$totalUsuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$totalAdmins = $pdo->query('SELECT COUNT(*) FROM usuarios WHERE tipo="admin"')->fetchColumn();
$totalAtivos = $pdo->query('SELECT COUNT(*) FROM usuarios WHERE status="ativo"')->fetchColumn();
$totalInativos = $pdo->query('SELECT COUNT(*) FROM usuarios WHERE status="inativo"')->fetchColumn();
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
                        <h1>Gerenciar Usuários</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Usuários</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <?php if ($msg): ?><div class="alert alert-success"> <?= $msg ?> </div><?php endif; ?>
                
                <!-- Cards de estatísticas -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $totalUsuarios ?></h3>
                                <p>Total de Usuários</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $totalAdmins ?></h3>
                                <p>Administradores</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $totalAtivos ?></h3>
                                <p>Usuários Ativos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $totalInativos ?></h3>
                                <p>Usuários Inativos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-times"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Filtros</h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Buscar</label>
                                    <input type="text" name="busca" class="form-control" placeholder="Nome ou e-mail" value="<?= htmlspecialchars($busca) ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo</label>
                                    <select name="tipo" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="admin" <?= $tipo === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                        <option value="usuario" <?= $tipo === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="submit" class="btn btn-primary">Filtrar</button>
                                        <a href="usuarios.php" class="btn btn-secondary">Limpar</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Lista de usuários -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Usuários</h3>
                        <div class="card-tools">
                            <a href="cadastro_usuario.php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Novo Usuário
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Foto</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td>
                                        <?php if ($u['foto_perfil']): ?>
                                            <img src="../<?= htmlspecialchars($u['foto_perfil']) ?>" alt="Foto" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                        <?php else: ?>
                                            <div style="width:40px;height:40px;border-radius:50%;background:#ddd;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['nome']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $u['tipo'] === 'admin' ? 'danger' : 'info' ?>">
                                            <?= ucfirst($u['tipo']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $u['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($u['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($u['criado_em'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="editar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($u['status'] === 'ativo'): ?>
                                                <a href="?desativar=<?= $u['id'] ?>" class="btn btn-sm btn-warning" title="Desativar" onclick="return confirm('Desativar usuário?')">
                                                    <i class="fas fa-user-times"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?ativar=<?= $u['id'] ?>" class="btn btn-sm btn-success" title="Ativar">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($u['tipo'] === 'usuario'): ?>
                                                <a href="?excluir=<?= $u['id'] ?>" class="btn btn-sm btn-danger" title="Excluir" onclick="return confirm('Excluir usuário? Esta ação não pode ser desfeita.')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($usuarios) === 0): ?>
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <p>Nenhum usuário encontrado.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?>
</body>
</html> 