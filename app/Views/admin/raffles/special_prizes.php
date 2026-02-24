<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container-custom max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= base_url('admin/raffles/edit/' . $raffle['id']) ?>" class="text-gray-600 hover:text-gray-900 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Premios Especiais</h1>
            <p class="text-gray-600"><?= esc($raffle['title']) ?></p>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-circle mr-1"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <!-- Adicionar Premio -->
        <div class="bg-white rounded-xl shadow-card p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-gift text-yellow-500 mr-2"></i>
                Adicionar Premios Instantaneos
            </h2>

            <form action="<?= base_url('admin/raffles/' . $raffle['id'] . '/prizes') ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Numeros (separados por virgula)</label>
                        <input type="text" name="numbers" placeholder="111111, 222222, 123456"
                               class="form-input" required>
                        <p class="text-xs text-gray-500 mt-1">Ex: 111111, 222222, 333333</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Premio</label>
                        <input type="text" name="prize_name" placeholder="PIX Instantaneo"
                               class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                        <input type="number" name="prize_amount" min="1" step="0.01" placeholder="50.00"
                               class="form-input" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-plus mr-1"></i> Adicionar Premios
                </button>
            </form>
        </div>

        <!-- Sugestoes de Numeros -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
            <h3 class="font-semibold text-yellow-800 mb-2">
                <i class="fas fa-lightbulb mr-1"></i>
                Sugestoes de Numeros Premiados
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm text-yellow-700">
                <div><strong>Triplos:</strong> 111111, 222222...</div>
                <div><strong>Sequencias:</strong> 123456, 654321</div>
                <div><strong>Especiais:</strong> 100000, 050000</div>
                <div><strong>Palindromos:</strong> 123321, 789987</div>
            </div>
        </div>

        <!-- Lista de Premios -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numero</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Premio</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Valor</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($specialPrizes as $prize): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono text-xl font-bold text-teal-600">
                                <?= esc($prize['number_pattern'] ?? $prize['number'] ?? '-') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-900"><?= esc($prize['prize_name']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-lg font-bold text-green-600">
                                R$ <?= number_format($prize['prize_amount'], 2, ',', '.') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if (!empty($prize['winner_user_id'])): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                PREMIADO
                            </span>
                            <?php else: ?>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                DISPONIVEL
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button onclick="editPrize(<?= $prize['id'] ?>, '<?= esc($prize['number_pattern'] ?? $prize['number'] ?? '') ?>', '<?= esc($prize['prize_name']) ?>', <?= $prize['prize_amount'] ?>)"
                                    class="text-blue-600 hover:text-blue-800 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="<?= base_url('admin/raffles/' . $raffle['id'] . '/prizes') ?>" method="post"
                                  class="inline" onsubmit="return confirm('Remover este premio?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="prize_id" value="<?= $prize['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($specialPrizes)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-gift text-4xl mb-2"></i>
                <p>Nenhum premio especial configurado</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Resumo Total de Premios -->
        <?php
        $totalSpecialPrizes = array_sum(array_column($specialPrizes, 'prize_amount'));
        $topBuyersPrizes = 1500 + 1000 + 500; // Top 3 compradores
        $totalExtraPrizes = $totalSpecialPrizes + $topBuyersPrizes;
        $maxExtraPrizes = $raffle['max_extra_prizes'] ?? 10000; // Limite configuravel ou padrao R$ 10k
        $percentageUsed = ($maxExtraPrizes > 0) ? ($totalExtraPrizes / $maxExtraPrizes) * 100 : 0;
        $isOverLimit = $totalExtraPrizes > $maxExtraPrizes;
        ?>
        <div class="bg-white rounded-xl shadow-card p-6 mt-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-calculator text-teal-600 mr-2"></i>
                Resumo Total de Premios Extras
            </h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="bg-yellow-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600">R$ <?= number_format($topBuyersPrizes, 0, ',', '.') ?></div>
                    <div class="text-xs text-gray-500">Top Compradores (fixo)</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">R$ <?= number_format($totalSpecialPrizes, 0, ',', '.') ?></div>
                    <div class="text-xs text-gray-500">Cotas Premiadas</div>
                </div>
                <div class="<?= $isOverLimit ? 'bg-red-50' : 'bg-emerald-50' ?> rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold <?= $isOverLimit ? 'text-red-600' : 'text-emerald-600' ?>">
                        R$ <?= number_format($totalExtraPrizes, 0, ',', '.') ?>
                    </div>
                    <div class="text-xs text-gray-500">Total Premios Extras</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-600">R$ <?= number_format($maxExtraPrizes, 0, ',', '.') ?></div>
                    <div class="text-xs text-gray-500">Limite Maximo</div>
                </div>
            </div>

            <!-- Barra de Progresso -->
            <div class="mb-2">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Utilizacao do limite</span>
                    <span class="<?= $isOverLimit ? 'text-red-600 font-bold' : 'text-gray-600' ?>">
                        <?= number_format(min($percentageUsed, 100), 1) ?>%
                        <?= $isOverLimit ? '(EXCEDIDO!)' : '' ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="<?= $isOverLimit ? 'bg-red-500' : ($percentageUsed > 80 ? 'bg-yellow-500' : 'bg-emerald-500') ?> rounded-full h-3 transition-all"
                         style="width: <?= min($percentageUsed, 100) ?>%"></div>
                </div>
            </div>

            <?php if ($isOverLimit): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mt-4">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Atencao!</strong> O total de premios extras (R$ <?= number_format($totalExtraPrizes, 0, ',', '.') ?>)
                excede o limite maximo configurado (R$ <?= number_format($maxExtraPrizes, 0, ',', '.') ?>).
                Remova alguns premios ou aumente o limite nas configuracoes da rifa.
            </div>
            <?php endif; ?>

            <div class="text-xs text-gray-500 mt-3">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Top Compradores:</strong> 1º R$ 1.500 + 2º R$ 1.000 + 3º R$ 500 = R$ 3.000 (fixo)
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edicao -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">Editar Premio Especial</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="<?= base_url('admin/raffles/' . $raffle['id'] . '/prizes') ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="prize_id" id="edit_prize_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero</label>
                    <input type="text" name="number" id="edit_number" class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Premio</label>
                    <input type="text" name="prize_name" id="edit_prize_name" class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$)</label>
                    <input type="number" name="prize_amount" id="edit_prize_amount" min="1" step="0.01" class="form-input" required>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeEditModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>

<script>
function editPrize(id, number, prizeName, prizeAmount) {
    document.getElementById('edit_prize_id').value = id;
    document.getElementById('edit_number').value = number;
    document.getElementById('edit_prize_name').value = prizeName;
    document.getElementById('edit_prize_amount').value = prizeAmount;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<?= $this->endSection() ?>
