<?php
/**
 * Sistema de WhatsApp Melhorado v2.0
 * Suporte a múltiplos provedores de API WhatsApp
 */

// Função principal para enviar mensagens WhatsApp
function enviar_whatsapp($telefone, $mensagem, $tentativas = 3) {
    require __DIR__ . '/../admin/db.php';
    
    // Log da tentativa
    log_whatsapp("Tentativa de envio para {$telefone}: " . substr($mensagem, 0, 50) . "...");
    
    // Validar telefone
    $telefone = validar_telefone($telefone);
    if (!$telefone) {
        log_whatsapp("Telefone inválido: {$telefone}");
        return false;
    }
    
    // Buscar configurações
    $config = obter_configuracoes_whatsapp($pdo);
    if (!$config) {
        log_whatsapp("Configurações do WhatsApp não encontradas");
        return false;
    }
    
    // Tentar enviar com múltiplas tentativas
    for ($i = 1; $i <= $tentativas; $i++) {
        try {
            $resultado = false;
            
            // Detectar provedor baseado na URL
            if (strpos($config['api_url'], 'evolution') !== false) {
                $resultado = enviar_evolution_api($telefone, $mensagem, $config);
            } elseif (strpos($config['api_url'], 'wppconnect') !== false) {
                $resultado = enviar_wppconnect_api($telefone, $mensagem, $config);
            } elseif (strpos($config['api_url'], 'baileys') !== false) {
                $resultado = enviar_baileys_api($telefone, $mensagem, $config);
            } else {
                // API genérica
                $resultado = enviar_api_generica($telefone, $mensagem, $config);
            }
            
            if ($resultado) {
                log_whatsapp("Mensagem enviada com sucesso para {$telefone} na tentativa {$i}");
                registrar_envio_whatsapp($pdo, $telefone, $mensagem, 'sucesso');
                return true;
            }
            
        } catch (Exception $e) {
            log_whatsapp("Erro na tentativa {$i} para {$telefone}: " . $e->getMessage());
            
            if ($i === $tentativas) {
                registrar_envio_whatsapp($pdo, $telefone, $mensagem, 'erro', $e->getMessage());
            }
        }
        
        // Aguardar antes de tentar novamente
        if ($i < $tentativas) {
            sleep(2);
        }
    }
    
    log_whatsapp("Falha ao enviar mensagem para {$telefone} após {$tentativas} tentativas");
    return false;
}

// Função para validar e formatar telefone
function validar_telefone($telefone) {
    // Remover caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verificar se tem pelo menos 10 dígitos
    if (strlen($telefone) < 10) {
        return false;
    }
    
    // Adicionar código do país se não tiver
    if (strlen($telefone) === 11 && $telefone[0] !== '5') {
        $telefone = '55' . $telefone;
    } elseif (strlen($telefone) === 10) {
        $telefone = '55' . $telefone;
    }
    
    return $telefone;
}

// Função para obter configurações do WhatsApp
function obter_configuracoes_whatsapp($pdo) {
    $chaves = ['whatsapp_name', 'whatsapp_token', 'whatsapp_api_url'];
    $config = [];
    
    foreach ($chaves as $chave) {
        $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
        $stmt->execute([$chave]);
        $config[str_replace('whatsapp_', '', $chave)] = $stmt->fetchColumn() ?: '';
    }
    
    // Verificar se configurações essenciais estão presentes
    if (empty($config['api_url']) || empty($config['token'])) {
        return false;
    }
    
    return $config;
}

// Evolution API
function enviar_evolution_api($telefone, $mensagem, $config) {
    $url = rtrim($config['api_url'], '/') . '/message/sendText/' . $config['name'];
    
    $payload = [
        'number' => $telefone,
        'text' => $mensagem
    ];
    
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $config['token']
    ];
    
    return fazer_requisicao_curl($url, $payload, $headers);
}

// WPPConnect API
function enviar_wppconnect_api($telefone, $mensagem, $config) {
    $url = rtrim($config['api_url'], '/') . '/' . $config['name'] . '/send-message';
    
    $payload = [
        'phone' => $telefone,
        'message' => $mensagem
    ];
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $config['token']
    ];
    
    return fazer_requisicao_curl($url, $payload, $headers);
}

// Baileys API
function enviar_baileys_api($telefone, $mensagem, $config) {
    $url = rtrim($config['api_url'], '/') . '/send-message';
    
    $payload = [
        'session' => $config['name'],
        'to' => $telefone . '@s.whatsapp.net',
        'text' => $mensagem
    ];
    
    $headers = [
        'Content-Type: application/json',
        'X-API-Key: ' . $config['token']
    ];
    
    return fazer_requisicao_curl($url, $payload, $headers);
}

// API Genérica (formato original)
function enviar_api_generica($telefone, $mensagem, $config) {
    $url = rtrim($config['api_url'], '/') . '/send-message';
    
    $payload = [
        'name' => $config['name'],
        'token' => $config['token'],
        'to' => $telefone,
        'message' => $mensagem
    ];
    
    $headers = [
        'Content-Type: application/json'
    ];
    
    return fazer_requisicao_curl($url, $payload, $headers);
}

// Função para fazer requisições CURL
function fazer_requisicao_curl($url, $payload, $headers) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Vaquinha-WhatsApp/2.0');
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("Erro CURL: " . $error);
    }
    
    if ($httpcode >= 200 && $httpcode < 300) {
        $data = json_decode($response, true);
        
        // Verificar diferentes formatos de resposta de sucesso
        if (isset($data['status']) && $data['status'] === 'success') {
            return true;
        } elseif (isset($data['success']) && $data['success'] === true) {
            return true;
        } elseif (isset($data['sent']) && $data['sent'] === true) {
            return true;
        } elseif ($httpcode === 200 || $httpcode === 201) {
            return true;
        }
    }
    
    throw new Exception("Resposta da API: HTTP {$httpcode} - " . $response);
}

// Função para registrar logs do WhatsApp
function log_whatsapp($mensagem) {
    $log_file = __DIR__ . '/../logs/whatsapp.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[{$timestamp}] {$mensagem}\n", FILE_APPEND | LOCK_EX);
}

// Função para registrar envios no banco
function registrar_envio_whatsapp($pdo, $telefone, $mensagem, $status, $erro = null) {
    try {
        // Criar tabela se não existir
        $pdo->exec("CREATE TABLE IF NOT EXISTS logs_whatsapp (
            id INT AUTO_INCREMENT PRIMARY KEY,
            telefone VARCHAR(20) NOT NULL,
            mensagem TEXT NOT NULL,
            status ENUM('sucesso', 'erro') NOT NULL,
            erro TEXT NULL,
            data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_telefone (telefone),
            INDEX idx_status (status),
            INDEX idx_data (data_envio)
        )");
        
        $stmt = $pdo->prepare("INSERT INTO logs_whatsapp (telefone, mensagem, status, erro) VALUES (?, ?, ?, ?)");
        $stmt->execute([$telefone, $mensagem, $status, $erro]);
    } catch (Exception $e) {
        log_whatsapp("Erro ao registrar no banco: " . $e->getMessage());
    }
}

// Função para testar conexão
function testar_whatsapp($telefone_teste = null) {
    require __DIR__ . '/../admin/db.php';
    
    $config = obter_configuracoes_whatsapp($pdo);
    if (!$config) {
        return ['sucesso' => false, 'erro' => 'Configurações não encontradas'];
    }
    
    $telefone = $telefone_teste ?: '5511999999999'; // Número de teste
    $mensagem = "🧪 Teste de conexão WhatsApp - " . date('d/m/Y H:i:s');
    
    try {
        $resultado = enviar_whatsapp($telefone, $mensagem, 1);
        
        if ($resultado) {
            return ['sucesso' => true, 'mensagem' => 'Teste realizado com sucesso'];
        } else {
            return ['sucesso' => false, 'erro' => 'Falha no envio'];
        }
    } catch (Exception $e) {
        return ['sucesso' => false, 'erro' => $e->getMessage()];
    }
}

// Função para enviar mensagem de boas-vindas
function enviar_boas_vindas_whatsapp($telefone, $nome, $email, $senha) {
    $textos = obter_textos_sistema();
    $nome_site = $textos['nome_site'] ?? 'Vaquinha Online';
    $url_site = 'https://' . $_SERVER['HTTP_HOST'];
    
    $mensagem = "🎉 Olá, {$nome}! Seja bem-vindo(a) ao {$nome_site}!\n\n";
    $mensagem .= "✅ Sua conta foi criada com sucesso!\n\n";
    $mensagem .= "📱 Seus dados de acesso:\n";
    $mensagem .= "E-mail: {$email}\n";
    $mensagem .= "Senha: {$senha}\n\n";
    $mensagem .= "🔗 Acesse: {$url_site}/entrar.php\n\n";
    $mensagem .= "💡 Dicas importantes:\n";
    $mensagem .= "• Compartilhe sua campanha nas redes sociais\n";
    $mensagem .= "• Conte sua história de forma emocionante\n";
    $mensagem .= "• Mantenha contato com seus doadores\n\n";
    $mensagem .= "🚀 Conte com a gente para transformar seus sonhos em realidade!\n\n";
    $mensagem .= "💙 Equipe {$nome_site}";
    
    return enviar_whatsapp($telefone, $mensagem);
}

// Função para enviar notificação de doação
function enviar_notificacao_doacao($telefone, $nome_campanha, $nome_doador, $valor_doacao, $total_arrecadado, $meta) {
    $mensagem = "💰 Oba! Nova doação recebida!\n\n";
    $mensagem .= "🎯 Campanha: {$nome_campanha}\n";
    $mensagem .= "👤 Doador: {$nome_doador}\n";
    $mensagem .= "💵 Valor: R$ " . number_format($valor_doacao, 2, ',', '.') . "\n\n";
    $mensagem .= "📊 Situação atual:\n";
    $mensagem .= "• Arrecadado: R$ " . number_format($total_arrecadado, 2, ',', '.') . "\n";
    $mensagem .= "• Meta: R$ " . number_format($meta, 2, ',', '.') . "\n";
    
    $percentual = ($total_arrecadado / $meta) * 100;
    $mensagem .= "• Progresso: " . number_format($percentual, 1) . "%\n\n";
    
    if ($percentual >= 100) {
        $mensagem .= "🏆 PARABÉNS! Sua meta foi atingida!\n";
    } elseif ($percentual >= 75) {
        $mensagem .= "🔥 Você está quase lá! Falta pouco!\n";
    } elseif ($percentual >= 50) {
        $mensagem .= "💪 Você já chegou na metade! Continue divulgando!\n";
    } else {
        $mensagem .= "🚀 Continue divulgando para alcançar sua meta!\n";
    }
    
    $mensagem .= "\nContinue engajando seus apoiadores! 💙";
    
    return enviar_whatsapp($telefone, $mensagem);
}

// Função auxiliar para obter textos do sistema
function obter_textos_sistema() {
    require __DIR__ . '/../admin/db.php';
    
    $textos = [];
    $stmt = $pdo->query('SELECT chave, valor FROM textos');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $textos[$row['chave']] = $row['valor'];
    }
    
    return $textos;
}
?> 