<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Buscar dados do admin
$stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header('Location: logout.php');
    exit;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $senha_atual = $_POST['senha_atual'] ?? '';
        $nova_senha = $_POST['nova_senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        
        // Validações
        if (empty($nome)) {
            throw new Exception('Nome é obrigatório');
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('E-mail inválido');
        }
        
        // Verificar se email já existe (exceto o próprio)
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = ? AND id != ?');
        $stmt->execute([$email, $_SESSION['admin_id']]);
        if ($stmt->fetch()) {
            throw new Exception('Este e-mail já está em uso');
        }
        
        // Upload de foto
        $foto_path = $admin['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['foto']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG ou GIF.');
            }
            
            $file_size = $_FILES['foto']['size'];
            if ($file_size > 5 * 1024 * 1024) { // 5MB
                throw new Exception('Arquivo muito grande. Máximo 5MB.');
            }
            
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'admin_' . $_SESSION['admin_id'] . '_' . uniqid() . '.' . $ext;
            $upload_path = '../uploads/admins/' . $filename;
            
            // Criar diretório se não existir
            if (!is_dir('../uploads/admins')) {
                mkdir('../uploads/admins', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                // Deletar foto antiga se existir
                if ($admin['foto'] && file_exists('../' . $admin['foto'])) {
                    unlink('../' . $admin['foto']);
                }
                $foto_path = 'uploads/admins/' . $filename;
            } else {
                throw new Exception('Erro ao fazer upload da foto');
            }
        }
        
        // Troca de senha
        if (!empty($nova_senha)) {
            if (empty($senha_atual)) {
                throw new Exception('Senha atual é obrigatória para trocar a senha');
            }
            
            if (!password_verify($senha_atual, $admin['senha'])) {
                throw new Exception('Senha atual incorreta');
            }
            
            if (strlen($nova_senha) < 6) {
                throw new Exception('Nova senha deve ter pelo menos 6 caracteres');
            }
            
            if ($nova_senha !== $confirmar_senha) {
                throw new Exception('Confirmação de senha não confere');
            }
            
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        } else {
            $senha_hash = $admin['senha'];
        }
        
        // Atualizar dados
        $stmt = $pdo->prepare('UPDATE admins SET nome = ?, email = ?, telefone = ?, foto = ?, senha = ?, atualizado_em = NOW() WHERE id = ?');
        $stmt->execute([$nome, $email, $telefone, $foto_path, $senha_hash, $_SESSION['admin_id']]);
        
        // Atualizar dados da sessão
        $_SESSION['admin_nome'] = $nome;
        $_SESSION['admin_email'] = $email;
        
        $sucesso = 'Perfil atualizado com sucesso!';
        
        // Recarregar dados
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar histórico de login
$stmt = $pdo->prepare('SELECT * FROM logs_acesso WHERE admin_id = ? ORDER BY data_acesso DESC LIMIT 10');
$stmt->execute([$_SESSION['admin_id']]);
$historico_login = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1><i class="fas fa-user-edit"></i> Editar Perfil</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Editar Perfil</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"><?= $sucesso ?></div><?php endif; ?>
                
                <div class="row">
                    <!-- Informações do Perfil -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user"></i> Informações do Perfil
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="nome">Nome Completo *</label>
                                                <input type="text" class="form-control" id="nome" name="nome" 
                                                       value="<?= htmlspecialchars($admin['nome']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">E-mail *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?= htmlspecialchars($admin['email']) ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="telefone">Telefone</label>
                                        <input type="text" class="form-control" id="telefone" name="telefone" 
                                               value="<?= htmlspecialchars($admin['telefone'] ?? '') ?>" 
                                               placeholder="(11) 99999-9999">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="foto">Foto de Perfil</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="foto" name="foto" accept="image/*">
                                            <label class="custom-file-label" for="foto">Escolher arquivo</label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Formatos: JPG, PNG, GIF. Máximo: 5MB.
                                        </small>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h5><i class="fas fa-lock"></i> Alterar Senha</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="senha_atual">Senha Atual</label>
                                                <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                                                <small class="form-text text-muted">Obrigatório para trocar a senha</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="nova_senha">Nova Senha</label>
                                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" minlength="6">
                                                <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="confirmar_senha">Confirmar Nova Senha</label>
                                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save"></i> Salvar Alterações
                                        </button>
                                        <a href="index.php" class="btn btn-secondary btn-lg ml-2">
                                            <i class="fas fa-arrow-left"></i> Voltar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Foto e Informações -->
                    <div class="col-md-4">
                        <!-- Foto do Perfil -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-camera"></i> Foto Atual
                                </h3>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($admin['foto'] && file_exists('../' . $admin['foto'])): ?>
                                    <img src="../<?= htmlspecialchars($admin['foto']) ?>" 
                                         alt="Foto do perfil" 
                                         class="img-fluid rounded-circle mb-3" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 150px; height: 150px;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <h5><?= htmlspecialchars($admin['nome']) ?></h5>
                                <p class="text-muted"><?= htmlspecialchars($admin['email']) ?></p>
                                
                                <div class="text-left">
                                    <p><strong>Membro desde:</strong> <?= date('d/m/Y', strtotime($admin['criado_em'])) ?></p>
                                    <p><strong>Última atualização:</strong> <?= $admin['atualizado_em'] ? date('d/m/Y H:i', strtotime($admin['atualizado_em'])) : 'Nunca' ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Histórico de Login -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-history"></i> Últimos Acessos
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Data</th>
                                                <th>IP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($historico_login)): ?>
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">
                                                    <small>Nenhum registro encontrado</small>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($historico_login as $log): ?>
                                                <tr>
                                                    <td>
                                                        <small><?= date('d/m H:i', strtotime($log['data_acesso'])) ?></small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?= htmlspecialchars($log['ip']) ?></small>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Informações de Segurança -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-shield-alt"></i> Segurança
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Dicas de Segurança</h6>
                                    <ul class="mb-0 small">
                                        <li>Use senhas fortes com letras, números e símbolos</li>
                                        <li>Não compartilhe suas credenciais</li>
                                        <li>Faça logout ao sair do sistema</li>
                                        <li>Mantenha seu navegador atualizado</li>
                                    </ul>
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
// Mostrar nome do arquivo selecionado
$('.custom-file-input').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').html(fileName);
});

// Validação de senha
$('#confirmar_senha').on('input', function() {
    var novaSenha = $('#nova_senha').val();
    var confirmarSenha = $(this).val();
    
    if (novaSenha && confirmarSenha) {
        if (novaSenha === confirmarSenha) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
        }
    } else {
        $(this).removeClass('is-valid is-invalid');
    }
});

// Validação do formulário
$('form').on('submit', function(e) {
    var novaSenha = $('#nova_senha').val();
    var confirmarSenha = $('#confirmar_senha').val();
    var senhaAtual = $('#senha_atual').val();
    
    if (novaSenha || confirmarSenha) {
        if (!senhaAtual) {
            e.preventDefault();
            alert('Senha atual é obrigatória para trocar a senha');
            $('#senha_atual').focus();
            return false;
        }
        
        if (novaSenha !== confirmarSenha) {
            e.preventDefault();
            alert('Confirmação de senha não confere');
            $('#confirmar_senha').focus();
            return false;
        }
    }
});
</script> 