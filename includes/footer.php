<?php
// Carregar configurações
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

// Carregar redes sociais
$redesSociais = $pdo->query('SELECT * FROM redes_sociais WHERE ativo = 1 ORDER BY ordem')->fetchAll(PDO::FETCH_ASSOC);
?>
<footer class="footer bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <!-- Coluna 1: Sobre -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="footer-brand mb-3">
                    <?php if ($logo && $logo['caminho']): ?>
                        <img src="<?= htmlspecialchars($logo['caminho']) ?>" alt="<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>" class="footer-logo mb-2" loading="lazy">
                    <?php else: ?>
                        <i class="fas fa-heart text-primary fa-2x mb-2"></i>
                    <?php endif; ?>
                    <h5 class="fw-bold"><?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?></h5>
                </div>
                <p class="text-muted mb-3">
                    <?= htmlspecialchars($textos['descricao_site'] ?? 'Plataforma de vaquinhas online para ajudar pessoas. Conectamos quem precisa de ajuda com quem quer ajudar.') ?>
                </p>
                <div class="social-links mb-2" aria-label="Redes sociais">
                    <?php foreach ($redesSociais as $rede): ?>
                        <a href="<?= htmlspecialchars($rede['url']) ?>" target="_blank" class="social-link" title="<?= htmlspecialchars($rede['nome']) ?>" rel="noopener">
                            <i class="<?= htmlspecialchars($rede['icone']) ?>"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
                <!-- Botão WhatsApp -->
                <?php if ($textos['whatsapp']): ?>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $textos['whatsapp']) ?>" target="_blank" class="btn btn-success btn-sm mb-2" aria-label="Fale conosco pelo WhatsApp">
                    <i class="fab fa-whatsapp"></i> Fale conosco
                </a>
                <?php endif; ?>
                <!-- Selo Mercado Pago -->
                <div class="mt-2">
                    <img src="https://www.mercadopago.com/org-img/MP3/API/logos/mp_logo_128.png" alt="Mercado Pago" style="max-height:32px;" loading="lazy">
                </div>
            </div>
            <!-- Coluna 2: Links Rápidos -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Links Rápidos</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="index.php" class="text-muted text-decoration-none"><i class="fas fa-home me-2"></i>Início</a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php#campanhas" class="text-muted text-decoration-none"><i class="fas fa-hand-holding-heart me-2"></i>Vaquinhas</a>
                    </li>
                    <li class="mb-2">
                        <a href="nova_campanha_usuario.php" class="text-muted text-decoration-none"><i class="fas fa-plus me-2"></i>Criar Campanha</a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php#categorias" class="text-muted text-decoration-none"><i class="fas fa-tags me-2"></i>Categorias</a>
                    </li>
                    <li class="mb-2">
                        <a href="index.php#como-funciona" class="text-muted text-decoration-none"><i class="fas fa-question-circle me-2"></i>Como Funciona</a>
                    </li>
                    <li class="mb-2">
                        <a href="contato.php" class="text-muted text-decoration-none"><i class="fas fa-envelope me-2"></i>Fale Conosco</a>
                    </li>
                </ul>
            </div>
            <!-- Coluna 3: Suporte -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Suporte</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="faq.php" class="text-muted text-decoration-none"><i class="fas fa-question me-2"></i>Perguntas Frequentes</a></li>
                    <li class="mb-2"><a href="termos.php" class="text-muted text-decoration-none"><i class="fas fa-file-contract me-2"></i>Termos de Uso</a></li>
                    <li class="mb-2"><a href="privacidade.php" class="text-muted text-decoration-none"><i class="fas fa-shield-alt me-2"></i>Política de Privacidade</a></li>
                    <li class="mb-2"><a href="seguranca.php" class="text-muted text-decoration-none"><i class="fas fa-lock me-2"></i>Segurança</a></li>
                    <li class="mb-2"><a href="central-ajuda.php" class="text-muted text-decoration-none"><i class="fas fa-life-ring me-2"></i>Central de Ajuda</a></li>
                </ul>
            </div>
            <!-- Coluna 4: Contato -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="fw-bold mb-3">Contato</h6>
                <ul class="list-unstyled">
                    <?php if ($textos['whatsapp']): ?>
                        <li class="mb-3"><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $textos['whatsapp']) ?>" target="_blank" class="text-muted text-decoration-none" aria-label="WhatsApp"><i class="fab fa-whatsapp me-2 text-success"></i><?= htmlspecialchars($textos['whatsapp']) ?></a></li>
                    <?php endif; ?>
                    <?php if ($textos['telefone']): ?>
                        <li class="mb-3"><a href="tel:<?= preg_replace('/[^0-9]/', '', $textos['telefone']) ?>" class="text-muted text-decoration-none" aria-label="Telefone"><i class="fas fa-phone me-2 text-primary"></i><?= htmlspecialchars($textos['telefone']) ?></a></li>
                    <?php endif; ?>
                    <?php if ($textos['email']): ?>
                        <li class="mb-3"><a href="mailto:<?= htmlspecialchars($textos['email']) ?>" class="text-muted text-decoration-none" aria-label="E-mail"><i class="fas fa-envelope me-2 text-info"></i><?= htmlspecialchars($textos['email']) ?></a></li>
                    <?php endif; ?>
                    <?php if ($textos['endereco']): ?>
                        <li class="mb-3"><i class="fas fa-map-marker-alt me-2 text-danger"></i><span class="text-muted"><?= htmlspecialchars($textos['endereco']) ?></span></li>
                    <?php endif; ?>
                </ul>
                <?php if ($textos['horario_funcionamento']): ?>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2">Horário de Atendimento</h6>
                        <p class="text-muted small mb-0"><i class="fas fa-clock me-2"></i><?= htmlspecialchars($textos['horario_funcionamento']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <hr class="my-4 border-secondary">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>. Todos os direitos reservados.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-badges">
                    <span class="badge bg-success me-2"><i class="fas fa-shield-alt"></i> Site Seguro</span>
                    <span class="badge bg-info me-2"><i class="fas fa-lock"></i> SSL</span>
                    <span class="badge bg-warning"><i class="fas fa-star"></i> Confiável</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    background: linear-gradient(135deg, #2c3e50, #34495e) !important;
}

.footer-logo {
    max-height: 40px;
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: var(--cor-secundaria);
    color: white;
    transform: translateY(-2px);
}

.footer h6 {
    color: var(--cor-secundaria);
    font-weight: 600;
}

.footer a:hover {
    color: var(--cor-secundaria) !important;
}

.footer-badges .badge {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .footer {
        text-align: center;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .footer-badges {
        margin-top: 1rem;
    }
}
</style> 