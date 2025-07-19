<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Carregar integrações do banco (tabela textos ou integracoes)
$chaves = [
    'mercado_pago_public_key',
    'mercado_pago_access_token',
    'whatsapp_name',
    'whatsapp_token',
    'whatsapp_api_url'
];
$integracoes = [];
foreach ($chaves as $chave) {
    $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
    $stmt->execute([$chave]);
    $integracoes[$chave] = $stmt->fetchColumn() ?: '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($chaves as $chave) {
            $valor = trim($_POST[$chave] ?? '');
            $stmt = $pdo->prepare('INSERT INTO textos (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?');
            $stmt->execute([$chave, $valor, $valor]);
        }
        $sucesso = 'Credenciais atualizadas com sucesso!';
        // Recarregar
        foreach ($chaves as $chave) {
            $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
            $stmt->execute([$chave]);
            $integracoes[$chave] = $stmt->fetchColumn() ?: '';
        }
    } catch (Exception $e) {
        $erro = 'Erro ao salvar: ' . $e->getMessage();
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
                        <h1><i class="fas fa-plug"></i> Integrações e APIs</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Integrações</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>
                <?php if ($sucesso): ?><div class="alert alert-success"><?= $sucesso ?></div><?php endif; ?>
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="card-title"><i class="fab fa-whatsapp"></i> WhatsApp API</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Nome (Name)</label>
                                        <input type="text" name="whatsapp_name" class="form-control" value="<?= htmlspecialchars($integracoes['whatsapp_name']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Token</label>
                                        <input type="text" name="whatsapp_token" class="form-control" value="<?= htmlspecialchars($integracoes['whatsapp_token']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>API URL</label>
                                        <input type="text" name="whatsapp_api_url" class="form-control" value="<?= htmlspecialchars($integracoes['whatsapp_api_url']) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h3 class="card-title"><i class="fab fa-cc-visa"></i> Mercado Pago</h3>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Public Key</label>
                                        <input type="text" name="mercado_pago_public_key" class="form-control" value="<?= htmlspecialchars($integracoes['mercado_pago_public_key']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Access Token</label>
                                        <input type="text" name="mercado_pago_access_token" class="form-control" value="<?= htmlspecialchars($integracoes['mercado_pago_access_token']) ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg mt-3">
                                <i class="fas fa-save"></i> Salvar Credenciais
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?>
</body>
</html> 