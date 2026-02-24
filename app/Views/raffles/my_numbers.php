<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 py-12">
    <div class="container-custom">

        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">
                <i class="fas fa-ticket-alt text-yellow-400 mr-3"></i>
                Meus Numeros da Sorte
            </h1>
            <p class="text-teal-200 text-lg">
                Acompanhe suas compras e torça pelos seus números!
            </p>
        </div>

        <?php if (!$raffle): ?>
        <!-- Sem Rifa Ativa -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-12 text-center">
            <i class="fas fa-ticket-alt text-6xl text-teal-300 mb-4"></i>
            <h3 class="text-xl text-white mb-2">Nenhuma rifa ativa no momento</h3>
            <p class="text-teal-200 mb-6">Aguarde a próxima edição dos Números da Sorte!</p>
            <a href="<?= base_url('campaigns') ?>" class="btn-primary">
                <i class="fas fa-heart mr-2"></i> Ver Campanhas
            </a>
        </div>
        <?php else: ?>

        <!-- Ranking do Usuário -->
        <?php if ($ranking): ?>
        <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 backdrop-blur-lg rounded-2xl p-6 mb-8 border border-yellow-500/30">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">
                            #<?= $ranking['position'] ?>
                        </span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Sua Posição no Ranking</h3>
                        <p class="text-yellow-200">
                            <?= number_format($ranking['total_numbers'], 0, ',', '.') ?> numeros comprados
                        </p>
                    </div>
                </div>
                <?php if ($ranking['position'] <= 3): ?>
                <div class="bg-yellow-500/30 rounded-xl px-6 py-3 text-center">
                    <p class="text-yellow-300 text-sm">Você está no TOP 3!</p>
                    <p class="text-yellow-100 font-bold text-lg">
                        <?php
                        // Calcular prêmios baseado no prêmio principal da rifa
                        $mainPrize = !empty($raffle['main_prize_amount']) ? $raffle['main_prize_amount'] : 100000;
                        $rankingPrizes = \App\Models\RaffleRankingModel::getRankingPrizes($mainPrize);
                        echo 'R$ ' . number_format($rankingPrizes[$ranking['position']] ?? 0, 0, ',', '.');
                        ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 text-center">
                <div class="text-3xl font-bold text-white">
                    <?= number_format(count($numbers), 0, ',', '.') ?>
                </div>
                <div class="text-teal-200 text-sm">Meus Números</div>
            </div>
            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 text-center">
                <div class="text-3xl font-bold text-green-400">
                    R$ <?= number_format(array_sum(array_column($purchases, 'total_amount')), 2, ',', '.') ?>
                </div>
                <div class="text-teal-200 text-sm">Total Investido</div>
            </div>
            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 text-center">
                <div class="text-3xl font-bold text-yellow-400">
                    <?= count($purchases) ?>
                </div>
                <div class="text-teal-200 text-sm">Compras</div>
            </div>
            <div class="bg-white/10 backdrop-blur-lg rounded-xl p-4 text-center">
                <div class="text-3xl font-bold text-blue-400">
                    <?php
                    $drawDate = $raffle['federal_lottery_date'] ?? '';
                    if ($drawDate && $drawDate != '0000-00-00' && strtotime($drawDate) !== false) {
                        echo date('d/m/Y', strtotime($drawDate));
                    } else {
                        echo 'A definir';
                    }
                    ?>
                </div>
                <div class="text-teal-200 text-sm">Sorteio</div>
            </div>
        </div>

        <!-- Lista de Números -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-list-ol text-yellow-400 mr-2"></i>
                Seus Números
            </h2>

            <?php if (empty($numbers)): ?>
            <div class="text-center py-12">
                <i class="fas fa-ticket-alt text-6xl text-teal-400 mb-4"></i>
                <h3 class="text-xl text-white mb-2">Você ainda não tem números</h3>
                <p class="text-teal-200 mb-6">Compre seus números da sorte e concorra a prêmios!</p>
                <a href="<?= base_url('rifas/' . $raffle['slug']) ?>" class="btn-primary">
                    <i class="fas fa-shopping-cart mr-2"></i> Comprar Números
                </a>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-2">
                <?php foreach ($numbers as $number): ?>
                <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-lg p-2 text-center <?= !empty($number['is_special']) ? 'ring-2 ring-yellow-400' : '' ?>">
                    <span class="text-white font-mono font-bold text-sm">
                        <?= $number['number'] ?>
                    </span>
                    <?php if (!empty($number['is_special'])): ?>
                    <i class="fas fa-star text-yellow-400 text-xs block mt-1"></i>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Histórico de Compras -->
        <?php if (!empty($purchases)): ?>
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-history text-green-400 mr-2"></i>
                Histórico de Compras
            </h2>

            <div class="space-y-4">
                <?php foreach ($purchases as $purchase): ?>
                <div class="bg-white/5 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <div class="text-white font-semibold">
                            <?= number_format($purchase['quantity'], 0, ',', '.') ?> números
                        </div>
                        <div class="text-teal-300 text-sm">
                            <?= date('d/m/Y H:i', strtotime($purchase['created_at'])) ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-green-400 font-bold">
                            R$ <?= number_format($purchase['total_amount'], 2, ',', '.') ?>
                        </div>
                        <?php if ($purchase['payment_status'] === 'paid'): ?>
                        <span class="px-2 py-1 bg-green-500/30 text-green-300 text-xs rounded-full">
                            Confirmado
                        </span>
                        <?php elseif ($purchase['payment_status'] === 'pending'): ?>
                        <span class="px-2 py-1 bg-yellow-500/30 text-yellow-300 text-xs rounded-full">
                            Pendente
                        </span>
                        <?php else: ?>
                        <span class="px-2 py-1 bg-red-500/30 text-red-300 text-xs rounded-full">
                            Expirado
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CTA -->
        <div class="text-center mt-8">
            <a href="<?= base_url('rifas/' . $raffle['slug']) ?>"
               class="inline-block px-8 py-4 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-bold text-lg rounded-xl hover:from-yellow-600 hover:to-orange-600 transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-plus mr-2"></i>
                Comprar Mais Números
            </a>
        </div>

        <?php endif; ?>

    </div>
</div>

<?= $this->endSection() ?>
