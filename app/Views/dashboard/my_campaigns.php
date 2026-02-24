<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 text-gray-900">Minhas Campanhas</h1>
                <p class="text-gray-600 mt-2">Gerencie todas as suas campanhas de arrecadação</p>
            </div>
            <a href="<?= base_url('campaigns/create') ?>" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>Nova Campanha
            </a>
        </div>

        <?php if (empty($campaigns)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="text-6xl mb-4 text-gray-300">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Nenhuma campanha criada ainda</h3>
                <p class="text-gray-600 mb-6">Comece criando sua primeira campanha de arrecadação!</p>
                <a href="<?= base_url('campaigns/create') ?>" class="btn-primary inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Criar Primeira Campanha
                </a>
            </div>
        <?php else: ?>
            <!-- Campaigns Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php foreach ($campaigns as $campaign): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Header com Imagem -->
                    <div class="relative h-48 bg-gray-200">
                        <?php if (!empty($campaign['image'])): ?>
                            <img src="<?= base_url('uploads/campaigns/' . $campaign['image']) ?>"
                                 alt="<?= esc($campaign['title']) ?>"
                                 class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-400 to-secondary-400">
                                <i class="fas fa-heart text-6xl text-white"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <?php
                            $statusClass = 'bg-gray-500';
                            $statusText = ucfirst($campaign['status']);
                            if ($campaign['status'] === 'active') {
                                $statusClass = 'bg-green-500';
                                $statusText = 'Ativa';
                            } elseif ($campaign['status'] === 'completed') {
                                $statusClass = 'bg-blue-500';
                                $statusText = 'Concluída';
                            } elseif ($campaign['status'] === 'paused') {
                                $statusClass = 'bg-yellow-500';
                                $statusText = 'Pausada';
                            }
                            ?>
                            <span class="px-3 py-1 text-white text-sm font-semibold rounded-full <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <!-- Title -->
                        <h3 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2"><?= esc($campaign['title']) ?></h3>

                        <!-- Category -->
                        <div class="mb-4">
                            <span class="px-3 py-1 bg-primary-100 text-primary-700 text-xs font-semibold rounded-full uppercase">
                                <?= esc($campaign['category']) ?>
                            </span>
                        </div>

                        <!-- Progress -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span class="font-semibold text-primary-600">
                                    R$ <?= number_format($campaign['current_amount'] ?? 0, 2, ',', '.') ?>
                                </span>
                                <span class="text-gray-500">
                                    Meta: R$ <?= number_format($campaign['goal_amount'], 2, ',', '.') ?>
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-primary-500 to-secondary-500 h-3 rounded-full transition-all"
                                     style="width: <?= min($campaign['percentage'] ?? 0, 100) ?>%"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?= number_format($campaign['percentage'] ?? 0, 1) ?>% arrecadado
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-4 pb-4 border-b border-gray-200">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900"><?= $campaign['donors_count'] ?? 0 ?></div>
                                <div class="text-xs text-gray-600">Doadores</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900"><?= $campaign['views_count'] ?? 0 ?></div>
                                <div class="text-xs text-gray-600">Views</div>
                            </div>
                            <div class="text-center">
                                <?php
                                $daysLeft = 0;
                                if (!empty($campaign['end_date'])) {
                                    $endDate = new DateTime($campaign['end_date']);
                                    $now = new DateTime();
                                    $diff = $now->diff($endDate);
                                    $daysLeft = $diff->invert ? 0 : $diff->days;
                                }
                                ?>
                                <div class="text-2xl font-bold text-gray-900"><?= $daysLeft ?></div>
                                <div class="text-xs text-gray-600">Dias</div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2 mb-3">
                            <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>"
                               class="flex-1 btn-secondary text-center text-sm py-2">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            <a href="<?= base_url('campaigns/edit/' . $campaign['id']) ?>"
                               class="flex-1 btn-primary text-center text-sm py-2">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                        </div>

                        <!-- Quick Links -->
                        <div class="flex flex-wrap gap-2 text-xs">
                            <a href="<?= base_url('campaigns/' . $campaign['id'] . '/rewards') ?>"
                               class="px-2 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition-colors">
                                <i class="fas fa-gift mr-1"></i>Recompensas
                            </a>
                            <a href="<?= base_url('campaigns/' . $campaign['id'] . '/media') ?>"
                               class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                                <i class="fas fa-images mr-1"></i>Midia
                            </a>
                            <a href="<?= base_url('campaigns/' . $campaign['id'] . '/donors') ?>"
                               class="px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors">
                                <i class="fas fa-users mr-1"></i>Doadores
                            </a>
                        </div>

                        <!-- Campaign Control Actions -->
                        <?php if (in_array($campaign['status'], ['active', 'paused'])): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex flex-wrap gap-2">
                                <?php if ($campaign['status'] === 'active'): ?>
                                    <!-- Pausar -->
                                    <form action="<?= base_url('campaigns/pause/' . $campaign['id']) ?>" method="POST" class="inline"
                                          onsubmit="return confirm('Tem certeza que deseja pausar esta campanha? Ela não receberá novas doações enquanto estiver pausada.')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded text-xs font-medium hover:bg-yellow-200 transition-colors">
                                            <i class="fas fa-pause mr-1"></i>Pausar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Reativar -->
                                    <form action="<?= base_url('campaigns/resume/' . $campaign['id']) ?>" method="POST" class="inline"
                                          onsubmit="return confirm('Reativar campanha? Ela voltará a receber doações.')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-3 py-1.5 bg-green-100 text-green-700 rounded text-xs font-medium hover:bg-green-200 transition-colors">
                                            <i class="fas fa-play mr-1"></i>Reativar
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Encerrar -->
                                <form action="<?= base_url('campaigns/end/' . $campaign['id']) ?>" method="POST" class="inline"
                                      onsubmit="return confirm('Tem certeza que deseja encerrar esta campanha? Esta acao nao pode ser desfeita. Voce ainda podera sacar os valores arrecadados.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200 transition-colors">
                                        <i class="fas fa-stop mr-1"></i>Encerrar
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
