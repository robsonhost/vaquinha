<?php
session_start();
require '../admin/db.php';

// Verificar se é admin
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Pegar dados do JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$cor_primaria = $input['cor_primaria'] ?? '';
$cor_secundaria = $input['cor_secundaria'] ?? '';
$cor_terciaria = $input['cor_terciaria'] ?? '';

// Validar cores
if (!preg_match('/^#[0-9A-F]{6}$/i', $cor_primaria) || 
    !preg_match('/^#[0-9A-F]{6}$/i', $cor_secundaria) || 
    !preg_match('/^#[0-9A-F]{6}$/i', $cor_terciaria)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cores inválidas']);
    exit;
}

try {
    // Desativar todos os temas
    $pdo->exec('UPDATE temas SET ativo = 0');
    
    // Verificar se já existe um tema padrão
    $temaExistente = $pdo->query('SELECT id FROM temas WHERE nome = "Tema Padrão"')->fetch(PDO::FETCH_ASSOC);
    
    if ($temaExistente) {
        // Atualizar tema existente
        $stmt = $pdo->prepare('UPDATE temas SET cor_primaria = ?, cor_secundaria = ?, cor_terciaria = ?, ativo = 1 WHERE id = ?');
        $stmt->execute([$cor_primaria, $cor_secundaria, $cor_terciaria, $temaExistente['id']]);
    } else {
        // Criar novo tema
        $stmt = $pdo->prepare('INSERT INTO temas (nome, cor_primaria, cor_secundaria, cor_terciaria, ativo) VALUES (?, ?, ?, ?, 1)');
        $stmt->execute(['Tema Padrão', $cor_primaria, $cor_secundaria, $cor_terciaria]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Tema salvo com sucesso']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 