<?php
require 'admin/db.php';
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

// Carregar tema ativo
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

session_start();
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if (!$email || !$senha) {
        $erro = 'Preencha todos os campos!';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_tipo'] = $user['tipo'];
            header('Location: area_usuario.php');
            exit;
        } else {
            $erro = 'E-mail ou senha inválidos!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - <?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?></title>
    
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
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .login-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .login-body {
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
        
        .btn-outline-primary {
            border: 2px solid var(--cor-primaria);
            color: var(--cor-primaria);
            border-radius: 12px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: var(--cor-primaria);
            color: white;
            transform: translateY(-2px);
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
        
        .login-options {
            background: var(--cor-terciaria);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .login-option {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border-left: 4px solid var(--cor-primaria);
        }
        
        .login-option i {
            color: var(--cor-primaria);
            margin-right: 1rem;
            font-size: 1.2rem;
        }
        
        .login-option strong {
            color: var(--cor-primaria);
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
            }
            
            .login-header {
                padding: 2rem 1rem;
            }
            
            .login-header h1 {
                font-size: 2rem;
            }
            
            .login-body {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="login-container">
                    <!-- Header -->
                    <div class="login-header">
                        <h1><i class="fas fa-sign-in-alt"></i> Entrar</h1>
                        <p>Acesse sua conta para criar ou doar em vaquinhas</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="login-body">
                        <?php if ($erro): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= $erro ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Opções de Login -->
                        <div class="login-options">
                            <h5 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Contas de Teste</h5>
                            <div class="login-option">
                                <i class="fas fa-user-shield"></i>
                                <div>
                                    <strong>Administrador:</strong><br>
                                    <small>admin@teste.com | admin123</small>
                                </div>
                            </div>
                            <div class="login-option">
                                <i class="fas fa-user"></i>
                                <div>
                                    <strong>Usuário:</strong><br>
                                    <small>usuario@teste.com | usuario123</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Formulário -->
                        <form method="post" class="needs-validation" novalidate>
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control" id="email" placeholder="seu@email.com" required>
                                <label for="email"><i class="fas fa-envelope me-2"></i>E-mail</label>
                            </div>
                            
                            <div class="form-floating position-relative">
                                <input type="password" name="senha" class="form-control" id="senha" placeholder="Sua senha" required>
                                <label for="senha"><i class="fas fa-lock me-2"></i>Senha</label>
                                <button type="button" class="senha-toggle" onclick="toggleSenha()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            
                            <!-- Botões -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                                </button>
                                <a href="cadastro.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Criar Conta
                                </a>
                            </div>
                            
                            <!-- Links Adicionais -->
                            <div class="text-center mt-4">
                                <p class="text-muted">
                                    <a href="#" class="text-decoration-none" style="color: var(--cor-primaria);">
                                        <i class="fas fa-key me-1"></i>Esqueci minha senha
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
        function toggleSenha() {
            const input = document.getElementById('senha');
            const icon = document.querySelector('.senha-toggle i');
            
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

        // Auto-preenchimento para teste
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tipo = urlParams.get('tipo');
            
            if (tipo === 'admin') {
                document.getElementById('email').value = 'admin@teste.com';
                document.getElementById('senha').value = 'admin123';
            } else if (tipo === 'usuario') {
                document.getElementById('email').value = 'usuario@teste.com';
                document.getElementById('senha').value = 'usuario123';
            }
        });
    </script>
</body>
</html> 