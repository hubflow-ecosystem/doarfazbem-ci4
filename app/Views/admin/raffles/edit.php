<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container-custom max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= base_url('admin/raffles') ?>" class="text-gray-600 hover:text-gray-900 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Editar Rifa</h1>
                    <p class="text-gray-600"><?= esc($raffle['title']) ?></p>
                </div>
                <a href="<?= base_url('rifas/' . $raffle['slug']) ?>" target="_blank"
                   class="btn-outline">
                    <i class="fas fa-external-link-alt mr-2"></i> Ver Pagina
                </a>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-teal-600">
                    <?= number_format($stats['numbers_sold'] ?? 0, 0, ',', '.') ?>
                </div>
                <div class="text-sm text-gray-500">Numeros Vendidos</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-green-600">
                    R$ <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?>
                </div>
                <div class="text-sm text-gray-500">Receita Total</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-yellow-600">
                    R$ <?= number_format($stats['main_prize'] ?? 0, 0, ',', '.') ?>
                </div>
                <div class="text-sm text-gray-500">Premio Acumulado</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-4 text-center">
                <div class="text-3xl font-bold text-blue-600">
                    <?= number_format($stats['percentage_sold'] ?? 0, 1) ?>%
                </div>
                <div class="text-sm text-gray-500">Progresso</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <a href="<?= base_url('admin/raffles/' . $raffle['id'] . '/packages') ?>"
               class="bg-white rounded-xl shadow-card p-6 text-center hover:shadow-lg transition">
                <i class="fas fa-box text-teal-500 text-3xl mb-2"></i>
                <h3 class="font-semibold text-gray-900">Pacotes</h3>
                <p class="text-sm text-gray-500"><?= count($packages) ?> configurados</p>
            </a>
            <a href="<?= base_url('admin/raffles/' . $raffle['id'] . '/prizes') ?>"
               class="bg-white rounded-xl shadow-card p-6 text-center hover:shadow-lg transition">
                <i class="fas fa-gift text-yellow-500 text-3xl mb-2"></i>
                <h3 class="font-semibold text-gray-900">Premios Especiais</h3>
                <p class="text-sm text-gray-500"><?= count($specialPrizes) ?> numeros premiados</p>
            </a>
            <a href="<?= base_url('admin/raffles/' . $raffle['id'] . '/purchases') ?>"
               class="bg-white rounded-xl shadow-card p-6 text-center hover:shadow-lg transition">
                <i class="fas fa-shopping-cart text-green-500 text-3xl mb-2"></i>
                <h3 class="font-semibold text-gray-900">Compras</h3>
                <p class="text-sm text-gray-500">Ver todas as vendas</p>
            </a>
        </div>

        <!-- Formulario -->
        <form action="<?= base_url('admin/raffles/edit/' . $raffle['id']) ?>" method="post" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Informacoes Basicas -->
            <div class="bg-white rounded-xl shadow-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-teal-500 mr-2"></i>
                    Informacoes Basicas
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titulo da Rifa *</label>
                        <input type="text" name="title" value="<?= esc($raffle['title']) ?>"
                               class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descricao</label>
                        <textarea name="description" rows="3" class="form-input"><?= esc($raffle['description']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total de Numeros</label>
                            <input type="text" value="<?= number_format($raffle['total_numbers'], 0, ',', '.') ?>"
                                   class="form-input bg-gray-100" disabled>
                            <p class="text-xs text-gray-500 mt-1">Nao pode ser alterado apos criacao</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preco por Cota (R$) *</label>
                            <input type="number" name="number_price" value="<?= $raffle['number_price'] ?>"
                                   min="0.10" step="0.01" class="form-input" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data da Loteria Federal</label>
                        <input type="date" name="federal_lottery_date"
                               value="<?= !empty($raffle['federal_lottery_date']) ? date('Y-m-d', strtotime($raffle['federal_lottery_date'])) : '' ?>"
                               class="form-input">
                        <p class="text-xs text-gray-500 mt-1">Defina quando todas as cotas forem vendidas ou quando decidir realizar o sorteio</p>
                    </div>
                </div>
            </div>

            <!-- Distribuicao de Valores -->
            <div class="bg-white rounded-xl shadow-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-pie text-green-500 mr-2"></i>
                    Distribuicao de Valores (deve somar 100%)
                </h2>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-trophy text-yellow-500 mr-1"></i>
                            Premio Principal (%)
                        </label>
                        <input type="number" name="main_prize_percentage" value="<?= $raffle['main_prize_percentage'] ?>"
                               min="0" max="100" step="1" class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-hand-holding-heart text-red-500 mr-1"></i>
                            Campanhas (%)
                        </label>
                        <input type="number" name="campaign_percentage" value="<?= $raffle['campaign_percentage'] ?>"
                               min="0" max="100" step="1" class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-building text-blue-500 mr-1"></i>
                            Plataforma (%)
                        </label>
                        <input type="number" name="platform_percentage" value="<?= $raffle['platform_percentage'] ?>"
                               min="0" max="100" step="1" class="form-input" required>
                    </div>
                </div>

                <!-- Preview da distribuicao atual -->
                <div class="bg-gradient-to-br from-blue-50 to-teal-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <div class="text-sm">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b-2 border-blue-300">
                            <strong class="text-blue-900"><i class="fas fa-calculator mr-2"></i> Distribuição Completa da Receita</strong>
                            <span class="text-lg font-bold text-blue-900">R$ <?= number_format($stats['total_revenue'] ?? 0, 2, ',', '.') ?></span>
                        </div>

                        <div class="space-y-2">
                            <!-- Receita e Taxas -->
                            <div class="bg-white/60 rounded-lg p-3">
                                <div class="flex justify-between text-gray-700">
                                    <span><i class="fas fa-dollar-sign text-green-600 mr-1"></i> Receita Total:</span>
                                    <span class="font-semibold">R$ <?= number_format($stats['total_revenue'] ?? 0, 2, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between text-red-600 text-xs pl-4 mt-1">
                                    <span><i class="fas fa-minus-circle mr-1"></i> Taxa Gateway (1%):</span>
                                    <span>- R$ <?= number_format($stats['gateway_fee'] ?? 0, 2, ',', '.') ?></span>
                                </div>
                                <div class="flex justify-between font-bold text-green-700 pl-4 mt-1 pt-1 border-t border-gray-300">
                                    <span><i class="fas fa-equals mr-1"></i> Receita Líquida:</span>
                                    <span>R$ <?= number_format($stats['revenue_liquid'] ?? 0, 2, ',', '.') ?></span>
                                </div>
                            </div>

                            <!-- Área de Prêmios -->
                            <div class="bg-yellow-50 rounded-lg p-3">
                                <div class="font-bold text-yellow-800 mb-2">
                                    <i class="fas fa-trophy mr-1"></i> ÁREA DE PRÊMIOS (<?= $raffle['main_prize_percentage'] ?>%):
                                    <span class="float-right">R$ <?= number_format($stats['main_prize_pool'] ?? 0, 2, ',', '.') ?></span>
                                </div>
                                <div class="pl-3 space-y-1 text-sm">
                                    <div class="flex justify-between text-gray-700">
                                        <span><i class="fas fa-crown text-yellow-600 mr-1"></i> Prêmio do Ganhador (50% da área):</span>
                                        <span class="font-semibold">R$ <?= number_format($stats['winner_prize'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span><i class="fas fa-medal text-teal-600 mr-1"></i> Top 3 Compradores (fixo):</span>
                                        <span class="font-semibold">R$ <?= number_format($stats['top_buyers_prizes'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-700">
                                        <span><i class="fas fa-gift text-purple-600 mr-1"></i> Cotas Premiadas:</span>
                                        <span class="font-semibold">R$ <?= number_format($stats['special_prizes'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between font-bold text-yellow-900 pt-1 mt-1 border-t border-yellow-300">
                                        <span><i class="fas fa-equals mr-1"></i> Total Comprometido:</span>
                                        <span>R$ <?= number_format($stats['total_prizes_committed'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between text-blue-700 bg-blue-50 p-1 rounded">
                                        <span><i class="fas fa-undo mr-1"></i> Sobra (retorna p/ campanhas):</span>
                                        <span class="font-semibold">R$ <?= number_format($stats['prize_pool_remainder'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Distribuição -->
                            <div class="bg-white/60 rounded-lg p-3">
                                <div class="font-bold text-blue-800 mb-2">
                                    <i class="fas fa-chart-pie mr-1"></i> DISTRIBUIÇÃO DA RECEITA LÍQUIDA:
                                </div>
                                <div class="pl-3 space-y-1 text-sm">
                                    <div class="flex justify-between text-gray-700">
                                        <span><i class="fas fa-hand-holding-heart text-red-500 mr-1"></i> Campanhas Direto (<?= $raffle['campaign_percentage'] ?>%):</span>
                                        <span class="font-semibold">R$ <?= number_format($stats['campaigns_direct'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 text-xs pl-4">
                                        <span><i class="fas fa-plus-circle mr-1"></i> + Sobra dos Prêmios:</span>
                                        <span>R$ <?= number_format($stats['prize_pool_remainder'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between font-bold text-red-700 pl-4 pt-1 border-t border-gray-300">
                                        <span><i class="fas fa-equals mr-1"></i> Total Campanhas:</span>
                                        <span>R$ <?= number_format($stats['for_campaigns'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between text-gray-700 mt-2">
                                        <span><i class="fas fa-building text-blue-600 mr-1"></i> Para Plataforma (<?= $raffle['platform_percentage'] ?>%):</span>
                                        <span class="font-semibold">R$ <?= number_format($stats['for_platform'] ?? 0, 2, ',', '.') ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Nota Informativa -->
                            <div class="text-xs text-gray-600 bg-gray-100 rounded p-2 mt-2">
                                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                <strong>Como funciona:</strong>
                                A taxa do gateway (1%) é deduzida primeiro. Depois, da receita líquida:
                                <?= $raffle['main_prize_percentage'] ?>% vai para área de prêmios (50% dela para o ganhador + extras),
                                <?= $raffle['campaign_percentage'] ?>% direto + sobra dos prêmios para campanhas,
                                e <?= $raffle['platform_percentage'] ?>% para plataforma.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botoes -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 btn-primary py-4 text-lg">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alteracoes
                </button>
                <a href="<?= base_url('admin/raffles') ?>" class="btn-outline py-4">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
