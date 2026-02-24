<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-shield-alt text-primary-500 mr-3"></i>
                        Logs de Auditoria
                    </h1>
                    <p class="mt-2 text-gray-600">Rastreie todas as ações administrativas do sistema</p>
                </div>
                <a href="<?= base_url('admin/dashboard') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-database text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total de Logs</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_logs']) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Hoje</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['today']) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-calendar-week text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Esta Semana</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['this_week']) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Este Mês</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['this_month']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Avançados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="<?= base_url('admin/audit-logs') ?>" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Busca -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-search text-primary-500 mr-2"></i>Buscar
                        </label>
                        <input type="text"
                               name="search"
                               value="<?= esc($filters['search'] ?? '') ?>"
                               placeholder="Usuário, email, IP, detalhes..."
                               class="form-input w-full">
                    </div>

                    <!-- Ação -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-bolt text-primary-500 mr-2"></i>Ação
                        </label>
                        <select name="action" class="form-input w-full">
                            <option value="">Todas</option>
                            <?php foreach ($actions_list as $act): ?>
                                <option value="<?= esc($act) ?>" <?= ($filters['action'] ?? '') === $act ? 'selected' : '' ?>>
                                    <?= esc($act) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tipo de Entidade -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-layer-group text-primary-500 mr-2"></i>Entidade
                        </label>
                        <select name="entity_type" class="form-input w-full">
                            <option value="">Todas</option>
                            <?php foreach ($entity_types_list as $entity): ?>
                                <option value="<?= esc($entity) ?>" <?= ($filters['entity_type'] ?? '') === $entity ? 'selected' : '' ?>>
                                    <?= esc(ucfirst($entity)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Usuário -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-primary-500 mr-2"></i>Usuário
                        </label>
                        <select name="user_id" class="form-input w-full">
                            <option value="">Todos</option>
                            <?php foreach ($users_list as $usr): ?>
                                <option value="<?= $usr['id'] ?>" <?= ($filters['user_id'] ?? '') == $usr['id'] ? 'selected' : '' ?>>
                                    <?= esc($usr['name']) ?>
                                </option>
                            <?php endforeach; ?>
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
                            <i class="fas fa-sort text-primary-500 mr-2"></i>Ordem
                        </label>
                        <select name="sort_order" class="form-input w-full">
                            <option value="DESC" <?= ($filters['sort_order'] ?? 'DESC') === 'DESC' ? 'selected' : '' ?>>Mais Recente</option>
                            <option value="ASC" <?= ($filters['sort_order'] ?? '') === 'ASC' ? 'selected' : '' ?>>Mais Antigo</option>
                        </select>
                    </div>

                    <!-- Botões -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="btn-primary flex-1">
                            <i class="fas fa-filter mr-2"></i>Filtrar
                        </button>
                        <a href="<?= base_url('admin/audit-logs') ?>"
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
                        <?php if (!empty($filters['action'])): ?>
                            <span class="inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-bolt mr-2"></i>
                                Ação: <?= esc($filters['action']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['entity_type'])): ?>
                            <span class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-layer-group mr-2"></i>
                                Entidade: <?= esc(ucfirst($filters['entity_type'])) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['user_id'])): ?>
                            <span class="inline-flex items-center bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-user mr-2"></i>
                                Usuário específico
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                            <span class="inline-flex items-center bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-calendar mr-2"></i>
                                Período: <?= esc($filters['date_from'] ?? '...') ?> até <?= esc($filters['date_to'] ?? '...') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        Registros de Auditoria
                        <span class="ml-2 text-sm text-gray-500">(<?= number_format($pagination['total']) ?> total)</span>
                    </h3>
                </div>
            </div>

            <div class="overflow-x-auto">
                <?php if (empty($logs)): ?>
                    <div class="p-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-6xl mb-4 opacity-20"></i>
                        <p>Nenhum log encontrado com os filtros aplicados.</p>
                    </div>
                <?php else: ?>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entidade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalhes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($logs as $log): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Data/Hora -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span class="font-medium"><?= date('d/m/Y', strtotime($log['created_at'])) ?></span>
                                            <span class="text-xs text-gray-500"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                                        </div>
                                    </td>

                                    <!-- Usuário -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if (!empty($log['user_name'] ?? '')): ?>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900"><?= esc($log['user_name'] ?? '') ?></span>
                                                <span class="text-xs text-gray-500"><?= esc($log['user_email'] ?? '') ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Sistema</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Ação -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $actionColors = [
                                            'user_suspended' => 'bg-yellow-100 text-yellow-800',
                                            'user_banned' => 'bg-red-100 text-red-800',
                                            'user_reactivated' => 'bg-green-100 text-green-800',
                                            'comment_approved' => 'bg-green-100 text-green-800',
                                            'comment_rejected' => 'bg-red-100 text-red-800',
                                            'comment_deleted' => 'bg-red-100 text-red-800',
                                            'export_donations' => 'bg-blue-100 text-blue-800',
                                            'export_campaigns' => 'bg-blue-100 text-blue-800',
                                            'export_users' => 'bg-blue-100 text-blue-800',
                                        ];
                                        $color = $actionColors[$log['action'] ?? ''] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $color ?>">
                                            <?= esc($log['action'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <!-- Entidade -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if (!empty($log['entity_type'] ?? '')): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-tag text-gray-400 mr-2"></i>
                                                <span><?= esc($log['entity_type'] ?? '') ?></span>
                                                <?php if (!empty($log['entity_id'] ?? '')): ?>
                                                    <span class="ml-1 text-gray-400">#<?= $log['entity_id'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Detalhes -->
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                        <?php $logDetails = $log['details'] ?? ''; ?>
                                        <?php if (!empty($logDetails)): ?>
                                            <div class="truncate" title="<?= esc($logDetails) ?>">
                                                <?php
                                                $details = json_decode($logDetails, true);
                                                if (is_array($details)) {
                                                    echo esc(substr(json_encode($details, JSON_UNESCAPED_UNICODE), 0, 100));
                                                } else {
                                                    echo esc(substr($logDetails, 0, 100));
                                                }
                                                ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- IP -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">
                                            <?= esc($log['ip_address'] ?? '-') ?>
                                        </code>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Paginação -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="bg-white px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Mostrando
                            <span class="font-medium"><?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?></span>
                            até
                            <span class="font-medium"><?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?></span>
                            de
                            <span class="font-medium"><?= number_format($pagination['total']) ?></span>
                            resultados
                        </div>

                        <nav class="flex items-center space-x-2">
                            <!-- Primeira -->
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>"
                                   class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                            <?php endif; ?>

                            <!-- Anterior -->
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>"
                                   class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Anterior
                                </a>
                            <?php endif; ?>

                            <!-- Páginas -->
                            <?php
                            $start = max(1, $pagination['current_page'] - 2);
                            $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>"
                                   class="px-3 py-2 text-sm rounded-md <?= $i === $pagination['current_page'] ? 'bg-primary-500 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <!-- Próxima -->
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>"
                                   class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    Próxima
                                </a>
                            <?php endif; ?>

                            <!-- Última -->
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['total_pages']])) ?>"
                                   class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
