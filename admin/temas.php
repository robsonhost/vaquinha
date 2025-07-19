<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Ações
if (isset($_GET['ativar'])) {
    $id = intval($_GET['ativar']);
    $pdo->prepare('UPDATE temas SET ativo = 0')->execute(); // Desativa todos
    $pdo->prepare('UPDATE temas SET ativo = 1 WHERE id = ?')->execute([$id]);
    $sucesso = 'Tema ativado com sucesso!';
}

if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $tema = $pdo->prepare('SELECT * FROM temas WHERE id = ?')->execute([$id]);
    if ($tema && $tema['ativo'] == 0) {
        $pdo->prepare('DELETE FROM temas WHERE id = ?')->execute([$id]);
        $sucesso = 'Tema excluído com sucesso!';
    } else {
        $erro = 'Não é possível excluir o tema ativo!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cor_primaria = trim($_POST['cor_primaria'] ?? '');
    $cor_secundaria = trim($_POST['cor_secundaria'] ?? '');
    $cor_terciaria = trim($_POST['cor_terciaria'] ?? '');
    
    if (!$nome || !$cor_primaria || !$cor_secundaria || !$cor_terciaria) {
        $erro = 'Preencha todos os campos!';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO temas (nome, cor_primaria, cor_secundaria, cor_terciaria) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nome, $cor_primaria, $cor_secundaria, $cor_terciaria]);
            $sucesso = 'Tema criado com sucesso!';
        } catch (Exception $e) {
            $erro = 'Erro ao criar tema: ' . $e->getMessage();
        }
    }
}

// Carregar temas
$temas = $pdo->query('SELECT * FROM temas ORDER BY ativo DESC, nome')->fetchAll(PDO::FETCH_ASSOC);
$temaAtivo = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
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
                        <h1>Gerenciar Temas</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Temas</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                
                <!-- Tema Ativo -->
                <?php if ($temaAtivo): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-gradient-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-star"></i> Tema Ativo: <?= htmlspecialchars($temaAtivo['nome']) ?>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="color-preview" style="background-color: <?= $temaAtivo['cor_primaria'] ?>; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px;"></div>
                                            <strong>Cor Primária</strong><br>
                                            <code><?= $temaAtivo['cor_primaria'] ?></code>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="color-preview" style="background-color: <?= $temaAtivo['cor_secundaria'] ?>; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px;"></div>
                                            <strong>Cor Secundária</strong><br>
                                            <code><?= $temaAtivo['cor_secundaria'] ?></code>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="color-preview" style="background-color: <?= $temaAtivo['cor_terciaria'] ?>; width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 10px;"></div>
                                            <strong>Cor Terciária</strong><br>
                                            <code><?= $temaAtivo['cor_terciaria'] ?></code>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4>Preview</h4>
                                            <div class="preview-buttons">
                                                <button class="btn" style="background-color: <?= $temaAtivo['cor_primaria'] ?>; color: white; margin: 2px;">Botão Primário</button>
                                                <button class="btn" style="background-color: <?= $temaAtivo['cor_secundaria'] ?>; color: white; margin: 2px;">Botão Secundário</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Lista de Temas -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-palette"></i> Todos os Temas
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($temas as $tema): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card <?= $tema['ativo'] ? 'border-primary' : '' ?>">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <?= htmlspecialchars($tema['nome']) ?>
                                                    <?php if ($tema['ativo']): ?>
                                                        <span class="badge badge-primary float-right">Ativo</span>
                                                    <?php endif; ?>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row text-center">
                                                    <div class="col-4">
                                                        <div class="color-preview" style="background-color: <?= $tema['cor_primaria'] ?>; width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 5px;"></div>
                                                        <small>Primária</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="color-preview" style="background-color: <?= $tema['cor_secundaria'] ?>; width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 5px;"></div>
                                                        <small>Secundária</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="color-preview" style="background-color: <?= $tema['cor_terciaria'] ?>; width: 40px; height: 40px; border-radius: 50%; margin: 0 auto 5px;"></div>
                                                        <small>Terciária</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <?php if (!$tema['ativo']): ?>
                                                        <a href="?ativar=<?= $tema['id'] ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i> Ativar
                                                        </a>
                                                        <a href="?excluir=<?= $tema['id'] ?>" class="btn btn-sm btn-danger float-right" onclick="return confirm('Excluir este tema?')">
                                                            <i class="fas fa-trash"></i> Excluir
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge badge-success">Tema Ativo</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Criar Novo Tema -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plus"></i> Criar Novo Tema
                                </h3>
                            </div>
                            <div class="card-body">
                                <form method="post" id="formTema">
                                    <div class="form-group">
                                        <label>Nome do Tema</label>
                                        <input type="text" name="nome" class="form-control" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Cor Primária</label>
                                        <div class="input-group">
                                            <input type="color" name="cor_primaria" class="form-control" value="#782F9B" required>
                                            <input type="text" class="form-control" id="cor_primaria_text" value="#782F9B" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Cor Secundária</label>
                                        <div class="input-group">
                                            <input type="color" name="cor_secundaria" class="form-control" value="#65A300" required>
                                            <input type="text" class="form-control" id="cor_secundaria_text" value="#65A300" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Cor Terciária</label>
                                        <div class="input-group">
                                            <input type="color" name="cor_terciaria" class="form-control" value="#F7F7F7" required>
                                            <input type="text" class="form-control" id="cor_terciaria_text" value="#F7F7F7" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Preview do Tema</label>
                                        <div class="preview-container p-3 border rounded">
                                            <div class="row">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-block mb-2" id="preview_btn_primario">Botão Primário</button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-block mb-2" id="preview_btn_secundario">Botão Secundário</button>
                                                </div>
                                            </div>
                                            <div class="progress mb-2">
                                                <div class="progress-bar" id="preview_progress" style="width: 75%"></div>
                                            </div>
                                            <div class="alert" id="preview_alert">
                                                <strong>Exemplo de alerta</strong> com as cores do tema.
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i> Criar Tema
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Temas Prontos -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-magic"></i> Temas Prontos
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <button type="button" class="list-group-item list-group-item-action" onclick="aplicarTemaPronto('padrao')">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Padrão</h6>
                                            <small>Roxo/Verde</small>
                                        </div>
                                        <div class="d-flex">
                                            <div style="background-color: #782F9B; width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></div>
                                            <div style="background-color: #65A300; width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></div>
                                            <div style="background-color: #F7F7F7; width: 20px; height: 20px; border-radius: 50%;"></div>
                                        </div>
                                    </button>
                                    
                                    <button type="button" class="list-group-item list-group-item-action" onclick="aplicarTemaPronto('azul')">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Azul</h6>
                                            <small>Azul/Roxo</small>
                                        </div>
                                        <div class="d-flex">
                                            <div style="background-color: #007bff; width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></div>
                                            <div style="background-color: #6610f2; width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></div>
                                            <div style="background-color: #f8f9fa; width: 20px; height: 20px; border-radius: 50%;"></div>
                                        </div>
                                    </button>
                                    
                                    <button type="button" class="list-group-item list-group-item-action" onclick="aplicarTemaPronto('verde')">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Verde</h6>
                                            <small>Verde/Escuro</small>
                                        </div>
                                        <div class="d-flex">
                                            <div style="background-color: #28a745; width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></div>
                                            <div style="background-color: #218838; width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></div>
                                            <div style="background-color: #e9f7ef; width: 20px; height: 20px; border-radius: 50%;"></div>
                                        </div>
                                    </button>
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
// Sincronizar inputs de cor
document.querySelectorAll('input[type="color"]').forEach(function(input) {
    input.addEventListener('input', function() {
        const textInput = document.getElementById(this.name + '_text');
        if (textInput) {
            textInput.value = this.value.toUpperCase();
        }
        atualizarPreview();
    });
});

// Atualizar preview
function atualizarPreview() {
    const corPrimaria = document.querySelector('input[name="cor_primaria"]').value;
    const corSecundaria = document.querySelector('input[name="cor_secundaria"]').value;
    const corTerciaria = document.querySelector('input[name="cor_terciaria"]').value;
    
    document.getElementById('preview_btn_primario').style.backgroundColor = corPrimaria;
    document.getElementById('preview_btn_primario').style.color = getContrastColor(corPrimaria);
    
    document.getElementById('preview_btn_secundario').style.backgroundColor = corSecundaria;
    document.getElementById('preview_btn_secundario').style.color = getContrastColor(corSecundaria);
    
    document.getElementById('preview_progress').style.backgroundColor = corPrimaria;
    document.getElementById('preview_alert').style.backgroundColor = corTerciaria;
    document.getElementById('preview_alert').style.borderColor = corPrimaria;
    document.getElementById('preview_alert').style.color = getContrastColor(corTerciaria);
}

// Função para determinar cor de contraste
function getContrastColor(hexcolor) {
    const r = parseInt(hexcolor.substr(1,2), 16);
    const g = parseInt(hexcolor.substr(3,2), 16);
    const b = parseInt(hexcolor.substr(5,2), 16);
    const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return (yiq >= 128) ? '#000000' : '#ffffff';
}

// Aplicar tema pronto
function aplicarTemaPronto(tipo) {
    const temas = {
        'padrao': { primaria: '#782F9B', secundaria: '#65A300', terciaria: '#F7F7F7' },
        'azul': { primaria: '#007bff', secundaria: '#6610f2', terciaria: '#f8f9fa' },
        'verde': { primaria: '#28a745', secundaria: '#218838', terciaria: '#e9f7ef' }
    };
    
    const tema = temas[tipo];
    if (tema) {
        document.querySelector('input[name="cor_primaria"]').value = tema.primaria;
        document.querySelector('input[name="cor_secundaria"]').value = tema.secundaria;
        document.querySelector('input[name="cor_terciaria"]').value = tema.terciaria;
        
        document.getElementById('cor_primaria_text').value = tema.primaria.toUpperCase();
        document.getElementById('cor_secundaria_text').value = tema.secundaria.toUpperCase();
        document.getElementById('cor_terciaria_text').value = tema.terciaria.toUpperCase();
        
        atualizarPreview();
    }
}

// Inicializar preview
document.addEventListener('DOMContentLoaded', function() {
    atualizarPreview();
});
</script>
</body>
</html> 