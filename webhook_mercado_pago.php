<?php
require 'admin/db.php';
require 'includes/mercado_pago.php';

// Log do webhook
$log_file = 'logs/webhook_mercado_pago.log';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

function log_webhook($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Receber dados do webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

log_webhook("Webhook recebido: " . json_encode($data));

// Verificar se é uma notificação de pagamento
if ($data && isset($data['type']) && $data['type'] === 'payment') {
    try {
        $resultado = processar_webhook_mercado_pago($data);
        
        if ($resultado) {
            log_webhook("Pagamento processado com sucesso");
            http_response_code(200);
            echo "OK";
        } else {
            log_webhook("Pagamento não processado");
            http_response_code(200);
            echo "OK";
        }
    } catch (Exception $e) {
        log_webhook("Erro ao processar pagamento: " . $e->getMessage());
        http_response_code(500);
        echo "ERROR";
    }
} else {
    log_webhook("Tipo de notificação não suportado");
    http_response_code(200);
    echo "OK";
}
?> 