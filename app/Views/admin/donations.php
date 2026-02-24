<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gerenciar Doações</h1>
                    <p class="mt-2 text-gray-600">Visualize e filtre todas as doações da plataforma</p>
                </div>
                <a href="<?= base_url('admin/dashboard') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>

        <!-- Status Badges -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-3">
                <a href="<?= base_url('admin/donations') ?>"
                   class="px-4 py-2 rounded-lg <?= empty($filters['status']) ? 'bg-gray-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' ?> font-medium border border-gray-300 transition-colors">
                    <i class="fas fa-list mr-2"></i>Todas
                    <span class="ml-2 bg-gray-200 text-gray-800 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['all'] ?></span>
                </a>
                <a href="<?= base_url('admin/donations?status=pending') ?>"
                   class="px-4 py-2 rounded-lg <?= ($filters['status'] ?? '') === 'pending' ? 'bg-yellow-500 text-white' : 'bg-white text-yellow-700 hover:bg-yellow-50' ?> font-medium border border-yellow-300 transition-colors">
                    <i class="fas fa-clock mr-2"></i>Pendentes
                    <span class="ml-2 bg-yellow-200 text-yellow-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['pending'] ?></span>
                </a>
                <a href="<?= base_url('admin/donations?status=confirmed') ?>"
                   class="px-4 py-2 rounded-lg <?= ($filters['status'] ?? '') === 'confirmed' ? 'bg-blue-500 text-white' : 'bg-white text-blue-700 hover:bg-blue-50' ?> font-medium border border-blue-300 transition-colors">
                    <i class="fas fa-check mr-2"></i>Confirmadas
                    <span class="ml-2 bg-blue-200 text-blue-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['confirmed'] ?></span>
                </a>
                <a href="<?= base_url('admin/donations?status=received') ?>"
                   class="px-4 py-2 rounded-lg <?= ($filters['status'] ?? '') === 'received' ? 'bg-green-500 text-white' : 'bg-white text-green-700 hover:bg-green-50' ?> font-medium border border-green-300 transition-colors">
                    <i class="fas fa-check-double mr-2"></i>Recebidas
                    <span class="ml-2 bg-green-200 text-green-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['received'] ?></span>
                </a>
                <a href="<?= base_url('admin/donations?status=failed') ?>"
                   class="px-4 py-2 rounded-lg <?= ($filters['status'] ?? '') === 'failed' ? 'bg-red-500 text-white' : 'bg-white text-red-700 hover:bg-red-50' ?> font-medium border border-red-300 transition-colors">
                    <i class="fas fa-times mr-2"></i>Falhadas
                    <span class="ml-2 bg-red-200 text-red-900 px-2 py-0.5 rounded-full text-xs font-bold"><?= $status_counts['failed'] ?></span>
                </a>
            </div>
        </div>

        <!-- Filtros Avançados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="<?= base_url('admin/donations') ?>" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Busca -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-search text-primary-500 mr-2"></i>Buscar
                        </label>
                        <input type="text"
                               name="search"
                               value="<?= esc($filters['search'] ?? '') ?>"
                               placeholder="Nome do doador, email, campanha..."
                               class="form-input w-full">
                    </div>

                    <!-- Método de Pagamento -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-credit-card text-primary-500 mr-2"></i>Método
                        </label>
                        <select name="payment_method" class="form-input w-full">
                            <option value="">Todos</option>
                            <option value="pix" <?= ($filters['payment_method'] ?? '') === 'pix' ? 'selected' : '' ?>>PIX</option>
                            <option value="credit_card" <?= ($filters['payment_method'] ?? '') === 'credit_card' ? 'selected' : '' ?>>Cartão de Crédito</option>
                            <option value="boleto" <?= ($filters['payment_method'] ?? '') === 'boleto' ? 'selected' : '' ?>>Boleto</option>
                        </select>
                    </div>

                    <!-- Campanha -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-bullhorn text-primary-500 mr-2"></i>Campanha
                        </label>
                        <select name="campaign_id" class="form-input w-full">
                            <option value="">Todas</option>
                            <?php foreach ($campaigns_list as $camp): ?>
                                <option value="<?= $camp['id'] ?>" <?= ($filters['campaign_id'] ?? '') == $camp['id'] ? 'selected' : '' ?>>
                                    <?= esc(strlen($camp['title']) > 40 ? substr($camp['title'], 0, 37) . '...' : $camp['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-info-circle text-primary-500 mr-2"></i>Status
                        </label>
                        <select name="status" class="form-input w-full">
                            <option value="">Todos</option>
                            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
                            <option value="confirmed" <?= ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmada</option>
                            <option value="received" <?= ($filters['status'] ?? '') === 'received' ? 'selected' : '' ?>>Recebida</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Falhada</option>
                        </select>
                    </div>

                    <!-- Ordenar por -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sort text-primary-500 mr-2"></i>Ordenar por
                        </label>
                        <select name="sort_by" class="form-input w-full">
                            <option value="created_at" <?= ($filters['sort_by'] ?? 'created_at') === 'created_at' ? 'selected' : '' ?>>Data</option>
                            <option value="amount" <?= ($filters['sort_by'] ?? '') === 'amount' ? 'selected' : '' ?>>Valor</option>
                            <option value="status" <?= ($filters['sort_by'] ?? '') === 'status' ? 'selected' : '' ?>>Status</option>
                            <option value="payment_method" <?= ($filters['sort_by'] ?? '') === 'payment_method' ? 'selected' : '' ?>>Método</option>
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

                    <!-- Valor mínimo -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign text-primary-500 mr-2"></i>Valor Mín.
                        </label>
                        <input type="number"
                               name="amount_from"
                               value="<?= esc($filters['amount_from'] ?? '') ?>"
                               placeholder="0.00"
                               step="0.01"
                               class="form-input w-full">
                    </div>

                    <!-- Valor máximo -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign text-primary-500 mr-2"></i>Valor Máx.
                        </label>
                        <input type="number"
                               name="amount_to"
                               value="<?= esc($filters['amount_to'] ?? '') ?>"
                               placeholder="10000.00"
                               step="0.01"
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
                        <a href="<?= base_url('admin/donations') ?>"
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
                        <?php if (!empty($filters['status'])): ?>
                            <span class="inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-info-circle mr-2"></i>
                                Status: <?= esc(ucfirst($filters['status'])) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['payment_method'])): ?>
                            <span class="inline-flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-credit-card mr-2"></i>
                                Método: <?= esc(strtoupper($filters['payment_method'])) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['campaign_id'])): ?>
                            <span class="inline-flex items-center bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-bullhorn mr-2"></i>
                                Campanha específica
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['date_from']) || !empty($filters['date_to'])): ?>
                            <span class="inline-flex items-center bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-calendar mr-2"></i>
                                Período: <?= esc($filters['date_from'] ?? '...') ?> até <?= esc($filters['date_to'] ?? '...') ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($filters['amount_from']) || !empty($filters['amount_to'])): ?>
                            <span class="inline-flex items-center bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">
                                <i class="fas fa-dollar-sign mr-2"></i>
                                Valor: R$ <?= esc($filters['amount_from'] ?? '0') ?> - R$ <?= esc($filters['amount_to'] ?? '∞') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-hand-holding-usd text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total Arrecadado</p>
                        <p class="text-2xl font-bold text-gray-900">R$ <?= number_format($total_donated ?? 0, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-receipt text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Total de Doações</p>
                        <p class="text-2xl font-bold text-gray-900"><?= count($donations ?? []) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Confirmadas</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?= count(array_filter($donations ?? [], fn($d) => ($d['status'] ?? '') === 'confirmed')) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">Pendentes</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?= count(array_filter($donations ?? [], fn($d) => ($d['status'] ?? '') === 'pending')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Donations Table -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Lista de Doações</h3>
                    <div class="flex items-center space-x-4">
                        <input type="text" id="searchDonations" placeholder="Buscar doações..."
                               class="form-input text-sm py-2 w-64">
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doador</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campanha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($donations)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                                    <p>Nenhuma doação encontrada</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($donations as $donation): ?>
                                <tr class="hover:bg-gray-50 donation-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        #<?= $donation['id'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= esc($donation['donor_name'] ?? 'Anônimo') ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= esc($donation['donor_email'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">
                                            <a href="<?= base_url('campanhas/' . ($donation['campaign_slug'] ?? '')) ?>"
                                               class="text-primary-600 hover:text-primary-800" target="_blank">
                                                <?= esc($donation['campaign_title'] ?? 'Campanha não encontrada') ?>
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            por <?= esc($donation['creator_name'] ?? '-') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-green-600">
                                            R$ <?= number_format($donation['amount'] ?? 0, 2, ',', '.') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $method = $donation['payment_method'] ?? 'pix';
                                        $methodIcons = [
                                            'pix' => '<i class="fas fa-qrcode text-green-500"></i> PIX',
                                            'credit_card' => '<i class="fas fa-credit-card text-blue-500"></i> Cartão',
                                            'boleto' => '<i class="fas fa-barcode text-gray-500"></i> Boleto'
                                        ];
                                        ?>
                                        <span class="text-sm">
                                            <?= $methodIcons[$method] ?? $method ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status = $donation['status'] ?? 'pending';
                                        $statusClasses = [
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'failed' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $statusLabels = [
                                            'confirmed' => 'Confirmada',
                                            'pending' => 'Pendente',
                                            'failed' => 'Falhou',
                                            'refunded' => 'Reembolsada'
                                        ];
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' ?>">
                                            <?= $statusLabels[$status] ?? ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d/m/Y H:i', strtotime($donation['created_at'] ?? 'now')) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?= base_url('campanhas/' . ($donation['campaign_slug'] ?? '')) ?>"
                                           class="text-primary-600 hover:text-primary-900 mr-3" target="_blank" title="Ver Campanha">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
// Filtro de busca
document.getElementById('searchDonations')?.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.donation-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
});
</script>

<?= $this->endSection() ?>
