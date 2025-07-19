<?php
require 'db.php';

// Carregar tema ativo
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);

if (!$tema) {
    // Tema padrão se nenhum estiver ativo
    $tema = [
        'id' => 1,
        'nome' => 'Padrão',
        'cor_primaria' => '#782F9B',
        'cor_secundaria' => '#65A300',
        'cor_terciaria' => '#F7F7F7',
        'ativo' => 1
    ];
}

// Carregar configurações gerais
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

// Carregar logo
$logo = $pdo->query('SELECT * FROM logo_site WHERE id=1')->fetch(PDO::FETCH_ASSOC);

// Preparar resposta
$response = [
    'tema' => $tema,
    'configuracoes' => [
        'nome_site' => $textos['nome_site'] ?? 'Vaquinha Online',
        'descricao_site' => $textos['descricao_site'] ?? 'Plataforma de vaquinhas online',
        'whatsapp' => $textos['whatsapp'] ?? '',
        'email' => $textos['email'] ?? '',
        'quem_somos' => $textos['quem_somos'] ?? '',
        'taxa_padrao' => floatval($textos['taxa_padrao'] ?? 2.5),
        'manutencao' => boolval($textos['manutencao'] ?? 0),
        'registro_usuarios' => boolval($textos['registro_usuarios'] ?? 1),
        'aprovacao_campanhas' => boolval($textos['aprovacao_campanhas'] ?? 1),
        'max_upload_size' => intval($textos['max_upload_size'] ?? 5),
        'itens_por_pagina' => intval($textos['itens_por_pagina'] ?? 12)
    ],
    'logo' => $logo ? $logo['caminho'] : null,
    'timestamp' => time()
];

// Definir headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Retornar JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?> 