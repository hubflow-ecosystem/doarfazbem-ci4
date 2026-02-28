<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title><?= esc($title ?? 'DoarFazBem - A plataforma de crowdfunding mais justa do Brasil') ?></title>
  <meta name="description" content="<?= esc($description ?? 'Campanhas médicas 100% gratuitas. Sistema transparente e seguro.') ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/images/favicon.png') ?>">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= base_url('assets/icons/icon-192x192.png') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192x192.png') ?>">
  <meta name="theme-color" content="#10B981">

  <!-- Tailwind CSS Compilado -->
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />

  <!-- Alpine.js -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <?= $this->renderSection('head') ?>
</head>
<body class="min-h-screen" style="font-family: 'Inter', system-ui, sans-serif;">
  <?= $this->renderSection('content') ?>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
