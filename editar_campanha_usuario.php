<?php
require 'admin/db.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM campanhas WHERE id = ? AND usuario_id = ?');
$stmt->execute([$id, $_SESSION['usuario_id']]);
$campanha = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$campanha) {
    echo 'Campanha não encontrada ou acesso negado!';
    exit;
}
$erro = $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $meta = $_POST['meta'] ?? 0;
    $imagem = $campanha['imagem'];
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = uniqid('img_') . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], 'uploads/' . $nome_arquivo);
        $imagem = 'uploads/' . $nome_arquivo;
    }
    if (!$titulo || !$descricao || !$meta) {
        $erro = 'Preencha todos os campos obrigatórios!';
    } else {
        $stmt = $pdo->prepare('UPDATE campanhas SET titulo=?, descricao=?, meta=?, imagem=? WHERE id=? AND usuario_id=?');
        $stmt->execute([$titulo, $descricao, $meta, $imagem, $id, $_SESSION['usuario_id']]);
        $sucesso = 'Campanha atualizada com sucesso!';
        $campanha['titulo'] = $titulo;
        $campanha['descricao'] = $descricao;
        $campanha['meta'] = $meta;
        $campanha['imagem'] = $imagem;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Campanha - Vaquinha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="mb-4 text-center">Editar Campanha</h2>
                    <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                    <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($campanha['titulo']) ?>" required>
                            <div class="invalid-feedback">Informe o título da campanha.</div>
                        </div>
                        <div class="form-group">
                            <label>Descrição</label>
                            <textarea name="descricao" class="form-control" required><?= htmlspecialchars($campanha['descricao']) ?></textarea>
                            <div class="invalid-feedback">Informe a descrição.</div>
                        </div>
                        <div class="form-group">
                            <label>Meta (R$)</label>
                            <input type="number" name="meta" class="form-control" step="0.01" value="<?= $campanha['meta'] ?>" required>
                            <div class="invalid-feedback">Informe a meta de arrecadação.</div>
                        </div>
                        <div class="form-group">
                            <label>Imagem (opcional)</label>
                            <?php if ($campanha['imagem']): ?><br><img src="<?= htmlspecialchars($campanha['imagem']) ?>" alt="Imagem atual" style="max-width:150px;"><br><?php endif; ?>
                            <input type="file" name="imagem" class="form-control-file" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Salvar Alterações</button>
                        <a href="area_usuario.php" class="btn btn-link btn-block">Voltar</a>
                    </form>
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
</script>
</body>
</html> 