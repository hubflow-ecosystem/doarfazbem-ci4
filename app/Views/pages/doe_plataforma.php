<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<!-- Hero Section com Gradiente Vibrante -->
<div class="relative bg-gradient-to-br from-teal-500 via-emerald-500 to-green-600 text-white py-32 overflow-hidden">
    <!-- Efeitos de Fundo Animados -->
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-yellow-300/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-blue-300/10 rounded-full blur-2xl"></div>
    </div>

    <div class="container-custom text-center relative z-10">
        <div class="max-w-5xl mx-auto">
            <!-- Ícone Hero com Animação -->
            <div class="inline-flex items-center justify-center w-32 h-32 bg-white/20 backdrop-blur-md rounded-full mb-8 shadow-2xl transform hover:scale-110 transition-transform duration-500 animate-bounce">
                <i class="fas fa-hand-holding-heart text-7xl text-white drop-shadow-lg"></i>
            </div>

            <h1 class="text-7xl font-black mb-8 drop-shadow-2xl tracking-tight text-white">
                Doe para Plataforma
            </h1>

            <p class="text-3xl font-medium mb-12 text-white drop-shadow-lg leading-relaxed max-w-4xl mx-auto">
                Ajude a manter o DoarFazBem funcionando e conectando pessoas que querem fazer a diferença
            </p>

            <a href="<?= base_url('campaigns/mantenha-a-plataforma-ativa') ?>" class="inline-flex items-center gap-4 px-12 py-6 bg-white text-teal-700 font-black text-xl rounded-full shadow-2xl hover:shadow-3xl hover:scale-110 transform transition-all duration-300 hover:bg-yellow-400 hover:text-gray-900">
                <i class="fas fa-heart text-3xl animate-pulse"></i>
                <span>QUERO AJUDAR AGORA</span>
                <i class="fas fa-arrow-right text-2xl"></i>
            </a>
        </div>
    </div>
</div>

<!-- Por que doar? -->
<div class="bg-gradient-to-b from-gray-900 via-gray-800 to-gray-900 py-24">
    <div class="container-custom">
        <div class="text-center mb-20">
            <h2 class="text-5xl font-black text-white mb-6">Por que sua doação é importante?</h2>
            <p class="text-2xl text-gray-300 max-w-3xl mx-auto">Sua contribuição mantém a plataforma funcionando perfeitamente</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <!-- Card 1 - Servidor -->
            <div class="group relative bg-gradient-to-br from-blue-600 to-blue-800 rounded-3xl shadow-2xl hover:shadow-blue-500/50 transition-all duration-500 p-10 hover:-translate-y-4 transform overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6 shadow-xl group-hover:rotate-12 transition-transform duration-500">
                        <i class="fas fa-server text-5xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-white">Custos de Servidor</h3>
                    <p class="text-blue-100 text-lg leading-relaxed">
                        Manter a plataforma online 24/7 com alta disponibilidade e segurança
                    </p>
                </div>
            </div>

            <!-- Card 2 - Desenvolvimento -->
            <div class="group relative bg-gradient-to-br from-purple-600 to-purple-800 rounded-3xl shadow-2xl hover:shadow-purple-500/50 transition-all duration-500 p-10 hover:-translate-y-4 transform overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6 shadow-xl group-hover:rotate-12 transition-transform duration-500">
                        <i class="fas fa-code text-5xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-white">Desenvolvimento</h3>
                    <p class="text-purple-100 text-lg leading-relaxed">
                        Novos recursos, melhorias e correções para melhor experiência
                    </p>
                </div>
            </div>

            <!-- Card 3 - Suporte -->
            <div class="group relative bg-gradient-to-br from-green-600 to-green-800 rounded-3xl shadow-2xl hover:shadow-green-500/50 transition-all duration-500 p-10 hover:-translate-y-4 transform overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6 shadow-xl group-hover:rotate-12 transition-transform duration-500">
                        <i class="fas fa-headset text-5xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-white">Suporte</h3>
                    <p class="text-green-100 text-lg leading-relaxed">
                        Atendimento aos usuários e organizadores de campanhas
                    </p>
                </div>
            </div>

            <!-- Card 4 - Marketing -->
            <div class="group relative bg-gradient-to-br from-orange-500 to-red-600 rounded-3xl shadow-2xl hover:shadow-orange-500/50 transition-all duration-500 p-10 hover:-translate-y-4 transform overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6 shadow-xl group-hover:rotate-12 transition-transform duration-500">
                        <i class="fas fa-bullhorn text-5xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-black mb-4 text-white">Marketing</h3>
                    <p class="text-orange-100 text-lg leading-relaxed">
                        Divulgação para alcançar mais doadores e campanhas
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Formas de Doar -->
<div id="doar" class="bg-gradient-to-b from-gray-900 to-gray-800 py-24">
    <div class="container-custom">
        <div class="text-center mb-20">
            <h2 class="text-5xl font-black text-white mb-6">Como Doar</h2>
            <p class="text-2xl text-gray-300">Escolha a forma mais conveniente para você</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl mx-auto">
            <!-- PIX -->
            <div class="group relative bg-gradient-to-br from-teal-500 to-teal-700 rounded-3xl shadow-2xl hover:shadow-teal-500/50 transition-all duration-500 p-10 hover:-translate-y-4 text-white overflow-hidden">
                <div class="absolute -top-20 -right-20 w-48 h-48 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-6">
                        <i class="fas fa-qrcode drop-shadow-lg"></i>
                    </div>
                    <h3 class="text-3xl font-black mb-4">PIX</h3>
                    <p class="text-teal-100 text-lg mb-8 font-semibold">Instantâneo e sem taxas</p>
                    <div class="bg-white/20 backdrop-blur-md p-6 rounded-2xl text-center text-base font-bold break-all border-2 border-white/40">
                        pix@doarfazbem.com.br
                    </div>
                </div>
            </div>

            <!-- Cartão de Crédito -->
            <div class="group relative bg-gradient-to-br from-green-500 to-green-700 rounded-3xl shadow-2xl hover:shadow-green-500/50 transition-all duration-500 p-10 hover:-translate-y-4 text-white overflow-hidden">
                <div class="absolute -top-20 -right-20 w-48 h-48 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-6">
                        <i class="fas fa-credit-card drop-shadow-lg"></i>
                    </div>
                    <h3 class="text-3xl font-black mb-4">Cartão de Crédito</h3>
                    <p class="text-green-100 text-lg mb-8 font-semibold">Parcelamento disponível</p>
                    <a href="<?= base_url('campaigns/mantenha-a-plataforma-ativa') ?>" class="block w-full bg-white text-green-800 font-black py-4 px-8 rounded-2xl hover:bg-yellow-300 hover:text-gray-900 transition-all shadow-xl hover:shadow-2xl transform hover:scale-105 text-center">
                        DOAR COM CARTÃO
                    </a>
                </div>
            </div>

            <!-- Boleto -->
            <div class="group relative bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl shadow-2xl hover:shadow-blue-500/50 transition-all duration-500 p-10 hover:-translate-y-4 text-white overflow-hidden">
                <div class="absolute -top-20 -right-20 w-48 h-48 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-6">
                        <i class="fas fa-barcode drop-shadow-lg"></i>
                    </div>
                    <h3 class="text-3xl font-black mb-4">Boleto Bancário</h3>
                    <p class="text-blue-100 text-lg mb-8 font-semibold">Compensação em 1-3 dias</p>
                    <a href="<?= base_url('campaigns/mantenha-a-plataforma-ativa') ?>" class="block w-full bg-white text-blue-800 font-black py-4 px-8 rounded-2xl hover:bg-yellow-300 hover:text-gray-900 transition-all shadow-xl hover:shadow-2xl transform hover:scale-105 text-center">
                        GERAR BOLETO
                    </a>
                </div>
            </div>

            <!-- Doação Recorrente - DESTAQUE -->
            <div class="group relative bg-gradient-to-br from-yellow-400 via-orange-500 to-red-600 rounded-3xl shadow-2xl hover:shadow-yellow-500/50 transition-all duration-500 p-10 hover:-translate-y-4 text-white overflow-hidden border-4 border-yellow-300">
                <div class="absolute top-4 right-4 bg-white text-red-600 text-sm font-black px-4 py-2 rounded-full shadow-lg animate-bounce">
                    ⭐ RECOMENDADO
                </div>
                <div class="absolute -top-20 -right-20 w-48 h-48 bg-white/20 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-6">
                        <i class="fas fa-sync-alt animate-spin-slow drop-shadow-lg"></i>
                    </div>
                    <h3 class="text-3xl font-black mb-4">Doação Recorrente</h3>
                    <p class="text-lg mb-8 font-semibold">Apoio mensal automático</p>
                    <a href="<?= base_url('campaigns/mantenha-a-plataforma-ativa') ?>" class="block w-full bg-white text-orange-700 font-black py-4 px-8 rounded-2xl hover:bg-gray-900 hover:text-yellow-300 transition-all shadow-xl hover:shadow-2xl transform hover:scale-105 text-center">
                        SER APOIADOR VIP
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recompensas -->
<div class="relative bg-gradient-to-br from-purple-600 via-pink-600 to-red-600 py-24 text-white overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-yellow-300/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="container-custom relative z-10">
        <div class="text-center mb-20">
            <i class="fas fa-gift text-7xl mb-6 animate-bounce"></i>
            <h2 class="text-5xl font-black mb-6">Recompensas para Apoiadores</h2>
            <p class="text-2xl">Ganhe benefícios exclusivos por apoiar a plataforma</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl mx-auto">
            <div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-10 text-center border-2 border-white/30 hover:bg-white/20 hover:-translate-y-2 transition-all duration-300">
                <div class="text-6xl mb-6">
                    <i class="fas fa-badge-check"></i>
                </div>
                <h3 class="text-2xl font-black mb-3">Badge Apoiador</h3>
                <p class="text-lg">Destaque especial no seu perfil</p>
            </div>

            <div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-10 text-center border-2 border-white/30 hover:bg-white/20 hover:-translate-y-2 transition-all duration-300">
                <div class="text-6xl mb-6">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3 class="text-2xl font-black mb-3">Hall da Fama</h3>
                <p class="text-lg">Seu nome na lista de apoiadores</p>
            </div>

            <div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-10 text-center border-2 border-white/30 hover:bg-white/20 hover:-translate-y-2 transition-all duration-300">
                <div class="text-6xl mb-6">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3 class="text-2xl font-black mb-3">Acesso Antecipado</h3>
                <p class="text-lg">Teste novos recursos primeiro</p>
            </div>

            <div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl p-10 text-center border-2 border-white/30 hover:bg-white/20 hover:-translate-y-2 transition-all duration-300">
                <div class="text-6xl mb-6">
                    <i class="fas fa-file-certificate"></i>
                </div>
                <h3 class="text-2xl font-black mb-3">Certificado</h3>
                <p class="text-lg">Certificado de doação (dedutível IR)</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Final -->
<div class="relative bg-gradient-to-br from-teal-600 via-emerald-600 to-green-700 text-white py-28 overflow-hidden">
    <div class="absolute inset-0">
        <div class="absolute top-10 left-20 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-20 w-96 h-96 bg-yellow-300/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="container-custom text-center relative z-10">
        <div class="max-w-5xl mx-auto">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-white/20 backdrop-blur-md rounded-full mb-10 animate-pulse shadow-2xl">
                <i class="fas fa-heart text-7xl"></i>
            </div>
            <h2 class="text-6xl font-black mb-8 drop-shadow-lg">Faça Parte Dessa História</h2>
            <p class="text-3xl mb-12 leading-relaxed font-medium">
                O DoarFazBem só existe graças ao apoio de pessoas como você.<br>
                Juntos, podemos ajudar milhares de pessoas a realizarem seus sonhos.
            </p>
            <div class="flex flex-col sm:flex-row gap-8 justify-center">
                <a href="<?= base_url('campaigns/mantenha-a-plataforma-ativa') ?>" class="inline-flex items-center justify-center gap-4 px-12 py-6 bg-white text-teal-800 font-black text-xl rounded-full shadow-2xl hover:shadow-3xl hover:scale-110 transform transition-all hover:bg-yellow-400 hover:text-gray-900">
                    <i class="fas fa-donate text-3xl"></i>
                    <span>FAZER UMA DOAÇÃO</span>
                </a>
                <a href="<?= base_url('campaigns') ?>" class="inline-flex items-center justify-center gap-4 px-12 py-6 bg-white/10 backdrop-blur-md text-white font-black text-xl rounded-full border-4 border-white hover:bg-white hover:text-teal-800 transition-all shadow-2xl">
                    <i class="fas fa-search text-2xl"></i>
                    <span>VER CAMPANHAS</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FAQ -->
<div class="bg-gray-900 py-24">
    <div class="container-custom">
        <div class="text-center mb-20">
            <i class="fas fa-question-circle text-6xl text-teal-400 mb-6 animate-pulse"></i>
            <h2 class="text-5xl font-black text-white mb-6">Perguntas Frequentes</h2>
            <p class="text-2xl text-gray-300">Tire suas dúvidas sobre doações para plataforma</p>
        </div>

        <div class="max-w-5xl mx-auto space-y-6">
            <details class="group bg-gradient-to-r from-gray-800 to-blue-900 rounded-2xl p-8 shadow-2xl hover:shadow-blue-500/30 transition-all border-2 border-gray-700">
                <summary class="font-black text-2xl cursor-pointer flex items-center justify-between text-white">
                    <span class="flex items-center gap-4">
                        <i class="fas fa-file-invoice text-3xl text-teal-400"></i>
                        Minha doação é dedutível do Imposto de Renda?
                    </span>
                    <i class="fas fa-chevron-down text-2xl group-open:rotate-180 transition-transform text-teal-400"></i>
                </summary>
                <p class="mt-8 text-gray-300 text-xl pl-16 leading-relaxed">
                    Sim! Emitimos certificado de doação que pode ser usado para dedução no IR,
                    conforme legislação vigente.
                </p>
            </details>

            <details class="group bg-gradient-to-r from-gray-800 to-green-900 rounded-2xl p-8 shadow-2xl hover:shadow-green-500/30 transition-all border-2 border-gray-700">
                <summary class="font-black text-2xl cursor-pointer flex items-center justify-between text-white">
                    <span class="flex items-center gap-4">
                        <i class="fas fa-chart-line text-3xl text-green-400"></i>
                        Como posso acompanhar como minha doação está sendo usada?
                    </span>
                    <i class="fas fa-chevron-down text-2xl group-open:rotate-180 transition-transform text-green-400"></i>
                </summary>
                <p class="mt-8 text-gray-300 text-xl pl-16 leading-relaxed">
                    Sua doação será usada para manter a infraestrutura da plataforma, desenvolvimento de
                    novos recursos, suporte aos usuários e divulgação para alcançar mais pessoas.
                </p>
            </details>

            <details class="group bg-gradient-to-r from-gray-800 to-purple-900 rounded-2xl p-8 shadow-2xl hover:shadow-purple-500/30 transition-all border-2 border-gray-700">
                <summary class="font-black text-2xl cursor-pointer flex items-center justify-between text-white">
                    <span class="flex items-center gap-4">
                        <i class="fas fa-sync-alt text-3xl text-purple-400"></i>
                        Posso cancelar minha doação recorrente?
                    </span>
                    <i class="fas fa-chevron-down text-2xl group-open:rotate-180 transition-transform text-purple-400"></i>
                </summary>
                <p class="mt-8 text-gray-300 text-xl pl-16 leading-relaxed">
                    Claro! Você pode cancelar sua doação recorrente a qualquer momento pelo painel,
                    sem burocracia ou perguntas.
                </p>
            </details>

            <details class="group bg-gradient-to-r from-gray-800 to-yellow-900 rounded-2xl p-8 shadow-2xl hover:shadow-yellow-500/30 transition-all border-2 border-gray-700">
                <summary class="font-black text-2xl cursor-pointer flex items-center justify-between text-white">
                    <span class="flex items-center gap-4">
                        <i class="fas fa-hand-holding-usd text-3xl text-yellow-400"></i>
                        Qual o valor mínimo de doação?
                    </span>
                    <i class="fas fa-chevron-down text-2xl group-open:rotate-180 transition-transform text-yellow-400"></i>
                </summary>
                <p class="mt-8 text-gray-300 text-xl pl-16 leading-relaxed">
                    O valor mínimo é R$ 5,00. Qualquer contribuição a partir desse valor ajuda a manter
                    a plataforma funcionando e é muito importante para nós.
                </p>
            </details>
        </div>
    </div>
</div>

<style>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}
</style>

<?= $this->endSection() ?>
