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
    <title>Termos de Uso - <?= htmlspecialchars($site_nome) ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Termos de uso e condições para utilização da plataforma <?= htmlspecialchars($site_nome) ?>">
    <meta name="keywords" content="termos de uso, condições, vaquinha, crowdfunding, doações">
    <meta name="author" content="<?= htmlspecialchars($site_nome) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Termos de Uso - <?= htmlspecialchars($site_nome) ?>">
    <meta property="og:description" content="Termos de uso e condições para utilização da plataforma">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Termos de Uso - <?= htmlspecialchars($site_nome) ?>">
    <meta name="twitter:description" content="Termos de uso e condições para utilização da plataforma">
    
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
            <h1><i class="fas fa-gavel"></i> Termos de Uso</h1>
            <p>Conheça as condições para utilização da nossa plataforma</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="legal-content">
                <h2>1. Aceitação dos Termos</h2>
                <p>Ao acessar e utilizar a plataforma <?= htmlspecialchars($site_nome) ?>, você concorda em cumprir e estar vinculado a estes Termos de Uso. Se você não concordar com qualquer parte destes termos, não deve utilizar nossos serviços.</p>
                
                <div class="highlight-box">
                    <strong>Importante:</strong> Estes termos constituem um acordo legal entre você e a <?= htmlspecialchars($site_nome) ?>. Recomendamos que você leia atentamente todos os termos antes de utilizar a plataforma.
                </div>

                <h2>2. Definições</h2>
                <ul>
                    <li><strong>Plataforma:</strong> Refere-se ao site <?= htmlspecialchars($site_nome) ?> e todos os seus serviços relacionados</li>
                    <li><strong>Usuário:</strong> Qualquer pessoa que acesse ou utilize a plataforma</li>
                    <li><strong>Criador:</strong> Usuário que cria e gerencia campanhas de crowdfunding</li>
                    <li><strong>Doador:</strong> Usuário que faz doações para campanhas</li>
                    <li><strong>Campanha:</strong> Projeto de crowdfunding criado na plataforma</li>
                    <li><strong>Doação:</strong> Contribuição financeira feita para uma campanha</li>
                </ul>

                <h2>3. Elegibilidade</h2>
                <h3>3.1 Requisitos Gerais</h3>
                <p>Para utilizar nossos serviços, você deve:</p>
                <ul>
                    <li>Ter pelo menos 18 anos de idade ou ter autorização de um responsável legal</li>
                    <li>Fornecer informações verdadeiras e precisas durante o cadastro</li>
                    <li>Manter a confidencialidade de suas credenciais de acesso</li>
                    <li>Utilizar a plataforma de acordo com as leis aplicáveis</li>
                </ul>

                <h3>3.2 Restrições</h3>
                <p>Você não pode utilizar a plataforma para:</p>
                <ul>
                    <li>Atividades ilegais ou fraudulentas</li>
                    <li>Violar direitos de terceiros</li>
                    <li>Transmitir conteúdo ofensivo ou inadequado</li>
                    <li>Interferir no funcionamento da plataforma</li>
                </ul>

                <h2>4. Criação de Campanhas</h2>
                <h3>4.1 Responsabilidades do Criador</h3>
                <p>Ao criar uma campanha, você se compromete a:</p>
                <ul>
                    <li>Fornecer informações verdadeiras e precisas sobre o projeto</li>
                    <li>Utilizar os recursos arrecadados conforme descrito na campanha</li>
                    <li>Manter os doadores informados sobre o progresso do projeto</li>
                    <li>Cumprir todas as obrigações legais relacionadas ao projeto</li>
                </ul>

                <h3>4.2 Conteúdo das Campanhas</h3>
                <p>O conteúdo das campanhas deve:</p>
                <ul>
                    <li>Ser original ou ter autorização para uso</li>
                    <li>Não violar direitos autorais ou de propriedade intelectual</li>
                    <li>Ser apropriado para todas as idades</li>
                    <li>Não conter informações enganosas ou fraudulentas</li>
                </ul>

                <h2>5. Doações</h2>
                <h3>5.1 Processo de Doação</h3>
                <p>Ao fazer uma doação, você:</p>
                <ul>
                    <li>Confirma que possui autorização para realizar a transação</li>
                    <li>Reconhece que a doação é voluntária e não reembolsável</li>
                    <li>Concorda com as taxas e comissões aplicáveis</li>
                    <li>Autoriza o processamento da transação</li>
                </ul>

                <h3>5.2 Reembolsos</h3>
                <p>As doações são geralmente não reembolsáveis. Reembolsos podem ser considerados apenas em casos específicos, conforme nossa política de reembolso.</p>

                <h2>6. Taxas e Comissões</h2>
                <p>A plataforma cobra taxas sobre as doações processadas. As taxas incluem:</p>
                <ul>
                    <li>Taxa de processamento de pagamento (2.9% + R$ 0,30)</li>
                    <li>Comissão da plataforma (5% sobre o valor arrecadado)</li>
                    <li>Taxas adicionais podem ser aplicadas conforme o método de pagamento</li>
                </ul>

                <h2>7. Privacidade e Proteção de Dados</h2>
                <p>O tratamento de seus dados pessoais é regido por nossa <a href="privacidade.php">Política de Privacidade</a>. Ao utilizar a plataforma, você concorda com a coleta e uso de suas informações conforme descrito na política.</p>

                <h2>8. Propriedade Intelectual</h2>
                <p>Todos os direitos de propriedade intelectual relacionados à plataforma são de propriedade da <?= htmlspecialchars($site_nome) ?> ou de seus licenciadores. Você mantém os direitos sobre o conteúdo que criar, mas concede à plataforma licença para utilizá-lo conforme necessário.</p>

                <h2>9. Limitação de Responsabilidade</h2>
                <p>A <?= htmlspecialchars($site_nome) ?> não se responsabiliza por:</p>
                <ul>
                    <li>Danos indiretos ou consequenciais</li>
                    <li>Perdas decorrentes do uso ou impossibilidade de uso da plataforma</li>
                    <li>Conteúdo criado pelos usuários</li>
                    <li>Disputas entre criadores e doadores</li>
                </ul>

                <h2>10. Modificações dos Termos</h2>
                <p>Reservamo-nos o direito de modificar estes termos a qualquer momento. As modificações entrarão em vigor imediatamente após sua publicação na plataforma. O uso continuado da plataforma após as modificações constitui aceitação dos novos termos.</p>

                <h2>11. Rescisão</h2>
                <p>Podemos suspender ou encerrar sua conta a qualquer momento, com ou sem aviso prévio, se você violar estes termos ou por qualquer outro motivo a nosso critério.</p>

                <h2>12. Lei Aplicável</h2>
                <p>Estes termos são regidos pelas leis brasileiras. Qualquer disputa será resolvida nos tribunais competentes do Brasil.</p>

                <h2>13. Contato</h2>
                <p>Para dúvidas sobre estes termos, entre em contato conosco:</p>
                <ul>
                    <li><strong>Email:</strong> <?= htmlspecialchars($site_email) ?></li>
                    <li><strong>Formulário:</strong> <a href="contato.php">Página de Contato</a></li>
                </ul>

                <div class="highlight-box">
                    <strong>Última atualização:</strong> <?= date('d/m/Y') ?><br>
                    <strong>Versão:</strong> 1.0
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