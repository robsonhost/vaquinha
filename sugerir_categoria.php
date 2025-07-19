<?php
require 'admin/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$imagem = '';

if (empty($nome)) {
    echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
    exit;
}

// Upload de imagem
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Formato de imagem não suportado']);
        exit;
    }
    $nome_arquivo = 'cat_' . uniqid() . '.' . $ext;
    $upload_path = 'images/' . $nome_arquivo;
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $upload_path)) {
        $imagem = $upload_path;
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao fazer upload da imagem']);
        exit;
    }
}

try {
    sugerirCategoria($pdo, $nome, $descricao, $imagem);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao sugerir categoria']);
} 