<?php
require 'admin/db.php';
require 'includes/whatsapp.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$erro = $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $meta = $_POST['meta'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? null;
    $destaque = $_POST['destaque'] ?? 0;
    $taxa_destaque = $_POST['taxa_destaque'] ?? 0;
    $imagem = '';
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = uniqid('img_') . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], 'uploads/' . $nome_arquivo);
        $imagem = 'uploads/' . $nome_arquivo;
    }
    
    if (!$titulo || !$descricao || !$meta || !$categoria_id) {
        $erro = 'Preencha todos os campos obrigatórios!';
    } else {
        $stmt = $pdo->prepare('INSERT INTO campanhas (titulo, descricao, meta, imagem, usuario_id, categoria_id, destaque, taxa_destaque, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$titulo, $descricao, $meta, $imagem, $_SESSION['usuario_id'], $categoria_id, $destaque, $taxa_destaque, 'pendente']);
        $sucesso = 'Campanha cadastrada com sucesso! Aguarde aprovação.';
        // Notificação WhatsApp para o criador
        $stmt = $pdo->prepare('SELECT nome, telefone FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario && $usuario['telefone']) {
            $msg = "🎉 Olá, {$usuario['nome']}! Sua campanha ‘$titulo’ foi criada e está aguardando aprovação.\nAssim que for aprovada, você poderá começar a receber doações!\n\n🚀 Dica: Prepare uma boa divulgação para alcançar sua meta mais rápido!\nEstamos juntos nessa jornada! 💙";
            enviar_whatsapp($usuario['telefone'], $msg);
        }
    }
}
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastrar Campanha - Vaquinha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0 text-center"><i class="fas fa-plus-circle"></i> Nova Campanha</h2>
                </div>
                <div class="card-body">
                    <?php if ($erro): ?><div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $erro ?></div><?php endif; ?>
                    <?php if ($sucesso): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $sucesso ?></div><?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label><i class="fas fa-heading"></i> Título da Campanha</label>
                                    <input type="text" name="titulo" class="form-control form-control-lg" placeholder="Ex: Ajude João com o tratamento médico" required>
                                    <div class="invalid-feedback">Informe o título da campanha.</div>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-align-left"></i> Descrição</label>
                                    <textarea name="descricao" class="form-control" rows="6" placeholder="Conte sua história, explique o objetivo da campanha..." required></textarea>
                                    <div class="invalid-feedback">Informe a descrição da campanha.</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-dollar-sign"></i> Meta de Arrecadação (R$)</label>
                                            <input type="number" name="meta" class="form-control" step="0.01" min="1" placeholder="0,00" required>
                                            <div class="invalid-feedback">Informe a meta de arrecadação.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><i class="fas fa-tag"></i> Categoria</label>
                                            <select name="categoria_id" class="form-control" required>
                                                <option value="">Selecione uma categoria</option>
                                                <?php foreach ($categorias as $cat): ?>
                                                    <option value="<?= $cat['id'] ?>">
                                                        <?= htmlspecialchars($cat['nome']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Selecione uma categoria.</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-star"></i> Tipo de Campanha</label>
                                    <select name="destaque" class="form-control" required onchange="toggleTaxaDestaque(this.value)">
                                        <option value="0">Campanha comum (taxa padrão)</option>
                                        <option value="1">Destaque (taxa maior, aparece em destaque)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" id="taxa_destaque_group" style="display:none;">
                                    <label><i class="fas fa-percentage"></i> Taxa para Destaque (%)</label>
                                    <input type="number" name="taxa_destaque" class="form-control" min="0" max="100" step="0.01" placeholder="5.00">
                                    <small class="text-muted">A taxa de destaque será aplicada a esta campanha.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-image"></i> Imagem da Campanha</label>
                                    <input type="file" name="imagem" class="form-control-file" accept="image/*" onchange="previewImage(this, 'preview-imagem')">
                                    <div id="preview-imagem" class="mt-2"></div>
                                    <small class="text-muted">Formatos: JPG, PNG, GIF. Máx: 5MB.</small>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Dicas para uma boa campanha:</h6>
                                    <ul class="mb-0 small">
                                        <li>Use um título claro e objetivo</li>
                                        <li>Conte uma história emocionante</li>
                                        <li>Adicione uma imagem de qualidade</li>
                                        <li>Defina uma meta realista</li>
                                        <li>Campanhas em destaque têm mais visibilidade</li>
                                    </ul>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-clock"></i> Processo de Aprovação:</h6>
                                    <p class="mb-0 small">Sua campanha será revisada pela nossa equipe antes de ser publicada. Isso pode levar até 24 horas.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-rocket"></i> Criar Campanha
                            </button>
                            <a href="area_usuario.php" class="btn btn-secondary btn-lg px-5 ml-2">
                                <i class="fas fa-arrow-left"></i> Voltar
                            </a>
                        </div>
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