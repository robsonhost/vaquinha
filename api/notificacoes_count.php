<?php
require '../admin/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT COUNT(*) FROM notificacoes 
        WHERE (usuario_id = ? OR destinatario IN ("todos", "usuarios"))
          AND lida = 0
    ');
    $stmt->execute([$_SESSION['usuario_id']]);
    $count = $stmt->fetchColumn();
    
    echo json_encode(['count' => (int)$count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}
?>