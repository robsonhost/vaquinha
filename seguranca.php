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
    <title>Segurança - <?= htmlspecialchars($site_nome) ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Informações sobre segurança e proteção de dados na plataforma <?= htmlspecialchars($site_nome) ?>">
    <meta name="keywords" content="segurança, proteção de dados, SSL, criptografia, vaquinha, crowdfunding">
    <meta name="author" content="<?= htmlspecialchars($site_nome) ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Segurança - <?= htmlspecialchars($site_nome) ?>">
    <meta property="og:description" content="Informações sobre segurança e proteção de dados na plataforma">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $_SERVER['REQUEST_URI'] ?>">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Segurança - <?= htmlspecialchars($site_nome) ?>">
    <meta name="twitter:description" content="Informações sobre segurança e proteção de dados na plataforma">
    
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
        
        .security-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        
        .security-content h2 {
            color: var(--cor-primaria);
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--cor-secundaria);
            padding-bottom: 0.5rem;
        }
        
        .security-content h3 {
            color: var(--cor-secundaria);
            font-weight: 600;
            margin: 2rem 0 1rem 0;
        }
        
        .security-content p {
            margin-bottom: 1rem;
            text-align: justify;
        }
        
        .security-content ul {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        
        .security-content li {
            margin-bottom: 0.5rem;
        }
        
        .security-feature {
            background: linear-gradient(135deg, var(--cor-terciaria), #f8f9fa);
            border-left: 4px solid var(--cor-primaria);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
        }
        
        .security-badge {
            display: inline-block;
            background: var(--cor-secundaria);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0.5rem 0.5rem 0.5rem 0;
        }
        
        .certification-box {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
        }
        
        .certification-box i {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
        
        .tips-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-left: 4px solid #f39c12;
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
            
            .security-content {
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
            <h1><i class="fas fa-shield-alt"></i> Segurança</h1>
            <p>Protegemos seus dados com as mais avançadas tecnologias de segurança</p>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="security-content">
                <h2>1. Nossa Compromisso com a Segurança</h2>
                <p>A <?= htmlspecialchars($site_nome) ?> prioriza a segurança e privacidade de todos os usuários. Implementamos múltiplas camadas de proteção para garantir que seus dados pessoais e transações financeiras estejam sempre seguros.</p>
                
                <div class="certification-box">
                    <i class="fas fa-certificate"></i>
                    <h3>Certificação de Segurança</h3>
                    <p>Nossa plataforma é certificada com os mais altos padrões de segurança da indústria, incluindo conformidade com a LGPD e certificações SSL/TLS.</p>
                    <div>
                        <span class="security-badge"><i class="fas fa-lock"></i> SSL/TLS</span>
                        <span class="security-badge"><i class="fas fa-shield-alt"></i> LGPD</span>
                        <span class="security-badge"><i class="fas fa-check-circle"></i> PCI DSS</span>
                        <span class="security-badge"><i class="fas fa-user-shield"></i> GDPR</span>
                    </div>
                </div>

                <h2>2. Proteção de Dados</h2>
                <h3>2.1 Criptografia</h3>
                <p>Todos os dados sensíveis são protegidos com criptografia de ponta a ponta:</p>
                <ul>
                    <li><strong>Criptografia SSL/TLS:</strong> Todas as comunicações são criptografadas com certificados SSL de 256 bits</li>
                    <li><strong>Criptografia de Dados:</strong> Informações pessoais e financeiras são criptografadas em repouso</li>
                    <li><strong>Hashing de Senhas:</strong> Senhas são protegidas com algoritmos de hash seguros (bcrypt)</li>
                    <li><strong>Tokens de Sessão:</strong> Sessões são protegidas com tokens únicos e seguros</li>
                </ul>

                <div class="security-feature">
                    <h4><i class="fas fa-lock"></i> Criptografia SSL/TLS</h4>
                    <p>Utilizamos certificados SSL/TLS de 256 bits para proteger todas as transmissões de dados. Você pode verificar a segurança do site através do ícone de cadeado no seu navegador.</p>
                </div>

                <h3>2.2 Armazenamento Seguro</h3>
                <p>Seus dados são armazenados em servidores seguros com as seguintes proteções:</p>
                <ul>
                    <li>Servidores em data centers certificados</li>
                    <li>Backups automáticos e criptografados</li>
                    <li>Monitoramento 24/7 de segurança</li>
                    <li>Controle de acesso rigoroso</li>
                    <li>Firewalls de última geração</li>
                </ul>

                <h2>3. Segurança de Transações</h2>
                <h3>3.1 Processamento de Pagamentos</h3>
                <p>As transações financeiras são processadas por empresas certificadas e seguras:</p>
                <ul>
                    <li><strong>Gateways Certificados:</strong> Utilizamos apenas processadores de pagamento certificados PCI DSS</li>
                    <li><strong>Dados Não Armazenados:</strong> Informações de cartão de crédito não são armazenadas em nossos servidores</li>
                    <li><strong>Fraude Detection:</strong> Sistema avançado de detecção de fraudes</li>
                    <li><strong>Monitoramento:</strong> Todas as transações são monitoradas em tempo real</li>
                </ul>

                <div class="security-feature">
                    <h4><i class="fas fa-credit-card"></i> Pagamentos Seguros</h4>
                    <p>Todas as doações são processadas através de gateways de pagamento certificados e seguros. Suas informações financeiras nunca são armazenadas em nossos servidores.</p>
                </div>

                <h3>3.2 Verificação de Identidade</h3>
                <p>Implementamos múltiplas camadas de verificação:</p>
                <ul>
                    <li>Verificação de email obrigatória</li>
                    <li>Validação de documentos (quando necessário)</li>
                    <li>Verificação de endereço IP</li>
                    <li>Análise de comportamento de usuário</li>
                </ul>

                <h2>4. Proteção contra Ameaças</h2>
                <h3>4.1 Firewalls e Proteção DDoS</h3>
                <p>Nossa infraestrutura é protegida por:</p>
                <ul>
                    <li>Firewalls de aplicação (WAF)</li>
                    <li>Proteção contra ataques DDoS</li>
                    <li>Filtros de spam e malware</li>
                    <li>Monitoramento de tráfego suspeito</li>
                </ul>

                <h3>4.2 Detecção de Intrusão</h3>
                <p>Sistemas avançados de detecção e prevenção:</p>
                <ul>
                    <li>IDS/IPS (Sistemas de Detecção/Prevenção de Intrusão)</li>
                    <li>Análise de logs em tempo real</li>
                    <li>Alertas automáticos para atividades suspeitas</li>
                    <li>Resposta rápida a incidentes de segurança</li>
                </ul>

                <h2>5. Conformidade Legal</h2>
                <h3>5.1 LGPD (Lei Geral de Proteção de Dados)</h3>
                <p>Estamos em total conformidade com a LGPD:</p>
                <ul>
                    <li>Coleta e processamento de dados transparentes</li>
                    <li>Direitos dos titulares de dados respeitados</li>
                    <li>Relatório de impacto à proteção de dados (RIPD)</li>
                    <li>Encarregado de Proteção de Dados (DPO)</li>
                </ul>

                <h3>5.2 Outras Conformidades</h3>
                <ul>
                    <li><strong>PCI DSS:</strong> Padrão de segurança para processamento de cartões</li>
                    <li><strong>ISO 27001:</strong> Gestão de segurança da informação</li>
                    <li><strong>GDPR:</strong> Regulamento Geral de Proteção de Dados (UE)</li>
                </ul>

                <h2>6. Monitoramento e Auditoria</h2>
                <h3>6.1 Monitoramento Contínuo</h3>
                <p>Nossa equipe de segurança monitora a plataforma 24/7:</p>
                <ul>
                    <li>Monitoramento de logs de segurança</li>
                    <li>Análise de vulnerabilidades</li>
                    <li>Testes de penetração regulares</li>
                    <li>Auditorias de segurança independentes</li>
                </ul>

                <h3>6.2 Relatórios de Segurança</h3>
                <p>Mantemos relatórios detalhados de segurança e disponibilizamos:</p>
                <ul>
                    <li>Relatórios de conformidade</li>
                    <li>Certificados de segurança</li>
                    <li>Política de divulgação de vulnerabilidades</li>
                    <li>Contato direto com a equipe de segurança</li>
                </ul>

                <h2>7. Dicas de Segurança para Usuários</h2>
                <div class="tips-box">
                    <h4><i class="fas fa-lightbulb"></i> Como se Proteger</h4>
                    <ul>
                        <li><strong>Senhas Fortes:</strong> Use senhas únicas e complexas para sua conta</li>
                        <li><strong>Verificação em Duas Etapas:</strong> Ative a autenticação de dois fatores quando disponível</li>
                        <li><strong>Dispositivos Seguros:</strong> Acesse apenas de dispositivos confiáveis</li>
                        <li><strong>Logout:</strong> Sempre faça logout ao terminar de usar</li>
                        <li><strong>Links Suspeitos:</strong> Não clique em links suspeitos ou de fontes não confiáveis</li>
                        <li><strong>Atualizações:</strong> Mantenha seu navegador e sistema operacional atualizados</li>
                    </ul>
                </div>

                <h2>8. Incidentes de Segurança</h2>
                <h3>8.1 Como Reportar</h3>
                <p>Se você encontrar algum problema de segurança:</p>
                <ul>
                    <li>Entre em contato imediatamente: <?= htmlspecialchars($site_email) ?></li>
                    <li>Assunto: "INCIDENTE DE SEGURANÇA"</li>
                    <li>Descreva detalhadamente o problema encontrado</li>
                    <li>Inclua screenshots ou evidências quando possível</li>
                </ul>

                <h3>8.2 Nossa Resposta</h3>
                <p>Comprometemo-nos a:</p>
                <ul>
                    <li>Responder em até 24 horas</li>
                    <li>Investigar todas as denúncias</li>
                    <li>Corrigir problemas identificados</li>
                    <li>Manter você informado sobre o progresso</li>
                </ul>

                <h2>9. Contato de Segurança</h2>
                <div class="security-feature">
                    <h4><i class="fas fa-headset"></i> Equipe de Segurança</h4>
                    <p><strong>Email:</strong> <?= htmlspecialchars($site_email) ?><br>
                    <strong>Assunto:</strong> Para questões de segurança, use "SEGURANÇA" no assunto<br>
                    <strong>Resposta:</strong> Comprometemo-nos a responder em até 24 horas</p>
                </div>

                <div class="certification-box">
                    <i class="fas fa-shield-check"></i>
                    <h3>Certificações Ativas</h3>
                    <p>Nossa plataforma mantém as seguintes certificações de segurança ativas e atualizadas:</p>
                    <div>
                        <span class="security-badge"><i class="fas fa-check"></i> SSL/TLS 256-bit</span>
                        <span class="security-badge"><i class="fas fa-check"></i> LGPD Compliant</span>
                        <span class="security-badge"><i class="fas fa-check"></i> PCI DSS Level 1</span>
                        <span class="security-badge"><i class="fas fa-check"></i> ISO 27001</span>
                    </div>
                </div>

                <div class="tips-box">
                    <strong>Última atualização:</strong> <?= date('d/m/Y') ?><br>
                    <strong>Próxima auditoria:</strong> <?= date('d/m/Y', strtotime('+6 months')) ?><br>
                    <strong>Status:</strong> <span class="text-success">Todas as certificações ativas</span>
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
                        <li><a href="faq.php">FAQ</a></li>
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