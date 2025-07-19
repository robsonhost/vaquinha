<?php
// Utilitário para enviar mensagens WhatsApp
function enviar_whatsapp($telefone, $mensagem) {
    require __DIR__ . '/../admin/db.php';
    // Buscar credenciais do banco
    $chaves = [
        'whatsapp_name',
        'whatsapp_token',
        'whatsapp_api_url'
    ];
    $integracoes = [];
    foreach ($chaves as $chave) {
        $stmt = $pdo->prepare('SELECT valor FROM textos WHERE chave = ?');
        $stmt->execute([$chave]);
        $integracoes[$chave] = $stmt->fetchColumn() ?: '';
    }
    $api_url = rtrim($integracoes['whatsapp_api_url'], '/') . '/send-message';
    $token = $integracoes['whatsapp_token'];
    $name = $integracoes['whatsapp_name'];

    $payload = [
        'name' => $name,
        'token' => $token,
        'to' => $telefone,
        'message' => $mensagem
    ];

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpcode === 200;
} 