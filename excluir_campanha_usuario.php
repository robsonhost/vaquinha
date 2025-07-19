<?php
require 'admin/db.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('DELETE FROM campanhas WHERE id = ? AND usuario_id = ?');
$stmt->execute([$id, $_SESSION['usuario_id']]);
header('Location: area_usuario.php');
exit; 