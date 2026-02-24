<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Menu de Navegação Admin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8">
            <nav class="flex flex-wrap gap-2">
                <a href="<?= base_url('admin/dashboard') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="<?= base_url('admin/campaigns?status=pending') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-bullhorn mr-2"></i>Campanhas
                </a>
                <a href="<?= base_url('admin/donations') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-hand-holding-heart mr-2"></i>Doacoes
                </a>
                <a href="<?= base_url('admin/reports') ?>"
                   class="px-4 py-2 rounded-lg bg-primary-500 text-white font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Relatorios
                </a>
                <a href="<?= base_url('admin/settings') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-cog mr-2"></i>Configuracoes
                </a>
            </nav>
        </div>

        <h1 class="text-heading-1 mb-8"><?= esc($title) ?></h1>

        <!-- Estatísticas Resumidas -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600">R$ <?= number_format($stats['total_amount'], 2, ',', '.') ?></p>
                <p class="text-xs text-gray-500">Total Arrecadado</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-blue-600"><?= number_format($stats['total_donations']) ?></p>
                <p class="text-xs text-gray-500">Doacoes</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-purple-600"><?= number_format($stats['total_campaigns']) ?></p>
                <p class="text-xs text-gray-500">Campanhas</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-indigo-600"><?= number_format($stats['active_campaigns']) ?></p>
                <p class="text-xs text-gray-500">Ativas</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-teal-600"><?= number_format($stats['total_users']) ?></p>
                <p class="text-xs text-gray-500">Usuarios</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-orange-600"><?= number_format($stats['total_withdrawals']) ?></p>
                <p class="text-xs text-gray-500">Saques</p>
            </div>
        </div>

        <!-- Cards de Exportação -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Exportar Doações -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-hand-holding-heart text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Doacoes</h3>
                        <p class="text-sm text-gray-500">Exportar lista de doacoes</p>
                    </div>
                </div>

                <form action="<?= base_url('admin/export/donations') ?>" method="GET" class="space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Data Inicio</label>
                            <input type="date" name="start_date" value="<?= date('Y-m-01') ?>"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Data Fim</label>
                            <input type="date" name="end_date" value="<?= date('Y-m-d') ?>"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                            <option value="all">Todos</option>
                            <option value="received">Recebidas</option>
                            <option value="pending">Pendentes</option>
                            <option value="refunded">Reembolsadas</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full btn-primary text-sm">
                        <i class="fas fa-download mr-2"></i>Exportar CSV
                    </button>
                </form>
            </div>

            <!-- Exportar Campanhas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-bullhorn text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Campanhas</h3>
                        <p class="text-sm text-gray-500">Exportar lista de campanhas</p>
                    </div>
                </div>

                <form action="<?= base_url('admin/export/campaigns') ?>" method="GET" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                            <option value="all">Todos</option>
                            <option value="active">Ativas</option>
                            <option value="pending">Pendentes</option>
                            <option value="completed">Finalizadas</option>
                            <option value="rejected">Rejeitadas</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full btn-primary text-sm">
                        <i class="fas fa-download mr-2"></i>Exportar CSV
                    </button>
                </form>
            </div>

            <!-- Exportar Usuários -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Usuarios</h3>
                        <p class="text-sm text-gray-500">Exportar lista de usuarios</p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Exportar todos os usuarios cadastrados com estatisticas de campanhas e doacoes.
                </p>

                <a href="<?= base_url('admin/export/users') ?>" class="block w-full btn-primary text-sm text-center">
                    <i class="fas fa-download mr-2"></i>Exportar CSV
                </a>
            </div>

            <!-- Exportar Saques -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-money-bill-wave text-orange-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Saques</h3>
                        <p class="text-sm text-gray-500">Exportar solicitacoes de saque</p>
                    </div>
                </div>

                <form action="<?= base_url('admin/export/withdrawals') ?>" method="GET" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                            <option value="all">Todos</option>
                            <option value="pending">Pendentes</option>
                            <option value="processing">Processando</option>
                            <option value="completed">Concluidos</option>
                            <option value="rejected">Rejeitados</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full btn-primary text-sm">
                        <i class="fas fa-download mr-2"></i>Exportar CSV
                    </button>
                </form>
            </div>

            <!-- Exportar Rifas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-ticket-alt text-pink-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Rifas</h3>
                        <p class="text-sm text-gray-500">Exportar dados das rifas</p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Exportar todas as rifas com dados de vendas e premios.
                </p>

                <a href="<?= base_url('admin/export/raffles') ?>" class="block w-full btn-primary text-sm text-center">
                    <i class="fas fa-download mr-2"></i>Exportar CSV
                </a>
            </div>

            <!-- Logs de Auditoria -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-history text-gray-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Logs de Auditoria</h3>
                        <p class="text-sm text-gray-500">Visualizar acoes do sistema</p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Acesse o historico de acoes realizadas pelos administradores.
                </p>

                <a href="<?= base_url('admin/audit-logs') ?>" class="block w-full btn-secondary text-sm text-center">
                    <i class="fas fa-eye mr-2"></i>Ver Logs
                </a>
            </div>

        </div>

        <!-- Dicas -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Dicas para exportacao</h3>
                    <ul class="text-sm text-blue-700 mt-2 space-y-1">
                        <li>Os arquivos CSV usam ponto-e-virgula (;) como separador para compatibilidade com Excel brasileiro.</li>
                        <li>Os arquivos incluem BOM UTF-8 para garantir acentuacao correta.</li>
                        <li>Para abrir no Excel: clique com botao direito > Abrir com > Excel (ou importe como dados).</li>
                        <li>Todas as exportacoes sao registradas nos logs de auditoria.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
