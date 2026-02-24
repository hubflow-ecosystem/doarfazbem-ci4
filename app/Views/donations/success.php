<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-50 py-12">
    <div class="container-custom max-w-2xl">

        <!-- Animação de Sucesso -->
        <div class="text-center mb-8">
            <div class="inline-block relative">
                <div class="absolute inset-0 bg-green-200 rounded-full animate-ping opacity-25"></div>
                <div class="relative p-6 bg-green-500 rounded-full">
                    <i class="fas fa-heart text-6xl text-white"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-8">

            <!-- Mensagem de Sucesso -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-3">
                    Doação Realizada com Sucesso!
                </h1>
                <p class="text-lg text-gray-600 mb-6">
                    Obrigado por fazer a diferença! Sua generosidade vai ajudar muito.
                </p>

                <!-- Checkmark animado -->
                <div class="inline-flex items-center gap-2 px-6 py-3 bg-green-100 text-green-800 rounded-full">
                    <i class="fas fa-check-circle text-2xl"></i>
                    <span class="font-semibold text-lg">Pagamento Confirmado</span>
                </div>
            </div>

            <!-- Detalhes da Doação -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-8">
                <h3 class="font-bold text-gray-900 mb-4 text-center">
                    Detalhes da Doação
                </h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Campanha:</span>
                        <span class="font-semibold text-gray-900 text-right max-w-xs">
                            <?= esc($campaign['title']) ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Valor pago:</span>
                        <span class="font-bold text-green-600 text-xl">
                            R$ <?= number_format($donation['charged_amount'], 2, ',', '.') ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Método:</span>
                        <span class="font-semibold text-gray-900">
                            <?= $donation['payment_method'] === 'pix' ? 'PIX' : ($donation['payment_method'] === 'credit_card' ? 'Cartão de Crédito' : 'Boleto') ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2 border-b border-green-200">
                        <span class="text-gray-600">Data:</span>
                        <span class="font-semibold text-gray-900">
                            <?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">ID da Doação:</span>
                        <span class="font-mono text-sm text-gray-900">
                            #<?= str_pad($donation['id'], 6, '0', STR_PAD_LEFT) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Mensagem do Doador (se houver) -->
            <?php if (!empty($donation['message'])): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                    <h3 class="font-bold text-blue-900 mb-2 flex items-center">
                        <i class="fas fa-comment-dots mr-2"></i>
                        Sua mensagem:
                    </h3>
                    <p class="text-blue-800 italic">
                        "<?= esc($donation['message']) ?>"
                    </p>
                </div>
            <?php endif; ?>

            <!-- Email de Confirmação -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-8 text-center">
                <i class="fas fa-envelope text-purple-500 text-2xl mb-2"></i>
                <p class="text-sm text-purple-800">
                    Um email de confirmação foi enviado para:
                    <strong><?= esc($donation['donor_email']) ?></strong>
                </p>
            </div>

            <!-- Impacto -->
            <div class="text-center mb-8">
                <div class="inline-block p-8 bg-gradient-to-br from-yellow-100 to-orange-100 rounded-2xl">
                    <i class="fas fa-star text-4xl text-yellow-500 mb-3"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        Você fez a diferença!
                    </h3>
                    <p class="text-gray-700 text-sm max-w-md mx-auto">
                        Sua doação vai ajudar diretamente esta causa. Obrigado por acreditar e contribuir!
                    </p>
                </div>
            </div>

            <!-- Compartilhar -->
            <div class="border-t border-gray-200 pt-8 mb-8">
                <h3 class="font-bold text-gray-900 mb-4 text-center">
                    <i class="fas fa-share-alt mr-2 text-primary-500"></i>
                    Compartilhe esta campanha
                </h3>
                <p class="text-sm text-gray-600 text-center mb-4">
                    Ajude a divulgar e alcançar mais pessoas!
                </p>

                <div class="flex justify-center gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(base_url('campaigns/' . $campaign['slug'])) ?>"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fab fa-facebook-f"></i>
                        <span class="hidden sm:inline">Facebook</span>
                    </a>

                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(base_url('campaigns/' . $campaign['slug'])) ?>&text=<?= urlencode('Apoie esta causa: ' . $campaign['title']) ?>"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors">
                        <i class="fab fa-twitter"></i>
                        <span class="hidden sm:inline">Twitter</span>
                    </a>

                    <a href="https://wa.me/?text=<?= urlencode('Apoie esta causa: ' . $campaign['title'] . ' - ' . base_url('campaigns/' . $campaign['slug'])) ?>"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fab fa-whatsapp"></i>
                        <span class="hidden sm:inline">WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>"
                   class="flex-1 btn-primary text-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Ver Campanha
                </a>

                <a href="<?= base_url('campaigns') ?>"
                   class="flex-1 btn-outline text-center">
                    <i class="fas fa-heart mr-2"></i>
                    Apoiar Outras Causas
                </a>
            </div>

            <!-- Mensagem Final -->
            <div class="text-center mt-8 pt-8 border-t border-gray-200">
                <p class="text-gray-600 text-sm">
                    <i class="fas fa-heart text-red-500 mr-1"></i>
                    Juntos somos mais fortes. Obrigado por fazer parte desta história!
                </p>
            </div>

        </div>

        <!-- Redirecionamento IMEDIATO para campanha da plataforma (se checkbox marcado) -->
        <?php if (!empty($donation['donate_to_platform'])): ?>
        <script>
        // Salvar flag na sessionStorage para mostrar popup na próxima página
        sessionStorage.setItem('show_platform_donation_popup', '1');
        // Redirecionar IMEDIATAMENTE
        window.location.href = '<?= base_url('campaigns/mantenha-a-plataforma-ativa') ?>';
        </script>
        <?php endif; ?>

        <!-- Popup removido daqui - será exibido na página da campanha -->
        <?php if (false): ?>
        <div id="platform-popup"
             x-data="{ show: false }"
             x-init="setTimeout(() => show = true, 2000)"
             x-show="show"
             x-cloak
             @click.away="show = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">

            <div @click.stop
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative">

                <!-- Botão Fechar -->
                <button @click="show = false"
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>

                <!-- Ícone e Título -->
                <div class="text-center mb-6">
                    <div class="inline-block p-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full mb-4">
                        <i class="fas fa-hands-helping text-4xl text-blue-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Um Último Pedido...
                    </h2>
                    <p class="text-gray-600 text-sm">
                        Você acabou de transformar a vida de alguém com sua doação para
                        <strong class="text-primary-600"><?= esc($campaign['title']) ?></strong>
                    </p>
                </div>

                <!-- Mensagem Emocional -->
                <div class="bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-xl p-6 mb-6">
                    <p class="text-gray-800 leading-relaxed mb-4">
                        <i class="fas fa-heart text-red-500 mr-2"></i>
                        <strong>Mas há milhares de outras histórias esperando por ajuda...</strong>
                    </p>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        A plataforma DoarFazBem conecta pessoas generosas como você a causas que transformam vidas.
                        Não cobramos taxas de campanhas médicas para que <strong>100% das doações</strong> cheguem a quem precisa.
                    </p>
                </div>

                <!-- Valor Sugerido -->
                <div class="bg-green-50 border-2 border-green-300 rounded-xl p-4 mb-6 text-center">
                    <p class="text-sm text-gray-700 mb-2">
                        <strong>Que tal ajudar a manter a plataforma ativa?</strong>
                    </p>
                    <p class="text-xs text-gray-600 mb-3">
                        Valor mínimo: R$ 5,00
                    </p>
                    <div class="flex items-center justify-center gap-2 text-green-700">
                        <i class="fas fa-shield-alt"></i>
                        <span class="text-xs">100% seguro e transparente</span>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex flex-col gap-3">
                    <a href="<?= base_url('campaigns/mantenha-a-plataforma-ativa') ?>"
                       class="btn-primary text-center w-full py-4 text-lg font-bold">
                        <i class="fas fa-hand-holding-heart mr-2"></i>
                        Sim, Quero Ajudar a Plataforma!
                    </a>

                    <button @click="show = false"
                            class="text-gray-500 hover:text-gray-700 text-sm py-2">
                        Talvez depois
                    </button>
                </div>

                <!-- Rodapé -->
                <div class="text-center mt-4">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Sua contribuição mantém nossa infraestrutura, servidores e desenvolvimento de novas funcionalidades.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estatísticas da Doação -->
        <div class="mt-8 grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-4 text-center shadow-card">
                <i class="fas fa-users text-primary-500 text-2xl mb-2"></i>
                <div class="text-2xl font-bold text-gray-900">
                    <?= $campaign['donors_count'] ?? 0 ?>
                </div>
                <div class="text-xs text-gray-600">Apoiadores</div>
            </div>

            <div class="bg-white rounded-xl p-4 text-center shadow-card">
                <i class="fas fa-chart-line text-green-500 text-2xl mb-2"></i>
                <div class="text-2xl font-bold text-gray-900">
                    <?= number_format($campaign['percentage'] ?? 0, 0) ?>%
                </div>
                <div class="text-xs text-gray-600">Alcançado</div>
            </div>

            <div class="bg-white rounded-xl p-4 text-center shadow-card">
                <i class="fas fa-clock text-orange-500 text-2xl mb-2"></i>
                <div class="text-2xl font-bold text-gray-900">
                    <?= ceil((strtotime($campaign['end_date']) - time()) / 86400) ?>
                </div>
                <div class="text-xs text-gray-600">Dias restantes</div>
            </div>
        </div>

    </div>
</div>

<!-- Confetti Animation (opcional) -->
<script>
// Adicionar confetti ou animação de celebração se desejar
console.log('🎉 Doação confirmada!');
</script>

<!-- Popup de Rifa (exibir apos 3 segundos) -->
<?= view('components/raffle_popup', ['autoShow' => true, 'delay' => 3000]) ?>

<?= $this->endSection() ?>
