<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Card Principal -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
            <!-- Icon -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 text-yellow-600 mb-4">
                    <i class="fas fa-bell-slash text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Cancelar Notificações</h1>
                <p class="text-gray-600 mt-2">Sentiremos sua falta!</p>
            </div>

            <!-- Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    Você está prestes a cancelar as notificações da campanha:
                </p>
                <p class="font-semibold text-blue-900 mt-2">
                    <?= esc($campaign_title ?? 'Campanha') ?>
                </p>
            </div>

            <!-- Opções -->
            <form method="POST" action="<?= base_url('notifications/unsubscribe') ?>" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= esc($token) ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        O que você deseja fazer?
                    </label>

                    <div class="space-y-3">
                        <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition-colors">
                            <input type="radio" name="action" value="email_only" class="mt-1 mr-3" checked>
                            <div>
                                <div class="font-medium text-gray-900">
                                    <i class="fas fa-envelope-open-text mr-2 text-primary-600"></i>
                                    Cancelar apenas emails
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Você continuará recebendo notificações push no navegador
                                </div>
                            </div>
                        </label>

                        <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-500 transition-colors">
                            <input type="radio" name="action" value="all" class="mt-1 mr-3">
                            <div>
                                <div class="font-medium text-gray-900">
                                    <i class="fas fa-ban mr-2 text-red-600"></i>
                                    Cancelar todas as notificações
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Você não receberá mais emails nem notificações push desta campanha
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Feedback Opcional -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Por que você está cancelando? (Opcional)
                    </label>
                    <select name="reason" class="form-input w-full">
                        <option value="">Selecione um motivo...</option>
                        <option value="too_many">Recebo muitas notificações</option>
                        <option value="not_interested">Não estou mais interessado nesta campanha</option>
                        <option value="mistake">Me inscrevi por engano</option>
                        <option value="privacy">Preocupações com privacidade</option>
                        <option value="other">Outro motivo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Comentários adicionais (Opcional)
                    </label>
                    <textarea name="comments" rows="3" class="form-input w-full"
                              placeholder="Suas sugestões nos ajudam a melhorar..."></textarea>
                </div>

                <!-- Botões -->
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 btn-primary bg-red-600 hover:bg-red-700">
                        <i class="fas fa-times-circle mr-2"></i>
                        Confirmar Cancelamento
                    </button>
                    <a href="<?= base_url('campaigns/' . ($campaign_slug ?? '')) ?>" class="flex-1 btn-secondary text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Voltar
                    </a>
                </div>
            </form>

            <!-- Alternativas -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">
                    Ou você prefere...
                </h3>
                <div class="space-y-2 text-sm">
                    <a href="<?= base_url('dashboard/notifications') ?>" class="flex items-center text-primary-600 hover:text-primary-700">
                        <i class="fas fa-cog mr-2"></i>
                        Gerenciar preferências de todas as campanhas
                    </a>
                    <a href="<?= base_url('user/profile') ?>" class="flex items-center text-primary-600 hover:text-primary-700">
                        <i class="fas fa-user-cog mr-2"></i>
                        Alterar configurações da conta
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Extra -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>
                <i class="fas fa-shield-alt mr-1"></i>
                Seus dados estão seguros e nunca serão compartilhados com terceiros
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
