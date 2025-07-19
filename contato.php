<?php
$erro = $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    if (!$nome || !$email || !$mensagem) {
        $erro = 'Preencha todos os campos!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } else {
        $sucesso = 'Mensagem enviada! Em breve entraremos em contato.';
    }
}
?>
<?php include '_header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="mb-4 text-center">Contato / Suporte</h2>
                    <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                    <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                    <form method="post" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                            <div class="invalid-feedback">Informe seu nome.</div>
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback">Informe um e-mail válido.</div>
                        </div>
                        <div class="form-group">
                            <label>Mensagem</label>
                            <textarea name="mensagem" class="form-control" required></textarea>
                            <div class="invalid-feedback">Digite sua mensagem.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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