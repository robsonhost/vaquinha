<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM campanhas WHERE id = ?');
$stmt->execute([$id]);
$campanha = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$campanha) {
    echo 'Campanha não encontrada!';
    exit;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $meta = $_POST['meta'] ?? 0;
    $imagem = $_POST['imagem'] ?? '';
    $stmt = $pdo->prepare('UPDATE campanhas SET titulo=?, descricao=?, meta=?, imagem=? WHERE id=?');
    $stmt->execute([$titulo, $descricao, $meta, $imagem, $id]);
    $msg = 'Campanha atualizada!';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Campanha</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Editar Campanha</h2>
    <a href="campanhas.php">Voltar</a>
    <?php if ($msg): ?><p style="color:green;"> <?= $msg ?> </p><?php endif; ?>
    <form method="post">
        <label>Título: <input type="text" name="titulo" value="<?= htmlspecialchars($campanha['titulo']) ?>" required></label><br>
        <label>Descrição:<br><textarea name="descricao" required><?= htmlspecialchars($campanha['descricao']) ?></textarea></label><br>
        <label>Meta (R$): <input type="number" name="meta" step="0.01" value="<?= $campanha['meta'] ?>" required></label><br>
        <label>Imagem (URL): <input type="text" name="imagem" value="<?= htmlspecialchars($campanha['imagem']) ?>"></label><br>
        <button type="submit">Salvar</button>
    </form>
</body>
</html> 