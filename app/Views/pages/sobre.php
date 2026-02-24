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
                    Sobre a<br>
                    <span class="text-emerald-500">DoarFazBem</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                    A plataforma de crowdfunding <strong class="text-emerald-600">mais justa e transparente</strong> do Brasil. Conectamos pessoas que precisam de ajuda com quem quer fazer a diferenca.
                </p>

                <!-- Badges -->
                <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                    <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-hand-holding-heart mr-1"></i> Solidariedade
                    </span>
                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-balance-scale mr-1"></i> Justica
                    </span>
                    <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-eye mr-1"></i> Transparencia
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
                    <!-- Imagem 1: Solidariedade -->
                    <div x-show="currentSlide === 0"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="w-full h-80 md:h-96 bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <i class="fas fa-hand-holding-heart text-6xl mb-4"></i>
                                <h3 class="text-2xl font-bold">Solidariedade</h3>
                                <p class="text-lg mt-2">Ajudando quem precisa</p>
                            </div>
                        </div>
                    </div>

                    <!-- Imagem 2: Transparencia -->
                    <div x-show="currentSlide === 1"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="w-full h-80 md:h-96 bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <i class="fas fa-eye text-6xl mb-4"></i>
                                <h3 class="text-2xl font-bold">Transparencia</h3>
                                <p class="text-lg mt-2">Sem taxas ocultas</p>
                            </div>
                        </div>
                    </div>

                    <!-- Imagem 3: Seguranca -->
                    <div x-show="currentSlide === 2"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <div class="w-full h-80 md:h-96 bg-gradient-to-br from-purple-400 to-pink-500 flex items-center justify-center">
                            <div class="text-center text-white p-8">
                                <i class="fas fa-shield-alt text-6xl mb-4"></i>
                                <h3 class="text-2xl font-bold">Seguranca</h3>
                                <p class="text-lg mt-2">Seus dados protegidos</p>
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
<!-- NOSSA MISSAO -->
<!-- ============================================ -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Nossa Missao</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Democratizar o acesso ao crowdfunding, oferecendo uma plataforma gratuita para quem mais precisa e cobrando taxas justas de quem pode contribuir.
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <div class="text-center p-6 bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-hand-holding-heart text-2xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Solidariedade</h3>
                <p class="text-gray-600">
                    Acreditamos que todos merecem uma chance de realizar seus sonhos e superar dificuldades.
                </p>
            </div>

            <div class="text-center p-6 bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-balance-scale text-2xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Justica</h3>
                <p class="text-gray-600">
                    Taxas diferenciadas garantem que campanhas medicas sejam 100% gratuitas.
                </p>
            </div>

            <div class="text-center p-6 bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow">
                <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <i class="fas fa-eye text-2xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Transparencia</h3>
                <p class="text-gray-600">
                    Voce ve exatamente para onde vai cada centavo doado. Sem taxas ocultas.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- NOSSA HISTORIA -->
<!-- ============================================ -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-8">
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">Nossa Historia</h2>
            </div>

            <div class="bg-gray-50 rounded-xl p-8">
                <div class="prose prose-lg max-w-none">
                    <p class="text-gray-700 text-lg leading-relaxed mb-6">
                        <i class="fas fa-quote-left text-emerald-400 text-2xl mr-2"></i>
                        A DoarFazBem nasceu da necessidade de criar uma plataforma de crowdfunding verdadeiramente social, onde pessoas em situacao de vulnerabilidade pudessem arrecadar fundos sem se preocupar com taxas abusivas.
                    </p>

                    <p class="text-gray-700 leading-relaxed mb-6">
                        Vimos muitas pessoas precisando de ajuda para tratamentos medicos, cirurgias urgentes e causas sociais importantes, mas perdendo uma parte significativa das doacoes em taxas de plataforma. Isso nao nos parecia justo.
                    </p>

                    <p class="text-gray-700 leading-relaxed">
                        Por isso criamos a DoarFazBem: uma plataforma onde campanhas medicas tem
                        <strong class="text-emerald-600">0% de taxa</strong>, e outras campanhas pagam apenas
                        <strong class="text-emerald-600">2%</strong> - a menor taxa do mercado brasileiro.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- NOSSOS VALORES -->
<!-- ============================================ -->
<section class="py-16 bg-gray-900 text-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black mb-4">Nossos Valores</h2>
        </div>

        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <div class="bg-white/10 rounded-xl p-6 hover:-translate-y-1 transition-transform duration-300">
                <div class="flex items-start space-x-4">
                    <i class="fas fa-shield-alt text-3xl text-emerald-400"></i>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Seguranca</h3>
                        <p class="text-gray-300">
                            Integracao com Asaas, gateway certificado. Seus dados e pagamentos estao protegidos.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 rounded-xl p-6 hover:-translate-y-1 transition-transform duration-300">
                <div class="flex items-start space-x-4">
                    <i class="fas fa-users text-3xl text-blue-400"></i>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Comunidade</h3>
                        <p class="text-gray-300">
                            Construimos uma comunidade solidaria onde todos se ajudam mutuamente.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 rounded-xl p-6 hover:-translate-y-1 transition-transform duration-300">
                <div class="flex items-start space-x-4">
                    <i class="fas fa-rocket text-3xl text-purple-400"></i>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Inovacao</h3>
                        <p class="text-gray-300">
                            Sempre buscando novas formas de melhorar a experiencia de doadores e criadores.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white/10 rounded-xl p-6 hover:-translate-y-1 transition-transform duration-300">
                <div class="flex items-start space-x-4">
                    <i class="fas fa-handshake text-3xl text-yellow-400"></i>
                    <div>
                        <h3 class="text-xl font-bold mb-2">Confianca</h3>
                        <p class="text-gray-300">
                            Transparencia total em cada transacao. Voce sabe exatamente o que acontece com cada real.
                        </p>
                    </div>
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
        <h2 class="text-3xl md:text-4xl font-black mb-6">Faca Parte Dessa Historia</h2>
        <p class="text-xl mb-8 max-w-2xl mx-auto">
            Junte-se a milhares de pessoas que ja transformaram vidas atraves da DoarFazBem
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= base_url('register') ?>"
               class="inline-flex items-center gap-2 px-8 py-4 bg-white text-emerald-600 font-bold text-lg rounded-xl hover:bg-gray-100 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                <i class="fas fa-user-plus text-xl"></i>
                Criar Conta Gratis
            </a>
            <a href="<?= base_url('campaigns') ?>"
               class="inline-flex items-center gap-2 px-8 py-4 bg-emerald-700 text-white font-bold text-lg rounded-xl hover:bg-emerald-800 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                <i class="fas fa-heart text-xl"></i>
                Ver Campanhas
            </a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
