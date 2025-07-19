<?php
require '../db.php';
session_start();

// Verificar se é admin
if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

try {
    // Receber dados
    $input = json_decode(file_get_contents('php://input'), true);
    $ultimaVerificacao = $input['ultima_verificacao'] ?? date('Y-m-d H:i:s');
    
    // Buscar notificações novas
    $stmt = $pdo->prepare('SELECT * FROM notificacoes WHERE criado_em > ? AND lida = 0 ORDER BY criado_em DESC');
    $stmt->execute([$ultimaVerificacao]);
    $novas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar total de não lidas
    $totalNaoLidas = $pdo->query('SELECT COUNT(*) FROM notificacoes WHERE lida = 0')->fetchColumn();
    
    // Retornar resposta
    header('Content-Type: application/json');
    echo json_encode([
        'sucesso' => true,
        'novas' => $novas,
        'total_nao_lidas' => $totalNaoLidas,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage()]);
}
?> 