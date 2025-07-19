<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('DELETE FROM campanhas WHERE id = ?');
$stmt->execute([$id]);
header('Location: campanhas.php');
exit; 