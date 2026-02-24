<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<!-- Hero Section -->
<div class="bg-gradient-to-br from-primary-500 to-primary-700 text-white py-20">
    <div class="container-custom text-center">
        <div class="max-w-3xl mx-auto">
            <i class="fas fa-bullhorn text-6xl mb-6 animate-pulse"></i>
            <h1 class="text-5xl font-bold mb-6">Anuncie Conosco</h1>
            <p class="text-xl text-primary-100 mb-8">
                Conecte sua marca a milhares de pessoas solidárias que acreditam em fazer a diferença
            </p>
            <a href="#contato" class="btn-primary bg-white text-primary-600 hover:bg-gray-100">
                <i class="fas fa-envelope mr-2"></i> Fale Conosco
            </a>
        </div>
    </div>
</div>

<!-- Estatísticas -->
<div class="bg-white py-16 border-b">
    <div class="container-custom">
        <h2 class="text-3xl font-bold text-center mb-12">Por que anunciar no DoarFazBem?</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="text-5xl font-bold text-primary-600 mb-2">50K+</div>
                <div class="text-gray-600 font-medium">Visitantes Mensais</div>
            </div>
            <div class="text-center">
                <div class="text-5xl font-bold text-secondary-600 mb-2">500+</div>
                <div class="text-gray-600 font-medium">Campanhas Ativas</div>
            </div>
            <div class="text-center">
                <div class="text-5xl font-bold text-green-600 mb-2">10K+</div>
                <div class="text-gray-600 font-medium">Doadores Únicos</div>
            </div>
            <div class="text-center">
                <div class="text-5xl font-bold text-yellow-600 mb-2">85%</div>
                <div class="text-gray-600 font-medium">Taxa de Engajamento</div>
            </div>
        </div>
    </div>
</div>

<!-- Formatos de Anúncio -->
<div class="bg-gray-50 py-16">
    <div class="container-custom">
        <h2 class="text-3xl font-bold text-center mb-12">Formatos de Anúncio</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Banner Topo -->
            <div class="bg-white rounded-xl shadow-card p-6 hover:shadow-lg transition-shadow">
                <div class="text-4xl mb-4 text-primary-500">
                    <i class="fas fa-rectangle-ad"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Banner Topo</h3>
                <p class="text-gray-600 mb-4">728x90 pixels</p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Homepage</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Lista de Campanhas</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Alta Visibilidade</li>
                </ul>
            </div>

            <!-- Banner Lateral -->
            <div class="bg-white rounded-xl shadow-card p-6 hover:shadow-lg transition-shadow">
                <div class="text-4xl mb-4 text-secondary-500">
                    <i class="fas fa-sidebar"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Banner Lateral</h3>
                <p class="text-gray-600 mb-4">300x250 pixels</p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Sidebar Desktop</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Detalhes Campanha</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Permanência Alta</li>
                </ul>
            </div>

            <!-- Banner Rodapé -->
            <div class="bg-white rounded-xl shadow-card p-6 hover:shadow-lg transition-shadow">
                <div class="text-4xl mb-4 text-green-500">
                    <i class="fas fa-window-maximize"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Banner Rodapé</h3>
                <p class="text-gray-600 mb-4">728x90 pixels</p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Todas as Páginas</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Mobile e Desktop</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Grande Alcance</li>
                </ul>
            </div>

            <!-- Campanha Patrocinada -->
            <div class="bg-white rounded-xl shadow-card p-6 hover:shadow-lg transition-shadow border-2 border-yellow-400">
                <div class="text-4xl mb-4 text-yellow-500">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Campanha Patrocinada</h3>
                <p class="text-gray-600 mb-4">Destaque Premium</p>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Posição #1</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Badge Patrocinado</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Máximo Destaque</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Preços -->
<div class="bg-white py-16">
    <div class="container-custom">
        <h2 class="text-3xl font-bold text-center mb-12">Planos e Preços</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Plano Mensal -->
            <div class="bg-white rounded-xl shadow-card p-8 hover:shadow-xl transition-shadow">
                <h3 class="text-2xl font-bold mb-4">Mensal</h3>
                <div class="text-4xl font-bold text-primary-600 mb-6">
                    R$ 500<span class="text-lg text-gray-500">/mês</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li><i class="fas fa-check text-green-500 mr-2"></i> 1 Banner de sua escolha</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Relatório mensal</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Suporte por email</li>
                </ul>
                <a href="#contato" class="btn-outline w-full text-center">Começar</a>
            </div>

            <!-- Plano Trimestral -->
            <div class="bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl shadow-card p-8 hover:shadow-xl transition-shadow text-white relative">
                <div class="absolute top-4 right-4 bg-yellow-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-full">
                    20% OFF
                </div>
                <h3 class="text-2xl font-bold mb-4">Trimestral</h3>
                <div class="text-4xl font-bold mb-6">
                    R$ 1.200<span class="text-lg opacity-75">/3 meses</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li><i class="fas fa-check mr-2"></i> 2 Banners simultâneos</li>
                    <li><i class="fas fa-check mr-2"></i> Relatórios semanais</li>
                    <li><i class="fas fa-check mr-2"></i> Suporte prioritário</li>
                </ul>
                <a href="#contato" class="btn-primary bg-white text-primary-600 hover:bg-gray-100 w-full text-center">
                    Mais Popular
                </a>
            </div>

            <!-- Plano Semestral -->
            <div class="bg-white rounded-xl shadow-card p-8 hover:shadow-xl transition-shadow">
                <div class="absolute top-4 right-4 bg-green-400 text-gray-900 text-xs font-bold px-3 py-1 rounded-full">
                    30% OFF
                </div>
                <h3 class="text-2xl font-bold mb-4">Semestral</h3>
                <div class="text-4xl font-bold text-primary-600 mb-6">
                    R$ 2.100<span class="text-lg text-gray-500">/6 meses</span>
                </div>
                <ul class="space-y-3 mb-8">
                    <li><i class="fas fa-check text-green-500 mr-2"></i> 3 Banners simultâneos</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Relatórios personalizados</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i> Suporte 24/7</li>
                </ul>
                <a href="#contato" class="btn-outline w-full text-center">Melhor Valor</a>
            </div>
        </div>
    </div>
</div>

<!-- CTA / Contato -->
<div id="contato" class="bg-gradient-to-br from-secondary-500 to-secondary-700 text-white py-16">
    <div class="container-custom">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-4xl font-bold mb-6">Pronto para Anunciar?</h2>
            <p class="text-xl mb-8 text-secondary-100">
                Entre em contato conosco e vamos criar uma campanha publicitária perfeita para sua marca
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="mailto:anuncios@doarfazbem.com.br" class="bg-white text-gray-900 rounded-xl p-6 hover:shadow-xl transition-shadow">
                    <i class="fas fa-envelope text-3xl text-secondary-500 mb-3"></i>
                    <div class="font-semibold">Email</div>
                    <div class="text-sm text-gray-600">anuncios@doarfazbem.com.br</div>
                </a>

                <a href="https://wa.me/5511999999999?text=Olá! Gostaria de anunciar no DoarFazBem" target="_blank" class="bg-white text-gray-900 rounded-xl p-6 hover:shadow-xl transition-shadow">
                    <i class="fab fa-whatsapp text-3xl text-green-500 mb-3"></i>
                    <div class="font-semibold">WhatsApp</div>
                    <div class="text-sm text-gray-600">(11) 99999-9999</div>
                </a>

                <a href="tel:+551133334444" class="bg-white text-gray-900 rounded-xl p-6 hover:shadow-xl transition-shadow">
                    <i class="fas fa-phone text-3xl text-blue-500 mb-3"></i>
                    <div class="font-semibold">Telefone</div>
                    <div class="text-sm text-gray-600">(11) 3333-4444</div>
                </a>
            </div>

            <p class="text-sm text-secondary-100">
                Horário de atendimento: Segunda a Sexta, das 9h às 18h
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
