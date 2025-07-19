<?php
require '../db.php';
session_start();

// Verificar se é admin
if (!isset($_SESSION['admin_id'])) {
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
    $tipo = $input['tipo'] ?? 'campanhas';
    $filtros = $input['filtros'] ?? [];
    
    // Construir query baseada no tipo
    switch ($tipo) {
        case 'campanhas':
            $sql = "SELECT 
                        c.id,
                        c.titulo,
                        c.descricao,
                        c.meta,
                        c.arrecadado,
                        c.status,
                        c.destaque,
                        c.criado_em,
                        u.nome as criador,
                        u.email as email_criador,
                        cat.nome as categoria
                    FROM campanhas c 
                    LEFT JOIN usuarios u ON c.usuario_id = u.id 
                    LEFT JOIN categorias cat ON c.categoria_id = cat.id";
            break;
            
        case 'doacoes':
            $sql = "SELECT 
                        d.id,
                        d.valor,
                        d.metodo_pagamento,
                        d.status,
                        d.mensagem,
                        d.criado_em,
                        u.nome as doador,
                        u.email as email_doador,
                        c.titulo as campanha
                    FROM doacoes d 
                    LEFT JOIN usuarios u ON d.usuario_id = u.id 
                    LEFT JOIN campanhas c ON d.campanha_id = c.id";
            break;
            
        case 'usuarios':
            $sql = "SELECT 
                        id,
                        nome,
                        email,
                        telefone,
                        criado_em,
                        ultimo_acesso
                    FROM usuarios";
            break;
            
        case 'categorias':
            $sql = "SELECT 
                        id,
                        nome,
                        descricao,
                        criado_em
                    FROM categorias";
            break;
            
        default:
            throw new Exception('Tipo de relatório inválido');
    }
    
    // Aplicar filtros
    $where_conditions = [];
    $params = [];
    
    if (!empty($filtros['data_inicio'])) {
        $where_conditions[] = "DATE(criado_em) >= ?";
        $params[] = $filtros['data_inicio'];
    }
    
    if (!empty($filtros['data_fim'])) {
        $where_conditions[] = "DATE(criado_em) <= ?";
        $params[] = $filtros['data_fim'];
    }
    
    if (!empty($filtros['status'])) {
        $where_conditions[] = "status = ?";
        $params[] = $filtros['status'];
    }
    
    if (!empty($filtros['categoria_id'])) {
        $where_conditions[] = "categoria_id = ?";
        $params[] = $filtros['categoria_id'];
    }
    
    if (!empty($where_conditions)) {
        $sql .= " WHERE " . implode(' AND ', $where_conditions);
    }
    
    $sql .= " ORDER BY criado_em DESC";
    
    // Executar query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gerar CSV
    $filename = "relatorio_{$tipo}_" . date('Y-m-d_H-i-s') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // BOM para UTF-8
    echo "\xEF\xBB\xBF";
    
    // Cabeçalho
    if (!empty($dados)) {
        echo implode(',', array_keys($dados[0])) . "\n";
        
        // Dados
        foreach ($dados as $linha) {
            $csv_linha = [];
            foreach ($linha as $valor) {
                // Escapar vírgulas e aspas
                $valor = str_replace('"', '""', $valor);
                $csv_linha[] = '"' . $valor . '"';
            }
            echo implode(',', $csv_linha) . "\n";
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage()]);
}
?> 