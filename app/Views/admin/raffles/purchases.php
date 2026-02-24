<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= base_url('admin/raffles/edit/' . $raffle['id']) ?>" class="text-gray-600 hover:text-gray-900 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Compras</h1>
            <p class="text-gray-600"><?= esc($raffle['title']) ?></p>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-gray-900"><?= $stats['total_purchases'] ?></div>
                <div class="text-sm text-gray-500">Total de Compras</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-green-600"><?= $stats['confirmed'] ?></div>
                <div class="text-sm text-gray-500">Confirmadas</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-yellow-600"><?= $stats['pending'] ?></div>
                <div class="text-sm text-gray-500">Pendentes</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-green-600">
                    R$ <?= number_format($stats['total_revenue'], 2, ',', '.') ?>
                </div>
                <div class="text-sm text-gray-500">Receita Total</div>
            </div>
        </div>

        <!-- Tabela de Compras -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuario</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cotas</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Valor</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($purchases as $purchase): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">
                                <span class="font-mono text-sm text-gray-600">#<?= $purchase['id'] ?></span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-gray-900">
                                    <?= esc($purchase['user_name'] ?? $purchase['buyer_name'] ?? 'Anonimo') ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= esc($purchase['user_email'] ?? $purchase['buyer_email'] ?? '-') ?>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-lg font-bold text-teal-600">
                                    <?= number_format($purchase['quantity'], 0, ',', '.') ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-lg font-bold text-green-600">
                                    R$ <?= number_format($purchase['total_amount'], 2, ',', '.') ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'expired' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusNames = [
                                    'pending' => 'Pendente',
                                    'paid' => 'Confirmado',
                                    'expired' => 'Expirado',
                                    'cancelled' => 'Cancelado',
                                ];
                                $currentStatus = $purchase['payment_status'] ?? 'pending';
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusColors[$currentStatus] ?? 'bg-gray-100 text-gray-800' ?>">
                                    <?= $statusNames[$currentStatus] ?? 'Desconhecido' ?>
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center text-sm text-gray-600">
                                <?= date('d/m/Y H:i', strtotime($purchase['created_at'])) ?>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <?php if (($purchase['payment_status'] ?? 'pending') === 'pending'): ?>
                                <form action="<?= base_url('admin/raffles/simulate-payment/' . $purchase['id']) ?>" method="post"
                                      class="inline" onsubmit="return confirm('Simular pagamento desta compra?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                                        <i class="fas fa-check mr-1"></i> Simular Pag.
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (empty($purchases)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                <p>Nenhuma compra realizada ainda</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info Modo Dev -->
        <?php if (ENVIRONMENT === 'development'): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Modo Desenvolvimento:</strong> Use o botao "Simular Pag." para confirmar compras pendentes sem pagamento real.
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
