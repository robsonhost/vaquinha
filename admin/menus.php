<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
// Adicionar/editar menu
if (isset($_POST['salvar_menu'])) {
    $nome = trim($_POST['nome'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $id = $_POST['id'] ?? null;
    if ($id) {
        $stmt = $pdo->prepare('UPDATE menus SET nome=?, url=? WHERE id=?');
        $stmt->execute([$nome, $url, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO menus (nome, url) VALUES (?, ?)');
        $stmt->execute([$nome, $url]);
    }
}
// Excluir menu
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $pdo->prepare('DELETE FROM menus WHERE id=?')->execute([$id]);
}
$menus = $pdo->query('SELECT * FROM menus ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
// Textos institucionais
if (isset($_POST['salvar_textos'])) {
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $taxa = trim($_POST['taxa'] ?? '');
    $quem_somos = trim($_POST['quem_somos'] ?? '');
    $pdo->prepare('UPDATE textos SET valor=? WHERE chave="whatsapp"')->execute([$whatsapp]);
    $pdo->prepare('UPDATE textos SET valor=? WHERE chave="email"')->execute([$email]);
    $pdo->prepare('UPDATE textos SET valor=? WHERE chave="taxa"')->execute([$taxa]);
    $pdo->prepare('UPDATE textos SET valor=? WHERE chave="quem_somos"')->execute([$quem_somos]);
}
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}
?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>Menus & Textos do Site</h1></div></section>
        <section class="content">
            <div class="container-fluid">
                <h4>Menus do Site</h4>
                <form method="post" class="form-inline mb-3">
                    <input type="text" name="nome" class="form-control mr-2" placeholder="Nome do menu" required>
                    <input type="text" name="url" class="form-control mr-2" placeholder="URL (ex: index.html)" required>
                    <button type="submit" name="salvar_menu" class="btn btn-success">Adicionar</button>
                </form>
                <table class="table table-bordered table-hover">
                    <tr><th>Nome</th><th>URL</th><th>Ações</th></tr>
                    <?php foreach ($menus as $m): ?>
                    <tr>
                        <form method="post" class="form-inline">
                            <td><input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($m['nome']) ?>" required></td>
                            <td><input type="text" name="url" class="form-control" value="<?= htmlspecialchars($m['url']) ?>" required></td>
                            <td>
                                <button type="submit" name="salvar_menu" class="btn btn-primary btn-sm">Salvar</button>
                                <a href="?excluir=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Excluir menu?')">Excluir</a>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <hr>
                <h4>Textos Institucionais</h4>
                <form method="post">
                    <div class="form-group">
                        <label>WhatsApp:</label>
                        <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($textos['whatsapp'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>E-mail:</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($textos['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Taxa do site (%):</label>
                        <input type="text" name="taxa" class="form-control" value="<?= htmlspecialchars($textos['taxa'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Quem Somos:</label>
                        <textarea name="quem_somos" class="form-control"><?= htmlspecialchars($textos['quem_somos'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" name="salvar_textos" class="btn btn-primary">Salvar Textos</button>
                </form>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?> 