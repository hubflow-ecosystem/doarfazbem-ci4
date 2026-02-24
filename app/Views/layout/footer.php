<!--
    DOAR FAZ BEM - Footer
    Footer completo estilo Vakinha com mais informacoes
-->

<footer class="bg-gray-900 text-white mt-16">

    <!-- Secao CTA antes do footer -->
    <div class="bg-emerald-600">
        <div class="container-custom py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-center md:text-left">
                    <h3 class="text-xl md:text-2xl font-bold">Pronto para começar sua campanha?</h3>
                    <p class="text-emerald-100 text-sm mt-1">Cadastro gratuito, sem taxas escondidas</p>
                </div>
                <div class="flex gap-3">
                    <a href="<?= base_url('register') ?>" class="px-6 py-3 bg-white text-emerald-600 font-bold rounded-lg hover:bg-gray-100 transition-colors">
                        Criar Campanha Grátis
                    </a>
                    <a href="<?= base_url('campaigns') ?>" class="px-6 py-3 bg-emerald-700 text-white font-bold rounded-lg hover:bg-emerald-800 transition-colors">
                        Ver Campanhas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Principal -->
    <div class="container-custom py-12">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">

            <!-- Coluna 1: Logo e Descricao -->
            <div class="col-span-2 md:col-span-3 lg:col-span-2">
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-heart text-primary-500 text-2xl"></i>
                    <span class="text-xl font-bold">DoarFazBem</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-4">
                    A plataforma de crowdfunding mais justa do Brasil. Campanhas medicas 100% gratuitas e apenas 2% de taxa para todas as outras categorias.
                </p>

                <!-- Selos de Confianca -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="bg-emerald-900/50 text-emerald-400 px-3 py-1 rounded-full text-xs font-semibold">
                        <i class="fas fa-shield-alt mr-1"></i> Site Seguro
                    </span>
                    <span class="bg-emerald-900/50 text-emerald-400 px-3 py-1 rounded-full text-xs font-semibold">
                        <i class="fas fa-lock mr-1"></i> SSL
                    </span>
                </div>

                <!-- Redes Sociais -->
                <div class="flex space-x-3">
                    <a href="https://facebook.com/doarfazbem" target="_blank" class="w-9 h-9 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition-colors" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://instagram.com/doarfazbem" target="_blank" class="w-9 h-9 bg-gray-800 hover:bg-pink-600 rounded-lg flex items-center justify-center transition-colors" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://twitter.com/doarfazbem" target="_blank" class="w-9 h-9 bg-gray-800 hover:bg-sky-500 rounded-lg flex items-center justify-center transition-colors" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://youtube.com/doarfazbem" target="_blank" class="w-9 h-9 bg-gray-800 hover:bg-red-600 rounded-lg flex items-center justify-center transition-colors" aria-label="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://wa.me/5547996966724" target="_blank" class="w-9 h-9 bg-gray-800 hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- Coluna 2: Campanhas -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4">Campanhas</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?= base_url('campaigns?category=medica') ?>" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                            <i class="fas fa-heartbeat text-red-400 w-4"></i> Medicas
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('campaigns?category=social') ?>" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                            <i class="fas fa-hands-helping text-blue-400 w-4"></i> Sociais
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('campaigns?category=educacao') ?>" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                            <i class="fas fa-graduation-cap text-purple-400 w-4"></i> Educacao
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('campaigns?category=negocio') ?>" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                            <i class="fas fa-briefcase text-yellow-400 w-4"></i> Negocios
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('campaigns?category=criativa') ?>" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                            <i class="fas fa-palette text-pink-400 w-4"></i> Criativas
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('campaigns') ?>" class="text-emerald-400 hover:text-emerald-300 transition-colors text-sm font-semibold">
                            Ver todas &rarr;
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Coluna 3: Plataforma -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4">Plataforma</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?= base_url('como-funciona') ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Como Funciona
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('campaigns/create') ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Criar Campanha
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('sobre') ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Sobre Nos
                        </a>
                    </li>
                    <li>
                        <a href="https://doarfazbem.com.br/blog" rel="dofollow" target="_blank" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Blog
                        </a>
                    </li>
                    <li>
                        <a href="https://doarfazbem.com.br/glossario" rel="dofollow" target="_blank" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Glossario
                        </a>
                    </li>
                    <li>
                        <a href="https://doarfazbem.com.br/perguntas-frequentes" rel="dofollow" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Perguntas Frequentes
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Coluna 4: Legal -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4">Legal</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="https://doarfazbem.com.br/termos-de-uso" rel="dofollow" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Termos de Uso
                        </a>
                    </li>
                    <li>
                        <a href="https://doarfazbem.com.br/politica-de-privacidade" rel="dofollow" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Privacidade
                        </a>
                    </li>
                    <li>
                        <a href="https://doarfazbem.com.br/politica-de-cookies" rel="dofollow" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Cookies
                        </a>
                    </li>
                    <li>
                        <a href="https://doarfazbem.com.br/contato" rel="dofollow" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Contato
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('doe-para-plataforma') ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Apoie a Plataforma
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('anuncie-conosco') ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Anuncie Conosco
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('mapa-do-site') ?>" class="text-gray-400 hover:text-white transition-colors text-sm">
                            Mapa do Site
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Coluna 5: Contato -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4">Contato</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="mailto:contato@doarfazbem.com.br" class="text-gray-400 hover:text-white transition-colors text-sm flex items-start gap-2">
                            <i class="fas fa-envelope mt-0.5 text-emerald-400"></i>
                            <span>contato@doarfazbem.com.br</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://wa.me/5547996966724" target="_blank" class="text-gray-400 hover:text-white transition-colors text-sm flex items-start gap-2">
                            <i class="fab fa-whatsapp mt-0.5 text-green-400"></i>
                            <span>(47) 99696-6724</span>
                        </a>
                    </li>
                    <li class="text-gray-500 text-xs mt-4">
                        Atendimento:<br>
                        Seg a Sex: 9h as 18h
                    </li>
                </ul>

                <!-- Metodos de Pagamento -->
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-3 mt-6">Pagamentos</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="bg-gray-800 px-2 py-1 rounded text-xs text-gray-400">
                        <i class="fas fa-qrcode mr-1"></i> PIX
                    </span>
                    <span class="bg-gray-800 px-2 py-1 rounded text-xs text-gray-400">
                        <i class="fas fa-credit-card mr-1"></i> Cartao
                    </span>
                    <span class="bg-gray-800 px-2 py-1 rounded text-xs text-gray-400">
                        <i class="fas fa-barcode mr-1"></i> Boleto
                    </span>
                </div>
            </div>
        </div>

        <!-- Separador com informacoes extras -->
        <div class="border-t border-gray-800 mt-10 pt-8">

            <!-- Diferenciais -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="text-center p-4 bg-gray-800/50 rounded-lg">
                    <i class="fas fa-hand-holding-heart text-2xl text-emerald-400 mb-2"></i>
                    <div class="text-sm font-semibold">Taxa Zero</div>
                    <div class="text-xs text-gray-500">Campanhas Medicas</div>
                </div>
                <div class="text-center p-4 bg-gray-800/50 rounded-lg">
                    <i class="fas fa-bolt text-2xl text-yellow-400 mb-2"></i>
                    <div class="text-sm font-semibold">Saque Imediato</div>
                    <div class="text-xs text-gray-500">Sem burocracia</div>
                </div>
                <div class="text-center p-4 bg-gray-800/50 rounded-lg">
                    <i class="fas fa-shield-alt text-2xl text-blue-400 mb-2"></i>
                    <div class="text-sm font-semibold">100% Seguro</div>
                    <div class="text-xs text-gray-500">Dados protegidos</div>
                </div>
                <div class="text-center p-4 bg-gray-800/50 rounded-lg">
                    <i class="fas fa-headset text-2xl text-pink-400 mb-2"></i>
                    <div class="text-sm font-semibold">Suporte</div>
                    <div class="text-xs text-gray-500">Atendimento humanizado</div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-800">
                <div class="text-gray-500 text-sm text-center md:text-left">
                    <p>
                        &copy; <?= date('Y') ?> DoarFazBem. Todos os direitos reservados.
                    </p>
                    <p class="mt-1">
                        CNPJ: 00.000.000/0001-00
                    </p>
                </div>

                <div class="text-gray-500 text-sm text-center md:text-right">
                    <p>
                        Feito com <i class="fas fa-heart text-red-500 animate-pulse"></i> por
                        <a href="https://cmnegociosdigitais.com.br"
                           rel="dofollow"
                           target="_blank"
                           class="text-emerald-400 hover:text-emerald-300 font-semibold transition-colors">
                            CM Negocios Digitais
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>
