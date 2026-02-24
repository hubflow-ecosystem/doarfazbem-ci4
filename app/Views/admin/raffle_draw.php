<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="<?= base_url('admin/raffles') ?>" class="text-gray-600 hover:text-gray-900 mb-2 inline-flex items-center text-sm">
                <i class="fas fa-arrow-left mr-2"></i>Voltar para Rifas
            </a>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($raffle['title']) ?></h1>
            <p class="text-gray-600">Realizar sorteio e visualizar ganhadores</p>
        </div>

        <?php
        $statusClass = match($raffle['status']) {
            'active' => 'bg-green-100 text-green-800',
            'paused' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
        $statusLabel = match($raffle['status']) {
            'active' => 'Ativa',
            'paused' => 'Pausada',
            'completed' => 'Finalizada',
            default => $raffle['status']
        };
        ?>
        <span class="px-4 py-2 rounded-full text-sm font-semibold <?= $statusClass ?>">
            <?= $statusLabel ?>
        </span>
    </div>

    <?php if ($raffle['status'] === 'completed'): ?>
    <!-- Resultado do Sorteio -->
    <div class="bg-gradient-to-r from-green-500 to-teal-500 rounded-xl p-8 mb-6 text-white">
        <div class="text-center">
            <div class="text-5xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold mb-2">Sorteio Realizado!</h2>
            <div class="text-6xl font-bold font-mono my-4"><?= esc($raffle['winning_number']) ?></div>
            <p class="text-white/80">Numero Sorteado</p>
            <?php if (!empty($raffle['federal_lottery_result'])): ?>
            <p class="text-sm mt-2">Resultado Loteria Federal: <?= esc($raffle['federal_lottery_result']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Lista de Ganhadores -->
    <?php if (!empty($winners)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Ganhadores</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ganhador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero/Posicao</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Premio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Pagamento</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($winners as $winner): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $typeClass = match($winner['prize_type']) {
                                'main' => 'bg-yellow-100 text-yellow-800',
                                'ranking' => 'bg-purple-100 text-purple-800',
                                'special' => 'bg-pink-100 text-pink-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $typeLabel = match($winner['prize_type']) {
                                'main' => 'Principal',
                                'ranking' => 'Ranking',
                                'special' => 'Cota Premiada',
                                default => $winner['prize_type']
                            };
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $typeClass ?>">
                                <?= $typeLabel ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= esc($winner['winner_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= esc($winner['winner_email']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($winner['prize_type'] === 'main'): ?>
                                <span class="font-mono text-lg font-bold text-green-600"><?= esc($winner['winning_number']) ?></span>
                            <?php elseif ($winner['prize_type'] === 'ranking'): ?>
                                <span class="font-bold"><?= $winner['ranking_position'] ?>º Lugar</span>
                                <span class="text-sm text-gray-500">(<?= $winner['total_numbers_bought'] ?> numeros)</span>
                            <?php else: ?>
                                <span class="font-mono"><?= esc($winner['winning_number']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-lg font-bold text-green-600">R$ <?= number_format($winner['prize_amount'], 2, ',', '.') ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $paymentClass = match($winner['payment_status'] ?? 'pending') {
                                'paid' => 'bg-green-100 text-green-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $paymentLabel = match($winner['payment_status'] ?? 'pending') {
                                'paid' => 'Pago',
                                'processing' => 'Processando',
                                'pending' => 'Pendente',
                                default => 'Pendente'
                            };
                            ?>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $paymentClass ?>">
                                <?= $paymentLabel ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Formulario para Realizar Sorteio -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Estatisticas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Numeros Vendidos</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?= number_format($paid_numbers_count) ?></p>
            <p class="text-sm text-gray-500">de <?= number_format($raffle['total_numbers']) ?> disponiveis</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Arrecadacao Total</h3>
            <p class="text-3xl font-bold text-green-600 mt-2">R$ <?= number_format($raffle['total_revenue'] ?? 0, 2, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Premio Principal</h3>
            <p class="text-3xl font-bold text-yellow-600 mt-2">R$ <?= number_format(($raffle['total_revenue'] ?? 0) * ($raffle['main_prize_percentage'] / 100), 2, ',', '.') ?></p>
            <p class="text-sm text-gray-500"><?= $raffle['main_prize_percentage'] ?>% da arrecadacao</p>
        </div>
    </div>

    <?php if ($paid_numbers_count > 0): ?>
    <!-- Formulario de Sorteio -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Realizar Sorteio</h3>

        <form action="<?= base_url('admin/raffles/complete/' . $raffle['id']) ?>" method="POST"
              onsubmit="return confirm('Tem certeza que deseja realizar o sorteio agora? Esta acao nao pode ser desfeita.')">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Resultado da Loteria Federal (opcional)
                </label>
                <input type="text" name="federal_result" class="form-input w-full max-w-xs"
                       placeholder="Ex: 12345" maxlength="10">
                <p class="text-xs text-gray-500 mt-1">
                    Se informado, os ultimos digitos serao usados para determinar o ganhador.
                    Caso contrario, sera sorteado aleatoriamente.
                </p>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-random mr-2"></i>Realizar Sorteio Agora
            </button>
        </form>
    </div>
    <?php else: ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Nao e possivel sortear</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    Esta rifa ainda nao possui numeros vendidos. Aguarde ate que pelo menos um numero seja comprado.
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Ranking de Compradores -->
    <?php if (!empty($rankings)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Top Compradores (Ranking)</h3>
            <p class="text-sm text-gray-500">Estes compradores receberao premios adicionais</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posicao</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Comprador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numeros Comprados</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Premio</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $rankingPrizes = [1 => 500, 2 => 300, 3 => 150, 4 => 100, 5 => 50];
                    foreach ($rankings as $index => $rank):
                        $position = $index + 1;
                        $prize = $rankingPrizes[$position] ?? 0;
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($position <= 3): ?>
                                <span class="text-2xl">
                                    <?= $position === 1 ? '🥇' : ($position === 2 ? '🥈' : '🥉') ?>
                                </span>
                            <?php else: ?>
                                <span class="text-lg font-bold text-gray-600"><?= $position ?>º</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900"><?= esc($rank['buyer_name']) ?></div>
                            <div class="text-xs text-gray-500"><?= esc($rank['buyer_email']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-lg font-bold text-purple-600"><?= $rank['total_numbers'] ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-lg font-bold text-green-600">R$ <?= number_format($prize, 2, ',', '.') ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
