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
                    Termos de<br>
                    <span class="text-emerald-500">Uso</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                    Ultima atualizacao: <?= date('d/m/Y') ?>
                </p>

                <!-- Badges -->
                <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                    <span class="inline-flex items-center px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-file-contract mr-1"></i> Legal
                    </span>
                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-shield-alt mr-1"></i> Protecao
                    </span>
                </div>
            </div>

            <!-- SVG Ilustracao -->
            <div class="flex justify-center">
                <div class="w-full max-w-md">
                    <svg viewBox="0 0 400 300" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
                        <!-- Fundo -->
                        <rect x="50" y="50" width="300" height="200" rx="20" fill="#f0fdf4"/>

                        <!-- Documento -->
                        <rect x="130" y="80" width="140" height="160" rx="10" fill="white" stroke="#10b981" stroke-width="3"/>

                        <!-- Linhas do documento -->
                        <rect x="150" y="110" width="100" height="8" rx="4" fill="#d1d5db"/>
                        <rect x="150" y="130" width="80" height="8" rx="4" fill="#d1d5db"/>
                        <rect x="150" y="150" width="100" height="8" rx="4" fill="#d1d5db"/>
                        <rect x="150" y="170" width="60" height="8" rx="4" fill="#d1d5db"/>
                        <rect x="150" y="190" width="90" height="8" rx="4" fill="#d1d5db"/>

                        <!-- Check -->
                        <circle cx="280" cy="180" r="30" fill="#10b981"/>
                        <path d="M265 180 L275 190 L295 170" stroke="white" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================ -->
<!-- CONTEUDO DOS TERMOS -->
<!-- ============================================ -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-sm p-8 prose prose-lg max-w-none">

                <h2 class="text-2xl font-bold text-gray-900 mt-0 mb-4">1. Aceitacao dos Termos</h2>
                <p class="text-gray-700 mb-6">
                    Ao acessar e usar a plataforma DoarFazBem, voce concorda com estes Termos de Uso e com nossa Politica de Privacidade. Se voce nao concorda com qualquer parte destes termos, nao deve usar nossa plataforma.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">2. Definicoes</h2>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li><strong>Plataforma:</strong> O site DoarFazBem e todos os seus servicos</li>
                    <li><strong>Usuario:</strong> Qualquer pessoa que acesse a plataforma</li>
                    <li><strong>Criador:</strong> Usuario que cria campanhas de arrecadacao</li>
                    <li><strong>Doador:</strong> Usuario que contribui financeiramente para campanhas</li>
                    <li><strong>Campanha:</strong> Iniciativa de arrecadacao criada na plataforma</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">3. Cadastro e Conta de Usuario</h2>
                <p class="text-gray-700 mb-4">
                    Para criar campanhas, voce deve:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li>Ter no minimo 18 anos ou autorizacao legal de responsavel</li>
                    <li>Fornecer informacoes verdadeiras, precisas e completas</li>
                    <li>Manter suas credenciais de acesso seguras</li>
                    <li>Notificar-nos imediatamente sobre uso nao autorizado da sua conta</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">4. Criacao de Campanhas</h2>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">4.1 Requisitos</h3>
                <p class="text-gray-700 mb-4">
                    O criador de campanha deve:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li>Fornecer informacoes verdadeiras sobre a causa</li>
                    <li>Ter autorizacao legal para arrecadar fundos para o proposito declarado</li>
                    <li>Usar os fundos exclusivamente para o proposito declarado na campanha</li>
                    <li>Fornecer atualizacoes periodicas aos doadores</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">4.2 Categorias e Taxas</h3>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li><strong>Campanhas Medicas:</strong> 0% de taxa da plataforma (taxas do gateway de pagamento se aplicam)</li>
                    <li><strong>Outras Categorias:</strong> Taxa de 2% sobre doacoes recebidas</li>
                </ul>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">4.3 Proibicoes</h3>
                <p class="text-gray-700 mb-4">
                    E expressamente proibido criar campanhas para:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li>Atividades ilegais ou fraudulentas</li>
                    <li>Violacao de direitos de terceiros</li>
                    <li>Discriminacao, odio ou violencia</li>
                    <li>Conteudo sexual ou pornografico</li>
                    <li>Jogos de azar ou investimentos de alto risco</li>
                    <li>Produtos ou servicos ilegais</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">5. Doacoes</h2>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">5.1 Processamento</h3>
                <p class="text-gray-700 mb-6">
                    Todas as doacoes sao processadas por gateways de pagamento terceirizados (Asaas). A DoarFazBem nao armazena dados de cartao de credito.
                </p>

                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">5.2 Reembolsos</h3>
                <p class="text-gray-700 mb-4">
                    Doacoes sao finais e nao reembolsaveis, exceto em casos de:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li>Fraude comprovada</li>
                    <li>Erro no processamento do pagamento</li>
                    <li>Campanha suspensa por violacao de termos</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">6. Responsabilidades da Plataforma</h2>
                <p class="text-gray-700 mb-4">
                    A DoarFazBem:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li>Fornece a infraestrutura tecnologica para criacao e divulgacao de campanhas</li>
                    <li>Realiza moderacao basica de conteudo</li>
                    <li>Processa transferencias de doacoes aos criadores</li>
                    <li>NAO garante o sucesso das campanhas</li>
                    <li>NAO se responsabiliza pelo uso indevido dos fundos arrecadados</li>
                    <li>NAO verifica a veracidade de todas as informacoes das campanhas</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">7. Propriedade Intelectual</h2>
                <p class="text-gray-700 mb-6">
                    Todo o conteudo da plataforma (design, codigo, marca) e de propriedade da DoarFazBem. O conteudo das campanhas (textos, imagens, videos) e de propriedade dos criadores, que concedem a DoarFazBem licenca para exibir e divulgar.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">8. Moderacao e Suspensao</h2>
                <p class="text-gray-700 mb-4">
                    A DoarFazBem reserva o direito de:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li>Revisar e aprovar campanhas antes da publicacao</li>
                    <li>Suspender ou remover campanhas que violem estes termos</li>
                    <li>Suspender ou cancelar contas de usuarios</li>
                    <li>Reter fundos de campanhas suspeitas ate investigacao</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">9. Limitacao de Responsabilidade</h2>
                <p class="text-gray-700 mb-4">
                    A DoarFazBem nao se responsabiliza por:
                </p>
                <ul class="list-disc pl-6 text-gray-700 mb-6 space-y-2">
                    <li>Perdas ou danos decorrentes do uso da plataforma</li>
                    <li>Fraudes ou informacoes falsas nas campanhas</li>
                    <li>Interrupcoes ou erros no servico</li>
                    <li>Disputas entre criadores e doadores</li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">10. Modificacoes dos Termos</h2>
                <p class="text-gray-700 mb-6">
                    Podemos modificar estes termos a qualquer momento. As alteracoes entram em vigor imediatamente apos publicacao. O uso continuado da plataforma constitui aceitacao dos novos termos.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">11. Lei Aplicavel e Foro</h2>
                <p class="text-gray-700 mb-6">
                    Estes termos sao regidos pelas leis brasileiras. Qualquer disputa sera resolvida no foro da comarca de Blumenau - SC.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 mt-8 mb-4">12. Contato</h2>
                <p class="text-gray-700 mb-4">
                    Para duvidas sobre estes termos, entre em contato:
                </p>
                <ul class="list-none text-gray-700 mb-6 space-y-2">
                    <li><strong>Email:</strong> contato@doarfazbem.com.br</li>
                    <li><strong>WhatsApp:</strong> (47) 99696-6724</li>
                </ul>

                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-6 mt-8 rounded-r-lg">
                    <p class="text-emerald-800 font-semibold">
                        <i class="fas fa-check-circle mr-2"></i> Ao usar a DoarFazBem, voce concorda com todos os termos descritos acima.
                    </p>
                </div>

            </div>

            <!-- Voltar -->
            <div class="text-center mt-8">
                <a href="<?= base_url('/') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors">
                    <i class="fas fa-arrow-left"></i> Voltar para Home
                </a>
            </div>

        </div>
    </div>
</section>

<?= $this->endSection() ?>
