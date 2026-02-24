<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<!-- ============================================ -->
<!-- HERO SECTION - Estilo Identidade Visual -->
<!-- ============================================ -->
<section class="bg-white py-8 lg:py-16">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">

            <!-- Texto do Hero -->
            <div class="text-center lg:text-left">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 mb-6 leading-tight">
                    Como<br>
                    <span class="text-emerald-500">Funciona</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                    Criar e arrecadar na DoarFazBem e <strong class="text-emerald-600">simples, rapido e seguro</strong>. Em poucos minutos sua campanha estara no ar.
                </p>

                <!-- Badges -->
                <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                    <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-bolt mr-1"></i> Rapido
                    </span>
                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-shield-alt mr-1"></i> Seguro
                    </span>
                    <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-check-circle mr-1"></i> Transparente
                    </span>
                </div>
            </div>

            <!-- Carrossel de Imagens -->
            <div class="relative"
                 x-data="{
                     currentSlide: 0,
                     totalSlides: 3,
                     init() {
                         setInterval(() => {
                             this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                         }, 4000);
                     }
                 }">
                <div class="w-full max-w-md mx-auto rounded-2xl overflow-hidden shadow-2xl">
                    <!-- Imagem 1: Criar -->
                    <div x-show="currentSlide === 0"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <img src="<?= base_url('assets/images/illustrations/como-funciona/hero-slide-1-criar.png') ?>"
                             alt="1. Crie sua Campanha - Em apenas 5 minutos"
                             class="w-full h-80 md:h-96 object-cover">
                    </div>

                    <!-- Imagem 2: Compartilhe -->
                    <div x-show="currentSlide === 1"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="w-full h-80 md:h-96 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <i class="fas fa-share-alt text-6xl mb-4"></i>
                                <h3 class="text-2xl font-bold">2. Compartilhe</h3>
                                <p class="text-lg mt-2">Nas redes sociais</p>
                            </div>
                        </div>
                    </div>

                    <!-- Imagem 3: Receba -->
                    <div x-show="currentSlide === 2"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="w-full h-80 md:h-96 bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <i class="fas fa-hand-holding-usd text-6xl mb-4"></i>
                                <h3 class="text-2xl font-bold">3. Receba as Doacoes</h3>
                                <p class="text-lg mt-2">Direto na sua conta</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Indicadores -->
                <div class="flex justify-center gap-2 mt-4">
                    <button @click="currentSlide = 0"
                            :class="currentSlide === 0 ? 'bg-emerald-500 w-8' : 'bg-gray-300 w-3'"
                            class="h-3 rounded-full transition-all duration-300"></button>
                    <button @click="currentSlide = 1"
                            :class="currentSlide === 1 ? 'bg-emerald-500 w-8' : 'bg-gray-300 w-3'"
                            class="h-3 rounded-full transition-all duration-300"></button>
                    <button @click="currentSlide = 2"
                            :class="currentSlide === 2 ? 'bg-emerald-500 w-8' : 'bg-gray-300 w-3'"
                            class="h-3 rounded-full transition-all duration-300"></button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- 3 PASSOS PRINCIPAIS -->
<!-- ============================================ -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Em 3 Passos Simples</h2>
            <p class="text-lg text-gray-600">
                Voce pode criar sua campanha e comecar a arrecadar hoje mesmo
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Passo 1 -->
            <div class="text-center group">
                <div class="w-24 h-24 mx-auto mb-6 bg-emerald-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-lg">
                    <span class="text-4xl font-black text-white">1</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Crie sua Campanha</h3>
                <p class="text-gray-600">
                    Cadastre-se gratis e crie sua campanha em minutos. Conte sua historia, adicione fotos e defina sua meta.
                </p>
            </div>

            <!-- Passo 2 -->
            <div class="text-center group">
                <div class="w-24 h-24 mx-auto mb-6 bg-purple-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-lg">
                    <span class="text-4xl font-black text-white">2</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Compartilhe</h3>
                <p class="text-gray-600">
                    Divulgue sua campanha nas redes sociais, WhatsApp, email e com amigos. Quanto mais pessoas virem, melhor!
                </p>
            </div>

            <!-- Passo 3 -->
            <div class="text-center group">
                <div class="w-24 h-24 mx-auto mb-6 bg-amber-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-lg">
                    <span class="text-4xl font-black text-white">3</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Receba as Doacoes</h3>
                <p class="text-gray-600">
                    Acompanhe em tempo real cada doacao. Solicite o saque direto para sua conta bancaria!
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- COMO DOAR -->
<!-- ============================================ -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Como Doar</h2>
            <p class="text-lg text-gray-600">
                Apoiar uma causa nunca foi tao facil
            </p>
        </div>

        <div class="grid md:grid-cols-4 gap-6 max-w-5xl mx-auto">
            <div class="bg-gray-50 rounded-xl p-6 hover:-translate-y-2 transition-transform duration-300 shadow-sm hover:shadow-lg">
                <div class="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-search text-2xl text-white"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 text-center">1. Encontre</h3>
                <p class="text-gray-600 text-sm text-center">
                    Navegue pelas campanhas e escolha uma causa
                </p>
            </div>

            <div class="bg-gray-50 rounded-xl p-6 hover:-translate-y-2 transition-transform duration-300 shadow-sm hover:shadow-lg">
                <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-hand-holding-usd text-2xl text-white"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 text-center">2. Escolha o Valor</h3>
                <p class="text-gray-600 text-sm text-center">
                    Defina quanto quer doar. Qualquer valor ajuda!
                </p>
            </div>

            <div class="bg-gray-50 rounded-xl p-6 hover:-translate-y-2 transition-transform duration-300 shadow-sm hover:shadow-lg">
                <div class="w-14 h-14 bg-purple-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-credit-card text-2xl text-white"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 text-center">3. Pague</h3>
                <p class="text-gray-600 text-sm text-center">
                    Cartao, PIX ou boleto bancario
                </p>
            </div>

            <div class="bg-gray-50 rounded-xl p-6 hover:-translate-y-2 transition-transform duration-300 shadow-sm hover:shadow-lg">
                <div class="w-14 h-14 bg-pink-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-heart text-2xl text-white"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2 text-center">4. Pronto!</h3>
                <p class="text-gray-600 text-sm text-center">
                    Sua doacao foi realizada com sucesso
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- TAXAS TRANSPARENTES -->
<!-- ============================================ -->
<section class="py-16 bg-gray-900 text-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black mb-4">Taxas Transparentes</h2>
            <p class="text-lg text-gray-300">
                Saiba exatamente quanto custa usar a DoarFazBem
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <!-- Campanhas Medicas -->
            <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-2xl p-8 hover:-translate-y-2 transition-transform duration-300">
                <div class="text-center">
                    <i class="fas fa-heartbeat text-5xl mb-4"></i>
                    <h3 class="text-2xl font-bold mb-4">Campanhas Medicas</h3>
                    <div class="text-6xl font-black mb-4">0%</div>
                    <p class="text-lg mb-4">
                        Taxa de plataforma ZERO!
                    </p>
                    <div class="bg-white/20 rounded-lg p-4">
                        <p class="text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Apenas taxas do gateway de pagamento
                        </p>
                    </div>
                </div>
            </div>

            <!-- Outras Campanhas -->
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-8 hover:-translate-y-2 transition-transform duration-300">
                <div class="text-center">
                    <i class="fas fa-rocket text-5xl mb-4"></i>
                    <h3 class="text-2xl font-bold mb-4">Outras Campanhas</h3>
                    <div class="text-6xl font-black mb-4">2%</div>
                    <p class="text-lg mb-4">
                        A menor do mercado!
                    </p>
                    <div class="bg-white/20 rounded-lg p-4">
                        <p class="text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Projetos criativos, negocios, educacao
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- FAQ -->
<!-- ============================================ -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Perguntas Frequentes</h2>
            </div>

            <div class="space-y-4">
                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-question-circle text-emerald-500 mr-3"></i>
                        Quanto tempo leva para criar uma campanha?
                    </h3>
                    <p class="text-gray-700 ml-8">
                        Em media, 5 minutos! Basta preencher as informacoes basicas, adicionar uma foto e publicar.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-question-circle text-emerald-500 mr-3"></i>
                        Quando posso sacar o dinheiro arrecadado?
                    </h3>
                    <p class="text-gray-700 ml-8">
                        Voce pode solicitar o saque assim que atingir sua meta ou ao final do prazo da campanha.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-question-circle text-emerald-500 mr-3"></i>
                        Preciso ter CNPJ para criar uma campanha?
                    </h3>
                    <p class="text-gray-700 ml-8">
                        Nao! Qualquer pessoa fisica pode criar campanhas na DoarFazBem.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-shadow">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                        <i class="fas fa-question-circle text-emerald-500 mr-3"></i>
                        Como funciona a seguranca dos pagamentos?
                    </h3>
                    <p class="text-gray-700 ml-8">
                        Utilizamos o Asaas, um gateway de pagamento certificado e seguro. Todos os dados sao criptografados.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- CTA FINAL -->
<!-- ============================================ -->
<section class="py-16 bg-emerald-600 text-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-black mb-6">Pronto para Comecar?</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Crie sua campanha agora e comece a transformar vidas!
        </p>
        <a href="<?= base_url('register') ?>"
           class="inline-flex items-center gap-2 px-8 py-4 bg-white text-emerald-600 font-bold text-lg rounded-xl hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
            <i class="fas fa-plus-circle text-xl"></i>
            Criar Campanha Gratis
        </a>
    </div>
</section>

<?= $this->endSection() ?>
