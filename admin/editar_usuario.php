<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

$erro = $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $tipo = $_POST['tipo'] ?? 'usuario';
    $status = $_POST['status'] ?? 'ativo';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $foto_perfil = $usuario['foto_perfil'];
    
    // Upload de nova foto
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = 'perfil_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], '../images/' . $nome_arquivo);
        $foto_perfil = 'images/' . $nome_arquivo;
    }
    
    if (!$nome || !$email) {
        $erro = 'Preencha todos os campos obrigatórios!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } else {
        try {
            // Verificar se email já existe em outro usuário
            $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $erro = 'Este e-mail já está sendo usado por outro usuário!';
            } else {
                // Atualizar dados básicos
                $sql = 'UPDATE usuarios SET nome=?, email=?, tipo=?, status=?, foto_perfil=? WHERE id=?';
                $params = [$nome, $email, $tipo, $status, $foto_perfil, $id];
                
                // Se nova senha foi fornecida, atualizar também
                if ($nova_senha) {
                    if (strlen($nova_senha) < 6) {
                        $erro = 'A nova senha deve ter pelo menos 6 caracteres!';
                    } else {
                        $sql = 'UPDATE usuarios SET nome=?, email=?, tipo=?, status=?, foto_perfil=?, senha=? WHERE id=?';
                        $params = [$nome, $email, $tipo, $status, $foto_perfil, password_hash($nova_senha, PASSWORD_DEFAULT), $id];
                    }
                }
                
                if (!$erro) {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $sucesso = 'Usuário atualizado com sucesso!';
                    
                    // Atualizar dados na sessão se for o usuário logado
                    if ($id == $_SESSION['admin_id'] ?? 0) {
                        $_SESSION['admin'] = $nome;
                    }
                    
                    // Atualizar dados locais
                    $usuario['nome'] = $nome;
                    $usuario['email'] = $email;
                    $usuario['tipo'] = $tipo;
                    $usuario['status'] = $status;
                    $usuario['foto_perfil'] = $foto_perfil;
                }
            }
        } catch (Exception $e) {
            $erro = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }
}
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
                        <h1>Editar Usuário</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="usuarios.php">Usuários</a></li>
                            <li class="breadcrumb-item active">Editar</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Dados do Usuário</h3>
                            </div>
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Nome</label>
                                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                                <div class="invalid-feedback">Informe o nome.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>E-mail</label>
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                                <div class="invalid-feedback">Informe um e-mail válido.</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tipo</label>
                                                <select name="tipo" class="form-control" required>
                                                    <option value="usuario" <?= $usuario['tipo'] === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                                                    <option value="admin" <?= $usuario['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="ativo" <?= $usuario['status'] === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                                    <option value="inativo" <?= $usuario['status'] === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Nova Senha (deixe em branco para manter a atual)</label>
                                        <input type="password" name="nova_senha" class="form-control" minlength="6" placeholder="Mínimo 6 caracteres">
                                        <small class="text-muted">Preencha apenas se quiser alterar a senha.</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Salvar Alterações
                                        </button>
                                        <a href="usuarios.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Voltar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Foto de Perfil</h3>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($usuario['foto_perfil']): ?>
                                    <img src="../<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto atual" class="img-fluid rounded" style="max-width:200px;max-height:200px;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:200px;height:200px;margin:0 auto;">
                                        <i class="fas fa-user fa-4x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <label>Nova Foto</label>
                                    <input type="file" name="foto_perfil" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview-foto')">
                                    <div id="preview-foto" class="mt-2"></div>
                                    <small class="text-muted">Formatos: JPG, PNG, GIF. Máx: 5MB.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Informações</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>ID:</strong> <?= $usuario['id'] ?></p>
                                <p><strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($usuario['criado_em'])) ?></p>
                                <p><strong>Última atualização:</strong> <?= date('d/m/Y H:i') ?></p>
                                
                                <?php if ($usuario['tipo'] === 'admin'): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Atenção:</strong> Este é um usuário administrador. Tenha cuidado ao fazer alterações.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?>
<script>
// Validação Bootstrap
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

// Preview de imagem
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width:150px;max-height:150px;">';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html> 