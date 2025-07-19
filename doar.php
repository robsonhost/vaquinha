<?php
require 'admin/db.php';
require 'includes/mercado_pago.php';
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM campanhas WHERE id = ?');
$stmt->execute([$id]);
$campanha = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$campanha) {
    echo 'Campanha não encontrada!';
    exit;
}
$erro = $sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = floatval($_POST['valor'] ?? 0);
    $mensagem = trim($_POST['mensagem'] ?? '');
    $metodo_pagamento = $_POST['metodo_pagamento'] ?? 'pix';
    
    if ($valor <= 0) {
        $erro = 'Valor inválido!';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Criar doação no banco
            $stmt = $pdo->prepare('INSERT INTO doacoes (valor, mensagem, metodo_pagamento, status, usuario_id, campanha_id, criado_em) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$valor, $mensagem, $metodo_pagamento, 'pendente', $_SESSION['usuario_id'] ?? null, $campanha['id']]);
            $doacao_id = $pdo->lastInsertId();
            
            if ($metodo_pagamento === 'pix') {
                // Criar pagamento PIX no Mercado Pago
                $descricao = "Doação para campanha: {$campanha['titulo']}";
                $pagamento = criar_pagamento_mercado_pago($valor, $descricao, $doacao_id, 'pix');
                
                // Atualizar doação com ID do pagamento
                $stmt = $pdo->prepare('UPDATE doacoes SET payment_id = ? WHERE id = ?');
                $stmt->execute([$pagamento['id'], $doacao_id]);
                
                $pdo->commit();
                
                // Redirecionar para página de pagamento PIX
                header("Location: pagamento.php?doacao_id={$doacao_id}");
                exit;
            } else {
                // Para cartão, apenas salvar a doação e redirecionar para formulário de cartão
                $pdo->commit();
                header("Location: pagamento_cartao.php?doacao_id={$doacao_id}");
                exit;
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = 'Erro ao processar doação: ' . $e->getMessage();
        }
    }
}
?>
<?php include '_header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="mb-4 text-center">Doar para: <?= htmlspecialchars($campanha['titulo']) ?></h2>
                    <?php if ($erro): ?><div class="alert alert-danger"> <?= $erro ?> </div><?php endif; ?>
                    <?php if ($sucesso): ?><div class="alert alert-success"> <?= $sucesso ?> </div><?php endif; ?>
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label>Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                            <div class="invalid-feedback">Informe seu nome.</div>
                        </div>
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" class="form-control" required>
                            <div class="invalid-feedback">Informe um e-mail válido.</div>
                        </div>
                        <div class="form-group">
                            <label>Valor (R$)</label>
                            <input type="number" name="valor" class="form-control" step="0.01" min="1" required>
                            <div class="invalid-feedback">Informe um valor válido.</div>
                        </div>
                        <div class="form-group">
                            <label>Método de Pagamento</label>
                            <select name="metodo_pagamento" class="form-control" required onchange="toggleMetodoPagamento(this.value)">
                                <option value="pix">PIX - Pagamento Instantâneo</option>
                                <option value="cartao_debito">Cartão de Débito</option>
                                <option value="cartao_credito">Cartão de Crédito</option>
                            </select>
                        </div>
                        
                        <!-- Opções específicas para cartão -->
                        <div id="opcoes-cartao" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Parcelas</label>
                                        <select name="parcelas" class="form-control" id="parcelas-select">
                                            <option value="1">1x sem juros</option>
                                            <option value="2">2x sem juros</option>
                                            <option value="3">3x sem juros</option>
                                            <option value="4">4x sem juros</option>
                                            <option value="5">5x sem juros</option>
                                            <option value="6">6x sem juros</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>CPF do Titular</label>
                                        <input type="text" name="cpf_titular" class="form-control" placeholder="000.000.000-00" maxlength="14">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Mensagem (opcional)</label>
                            <input type="text" name="mensagem" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success btn-block">Doar</button>
                        <a href="detalhes_campanha.php?id=<?= $campanha['id'] ?>" class="btn btn-link btn-block">Voltar</a>
                    </form>
                    <div class="alert alert-info mt-4"><b>Pix/Cartão:</b> Envie o comprovante para agilizar a confirmação. Integração real pode ser feita com APIs de pagamento (PagSeguro, MercadoPago, Stripe, etc).</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Validação Bootstrap
(function() {
  'use strict';
  window.addEventListener('load', function() {
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);
})();

// Função para mostrar/ocultar opções de cartão
function toggleMetodoPagamento(metodo) {
    const opcoesCartao = document.getElementById('opcoes-cartao');
    const parcelasSelect = document.getElementById('parcelas-select');
    
    if (metodo === 'cartao_debito' || metodo === 'cartao_credito') {
        opcoesCartao.style.display = 'block';
        
        // Para débito, apenas 1x
        if (metodo === 'cartao_debito') {
            parcelasSelect.innerHTML = '<option value="1">1x sem juros</option>';
        } else {
            // Para crédito, até 6x
            parcelasSelect.innerHTML = `
                <option value="1">1x sem juros</option>
                <option value="2">2x sem juros</option>
                <option value="3">3x sem juros</option>
                <option value="4">4x sem juros</option>
                <option value="5">5x sem juros</option>
                <option value="6">6x sem juros</option>
            `;
        }
    } else {
        opcoesCartao.style.display = 'none';
    }
}

// Máscara para CPF
document.addEventListener('DOMContentLoaded', function() {
    const cpfInput = document.querySelector('input[name="cpf_titular"]');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });
    }
});
</script>
</body>
</html> 