<?php
require 'admin/db.php';
require 'includes/whatsapp.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Verificar se o usuário está ativo
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ? AND status = "ativo"');
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $meta = floatval($_POST['meta'] ?? 0);
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $imagem = '';
    
    // Validações
    if (!$titulo) {
        $erro = 'O título é obrigatório!';
    } elseif (strlen($titulo) < 10) {
        $erro = 'O título deve ter pelo menos 10 caracteres!';
    } elseif (!$descricao) {
        $erro = 'A descrição é obrigatória!';
    } elseif (strlen($descricao) < 50) {
        $erro = 'A descrição deve ter pelo menos 50 caracteres!';
    } elseif ($meta <= 0) {
        $erro = 'A meta deve ser maior que zero!';
    } elseif ($meta < 50) {
        $erro = 'A meta mínima é R$ 50,00!';
    } elseif (!$categoria_id) {
        $erro = 'Selecione uma categoria!';
    } else {
        // Upload da imagem
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['imagem'];
            $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($ext, $extensoes_permitidas)) {
                $erro = 'Formato de imagem não permitido! Use: JPG, PNG, GIF ou WebP.';
            } elseif ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
                $erro = 'A imagem deve ter no máximo 5MB!';
            } else {
                // Criar diretório se não existir
                $upload_dir = 'uploads/campanhas/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $nome_arquivo = 'campanha_' . $usuario_id . '_' . uniqid() . '.' . $ext;
                $caminho_completo = $upload_dir . $nome_arquivo;
                
                if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                    $imagem = $caminho_completo;
                } else {
                    $erro = 'Erro ao fazer upload da imagem!';
                }
            }
        }
        
        if (!$erro) {
            try {
                $stmt = $pdo->prepare('
                    INSERT INTO campanhas (
                        titulo, descricao, meta, imagem, usuario_id, categoria_id, 
                        status, arrecadado, criado_em
                    ) VALUES (?, ?, ?, ?, ?, ?, "pendente", 0, NOW())
                ');
                
                $stmt->execute([$titulo, $descricao, $meta, $imagem, $usuario_id, $categoria_id]);
                $campanha_id = $pdo->lastInsertId();
                
                $sucesso = 'Campanha criada com sucesso! Aguarde a aprovação da administração.';
                
                // Notificação WhatsApp para o criador
                if ($usuario['telefone']) {
                    $mensagem = "🎉 Parabéns, {$usuario['nome']}!\n\n";
                    $mensagem .= "✅ Sua campanha '{$titulo}' foi criada com sucesso!\n\n";
                    $mensagem .= "📋 Próximos passos:\n";
                    $mensagem .= "• Aguarde a aprovação da administração\n";
                    $mensagem .= "• Prepare materiais para divulgação\n";
                    $mensagem .= "• Pense em estratégias para alcançar sua meta\n\n";
                    $mensagem .= "🚀 Dica: Campanhas com boa descrição e imagem atrativa são aprovadas mais rapidamente!\n\n";
                    $mensagem .= "💙 Estamos torcendo pelo seu sucesso!";
                    
                    enviar_whatsapp($usuario['telefone'], $mensagem);
                }
                
                // Limpar formulário
                $titulo = $descricao = $meta = $categoria_id = '';
                
            } catch (PDOException $e) {
                $erro = 'Erro ao criar campanha. Tente novamente!';
                error_log('Erro ao criar campanha: ' . $e->getMessage());
            }
        }
    }
}

// Buscar categorias ativas
$categorias = $pdo->query('SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);

// Carregar tema
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

// Carregar configurações
$textos = [];
foreach ($pdo->query('SELECT chave, valor FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}
$nome_site = $textos['nome_site'] ?? 'Vaquinha Online';

include '_header_usuario.php';
?>
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: var(--cor-terciaria);
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-brand, .navbar-nav .nav-link {
            color: white !important;
        }
        
        .main-content {
            padding: 2rem 0;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .form-header {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .form-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .form-body {
            padding: 3rem 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--cor-primaria);
            box-shadow: 0 0 0 0.2rem rgba(120, 47, 155, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            border: none;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .btn-outline-secondary {
            border: 2px solid #6c757d;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-weight: 600;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .required {
            color: #dc3545;
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            margin-top: 1rem;
            display: none;
        }
        
        .tips-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .tips-card h5 {
            color: var(--cor-primaria);
            margin-bottom: 1rem;
        }
        
        .tips-card ul {
            margin-bottom: 0;
        }
        
        .tips-card li {
            margin-bottom: 0.5rem;
        }
        
        .character-count {
            font-size: 0.875rem;
            color: #6c757d;
            float: right;
        }
        
        .character-count.warning {
            color: #ffc107;
        }
        
        .character-count.danger {
            color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem 0;
            }
            
            .form-header {
                padding: 1.5rem 1rem;
            }
            
            .form-body {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heart me-2"></i><?= htmlspecialchars($nome_site) ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="area_usuario.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="minhas_campanhas.php">
                            <i class="fas fa-bullhorn me-1"></i>Minhas Campanhas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="nova_campanha_usuario.php">
                            <i class="fas fa-plus me-1"></i>Nova Campanha
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <a href="area_usuario.php" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-card">
                        <!-- Header -->
                        <div class="form-header">
                            <h1><i class="fas fa-plus me-2"></i>Criar Nova Campanha</h1>
                            <p>Conte sua história e mobilize pessoas para sua causa</p>
                        </div>
                        
                        <!-- Body -->
                        <div class="form-body">
                            <?php if ($erro): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <?= htmlspecialchars($erro) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($sucesso): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?= htmlspecialchars($sucesso) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Dicas -->
                            <div class="tips-card">
                                <h5><i class="fas fa-lightbulb me-2"></i>Dicas para uma campanha de sucesso</h5>
                                <ul>
                                    <li><strong>Título atrativo:</strong> Use palavras que despertam emoção e curiosidade</li>
                                    <li><strong>História completa:</strong> Conte o motivo, como o dinheiro será usado e o impacto esperado</li>
                                    <li><strong>Meta realista:</strong> Defina um valor que seja necessário e alcançável</li>
                                    <li><strong>Imagem impactante:</strong> Use uma foto que represente bem sua causa</li>
                                    <li><strong>Divulgação:</strong> Compartilhe em redes sociais e com amigos</li>
                                </ul>
                            </div>

                            <!-- Formulário -->
                            <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="titulo" class="form-label">
                                                Título da Campanha <span class="required">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="titulo" 
                                                   name="titulo" 
                                                   value="<?= htmlspecialchars($titulo ?? '') ?>"
                                                   placeholder="Ex: Ajude João a realizar sua cirurgia"
                                                   required
                                                   maxlength="100">
                                            <div class="form-text">
                                                <span class="character-count" id="titulo-count">0/100</span>
                                                Mínimo 10 caracteres
                                            </div>
                                            <div class="invalid-feedback">
                                                Por favor, informe um título com pelo menos 10 caracteres.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="categoria_id" class="form-label">
                                                Categoria <span class="required">*</span>
                                            </label>
                                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                                <option value="">Selecione uma categoria</option>
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?= $categoria['id'] ?>" 
                                                            <?= (isset($categoria_id) && $categoria_id == $categoria['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($categoria['nome']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">
                                                Por favor, selecione uma categoria.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="descricao" class="form-label">
                                        Descrição da Campanha <span class="required">*</span>
                                    </label>
                                    <textarea class="form-control" 
                                              id="descricao" 
                                              name="descricao" 
                                              rows="8" 
                                              placeholder="Conte sua história... Explique o motivo da campanha, como o dinheiro será usado e qual o impacto esperado."
                                              required
                                              maxlength="2000"><?= htmlspecialchars($descricao ?? '') ?></textarea>
                                    <div class="form-text">
                                        <span class="character-count" id="descricao-count">0/2000</span>
                                        Mínimo 50 caracteres. Seja detalhado e transparente.
                                    </div>
                                    <div class="invalid-feedback">
                                        Por favor, informe uma descrição com pelo menos 50 caracteres.
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="meta" class="form-label">
                                                Meta de Arrecadação <span class="required">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="meta" 
                                                       name="meta" 
                                                       value="<?= htmlspecialchars($meta ?? '') ?>"
                                                       placeholder="1000.00"
                                                       min="50"
                                                       step="0.01"
                                                       required>
                                            </div>
                                            <div class="form-text">
                                                Valor mínimo: R$ 50,00
                                            </div>
                                            <div class="invalid-feedback">
                                                Por favor, informe uma meta válida (mínimo R$ 50,00).
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="imagem" class="form-label">
                                                Imagem da Campanha
                                            </label>
                                            <input type="file" 
                                                   class="form-control" 
                                                   id="imagem" 
                                                   name="imagem" 
                                                   accept="image/*">
                                            <div class="form-text">
                                                Formatos: JPG, PNG, GIF, WebP. Máximo: 5MB
                                            </div>
                                            <img id="image-preview" class="image-preview" alt="Preview">
                                        </div>
                                    </div>
                                </div>

                                <!-- Botões -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <a href="area_usuario.php" class="btn btn-outline-secondary me-md-2">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Criar Campanha
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Bootstrap validation
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

        // Character counting
        function updateCharacterCount(elementId, counterId, maxLength) {
            const element = document.getElementById(elementId);
            const counter = document.getElementById(counterId);
            
            function updateCount() {
                const currentLength = element.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;
                
                counter.classList.remove('warning', 'danger');
                if (currentLength > maxLength * 0.9) {
                    counter.classList.add('danger');
                } else if (currentLength > maxLength * 0.7) {
                    counter.classList.add('warning');
                }
            }
            
            element.addEventListener('input', updateCount);
            updateCount(); // Initial count
        }

        updateCharacterCount('titulo', 'titulo-count', 100);
        updateCharacterCount('descricao', 'descricao-count', 2000);

        // Image preview
        document.getElementById('imagem').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('image-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        // Format currency input
        document.getElementById('meta').addEventListener('input', function(e) {
            let value = e.target.value;
            
            // Remove non-numeric characters except dots
            value = value.replace(/[^\d.]/g, '');
            
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            e.target.value = value;
        });

        // Auto-resize textarea
        document.getElementById('descricao').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>

<?php include '_footer_usuario.php'; ?> 