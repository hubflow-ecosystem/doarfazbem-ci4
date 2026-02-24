<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?= base_url('dashboard/my-campaigns') ?>" class="text-primary-600 hover:text-primary-700 font-semibold mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Voltar para Minhas Campanhas
            </a>

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-heading-1 text-gray-900 mb-2"><?= esc($campaign['title']) ?></h1>
                    <div class="flex items-center gap-3 text-gray-600">
                        <span class="inline-block bg-<?= $campaign['status'] === 'active' ? 'green' : 'gray' ?>-100 text-<?= $campaign['status'] === 'active' ? 'green' : 'gray' ?>-700 px-3 py-1 rounded-full text-sm font-semibold">
                            <?= $campaign['status'] === 'active' ? 'Ativa' : 'Encerrada' ?>
                        </span>
                        <span><i class="fas fa-calendar mr-1"></i> <?= $stats['days_left'] ?> dias restantes</span>
                    </div>
                </div>
                <div>
                    <a href="<?= base_url('campaigns/' . $campaign['id']) ?>" target="_blank" class="btn-outline">
                        <i class="fas fa-external-link-alt mr-2"></i> Ver Página Pública
                    </a>
                </div>
            </div>
        </div>

        <!-- Estatísticas Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Arrecadado -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600 text-sm">Arrecadado</span>
                    <i class="fas fa-hand-holding-usd text-2xl text-green-500"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">
                    R$ <?= number_format($stats['total_raised'], 2, ',', '.') ?>
                </div>
                <div class="text-sm text-gray-600">
                    de R$ <?= number_format($campaign['goal_amount'], 2, ',', '.') ?> (<?= number_format($stats['percentage'], 1) ?>%)
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full"
                         style="width: <?= min($stats['percentage'], 100) ?>%"></div>
                </div>
            </div>

            <!-- Total de Doadores -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600 text-sm">Doadores</span>
                    <i class="fas fa-users text-2xl text-blue-500"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">
                    <?= $stats['donors_count'] ?>
                </div>
                <div class="text-sm text-gray-600">
                    <?= count($donations) ?> doações no total
                </div>
            </div>

            <!-- Assinaturas Ativas -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600 text-sm">Assinaturas Ativas</span>
                    <i class="fas fa-redo text-2xl text-purple-500"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">
                    <?= $stats['active_subscriptions'] ?>
                </div>
                <div class="text-sm text-gray-600">
                    <?= count($subscriptions) ?> assinaturas no total
                </div>
            </div>

            <!-- Recorrente Mensal -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600 text-sm">Recorrente/Mês</span>
                    <i class="fas fa-chart-line text-2xl text-orange-500"></i>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">
                    R$ <?= number_format($stats['monthly_recurring'], 2, ',', '.') ?>
                </div>
                <div class="text-sm text-gray-600">
                    Previsão mensal
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Coluna Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Doações Recentes -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-hand-holding-heart text-primary-600 mr-2"></i>
                        Doações Recentes
                    </h2>

                    <?php if (!empty($donations)): ?>
                        <div class="space-y-3">
                            <?php foreach ($donations as $donation): ?>
                                <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-start gap-3 flex-1">
                                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-heart text-lg text-primary-600"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-semibold text-gray-900">
                                                <?= $donation['is_anonymous'] ? 'Doador Anônimo' : esc($donation['donor_name']) ?>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <?= esc($donation['donor_email']) ?>
                                            </div>
                                            <?php if (!empty($donation['message'])): ?>
                                                <div class="text-sm text-gray-600 mt-1 italic">
                                                    "<?= esc($donation['message']) ?>"
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-green-600">
                                            R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                                        </div>
                                        <div class="text-xs">
                                            <span class="inline-block px-2 py-1 rounded text-white <?= $donation['payment_status'] === 'confirmed' ? 'bg-green-500' : ($donation['payment_status'] === 'pending' ? 'bg-yellow-500' : 'bg-gray-500') ?>">
                                                <?= ucfirst($donation['payment_status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>Ainda não há doações para esta campanha</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Assinaturas -->
                <?php if (!empty($subscriptions)): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-redo text-purple-600 mr-2"></i>
                        Assinaturas Recorrentes
                    </h2>

                    <div class="space-y-3">
                        <?php foreach ($subscriptions as $subscription): ?>
                            <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-start gap-3 flex-1">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-redo text-lg text-purple-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900">
                                            <?= esc($subscription['donor_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            <?= esc($subscription['donor_email']) ?>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?= \App\Models\Subscription::getCycleLabel($subscription['cycle']) ?>
                                            • Próxima: <?= date('d/m/Y', strtotime($subscription['next_due_date'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-purple-600">
                                        R$ <?= number_format($subscription['amount'], 2, ',', '.') ?>
                                    </div>
                                    <div class="text-xs">
                                        <span class="inline-block px-2 py-1 rounded text-white <?= $subscription['status'] === 'active' ? 'bg-green-500' : 'bg-gray-500' ?>">
                                            <?= \App\Models\Subscription::getStatusLabel($subscription['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Imagem da Campanha -->
                <?php if ($campaign['image']): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="<?= base_url('uploads/campaigns/' . $campaign['image']) ?>"
                         alt="<?= esc($campaign['title']) ?>"
                         class="w-full h-48 object-cover">
                </div>
                <?php endif; ?>

                <!-- Informações -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Informações</h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-gray-600">Categoria:</span>
                            <span class="font-semibold ml-2"><?= esc(ucfirst($campaign['category'])) ?></span>
                        </div>

                        <div>
                            <span class="text-gray-600">Tipo:</span>
                            <span class="font-semibold ml-2">
                                <?php
                                $types = [
                                    'flexivel' => 'Flexível',
                                    'tudo_ou_tudo' => 'Tudo ou Tudo',
                                    'recorrente' => 'Recorrente'
                                ];
                                echo $types[$campaign['campaign_type']] ?? 'Flexível';
                                ?>
                            </span>
                        </div>

                        <div>
                            <span class="text-gray-600">Localização:</span>
                            <span class="font-semibold ml-2"><?= esc($campaign['city']) ?>, <?= esc($campaign['state']) ?></span>
                        </div>

                        <div>
                            <span class="text-gray-600">Criada em:</span>
                            <span class="font-semibold ml-2"><?= date('d/m/Y', strtotime($campaign['created_at'])) ?></span>
                        </div>

                        <div>
                            <span class="text-gray-600">Termina em:</span>
                            <span class="font-semibold ml-2"><?= date('d/m/Y', strtotime($campaign['end_date'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Ações</h3>

                    <div class="space-y-2">
                        <a href="<?= base_url('campaigns/edit/' . $campaign['id']) ?>"
                           class="block w-full text-center btn-primary">
                            <i class="fas fa-edit mr-2"></i> Editar Campanha
                        </a>

                        <button onclick="shareCampaign()" class="block w-full text-center btn-outline">
                            <i class="fas fa-share-alt mr-2"></i> Compartilhar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function shareCampaign() {
    const url = '<?= base_url('campaigns/' . $campaign['id']) ?>';
    const title = '<?= esc($campaign['title']) ?>';

    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        });
    } else {
        navigator.clipboard.writeText(url);
        alert('Link copiado para a área de transferência!');
    }
}
</script>

<?= $this->endSection() ?>
