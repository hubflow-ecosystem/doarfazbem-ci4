<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Meta Tags Anti-Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- SEO Meta Tags (Dinâmico via SeoMetaService) -->
    <?php
      $seoTitle = $seoMeta['title'] ?? $title ?? 'DoarFazBem - A plataforma de crowdfunding mais justa do Brasil';
      $seoDescription = $seoMeta['description'] ?? $description ?? 'Campanhas médicas 100% gratuitas. Apenas 2% para outras campanhas. Sistema transparente e seguro.';
      $seoKeywords = $seoMeta['keywords'] ?? 'crowdfunding, doação, vaquinha online, campanhas sociais, campanhas médicas, crowdfunding brasil, doação online, vaquinha solidária';
      $seoImage = $seoMeta['image'] ?? base_url('assets/images/og-image.jpg');
      $seoCanonical = $seoMeta['canonical'] ?? current_url();
      $seoRobots = $seoMeta['robots'] ?? 'index, follow';
      $seoOgType = $seoMeta['og_type'] ?? 'website';
    ?>
    <title><?= esc($seoTitle) ?></title>
    <meta name="description" content="<?= esc($seoDescription) ?>">
    <meta name="keywords" content="<?= esc($seoKeywords) ?>">
    <meta name="author" content="DoarFazBem">
    <meta name="robots" content="<?= esc($seoRobots) ?>">
    <meta name="language" content="pt-BR">
    <meta name="revisit-after" content="7 days">
    <meta name="rating" content="general">
    <link rel="canonical" href="<?= esc($seoCanonical) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= esc($seoOgType) ?>">
    <meta property="og:url" content="<?= esc($seoCanonical) ?>">
    <meta property="og:title" content="<?= esc($seoTitle) ?>">
    <meta property="og:description" content="<?= esc($seoDescription) ?>">
    <meta property="og:image" content="<?= esc($seoImage) ?>">
    <meta property="og:site_name" content="DoarFazBem">
    <meta property="og:locale" content="pt_BR">
    <?php if (!empty($seoMeta['article_published'])): ?>
    <meta property="article:published_time" content="<?= esc($seoMeta['article_published']) ?>">
    <?php endif; ?>
    <?php if (!empty($seoMeta['article_modified'])): ?>
    <meta property="article:modified_time" content="<?= esc($seoMeta['article_modified']) ?>">
    <?php endif; ?>
    <?php if (!empty($seoMeta['article_author'])): ?>
    <meta property="article:author" content="<?= esc($seoMeta['article_author']) ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= esc($seoCanonical) ?>">
    <meta name="twitter:title" content="<?= esc($seoTitle) ?>">
    <meta name="twitter:description" content="<?= esc($seoDescription) ?>">
    <meta name="twitter:image" content="<?= esc($seoImage) ?>">

    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="DoarFazBem Blog" href="<?= base_url('blog/feed') ?>"">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/favicon.png') ?>">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= base_url('manifest.json') ?>">
    <meta name="theme-color" content="#10B981">
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#10B981">
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#047857">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="DoarFazBem">
    <link rel="apple-touch-icon" href="<?= base_url('assets/icons/icon-192x192.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/icons/icon-192x192.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= base_url('assets/icons/icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?= base_url('assets/icons/icon-144x144.png') ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="DoarFazBem">
    <meta name="msapplication-TileColor" content="#10B981">
    <meta name="msapplication-TileImage" content="<?= base_url('assets/icons/icon-144x144.png') ?>">
    <meta name="msapplication-config" content="<?= base_url('browserconfig.xml') ?>">

    <!-- Tailwind CSS Compilado -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" />

    <!-- Alpine.js Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js (para gráficos) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Tremor-Style Components (DEVE carregar ANTES do Alpine.js) -->
    <script src="<?= base_url('assets/js/tremor-style-components.js') ?>"></script>

    <!-- Alpine Components (nossos componentes customizados) -->
    <script src="<?= base_url('assets/js/alpine-components.js') ?>"></script>

    <!-- Alpine.js Core (CARREGAR POR ÚLTIMO) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9SWBDMBQL6"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-9SWBDMBQL6', {
            'send_page_view': true,
            'anonymize_ip': true,
            'cookie_flags': 'SameSite=None;Secure'
        });
    </script>

    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GT-P8452X3');
    </script>

    <!-- Schema.org Structured Data (Organização - global) -->
    <?php
      // Usa SchemaMarkupService se disponível, senão Schema estático
      if (!empty($schemaOrg)) {
        echo $schemaOrg;
      } else {
    ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "DoarFazBem",
        "url": "<?= base_url() ?>",
        "logo": "<?= base_url('assets/images/logo.png') ?>",
        "description": "A plataforma de crowdfunding mais justa do Brasil. Campanhas médicas 100% gratuitas.",
        "sameAs": [
            "https://facebook.com/doarfazbem",
            "https://instagram.com/doarfazbem",
            "https://twitter.com/doarfazbem"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "Customer Service",
            "email": "contato@doarfazbem.com.br",
            "availableLanguage": "Portuguese"
        }
    }
    </script>
    <?php } ?>

    <?php if (isset($additional_css)): ?>
        <?= $additional_css ?>
    <?php endif; ?>

    <!-- Firebase Cloud Messaging (Push Notifications) -->
    <script type="module" src="<?= base_url('assets/js/firebase-init.js') ?>"></script>

    <!-- Service Worker Registration -->
    <script>
        // Registrar Service Worker para PWA e notificações
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // Service Worker principal (PWA)
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('[PWA] Service Worker registrado com sucesso:', registration.scope);
                    })
                    .catch(function(error) {
                        console.error('[PWA] Falha ao registrar Service Worker:', error);
                    });

                // Service Worker do Firebase Messaging
                navigator.serviceWorker.register('/firebase-messaging-sw.js')
                    .then(function(registration) {
                        console.log('[FCM] Firebase Messaging SW registrado:', registration.scope);
                    })
                    .catch(function(error) {
                        console.warn('[FCM] Erro ao registrar Firebase Messaging SW:', error);
                    });
            });
        }

        // Detectar se PWA foi instalada
        window.addEventListener('appinstalled', function() {
            console.log('[PWA] App instalado com sucesso!');
            // Enviar evento para analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'pwa_installed', {
                    'event_category': 'PWA',
                    'event_label': 'App Installed'
                });
            }
        });

        // Prompt de instalação do PWA
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            console.log('[PWA] Prompt de instalação disponível');

            // Mostrar botão de instalação customizado
            const installButton = document.getElementById('pwa-install-button');
            if (installButton) {
                installButton.style.display = 'block';
                installButton.addEventListener('click', async function() {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        console.log('[PWA] Escolha do usuário:', outcome);

                        if (outcome === 'accepted') {
                            console.log('[PWA] Usuário aceitou instalar');
                            if (typeof gtag !== 'undefined') {
                                gtag('event', 'pwa_install_accepted', {
                                    'event_category': 'PWA',
                                    'event_label': 'Install Accepted'
                                });
                            }
                        }

                        deferredPrompt = null;
                        installButton.style.display = 'none';
                    }
                });
            }
        });
    </script>

    <!-- Meta Tags Customizadas por Página -->
    <?= $this->renderSection('head') ?>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen" x-data>

    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GT-P8452X3"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>

    <!-- Global Notifications (Alpine Store) -->
    <div class="fixed top-4 right-4 z-50 space-y-2" x-data>
        <template x-for="notification in $store.app?.notifications || []" :key="notification.id">
            <div class="bg-white rounded-lg shadow-lg p-4 max-w-sm animate-slide-up"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full">
                <div class="flex items-start">
                    <i :class="`fas ${notification.type === 'success' ? 'fa-check-circle text-green-500' : notification.type === 'error' ? 'fa-exclamation-circle text-red-500' : 'fa-info-circle text-blue-500'} text-2xl mr-3`"></i>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900" x-text="notification.message"></p>
                    </div>
                    <button @click="$store.app.removeNotification(notification.id)" class="text-gray-400 hover:text-gray-600 ml-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Header -->
    <?= $this->include('layout/header') ?>

    <!-- Flash Messages (Mensagens de Sucesso/Erro do PHP) -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="container-custom py-4">
            <div class="alert-success animate-fade-in" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-3"></i>
                        <p class="text-green-800 font-medium"><?= session()->getFlashdata('success') ?></p>
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="container-custom py-4">
            <div class="alert-error animate-fade-in" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                        <p class="text-red-800 font-medium"><?= session()->getFlashdata('error') ?></p>
                    </div>
                    <button @click="show = false" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('info')): ?>
        <div class="container-custom py-4">
            <div class="alert-info animate-fade-in" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                        <p class="text-blue-800 font-medium"><?= session()->getFlashdata('info') ?></p>
                    </div>
                    <button @click="show = false" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-grow">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <?= $this->include('layout/footer') ?>

    <!-- Alpine.js Global Store Initialization -->
    <script>
        document.addEventListener('alpine:init', () => {
            // Initialize store if needed
            Alpine.store('app', {
                user: {
                    id: <?= session()->get('id') ?? 'null' ?>,
                    name: <?= session()->get('name') ? '"' . esc(session()->get('name')) . '"' : 'null' ?>,
                    email: <?= session()->get('email') ? '"' . esc(session()->get('email')) . '"' : 'null' ?>,
                    role: <?= session()->get('role') ? '"' . session()->get('role') . '"' : 'null' ?>,
                    isLoggedIn: <?= session()->get('isLoggedIn') ? 'true' : 'false' ?>
                },
                ui: {
                    sidebarOpen: false,
                    mobileMenuOpen: false,
                    modalOpen: false
                },
                notifications: [],
                toggleSidebar() {
                    this.ui.sidebarOpen = !this.ui.sidebarOpen;
                },
                toggleMobileMenu() {
                    this.ui.mobileMenuOpen = !this.ui.mobileMenuOpen;
                },
                addNotification(message, type = 'info') {
                    const id = Date.now();
                    this.notifications.push({ id, message, type });
                    setTimeout(() => {
                        this.removeNotification(id);
                    }, 5000);
                },
                removeNotification(id) {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }
            });
        });
    </script>

    <!-- Chart.js (se necessário) -->
    <?php if (isset($use_charts) && $use_charts): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php endif; ?>

    <!-- JavaScript Adicional -->
    <?php if (isset($additional_js)): ?>
        <?= $additional_js ?>
    <?php endif; ?>

    <!-- Scripts das Views -->
    <?= $this->renderSection('scripts') ?>

    <!-- Service Worker Registration já está sendo feito em firebase-init.js (linha 132) -->

    <!-- Timestamp para forçar atualização -->
    <div class="hidden"><?= date('Y-m-d H:i:s') ?></div>

    <!-- Banner Global de Rifas (exceto em paginas de rifas) -->
    <?php
    $currentUrl = current_url();
    $isRafflePage = strpos($currentUrl, '/rifas') !== false || strpos($currentUrl, '/numeros-da-sorte') !== false;
    if (!$isRafflePage):
    ?>
        <?= view('components/raffle_banner') ?>
    <?php endif; ?>

    <!-- Widget de Chat Alex — DoarFazBem (config padrão: recepção) -->
    <!-- Views específicas (campanha/rifa) sobrescrevem via window.DoarFazBemChat antes desta tag -->
    <script>
        if (!window.DoarFazBemChat) {
            window.DoarFazBemChat = {
                agentId: 'doarfazbem-recepcao',
                color: '#16a34a',
                position: 'bottom-right',
                title: 'Alex',
                subtitle: '● Fale conosco',
                lang: 'pt',
                avatar: '<?= base_url("assets/images/avatar-alex.png") ?>',
                whatsappFallback: '5547996966724'
            };
        }
    </script>
    <script async src="https://agents.hubflowai.com/widget.js"></script>

</body>
</html>
