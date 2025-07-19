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
    <title>Política de Privacidade - <?= htmlspecialchars($site_nome) ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Política de privacidade e proteção de dados da plataforma <?= htmlspecialchars($site_nome) ?>">
    <meta name="keywords" content="privacidade, proteção de dados, LGPD, vaquinha, crowdfunding">
    <meta name="author" content="<?= htmlspecialchars($site_nome) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Política de Privacidade - <?= htmlspecialchars($site_nome) ?>">
    <meta property="og:description" content="Política de privacidade e proteção de dados da plataforma">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Política de Privacidade - <?= htmlspecialchars($site_nome) ?>">
    <meta name="twitter:description" content="Política de privacidade e proteção de dados da plataforma">
    
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
        
        .legal-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        
        .legal-content h2 {
            color: var(--cor-primaria);
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--cor-secundaria);
            padding-bottom: 0.5rem;
        }
        
        .legal-content h3 {
            color: var(--cor-secundaria);
            font-weight: 600;
            margin: 2rem 0 1rem 0;
        }
        
        .legal-content p {
            margin-bottom: 1rem;
            text-align: justify;
        }
        
        .legal-content ul {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        
        .legal-content li {
            margin-bottom: 0.5rem;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, var(--cor-terciaria), #f8f9fa);
            border-left: 4px solid var(--cor-primaria);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
        }
        
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #f39c12;
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
        }
        
        .info-box {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-left: 4px solid #17a2b8;
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
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
            
            .legal-content {
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
            <h1><i class="fas fa-shield-alt"></i> Política de Privacidade</h1>
            <p>Como protegemos e utilizamos seus dados pessoais</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="legal-content">
                <h2>1. Introdução</h2>
                <p>A <?= htmlspecialchars($site_nome) ?> está comprometida em proteger sua privacidade e garantir a segurança de seus dados pessoais. Esta Política de Privacidade explica como coletamos, utilizamos, armazenamos e protegemos suas informações quando você utiliza nossa plataforma.</p>
                
                <div class="highlight-box">
                    <strong>Conformidade com a LGPD:</strong> Nossa política está em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018) e outras legislações aplicáveis de proteção de dados.
                </div>

                <h2>2. Dados que Coletamos</h2>
                <h3>2.1 Dados Pessoais</h3>
                <p>Coletamos os seguintes dados pessoais quando você utiliza nossa plataforma:</p>
                <ul>
                    <li><strong>Informações de identificação:</strong> Nome completo, CPF, data de nascimento</li>
                    <li><strong>Informações de contato:</strong> Email, telefone, endereço</li>
                    <li><strong>Informações de conta:</strong> Nome de usuário, senha (criptografada)</li>
                    <li><strong>Informações de perfil:</strong> Foto de perfil, biografia</li>
                    <li><strong>Informações financeiras:</strong> Dados de pagamento (processados por terceiros seguros)</li>
                </ul>

                <h3>2.2 Dados de Uso</h3>
                <p>Também coletamos dados sobre como você utiliza nossa plataforma:</p>
                <ul>
                    <li>Páginas visitadas e tempo de permanência</li>
                    <li>Campanhas visualizadas e interações</li>
                    <li>Doações realizadas e valores</li>
                    <li>Dispositivo e navegador utilizados</li>
                    <li>Endereço IP e localização aproximada</li>
                </ul>

                <h3>2.3 Cookies e Tecnologias Similares</h3>
                <p>Utilizamos cookies e tecnologias similares para:</p>
                <ul>
                    <li>Manter sua sessão ativa</li>
                    <li>Lembrar suas preferências</li>
                    <li>Analisar o uso da plataforma</li>
                    <li>Personalizar sua experiência</li>
                    <li>Melhorar nossos serviços</li>
                </ul>

                <h2>3. Como Utilizamos Seus Dados</h2>
                <h3>3.1 Finalidades Principais</h3>
                <p>Utilizamos seus dados para:</p>
                <ul>
                    <li>Fornecer e manter nossos serviços</li>
                    <li>Processar doações e transações</li>
                    <li>Gerenciar sua conta e perfil</li>
                    <li>Comunicar-se com você sobre sua conta</li>
                    <li>Enviar notificações importantes</li>
                    <li>Melhorar nossos serviços</li>
                    <li>Cumprir obrigações legais</li>
                </ul>

                <h3>3.2 Finalidades Secundárias</h3>
                <p>Com seu consentimento, também podemos utilizar seus dados para:</p>
                <ul>
                    <li>Enviar newsletters e comunicações promocionais</li>
                    <li>Personalizar conteúdo e recomendações</li>
                    <li>Conduzir pesquisas e análises</li>
                    <li>Desenvolver novos recursos</li>
                </ul>

                <h2>4. Compartilhamento de Dados</h2>
                <h3>4.1 Quando Compartilhamos</h3>
                <p>Podemos compartilhar seus dados nas seguintes situações:</p>
                <ul>
                    <li><strong>Processadores de pagamento:</strong> Para processar doações</li>
                    <li><strong>Prestadores de serviços:</strong> Para fornecer funcionalidades da plataforma</li>
                    <li><strong>Autoridades legais:</strong> Quando exigido por lei</li>
                    <li><strong>Proteção de direitos:</strong> Para proteger nossos direitos e segurança</li>
                    <li><strong>Com seu consentimento:</strong> Em outras situações com sua autorização</li>
                </ul>

                <h3>4.2 O que NÃO Compartilhamos</h3>
                <p>Não vendemos, alugamos ou comercializamos seus dados pessoais com terceiros para fins comerciais.</p>

                <h2>5. Segurança dos Dados</h2>
                <p>Implementamos medidas de segurança técnicas e organizacionais para proteger seus dados:</p>
                <ul>
                    <li>Criptografia de dados em trânsito e em repouso</li>
                    <li>Controle de acesso rigoroso</li>
                    <li>Monitoramento contínuo de segurança</li>
                    <li>Backups regulares e seguros</li>
                    <li>Treinamento da equipe em segurança</li>
                    <li>Auditorias regulares de segurança</li>
                </ul>

                <div class="warning-box">
                    <strong>Atenção:</strong> Embora implementemos medidas de segurança robustas, nenhum sistema é 100% seguro. Recomendamos que você também tome medidas para proteger suas informações, como usar senhas fortes e não compartilhar suas credenciais.
                </div>

                <h2>6. Seus Direitos</h2>
                <p>Conforme a LGPD, você tem os seguintes direitos:</p>
                <ul>
                    <li><strong>Acesso:</strong> Solicitar informações sobre seus dados</li>
                    <li><strong>Correção:</strong> Solicitar correção de dados incorretos</li>
                    <li><strong>Exclusão:</strong> Solicitar exclusão de seus dados</li>
                    <li><strong>Portabilidade:</strong> Solicitar transferência de seus dados</li>
                    <li><strong>Revogação:</strong> Revogar consentimentos dados</li>
                    <li><strong>Oposição:</strong> Opor-se ao tratamento de dados</li>
                    <li><strong>Revisão:</strong> Solicitar revisão de decisões automatizadas</li>
                </ul>

                <h2>7. Retenção de Dados</h2>
                <p>Mantemos seus dados pelo tempo necessário para:</p>
                <ul>
                    <li>Fornecer nossos serviços</li>
                    <li>Cumprir obrigações legais</li>
                    <li>Resolver disputas</li>
                    <li>Fazer cumprir nossos termos</li>
                </ul>
                <p>Quando não for mais necessário, seus dados serão excluídos ou anonimizados de forma segura.</p>

                <h2>8. Transferências Internacionais</h2>
                <p>Seus dados podem ser transferidos para outros países quando:</p>
                <ul>
                    <li>Utilizamos serviços de terceiros baseados no exterior</li>
                    <li>É necessário para fornecer nossos serviços</li>
                    <li>Existe adequação de proteção de dados</li>
                </ul>
                <p>Garantimos que tais transferências sejam feitas em conformidade com a LGPD.</p>

                <h2>9. Dados de Menores</h2>
                <p>Nossa plataforma não é destinada a menores de 18 anos. Não coletamos intencionalmente dados de menores sem o consentimento dos pais ou responsáveis legais.</p>

                <h2>10. Cookies e Tecnologias de Rastreamento</h2>
                <h3>10.1 Tipos de Cookies</h3>
                <ul>
                    <li><strong>Cookies essenciais:</strong> Necessários para o funcionamento da plataforma</li>
                    <li><strong>Cookies de funcionalidade:</strong> Para lembrar suas preferências</li>
                    <li><strong>Cookies de análise:</strong> Para entender como você usa a plataforma</li>
                    <li><strong>Cookies de marketing:</strong> Para personalizar anúncios (com consentimento)</li>
                </ul>

                <h3>10.2 Gerenciamento de Cookies</h3>
                <p>Você pode gerenciar suas preferências de cookies através das configurações do seu navegador ou através de nossa ferramenta de consentimento.</p>

                <h2>11. Alterações na Política</h2>
                <p>Podemos atualizar esta política periodicamente. Notificaremos você sobre mudanças significativas através de:</p>
                <ul>
                    <li>Email para sua conta registrada</li>
                    <li>Notificação na plataforma</li>
                    <li>Atualização da data de "última modificação"</li>
                </ul>

                <h2>12. Contato</h2>
                <p>Para exercer seus direitos ou esclarecer dúvidas sobre esta política:</p>
                <ul>
                    <li><strong>Email:</strong> <?= htmlspecialchars($site_email) ?></li>
                    <li><strong>Formulário:</strong> <a href="contato.php">Página de Contato</a></li>
                    <li><strong>Endereço:</strong> São Paulo, SP - Brasil</li>
                </ul>

                <div class="info-box">
                    <strong>Encarregado de Proteção de Dados (DPO):</strong><br>
                    Se você tiver dúvidas específicas sobre proteção de dados, pode entrar em contato diretamente com nosso DPO através do email: dpo@<?= strtolower(str_replace(' ', '', $site_nome)) ?>.com
                </div>

                <div class="highlight-box">
                    <strong>Última atualização:</strong> <?= date('d/m/Y') ?><br>
                    <strong>Versão:</strong> 1.0<br>
                    <strong>Conformidade:</strong> LGPD (Lei nº 13.709/2018)
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