<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<!-- ============================================ -->
<!-- HERO SECTION - Texto + Carrossel de Imagens -->
<!-- ============================================ -->
<section class="bg-white py-8 lg:py-16">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">

            <!-- Texto do Hero - Fixo -->
            <div class="text-center lg:text-left">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-gray-900 mb-6 leading-tight">
                    A plataforma de<br>
                    <span class="text-emerald-500">crowdfunding mais justa</span><br>
                    do Brasil
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                    <strong class="text-emerald-600">Taxa ZERO</strong> para campanhas medicas e apenas <strong class="text-emerald-600">2%</strong> para outras categorias. Sem surpresas, sem taxas escondidas.
                </p>

                <!-- Botoes CTA -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="<?= base_url('register') ?>"
                       class="group px-8 py-4 bg-emerald-500 text-white font-bold text-lg rounded-xl hover:bg-emerald-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1 flex items-center justify-center gap-2">
                        <i class="fas fa-plus-circle text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                        Criar campanha - É grátis!
                    </a>
                    <a href="<?= base_url('campaigns') ?>"
                       class="group px-8 py-4 bg-white text-gray-900 font-bold text-lg rounded-xl border-2 border-gray-300 hover:border-emerald-500 hover:text-emerald-600 transition-all duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-heart text-xl text-red-500 group-hover:scale-110 transition-transform"></i>
                        Quero doar
                    </a>
                </div>

                <!-- Badge de Confianca -->
                <div class="mt-6 flex flex-wrap justify-center lg:justify-start gap-3">
                    <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-check-circle mr-1"></i> Sem taxas escondidas
                    </span>
                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-shield-alt mr-1"></i> 100% seguro
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
                    <!-- Imagem 1: Saude -->
                    <div x-show="currentSlide === 0"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <img src="<?= base_url('assets/images/illustrations/home/hero-slide-1-saude.png') ?>"
                             alt="Campanhas Médicas - Taxa ZERO"
                             class="w-full h-80 md:h-96 object-cover">
                    </div>

                    <!-- Imagem 2: Educacao -->
                    <div x-show="currentSlide === 1"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <img src="<?= base_url('assets/images/illustrations/home/hero-slide-2-educacao.png') ?>"
                             alt="Educação - Apenas 2% de taxa"
                             class="w-full h-80 md:h-96 object-cover">
                    </div>

                    <!-- Imagem 3: Social -->
                    <div x-show="currentSlide === 2"
                         x-transition:enter="transition ease-out duration-500"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <img src="<?= base_url('assets/images/illustrations/home/hero-slide-3-social.png') ?>"
                             alt="Projetos Sociais - Ajude quem precisa"
                             class="w-full h-80 md:h-96 object-cover">
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
<!-- BARRA DE BUSCA GRANDE - Estilo Vakinha -->
<!-- ============================================ -->
<section class="bg-gray-50 py-8 border-y border-gray-200">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <form action="<?= base_url('campaigns') ?>" method="GET"
              class="bg-white rounded-xl shadow-lg p-4 md:p-6 max-w-5xl mx-auto">
            <div class="grid md:grid-cols-4 gap-4">
                <!-- Campo de Busca -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar campanhas</label>
                    <input type="text"
                           name="search"
                           placeholder="O que voce esta procurando?"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                </div>

                <!-- Categoria -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Categoria</label>
                    <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                        <option value="">Todas as categorias</option>
                        <option value="medica">Medica</option>
                        <option value="social">Social</option>
                        <option value="educacao">Educacao</option>
                        <option value="negocio">Negocio</option>
                        <option value="criativa">Criativa</option>
                    </select>
                </div>

                <!-- Botao Buscar -->
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full px-6 py-3 bg-gray-900 text-white font-bold rounded-lg hover:bg-emerald-600 transition-all duration-300 flex items-center justify-center gap-2 hover:shadow-lg">
                        <i class="fas fa-search"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- ============================================ -->
<!-- ESTATISTICAS ANIMADAS -->
<!-- ============================================ -->
<section class="py-12 bg-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 max-w-4xl mx-auto"
             x-data="{
                 totalRaised: 0,
                 totalCampaigns: 0,
                 totalUsers: 0,
                 totalDonors: 0,
                 targetRaised: <?= $stats['total_raised'] ?>,
                 targetCampaigns: <?= $stats['total_campaigns'] ?>,
                 targetUsers: <?= $stats['total_users'] ?>,
                 targetDonors: <?= $stats['total_donors'] ?>,
                 formatMoney(value) {
                     if (value >= 1000000) return 'R$ ' + (value / 1000000).toFixed(1) + 'M';
                     if (value >= 1000) return 'R$ ' + (value / 1000).toFixed(1) + 'K';
                     return 'R$ ' + value.toFixed(0);
                 },
                 animateNumbers() {
                     const duration = 2000;
                     const steps = 60;
                     let step = 0;
                     const timer = setInterval(() => {
                         step++;
                         const progress = step / steps;
                         this.totalRaised = this.targetRaised * progress;
                         this.totalCampaigns = Math.floor(this.targetCampaigns * progress);
                         this.totalUsers = Math.floor(this.targetUsers * progress);
                         this.totalDonors = Math.floor(this.targetDonors * progress);
                         if (step >= steps) {
                             clearInterval(timer);
                             this.totalRaised = this.targetRaised;
                             this.totalCampaigns = this.targetCampaigns;
                             this.totalUsers = this.targetUsers;
                             this.totalDonors = this.targetDonors;
                         }
                     }, duration / steps);
                 }
             }"
             x-init="setTimeout(() => animateNumbers(), 300)"
             x-intersect="animateNumbers()">

            <div class="text-center">
                <div class="text-2xl md:text-3xl font-black text-emerald-600" x-text="formatMoney(totalRaised)"></div>
                <div class="text-sm text-gray-600 font-medium">Arrecadado</div>
            </div>
            <div class="text-center">
                <div class="text-2xl md:text-3xl font-black text-emerald-600" x-text="totalCampaigns"></div>
                <div class="text-sm text-gray-600 font-medium">Campanhas</div>
            </div>
            <div class="text-center">
                <div class="text-2xl md:text-3xl font-black text-emerald-600" x-text="totalUsers"></div>
                <div class="text-sm text-gray-600 font-medium">Criadores</div>
            </div>
            <div class="text-center">
                <div class="text-2xl md:text-3xl font-black text-emerald-600" x-text="totalDonors"></div>
                <div class="text-sm text-gray-600 font-medium">Doadores</div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- CATEGORIAS COM ICONES SVG -->
<!-- ============================================ -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                Crie sua campanha
            </h2>
            <p class="text-lg text-gray-600">
                Escolha a categoria que mais combina com sua necessidade
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 max-w-5xl mx-auto">
            <!-- Medica -->
            <a href="<?= base_url('campaigns/create?category=medica') ?>"
               class="group bg-white rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-emerald-200 hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center group-hover:bg-red-200 transition-colors group-hover:scale-110">
                    <svg class="w-8 h-8 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                </div>
                <div class="font-semibold text-gray-900 mb-1">Medica</div>
                <div class="text-xs text-emerald-600 font-bold">Taxa Zero</div>
            </a>

            <!-- Social -->
            <a href="<?= base_url('campaigns/create?category=social') ?>"
               class="group bg-white rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-emerald-200 hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition-colors group-hover:scale-110">
                    <svg class="w-8 h-8 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                </div>
                <div class="font-semibold text-gray-900 mb-1">Social</div>
                <div class="text-xs text-gray-500">2% taxa</div>
            </a>

            <!-- Educacao -->
            <a href="<?= base_url('campaigns/create?category=educacao') ?>"
               class="group bg-white rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-emerald-200 hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition-colors group-hover:scale-110">
                    <svg class="w-8 h-8 text-purple-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                </div>
                <div class="font-semibold text-gray-900 mb-1">Educacao</div>
                <div class="text-xs text-gray-500">2% taxa</div>
            </a>

            <!-- Negocio -->
            <a href="<?= base_url('campaigns/create?category=negocio') ?>"
               class="group bg-white rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-emerald-200 hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center group-hover:bg-yellow-200 transition-colors group-hover:scale-110">
                    <svg class="w-8 h-8 text-yellow-600" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                    </svg>
                </div>
                <div class="font-semibold text-gray-900 mb-1">Negocio</div>
                <div class="text-xs text-gray-500">2% taxa</div>
            </a>

            <!-- Criativa -->
            <a href="<?= base_url('campaigns/create?category=criativa') ?>"
               class="group bg-white rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-emerald-200 hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-pink-100 rounded-full flex items-center justify-center group-hover:bg-pink-200 transition-colors group-hover:scale-110">
                    <svg class="w-8 h-8 text-pink-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                    </svg>
                </div>
                <div class="font-semibold text-gray-900 mb-1">Criativa</div>
                <div class="text-xs text-gray-500">2% taxa</div>
            </a>

            <!-- Esporte -->
            <a href="<?= base_url('campaigns/create?category=social') ?>"
               class="group bg-white rounded-xl p-6 text-center hover:shadow-lg transition-all duration-300 border border-gray-100 hover:border-emerald-200 hover:-translate-y-1">
                <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center group-hover:bg-green-200 transition-colors group-hover:scale-110">
                    <svg class="w-8 h-8 text-green-600" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <div class="font-semibold text-gray-900 mb-1">Esporte</div>
                <div class="text-xs text-gray-500">2% taxa</div>
            </a>
        </div>
    </div>
</section>

<?php if (!empty($campaigns)): ?>
<!-- ============================================ -->
<!-- CAMPANHAS EM DESTAQUE - Cards Melhorados -->
<!-- ============================================ -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                Campanhas em destaque
            </h2>
            <p class="text-lg text-gray-600">
                Conheca historias reais e faca a diferenca
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <?php foreach (array_slice($campaigns, 0, 6) as $campaign): ?>
            <div class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 overflow-hidden border border-gray-100 hover:-translate-y-2">
                <!-- Link na imagem -->
                <a href="<?= base_url('campaign/' . $campaign['id']) ?>" class="block relative">
                    <!-- Imagem -->
                    <div class="relative h-52 bg-gradient-to-br from-emerald-400 to-teal-500 overflow-hidden">
                        <?php if (!empty($campaign['image'])): ?>
                            <img src="<?= base_url('uploads/campaigns/' . $campaign['image']) ?>"
                                 alt="<?= esc($campaign['title']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full">
                                <svg class="w-20 h-20 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        <?php endif; ?>

                        <!-- Overlay gradient -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <?php if ($campaign['category'] === 'medica'): ?>
                        <div class="absolute top-4 left-4 bg-red-500 text-white px-3 py-1.5 rounded-full text-xs font-bold shadow-lg flex items-center gap-1">
                            <i class="fas fa-heart"></i> Taxa Zero
                        </div>
                        <?php endif; ?>

                        <!-- Porcentagem no hover -->
                        <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-full text-sm font-bold text-emerald-600 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                            <?= min(100, round($campaign['percentage'])) ?>% arrecadado
                        </div>
                    </div>
                </a>

                <!-- Conteudo -->
                <div class="p-6">
                    <a href="<?= base_url('campaign/' . $campaign['id']) ?>" class="block">
                        <h3 class="font-bold text-lg text-gray-900 mb-3 line-clamp-2 group-hover:text-emerald-600 transition-colors duration-300 min-h-[56px]">
                            <?= esc($campaign['title']) ?>
                        </h3>
                    </a>

                    <!-- Valores e Progresso -->
                    <div class="mb-4">
                        <div class="flex justify-between items-baseline mb-2">
                            <span class="text-xl font-black text-gray-900">
                                R$ <?= number_format($campaign['raised'], 0, ',', '.') ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                de R$ <?= number_format($campaign['goal_amount'], 0, ',', '.') ?>
                            </span>
                        </div>
                        <div class="w-full h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-1000"
                                 style="width: <?= min(100, $campaign['percentage']) ?>%"></div>
                        </div>
                    </div>

                    <!-- Info e CTA -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-users mr-2 text-emerald-500"></i>
                            <?= $campaign['donors_count'] ?> apoiadores
                        </div>
                        <a href="<?= base_url('donate/' . $campaign['id']) ?>"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white text-sm font-bold rounded-lg hover:bg-emerald-600 transition-all duration-300 hover:shadow-md">
                            <i class="fas fa-heart"></i>
                            Apoiar
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="<?= base_url('campaigns') ?>"
               class="group inline-flex items-center gap-3 px-8 py-4 bg-gray-900 text-white font-bold rounded-xl hover:bg-emerald-600 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                Ver todas as campanhas
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================ -->
<!-- COMO FUNCIONA - 3 Passos -->
<!-- ============================================ -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                Como funciona?
            </h2>
            <p class="text-lg text-gray-600">
                Simples, rapido e seguro
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <!-- Passo 1 -->
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto mb-6 bg-emerald-100 rounded-full flex items-center justify-center group-hover:bg-emerald-200 transition-colors group-hover:scale-110 transition-transform duration-300">
                    <span class="text-3xl font-black text-emerald-600">1</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Crie sua campanha</h3>
                <p class="text-gray-600">
                    Cadastre-se grátis e conte sua história em menos de 5 minutos
                </p>
            </div>

            <!-- Passo 2 -->
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto mb-6 bg-emerald-100 rounded-full flex items-center justify-center group-hover:bg-emerald-200 transition-colors group-hover:scale-110 transition-transform duration-300">
                    <span class="text-3xl font-black text-emerald-600">2</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Compartilhe</h3>
                <p class="text-gray-600">
                    Divulgue no WhatsApp, Facebook e Instagram para seus amigos
                </p>
            </div>

            <!-- Passo 3 -->
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto mb-6 bg-emerald-100 rounded-full flex items-center justify-center group-hover:bg-emerald-200 transition-colors group-hover:scale-110 transition-transform duration-300">
                    <span class="text-3xl font-black text-emerald-600">3</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Receba</h3>
                <p class="text-gray-600">
                    Acompanhe em tempo real e saque quando quiser
                </p>
            </div>
        </div>

        <div class="text-center mt-10">
            <a href="<?= base_url('register') ?>"
               class="group inline-flex items-center gap-2 px-8 py-4 bg-emerald-500 text-white font-bold text-lg rounded-xl hover:bg-emerald-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                <i class="fas fa-rocket text-xl group-hover:translate-x-1 transition-transform"></i>
                Começar agora - É grátis!
            </a>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- POR QUE ESCOLHER - Icones Maiores -->
<!-- ============================================ -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-4">
                Por que escolher o DoarFazBem?
            </h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-5xl mx-auto">
            <div class="text-center p-6 group hover:bg-red-50 rounded-2xl transition-all duration-300">
                <div class="w-20 h-20 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center group-hover:bg-red-200 transition-colors group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-heart text-red-500 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2 text-lg">Taxa Zero</h3>
                <p class="text-sm text-gray-600">Campanhas medicas 100% gratuitas</p>
            </div>

            <div class="text-center p-6 group hover:bg-emerald-50 rounded-2xl transition-all duration-300">
                <div class="w-20 h-20 mx-auto mb-4 bg-emerald-100 rounded-full flex items-center justify-center group-hover:bg-emerald-200 transition-colors group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-bolt text-emerald-500 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2 text-lg">Saque Imediato</h3>
                <p class="text-sm text-gray-600">Receba quando quiser, sem espera</p>
            </div>

            <div class="text-center p-6 group hover:bg-blue-50 rounded-2xl transition-all duration-300">
                <div class="w-20 h-20 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center group-hover:bg-blue-200 transition-colors group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-shield-alt text-blue-500 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2 text-lg">100% Seguro</h3>
                <p class="text-sm text-gray-600">Seus dados protegidos</p>
            </div>

            <div class="text-center p-6 group hover:bg-purple-50 rounded-2xl transition-all duration-300">
                <div class="w-20 h-20 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center group-hover:bg-purple-200 transition-colors group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-headset text-purple-500 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2 text-lg">Suporte</h3>
                <p class="text-sm text-gray-600">Atendimento humanizado</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- NEWSLETTER -->
<!-- ============================================ -->
<section class="py-16 bg-gray-900">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            <div class="w-16 h-16 mx-auto mb-6 bg-emerald-500 rounded-full flex items-center justify-center">
                <i class="fas fa-envelope text-white text-2xl"></i>
            </div>
            <h2 class="text-2xl md:text-3xl font-black text-white mb-4">
                Fique por dentro das novidades
            </h2>
            <p class="text-gray-400 mb-8">
                Receba dicas, historias inspiradoras e novidades sobre campanhas de sucesso
            </p>

            <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                <input type="email"
                       name="email"
                       placeholder="Seu melhor e-mail"
                       required
                       class="flex-1 px-5 py-4 rounded-xl bg-gray-800 border border-gray-700 text-white placeholder-gray-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/50">
                <button type="submit"
                        class="px-6 py-4 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-paper-plane"></i>
                    Inscrever
                </button>
            </form>

            <p class="text-xs text-gray-500 mt-4">
                <i class="fas fa-lock mr-1"></i> Seus dados estao seguros. Nao enviamos spam.
            </p>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- CTA FINAL -->
<!-- ============================================ -->
<section class="py-20 bg-emerald-600 text-white">
    <div class="container mx-auto px-4 md:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-black mb-6">
            Pronto para transformar vidas?
        </h2>
        <p class="text-xl text-emerald-100 mb-8 max-w-2xl mx-auto">
            Junte-se a milhares de pessoas que ja realizaram seus sonhos com o DoarFazBem
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= base_url('register') ?>"
               class="group px-10 py-4 bg-white text-emerald-600 font-bold text-lg rounded-xl hover:bg-gray-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class="fas fa-plus-circle text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                Criar campanha grátis
            </a>
            <a href="<?= base_url('campaigns') ?>"
               class="group px-10 py-4 bg-emerald-700 text-white font-bold text-lg rounded-xl hover:bg-emerald-800 transition-all duration-300 flex items-center justify-center gap-2">
                <i class="fas fa-search text-xl"></i>
                Ver campanhas
            </a>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- BOTOES FLUTUANTES - WhatsApp e Voltar ao Topo -->
<!-- ============================================ -->
<div class="fixed bottom-6 right-6 flex flex-col gap-3"
     style="z-index: 9999;"
     x-data="{ showBackToTop: false }"
     x-init="window.addEventListener('scroll', () => { showBackToTop = window.scrollY > 500 })">

    <!-- Botao Voltar ao Topo -->
    <button x-show="showBackToTop"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="w-12 h-12 bg-gray-900 text-white rounded-full shadow-lg hover:bg-gray-700 transition-all duration-300 hover:shadow-xl flex items-center justify-center">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Botao WhatsApp - Sempre visivel -->
    <a href="https://wa.me/5547996966724?text=Ola!%20Preciso%20de%20ajuda%20com%20o%20DoarFazBem"
       target="_blank"
       class="w-14 h-14 bg-green-500 text-white rounded-full shadow-lg hover:bg-green-600 transition-all duration-300 hover:shadow-xl hover:scale-110 flex items-center justify-center"
       style="animation: bounce-slow 3s ease-in-out infinite;"
       title="Fale conosco no WhatsApp">
        <i class="fab fa-whatsapp text-2xl"></i>
    </a>
</div>

<style>
    @keyframes bounce-slow {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-5px);
        }
    }
</style>

<?= $this->endSection() ?>
