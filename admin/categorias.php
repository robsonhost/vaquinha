<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Processar ações via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'adicionar':
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $imagem = '';
            
            if (empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
                exit;
            }
            
            // Upload de imagem
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($ext, $allowed)) {
                    echo json_encode(['success' => false, 'message' => 'Formato de imagem não suportado']);
                    exit;
                }
                
                $nome_arquivo = 'cat_' . uniqid() . '.' . $ext;
                $upload_path = '../images/' . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                    $imagem = 'images/' . $nome_arquivo;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem']);
                    exit;
                }
            }
            
            try {
                $stmt = $pdo->prepare('INSERT INTO categorias (nome, descricao, imagem) VALUES (?, ?, ?)');
                $stmt->execute([$nome, $descricao, $imagem]);
                echo json_encode(['success' => true, 'message' => 'Categoria adicionada com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao adicionar categoria']);
            }
            break;
            
        case 'editar':
            $id = intval($_POST['id']);
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $imagem_atual = $_POST['imagem_atual'] ?? '';
            $imagem = $imagem_atual;
            
            if (empty($nome)) {
                echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
                exit;
            }
            
            // Upload de nova imagem
            if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($ext, $allowed)) {
                    echo json_encode(['success' => false, 'message' => 'Formato de imagem não suportado']);
                    exit;
                }
                
                $nome_arquivo = 'cat_' . uniqid() . '.' . $ext;
                $upload_path = '../images/' . $nome_arquivo;
                
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
                    $imagem = 'images/' . $nome_arquivo;
                    
                    // Excluir imagem antiga se existir
                    if ($imagem_atual && file_exists('../' . $imagem_atual)) {
                        unlink('../' . $imagem_atual);
                    }
                }
            }
            
            try {
                $stmt = $pdo->prepare('UPDATE categorias SET nome = ?, descricao = ?, imagem = ? WHERE id = ?');
                $stmt->execute([$nome, $descricao, $imagem, $id]);
                echo json_encode(['success' => true, 'message' => 'Categoria atualizada com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar categoria']);
            }
            break;
            
        case 'excluir':
            $id = intval($_POST['id']);
            
            try {
                // Verificar se há campanhas usando esta categoria
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM campanhas WHERE categoria_id = ?');
                $stmt->execute([$id]);
                $count = $stmt->fetchColumn();
                
                if ($count > 0) {
                    echo json_encode(['success' => false, 'message' => 'Não é possível excluir: existem campanhas usando esta categoria']);
                    exit;
                }
                
                // Pegar imagem para excluir
                $stmt = $pdo->prepare('SELECT imagem FROM categorias WHERE id = ?');
                $stmt->execute([$id]);
                $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Excluir categoria
                $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = ?');
                $stmt->execute([$id]);
                
                // Excluir imagem se existir
                if ($categoria['imagem'] && file_exists('../' . $categoria['imagem'])) {
                    unlink('../' . $categoria['imagem']);
                }
                
                echo json_encode(['success' => true, 'message' => 'Categoria excluída com sucesso']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir categoria']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida']);
    }
    exit;
}

// Processar ação de aprovar categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'aprovar') {
    $id = intval($_POST['id']);
    try {
        $stmt = $pdo->prepare('UPDATE categorias SET status = "aprovada" WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao aprovar categoria']);
    }
    exit;
}

// Buscar categorias
$categorias = $pdo->query('SELECT c.*, COUNT(cp.id) as total_campanhas FROM categorias c LEFT JOIN campanhas cp ON c.id = cp.categoria_id WHERE c.status = "aprovada" GROUP BY c.id ORDER BY c.nome')->fetchAll(PDO::FETCH_ASSOC);
$categoriasPendentes = $pdo->query('SELECT * FROM categorias WHERE status = "pendente" ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1><i class="fas fa-tags"></i> Gerenciar Categorias</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Categorias</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- Card Principal -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Lista de Categorias
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalNovaCategoria">
                                <i class="fas fa-plus"></i> Nova Categoria
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tabelaCategorias">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="80">Imagem</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th width="120">Campanhas</th>
                                        <th width="150">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categorias)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                            Nenhuma categoria cadastrada
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($categorias as $cat): ?>
                                        <tr data-id="<?= $cat['id'] ?>">
                                            <td class="text-center">
                                                <?php if ($cat['imagem']): ?>
                                                    <img src="../<?= htmlspecialchars($cat['imagem']) ?>" 
                                                         alt="<?= htmlspecialchars($cat['nome']) ?>" 
                                                         class="img-thumbnail" 
                                                         style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px; border-radius: 4px;">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($cat['nome']) ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($cat['descricao'] ?? 'Sem descrição') ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info"><?= $cat['total_campanhas'] ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        onclick="editarCategoria(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['nome']) ?>', '<?= htmlspecialchars($cat['descricao'] ?? '') ?>', '<?= htmlspecialchars($cat['imagem'] ?? '') ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="excluirCategoria(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['nome']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Categorias Pendentes -->
                <div class="card mt-4">
                    <div class="card-header bg-warning">
                        <h3 class="card-title"><i class="fas fa-hourglass-half"></i> Categorias Pendentes de Aprovação</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="80">Imagem</th>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th width="150">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($categoriasPendentes)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Nenhuma categoria pendente</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($categoriasPendentes as $cat): ?>
                                        <tr data-id="<?= $cat['id'] ?>">
                                            <td class="text-center">
                                                <?php if ($cat['imagem']): ?>
                                                    <img src="../<?= htmlspecialchars($cat['imagem']) ?>" alt="<?= htmlspecialchars($cat['nome']) ?>" class="img-thumbnail" style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 4px;"><i class="fas fa-image"></i></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($cat['nome']) ?></strong></td>
                                            <td><?= htmlspecialchars($cat['descricao'] ?? 'Sem descrição') ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success" onclick="aprovarCategoria(<?= $cat['id'] ?>)"><i class="fas fa-check"></i> Aprovar</button>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="excluirCategoria(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['nome']) ?>')"><i class="fas fa-trash"></i> Excluir</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modal Nova Categoria -->
<div class="modal fade" id="modalNovaCategoria" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i> Nova Categoria
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formNovaCategoria" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome">Nome da Categoria *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descrição opcional da categoria"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="imagem">Imagem da Categoria</label>
                        <input type="file" class="form-control-file" id="imagem" name="imagem" accept="image/*">
                        <small class="form-text text-muted">Formatos aceitos: JPG, PNG, GIF, WebP. Tamanho máximo: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Categoria -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Categoria
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEditarCategoria" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">
                    <input type="hidden" id="edit_imagem_atual" name="imagem_atual">
                    
                    <div class="form-group">
                        <label for="edit_nome">Nome da Categoria *</label>
                        <input type="text" class="form-control" id="edit_nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_descricao">Descrição</label>
                        <textarea class="form-control" id="edit_descricao" name="descricao" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_imagem">Nova Imagem (opcional)</label>
                        <input type="file" class="form-control-file" id="edit_imagem" name="imagem" accept="image/*">
                        <small class="form-text text-muted">Deixe em branco para manter a imagem atual</small>
                    </div>
                    <div id="preview_imagem_atual" class="form-group" style="display: none;">
                        <label>Imagem Atual</label>
                        <div>
                            <img id="img_atual" src="" alt="Imagem atual" class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Atualizar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '_footer.php'; ?>

<script>
// Nova Categoria
$('#formNovaCategoria').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'adicionar');
    
    $.ajax({
        url: 'categorias.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#formNovaCategoria button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#modalNovaCategoria').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Erro ao processar requisição');
        },
        complete: function() {
            $('#formNovaCategoria button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Salvar Categoria');
        }
    });
});

// Editar Categoria
function editarCategoria(id, nome, descricao, imagem) {
    $('#edit_id').val(id);
    $('#edit_nome').val(nome);
    $('#edit_descricao').val(descricao);
    $('#edit_imagem_atual').val(imagem);
    
    if (imagem) {
        $('#img_atual').attr('src', '../' + imagem);
        $('#preview_imagem_atual').show();
    } else {
        $('#preview_imagem_atual').hide();
    }
    
    $('#modalEditarCategoria').modal('show');
}

$('#formEditarCategoria').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'editar');
    
    $.ajax({
        url: 'categorias.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $('#formEditarCategoria button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Atualizando...');
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#modalEditarCategoria').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Erro ao processar requisição');
        },
        complete: function() {
            $('#formEditarCategoria button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Atualizar Categoria');
        }
    });
});

// Excluir Categoria
function excluirCategoria(id, nome) {
    if (confirm(`Tem certeza que deseja excluir a categoria "${nome}"?`)) {
        $.ajax({
            url: 'categorias.php',
            type: 'POST',
            data: {
                action: 'excluir',
                id: id
            },
            beforeSend: function() {
                // Mostrar loading
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Erro ao processar requisição');
            }
        });
    }
}

// Limpar formulários ao fechar modais
$('#modalNovaCategoria').on('hidden.bs.modal', function() {
    $('#formNovaCategoria')[0].reset();
});

$('#modalEditarCategoria').on('hidden.bs.modal', function() {
    $('#formEditarCategoria')[0].reset();
    $('#preview_imagem_atual').hide();
});

function aprovarCategoria(id) {
    if (confirm('Aprovar esta categoria?')) {
        fetch('categorias.php', {
            method: 'POST',
            body: new URLSearchParams({action: 'aprovar', id: id})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao aprovar categoria.');
            }
        });
    }
}
</script> 