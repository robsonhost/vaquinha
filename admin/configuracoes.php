<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Carregar configurações atuais
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

$logo = $pdo->query('SELECT * FROM logo_site WHERE id=1')->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Atualizar textos institucionais
        $campos = ['whatsapp', 'email', 'taxa', 'quem_somos'];
        foreach ($campos as $campo) {
            $valor = trim($_POST[$campo] ?? '');
            $stmt = $pdo->prepare('INSERT INTO textos (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?');
            $stmt->execute([$campo, $valor, $valor]);
        }
        
        // Upload de nova logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $nome_arquivo = 'logo_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], '../images/' . $nome_arquivo);
            
            $stmt = $pdo->prepare('UPDATE logo_site SET caminho = ? WHERE id = 1');
            $stmt->execute(['images/' . $nome_arquivo]);
        }
        
        // Atualizar configurações gerais
        $configuracoes = [
            'nome_site' => trim($_POST['nome_site'] ?? ''),
            'descricao_site' => trim($_POST['descricao_site'] ?? ''),
            'taxa_padrao' => floatval($_POST['taxa_padrao'] ?? 0),
            'manutencao' => isset($_POST['manutencao']) ? 1 : 0,
            'registro_usuarios' => isset($_POST['registro_usuarios']) ? 1 : 0,
            'aprovacao_campanhas' => isset($_POST['aprovacao_campanhas']) ? 1 : 0,
            'max_upload_size' => intval($_POST['max_upload_size'] ?? 5),
            'itens_por_pagina' => intval($_POST['itens_por_pagina'] ?? 12)
        ];
        
        foreach ($configuracoes as $chave => $valor) {
            $stmt = $pdo->prepare('INSERT INTO textos (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?');
            $stmt->execute([$chave, $valor, $valor]);
        }
        
        $sucesso = 'Configurações atualizadas com sucesso!';
        
        // Recarregar dados
        foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
            $textos[$t['chave']] = $t['valor'];
        }
        $logo = $pdo->query('SELECT * FROM logo_site WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $erro = 'Erro ao salvar configurações: ' . $e->getMessage();
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
                        <h1>Configurações Gerais</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Configurações</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Configurações Básicas -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-cog"></i> Configurações Básicas
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Nome do Site</label>
                                        <input type="text" name="nome_site" class="form-control" value="<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Descrição do Site</label>
                                        <textarea name="descricao_site" class="form-control" rows="3"><?= htmlspecialchars($textos['descricao_site'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Taxa Padrão (%)</label>
                                        <input type="number" name="taxa_padrao" class="form-control" step="0.01" min="0" max="100" value="<?= htmlspecialchars($textos['taxa_padrao'] ?? '2.5') ?>">
                                        <small class="text-muted">Taxa padrão cobrada sobre doações</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Itens por Página</label>
                                        <input type="number" name="itens_por_pagina" class="form-control" min="5" max="50" value="<?= htmlspecialchars($textos['itens_por_pagina'] ?? '12') ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Tamanho Máximo de Upload (MB)</label>
                                        <input type="number" name="max_upload_size" class="form-control" min="1" max="50" value="<?= htmlspecialchars($textos['max_upload_size'] ?? '5') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Configurações de Funcionamento -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-toggle-on"></i> Configurações de Funcionamento
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="manutencao" name="manutencao" <?= ($textos['manutencao'] ?? 0) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="manutencao">Modo Manutenção</label>
                                        </div>
                                        <small class="text-muted">Ativa a página de manutenção</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="registro_usuarios" name="registro_usuarios" <?= ($textos['registro_usuarios'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="registro_usuarios">Permitir Registro de Usuários</label>
                                        </div>
                                        <small class="text-muted">Permite que novos usuários se cadastrem</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="aprovacao_campanhas" name="aprovacao_campanhas" <?= ($textos['aprovacao_campanhas'] ?? 1) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="aprovacao_campanhas">Aprovação Manual de Campanhas</label>
                                        </div>
                                        <small class="text-muted">Campanhas precisam ser aprovadas por admin</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Logo do Site -->
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-image"></i> Logo do Site
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <?php if ($logo && $logo['caminho']): ?>
                                        <div class="text-center mb-3">
                                            <img src="../<?= htmlspecialchars($logo['caminho']) ?>" alt="Logo atual" style="max-width:200px;max-height:100px;">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label>Nova Logo</label>
                                        <input type="file" name="logo" class="form-control-file" accept="image/*" id="inputLogo" onchange="previewLogo(event)">
                                        <small class="text-muted">Formatos: JPG, PNG, GIF. Máx: 2MB.</small>
                                        <div id="logoPreview" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações de Contato -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-address-book"></i> Informações de Contato
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>WhatsApp</label>
                                                <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($textos['whatsapp'] ?? '') ?>" placeholder="(11) 99999-9999">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>E-mail de Contato</label>
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($textos['email'] ?? '') ?>" placeholder="contato@site.com">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Quem Somos</label>
                                        <textarea name="quem_somos" class="form-control" rows="4" placeholder="Texto sobre a empresa/organização..."><?= htmlspecialchars($textos['quem_somos'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Salvar Configurações
                                    </button>
                                    <a href="index.php" class="btn btn-secondary btn-lg ml-2">
                                        <i class="fas fa-arrow-left"></i> Voltar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Informações do Sistema -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle"></i> Informações do Sistema
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-server"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Versão PHP</span>
                                                <span class="info-box-number"><?= PHP_VERSION ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-database"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">MySQL</span>
                                                <span class="info-box-number"><?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Fuso Horário</span>
                                                <span class="info-box-number"><?= date_default_timezone_get() ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-danger"><i class="fas fa-calendar"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Data/Hora</span>
                                                <span class="info-box-number"><?= date('d/m/Y H:i') ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
function previewLogo(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('logoPreview');
    preview.innerHTML = '';
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
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview Logo" style="max-width:200px;max-height:100px;">`;
        };
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html> 