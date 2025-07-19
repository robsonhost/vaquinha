<?php
// Utilitário para integração com Mercado Pago
function get_mercado_pago_credentials() {
    require __DIR__ . '/../admin/db.php';
    $chaves = [
        'mercado_pago_public_key',
        'mercado_pago_access_token'
    ];
    $credenciais = [];
    foreach ($chaves as $chave) {
        $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
        $stmt->execute([$chave]);
        $credenciais[$chave] = $stmt->fetchColumn() ?: '';
    }
    return $credenciais;
}

function criar_pagamento_mercado_pago($valor, $descricao, $external_reference, $metodo_pagamento = 'pix', $dados_cartao = null) {
    $credenciais = get_mercado_pago_credentials();
    $access_token = $credenciais['mercado_pago_access_token'];
    
    if (!$access_token) {
        throw new Exception('Credenciais do Mercado Pago não configuradas');
    }
    
    $payload = [
        'transaction_amount' => floatval($valor),
        'description' => $descricao,
        'external_reference' => $external_reference,
        'payer' => [
            'email' => 'doador@example.com'
        ]
    ];
    
    // Configurar método de pagamento
    if ($metodo_pagamento === 'pix') {
        $payload['payment_method_id'] = 'pix';
    } elseif ($metodo_pagamento === 'cartao') {
        $payload['payment_method_id'] = $dados_cartao['payment_method_id'];
        $payload['token'] = $dados_cartao['token'];
        $payload['installments'] = $dados_cartao['installments'] ?? 1;
        $payload['payer']['identification'] = [
            'type' => 'CPF',
            'number' => $dados_cartao['cpf']
        ];
    }
    
    $ch = curl_init('https://api.mercadopago.com/v1/payments');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpcode !== 201) {
        throw new Exception('Erro ao criar pagamento: ' . $response);
    }
    
    $data = json_decode($response, true);
    
    if ($metodo_pagamento === 'pix') {
        return [
            'id' => $data['id'],
            'qr_code' => $data['point_of_interaction']['transaction_data']['qr_code'],
            'qr_code_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'],
            'status' => $data['status']
        ];
    } else {
        return [
            'id' => $data['id'],
            'status' => $data['status'],
            'status_detail' => $data['status_detail']
        ];
    }
}

function criar_token_cartao($dados_cartao) {
    $credenciais = get_mercado_pago_credentials();
    $public_key = $credenciais['mercado_pago_public_key'];
    
    if (!$public_key) {
        throw new Exception('Public Key do Mercado Pago não configurada');
    }
    
    $payload = [
        'card_number' => $dados_cartao['card_number'],
        'security_code' => $dados_cartao['security_code'],
        'expiration_month' => $dados_cartao['expiration_month'],
        'expiration_year' => $dados_cartao['expiration_year'],
        'cardholder' => [
            'name' => $dados_cartao['cardholder_name'],
            'identification' => [
                'type' => 'CPF',
                'number' => $dados_cartao['cpf']
            ]
        ]
    ];
    
    $ch = curl_init('https://api.mercadopago.com/v1/card_tokens');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $public_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpcode !== 201) {
        throw new Exception('Erro ao criar token do cartão: ' . $response);
    }
    
    $data = json_decode($response, true);
    return $data['id'];
}

function verificar_status_pagamento($payment_id) {
    $credenciais = get_mercado_pago_credentials();
    $access_token = $credenciais['mercado_pago_access_token'];
    
    $ch = curl_init('https://api.mercadopago.com/v1/payments/' . $payment_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpcode !== 200) {
        throw new Exception('Erro ao verificar pagamento');
    }
    
    $data = json_decode($response, true);
    return [
        'status' => $data['status'],
        'external_reference' => $data['external_reference']
    ];
}

function processar_webhook_mercado_pago($data) {
    require __DIR__ . '/../admin/db.php';
    
    if ($data['type'] !== 'payment') {
        return false;
    }
    
    $payment_id = $data['data']['id'];
    $status_info = verificar_status_pagamento($payment_id);
    
    if ($status_info['status'] === 'approved') {
        // Buscar doação pelo external_reference
        $stmt = $pdo->prepare('SELECT * FROM doacoes WHERE id = ?');
        $stmt->execute([$status_info['external_reference']]);
        $doacao = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doacao && $doacao['status'] === 'pendente') {
            $pdo->beginTransaction();
            
            try {
                // Atualizar status da doação
                $stmt = $pdo->prepare('UPDATE doacoes SET status = "confirmada", aprovado_em = NOW() WHERE id = ?');
                $stmt->execute([$doacao['id']]);
                
                // Atualizar arrecadado na campanha
                $stmt = $pdo->prepare('UPDATE campanhas SET arrecadado = arrecadado + ? WHERE id = ?');
                $stmt->execute([$doacao['valor'], $doacao['campanha_id']]);
                
                // Notificar criador da campanha via WhatsApp
                require_once __DIR__ . '/whatsapp.php';
                $stmt = $pdo->prepare('SELECT c.titulo, u.nome, u.telefone, c.arrecadado, c.meta FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?');
                $stmt->execute([$doacao['campanha_id']]);
                $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($campanha && $campanha['telefone']) {
                    $msg = "💰 Parabéns, {$campanha['nome']}! Você recebeu uma nova doação de R$ " . number_format($doacao['valor'], 2, ',', '.') . " em sua campanha '{$campanha['titulo']}'.\nSeu total arrecadado agora é R$ " . number_format($campanha['arrecadado'] + $doacao['valor'], 2, ',', '.') . ".\nContinue divulgando e inspire mais pessoas! 🙌";
                    enviar_whatsapp($campanha['telefone'], $msg);
                    
                    // Se atingiu a meta, enviar parabéns
                    if ($campanha['arrecadado'] + $doacao['valor'] >= $campanha['meta']) {
                        $msg_meta = "🎯 Uau, {$campanha['nome']}! Sua campanha '{$campanha['titulo']}' atingiu a meta!\nParabéns por essa conquista! 🏆";
                        enviar_whatsapp($campanha['telefone'], $msg_meta);
                    }
                }
                
                $pdo->commit();
                return true;
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    }
    
    return false;
}
?> 