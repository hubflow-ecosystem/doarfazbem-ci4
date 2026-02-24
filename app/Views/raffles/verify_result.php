<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50 py-12">
    <div class="container-custom max-w-2xl">
        <!-- Card Principal -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-teal-500 to-emerald-500 p-8 text-center text-white">
                <div class="w-20 h-20 bg-white/20 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-4xl"></i>
                </div>
                <h1 class="text-2xl font-bold mb-2">Verificacao de Premio</h1>
                <p class="text-teal-100">Certificado de Autenticidade</p>
            </div>

            <!-- Conteudo -->
            <div class="p-8">
                <!-- Status Verificado -->
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6 mb-6 text-center">
                    <div class="text-green-600 text-5xl mb-2">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="text-xl font-bold text-green-800">Premio Verificado</h2>
                    <p class="text-green-600">Este premio e autentico e foi registrado em nosso sistema.</p>
                </div>

                <!-- Detalhes do Premio -->
                <div class="space-y-4">
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Codigo de Verificacao:</span>
                        <span class="font-mono font-bold text-teal-600"><?= esc($winner['verification_code']) ?></span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Rifa:</span>
                        <span class="font-semibold text-gray-900"><?= esc($raffle['title']) ?></span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Tipo de Premio:</span>
                        <span class="font-semibold">
                            <?php
                            switch ($winner['prize_type']) {
                                case 'main':
                                    echo '<span class="text-yellow-600"><i class="fas fa-crown mr-1"></i> Premio Principal</span>';
                                    break;
                                case 'ranking':
                                    echo '<span class="text-teal-600"><i class="fas fa-medal mr-1"></i> ' . $winner['ranking_position'] . 'º Lugar - Top Comprador</span>';
                                    break;
                                case 'special':
                                    echo '<span class="text-purple-600"><i class="fas fa-gift mr-1"></i> Cota Premiada</span>';
                                    break;
                            }
                            ?>
                        </span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Premio:</span>
                        <span class="font-semibold text-gray-900"><?= esc($winner['prize_name']) ?></span>
                    </div>

                    <?php if ($winner['winning_number']): ?>
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Numero:</span>
                        <span class="font-mono font-bold text-lg text-teal-600"><?= esc($winner['winning_number']) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($winner['total_numbers_bought']): ?>
                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Numeros Comprados:</span>
                        <span class="font-semibold text-gray-900"><?= number_format($winner['total_numbers_bought'], 0, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Valor do Premio:</span>
                        <span class="text-2xl font-bold text-green-600">R$ <?= number_format($winner['prize_amount'], 2, ',', '.') ?></span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Ganhador:</span>
                        <span class="font-semibold text-gray-900">
                            <?= \App\Models\RaffleWinnerModel::maskName($winner['winner_name']) ?>
                        </span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Data do Sorteio:</span>
                        <span class="font-semibold text-gray-900">
                            <?php
                            $drawDate = $raffle['federal_lottery_date'] ?? '';
                            echo ($drawDate && $drawDate != '0000-00-00') ? date('d/m/Y', strtotime($drawDate)) : 'A definir';
                            ?>
                        </span>
                    </div>

                    <div class="flex justify-between py-3 border-b">
                        <span class="text-gray-500">Status do Pagamento:</span>
                        <?php if ($winner['payment_status'] === 'paid'): ?>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                            <i class="fas fa-check mr-1"></i> Pago em <?= date('d/m/Y', strtotime($winner['paid_at'])) ?>
                        </span>
                        <?php else: ?>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                            <i class="fas fa-clock mr-1"></i> Pendente
                        </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-between py-3">
                        <span class="text-gray-500">Registrado em:</span>
                        <span class="text-gray-600">
                            <?= date('d/m/Y H:i', strtotime($winner['created_at'])) ?>
                        </span>
                    </div>
                </div>

                <!-- Aviso Legal -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        Este certificado comprova a autenticidade do premio. Em caso de duvidas,
                        entre em contato conosco atraves do email suporte@doarfazbem.com.br
                    </p>
                </div>
            </div>
        </div>

        <!-- Acoes -->
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
            <a href="<?= base_url('rifas/historico/' . $raffle['slug']) ?>" class="btn-secondary">
                <i class="fas fa-eye mr-2"></i>
                Ver Detalhes da Rifa
            </a>
            <a href="<?= base_url('rifas/historico') ?>" class="btn-primary">
                <i class="fas fa-history mr-2"></i>
                Historico Completo
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
