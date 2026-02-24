<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50 py-8">
    <div class="container-custom max-w-6xl">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                <i class="fas fa-history text-teal-600 mr-3"></i>
                Historico de Rifas
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Transparencia total: veja todas as rifas realizadas, ganhadores e campanhas beneficiadas.
            </p>
        </div>

        <!-- Estatisticas Gerais -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
            <div class="bg-white rounded-xl shadow-card p-6 text-center">
                <div class="text-3xl font-bold text-teal-600"><?= $stats['total_raffles'] ?? 0 ?></div>
                <div class="text-sm text-gray-500">Rifas Realizadas</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-6 text-center">
                <div class="text-3xl font-bold text-green-600"><?= $stats['total_winners'] ?? 0 ?></div>
                <div class="text-sm text-gray-500">Ganhadores</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-6 text-center">
                <div class="text-3xl font-bold text-yellow-600">R$ <?= number_format($stats['total_prizes'] ?? 0, 0, ',', '.') ?></div>
                <div class="text-sm text-gray-500">em Premios</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-6 text-center">
                <div class="text-3xl font-bold text-purple-600">R$ <?= number_format($stats['total_campaigns'] ?? 0, 0, ',', '.') ?></div>
                <div class="text-sm text-gray-500">para Campanhas</div>
            </div>
        </div>

        <!-- Lista de Rifas Finalizadas -->
        <?php if (!empty($raffles)): ?>
        <div class="space-y-6">
            <?php foreach ($raffles as $raffle): ?>
            <div class="bg-white rounded-2xl shadow-card overflow-hidden hover:shadow-lg transition-shadow">
                <div class="md:flex">
                    <!-- Imagem -->
                    <div class="md:w-48 h-48 md:h-auto flex-shrink-0">
                        <?php if ($raffle['image']): ?>
                        <img src="<?= base_url('uploads/raffles/' . $raffle['image']) ?>"
                             alt="<?= esc($raffle['title']) ?>"
                             class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-teal-400 to-emerald-500 flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-white text-4xl"></i>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Conteudo -->
                    <div class="flex-1 p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900"><?= esc($raffle['title']) ?></h2>
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Sorteio: <?php
                                    $drawDate = $raffle['federal_lottery_date'] ?? '';
                                    echo ($drawDate && $drawDate != '0000-00-00') ? date('d/m/Y', strtotime($drawDate)) : 'A definir';
                                    ?>
                                </p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle mr-1"></i> Finalizada
                            </span>
                        </div>

                        <!-- Info do Sorteio -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
                            <div>
                                <span class="text-gray-500">Numeros Vendidos:</span>
                                <span class="font-bold text-gray-900 block"><?= number_format($raffle['numbers_sold'], 0, ',', '.') ?></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Arrecadacao:</span>
                                <span class="font-bold text-green-600 block">R$ <?= number_format($raffle['total_revenue'], 2, ',', '.') ?></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Numero Sorteado:</span>
                                <span class="font-mono font-bold text-teal-600 block"><?= $raffle['winning_number'] ?? '-' ?></span>
                            </div>
                            <div>
                                <span class="text-gray-500">Loteria Federal:</span>
                                <span class="font-bold text-gray-900 block"><?= $raffle['federal_lottery_result'] ?? '-' ?></span>
                            </div>
                        </div>

                        <!-- Ganhadores Resumo -->
                        <?php if (!empty($raffle['winners'])): ?>
                        <div class="bg-yellow-50 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-yellow-800 mb-2">
                                <i class="fas fa-trophy mr-1"></i> Ganhadores
                            </h4>
                            <div class="flex flex-wrap gap-3 text-sm">
                                <?php foreach ($raffle['winners'] as $winner): ?>
                                <div class="bg-white rounded px-3 py-1 shadow-sm">
                                    <?php if ($winner['prize_type'] === 'main'): ?>
                                    <span class="text-yellow-600 font-bold">
                                        <i class="fas fa-crown mr-1"></i> Principal:
                                    </span>
                                    <?php elseif ($winner['prize_type'] === 'ranking'): ?>
                                    <span class="text-teal-600 font-bold">
                                        <?= $winner['ranking_position'] ?>º:
                                    </span>
                                    <?php else: ?>
                                    <span class="text-purple-600 font-bold">
                                        <i class="fas fa-gift mr-1"></i>
                                    </span>
                                    <?php endif; ?>
                                    <?= \App\Models\RaffleWinnerModel::maskName($winner['winner_name']) ?>
                                    <span class="text-green-600 font-semibold ml-1">
                                        R$ <?= number_format($winner['prize_amount'], 0, ',', '.') ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Campanhas Beneficiadas -->
                        <?php if (!empty($raffle['campaigns'])): ?>
                        <div class="bg-emerald-50 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-teal-800 mb-2">
                                <i class="fas fa-heart mr-1"></i> Campanhas Beneficiadas
                            </h4>
                            <div class="flex flex-wrap gap-2 text-sm">
                                <?php foreach ($raffle['campaigns'] as $campaign): ?>
                                <a href="<?= base_url('campaigns/' . $campaign['campaign_id']) ?>"
                                   class="bg-white rounded px-3 py-1 shadow-sm hover:bg-teal-50 transition-colors">
                                    <?= esc($campaign['campaign_title']) ?>
                                    <span class="text-green-600 font-semibold">
                                        R$ <?= number_format($campaign['amount'], 2, ',', '.') ?>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Link para Detalhes -->
                        <a href="<?= base_url('rifas/historico/' . $raffle['slug']) ?>"
                           class="inline-flex items-center text-teal-600 hover:text-teal-800 font-semibold">
                            Ver detalhes completos
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginacao -->
        <?php if ($pager): ?>
        <div class="mt-8 flex justify-center">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-card p-12 text-center">
            <div class="text-6xl text-gray-300 mb-4">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-600 mb-2">Nenhuma rifa finalizada ainda</h3>
            <p class="text-gray-500 mb-6">As rifas finalizadas aparecerão aqui com todos os detalhes.</p>
            <a href="<?= base_url('rifas') ?>" class="btn-primary">
                Ver Rifas Ativas
            </a>
        </div>
        <?php endif; ?>

        <!-- Verificar Premio -->
        <div class="mt-10 bg-white rounded-2xl shadow-card p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4 text-center">
                <i class="fas fa-search text-teal-600 mr-2"></i>
                Verificar Premio
            </h3>
            <p class="text-gray-600 text-center mb-6">
                Recebeu um codigo de verificacao? Confira a autenticidade do seu premio.
            </p>
            <form action="<?= base_url('rifas/verificar') ?>" method="get" class="max-w-md mx-auto flex gap-2">
                <input type="text" name="code" placeholder="Digite o codigo de verificacao"
                       class="form-input flex-1" required>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
