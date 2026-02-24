<!--
    DOAR FAZ BEM - Header / Navbar
    Estilo Vakinha com dropdowns e menu moderno
-->

<header class="bg-white shadow-sm sticky top-0 z-50"
        x-data="{
            mobileMenuOpen: false,
            comoAjudarOpen: false,
            descubraOpen: false,
            comoFuncionaOpen: false,
            userMenuOpen: false
        }">

    <!-- Top Bar de Alerta/Promocao - Cores vibrantes -->
    <div class="bg-gradient-to-r from-orange-500 via-pink-500 to-purple-600 text-white text-sm py-2.5 relative overflow-hidden">
        <!-- Efeito de brilho animado -->
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -skew-x-12 animate-pulse"></div>

        <div class="max-w-7xl mx-auto px-4 flex items-center justify-center gap-3 relative z-10">
            <i class="fas fa-gift text-yellow-300 text-lg animate-bounce"></i>
            <span class="font-medium">
                <strong class="text-yellow-200">🎉 NÚMEROS DA SORTE:</strong>
                Concorra a prêmios incríveis ajudando campanhas!
            </span>
            <a href="<?= base_url('rifas') ?>" class="ml-2 px-4 py-1.5 bg-yellow-400 text-orange-900 rounded-full text-xs font-black hover:bg-yellow-300 hover:scale-105 transition-all shadow-lg">
                Comprar Cotas
            </a>
        </div>
    </div>

    <nav class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">

            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?= base_url('/') ?>" class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-emerald-500" viewBox="0 0 40 40" fill="currentColor">
                        <path d="M20 35.5c-1.1 0-2.1-.4-2.9-1.1L6.8 24.3c-3.9-3.8-3.9-10 0-13.8 1.9-1.8 4.4-2.8 7-2.8s5.1 1 7 2.8l-.8.8.8-.8c1.9-1.8 4.4-2.8 7-2.8s5.1 1 7 2.8c3.9 3.8 3.9 10 0 13.8L23 34.4c-.8.7-1.8 1.1-3 1.1z"/>
                        <circle cx="20" cy="20" r="6" fill="white"/>
                        <path d="M20 17v6M17 20h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">Doar<span class="text-emerald-500">FazBem</span></span>
                </a>
            </div>

            <!-- Menu Desktop - Usar md: ao inves de lg: para aparecer em 768px+ -->
            <div class="hidden md:flex md:items-center md:space-x-1">

                <!-- Dropdown: Como ajudar -->
                <div class="relative" @mouseover="comoAjudarOpen = true" @mouseleave="comoAjudarOpen = false">
                    <button class="flex items-center px-3 py-2 text-gray-700 hover:text-emerald-600 font-medium transition-colors text-sm">
                        Como ajudar
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="comoAjudarOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="absolute left-0 mt-0 w-64 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50"
                         style="display: none;">
                        <a href="<?= base_url('campaigns') ?>" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-hand-holding-heart text-emerald-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Fazer uma doacao</div>
                                <div class="text-xs text-gray-500">Apoie quem precisa</div>
                            </div>
                        </a>
                        <a href="<?= base_url('campaigns?category=medica') ?>" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-heartbeat text-red-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Campanhas Medicas</div>
                                <div class="text-xs text-gray-500">Taxa zero</div>
                            </div>
                        </a>
                        <a href="<?= base_url('campaigns?category=social') ?>" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-hands-helping text-blue-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Campanhas Sociais</div>
                                <div class="text-xs text-gray-500">ONGs e projetos</div>
                            </div>
                        </a>
                        <div class="border-t border-gray-100 my-2"></div>
                        <a href="<?= base_url('rifas') ?>" class="flex items-center px-4 py-3 hover:scale-[1.02] transition-all bg-gradient-to-r from-orange-50 via-pink-50 to-purple-50 border border-pink-100 rounded-lg mx-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-pink-500 rounded-lg flex items-center justify-center mr-3 shadow">
                                <i class="fas fa-gift text-white"></i>
                            </div>
                            <div>
                                <div class="font-bold text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-pink-600">Números da Sorte</div>
                                <div class="text-xs text-purple-600">Concorra a prêmios!</div>
                            </div>
                            <span class="ml-auto px-2 py-1 bg-gradient-to-r from-yellow-400 to-orange-400 text-white text-xs font-bold rounded-full animate-pulse shadow">🎉 NOVO</span>
                        </a>
                    </div>
                </div>

                <!-- Dropdown: Descubra -->
                <div class="relative" @mouseover="descubraOpen = true" @mouseleave="descubraOpen = false">
                    <button class="flex items-center px-3 py-2 text-gray-700 hover:text-emerald-600 font-medium transition-colors text-sm">
                        Descubra
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="descubraOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="absolute left-0 mt-0 w-64 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50"
                         style="display: none;">
                        <a href="<?= base_url('campaigns') ?>" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-th-large text-teal-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Todas as campanhas</div>
                                <div class="text-xs text-gray-500">Explore e apoie</div>
                            </div>
                        </a>
                        <a href="<?= base_url('sobre') ?>" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-info-circle text-emerald-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Sobre nos</div>
                                <div class="text-xs text-gray-500">Nossa historia</div>
                            </div>
                        </a>
                        <a href="https://doarfazbem.com.br/blog" target="_blank" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-newspaper text-orange-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Blog</div>
                                <div class="text-xs text-gray-500">Dicas e historias</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Dropdown: Como funciona -->
                <div class="relative" @mouseover="comoFuncionaOpen = true" @mouseleave="comoFuncionaOpen = false">
                    <button class="flex items-center px-3 py-2 text-gray-700 hover:text-emerald-600 font-medium transition-colors text-sm">
                        Como funciona
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="comoFuncionaOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="absolute left-0 mt-0 w-64 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50"
                         style="display: none;">
                        <a href="<?= base_url('como-funciona') ?>" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-play-circle text-emerald-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Passo a passo</div>
                                <div class="text-xs text-gray-500">Crie em 5 minutos</div>
                            </div>
                        </a>
                        <a href="https://doarfazbem.com.br/perguntas-frequentes" target="_blank" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-question-circle text-yellow-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Perguntas frequentes</div>
                                <div class="text-xs text-gray-500">Tire suas duvidas</div>
                            </div>
                        </a>
                        <a href="https://doarfazbem.com.br/contato" target="_blank" class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-headset text-blue-600"></i>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Suporte</div>
                                <div class="text-xs text-gray-500">Fale conosco</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Botao Buscar -->
                <a href="<?= base_url('campaigns') ?>" class="flex items-center px-3 py-2 text-emerald-600 hover:text-emerald-700 font-medium transition-colors text-sm">
                    <i class="fas fa-search mr-2"></i>
                    Buscar
                </a>

            </div>

            <!-- Acoes do Usuario - Desktop -->
            <div class="hidden md:flex md:items-center md:space-x-4">
                <?php if (session()->get('isLoggedIn')): ?>
                    <!-- Menu de Usuario Logado -->
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 text-gray-700 hover:text-emerald-600 focus:outline-none">
                            <img src="<?= session()->get('avatar') ?? base_url('assets/images/default-avatar.png') ?>"
                                 alt="<?= esc(explode(' ', session()->get('name'))[0]) ?>"
                                 class="w-8 h-8 rounded-full border-2 border-emerald-200 hover:border-emerald-400 transition-colors">
                            <span class="font-medium text-sm"><?= esc(explode(' ', session()->get('name'))[0]) ?></span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu Usuario -->
                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-2 z-50"
                             style="display: none;">
                            <a href="<?= base_url('dashboard') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                                <i class="fas fa-chart-line mr-2 text-emerald-500"></i> Meu Dashboard
                            </a>
                            <a href="<?= base_url('dashboard/my-campaigns') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                                <i class="fas fa-bullhorn mr-2 text-emerald-500"></i> Minhas Campanhas
                            </a>
                            <a href="<?= base_url('dashboard/my-donations') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                                <i class="fas fa-heart mr-2 text-red-500"></i> Minhas Doacoes
                            </a>
                            <a href="<?= base_url('profile') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                                <i class="fas fa-user mr-2 text-emerald-500"></i> Meu Perfil
                            </a>
                            <?php if (in_array(session()->get('role'), ['admin', 'superadmin'])): ?>
                                <hr class="my-2">
                                <a href="<?= base_url('admin') ?>" class="block px-4 py-2 text-blue-600 hover:bg-blue-50 transition-colors font-semibold text-sm">
                                    <i class="fas fa-shield-alt mr-2"></i> Admin
                                </a>
                            <?php endif; ?>
                            <hr class="my-2">
                            <a href="<?= base_url('logout') ?>" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors text-sm">
                                <i class="fas fa-sign-out-alt mr-2"></i> Sair
                            </a>
                        </div>
                    </div>

                    <!-- Botao Criar Campanha -->
                    <a href="<?= base_url('campaigns/create') ?>" class="px-4 py-2 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition-colors shadow-sm text-sm">
                        Criar campanha
                    </a>

                <?php else: ?>
                    <a href="<?= base_url('login') ?>" class="px-3 py-2 text-gray-700 hover:text-emerald-600 font-medium transition-colors text-sm">
                        Entrar
                    </a>
                    <a href="<?= base_url('register') ?>" class="px-5 py-2.5 bg-emerald-500 text-white font-semibold rounded-lg hover:bg-emerald-600 transition-colors shadow-sm text-sm whitespace-nowrap">
                        Criar campanha
                    </a>
                <?php endif; ?>
            </div>

            <!-- Botao Menu Mobile -->
            <div class="md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-emerald-600 focus:outline-none p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menu Mobile -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="md:hidden pb-4 border-t border-gray-100"
             style="display: none;">

            <div class="pt-4 space-y-2">
                <!-- Links Mobile -->
                <a href="<?= base_url('campaigns') ?>" class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors font-medium">
                    <i class="fas fa-hand-holding-heart mr-3 text-emerald-500"></i> Fazer uma doacao
                </a>
                <a href="<?= base_url('campaigns/create') ?>" class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors font-medium">
                    <i class="fas fa-plus-circle mr-3 text-emerald-500"></i> Criar campanha
                </a>
                <a href="<?= base_url('como-funciona') ?>" class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors font-medium">
                    <i class="fas fa-play-circle mr-3 text-emerald-500"></i> Como funciona
                </a>
                <a href="<?= base_url('sobre') ?>" class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors font-medium">
                    <i class="fas fa-info-circle mr-3 text-emerald-500"></i> Sobre nos
                </a>

                <hr class="my-3">

                <?php if (session()->get('isLoggedIn')): ?>
                    <a href="<?= base_url('dashboard') ?>" class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors">
                        <i class="fas fa-chart-line mr-3 text-emerald-500"></i> Meu Dashboard
                    </a>
                    <a href="<?= base_url('profile') ?>" class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors">
                        <i class="fas fa-user mr-3 text-emerald-500"></i> Meu Perfil
                    </a>
                    <a href="<?= base_url('logout') ?>" class="block px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt mr-3"></i> Sair
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('login') ?>" class="block px-4 py-3 text-gray-700 hover:bg-emerald-50 rounded-lg transition-colors font-medium">
                        <i class="fas fa-sign-in-alt mr-3 text-emerald-500"></i> Entrar
                    </a>
                    <a href="<?= base_url('register') ?>" class="block px-4 py-3 bg-emerald-500 text-white rounded-lg font-semibold text-center hover:bg-emerald-600 transition-colors">
                        Cadastrar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
