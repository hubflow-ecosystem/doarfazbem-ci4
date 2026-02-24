<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 text-gray-900">Saques</h1>
                <p class="text-gray-600 mt-2">Gerencie os recebimentos das suas campanhas</p>
            </div>
            <?php if ($stats['total_available'] > 0): ?>
            <a href="<?= base_url('dashboard/withdrawals/request') ?>" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>Solicitar Saque
            </a>
            <?php endif; ?>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-wallet text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Saldo Disponível</p>
                        <p class="text-2xl font-bold text-green-600">
                            R$ <?= number_format($stats['total_available'], 2, ',', '.') ?>
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
                        <p class="text-sm text-gray-600">Saques Pendentes</p>
                        <p class="text-2xl font-bold text-yellow-600">
                            R$ <?= number_format($stats['pending_withdrawals'], 2, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Sacado</p>
                        <p class="text-2xl font-bold text-blue-600">
                            R$ <?= number_format($stats['total_withdrawn'], 2, ',', '.') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Saldo por Campanha -->
        <?php if (!empty($campaigns)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Saldo por Campanha</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <?php foreach ($campaigns as $campaign): ?>
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-900"><?= esc($campaign['title']) ?></h4>
                        <p class="text-sm text-gray-500">
                            Recebido: R$ <?= number_format($campaign['balance']['total_received'], 2, ',', '.') ?>
                            |
                            Sacado: R$ <?= number_format($campaign['balance']['total_withdrawn'], 2, ',', '.') ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold <?= $campaign['balance']['available'] > 0 ? 'text-green-600' : 'text-gray-400' ?>">
                            R$ <?= number_format($campaign['balance']['available'], 2, ',', '.') ?>
                        </p>
                        <p class="text-xs text-gray-500">disponível</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Histórico de Saques -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Histórico de Saques</h3>
            </div>

            <?php if (empty($withdrawals)): ?>
            <div class="p-12 text-center">
                <div class="text-5xl text-gray-300 mb-4">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h4 class="text-xl font-medium text-gray-600 mb-2">Nenhum saque realizado</h4>
                <p class="text-gray-500">Quando você solicitar saques, eles aparecerão aqui.</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Taxa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Líquido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($withdrawals as $withdrawal): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y', strtotime($withdrawal['created_at'])) ?>
                                <br>
                                <span class="text-xs text-gray-500">
                                    <?= date('H:i', strtotime($withdrawal['created_at'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                R$ <?= number_format($withdrawal['amount'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                - R$ <?= number_format($withdrawal['fee_amount'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                R$ <?= number_format($withdrawal['net_amount'], 2, ',', '.') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $withdrawal['payment_method'] === 'pix' ? 'PIX' : 'TED' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusConfig = [
                                    'pending' => ['bg-yellow-100 text-yellow-800', 'Pendente'],
                                    'processing' => ['bg-blue-100 text-blue-800', 'Processando'],
                                    'completed' => ['bg-green-100 text-green-800', 'Concluído'],
                                    'failed' => ['bg-red-100 text-red-800', 'Falhou'],
                                    'cancelled' => ['bg-gray-100 text-gray-800', 'Cancelado'],
                                ];
                                $config = $statusConfig[$withdrawal['status']] ?? ['bg-gray-100 text-gray-800', $withdrawal['status']];
                                ?>
                                <span class="px-3 py-1 text-xs font-medium rounded-full <?= $config[0] ?>">
                                    <?= $config[1] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($withdrawal['status'] === 'pending'): ?>
                                <form action="<?= base_url('dashboard/withdrawals/cancel/' . $withdrawal['id']) ?>" method="POST"
                                      onsubmit="return confirm('Cancelar esta solicitação?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </form>
                                <?php elseif ($withdrawal['status'] === 'completed' && $withdrawal['processed_at']): ?>
                                <span class="text-xs text-gray-500">
                                    <?= date('d/m/Y', strtotime($withdrawal['processed_at'])) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info sobre taxas -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h4 class="font-medium text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-1"></i>Informações sobre saques
            </h4>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Valor mínimo para saque: R$ 20,00</li>
                <li>• Taxa por saque: 2,5% + R$ 2,00</li>
                <li>• Prazo de processamento: até 3 dias úteis</li>
                <li>• Saques via PIX são processados mais rapidamente</li>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
