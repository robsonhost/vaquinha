<?php
require 'admin/db.php';
require 'includes/mercado_pago.php';

header('Content-Type: application/json');

$doacao_id = intval($_GET['doacao_id'] ?? 0);
if (!$doacao_id) {
    echo json_encode(['error' => 'ID da doação não fornecido']);
    exit;
}

try {
    // Buscar doação
    $stmt = $pdo->prepare('SELECT payment_id FROM doacoes WHERE id = ?');
    $stmt->execute([$doacao_id]);
    $doacao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doacao || !$doacao['payment_id']) {
        echo json_encode(['error' => 'Doação não encontrada']);
        exit;
    }
    
    // Verificar status no Mercado Pago
    $status_info = verificar_status_pagamento($doacao['payment_id']);
    
    echo json_encode([
        'status' => $status_info['status'],
        'external_reference' => $status_info['external_reference']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 