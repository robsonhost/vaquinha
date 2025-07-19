<?php
session_start();
require 'admin/db.php';

// Carregar configurações do site
$config = $pdo->query('SELECT * FROM configuracoes LIMIT 1')->fetch(PDO::FETCH_ASSOC);
$site_nome = $config['nome_site'] ?? 'Vaquinha Online';
$site_email = $config['email_contato'] ?? 'contato@vaquinha.com';

// Carregar tema ativo
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Perguntas Frequentes - <?= htmlspecialchars($site_nome) ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Perguntas frequentes sobre a plataforma <?= htmlspecialchars($site_nome) ?> - Tire suas dúvidas sobre crowdfunding">
    <meta name="keywords" content="FAQ, perguntas frequentes, dúvidas, vaquinha, crowdfunding, como funciona">
    <meta name="author" content="<?= htmlspecialchars($site_nome) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="FAQ - Perguntas Frequentes - <?= htmlspecialchars($site_nome) ?>">
    <meta property="og:description" content="Tire suas dúvidas sobre crowdfunding e como usar nossa plataforma">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="FAQ - Perguntas Frequentes - <?= htmlspecialchars($site_nome) ?>">
    <meta name="twitter:description" content="Tire suas dúvidas sobre crowdfunding e como usar nossa plataforma">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --cor-primaria: <?= $tema['cor_primaria'] ?>;
            --cor-secundaria: <?= $tema['cor_secundaria'] ?>;
            --cor-terciaria: <?= $tema['cor_terciaria'] ?>;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .content-section {
            padding: 60px 0;
            background: white;
        }
        
        .faq-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        
        .faq-category {
            margin-bottom: 3rem;
        }
        
        .faq-category h2 {
            color: var(--cor-primaria);
            font-weight: 600;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--cor-secundaria);
            padding-bottom: 0.5rem;
        }
        
        .accordion-item {
            border: none;
            margin-bottom: 1rem;
            border-radius: 10px !important;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .accordion-button {
            background: linear-gradient(135deg, var(--cor-terciaria), #f8f9fa);
            border: none;
            font-weight: 600;
            color: var(--cor-primaria);
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            box-shadow: none;
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border: none;
        }
        
        .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23currentColor'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }
        
        .accordion-body {
            padding: 1.5rem;
            background: white;
            color: #666;
            line-height: 1.8;
        }
        
        .search-box {
            background: linear-gradient(135deg, var(--cor-terciaria), #f8f9fa);
            border: 2px solid var(--cor-primaria);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 3rem;
            text-align: center;
        }
        
        .search-box h3 {
            color: var(--cor-primaria);
            margin-bottom: 1rem;
        }
        
        .search-input {
            border: 2px solid #e9ecef;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: var(--cor-primaria);
            box-shadow: 0 0 0 0.2rem rgba(120, 47, 155, 0.25);
        }
        
        .contact-box {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 3rem;
            text-align: center;
        }
        
        .contact-box h3 {
            margin-bottom: 1rem;
        }
        
        .contact-box p {
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }
        
        .btn-contact {
            background: white;
            color: var(--cor-primaria);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-contact:hover {
            background: var(--cor-terciaria);
            color: var(--cor-primaria);
            transform: translateY(-2px);
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0 20px;
        }
        
        .footer h5 {
            color: var(--cor-secundaria);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .footer a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer a:hover {
            color: var(--cor-secundaria);
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--cor-primaria);
            color: white;
            text-align: center;
            line-height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--cor-secundaria);
            transform: translateY(-2px);
        }
        
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0,0,0,0.3);
        }
        
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-section p {
                font-size: 1rem;
            }
            
            .faq-container {
                padding: 20px;
                margin: 20px;
            }
            
            .back-to-top {
                bottom: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-heart"></i> <?= htmlspecialchars($site_nome) ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="como-funciona.php">Como Funciona</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contato.php">Contato</a>
                    </li>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="area_usuario.php">Minha Conta</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout_usuario.php">Sair</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Entrar</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro.php">Cadastrar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1><i class="fas fa-question-circle"></i> Perguntas Frequentes</h1>
            <p>Tire suas dúvidas sobre nossa plataforma de crowdfunding</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="faq-container">
                <!-- Search Box -->
                <div class="search-box">
                    <h3><i class="fas fa-search"></i> Encontre sua resposta</h3>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <input type="text" class="form-control search-input" id="searchFAQ" placeholder="Digite sua pergunta ou palavra-chave...">
                        </div>
                    </div>
                </div>

                <!-- FAQ Categories -->
                <div class="faq-category">
                    <h2><i class="fas fa-users"></i> Sobre a Plataforma</h2>
                    <div class="accordion" id="accordionPlataforma">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    O que é o <?= htmlspecialchars($site_nome) ?>?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#accordionPlataforma">
                                <div class="accordion-body">
                                    O <?= htmlspecialchars($site_nome) ?> é uma plataforma de crowdfunding (vaquinha online) que conecta pessoas que precisam de ajuda financeira com aquelas que querem ajudar. Nossa missão é facilitar a solidariedade e permitir que projetos, causas e sonhos se tornem realidade através de doações coletivas.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    A plataforma é segura?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionPlataforma">
                                <div class="accordion-body">
                                    Sim! Utilizamos as mais avançadas tecnologias de segurança para proteger seus dados e transações. Todas as doações são processadas por gateways de pagamento certificados e seguros. Além disso, seguimos rigorosamente a LGPD (Lei Geral de Proteção de Dados) para garantir a privacidade de suas informações.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Quais são as taxas cobradas?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionPlataforma">
                                <div class="accordion-body">
                                    Cobramos uma taxa de 5% sobre o valor total arrecadado pela campanha. Além disso, há uma taxa de processamento de pagamento de 2.9% + R$ 0,30 por doação, cobrada pelo processador de pagamento. Essas taxas nos permitem manter a plataforma funcionando e oferecer suporte aos usuários.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <h2><i class="fas fa-plus-circle"></i> Criando Campanhas</h2>
                    <div class="accordion" id="accordionCriar">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Como criar uma campanha?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse show" data-bs-parent="#accordionCriar">
                                <div class="accordion-body">
                                    Para criar uma campanha, siga estes passos:<br>
                                    1. Faça login ou crie uma conta<br>
                                    2. Clique em "Nova Campanha"<br>
                                    3. Preencha todas as informações solicitadas (título, descrição, meta, categoria, etc.)<br>
                                    4. Adicione fotos e vídeos para tornar sua campanha mais atrativa<br>
                                    5. Revise todas as informações e publique<br>
                                    6. Aguarde a aprovação da nossa equipe (geralmente em até 24h)
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Quais tipos de campanhas posso criar?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#accordionCriar">
                                <div class="accordion-body">
                                    Você pode criar campanhas para diversos fins, incluindo:<br>
                                    • Tratamentos médicos e cirurgias<br>
                                    • Educação e cursos<br>
                                    • Projetos sociais e comunitários<br>
                                    • Empreendedorismo e negócios<br>
                                    • Eventos e celebrações<br>
                                    • Ajuda emergencial<br>
                                    • Projetos culturais e artísticos<br>
                                    • Causas ambientais e animais<br><br>
                                    <strong>Importante:</strong> Todas as campanhas devem ser legais e éticas.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    Quanto tempo leva para a campanha ser aprovada?
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#accordionCriar">
                                <div class="accordion-body">
                                    Nossa equipe analisa todas as campanhas em até 24 horas úteis. Campanhas bem documentadas e com informações claras são aprovadas mais rapidamente. Se sua campanha não for aprovada, você receberá um email explicando o motivo e como pode corrigir.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                    Posso editar minha campanha depois de publicada?
                                </button>
                            </h2>
                            <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#accordionCriar">
                                <div class="accordion-body">
                                    Sim! Você pode editar sua campanha a qualquer momento através da sua área do usuário. As alterações incluem título, descrição, meta, fotos e vídeos. Lembre-se de que mudanças significativas podem precisar de nova aprovação da nossa equipe.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <h2><i class="fas fa-hand-holding-heart"></i> Fazendo Doações</h2>
                    <div class="accordion" id="accordionDoar">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                    Como fazer uma doação?
                                </button>
                            </h2>
                            <div id="faq8" class="accordion-collapse collapse show" data-bs-parent="#accordionDoar">
                                <div class="accordion-body">
                                    Para fazer uma doação:<br>
                                    1. Escolha a campanha que deseja ajudar<br>
                                    2. Clique em "Fazer Doação"<br>
                                    3. Digite o valor que deseja doar<br>
                                    4. Escolha o método de pagamento (PIX, cartão de crédito/débito, boleto)<br>
                                    5. Preencha os dados solicitados<br>
                                    6. Confirme a doação<br>
                                    7. Aguarde a confirmação do pagamento
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                    Quais formas de pagamento são aceitas?
                                </button>
                            </h2>
                            <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#accordionDoar">
                                <div class="accordion-body">
                                    Aceitamos as seguintes formas de pagamento:<br>
                                    • <strong>PIX:</strong> Transferência instantânea (recomendado)<br>
                                    • <strong>Cartão de Crédito:</strong> Todas as bandeiras principais<br>
                                    • <strong>Cartão de Débito:</strong> Todas as bandeiras principais<br>
                                    • <strong>Boleto Bancário:</strong> Pagamento em até 3 dias úteis<br><br>
                                    Todas as transações são processadas por empresas certificadas e seguras.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                    Posso fazer doações anônimas?
                                </button>
                            </h2>
                            <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#accordionDoar">
                                <div class="accordion-body">
                                    Sim! Você pode escolher fazer uma doação anônima. Nesse caso, seu nome não aparecerá na lista de doadores da campanha, mas você ainda receberá um comprovante da doação por email. O criador da campanha saberá que recebeu uma doação, mas não saberá de quem.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq11">
                                    Recebo comprovante da doação?
                                </button>
                            </h2>
                            <div id="faq11" class="accordion-collapse collapse" data-bs-parent="#accordionDoar">
                                <div class="accordion-body">
                                    Sim! Você receberá um comprovante por email imediatamente após a confirmação do pagamento. O comprovante inclui todas as informações da doação e pode ser usado para fins fiscais ou de controle pessoal.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <h2><i class="fas fa-money-bill-wave"></i> Pagamentos e Reembolsos</h2>
                    <div class="accordion" id="accordionPagamento">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq12">
                                    Quando o dinheiro chega ao criador da campanha?
                                </button>
                            </h2>
                            <div id="faq12" class="accordion-collapse collapse show" data-bs-parent="#accordionPagamento">
                                <div class="accordion-body">
                                    O dinheiro é transferido para o criador da campanha em até 7 dias úteis após o término da campanha. Para campanhas que atingem a meta antes do prazo, o criador pode solicitar o saque antecipado através da nossa equipe de suporte.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq13">
                                    Posso solicitar reembolso?
                                </button>
                            </h2>
                            <div id="faq13" class="accordion-collapse collapse" data-bs-parent="#accordionPagamento">
                                <div class="accordion-body">
                                    Reembolsos são considerados apenas em casos específicos:<br>
                                    • Erro no processamento do pagamento<br>
                                    • Campanha cancelada antes da confirmação<br>
                                    • Problemas técnicos da plataforma<br><br>
                                    Para solicitar um reembolso, entre em contato conosco através do email <?= htmlspecialchars($site_email) ?> explicando o motivo da solicitação.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq14">
                                    E se a campanha não atingir a meta?
                                </button>
                            </h2>
                            <div id="faq14" class="accordion-collapse collapse" data-bs-parent="#accordionPagamento">
                                <div class="accordion-body">
                                    Se a campanha não atingir a meta no prazo estabelecido, o dinheiro arrecadado é devolvido aos doadores automaticamente. O criador da campanha não recebe nenhum valor, e os doadores recebem o reembolso em até 10 dias úteis.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <h2><i class="fas fa-shield-alt"></i> Segurança e Privacidade</h2>
                    <div class="accordion" id="accordionSeguranca">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq15">
                                    Meus dados estão seguros?
                                </button>
                            </h2>
                            <div id="faq15" class="accordion-collapse collapse show" data-bs-parent="#accordionSeguranca">
                                <div class="accordion-body">
                                    Absolutamente! Utilizamos as mais avançadas tecnologias de segurança:<br>
                                    • Criptografia SSL/TLS para todas as transações<br>
                                    • Dados pessoais criptografados<br>
                                    • Conformidade com a LGPD<br>
                                    • Processadores de pagamento certificados<br>
                                    • Monitoramento 24/7 de segurança<br>
                                    • Backups regulares e seguros
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq16">
                                    Como verifico se uma campanha é legítima?
                                </button>
                            </h2>
                            <div id="faq16" class="accordion-collapse collapse" data-bs-parent="#accordionSeguranca">
                                <div class="accordion-body">
                                    Para verificar a legitimidade de uma campanha:<br>
                                    • Leia atentamente a descrição e documentos<br>
                                    • Verifique as fotos e vídeos fornecidos<br>
                                    • Entre em contato com o criador através da plataforma<br>
                                    • Verifique o histórico do criador na plataforma<br>
                                    • Se houver dúvidas, denuncie através do botão "Reportar"<br><br>
                                    Nossa equipe analisa todas as campanhas antes da aprovação.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Box -->
                <div class="contact-box">
                    <h3><i class="fas fa-headset"></i> Não encontrou sua resposta?</h3>
                    <p>Nossa equipe está pronta para ajudar você com qualquer dúvida adicional.</p>
                    <a href="contato.php" class="btn btn-contact">
                        <i class="fas fa-envelope"></i> Entre em Contato
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-heart"></i> <?= htmlspecialchars($site_nome) ?></h5>
                    <p>Conectando pessoas através da solidariedade. Faça a diferença hoje mesmo!</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Links Úteis</h5>
                    <ul class="list-unstyled">
                        <li><a href="como-funciona.php">Como Funciona</a></li>
                        <li><a href="categorias.php">Categorias</a></li>
                        <li><a href="contato.php">Contato</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="termos.php">Termos de Uso</a></li>
                        <li><a href="privacidade.php">Política de Privacidade</a></li>
                        <li><a href="seguranca.php">Segurança</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contato</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope"></i> <?= htmlspecialchars($site_email) ?></li>
                        <li><i class="fas fa-phone"></i> (11) 99999-9999</li>
                        <li><i class="fas fa-map-marker-alt"></i> São Paulo, SP</li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-3">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($site_nome) ?>. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Feito com <i class="fas fa-heart text-danger"></i> para você</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" onclick="scrollToTop()" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchFAQ').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const accordionItems = document.querySelectorAll('.accordion-item');
            
            accordionItems.forEach(item => {
                const question = item.querySelector('.accordion-button').textContent.toLowerCase();
                const answer = item.querySelector('.accordion-body').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    if (searchTerm && !item.querySelector('.accordion-collapse').classList.contains('show')) {
                        item.querySelector('.accordion-button').click();
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Back to top functionality
        window.onscroll = function() {
            const backToTop = document.getElementById('backToTop');
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        };

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html> 