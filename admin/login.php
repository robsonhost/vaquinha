<?php
require 'db.php';
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}
session_start();
if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';
    // Busca admin real no banco
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE (email = ? OR nome = ?) AND tipo = "admin"');
    $stmt->execute([$usuario, $usuario]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($senha, $admin['senha'])) {
        $_SESSION['admin'] = $admin['nome'];
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Usuário ou senha inválidos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0">
                <div class="card-body">
                    <h2 class="mb-4 text-center">Login Admin</h2>
                    <p class="text-center text-muted mb-4">Acesse o painel administrativo.<br><small>Admin: <b>admin@teste.com</b> | Senha: <b>admin123</b></small></p>
                    <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                    <form method="post" class="needs-validation" novalidate autocomplete="on">
                        <div class="form-group">
                            <label>Usuário ou E-mail</label>
                            <input type="text" name="usuario" class="form-control" required autofocus>
                            <div class="invalid-feedback">Informe o usuário ou e-mail.</div>
                        </div>
                        <div class="form-group position-relative">
                            <label>Senha</label>
                            <input type="password" name="senha" class="form-control" id="senha" required>
                            <button type="button" class="btn btn-sm btn-outline-secondary position-absolute" style="top:32px;right:10px;z-index:2;" onclick="mostrarSenha()">👁️</button>
                            <div class="invalid-feedback">Informe sua senha.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                    </form>
                    <hr>
                    <div class="text-center text-muted small">
                        <b>WhatsApp:</b> <?= htmlspecialchars($textos['whatsapp'] ?? '') ?><br>
                        <b>E-mail:</b> <?= htmlspecialchars($textos['email'] ?? '') ?><br>
                        <b>Taxa do site:</b> <?= htmlspecialchars($textos['taxa'] ?? '') ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
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
function mostrarSenha() {
  var x = document.getElementById('senha');
  if (x.type === 'password') { x.type = 'text'; } else { x.type = 'password'; }
}
</script>
</body>
</html> 