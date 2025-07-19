<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $meta = $_POST['meta'] ?? 0;
    $imagem = '';
    $categoria_id = $_POST['categoria_id'] ?? null;
    $destaque = $_POST['destaque'] ?? 0;
    $taxa_destaque = $_POST['taxa_destaque'] ?? 0;
    $usuario_id = $_POST['usuario_id'] ?? null;

    // Upload de imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = 'camp_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], '../uploads/' . $nome_arquivo);
        $imagem = 'uploads/' . $nome_arquivo;
    }

    if (!$categoria_id) {
        $msg = 'Selecione uma categoria para a campanha.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO campanhas (titulo, descricao, meta, imagem, status, categoria_id, destaque, taxa_destaque, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$titulo, $descricao, $meta, $imagem, 'pendente', $categoria_id, $destaque, $taxa_destaque, $usuario_id]);
        $msg = 'Campanha criada com sucesso!';
    }
}
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $pdo->query('SELECT * FROM usuarios WHERE tipo="usuario" ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>Nova Campanha</h1></div></section>
        <section class="content">
            <div class="container-fluid">
                <?php if ($msg): ?><div class="alert alert-success"> <?= $msg ?> </div><?php endif; ?>
                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Título da Campanha</label>
                                        <input type="text" name="titulo" class="form-control" required>
                                        <div class="invalid-feedback">Informe o título da campanha.</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Descrição</label>
                                        <textarea name="descricao" class="form-control" rows="5" required></textarea>
                                        <div class="invalid-feedback">Informe a descrição da campanha.</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Meta de Arrecadação (R$)</label>
                                                <input type="number" name="meta" class="form-control" step="0.01" min="1" required>
                                                <div class="invalid-feedback">Informe a meta de arrecadação.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Usuário Responsável</label>
                                                <select name="usuario_id" class="form-control">
                                                    <option value="">Selecione um usuário (opcional)</option>
                                                    <?php foreach ($usuarios as $u): ?>
                                                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nome']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Categoria</label>
                                        <select name="categoria_id" class="form-control" required>
                                            <option value="">Selecione uma categoria</option>
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?= $cat['id'] ?>">
                                                    <?= htmlspecialchars($cat['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Selecione uma categoria.</div>
                                        <div class="mt-2">
                                            <?php foreach ($categorias as $cat): ?>
                                                <?php if ($cat['imagem']): ?>
                                                    <img src="../<?= htmlspecialchars($cat['imagem']) ?>" alt="img" style="max-width:40px;max-height:40px;" title="<?= htmlspecialchars($cat['nome']) ?>">
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Tipo de Campanha</label>
                                        <select name="destaque" class="form-control" required onchange="toggleTaxaDestaque(this.value)">
                                            <option value="0">Campanha comum (taxa padrão)</option>
                                            <option value="1">Destaque (taxa maior, aparece em destaque)</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="taxa_destaque_group" style="display:none;">
                                        <label>Taxa para Destaque (%)</label>
                                        <input type="number" name="taxa_destaque" class="form-control" min="0" max="100" step="0.01">
                                        <small class="text-muted">A taxa de destaque será aplicada a esta campanha.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Imagem da Campanha</label>
                                        <input type="file" name="imagem" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview-imagem')">
                                        <div id="preview-imagem" class="mt-2"></div>
                                        <small class="text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB.</small>
                                    </div>
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Dicas:</h6>
                                        <ul class="mb-0 small">
                                            <li>Use imagens de boa qualidade</li>
                                            <li>Dimensões recomendadas: 800x600px</li>
                                            <li>Campanhas em destaque aparecem primeiro na home</li>
                                            <li>Taxa de destaque é cobrada sobre doações</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg">Criar Campanha</button>
                                <a href="campanhas.php" class="btn btn-secondary">Cancelar</a>
                            </div>
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

// Toggle taxa de destaque
function toggleTaxaDestaque(value) {
    var grupo = document.getElementById('taxa_destaque_group');
    if (value == '1') {
        grupo.style.display = 'block';
    } else {
        grupo.style.display = 'none';
    }
}

// Preview de imagem
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width:200px;max-height:200px;">';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html> 