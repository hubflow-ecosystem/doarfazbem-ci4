<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Gerenciar Rifas</h1>
                <p class="text-gray-600">Administre as rifas "Numeros da Sorte"</p>
            </div>
            <a href="<?= base_url('admin/raffles/create') ?>"
               class="btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Nova Rifa
            </a>
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

        <!-- Lista de Rifas -->
        <div class="space-y-6">
            <?php if (empty($raffles)): ?>
            <div class="bg-white rounded-xl shadow-card p-12 text-center">
                <i class="fas fa-ticket-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Nenhuma rifa cadastrada</h3>
                <p class="text-gray-500 mb-6">Crie sua primeira rifa para comecar a vender numeros da sorte.</p>
                <a href="<?= base_url('admin/raffles/create') ?>" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i> Criar Primeira Rifa
                </a>
            </div>
            <?php else: ?>
            <?php foreach ($raffles as $raffle): ?>
            <div class="bg-white rounded-xl shadow-card overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <!-- Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-xl font-bold text-gray-900">
                                    <?= esc($raffle['title']) ?>
                                </h2>
                                <?php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'active' => 'bg-green-100 text-green-800',
                                    'paused' => 'bg-yellow-100 text-yellow-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                ];
                                $statusNames = [
                                    'draft' => 'Rascunho',
                                    'active' => 'Ativa',
                                    'paused' => 'Pausada',
                                    'completed' => 'Finalizada',
                                ];
                                ?>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $statusColors[$raffle['status']] ?>">
                                    <?= $statusNames[$raffle['status']] ?>
                                </span>
                            </div>

                            <p class="text-gray-600 text-sm mb-4">
                                <?= esc($raffle['description']) ?>
                            </p>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <div class="bg-gray-50 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-teal-600">
                                        <?= number_format($raffle['stats']['numbers_sold'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                    <div class="text-xs text-gray-500">Vendidos</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-green-600">
                                        R$ <?= number_format($raffle['stats']['total_revenue'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                    <div class="text-xs text-gray-500">Receita</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-yellow-600">
                                        R$ <?= number_format($raffle['stats']['main_prize'] ?? 0, 0, ',', '.') ?>
                                    </div>
                                    <div class="text-xs text-gray-500">Premio</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-blue-600">
                                        <?= number_format($raffle['stats']['percentage_sold'] ?? 0, 1) ?>%
                                    </div>
                                    <div class="text-xs text-gray-500">Progresso</div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3 text-center">
                                    <div class="text-2xl font-bold text-gray-600">
                                        <?= number_format($raffle['total_numbers'], 0, ',', '.') ?>
                                    </div>
                                    <div class="text-xs text-gray-500">Total</div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2 ml-6">
                            <a href="<?= base_url('admin/raffles/edit/' . $raffle['id']) ?>"
                               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                            <a href="<?= base_url('admin/raffles/' . $raffle['id'] . '/packages') ?>"
                               class="px-4 py-2 bg-emerald-100 text-teal-700 rounded-lg hover:bg-emerald-200 transition text-sm">
                                <i class="fas fa-box mr-1"></i> Pacotes
                            </a>
                            <a href="<?= base_url('admin/raffles/' . $raffle['id'] . '/prizes') ?>"
                               class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition text-sm">
                                <i class="fas fa-gift mr-1"></i> Premios
                            </a>
                            <a href="<?= base_url('admin/raffles/' . $raffle['id'] . '/purchases') ?>"
                               class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition text-sm">
                                <i class="fas fa-shopping-cart mr-1"></i> Compras
                            </a>
                        </div>
                    </div>

                    <!-- Status Actions -->
                    <div class="flex gap-2 mt-6 pt-6 border-t border-gray-200">
                        <?php if ($raffle['status'] === 'draft' || $raffle['status'] === 'paused'): ?>
                        <form action="<?= base_url('admin/raffles/activate/' . $raffle['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                                <i class="fas fa-play mr-1"></i> Ativar
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($raffle['status'] === 'active'): ?>
                        <form action="<?= base_url('admin/raffles/pause/' . $raffle['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-sm">
                                <i class="fas fa-pause mr-1"></i> Pausar
                            </button>
                        </form>
                        <?php endif; ?>

                        <?php if ($raffle['status'] !== 'completed'): ?>
                        <form action="<?= base_url('admin/raffles/complete/' . $raffle['id']) ?>" method="post"
                              onsubmit="return confirm('Tem certeza que deseja finalizar esta rifa?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                <i class="fas fa-flag-checkered mr-1"></i> Finalizar
                            </button>
                        </form>
                        <?php endif; ?>

                        <a href="<?= base_url('rifas/' . $raffle['slug']) ?>" target="_blank"
                           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm ml-auto">
                            <i class="fas fa-external-link-alt mr-1"></i> Ver Pagina
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Link Voltar -->
        <div class="mt-8">
            <a href="<?= base_url('admin/dashboard') ?>" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
