<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Internsoft') ?></title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo-internsoft.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/logo-internsoft.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/company-profile.css') ?>">
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
