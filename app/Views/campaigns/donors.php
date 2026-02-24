<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Doadores da Campanha</h1>
                    <p class="mt-2 text-gray-600"><?= esc($campaign['title']) ?></p>
                </div>
                <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar à Campanha
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total de Doadores</p>
                        <p class="text-2xl font-semibold text-gray-900"><?= count($donations) ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                        <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Arrecadado</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            R$ <?= number_format($campaign['current_amount'] ?? 0, 2, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-full p-3">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Média por Doação</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            R$ <?= count($donations) > 0 ? number_format(($campaign['current_amount'] ?? 0) / count($donations), 2, ',', '.') : '0,00' ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-percentage text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Progresso</p>
                        <p class="text-2xl font-semibold text-gray-900">
                            <?php
                            $percentage = $campaign['goal_amount'] > 0
                                ? round(($campaign['current_amount'] / $campaign['goal_amount']) * 100, 1)
                                : 0;
                            ?>
                            <?= $percentage ?>%
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Donors Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Lista de Doações</h3>
                    <div class="relative">
                        <input type="text" id="searchDonors" placeholder="Buscar doador..."
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-primary-500 focus:border-primary-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Doador
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Valor
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Método
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mensagem
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="donorsTable">
                        <?php foreach ($donations as $donation): ?>
                        <tr class="hover:bg-gray-50 donor-row">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <span class="text-primary-600 font-medium text-sm">
                                                <?= strtoupper(substr($donation['donor_name'] ?? 'A', 0, 2)) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 donor-name">
                                            <?= $donation['is_anonymous'] ? 'Doador Anônimo' : esc($donation['donor_name']) ?>
                                        </div>
                                        <?php if (!$donation['is_anonymous'] && !empty($donation['donor_email'])): ?>
                                            <div class="text-sm text-gray-500"><?= esc($donation['donor_email']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-green-600">
                                    R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $methodIcons = [
                                    'pix' => ['icon' => 'fas fa-qrcode', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
                                    'credit_card' => ['icon' => 'fas fa-credit-card', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100'],
                                    'boleto' => ['icon' => 'fas fa-barcode', 'color' => 'text-gray-600', 'bg' => 'bg-gray-100'],
                                ];
                                $method = $methodIcons[$donation['payment_method']] ?? $methodIcons['pix'];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $method['bg'] ?> <?= $method['color'] ?>">
                                    <i class="<?= $method['icon'] ?> mr-1"></i>
                                    <?= ucfirst(str_replace('_', ' ', $donation['payment_method'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusColors = [
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusLabels = [
                                    'confirmed' => 'Confirmado',
                                    'pending' => 'Pendente',
                                    'failed' => 'Falhou',
                                    'refunded' => 'Reembolsado',
                                ];
                                $statusColor = $statusColors[$donation['status']] ?? 'bg-gray-100 text-gray-800';
                                $statusLabel = $statusLabels[$donation['status']] ?? $donation['status'];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($donation['message'])): ?>
                                    <p class="text-sm text-gray-600 max-w-xs truncate" title="<?= esc($donation['message']) ?>">
                                        "<?= esc($donation['message']) ?>"
                                    </p>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($donations)): ?>
            <div class="text-center py-12">
                <i class="fas fa-hand-holding-heart text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">Ainda não há doações para esta campanha</p>
                <p class="text-sm text-gray-400 mt-2">Compartilhe sua campanha para receber as primeiras doações!</p>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
// Busca de doadores
document.getElementById('searchDonors').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.donor-row');

    rows.forEach(row => {
        const name = row.querySelector('.donor-name').textContent.toLowerCase();

        if (name.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?= $this->endSection() ?>
