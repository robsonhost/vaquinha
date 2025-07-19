<!-- Meta Tags SEO -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- SEO Básico -->
<title><?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?> - <?= htmlspecialchars($textos['slogan'] ?? 'Plataforma de Vaquinhas Online') ?></title>
<meta name="description" content="<?= htmlspecialchars($textos['descricao_site'] ?? 'Plataforma de vaquinhas online para ajudar pessoas. Crie campanhas, faça doações e ajude causas importantes de forma segura e transparente.') ?>">
<meta name="keywords" content="vaquinha, vaquinha online, doação, campanha, solidariedade, ajuda, crowdfunding, arrecadação">
<meta name="author" content="<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>">
<meta name="robots" content="index, follow">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">
<meta property="og:title" content="<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>">
<meta property="og:description" content="<?= htmlspecialchars($textos['descricao_site'] ?? 'Plataforma de vaquinhas online para ajudar pessoas') ?>">
<?php if ($logo && $logo['caminho']): ?>
<meta property="og:image" content="<?= htmlspecialchars($logo['caminho']) ?>">
<?php endif; ?>
<meta property="og:site_name" content="<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>">
<meta property="og:locale" content="pt_BR">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">
<meta property="twitter:title" content="<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>">
<meta property="twitter:description" content="<?= htmlspecialchars($textos['descricao_site'] ?? 'Plataforma de vaquinhas online para ajudar pessoas') ?>">
<?php if ($logo && $logo['caminho']): ?>
<meta property="twitter:image" content="<?= htmlspecialchars($logo['caminho']) ?>">
<?php endif; ?>

<!-- Canonical URL -->
<link rel="canonical" href="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
<link rel="manifest" href="site.webmanifest">

<!-- Preconnect para performance -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net">
<link rel="preconnect" href="https://cdnjs.cloudflare.com">

<!-- DNS Prefetch -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

<!-- Structured Data -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>",
    "description": "<?= htmlspecialchars($textos['descricao_site'] ?? 'Plataforma de vaquinhas online para ajudar pessoas') ?>",
    "url": "<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" ?>",
    "potentialAction": {
        "@type": "SearchAction",
        "target": "<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" ?>/index.php?busca={search_term_string}",
        "query-input": "required name=search_term_string"
    }
}
</script>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>",
    "url": "<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" ?>",
    "logo": "<?= $logo && $logo['caminho'] ? htmlspecialchars($logo['caminho']) : '' ?>",
    "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "<?= htmlspecialchars($textos['telefone'] ?? '') ?>",
        "contactType": "customer service",
        "availableLanguage": "Portuguese"
    }
}
</script>

<!-- Acessibilidade -->
<meta name="theme-color" content="<?= $tema['cor_primaria'] ?? '#782F9B' ?>">
<meta name="msapplication-TileColor" content="<?= $tema['cor_primaria'] ?? '#782F9B' ?>">
<meta name="msapplication-config" content="browserconfig.xml">

<!-- Preload de recursos críticos -->
<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" as="style">
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" as="style"> 