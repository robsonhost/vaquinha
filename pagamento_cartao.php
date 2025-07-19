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

// Buscar credenciais do Mercado Pago
$credenciais = get_mercado_pago_credentials();
$public_key = $credenciais['mercado_pago_public_key'];

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
    <title>Pagamento com Cartão - <?= htmlspecialchars($doacao['campanha_titulo']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Mercado Pago SDK -->
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    
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
        
        .form-pagamento {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .form-pagamento h4 {
            color: var(--cor-primaria);
            margin-bottom: 1.5rem;
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
        
        .status-erro {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                        <h1><i class="fas fa-credit-card"></i> Pagamento com Cartão</h1>
                        <p>Preencha os dados do seu cartão para finalizar a doação</p>
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
                        <div id="status-pagamento" class="status-pagamento status-pendente" style="display:none;">
                            <i class="fas fa-clock"></i>
                            <span id="status-text">Processando pagamento...</span>
                        </div>
                        
                        <!-- Formulário de Pagamento -->
                        <div class="form-pagamento">
                            <h4><i class="fas fa-credit-card"></i> Dados do Cartão</h4>
                            
                            <form id="form-pagamento">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Número do Cartão</label>
                                            <input type="text" id="cardNumber" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Nome do Titular</label>
                                            <input type="text" id="cardholderName" class="form-control" placeholder="Nome como está no cartão">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Mês de Expiração</label>
                                            <select id="expirationMonth" class="form-control">
                                                <option value="">Mês</option>
                                                <?php for($i = 1; $i <= 12; $i++): ?>
                                                    <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Ano de Expiração</label>
                                            <select id="expirationYear" class="form-control">
                                                <option value="">Ano</option>
                                                <?php for($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label>Código de Segurança</label>
                                            <input type="text" id="securityCode" class="form-control" placeholder="123" maxlength="4">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>CPF do Titular</label>
                                            <input type="text" id="identificationNumber" class="form-control" placeholder="000.000.000-00" maxlength="14">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label>Parcelas</label>
                                            <select id="installments" class="form-control">
                                                <option value="1">1x sem juros</option>
                                                <option value="2">2x sem juros</option>
                                                <option value="3">3x sem juros</option>
                                                <option value="4">4x sem juros</option>
                                                <option value="5">5x sem juros</option>
                                                <option value="6">6x sem juros</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg" id="btn-pagar">
                                        <i class="fas fa-lock"></i> Pagar R$ <?= number_format($doacao['valor'], 2, ',', '.') ?>
                                    </button>
                                </div>
                            </form>
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
        // Inicializar Mercado Pago
        const mp = new MercadoPago('<?= $public_key ?>');
        
        // Máscaras para os campos
        document.getElementById('cardNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(\d)/, '$1 $2');
            value = value.replace(/(\d{4})(\d)/, '$1 $2');
            value = value.replace(/(\d{4})(\d)/, '$1 $2');
            e.target.value = value;
        });
        
        document.getElementById('identificationNumber').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });
        
        // Processar pagamento
        document.getElementById('form-pagamento').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btnPagar = document.getElementById('btn-pagar');
            const statusDiv = document.getElementById('status-pagamento');
            const statusText = document.getElementById('status-text');
            
            btnPagar.disabled = true;
            btnPagar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            statusDiv.style.display = 'block';
            statusDiv.className = 'status-pagamento status-pendente';
            statusText.textContent = 'Processando pagamento...';
            
            try {
                // Criar token do cartão
                const cardFormData = {
                    cardNumber: document.getElementById('cardNumber').value.replace(/\s/g, ''),
                    cardholderName: document.getElementById('cardholderName').value,
                    expirationMonth: document.getElementById('expirationMonth').value,
                    expirationYear: document.getElementById('expirationYear').value,
                    securityCode: document.getElementById('securityCode').value,
                    identificationType: 'CPF',
                    identificationNumber: document.getElementById('identificationNumber').value.replace(/\D/g, '')
                };
                
                const token = await mp.createCardToken(cardFormData);
                
                // Enviar para processamento
                const response = await fetch('processar_cartao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        doacao_id: <?= $doacao_id ?>,
                        token: token.id,
                        installments: document.getElementById('installments').value,
                        payment_method_id: 'master' // Será detectado automaticamente
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusDiv.className = 'status-pagamento status-aprovado';
                    statusText.innerHTML = '<i class="fas fa-check-circle"></i> Pagamento aprovado! Redirecionando...';
                    
                    setTimeout(() => {
                        window.location.href = 'detalhes_campanha.php?id=<?= $doacao['campanha_id'] ?>?sucesso=1';
                    }, 2000);
                } else {
                    throw new Error(result.error || 'Erro ao processar pagamento');
                }
                
            } catch (error) {
                statusDiv.className = 'status-pagamento status-erro';
                statusText.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erro: ' + error.message;
                btnPagar.disabled = false;
                btnPagar.innerHTML = '<i class="fas fa-lock"></i> Tentar Novamente';
            }
        });
    </script>
</body>
</html> 