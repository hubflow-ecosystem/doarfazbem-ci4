<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50 py-8">
    <div class="container-custom max-w-5xl">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="<?= base_url('rifas/historico') ?>" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Voltar ao Historico
            </a>
        </div>

        <!-- Header da Rifa -->
        <div class="bg-white rounded-2xl shadow-card overflow-hidden mb-8">
            <div class="md:flex">
                <!-- Imagem -->
                <div class="md:w-80 h-64 md:h-auto flex-shrink-0">
                    <?php if ($raffle['image']): ?>
                    <img src="<?= base_url('uploads/raffles/' . $raffle['image']) ?>"
                         alt="<?= esc($raffle['title']) ?>"
                         class="w-full h-full object-cover">
                    <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-white text-6xl"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info Principal -->
                <div class="flex-1 p-8">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i> Rifa Finalizada
                            </span>
                            <h1 class="text-3xl font-bold text-gray-900 mt-3"><?= esc($raffle['title']) ?></h1>
                        </div>
                    </div>

                    <?php if ($raffle['description']): ?>
                    <p class="text-gray-600 mb-6"><?= nl2br(esc($raffle['description'])) ?></p>
                    <?php endif; ?>

                    <!-- Dados do Sorteio -->
                    <div class="bg-gradient-to-r from-teal-500 to-emerald-500 rounded-xl p-6 text-white">
                        <h3 class="text-lg font-semibold mb-4">
                            <i class="fas fa-dice mr-2"></i> Dados do Sorteio
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-teal-100 text-sm">Data do Sorteio</div>
                                <div class="font-bold text-lg">
                                    <?php
                                    $drawDate = $raffle['federal_lottery_date'] ?? '';
                                    echo ($drawDate && $drawDate != '0000-00-00') ? date('d/m/Y', strtotime($drawDate)) : 'A definir';
                                    ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-teal-100 text-sm">Loteria Federal</div>
                                <div class="font-bold text-lg">
                                    <?= $raffle['federal_lottery_result'] ?? 'Aguardando' ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-teal-100 text-sm">Numero Sorteado</div>
                                <div class="font-mono font-bold text-2xl text-yellow-300">
                                    <?= $raffle['winning_number'] ?? '-' ?>
                                </div>
                            </div>
                            <div>
                                <div class="text-teal-100 text-sm">Numeros Vendidos</div>
                                <div class="font-bold text-lg">
                                    <?= number_format($raffle['numbers_sold'], 0, ',', '.') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ganhadores -->
        <div class="bg-white rounded-2xl shadow-card p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                Ganhadores
            </h2>

            <!-- Ganhador Principal -->
            <?php if (!empty($mainWinner)): ?>
            <div class="bg-gradient-to-r from-yellow-400 to-orange-400 rounded-xl p-6 mb-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <i class="fas fa-crown text-3xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-yellow-100 text-sm font-medium">Premio Principal</div>
                        <div class="text-2xl font-bold"><?= \App\Models\RaffleWinnerModel::maskName($mainWinner['winner_name']) ?></div>
                        <div class="text-yellow-100 text-sm">
                            <i class="fas fa-ticket-alt mr-1"></i>
                            Numero: <span class="font-mono font-bold text-white"><?= $mainWinner['winning_number'] ?></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">R$ <?= number_format($mainWinner['prize_amount'], 2, ',', '.') ?></div>
                        <?php if ($mainWinner['payment_status'] === 'paid'): ?>
                        <span class="inline-block mt-1 px-2 py-1 bg-white/20 rounded text-sm">
                            <i class="fas fa-check mr-1"></i> Pago
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($mainWinner['verification_code']): ?>
                <div class="mt-4 pt-4 border-t border-white/20 text-sm">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Codigo de Verificacao: <span class="font-mono font-bold"><?= $mainWinner['verification_code'] ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Top Compradores -->
            <?php if (!empty($rankingWinners)): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-medal text-teal-500 mr-2"></i>
                    Top Compradores
                </h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <?php
                    $medals = [
                        1 => ['bg-yellow-400', 'text-yellow-900', 'border-yellow-500'],
                        2 => ['bg-gray-300', 'text-gray-700', 'border-gray-400'],
                        3 => ['bg-orange-400', 'text-orange-900', 'border-orange-500'],
                    ];
                    foreach ($rankingWinners as $winner):
                        $pos = $winner['ranking_position'];
                        $medal = $medals[$pos] ?? ['bg-gray-200', 'text-gray-600', 'border-gray-300'];
                    ?>
                    <div class="bg-gray-50 rounded-xl p-5 border-2 <?= $medal[2] ?>">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 <?= $medal[0] ?> <?= $medal[1] ?> rounded-full flex items-center justify-center font-bold text-lg">
                                <?= $pos ?>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">
                                    <?= \App\Models\RaffleWinnerModel::maskName($winner['winner_name']) ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= number_format($winner['total_numbers_bought'], 0, ',', '.') ?> numeros comprados
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-green-600">
                                R$ <?= number_format($winner['prize_amount'], 2, ',', '.') ?>
                            </div>
                            <?php if ($winner['payment_status'] === 'paid'): ?>
                            <span class="text-xs text-green-600">
                                <i class="fas fa-check mr-1"></i> Pago
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($winner['verification_code']): ?>
                        <div class="mt-3 pt-3 border-t text-xs text-gray-500">
                            <i class="fas fa-shield-alt mr-1"></i>
                            <?= $winner['verification_code'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Premios Especiais (Cotas Premiadas) -->
            <?php if (!empty($specialWinners)): ?>
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-gift text-purple-500 mr-2"></i>
                    Cotas Premiadas
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Premio</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ganhador</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($specialWinners as $winner): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-mono font-bold text-teal-600 text-lg">
                                        <?= $winner['winning_number'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-gray-900"><?= esc($winner['prize_name']) ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-gray-700">
                                        <?= \App\Models\RaffleWinnerModel::maskName($winner['winner_name']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="font-bold text-green-600">
                                        R$ <?= number_format($winner['prize_amount'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($winner['payment_status'] === 'paid'): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        Pago
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        Pendente
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <?php if (empty($mainWinner) && empty($rankingWinners) && empty($specialWinners)): ?>
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-clock text-4xl mb-2"></i>
                <p>Os ganhadores serao divulgados apos a finalizacao do sorteio.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Campanhas Beneficiadas -->
        <?php if (!empty($campaigns)): ?>
        <div class="bg-white rounded-2xl shadow-card p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-heart text-red-500 mr-2"></i>
                Campanhas Beneficiadas
            </h2>

            <div class="grid md:grid-cols-2 gap-4">
                <?php foreach ($campaigns as $campaign): ?>
                <a href="<?= base_url('campaigns/' . $campaign['campaign_id']) ?>"
                   class="flex items-center gap-4 bg-gray-50 hover:bg-emerald-50 rounded-xl p-4 transition-colors">
                    <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0">
                        <?php if (!empty($campaign['campaign_image'])): ?>
                        <img src="<?= base_url('uploads/campaigns/' . $campaign['campaign_image']) ?>"
                             alt="<?= esc($campaign['campaign_title']) ?>"
                             class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center">
                            <i class="fas fa-hand-holding-heart text-white"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900"><?= esc($campaign['campaign_title']) ?></h4>
                        <p class="text-sm text-gray-500">
                            <?= $campaign['percentage'] ?>% da arrecadacao
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-green-600">
                            R$ <?= number_format($campaign['amount'], 2, ',', '.') ?>
                        </div>
                        <?php if ($campaign['transferred']): ?>
                        <span class="text-xs text-green-600">
                            <i class="fas fa-check mr-1"></i> Transferido
                        </span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Total para Campanhas -->
            <div class="mt-6 pt-6 border-t">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Total destinado a campanhas:</span>
                    <span class="text-2xl font-bold text-green-600">
                        R$ <?= number_format(array_sum(array_column($campaigns, 'amount')), 2, ',', '.') ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resumo Financeiro -->
        <div class="bg-white rounded-2xl shadow-card p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fas fa-chart-pie text-teal-500 mr-2"></i>
                Transparencia Financeira
            </h2>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Arrecadacao -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Arrecadacao</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Numeros vendidos:</span>
                            <span class="font-semibold"><?= number_format($raffle['numbers_sold'], 0, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Preco por numero:</span>
                            <span class="font-semibold">R$ <?= number_format($raffle['number_price'], 2, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between text-lg border-t pt-3">
                            <span class="text-gray-800 font-medium">Total arrecadado:</span>
                            <span class="font-bold text-green-600">R$ <?= number_format($raffle['total_revenue'], 2, ',', '.') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Distribuicao -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribuicao</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">
                                <i class="fas fa-trophy text-yellow-500 mr-1"></i>
                                Premio principal (<?= $raffle['main_prize_percentage'] ?>%):
                            </span>
                            <span class="font-semibold text-yellow-600">
                                R$ <?= number_format($raffle['total_revenue'] * $raffle['main_prize_percentage'] / 100, 2, ',', '.') ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">
                                <i class="fas fa-heart text-red-500 mr-1"></i>
                                Campanhas (<?= $raffle['campaign_percentage'] ?>%):
                            </span>
                            <span class="font-semibold text-red-600">
                                R$ <?= number_format($raffle['total_revenue'] * $raffle['campaign_percentage'] / 100, 2, ',', '.') ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">
                                <i class="fas fa-building text-teal-500 mr-1"></i>
                                Plataforma:
                            </span>
                            <span class="font-semibold text-teal-600">
                                R$ <?= number_format($raffle['total_revenue'] * (100 - $raffle['main_prize_percentage'] - $raffle['campaign_percentage']) / 100, 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aviso de Auditoria -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Todos os valores sao calculados automaticamente e podem ser auditados. O sorteio utiliza os resultados
                    oficiais da Loteria Federal como fonte de aleatoriedade.
                </p>
            </div>
        </div>

        <!-- Acoes -->
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="<?= base_url('rifas/historico') ?>" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>
                Ver Outras Rifas
            </a>
            <a href="<?= base_url('rifas') ?>" class="btn-primary">
                <i class="fas fa-ticket-alt mr-2"></i>
                Rifas Ativas
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
