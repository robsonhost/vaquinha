<?php
require 'admin/db.php';
require 'includes/mercado_pago.php';
session_start();

$doacao_id = intval($_GET['doacao_id'] ?? 0);
if (!$doacao_id) {
    header('Location: index.php');
    exit;
}

// Buscar dados da doação
$stmt = $pdo->prepare('SELECT d.*, c.titulo as campanha_titulo, c.imagem as campanha_imagem FROM doacoes d JOIN campanhas c ON d.campanha_id = c.id WHERE d.id = ?');
$stmt->execute([$doacao_id]);
$doacao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doacao) {
    header('Location: index.php');
    exit;
}

// Buscar dados do pagamento
$pagamento = null;
if ($doacao['payment_id']) {
    try {
        $status_info = verificar_status_pagamento($doacao['payment_id']);
        $pagamento = [
            'id' => $doacao['payment_id'],
            'status' => $status_info['status']
        ];
    } catch (Exception $e) {
        // Erro ao verificar status
    }
}

// Carregar tema
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - <?= htmlspecialchars($doacao['campanha_titulo']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --cor-primaria: <?= $tema['cor_primaria'] ?>;
            --cor-secundaria: <?= $tema['cor_secundaria'] ?>;
            --cor-terciaria: <?= $tema['cor_terciaria'] ?>;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .pagamento-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .pagamento-header {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .pagamento-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .pagamento-body {
            padding: 3rem 2rem;
        }
        
        .qr-code-container {
            text-align: center;
            padding: 2rem;
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .qr-code-container img {
            max-width: 200px;
            height: auto;
        }
        
        .instrucoes {
            background: var(--cor-terciaria);
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
        }
        
        .instrucoes h5 {
            color: var(--cor-primaria);
            margin-bottom: 1rem;
        }
        
        .instrucoes ol {
            margin-bottom: 0;
        }
        
        .instrucoes li {
            margin-bottom: 0.5rem;
        }
        
        .status-pagamento {
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            margin: 1rem 0;
        }
        
        .status-pendente {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-aprovado {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .campanha-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .campanha-imagem {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 1rem;
        }
        
        @media (max-width: 768px) {
            .pagamento-container {
                margin: 1rem;
            }
            
            .pagamento-header {
                padding: 1.5rem 1rem;
            }
            
            .pagamento-header h1 {
                font-size: 1.5rem;
            }
            
            .pagamento-body {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="pagamento-container">
                    <!-- Header -->
                    <div class="pagamento-header">
                        <h1><i class="fas fa-qrcode"></i> Pagamento PIX</h1>
                        <p>Escaneie o QR Code ou copie o código PIX para finalizar sua doação</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="pagamento-body">
                        <!-- Informações da Campanha -->
                        <div class="campanha-info">
                            <div class="d-flex align-items-center">
                                <?php if ($doacao['campanha_imagem']): ?>
                                    <img src="<?= htmlspecialchars($doacao['campanha_imagem']) ?>" alt="Campanha" class="campanha-imagem">
                                <?php endif; ?>
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($doacao['campanha_titulo']) ?></h5>
                                    <p class="mb-0 text-muted">Valor da doação: <strong class="text-success">R$ <?= number_format($doacao['valor'], 2, ',', '.') ?></strong></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status do Pagamento -->
                        <?php if ($pagamento): ?>
                            <div class="status-pagamento status-<?= $pagamento['status'] === 'approved' ? 'aprovado' : 'pendente' ?>">
                                <i class="fas fa-<?= $pagamento['status'] === 'approved' ? 'check-circle' : 'clock' ?>"></i>
                                <?= $pagamento['status'] === 'approved' ? 'Pagamento Aprovado!' : 'Aguardando Pagamento' ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- QR Code -->
                        <div class="qr-code-container">
                            <h4 class="mb-3"><i class="fas fa-mobile-alt"></i> Escaneie o QR Code</h4>
                            <div id="qr-code">
                                <!-- QR Code será inserido aqui via JavaScript -->
                            </div>
                            <p class="text-muted mt-3">Use o app do seu banco para escanear</p>
                        </div>
                        
                        <!-- Instruções -->
                        <div class="instrucoes">
                            <h5><i class="fas fa-info-circle"></i> Como pagar com PIX:</h5>
                            <ol>
                                <li>Abra o app do seu banco</li>
                                <li>Procure a opção "PIX" ou "Pagar com PIX"</li>
                                <li>Escolha "Escanear QR Code"</li>
                                <li>Aponte a câmera para o código acima</li>
                                <li>Confirme o valor e finalize o pagamento</li>
                            </ol>
                        </div>
                        
                        <!-- Código PIX (opcional) -->
                        <div class="text-center">
                            <button class="btn btn-outline-primary" onclick="copiarPix()">
                                <i class="fas fa-copy"></i> Copiar Código PIX
                            </button>
                        </div>
                        
                        <!-- Voltar -->
                        <div class="text-center mt-4">
                            <a href="detalhes_campanha.php?id=<?= $doacao['campanha_id'] ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Voltar para a Campanha
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Função para copiar código PIX
        function copiarPix() {
            // Aqui você pode implementar a cópia do código PIX
            alert('Funcionalidade de copiar código PIX será implementada');
        }
        
        // Verificar status do pagamento periodicamente
        function verificarStatus() {
            fetch('verificar_pagamento.php?doacao_id=<?= $doacao_id ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'approved') {
                        location.reload();
                    }
                })
                .catch(error => console.error('Erro:', error));
        }
        
        // Verificar a cada 5 segundos
        setInterval(verificarStatus, 5000);
    </script>
</body>
</html> 