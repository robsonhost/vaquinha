<?php
require 'admin/db.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$erro = $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $foto_perfil = $user['foto_perfil'];
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = 'perfil_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], 'images/' . $nome_arquivo);
        $foto_perfil = 'images/' . $nome_arquivo;
    }
    if (!$nome || !$email) {
        $erro = 'Preencha todos os campos!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET nome=?, email=?, foto_perfil=? WHERE id=?');
        $stmt->execute([$nome, $email, $foto_perfil, $id]);
        $sucesso = 'Dados atualizados!';
        $_SESSION['usuario_nome'] = $nome;
        $user['nome'] = $nome;
        $user['email'] = $email;
        $user['foto_perfil'] = $foto_perfil;
    }
}
// Histórico de campanhas
$campanhas = $pdo->prepare('SELECT * FROM campanhas WHERE usuario_id = ? ORDER BY criado_em DESC');
$campanhas->execute([$id]);
$campanhas = $campanhas->fetchAll(PDO::FETCH_ASSOC);
// Histórico de doações
$doacoes = $pdo->prepare('SELECT d.*, c.titulo as campanha FROM doacoes d JOIN campanhas c ON d.campanha_id = c.id WHERE d.usuario_id = ? ORDER BY d.criado_em DESC');
$doacoes->execute([$id]);
$doacoes = $doacoes->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '_header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h2 class="mb-4">Meu Perfil</h2>
                    <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                    <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Nome</label>
                                    <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($user['nome']) ?>" required>
                                    <div class="invalid-feedback">Informe seu nome.</div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>E-mail</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    <div class="invalid-feedback">Informe um e-mail válido.</div>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Telefone</label>
                                    <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>" placeholder="(99) 99999-9999">
                                </div>
                                <div class="form-group mb-3">
                                    <label>CPF</label>
                                    <input type="text" name="cpf" class="form-control" value="<?= htmlspecialchars($user['cpf'] ?? '') ?>" placeholder="000.000.000-00">
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <label>Foto de Perfil</label><br>
                                <div class="mb-3">
                                    <img id="previewFotoPerfil" src="<?= $user['foto_perfil'] ? htmlspecialchars($user['foto_perfil']) : 'images/default-avatar.png' ?>" alt="Foto de perfil" style="max-width:120px;max-height:120px;border-radius:50%;object-fit:cover;">
                                </div>
                                <input type="file" name="foto_perfil" class="form-control-file" accept="image/*" onchange="previewFoto(event)">
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Máx: 2MB.</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar</button>
                            <a href="alterar_senha.php" class="btn btn-outline-warning"><i class="fas fa-key"></i> Alterar Senha</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h4 class="mb-3">Minhas Campanhas</h4>
                    <?php if (count($campanhas) === 0): ?><p>Nenhuma campanha cadastrada.</p><?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Título</th><th>Status</th><th>Meta</th><th>Arrecadado</th><th>Criada em</th></tr></thead>
                            <tbody>
                            <?php foreach ($campanhas as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['titulo']) ?></td>
                                <td><span class="badge bg-<?= $c['status']==='aprovada'?'success':($c['status']==='pendente'?'warning':'secondary') ?>"><?= ucfirst($c['status'] ?? 'pendente') ?></span></td>
                                <td>R$ <?= number_format($c['meta'],2,',','.') ?></td>
                                <td>R$ <?= number_format($c['arrecadado'],2,',','.') ?></td>
                                <td><?= date('d/m/Y', strtotime($c['criado_em'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="mb-3">Minhas Doações</h4>
                    <?php if (count($doacoes) === 0): ?><p>Nenhuma doação realizada.</p><?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Campanha</th><th>Valor</th><th>Status</th><th>Data</th></tr></thead>
                            <tbody>
                            <?php foreach ($doacoes as $d): ?>
                            <tr>
                                <td><?= htmlspecialchars($d['campanha']) ?></td>
                                <td>R$ <?= number_format($d['valor'],2,',','.') ?></td>
                                <td><span class="badge bg-<?= $d['status']==='confirmada'?'success':($d['status']==='pendente'?'warning':'secondary') ?>"><?= ucfirst($d['status']) ?></span></td>
                                <td><?= date('d/m/Y', strtotime($d['criado_em'])) ?></td>
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
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewFoto(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('previewFotoPerfil');
    if (file) {
        const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowed.includes(file.type)) {
            alert('Formato de imagem não suportado.');
            event.target.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Tamanho máximo permitido: 2MB');
            event.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
(function() {
  'use strict';
  window.addEventListener('load', function() {
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();
</script> 