<?php
// Carregar configuração do WhatsApp
$whatsapp = '';
foreach ($pdo->query('SELECT valor FROM textos WHERE chave = "whatsapp"')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $whatsapp = $t['valor'];
    break;
}

if ($whatsapp):
    $whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp);
?>
<!-- WhatsApp Flutuante -->
<div class="whatsapp-float">
    <a href="https://wa.me/<?= $whatsapp_clean ?>" target="_blank" class="whatsapp-btn" aria-label="Fale conosco no WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-pulse"></span>
    </a>
</div>

<style>
.whatsapp-float {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.whatsapp-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background: #25d366;
    color: white;
    border-radius: 50%;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
    transition: all 0.3s ease;
    position: relative;
    animation: whatsapp-bounce 2s infinite;
}

.whatsapp-btn:hover {
    background: #128c7e;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(37, 211, 102, 0.6);
}

.whatsapp-btn i {
    font-size: 28px;
    z-index: 2;
    position: relative;
}

.whatsapp-pulse {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 50%;
    background: #25d366;
    animation: whatsapp-pulse 2s infinite;
    z-index: 1;
}

@keyframes whatsapp-bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

@keyframes whatsapp-pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

/* Tooltip */
.whatsapp-btn::before {
    content: "Fale conosco no WhatsApp";
    position: absolute;
    right: 70px;
    top: 50%;
    transform: translateY(-50%);
    background: #333;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    pointer-events: none;
}

.whatsapp-btn::after {
    content: "";
    position: absolute;
    right: 60px;
    top: 50%;
    transform: translateY(-50%);
    border: 5px solid transparent;
    border-left-color: #333;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    pointer-events: none;
}

.whatsapp-btn:hover::before,
.whatsapp-btn:hover::after {
    opacity: 1;
    visibility: visible;
}

/* Responsividade */
@media (max-width: 768px) {
    .whatsapp-float {
        bottom: 15px;
        right: 15px;
    }
    
    .whatsapp-btn {
        width: 50px;
        height: 50px;
    }
    
    .whatsapp-btn i {
        font-size: 24px;
    }
    
    .whatsapp-btn::before {
        display: none;
    }
    
    .whatsapp-btn::after {
        display: none;
    }
}

/* Reduzir animação para usuários que preferem menos movimento */
@media (prefers-reduced-motion: reduce) {
    .whatsapp-btn {
        animation: none;
    }
    
    .whatsapp-pulse {
        animation: none;
    }
}
</style>
<?php endif; ?> 