<?php
require 'admin/db.php';
require 'includes/mercado_pago.php';

header('Content-Type: application/json');

// Receber dados do POST
$input = json_decode(file_get_contents('php://input'), true);

$doacao_id = intval($input['doacao_id'] ?? 0);
$token = $input['token'] ?? '';
$installments = intval($input['installments'] ?? 1);
$payment_method_id = $input['payment_method_id'] ?? '';

if (!$doacao_id || !$token) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

try {
    // Buscar dados da doação
    $stmt = $pdo->prepare('SELECT d.*, c.titulo FROM doacoes d JOIN campanhas c ON d.campanha_id = c.id WHERE d.id = ?');
    $stmt->execute([$doacao_id]);
    $doacao = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doacao) {
        throw new Exception('Doação não encontrada');
    }
    
    // Criar pagamento no Mercado Pago
    $descricao = "Doação para campanha: {$doacao['titulo']}";
    $dados_cartao = [
        'payment_method_id' => $payment_method_id,
        'token' => $token,
        'installments' => $installments,
        'cpf' => '00000000000' // Será preenchido pelo token
    ];
    
    $pagamento = criar_pagamento_mercado_pago($doacao['valor'], $descricao, $doacao_id, 'cartao', $dados_cartao);
    
    // Atualizar doação com ID do pagamento
    $stmt = $pdo->prepare('UPDATE doacoes SET payment_id = ?, status = ? WHERE id = ?');
    $stmt->execute([$pagamento['id'], $pagamento['status'], $doacao_id]);
    
    if ($pagamento['status'] === 'approved') {
        // Atualizar arrecadado na campanha
        $stmt = $pdo->prepare('UPDATE campanhas SET arrecadado = arrecadado + ? WHERE id = ?');
        $stmt->execute([$doacao['valor'], $doacao['campanha_id']]);
        
        // Notificar criador da campanha via WhatsApp
        require_once 'includes/whatsapp.php';
        $stmt = $pdo->prepare('SELECT c.titulo, u.nome, u.telefone, c.arrecadado, c.meta FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?');
        $stmt->execute([$doacao['campanha_id']]);
        $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($campanha && $campanha['telefone']) {
            $total_arrecadado = $campanha['arrecadado'] + $doacao['valor'];
            enviar_notificacao_doacao(
                $campanha['telefone'],
                $campanha['titulo'],
                $doacao['nome'] ?? 'Doador Anônimo',
                $doacao['valor'],
                $total_arrecadado,
                $campanha['meta']
            );
        }
    }
    
    echo json_encode([
        'success' => true,
        'status' => $pagamento['status'],
        'payment_id' => $pagamento['id']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 