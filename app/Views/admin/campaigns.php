<?= $this->extend('layout/app') ?>
<?php helper('text'); ?>
<?= $this->section('content') ?>
<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Menu de Navegação Admin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8">
            <nav class="flex flex-wrap gap-2">
                <a href="<?= base_url('admin/dashboard') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="<?= base_url('admin/campaigns?status=pending') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' ?> font-medium">
                    <i class="fas fa-clock mr-2"></i>Pendentes
                </a>
                <a href="<?= base_url('admin/campaigns?status=active') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'active' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?> font-medium">
                    <i class="fas fa-bullhorn mr-2"></i>Campanhas
                </a>
                <a href="<?= base_url('admin/users') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-users mr-2"></i>Usuários
                </a>
                <a href="<?= base_url('admin/donations') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-hand-holding-heart mr-2"></i>Doações
                </a>
                <a href="<?= base_url('admin/comments') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-comments mr-2"></i>Comentários
                </a>
                <a href="<?= base_url('admin/withdrawals') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-money-bill-wave mr-2"></i>Saques
                </a>
                <a href="<?= base_url('admin/raffles') ?>"
                   class="px-4 py-2 rounded-lg bg-purple-100 text-purple-700 hover:bg-purple-200 font-medium">
                    <i class="fas fa-ticket-alt mr-2"></i>Rifas
                </a>
                <a href="<?= base_url('admin/reports') ?>"
                   class="px-4 py-2 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 font-medium">
                    <i class="fas fa-file-export mr-2"></i>Relatórios
                </a>
                <a href="<?= base_url('admin/audit-logs') ?>"
                   class="px-4 py-2 rounded-lg bg-orange-100 text-orange-700 hover:bg-orange-200 font-medium">
                    <i class="fas fa-shield-alt mr-2"></i>Logs
                </a>
                <a href="<?= base_url('admin/backup') ?>"
                   class="px-4 py-2 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 font-medium">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Backup
                </a>
                <a href="<?= base_url('admin/settings') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-cog mr-2"></i>Configurações
                </a>
            </nav>
        </div>

        <!-- Header com Badges de Status -->
        <div class="mb-8">
            <h1 class="text-heading-1 mb-4">Gerenciar Campanhas</h1>
            <div class="flex flex-wrap gap-3">
                <a href="<?= base_url('admin/campaigns?status=all') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'all' ? 'bg-gray-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?> font-medium border border-gray-300 transition-colors">
                    <i class="fas fa-list mr-2"></i>Todas
                    <span class="ml-2 bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['all'] ?></span>
                </a>
                <a href="<?= base_url('admin/campaigns?status=pending') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'pending' ? 'bg-yellow-500 text-white' : 'bg-white text-yellow-700 hover:bg-yellow-50' ?> font-medium border border-yellow-300 transition-colors">
                    <i class="fas fa-clock mr-2"></i>Pendentes
                    <span class="ml-2 bg-yellow-200 text-yellow-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['pending'] ?></span>
                </a>
                <a href="<?= base_url('admin/campaigns?status=active') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'active' ? 'bg-primary-500 text-white' : 'bg-white text-primary-700 hover:bg-primary-50' ?> font-medium border border-primary-300 transition-colors">
                    <i class="fas fa-bullhorn mr-2"></i>Ativas
                    <span class="ml-2 bg-primary-200 text-primary-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['active'] ?></span>
                </a>
                <a href="<?= base_url('admin/campaigns?status=paused') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'paused' ? 'bg-blue-500 text-white' : 'bg-white text-blue-700 hover:bg-blue-50' ?> font-medium border border-blue-300 transition-colors">
                    <i class="fas fa-pause mr-2"></i>Pausadas
                    <span class="ml-2 bg-blue-200 text-blue-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['paused'] ?></span>
                </a>
                <a href="<?= base_url('admin/campaigns?status=completed') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'completed' ? 'bg-green-500 text-white' : 'bg-white text-green-700 hover:bg-green-50' ?> font-medium border border-green-300 transition-colors">
                    <i class="fas fa-check-circle mr-2"></i>Concluídas
                    <span class="ml-2 bg-green-200 text-green-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['completed'] ?></span>
                </a>
                <a href="<?= base_url('admin/campaigns?status=rejected') ?>"
                   class="px-4 py-2 rounded-lg <?= $current_status === 'rejected' ? 'bg-red-500 text-white' : 'bg-white text-red-700 hover:bg-red-50' ?> font-medium border border-red-300 transition-colors">
                    <i class="fas fa-times-circle mr-2"></i>Rejeitadas
                    <span class="ml-2 bg-red-200 text-red-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['rejected'] ?></span>
                </a>
            </div>
        </div>

        <!-- Filtros Avançados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="<?= base_url('admin/campaigns') ?>" class="space-y-4">
                <input type="hidden" name="status" value="<?= esc($current_status) ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Busca -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-search text-primary-500 mr-2"></i>Buscar
                        </label>
                        <input type="text"
                               name="search"
                               value="<?= esc($filters['search'] ?? '') ?>"
                               placeholder="Título, descrição, criador..."
                               class="form-input w-full">
                    </div>

                    <!-- Categoria -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tag text-primary-500 mr-2"></i>Categoria
                        </label>
                        <select name="category" class="form-input w-full">
                            <option value="">Todas</option>
                            <?php foreach ($category_list as $cat): ?>
                                <option value="<?= esc($cat) ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                    <?= esc(ucfirst($cat)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Ordenar por -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sort text-primary-500 mr-2"></i>Ordenar por
                        </label>
                        <select name="sort_by" class="form-input w-full">
                            <option value="created_at" <?= ($filters['sort_by'] ?? 'created_at') === 'created_at' ? 'selected' : '' ?>>Data de Criação</option>
                            <option value="title" <?= ($filters['sort_by'] ?? '') === 'title' ? 'selected' : '' ?>>Título</option>
                            <option value="goal_amount" <?= ($filters['sort_by'] ?? '') === 'goal_amount' ? 'selected' : '' ?>>Meta</option>
                            <option value="category" <?= ($filters['sort_by'] ?? '') === 'category' ? 'selected' : '' ?>>Categoria</option>
                        </select>
                    </div>

                    <!-- Data de -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-primary-500 mr-2"></i>De
                        </label>
                        <input type="date"
                               name="date_from"
                               value="<?= esc($filters['date_from'] ?? '') ?>"
                               class="form-input w-full">
                    </div>

                    <!-- Data até -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar text-primary-500 mr-2"></i>Até
                        </label>
                        <input type="date"
                               name="date_to"
                               value="<?= esc($filters['date_to'] ?? '') ?>"
                               class="form-input w-full">
                    </div>

                    <!-- Ordem -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-arrow-down-up-across-line text-primary-500 mr-2"></i>Ordem
                        </label>
                        <select name="sort_order" class="form-input w-full">
                            <option value="DESC" <?= ($filters['sort_order'] ?? 'DESC') === 'DESC' ? 'selected' : '' ?>>Decrescente</option>
                            <option value="ASC" <?= ($filters['sort_order'] ?? '') === 'ASC' ? 'selected' : '' ?>>Crescente</option>
                        </select>
                    </div>

                    <!-- Botões -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn-primary flex-1">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                        <a href="<?= base_url('admin/campaigns?status=' . $current_status) ?>"
                           class="btn-outline px-4 py-2">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>

                <!-- Resumo dos Filtros Ativos -->
                <?php
                $activeFilters = array_filter($filters ?? []);
                if (!empty($activeFilters)):
                ?>
                <div class="pt-4 border-t">
                    <p class="text-sm text-gray-600 mb-2"><strong>Filtros ativos:</strong></p>
                    <div class="flex flex-wrap gap-2">
                        <?php if (!empty($filters['search'])): ?>
                            <span class="inline-flex items-center bg-primary-100 text-primary-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-search mr-2"></i>
                                Busca: "<?= esc($filters['search']) ?>"
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['category'])): ?>
                            <span class="inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-tag mr-2"></i>
                                Categoria: <?= esc(ucfirst($filters['category'])) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                            <span class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-calendar mr-2"></i>
                                Período: <?= esc($filters['date_from'] ?? '...') ?> até <?= esc($filters['date_to'] ?? '...') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($campaigns)): ?>
            <div class="bg-white rounded-xl shadow-card p-12 text-center">
                <p class="text-gray-600">Nenhuma campanha encontrada</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <?php foreach ($campaigns as $campaign): ?>
                    <div class="p-6 border-b last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg mb-1">
                                    <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>" target="_blank" class="hover:text-primary-600">
                                        <?= esc($campaign['title']) ?>
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    Categoria: <?= esc($campaign['category']) ?> | Meta: R$ <?= number_format($campaign['goal_amount'], 2, ',', '.') ?>
                                </p>
                                <p class="text-sm text-gray-500"><?= character_limiter($campaign['description'], 150) ?></p>

                                <?php if ($current_status === 'rejected' && !empty($campaign['rejection_reason'])): ?>
                                <div class="mt-3 p-3 bg-red-50 border-l-4 border-red-500 rounded">
                                    <p class="text-sm font-medium text-red-700">Motivo da rejeicao:</p>
                                    <p class="text-sm text-red-600"><?= nl2br(esc($campaign['rejection_reason'])) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($current_status === 'pending'): ?>
                                <div class="flex gap-2 ml-4">
                                    <form action="<?= base_url('admin/campaigns/approve/' . $campaign['id']) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn-primary text-sm">Aprovar</button>
                                    </form>
                                    <button type="button"
                                            onclick="openRejectModal(<?= $campaign['id'] ?>, '<?= esc($campaign['title'], 'js') ?>')"
                                            class="btn-outline text-sm text-red-600 border-red-300 hover:bg-red-50">
                                        Rejeitar
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if ($current_status === 'active'): ?>
                                <div class="flex gap-2 ml-4">
                                    <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>" target="_blank"
                                       class="btn-secondary text-sm">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Rejeição -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <form id="rejectForm" method="POST">
            <?= csrf_field() ?>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Rejeitar Campanha</h3>
                <p class="text-sm text-gray-600 mb-4" id="rejectCampaignTitle"></p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo da rejeicao *</label>
                    <textarea name="reason" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              placeholder="Explique o motivo da rejeicao para ajudar o criador a entender e corrigir os problemas..." required></textarea>
                    <p class="text-xs text-gray-500 mt-1">Este motivo sera enviado por email ao criador da campanha.</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-xl">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Confirmar Rejeicao
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id, title) {
    document.getElementById('rejectForm').action = '<?= base_url('admin/campaigns/reject/') ?>' + id;
    document.getElementById('rejectCampaignTitle').textContent = 'Campanha: "' + title + '"';
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.querySelector('#rejectForm textarea[name="reason"]').value = '';
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});
</script>

<?= $this->endSection() ?>
