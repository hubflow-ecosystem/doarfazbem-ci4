<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container-custom max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= base_url('admin/raffles/edit/' . $raffle['id']) ?>" class="text-gray-600 hover:text-gray-900 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Pacotes de Cotas</h1>
            <p class="text-gray-600"><?= esc($raffle['title']) ?></p>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('success') ?>
        </div>
        <?php endif; ?>

        <!-- Adicionar Pacote -->
        <div class="bg-white rounded-xl shadow-card p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">
                <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                Adicionar Novo Pacote
            </h2>

            <form action="<?= base_url('admin/raffles/' . $raffle['id'] . '/packages') ?>" method="post" class="grid grid-cols-5 gap-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input type="text" name="name" placeholder="Ex: Mega" class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                    <input type="number" name="quantity" min="1" placeholder="100" class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desconto (%)</label>
                    <input type="number" name="discount_percentage" min="0" max="99" step="0.1" placeholder="15" class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Popular?</label>
                    <select name="is_popular" class="form-input">
                        <option value="0">Nao</option>
                        <option value="1">Sim</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">
                        <i class="fas fa-plus mr-1"></i> Adicionar
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de Pacotes -->
        <div class="bg-white rounded-xl shadow-card overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qtd</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Desconto</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Preco Final</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Popular</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($packages as $pkg): ?>
                    <?php
                    $basePrice = (float) $raffle['number_price'];
                    $discount = (float) $pkg['discount_percentage'];
                    $pricePerUnit = $basePrice * (1 - $discount / 100);
                    $totalPrice = $pricePerUnit * $pkg['quantity'];
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900"><?= esc($pkg['name'] ?? 'Pacote') ?></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-gray-900 font-bold"><?= number_format($pkg['quantity'], 0, ',', '.') ?></span>
                            <span class="text-xs text-gray-500"> cotas</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($discount > 0): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                -<?= number_format($discount, 0) ?>%
                            </span>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="text-lg font-bold text-green-600">
                                R$ <?= number_format($totalPrice, 2, ',', '.') ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                R$ <?= number_format($pricePerUnit, 2, ',', '.') ?>/cota
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($pkg['is_popular']): ?>
                            <span class="px-2 py-1 bg-emerald-100 text-teal-800 rounded-full text-xs font-semibold">
                                POPULAR
                            </span>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button onclick="editPackage(<?= $pkg['id'] ?>, '<?= esc($pkg['name'] ?? '') ?>', <?= $pkg['quantity'] ?>, <?= $pkg['discount_percentage'] ?>, <?= $pkg['is_popular'] ?>)"
                                    class="text-blue-600 hover:text-blue-800 inline-block">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="<?= base_url('admin/raffles/' . $raffle['id'] . '/packages') ?>" method="post"
                                  class="inline" onsubmit="return confirm('Remover este pacote?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($packages)): ?>
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-box-open text-4xl mb-2"></i>
                <p>Nenhum pacote configurado</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Dica -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <p class="text-sm text-blue-800">
                <i class="fas fa-lightbulb mr-1"></i>
                <strong>Dica:</strong> Pacotes com mais cotas devem ter maiores descontos para incentivar compras maiores.
                Marque um pacote como "Popular" para destaca-lo na pagina de venda.
            </p>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-xl shadow-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Editar Pacote</h3>

        <form action="<?= base_url('admin/raffles/' . $raffle['id'] . '/packages') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="package_id" id="edit_package_id">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                    <input type="number" name="quantity" id="edit_quantity" min="1" class="form-input" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desconto (%)</label>
                    <input type="number" name="discount_percentage" id="edit_discount" min="0" max="99" step="0.1" class="form-input" required>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_popular" id="edit_popular" class="rounded">
                    <label for="edit_popular" class="ml-2 text-sm text-gray-700">Marcar como Popular</label>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn-primary flex-1">Salvar</button>
                <button type="button" onclick="closeEditModal()" class="btn-secondary flex-1">Cancelar</button>
            </div>
        </form>
    </div>
    </div>
</div>

<script>
function editPackage(id, name, quantity, discount, isPopular) {
    document.getElementById('edit_package_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_discount').value = discount;
    document.getElementById('edit_popular').checked = isPopular == 1;
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
