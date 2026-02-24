<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Card Principal -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
            <!-- Icon Success -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4 animate-bounce">
                    <i class="fas fa-check text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Preferências Atualizadas!</h1>
                <p class="text-gray-600 mt-2">Suas preferências foram salvas com sucesso</p>
            </div>

            <!-- Info -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-green-600 mt-1 mr-3"></i>
                    <div class="text-sm text-green-800">
                        <p class="font-medium mb-1">O que mudou?</p>
                        <?php if (isset($action) && $action === 'all'): ?>
                            <p>Você não receberá mais nenhuma notificação (email ou push) desta campanha.</p>
                        <?php else: ?>
                            <p>Você não receberá mais emails desta campanha, mas continuará recebendo notificações push.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Campanha Info -->
            <?php if (isset($campaign_title)): ?>
                <div class="border border-gray-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Campanha:</h3>
                    <p class="font-semibold text-gray-900"><?= esc($campaign_title) ?></p>
                    <?php if (isset($campaign_slug)): ?>
                        <a href="<?= base_url('campaigns/' . $campaign_slug) ?>"
                           class="text-primary-600 hover:text-primary-700 text-sm mt-2 inline-block">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Ver campanha
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Próximos Passos -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">O que você pode fazer agora?</h3>

                <a href="<?= base_url('dashboard/notifications') ?>"
                   class="block p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 transition-colors group">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900 group-hover:text-primary-600">
                                <i class="fas fa-cog mr-2"></i>
                                Gerenciar Todas as Notificações
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                Veja e edite preferências de todas as campanhas
                            </div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-primary-600"></i>
                    </div>
                </a>

                <a href="<?= base_url('campaigns') ?>"
                   class="block p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 transition-colors group">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900 group-hover:text-primary-600">
                                <i class="fas fa-search mr-2"></i>
                                Explorar Outras Campanhas
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                Descubra mais causas para apoiar
                            </div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-primary-600"></i>
                    </div>
                </a>

                <a href="<?= base_url('dashboard') ?>"
                   class="block p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 transition-colors group">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900 group-hover:text-primary-600">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Ir para o Dashboard
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                Veja suas doações e campanhas
                            </div>
                        </div>
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-primary-600"></i>
                    </div>
                </a>
            </div>

            <!-- Feedback Agradecimento -->
            <?php if (isset($feedback_received) && $feedback_received): ?>
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-heart mr-2"></i>
                        Obrigado pelo seu feedback! Ele nos ajuda a melhorar a plataforma para todos.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Message Extra -->
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Mudou de ideia? Você pode reativar as notificações a qualquer momento no seu
                <a href="<?= base_url('dashboard/notifications') ?>" class="text-primary-600 hover:text-primary-700 font-medium">
                    painel de preferências
                </a>
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
