<?php
session_start();
require '../admin/db.php';

// Verificar se é admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Processar formulário
if ($_POST) {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'adicionar':
                $stmt = $pdo->prepare('INSERT INTO redes_sociais (nome, url, icone, ordem, ativo) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['url'],
                    $_POST['icone'],
                    $_POST['ordem'] ?: 0,
                    isset($_POST['ativo']) ? 1 : 0
                ]);
                $mensagem = 'Rede social adicionada com sucesso!';
                break;
                
            case 'editar':
                $stmt = $pdo->prepare('UPDATE redes_sociais SET nome = ?, url = ?, icone = ?, ordem = ?, ativo = ? WHERE id = ?');
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['url'],
                    $_POST['icone'],
                    $_POST['ordem'] ?: 0,
                    isset($_POST['ativo']) ? 1 : 0,
                    $_POST['id']
                ]);
                $mensagem = 'Rede social atualizada com sucesso!';
                break;
                
            case 'excluir':
                $stmt = $pdo->prepare('DELETE FROM redes_sociais WHERE id = ?');
                $stmt->execute([$_POST['id']]);
                $mensagem = 'Rede social excluída com sucesso!';
                break;
        }
    }
}

// Carregar redes sociais
$redesSociais = $pdo->query('SELECT * FROM redes_sociais ORDER BY ordem, nome')->fetchAll(PDO::FETCH_ASSOC);

// Rede social para edição
$redeEdicao = null;
if (isset($_GET['editar'])) {
    $redeEdicao = $pdo->query('SELECT * FROM redes_sociais WHERE id = ' . (int)$_GET['editar'])->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Redes Sociais - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-radius: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .rede-social-item {
            transition: transform 0.3s ease;
        }
        .rede-social-item:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">Admin Panel</h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a class="nav-link text-white" href="campanhas.php">
                            <i class="fas fa-hand-holding-heart me-2"></i>Campanhas
                        </a>
                        <a class="nav-link text-white" href="usuarios.php">
                            <i class="fas fa-users me-2"></i>Usuários
                        </a>
                        <a class="nav-link text-white" href="doacoes.php">
                            <i class="fas fa-heart me-2"></i>Doações
                        </a>
                        <a class="nav-link text-white active" href="redes_sociais.php">
                            <i class="fas fa-share-alt me-2"></i>Redes Sociais
                        </a>
                        <a class="nav-link text-white" href="configuracoes.php">
                            <i class="fas fa-cog me-2"></i>Configurações
                        </a>
                        <a class="nav-link text-white" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Sair
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-share-alt text-primary"></i> Gerenciar Redes Sociais</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRedeSocial">
                        <i class="fas fa-plus"></i> Nova Rede Social
                    </button>
                </div>

                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $mensagem ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Lista de Redes Sociais -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Redes Sociais Configuradas</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($redesSociais)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-share-alt fa-3x text-muted mb-3"></i>
                                <h5>Nenhuma rede social configurada</h5>
                                <p class="text-muted">Adicione suas redes sociais para aparecerem no rodapé do site.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ícone</th>
                                            <th>Nome</th>
                                            <th>URL</th>
                                            <th>Ordem</th>
                                            <th>Status</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($redesSociais as $rede): ?>
                                            <tr class="rede-social-item">
                                                <td>
                                                    <i class="<?= htmlspecialchars($rede['icone']) ?> fa-2x" style="color: var(--cor-primaria)"></i>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($rede['nome']) ?></strong>
                                                </td>
                                                <td>
                                                    <a href="<?= htmlspecialchars($rede['url']) ?>" target="_blank" class="text-decoration-none">
                                                        <?= htmlspecialchars($rede['url']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= $rede['ordem'] ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($rede['ativo']): ?>
                                                        <span class="badge bg-success">Ativo</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Inativo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="?editar=<?= $rede['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirRede(<?= $rede['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Redes Sociais Pré-configuradas -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Redes Sociais Disponíveis</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-facebook fa-3x text-primary mb-2"></i>
                                    <h6>Facebook</h6>
                                    <small class="text-muted">facebook.com/suapagina</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-instagram fa-3x text-danger mb-2"></i>
                                    <h6>Instagram</h6>
                                    <small class="text-muted">instagram.com/seuperfil</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-youtube fa-3x text-danger mb-2"></i>
                                    <h6>YouTube</h6>
                                    <small class="text-muted">youtube.com/seucanal</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-tiktok fa-3x text-dark mb-2"></i>
                                    <h6>TikTok</h6>
                                    <small class="text-muted">tiktok.com/@seuperfil</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-twitter fa-3x text-info mb-2"></i>
                                    <h6>Twitter</h6>
                                    <small class="text-muted">twitter.com/seuperfil</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-linkedin fa-3x text-primary mb-2"></i>
                                    <h6>LinkedIn</h6>
                                    <small class="text-muted">linkedin.com/in/seuperfil</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-whatsapp fa-3x text-success mb-2"></i>
                                    <h6>WhatsApp</h6>
                                    <small class="text-muted">wa.me/5511999999999</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center p-3">
                                    <i class="fab fa-telegram fa-3x text-info mb-2"></i>
                                    <h6>Telegram</h6>
                                    <small class="text-muted">t.me/seucanal</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rede Social -->
    <div class="modal fade" id="modalRedeSocial" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?= $redeEdicao ? 'Editar' : 'Nova' ?> Rede Social
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="<?= $redeEdicao ? 'editar' : 'adicionar' ?>">
                        <?php if ($redeEdicao): ?>
                            <input type="hidden" name="id" value="<?= $redeEdicao['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nome da Rede Social</label>
                            <input type="text" name="nome" class="form-control" value="<?= $redeEdicao ? htmlspecialchars($redeEdicao['nome']) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="url" name="url" class="form-control" value="<?= $redeEdicao ? htmlspecialchars($redeEdicao['url']) : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ícone (Font Awesome)</label>
                            <input type="text" name="icone" class="form-control" value="<?= $redeEdicao ? htmlspecialchars($redeEdicao['icone']) : '' ?>" placeholder="fab fa-facebook" required>
                            <small class="text-muted">Ex: fab fa-facebook, fab fa-instagram, fab fa-youtube</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ordem</label>
                            <input type="number" name="ordem" class="form-control" value="<?= $redeEdicao ? $redeEdicao['ordem'] : '0' ?>" min="0">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="ativo" class="form-check-input" id="ativo" <?= (!$redeEdicao || $redeEdicao['ativo']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="ativo">Ativo</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <?= $redeEdicao ? 'Atualizar' : 'Adicionar' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function excluirRede(id) {
            if (confirm('Tem certeza que deseja excluir esta rede social?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Abrir modal se estiver editando
        <?php if ($redeEdicao): ?>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('modalRedeSocial')).show();
        });
        <?php endif; ?>
    </script>
</body>
</html> 