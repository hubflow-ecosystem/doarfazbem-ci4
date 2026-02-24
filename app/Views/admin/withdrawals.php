<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gerenciar Saques</h1>
        <p class="text-gray-600">Aprovar e processar solicitações de saque dos criadores</p>
    </div>

    <!-- Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Pendentes</div>
            <div class="text-2xl font-bold text-yellow-600"><?= $stats['pending_count'] ?? 0 ?></div>
            <div class="text-xs text-gray-400">R$ <?= number_format($stats['pending_amount'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Processando</div>
            <div class="text-2xl font-bold text-blue-600"><?= $stats['processing_count'] ?? 0 ?></div>
            <div class="text-xs text-gray-400">R$ <?= number_format($stats['processing_amount'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Concluídos (mês)</div>
            <div class="text-2xl font-bold text-green-600"><?= $stats['completed_count'] ?? 0 ?></div>
            <div class="text-xs text-gray-400">R$ <?= number_format($stats['completed_amount'] ?? 0, 2, ',', '.') ?></div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-sm text-gray-500">Taxas Arrecadadas</div>
            <div class="text-2xl font-bold text-purple-600">R$ <?= number_format($stats['total_fees'] ?? 0, 2, ',', '.') ?></div>
            <div class="text-xs text-gray-400">2,5% + R$ 2,00</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-600">Status:</span>
            <a href="<?= base_url('admin/withdrawals') ?>"
               class="px-3 py-1 rounded-full text-sm <?= empty($currentStatus) ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                Todos
            </a>
            <a href="<?= base_url('admin/withdrawals?status=pending') ?>"
               class="px-3 py-1 rounded-full text-sm <?= $currentStatus === 'pending' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' ?>">
                Pendentes
            </a>
            <a href="<?= base_url('admin/withdrawals?status=processing') ?>"
               class="px-3 py-1 rounded-full text-sm <?= $currentStatus === 'processing' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200' ?>">
                Processando
            </a>
            <a href="<?= base_url('admin/withdrawals?status=completed') ?>"
               class="px-3 py-1 rounded-full text-sm <?= $currentStatus === 'completed' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700 hover:bg-green-200' ?>">
                Concluídos
            </a>
            <a href="<?= base_url('admin/withdrawals?status=failed') ?>"
               class="px-3 py-1 rounded-full text-sm <?= $currentStatus === 'failed' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 hover:bg-red-200' ?>">
                Rejeitados
            </a>
        </div>
    </div>

    <!-- Lista de Saques -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <?php if (empty($withdrawals)): ?>
        <div class="p-12 text-center">
            <div class="text-5xl text-gray-300 mb-4">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-600">Nenhuma solicitação encontrada</h3>
            <p class="text-gray-500 mt-2">Não há solicitações de saque<?= $currentStatus ? ' com status "' . $currentStatus . '"' : '' ?>.</p>
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criador</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campanha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Líquido</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($withdrawals as $withdrawal): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        #<?= $withdrawal['id'] ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900"><?= esc($withdrawal['user_name'] ?? 'N/A') ?></div>
                        <div class="text-xs text-gray-500"><?= esc($withdrawal['user_email'] ?? '') ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate"><?= esc($withdrawal['campaign_title'] ?? 'N/A') ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900">R$ <?= number_format($withdrawal['amount'], 2, ',', '.') ?></div>
                        <div class="text-xs text-red-500">- R$ <?= number_format($withdrawal['fee_amount'], 2, ',', '.') ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold text-green-600">R$ <?= number_format($withdrawal['net_amount'], 2, ',', '.') ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($withdrawal['payment_method'] === 'pix'): ?>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-teal-100 text-teal-800">
                                <i class="fas fa-bolt mr-1"></i>PIX
                            </span>
                            <div class="text-xs text-gray-500 mt-1"><?= esc($withdrawal['pix_key'] ?? '') ?></div>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-university mr-1"></i>TED
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                Banco <?= esc($withdrawal['bank_code'] ?? '') ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                        $statusClass = match($withdrawal['status']) {
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'cancelled' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        $statusLabel = match($withdrawal['status']) {
                            'pending' => 'Pendente',
                            'processing' => 'Processando',
                            'completed' => 'Concluído',
                            'failed' => 'Rejeitado',
                            'cancelled' => 'Cancelado',
                            default => $withdrawal['status']
                        };
                        ?>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d/m/Y', strtotime($withdrawal['created_at'])) ?><br>
                        <span class="text-xs"><?= date('H:i', strtotime($withdrawal['created_at'])) ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center space-x-2">
                            <?php if ($withdrawal['status'] === 'pending'): ?>
                                <!-- Aprovar -->
                                <form action="<?= base_url('admin/withdrawals/approve/' . $withdrawal['id']) ?>" method="POST" class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-green-600 hover:text-green-900" title="Aprovar e Processar"
                                            onclick="return confirm('Confirma aprovar e processar este saque?\n\nValor: R$ <?= number_format($withdrawal['net_amount'], 2, ',', '.') ?>\nMétodo: <?= $withdrawal['payment_method'] === 'pix' ? 'PIX' : 'TED' ?>')">
                                        <i class="fas fa-check-circle text-lg"></i>
                                    </button>
                                </form>

                                <!-- Rejeitar -->
                                <button type="button"
                                        onclick="openRejectModal(<?= $withdrawal['id'] ?>)"
                                        class="text-red-600 hover:text-red-900" title="Rejeitar">
                                    <i class="fas fa-times-circle text-lg"></i>
                                </button>
                            <?php endif; ?>

                            <?php if ($withdrawal['status'] === 'completed' && $withdrawal['asaas_transfer_id']): ?>
                                <span class="text-xs text-gray-400" title="ID Asaas">
                                    <i class="fas fa-check text-green-500"></i>
                                    <?= esc($withdrawal['asaas_transfer_id']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Rejeição -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <form id="rejectForm" method="POST">
            <?= csrf_field() ?>
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Rejeitar Solicitação de Saque</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo da rejeição</label>
                    <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                              placeholder="Informe o motivo da rejeição..." required></textarea>
                    <p class="text-xs text-gray-500 mt-1">Este motivo será registrado e poderá ser enviado ao criador.</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-xl">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Confirmar Rejeição
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = '<?= base_url('admin/withdrawals/reject/') ?>' + id;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});
</script>

<?= $this->endSection() ?>
