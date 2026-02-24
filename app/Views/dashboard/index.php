<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <h1 class="text-heading-1 mb-8">Meu Dashboard</h1>

        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="text-3xl mb-2">
                    <i class="fas fa-bullhorn text-emerald-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900"><?= $total_campaigns ?></div>
                <div class="text-gray-600 font-medium">Campanhas Criadas</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="text-3xl mb-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="text-2xl font-bold text-green-600"><?= $active_campaigns ?></div>
                <div class="text-gray-600 font-medium">Campanhas Ativas</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="text-3xl mb-2">
                    <i class="fas fa-dollar-sign text-emerald-600"></i>
                </div>
                <div class="text-2xl font-bold text-emerald-600">R$ <?= number_format($total_raised, 2, ',', '.') ?></div>
                <div class="text-gray-600 font-medium">Total Arrecadado</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="text-3xl mb-2">
                    <i class="fas fa-heart text-pink-600"></i>
                </div>
                <div class="text-2xl font-bold text-pink-600"><?= $total_donations ?></div>
                <div class="text-gray-600 font-medium">Doações Feitas</div>
            </div>
        </div>

        <!-- Minhas Campanhas Recentes -->
        <?php if (!empty($recent_campaigns)): ?>
            <div class="bg-white rounded-xl shadow-card p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Minhas Campanhas Recentes</h2>
                    <a href="<?= base_url('dashboard/my-campaigns') ?>" class="text-primary-600 hover:text-primary-700">Ver Todas →</a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($recent_campaigns as $campaign): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <h3 class="font-semibold"><?= esc($campaign['title']) ?></h3>
                                <div class="text-sm text-gray-600">
                                    <?= $campaign['donors_count'] ?> doadores • R$ <?= number_format($campaign['current_amount'], 2, ',', '.') ?>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm <?= $campaign['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                <?= ucfirst($campaign['status']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Conta de Recebimentos (Asaas) -->
        <?php if (!empty($asaas_account)): ?>
            <div class="bg-white rounded-xl shadow-card p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold flex items-center">
                        <i class="fas fa-university text-emerald-600 mr-2"></i>
                        Minha Conta de Recebimentos
                    </h2>
                    <span class="px-3 py-1 rounded-full text-sm <?= ($asaas_account['account_status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                        <i class="fas fa-check-circle mr-1"></i>
                        <?= ucfirst($asaas_account['account_status'] ?? 'Ativa') ?>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 mb-1">ID da Conta</div>
                        <div class="font-mono text-sm"><?= esc($asaas_account['asaas_account_id'] ?? $asaas_account['asaas_id'] ?? 'N/A') ?></div>
                    </div>
                    <?php if (!empty($asaas_account['asaas_wallet_id'] ?? $asaas_account['wallet_id'])): ?>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500 mb-1">ID da Carteira</div>
                        <div class="font-mono text-sm"><?= esc($asaas_account['asaas_wallet_id'] ?? $asaas_account['wallet_id']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                    <p class="text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Sobre sua conta:</strong> Esta e sua conta no gateway de pagamentos Asaas. Todas as doacoes recebidas em suas campanhas sao depositadas automaticamente nesta conta.
                    </p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <h4 class="font-semibold text-gray-700 mb-2">
                        <i class="fas fa-key mr-2"></i>Como acessar seu painel Asaas
                    </h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li><i class="fas fa-envelope mr-2 text-gray-400"></i><strong>Email:</strong> Use o mesmo email do seu cadastro no DoarFazBem</li>
                        <li><i class="fas fa-lock mr-2 text-gray-400"></i><strong>Senha:</strong> Verifique seu email - o Asaas enviou um link para criar sua senha</li>
                        <li><i class="fas fa-question-circle mr-2 text-gray-400"></i><strong>Esqueceu?</strong> Clique em "Esqueci minha senha" na tela de login do Asaas</li>
                    </ul>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="https://www.asaas.com/login" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Acessar Painel Asaas
                    </a>
                </div>
            </div>
        <?php elseif ($total_campaigns > 0): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 mb-8 rounded-r-lg">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h3 class="font-semibold text-yellow-800 mb-2">Configure sua conta de recebimentos</h3>
                        <p class="text-yellow-700 mb-4">
                            Para receber as doacoes das suas campanhas, voce precisa configurar sua conta no gateway de pagamentos.
                        </p>
                        <a href="<?= base_url('dashboard/complete-profile') ?>" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors shadow-md">
                            <i class="fas fa-user-cog mr-2"></i>
                            Configurar Agora
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Usuário sem conta Asaas e sem campanhas - informação sobre como criar -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 mb-8 rounded-r-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 text-2xl mr-4 mt-1"></i>
                    <div>
                        <h3 class="font-semibold text-blue-800 mb-2">Conta de Recebimentos</h3>
                        <p class="text-blue-700 mb-4">
                            Quando você criar sua primeira campanha, será criada automaticamente uma conta no gateway de pagamentos Asaas para receber suas doações.
                        </p>
                        <a href="<?= base_url('campaigns/create') ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Criar Minha Primeira Campanha
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Ações Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <a href="<?= base_url('campaigns/create') ?>" class="group relative bg-gradient-to-br from-teal-500 to-teal-700 text-white rounded-3xl p-10 hover:shadow-2xl hover:shadow-teal-500/50 hover:-translate-y-2 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-6">
                        <i class="fas fa-plus-circle drop-shadow-lg"></i>
                    </div>
                    <h3 class="font-black text-2xl mb-3">CRIAR NOVA CAMPANHA</h3>
                    <p class="text-lg">Comece uma nova arrecadação agora mesmo</p>
                </div>
            </a>

            <a href="<?= base_url('campaigns') ?>" class="group relative bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-3xl p-10 hover:shadow-2xl hover:shadow-blue-500/50 hover:-translate-y-2 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-6">
                        <i class="fas fa-search drop-shadow-lg"></i>
                    </div>
                    <h3 class="font-black text-2xl mb-3">EXPLORAR CAMPANHAS</h3>
                    <p class="text-lg">Encontre causas para apoiar</p>
                </div>
            </a>

            <a href="<?= base_url('dashboard/my-donations') ?>" class="group relative bg-gradient-to-br from-green-500 to-green-700 text-white rounded-3xl p-10 hover:shadow-2xl hover:shadow-green-500/50 hover:-translate-y-2 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative z-10">
                    <div class="text-6xl mb-6">
                        <i class="fas fa-hand-holding-heart drop-shadow-lg"></i>
                    </div>
                    <h3 class="font-black text-2xl mb-3">MINHAS DOAÇÕES</h3>
                    <p class="text-lg">Veja seu histórico completo</p>
                </div>
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
