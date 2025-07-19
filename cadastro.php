<?php
require 'admin/db.php';
require 'includes/util.php';
require 'includes/whatsapp.php';
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

// Carregar tema ativo
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

$erro = $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';
    $foto_perfil = '';
    
    // Upload de foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
        $nome_arquivo = 'perfil_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], 'images/' . $nome_arquivo);
        $foto_perfil = 'images/' . $nome_arquivo;
    }
    
    if (!$nome || !$email || !$telefone || !$cpf || !$senha || !$senha2) {
        $erro = 'Preencha todos os campos!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido!';
    } elseif (!preg_match('/^\d{11}$/', preg_replace('/\D/', '', $cpf))) {
        $erro = 'CPF inválido!';
    } elseif ($senha !== $senha2) {
        $erro = 'As senhas não conferem!';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres!';
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, telefone, cpf, senha, tipo, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nome, $email, $telefone, $cpf, $hash, 'usuario', $foto_perfil]);
            $sucesso = 'Cadastro realizado com sucesso! Faça login para criar sua campanha.';
            // Enviar WhatsApp de boas-vindas
            $senha_aleatoria = $senha; // já foi digitada pelo usuário
            $mensagem = "🎉 Olá, $nome! Sua conta foi criada com sucesso na " . ($textos['nome_site'] ?? 'Vaquinha Online') . "!\nAcesse: https://" . $_SERVER['HTTP_HOST'] . "/entrar.php\nE-mail: $email\nSenha: $senha_aleatoria\n🚀 Dica: Compartilhe sua campanha para alcançar sua meta mais rápido!\nConte com a gente para transformar sonhos em realidade! 💙";
            if ($telefone) {
                enviar_whatsapp($telefone, $mensagem);
            }
            // Enviar e-mail de boas-vindas (opcional)
            // ...
        } catch (PDOException $e) {
            $erro = 'Erro ao cadastrar: ' . ($e->errorInfo[1] == 1062 ? 'E-mail já cadastrado!' : $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - <?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --cor-primaria: <?= $tema['cor_primaria'] ?>;
            --cor-secundaria: <?= $tema['cor_secundaria'] ?>;
            --cor-terciaria: <?= $tema['cor_terciaria'] ?>;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .cadastro-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .cadastro-header {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .cadastro-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cadastro-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .cadastro-body {
            padding: 3rem 2rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--cor-primaria);
            box-shadow: 0 0 0 0.2rem rgba(120, 47, 155, 0.25);
        }
        
        .form-floating label {
            padding: 1rem 1.25rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .foto-upload {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .foto-upload:hover {
            border-color: var(--cor-primaria);
            background-color: rgba(120, 47, 155, 0.05);
        }
        
        .foto-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--cor-primaria);
        }
        
        .senha-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
        }
        
        .senha-toggle:hover {
            color: var(--cor-primaria);
        }
        
        .vantagens-cadastro {
            background: var(--cor-terciaria);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .vantagem-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .vantagem-item i {
            color: var(--cor-primaria);
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .cadastro-container {
                margin: 1rem;
            }
            
            .cadastro-header {
                padding: 2rem 1rem;
            }
            
            .cadastro-header h1 {
                font-size: 2rem;
            }
            
            .cadastro-body {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="cadastro-container">
                    <!-- Header -->
                    <div class="cadastro-header">
                        <h1><i class="fas fa-user-plus"></i> Criar Conta</h1>
                        <p>Junte-se a milhares de pessoas que já ajudaram causas importantes</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="cadastro-body">
                        <?php if ($erro): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= $erro ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($sucesso): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?= $sucesso ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Vantagens -->
                        <div class="vantagens-cadastro">
                            <h5 class="mb-3"><i class="fas fa-star text-warning"></i> Por que se cadastrar?</h5>
                            <div class="vantagem-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Crie campanhas ilimitadas</span>
                            </div>
                            <div class="vantagem-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Acompanhe suas doações</span>
                            </div>
                            <div class="vantagem-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Receba notificações</span>
                            </div>
                            <div class="vantagem-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Totalmente gratuito</span>
                            </div>
                        </div>
                        
                        <!-- Formulário -->
                        <form method="post" enctype="multipart/form-data" autocomplete="off" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="nome" class="form-control" id="nome" placeholder="Seu nome completo" required>
                                        <label for="nome"><i class="fas fa-user me-2"></i>Nome completo</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" name="email" class="form-control" id="email" placeholder="seu@email.com" required>
                                        <label for="email"><i class="fas fa-envelope me-2"></i>E-mail</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="telefone" class="form-control" id="telefone" placeholder="(11) 99999-9999" required maxlength="15">
                                        <label for="telefone"><i class="fas fa-phone me-2"></i>Telefone (WhatsApp)</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="cpf" class="form-control" id="cpf" placeholder="CPF" required maxlength="14">
                                        <label for="cpf"><i class="fas fa-id-card me-2"></i>CPF</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="password" name="senha" class="form-control" id="senha" placeholder="Sua senha" minlength="6" required>
                                        <label for="senha"><i class="fas fa-lock me-2"></i>Senha</label>
                                        <button type="button" class="senha-toggle" onclick="toggleSenha('senha')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating position-relative">
                                        <input type="password" name="senha2" class="form-control" id="senha2" placeholder="Confirme sua senha" minlength="6" required>
                                        <label for="senha2"><i class="fas fa-lock me-2"></i>Confirmar senha</label>
                                        <button type="button" class="senha-toggle" onclick="toggleSenha('senha2')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Upload de Foto -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-camera me-2"></i>Foto de perfil (opcional)
                                </label>
                                <div class="foto-upload" onclick="document.getElementById('foto_perfil').click()">
                                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" style="display: none;" onchange="previewFoto(this)">
                                    <div id="foto-preview-container">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">Clique para selecionar uma foto</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botão Submit -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Criar Conta
                                </button>
                            </div>
                            
                            <!-- Link para Login -->
                            <div class="text-center mt-4">
                                <p class="text-muted">
                                    Já tem uma conta? 
                                    <a href="login.php" class="text-decoration-none fw-bold" style="color: var(--cor-primaria);">
                                        Faça login aqui
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
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

        // Toggle senha
        function toggleSenha(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Preview de foto
        function previewFoto(input) {
            const container = document.getElementById('foto-preview-container');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    container.innerHTML = `
                        <img src="${e.target.result}" class="foto-preview" alt="Preview">
                        <p class="text-muted mt-2 mb-0">Foto selecionada</p>
                    `;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Validação de senha em tempo real
        document.getElementById('senha2').addEventListener('input', function() {
            const senha = document.getElementById('senha').value;
            const senha2 = this.value;
            
            if (senha2 && senha !== senha2) {
                this.setCustomValidity('As senhas não conferem');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 