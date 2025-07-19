<?php
$host = 'localhost';
$db = 'vaquinha';
$user = 'root'; // Troque pelo usuário do seu MySQL
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

function sugerirCategoria($pdo, $nome, $descricao, $imagem) {
    $stmt = $pdo->prepare('INSERT INTO categorias (nome, descricao, imagem, status) VALUES (?, ?, ?, ?)');
    return $stmt->execute([$nome, $descricao, $imagem, 'pendente']);
} 