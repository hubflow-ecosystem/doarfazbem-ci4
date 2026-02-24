<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gerenciar Recompensas</h1>
                    <p class="mt-2 text-gray-600"><?= esc($campaign['title']) ?></p>
                </div>
                <a href="<?= base_url('dashboard/my-campaigns') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i><?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <!-- Formulário para adicionar recompensa -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-plus-circle text-primary-500 mr-2"></i>Adicionar Nova Recompensa
                </h3>
            </div>
            <form action="<?= base_url('campaigns/' . $campaign['id'] . '/rewards/add') ?>" method="POST" class="p-6">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titulo da Recompensa *</label>
                        <input type="text" name="title" required
                               placeholder="Ex: Kit Apoiador"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor Minimo (R$) *</label>
                        <input type="number" name="min_amount" required min="1" step="0.01"
                               placeholder="50.00"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descricao *</label>
                    <textarea name="description" required rows="3"
                              placeholder="Descreva o que o apoiador recebera..."
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade Maxima</label>
                        <input type="number" name="max_quantity" min="1"
                               placeholder="Deixe vazio para ilimitado"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <p class="mt-1 text-xs text-gray-500">Deixe vazio para quantidade ilimitada</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Previsao de Entrega</label>
                        <input type="date" name="delivery_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                        <i class="fas fa-plus mr-2"></i>Adicionar Recompensa
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de Recompensas -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-gift text-primary-500 mr-2"></i>Recompensas Cadastradas
                </h3>
            </div>

            <?php if (!empty($rewards)): ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($rewards as $reward): ?>
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-xl font-bold text-primary-600">
                                            R$ <?= number_format($reward['min_amount'], 0, ',', '.') ?>+
                                        </span>
                                        <h4 class="text-lg font-semibold text-gray-900"><?= esc($reward['title']) ?></h4>
                                    </div>
                                    <p class="text-gray-600 mb-3"><?= esc($reward['description']) ?></p>
                                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                                        <?php if ($reward['max_quantity']): ?>
                                            <span>
                                                <i class="fas fa-box mr-1"></i>
                                                <?= $reward['remaining'] ?>/<?= $reward['max_quantity'] ?> disponiveis
                                            </span>
                                        <?php else: ?>
                                            <span><i class="fas fa-infinity mr-1"></i>Ilimitado</span>
                                        <?php endif; ?>
                                        <span><i class="fas fa-users mr-1"></i><?= $reward['claimed_quantity'] ?> apoiadores</span>
                                        <?php if ($reward['delivery_date']): ?>
                                            <span><i class="fas fa-truck mr-1"></i>Entrega: <?= date('m/Y', strtotime($reward['delivery_date'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <form action="<?= base_url('campaigns/rewards/' . $reward['id'] . '/delete') ?>" method="POST"
                                      onsubmit="return confirm('Tem certeza que deseja remover esta recompensa?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Remover">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <i class="fas fa-gift text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">Nenhuma recompensa cadastrada</p>
                    <p class="text-sm text-gray-400 mt-2">Adicione recompensas para incentivar maiores doacoes!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Links para outras configuracoes -->
        <div class="mt-6 flex gap-4">
            <a href="<?= base_url('campaigns/' . $campaign['id'] . '/media') ?>"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-images mr-2"></i>Gerenciar Midia
            </a>
            <a href="<?= base_url('campaigns/edit/' . $campaign['id']) ?>"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-edit mr-2"></i>Editar Campanha
            </a>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
