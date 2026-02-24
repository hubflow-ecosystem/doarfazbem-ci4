<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Card Principal -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
            <!-- Icon Error -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                    <i class="fas fa-exclamation-triangle text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Ops! Algo deu errado</h1>
                <p class="text-gray-600 mt-2">Não foi possível processar sua solicitação</p>
            </div>

            <!-- Erro -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-times-circle text-red-600 mt-1 mr-3"></i>
                    <div class="text-sm text-red-800">
                        <p class="font-medium mb-1">Erro ao processar:</p>
                        <p><?= esc($error_message ?? 'Token inválido ou expirado') ?></p>
                    </div>
                </div>
            </div>

            <!-- Possíveis Causas -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Possíveis causas:</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-400 text-xs mt-1 mr-3"></i>
                        <span>O link de cancelamento pode ter expirado</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-400 text-xs mt-1 mr-3"></i>
                        <span>Você pode já ter cancelado estas notificações anteriormente</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-circle text-gray-400 text-xs mt-1 mr-3"></i>
                        <span>O link pode estar incompleto ou corrompido</span>
                    </li>
                </ul>
            </div>

            <!-- Soluções -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900">O que fazer agora?</h3>

                <?php if (session('user_id')): ?>
                    <a href="<?= base_url('dashboard/notifications') ?>"
                       class="block p-4 border-2 border-primary-500 rounded-lg hover:bg-primary-50 transition-colors group">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-primary-700 group-hover:text-primary-800">
                                    <i class="fas fa-cog mr-2"></i>
                                    Gerenciar Preferências pelo Dashboard
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Acesse o painel de preferências diretamente (recomendado)
                                </div>
                            </div>
                            <i class="fas fa-arrow-right text-primary-600"></i>
                        </div>
                    </a>
                <?php endif; ?>

                <div class="p-4 border-2 border-gray-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-envelope text-gray-400 text-xl mr-3 mt-1"></i>
                        <div>
                            <div class="font-medium text-gray-900">
                                Use o link mais recente do email
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                Se você recebeu vários emails, use o link do email mais recente
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-2 border-gray-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-life-ring text-gray-400 text-xl mr-3 mt-1"></i>
                        <div>
                            <div class="font-medium text-gray-900">
                                Entre em contato com o suporte
                            </div>
                            <div class="text-sm text-gray-600 mt-1 mb-3">
                                Nossa equipe pode ajudar você a gerenciar suas notificações
                            </div>
                            <a href="mailto:suporte@doarfazbem.com.br?subject=Erro ao cancelar notificações"
                               class="text-primary-600 hover:text-primary-700 text-sm font-medium inline-flex items-center">
                                <i class="fas fa-paper-plane mr-2"></i>
                                suporte@doarfazbem.com.br
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="<?= base_url('campaigns') ?>" class="flex-1 btn-primary text-center">
                    <i class="fas fa-home mr-2"></i>
                    Voltar para Campanhas
                </a>
                <?php if (session('user_id')): ?>
                    <a href="<?= base_url('dashboard') ?>" class="flex-1 btn-secondary text-center">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        Ir para Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Extra -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">Importante:</p>
                    <p>
                        Se você continuar recebendo notificações indesejadas mesmo após cancelar,
                        por favor entre em contato com nosso suporte para que possamos resolver o problema.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
