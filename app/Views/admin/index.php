<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <h1 class="text-heading-1 mb-8">🔐 Painel Administrativo</h1>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-card p-6">
                <div class="text-3xl mb-2">👥</div>
                <div class="text-2xl font-bold"><?= $total_users ?></div>
                <div class="text-gray-600">Usuários</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-6">
                <div class="text-3xl mb-2">📢</div>
                <div class="text-2xl font-bold"><?= $total_campaigns ?></div>
                <div class="text-gray-600">Campanhas Total</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-6">
                <div class="text-3xl mb-2">✅</div>
                <div class="text-2xl font-bold text-green-600"><?= $active_campaigns ?></div>
                <div class="text-gray-600">Campanhas Ativas</div>
            </div>
            <div class="bg-white rounded-xl shadow-card p-6">
                <div class="text-3xl mb-2">⏳</div>
                <div class="text-2xl font-bold text-yellow-600"><?= $pending_campaigns ?></div>
                <div class="text-gray-600">Aguardando Aprovação</div>
            </div>
        </div>

        <!-- Doações -->
        <?php if (!empty($donation_stats)): ?>
            <div class="bg-white rounded-xl shadow-card p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Estatísticas de Doações</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-gray-600 text-sm">Total de Doações</div>
                        <div class="text-2xl font-bold"><?= $donation_stats['total_donations'] ?? 0 ?></div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">Valor Total Arrecadado</div>
                        <div class="text-2xl font-bold text-primary-600">R$ <?= number_format($donation_stats['total_amount'] ?? 0, 2, ',', '.') ?></div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">Doação Média</div>
                        <div class="text-2xl font-bold text-secondary-600">R$ <?= number_format($donation_stats['average_donation'] ?? 0, 2, ',', '.') ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Ações Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="<?= base_url('admin/campaigns?status=pending') ?>" class="bg-yellow-500 text-white rounded-xl p-6 hover:bg-yellow-600 transition-colors">
                <div class="text-3xl mb-2">⏳</div>
                <h3 class="font-semibold text-lg mb-2">Campanhas Pendentes</h3>
                <p class="text-yellow-100 text-sm"><?= $pending_campaigns ?> aguardando aprovação</p>
            </a>
            <a href="<?= base_url('admin/campaigns?status=active') ?>" class="bg-green-500 text-white rounded-xl p-6 hover:bg-green-600 transition-colors">
                <div class="text-3xl mb-2">✅</div>
                <h3 class="font-semibold text-lg mb-2">Campanhas Ativas</h3>
                <p class="text-green-100 text-sm">Gerenciar campanhas publicadas</p>
            </a>
            <a href="<?= base_url('admin/users') ?>" class="bg-blue-500 text-white rounded-xl p-6 hover:bg-blue-600 transition-colors">
                <div class="text-3xl mb-2">👥</div>
                <h3 class="font-semibold text-lg mb-2">Usuários</h3>
                <p class="text-blue-100 text-sm">Gerenciar usuários da plataforma</p>
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
