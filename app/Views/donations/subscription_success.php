<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen py-12">
    <div class="container-custom max-w-2xl">
        <!-- Card de Sucesso -->
        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <!-- Ícone de Sucesso -->
            <div class="mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-4xl text-green-600"></i>
                </div>
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto -mt-8 ml-12">
                    <i class="fas fa-redo text-2xl text-blue-600"></i>
                </div>
            </div>

            <!-- Título -->
            <h1 class="text-heading-1 text-gray-900 mb-4">
                Assinatura Criada com Sucesso!
            </h1>

            <p class="text-lg text-gray-700 mb-6">
                Sua doação recorrente foi configurada e está <strong class="text-green-600">ativa</strong>!
            </p>

            <!-- Detalhes da Assinatura -->
            <?php if (isset($subscription)): ?>
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-6 text-left">
                <h3 class="text-lg font-bold text-gray-900 mb-4 text-center">
                    <i class="fas fa-info-circle text-blue-600"></i> Detalhes da Assinatura
                </h3>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Valor mensal:</span>
                        <span class="font-bold text-gray-900">R$ <?= number_format($subscription['amount'], 2, ',', '.') ?></span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Frequência:</span>
                        <span class="font-semibold text-gray-900">
                            <?= \App\Models\Subscription::getCycleLabel($subscription['cycle']) ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Forma de pagamento:</span>
                        <span class="font-semibold text-gray-900">
                            <?php if ($subscription['payment_method'] === 'credit_card'): ?>
                                <i class="fas fa-credit-card mr-1"></i> Cartão de Crédito
                            <?php else: ?>
                                <i class="fas fa-barcode mr-1"></i> Boleto
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Próxima cobrança:</span>
                        <span class="font-semibold text-gray-900">
                            <?= date('d/m/Y', strtotime($subscription['next_due_date'])) ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-check-circle"></i> Ativa
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Informações Importantes -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 text-left">
                <h4 class="font-bold text-gray-900 mb-2 flex items-center">
                    <i class="fas fa-lightbulb text-yellow-600 mr-2"></i>
                    O que acontece agora?
                </h4>
                <ul class="text-sm text-gray-700 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-envelope text-blue-600 mr-2 mt-1"></i>
                        <span>Você receberá um <strong>email de confirmação</strong> com todos os detalhes</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-calendar-check text-green-600 mr-2 mt-1"></i>
                        <span>A primeira cobrança será processada em <strong><?= date('d/m/Y', strtotime($subscription['next_due_date'] ?? '+7 days')) ?></strong></span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-redo text-purple-600 mr-2 mt-1"></i>
                        <span>As cobranças se <strong>renovarão automaticamente</strong> a cada período</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-times-circle text-red-600 mr-2 mt-1"></i>
                        <span>Você pode <strong>cancelar a qualquer momento</strong> no seu painel</span>
                    </li>
                </ul>
            </div>

            <!-- Campanha -->
            <?php if (isset($campaign)): ?>
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <p class="text-sm text-gray-600 mb-2">Você está apoiando:</p>
                <h3 class="font-bold text-gray-900 text-lg mb-2"><?= esc($campaign['title']) ?></h3>
                <a href="<?= base_url('campaigns/' . $campaign['id']) ?>"
                   class="text-primary-600 hover:text-primary-700 text-sm font-semibold">
                    <i class="fas fa-arrow-right mr-1"></i> Ver campanha
                </a>
            </div>
            <?php endif; ?>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <?php if (session()->get('isLoggedIn')): ?>
                    <a href="<?= base_url('dashboard') ?>"
                       class="btn-primary inline-flex items-center justify-center">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Ir para Meu Painel
                    </a>
                <?php endif; ?>

                <a href="<?= base_url('/') ?>"
                   class="btn-outline inline-flex items-center justify-center">
                    <i class="fas fa-home mr-2"></i>
                    Voltar ao Início
                </a>
            </div>

            <!-- Mensagem de Agradecimento -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-gray-600">
                    <i class="fas fa-heart text-red-500 mr-1"></i>
                    Obrigado por fazer a diferença com sua doação recorrente!
                </p>
            </div>
        </div>

        <!-- Card de Compartilhar -->
        <div class="bg-white rounded-xl shadow-lg p-6 mt-6 text-center">
            <h3 class="font-bold text-gray-900 mb-3">
                <i class="fas fa-share-alt text-primary-600 mr-2"></i>
                Compartilhe esta campanha
            </h3>
            <p class="text-sm text-gray-600 mb-4">
                Ajude a divulgar e inspire mais pessoas a contribuírem!
            </p>

            <?php if (isset($campaign)): ?>
            <div class="flex justify-center gap-2">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= base_url('campaigns/' . $campaign['id']) ?>"
                   target="_blank"
                   class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?= base_url('campaigns/' . $campaign['id']) ?>&text=<?= urlencode($campaign['title']) ?>"
                   target="_blank"
                   class="w-10 h-10 bg-sky-500 text-white rounded-full flex items-center justify-center hover:bg-sky-600 transition">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($campaign['title'] . ' - ' . base_url('campaigns/' . $campaign['id'])) ?>"
                   target="_blank"
                   class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center hover:bg-green-700 transition">
                    <i class="fab fa-whatsapp"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
