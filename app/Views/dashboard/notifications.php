<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="mb-8">
            <div>
                <h1 class="text-heading-1 text-gray-900">Preferências de Notificações</h1>
                <p class="text-gray-600 mt-2">Gerencie como e quando você deseja receber atualizações das campanhas</p>
            </div>
        </div>

        <?php if (session('success')): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?= session('success') ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session('error')): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?= session('error') ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Push Notifications Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6"
             x-data="{
                 pushEnabled: <?= json_encode($push_enabled ?? false) ?>,
                 pushSupported: 'Notification' in window,
                 pushPermission: 'Notification' in window ? Notification.permission : 'default',
                 loading: false
             }">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bell text-primary-500 mr-2"></i>
                        Notificações Push no Navegador
                    </h3>
                    <p class="text-gray-600 mt-1 text-sm">
                        Receba alertas instantâneos mesmo quando o site não estiver aberto
                    </p>

                    <div class="mt-4">
                        <template x-if="!pushSupported">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-sm text-yellow-700">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Seu navegador não suporta notificações push.
                                </p>
                            </div>
                        </template>

                        <template x-if="pushSupported && pushPermission === 'denied'">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-sm text-red-700">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Você bloqueou notificações. Habilite nas configurações do navegador para continuar.
                                </p>
                            </div>
                        </template>

                        <template x-if="pushSupported && pushPermission === 'default'">
                            <button @click="window.DoarFazBemFirebase?.requestNotificationPermission()"
                                    class="btn-primary text-sm">
                                <i class="fas fa-bell mr-2"></i>
                                Ativar Notificações Push
                            </button>
                        </template>

                        <template x-if="pushSupported && pushPermission === 'granted' && pushEnabled">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <p class="text-sm text-green-700">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Notificações push ativadas e funcionando!
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Preferences by Campaign -->
        <?php if (empty($preferences)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="text-6xl mb-4 text-gray-300">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Nenhuma preferência configurada</h3>
                <p class="text-gray-600 mb-6">
                    Quando você fizer uma doação e optar por receber atualizações, suas preferências aparecerão aqui.
                </p>
                <a href="<?= base_url('campaigns') ?>" class="btn-primary inline-flex items-center">
                    <i class="fas fa-search mr-2"></i>Explorar Campanhas
                </a>
            </div>
        <?php else: ?>
            <!-- Preferences Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Campanhas que Você Acompanha (<?= count($preferences) ?>)
                    </h3>
                    <p class="text-gray-600 text-sm mt-1">
                        Gerencie suas preferências de notificação para cada campanha
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Campanha
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notificações por Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notificações Push
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($preferences as $pref): ?>
                                <tr class="hover:bg-gray-50" x-data="{
                                    notifyEmail: <?= json_encode((bool)$pref['notify_email']) ?>,
                                    notifyPush: <?= json_encode((bool)$pref['notify_push']) ?>,
                                    saving: false
                                }">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?= esc($pref['campaign_title']) ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Criada por <?= esc($pref['campaign_creator']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <!-- Toggle Switch -->
                                            <button @click="notifyEmail = !notifyEmail; updatePreference(<?= $pref['id'] ?>, 'email', notifyEmail)"
                                                    :disabled="saving"
                                                    :class="notifyEmail ? 'bg-primary-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span :class="notifyEmail ? 'translate-x-5' : 'translate-x-0'"
                                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                            </button>
                                            <!-- Label -->
                                            <span :class="notifyEmail ? 'text-primary-700 font-medium' : 'text-gray-500'"
                                                  class="text-sm transition-colors">
                                                <i :class="notifyEmail ? 'fas fa-envelope' : 'far fa-envelope'" class="mr-1"></i>
                                                <span x-text="notifyEmail ? 'Ativo' : 'Inativo'"></span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <!-- Toggle Switch -->
                                            <button @click="notifyPush = !notifyPush; updatePreference(<?= $pref['id'] ?>, 'push', notifyPush)"
                                                    :disabled="saving"
                                                    :class="notifyPush ? 'bg-primary-600' : 'bg-gray-300'"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span :class="notifyPush ? 'translate-x-5' : 'translate-x-0'"
                                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                            </button>
                                            <!-- Label -->
                                            <span :class="notifyPush ? 'text-primary-700 font-medium' : 'text-gray-500'"
                                                  class="text-sm transition-colors">
                                                <i :class="notifyPush ? 'fas fa-bell' : 'far fa-bell'" class="mr-1"></i>
                                                <span x-text="notifyPush ? 'Ativo' : 'Inativo'"></span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?= base_url('campaigns/' . $pref['campaign_slug']) ?>"
                                           class="text-primary-600 hover:text-primary-900 mr-3">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                        <button @click="unsubscribe(<?= $pref['id'] ?>)"
                                                :disabled="saving"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times-circle mr-1"></i>Cancelar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Global Actions -->
            <div class="mt-6 flex justify-end space-x-4">
                <button @click="if(confirm('Deseja realmente desativar TODAS as notificações?')) { unsubscribeAll(); }"
                        class="btn-secondary text-red-600 hover:bg-red-50">
                    <i class="fas fa-ban mr-2"></i>
                    Desativar Todas
                </button>
            </div>
        <?php endif; ?>

        <!-- Info Card -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <h4 class="text-sm font-semibold text-blue-900 mb-3">
                <i class="fas fa-info-circle mr-2"></i>Como funciona?
            </h4>
            <ul class="text-sm text-blue-800 space-y-2">
                <li>
                    <i class="fas fa-toggle-on mr-2"></i>
                    <strong>Toggles:</strong> Clique nos botões para ativar/desativar cada tipo de notificação individualmente
                </li>
                <li>
                    <i class="fas fa-envelope mr-2"></i>
                    <strong>Email:</strong> Receba emails quando os criadores postarem atualizações nas campanhas
                </li>
                <li>
                    <i class="fas fa-bell mr-2"></i>
                    <strong>Push:</strong> Receba notificações instantâneas no navegador (mesmo com o site fechado)
                </li>
                <li>
                    <i class="fas fa-user-shield mr-2"></i>
                    Você pode cancelar a qualquer momento clicando no botão "Cancelar" ou no link de descadastro no email
                </li>
                <li>
                    <i class="fas fa-lock mr-2"></i>
                    Seus dados são privados e nunca serão compartilhados com terceiros
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
// Função para atualizar preferência via AJAX
async function updatePreference(id, type, enabled) {
    try {
        const response = await fetch('<?= base_url('dashboard/notifications/update') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                preference_id: id,
                type: type,
                enabled: enabled
            })
        });

        const data = await response.json();

        if (!data.success) {
            alert('Erro ao atualizar preferência: ' + (data.error || 'Erro desconhecido'));
            // Reverter mudança
            location.reload();
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao atualizar preferência. Tente novamente.');
        location.reload();
    }
}

// Função para cancelar assinatura de uma campanha
async function unsubscribe(id) {
    if (!confirm('Deseja realmente parar de receber notificações desta campanha?')) {
        return;
    }

    try {
        const response = await fetch('<?= base_url('dashboard/notifications/unsubscribe') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                preference_id: id
            })
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao cancelar assinatura: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao cancelar assinatura. Tente novamente.');
    }
}

// Função para cancelar todas as assinaturas
async function unsubscribeAll() {
    try {
        const response = await fetch('<?= base_url('dashboard/notifications/unsubscribe-all') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert('Erro ao cancelar todas as assinaturas: ' + (data.error || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao cancelar assinaturas. Tente novamente.');
    }
}
</script>

<?= $this->endSection() ?>
