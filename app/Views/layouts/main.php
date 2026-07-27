<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Internsoft') ?></title>
    <?php if (! empty($metaDescription)): ?>
        <meta name="description" content="<?= esc($metaDescription) ?>">
    <?php endif; ?>
    <?php if (! empty($metaKeywords)): ?>
        <meta name="keywords" content="<?= esc($metaKeywords) ?>">
    <?php endif; ?>
    <?php
        $canonical = $canonicalUrl ?? current_url();
        $ogTitle = $ogTitle ?? ($title ?? 'Internsoft');
        $ogDescription = $ogDescription ?? ($metaDescription ?? '');
        $ogImage = $ogImage ?? base_url('assets/img/logo-internsoft.png');
    ?>
    <link rel="canonical" href="<?= esc($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="<?= esc($ogTitle) ?>">
    <meta property="og:description" content="<?= esc($ogDescription) ?>">
    <meta property="og:url" content="<?= esc($canonical) ?>">
    <meta property="og:image" content="<?= esc($ogImage) ?>">
    <meta property="og:site_name" content="Internsoft Technology Solutions">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($ogTitle) ?>">
    <meta name="twitter:description" content="<?= esc($ogDescription) ?>">
    <meta name="robots" content="<?= esc($robots ?? 'index,follow') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo-internsoft.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/logo-internsoft.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/company-profile.css') ?>">
    <?php if (! empty($jsonLd)): ?>
        <script type="application/ld+json"><?= $jsonLd ?></script>
    <?php endif; ?>
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
