<?php
/**
 * Sistema de Mercado Pago Melhorado v2.0
 * Integração completa com validações e logs
 */

// Função para obter credenciais do Mercado Pago
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

// Função para validar credenciais
function validar_credenciais_mercado_pago() {
    $credenciais = get_mercado_pago_credentials();
    
    if (empty($credenciais['mercado_pago_access_token'])) {
        throw new Exception('Access Token do Mercado Pago não configurado');
    }
    
    if (empty($credenciais['mercado_pago_public_key'])) {
        throw new Exception('Public Key do Mercado Pago não configurada');
    }
    
    // Validar formato das chaves
    if (!preg_match('/^(TEST-|APP_USR-|APP-)/', $credenciais['mercado_pago_access_token'])) {
        throw new Exception('Formato do Access Token inválido');
    }
    
    if (!preg_match('/^(TEST-|APP_USR-|APP-)/', $credenciais['mercado_pago_public_key'])) {
        throw new Exception('Formato da Public Key inválido');
    }
    
    return $credenciais;
}

// Função para criar pagamento PIX ou Cartão
function criar_pagamento_mercado_pago($valor, $descricao, $external_reference, $metodo_pagamento = 'pix', $dados_cartao = null, $dados_comprador = null) {
    try {
        log_mercado_pago("Iniciando criação de pagamento: {$metodo_pagamento} - R$ {$valor}");
        
        $credenciais = validar_credenciais_mercado_pago();
        $access_token = $credenciais['mercado_pago_access_token'];
        
        // Validar valor
        if ($valor <= 0) {
            throw new Exception('Valor deve ser maior que zero');
        }
        
        // Payload base
        $payload = [
            'transaction_amount' => floatval($valor),
            'description' => $descricao,
            'external_reference' => (string)$external_reference,
            'notification_url' => get_webhook_url(),
            'metadata' => [
                'sistema' => 'vaquinha_online',
                'versao' => '2.0',
                'timestamp' => time()
            ]
        ];
        
        // Configurar comprador
        if ($dados_comprador) {
            $payload['payer'] = [
                'email' => $dados_comprador['email'] ?? 'doador@exemplo.com',
                'first_name' => $dados_comprador['nome'] ?? 'Doador',
                'phone' => [
                    'area_code' => substr($dados_comprador['telefone'] ?? '11', 0, 2),
                    'number' => substr($dados_comprador['telefone'] ?? '999999999', 2)
                ]
            ];
            
            if (isset($dados_comprador['cpf'])) {
                $payload['payer']['identification'] = [
                    'type' => 'CPF',
                    'number' => preg_replace('/[^0-9]/', '', $dados_comprador['cpf'])
                ];
            }
        } else {
            $payload['payer'] = [
                'email' => 'doador@exemplo.com'
            ];
        }
        
        // Configurar método de pagamento
        if ($metodo_pagamento === 'pix') {
            $payload['payment_method_id'] = 'pix';
            $payload['date_of_expiration'] = date('c', strtotime('+30 minutes'));
            
        } elseif ($metodo_pagamento === 'cartao' && $dados_cartao) {
            $payload['payment_method_id'] = $dados_cartao['payment_method_id'] ?? 'visa';
            $payload['token'] = $dados_cartao['token'];
            $payload['installments'] = intval($dados_cartao['installments'] ?? 1);
            $payload['issuer_id'] = $dados_cartao['issuer_id'] ?? null;
            
            // Adicionar dados do portador se disponível
            if (isset($dados_cartao['cardholder_name'])) {
                $payload['cardholder'] = [
                    'name' => $dados_cartao['cardholder_name']
                ];
            }
            
        } else {
            throw new Exception('Método de pagamento inválido ou dados insuficientes');
        }
        
        log_mercado_pago("Payload preparado: " . json_encode($payload, JSON_UNESCAPED_UNICODE));
        
        // Fazer requisição
        $response = fazer_requisicao_mercado_pago('POST', '/v1/payments', $payload, $access_token);
        
        log_mercado_pago("Pagamento criado com sucesso: ID " . $response['id']);
        
        // Registrar no banco
        registrar_transacao_mercado_pago($response['id'], $external_reference, $metodo_pagamento, $valor, $response['status']);
        
        // Retornar dados específicos por método
        if ($metodo_pagamento === 'pix') {
            return [
                'id' => $response['id'],
                'status' => $response['status'],
                'qr_code' => $response['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                'qr_code_base64' => $response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                'ticket_url' => $response['point_of_interaction']['transaction_data']['ticket_url'] ?? null,
                'date_of_expiration' => $response['date_of_expiration'] ?? null
            ];
        } else {
            return [
                'id' => $response['id'],
                'status' => $response['status'],
                'status_detail' => $response['status_detail'] ?? null,
                'payment_method_id' => $response['payment_method_id'] ?? null,
                'payment_type_id' => $response['payment_type_id'] ?? null
            ];
        }
        
    } catch (Exception $e) {
        log_mercado_pago("Erro ao criar pagamento: " . $e->getMessage());
        throw $e;
    }
}

// Função para criar token do cartão
function criar_token_cartao($dados_cartao) {
    try {
        log_mercado_pago("Criando token de cartão");
        
        $credenciais = validar_credenciais_mercado_pago();
        $public_key = $credenciais['mercado_pago_public_key'];
        
        // Validar dados obrigatórios
        $campos_obrigatorios = ['card_number', 'security_code', 'expiration_month', 'expiration_year', 'cardholder_name'];
        foreach ($campos_obrigatorios as $campo) {
            if (empty($dados_cartao[$campo])) {
                throw new Exception("Campo obrigatório não fornecido: {$campo}");
            }
        }
        
        // Limpar número do cartão
        $card_number = preg_replace('/[^0-9]/', '', $dados_cartao['card_number']);
        if (strlen($card_number) < 13 || strlen($card_number) > 19) {
            throw new Exception('Número do cartão inválido');
        }
        
        $payload = [
            'card_number' => $card_number,
            'security_code' => $dados_cartao['security_code'],
            'expiration_month' => str_pad($dados_cartao['expiration_month'], 2, '0', STR_PAD_LEFT),
            'expiration_year' => $dados_cartao['expiration_year'],
            'cardholder' => [
                'name' => $dados_cartao['cardholder_name']
            ]
        ];
        
        if (isset($dados_cartao['cpf'])) {
            $payload['cardholder']['identification'] = [
                'type' => 'CPF',
                'number' => preg_replace('/[^0-9]/', '', $dados_cartao['cpf'])
            ];
        }
        
        $response = fazer_requisicao_mercado_pago('POST', '/v1/card_tokens', $payload, $public_key);
        
        log_mercado_pago("Token criado com sucesso: " . $response['id']);
        
        return [
            'id' => $response['id'],
            'first_six_digits' => $response['first_six_digits'] ?? null,
            'last_four_digits' => $response['last_four_digits'] ?? null,
            'cardholder_name' => $response['cardholder']['name'] ?? null,
            'expiration_month' => $response['expiration_month'] ?? null,
            'expiration_year' => $response['expiration_year'] ?? null
        ];
        
    } catch (Exception $e) {
        log_mercado_pago("Erro ao criar token: " . $e->getMessage());
        throw $e;
    }
}

// Função para verificar status do pagamento
function verificar_status_pagamento($payment_id) {
    try {
        $credenciais = validar_credenciais_mercado_pago();
        $access_token = $credenciais['mercado_pago_access_token'];
        
        $response = fazer_requisicao_mercado_pago('GET', "/v1/payments/{$payment_id}", null, $access_token);
        
        // Atualizar status no banco
        atualizar_status_transacao($payment_id, $response['status']);
        
        return [
            'id' => $response['id'],
            'status' => $response['status'],
            'status_detail' => $response['status_detail'] ?? null,
            'external_reference' => $response['external_reference'] ?? null,
            'payment_method_id' => $response['payment_method_id'] ?? null,
            'payment_type_id' => $response['payment_type_id'] ?? null,
            'transaction_amount' => $response['transaction_amount'] ?? null,
            'date_created' => $response['date_created'] ?? null,
            'date_approved' => $response['date_approved'] ?? null
        ];
        
    } catch (Exception $e) {
        log_mercado_pago("Erro ao verificar pagamento {$payment_id}: " . $e->getMessage());
        throw $e;
    }
}

// Função para processar webhook
function processar_webhook_mercado_pago($data) {
    require __DIR__ . '/../admin/db.php';
    
    try {
        log_mercado_pago("Processando webhook: " . json_encode($data));
        
        if (!isset($data['type']) || $data['type'] !== 'payment') {
            log_mercado_pago("Tipo de webhook não suportado: " . ($data['type'] ?? 'indefinido'));
            return false;
        }
        
        $payment_id = $data['data']['id'] ?? null;
        if (!$payment_id) {
            log_mercado_pago("ID do pagamento não encontrado no webhook");
            return false;
        }
        
        // Verificar status atual do pagamento
        $status_info = verificar_status_pagamento($payment_id);
        
        if ($status_info['status'] === 'approved') {
            log_mercado_pago("Processando pagamento aprovado: {$payment_id}");
            
            // Buscar doação pelo external_reference
            $stmt = $pdo->prepare('SELECT * FROM doacoes WHERE id = ?');
            $stmt->execute([$status_info['external_reference']]);
            $doacao = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$doacao) {
                log_mercado_pago("Doação não encontrada: " . $status_info['external_reference']);
                return false;
            }
            
            if ($doacao['status'] === 'confirmada') {
                log_mercado_pago("Doação já processada: " . $doacao['id']);
                return true;
            }
            
            $pdo->beginTransaction();
            
            try {
                // Atualizar status da doação
                $stmt = $pdo->prepare('UPDATE doacoes SET status = "confirmada", payment_id = ?, aprovado_em = NOW() WHERE id = ?');
                $stmt->execute([$payment_id, $doacao['id']]);
                
                // Atualizar arrecadado na campanha
                $stmt = $pdo->prepare('UPDATE campanhas SET arrecadado = arrecadado + ? WHERE id = ?');
                $stmt->execute([$doacao['valor'], $doacao['campanha_id']]);
                
                // Buscar dados da campanha e usuário para notificação
                $stmt = $pdo->prepare('
                    SELECT c.titulo, c.arrecadado, c.meta, u.nome, u.telefone, u.email 
                    FROM campanhas c 
                    JOIN usuarios u ON c.usuario_id = u.id 
                    WHERE c.id = ?
                ');
                $stmt->execute([$doacao['campanha_id']]);
                $campanha = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($campanha) {
                    $total_arrecadado = $campanha['arrecadado'] + $doacao['valor'];
                    
                    // Enviar notificação WhatsApp
                    if ($campanha['telefone']) {
                        require_once __DIR__ . '/whatsapp.php';
                        enviar_notificacao_doacao(
                            $campanha['telefone'],
                            $campanha['titulo'],
                            $doacao['nome'] ?? 'Doador Anônimo',
                            $doacao['valor'],
                            $total_arrecadado,
                            $campanha['meta']
                        );
                    }
                    
                    // Enviar email de notificação (opcional)
                    if ($campanha['email']) {
                        enviar_email_notificacao_doacao($campanha, $doacao, $total_arrecadado);
                    }
                }
                
                $pdo->commit();
                log_mercado_pago("Doação processada com sucesso: {$doacao['id']}");
                return true;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                log_mercado_pago("Erro ao processar doação: " . $e->getMessage());
                throw $e;
            }
        } else {
            log_mercado_pago("Pagamento não aprovado: {$payment_id} - Status: {$status_info['status']}");
            return false;
        }
        
    } catch (Exception $e) {
        log_mercado_pago("Erro no webhook: " . $e->getMessage());
        return false;
    }
}

// Função para fazer requisições à API do Mercado Pago
function fazer_requisicao_mercado_pago($method, $endpoint, $data = null, $token = null) {
    $url = 'https://api.mercadopago.com' . $endpoint;
    
    $headers = [
        'Content-Type: application/json',
        'User-Agent: Vaquinha-MercadoPago/2.0',
        'X-Idempotency-Key: ' . uniqid('vaquinha_', true)
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Erro CURL: " . $error);
    }
    
    $decoded = json_decode($response, true);
    
    if ($httpcode >= 400) {
        $error_message = isset($decoded['message']) ? $decoded['message'] : 'Erro desconhecido';
        $error_details = isset($decoded['cause']) ? json_encode($decoded['cause']) : '';
        throw new Exception("Erro da API Mercado Pago (HTTP {$httpcode}): {$error_message} {$error_details}");
    }
    
    if (!$decoded) {
        throw new Exception("Resposta inválida da API: " . $response);
    }
    
    return $decoded;
}

// Função para obter URL do webhook
function get_webhook_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$protocol}://{$host}/webhook_mercado_pago.php";
}

// Função para registrar logs
function log_mercado_pago($mensagem) {
    $log_file = __DIR__ . '/../logs/mercado_pago.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[{$timestamp}] {$mensagem}\n", FILE_APPEND | LOCK_EX);
}

// Função para registrar transações no banco
function registrar_transacao_mercado_pago($payment_id, $external_reference, $metodo, $valor, $status) {
    require __DIR__ . '/../admin/db.php';
    
    try {
        // Criar tabela se não existir
        $pdo->exec("CREATE TABLE IF NOT EXISTS logs_mercado_pago (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id VARCHAR(100) NOT NULL,
            external_reference VARCHAR(100),
            metodo_pagamento VARCHAR(50),
            valor DECIMAL(10,2),
            status VARCHAR(50),
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_payment_id (payment_id),
            INDEX idx_external_ref (external_reference),
            INDEX idx_status (status)
        )");
        
        $stmt = $pdo->prepare("
            INSERT INTO logs_mercado_pago (payment_id, external_reference, metodo_pagamento, valor, status) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            status = VALUES(status), 
            data_atualizacao = CURRENT_TIMESTAMP
        ");
        
        $stmt->execute([$payment_id, $external_reference, $metodo, $valor, $status]);
        
    } catch (Exception $e) {
        log_mercado_pago("Erro ao registrar transação: " . $e->getMessage());
    }
}

// Função para atualizar status da transação
function atualizar_status_transacao($payment_id, $status) {
    require __DIR__ . '/../admin/db.php';
    
    try {
        $stmt = $pdo->prepare("UPDATE logs_mercado_pago SET status = ?, data_atualizacao = NOW() WHERE payment_id = ?");
        $stmt->execute([$status, $payment_id]);
    } catch (Exception $e) {
        log_mercado_pago("Erro ao atualizar status: " . $e->getMessage());
    }
}

// Função para enviar email de notificação
function enviar_email_notificacao_doacao($campanha, $doacao, $total_arrecadado) {
    $assunto = "Nova doação recebida - {$campanha['titulo']}";
    
    $mensagem = "
    <h2>🎉 Nova doação recebida!</h2>
    <p><strong>Campanha:</strong> {$campanha['titulo']}</p>
    <p><strong>Doador:</strong> " . ($doacao['nome'] ?? 'Doador Anônimo') . "</p>
    <p><strong>Valor:</strong> R$ " . number_format($doacao['valor'], 2, ',', '.') . "</p>
    <p><strong>Total arrecadado:</strong> R$ " . number_format($total_arrecadado, 2, ',', '.') . "</p>
    <p><strong>Meta:</strong> R$ " . number_format($campanha['meta'], 2, ',', '.') . "</p>
    
    <hr>
    <p><small>Este é um email automático do sistema de vaquinhas.</small></p>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: sistema@vaquinha.com\r\n";
    
    return mail($campanha['email'], $assunto, $mensagem, $headers);
}

// Função para testar integração
function testar_mercado_pago() {
    try {
        $credenciais = validar_credenciais_mercado_pago();
        
        // Teste simples: buscar dados da conta
        $response = fazer_requisicao_mercado_pago('GET', '/v1/account/settings', null, $credenciais['mercado_pago_access_token']);
        
        return [
            'sucesso' => true,
            'dados' => [
                'site_id' => $response['site_id'] ?? 'Não disponível',
                'category_id' => $response['category_id'] ?? 'Não disponível',
                'payment_methods' => count($response['payment_methods'] ?? []) . ' métodos disponíveis'
            ]
        ];
        
    } catch (Exception $e) {
        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    }
}
?> 