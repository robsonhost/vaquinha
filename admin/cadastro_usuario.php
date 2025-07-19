<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$erro = $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo = $_POST['tipo'] ?? 'usuario';
    $foto_perfil = '';
    
    // Upload de foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = 'perfil_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], '../images/' . $nome_arquivo);
        $foto_perfil = 'images/' . $nome_arquivo;
    }
    
    if (!$nome || !$email || !$senha) {
        $erro = 'Preencha todos os campos!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres!';
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha, tipo, foto_perfil) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$nome, $email, $hash, $tipo, $foto_perfil]);
            $sucesso = 'Usuário cadastrado com sucesso!';
        } catch (PDOException $e) {
            $erro = 'Erro ao cadastrar: ' . ($e->errorInfo[1] == 1062 ? 'E-mail já cadastrado!' : $e->getMessage());
        }
    }
}
?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>Cadastrar Novo Usuário</h1></div></section>
        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" name="nome" class="form-control" required>
                                <div class="invalid-feedback">Informe o nome.</div>
                            </div>
                            <div class="form-group">
                                <label>E-mail</label>
                                <input type="email" name="email" class="form-control" required>
                                <div class="invalid-feedback">Informe um e-mail válido.</div>
                            </div>
                            <div class="form-group">
                                <label>Senha</label>
                                <input type="password" name="senha" class="form-control" minlength="6" required>
                                <div class="invalid-feedback">A senha deve ter pelo menos 6 caracteres.</div>
                            </div>
                            <div class="form-group">
                                <label>Tipo</label>
                                <select name="tipo" class="form-control" required>
                                    <option value="usuario">Usuário</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Foto de Perfil (opcional)</label>
                                <input type="file" name="foto_perfil" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview-foto')">
                                <div id="preview-foto" class="mt-2"></div>
                            </div>
                            <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
                            <a href="usuarios.php" class="btn btn-secondary">Voltar</a>
                        </form>
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