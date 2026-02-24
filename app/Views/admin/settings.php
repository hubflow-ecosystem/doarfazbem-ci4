<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Configurações da Plataforma</h1>
                    <p class="mt-2 text-gray-600">Gerencie todas as configurações do DoarFazBem</p>
                </div>
                <a href="<?= base_url('admin/dashboard') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
                    <p class="text-green-700"><?= session()->getFlashdata('success') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
                    <p class="text-red-700"><?= session()->getFlashdata('error') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div x-data="{ activeTab: 'general' }" class="space-y-6">

            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <nav class="flex flex-wrap -mb-px">
                    <button @click="activeTab = 'general'" :class="activeTab === 'general' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-cog mr-2"></i> Geral
                    </button>
                    <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-credit-card mr-2"></i> Pagamentos
                    </button>
                    <button @click="activeTab = 'campaigns'" :class="activeTab === 'campaigns' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-bullhorn mr-2"></i> Campanhas
                    </button>
                    <button @click="activeTab = 'raffles'" :class="activeTab === 'raffles' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-ticket-alt mr-2"></i> Rifas
                    </button>
                    <button @click="activeTab = 'notifications'" :class="activeTab === 'notifications' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-bell mr-2"></i> Notificações
                    </button>
                    <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-shield-alt mr-2"></i> Segurança
                    </button>
                    <button @click="activeTab = 'appearance'" :class="activeTab === 'appearance' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-palette mr-2"></i> Aparência
                    </button>
                    <button @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 text-sm font-medium border-b-2 flex items-center">
                        <i class="fas fa-search mr-2"></i> SEO
                    </button>
                </nav>
            </div>

            <form action="<?= base_url('admin/settings/save') ?>" method="POST">
                <?= csrf_field() ?>

                <!-- Tab: Geral -->
                <div x-show="activeTab === 'general'" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Configurações Gerais</h3>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome da Plataforma</label>
                                <input type="text" name="platform_name" value="<?= esc($settings['platform_name'] ?? 'DoarFazBem') ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email de Contato</label>
                                <input type="email" name="contact_email" value="<?= esc($settings['contact_email'] ?? '') ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Telefone de Contato</label>
                                <input type="text" name="contact_phone" value="<?= esc($settings['contact_phone'] ?? '') ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div class="flex items-center pt-6">
                                <input type="checkbox" name="maintenance_mode" id="maintenance_mode" value="1"
                                       <?= ($settings['maintenance_mode'] ?? false) ? 'checked' : '' ?>
                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                <label for="maintenance_mode" class="ml-2 block text-sm text-gray-700">
                                    <span class="text-red-600 font-medium">Modo Manutenção</span> - Site fica offline para usuários
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Pagamentos -->
                <div x-show="activeTab === 'payments'" class="space-y-6">

                    <!-- Gateway Asaas - Doações -->
                    <div class="bg-white shadow rounded-lg border-l-4 border-blue-500">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                        <img src="https://www.asaas.com/favicon.ico" class="w-5 h-5 mr-2" alt="Asaas">
                                        Gateway Asaas
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">Processa pagamentos de <strong>Doações</strong> (PIX, Cartão, Boleto)</p>
                                </div>
                                <?php
                                $asaasConfigured = !empty(env('ASAAS_API_KEY'));
                                ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $asaasConfigured ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <i class="fas <?= $asaasConfigured ? 'fa-check-circle' : 'fa-times-circle' ?> mr-1"></i>
                                    <?= $asaasConfigured ? 'Configurado' : 'Não Configurado' ?>
                                </span>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ambiente</label>
                                    <select name="asaas_environment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="sandbox" <?= ($settings['asaas_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>
                                            🧪 Sandbox (Testes)
                                        </option>
                                        <option value="production" <?= ($settings['asaas_environment'] ?? '') === 'production' ? 'selected' : '' ?>>
                                            🚀 Production (Produção)
                                        </option>
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <div class="text-sm text-gray-600">
                                        <p><strong>Usado para:</strong> Campanhas de Doação</p>
                                        <p class="mt-1"><strong>Métodos:</strong> PIX, Cartão de Crédito, Boleto</p>
                                    </div>
                                </div>
                            </div>
                            <?php if (($settings['asaas_environment'] ?? 'sandbox') === 'sandbox'): ?>
                                <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-sm text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        <strong>Modo Sandbox:</strong> Pagamentos de doações não são reais. Mude para "Production" quando estiver pronto.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-sm text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <strong>Modo Produção:</strong> Doações estão sendo processadas com dinheiro real.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Gateway MercadoPago - Rifas -->
                    <div class="bg-white shadow rounded-lg border-l-4 border-cyan-500">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                        <img src="https://http2.mlstatic.com/frontend-assets/mp-web-navigation/ui-navigation/6.6.92/mercadopago/favicon.svg" class="w-5 h-5 mr-2" alt="MercadoPago">
                                        Gateway MercadoPago
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">Processa pagamentos de <strong>Rifas</strong> (apenas PIX)</p>
                                </div>
                                <?php
                                $mpSandboxConfigured = !empty(env('mercadopago.sandbox.access_token'));
                                $mpProductionConfigured = !empty(env('mercadopago.production.access_token'));
                                $mpConfigured = $mpSandboxConfigured || $mpProductionConfigured;
                                ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $mpConfigured ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <i class="fas <?= $mpConfigured ? 'fa-check-circle' : 'fa-times-circle' ?> mr-1"></i>
                                    <?= $mpConfigured ? 'Configurado' : 'Não Configurado' ?>
                                </span>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ambiente</label>
                                    <select name="mercadopago_environment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="sandbox" <?= ($settings['mercadopago_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>
                                            🧪 Sandbox (Testes)
                                        </option>
                                        <option value="production" <?= ($settings['mercadopago_environment'] ?? '') === 'production' ? 'selected' : '' ?>>
                                            🚀 Production (Produção)
                                        </option>
                                    </select>
                                </div>
                                <div class="flex items-center">
                                    <div class="text-sm text-gray-600">
                                        <p><strong>Usado para:</strong> Sistema de Rifas</p>
                                        <p class="mt-1"><strong>Métodos:</strong> <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-qrcode mr-1"></i> Apenas PIX</span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Status das Credenciais -->
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div class="p-3 rounded-lg <?= $mpSandboxConfigured ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' ?>">
                                    <p class="text-sm font-medium <?= $mpSandboxConfigured ? 'text-green-800' : 'text-gray-600' ?>">
                                        <i class="fas <?= $mpSandboxConfigured ? 'fa-check' : 'fa-times' ?> mr-1"></i>
                                        Credenciais Sandbox
                                    </p>
                                    <p class="text-xs <?= $mpSandboxConfigured ? 'text-green-600' : 'text-gray-500' ?> mt-1">
                                        <?= $mpSandboxConfigured ? 'Configuradas no .env' : 'Não configuradas' ?>
                                    </p>
                                </div>
                                <div class="p-3 rounded-lg <?= $mpProductionConfigured ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' ?>">
                                    <p class="text-sm font-medium <?= $mpProductionConfigured ? 'text-green-800' : 'text-gray-600' ?>">
                                        <i class="fas <?= $mpProductionConfigured ? 'fa-check' : 'fa-times' ?> mr-1"></i>
                                        Credenciais Produção
                                    </p>
                                    <p class="text-xs <?= $mpProductionConfigured ? 'text-green-600' : 'text-gray-500' ?> mt-1">
                                        <?= $mpProductionConfigured ? 'Configuradas no .env' : 'Não configuradas' ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (($settings['mercadopago_environment'] ?? 'sandbox') === 'sandbox'): ?>
                                <div class="mt-4 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <p class="text-sm text-yellow-800">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        <strong>Modo Sandbox:</strong> Pagamentos de rifas não são reais. Use cartões de teste do MercadoPago.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                                    <p class="text-sm text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <strong>Modo Produção:</strong> Rifas estão recebendo pagamentos reais via PIX.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Métodos de Pagamento -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Métodos de Pagamento</h3>
                        </div>
                        <div class="px-6 py-4 space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="enable_pix" id="enable_pix" value="1"
                                       <?= ($settings['enable_pix'] ?? true) ? 'checked' : '' ?>
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="enable_pix" class="ml-2 block text-sm text-gray-700">
                                    <i class="fas fa-qrcode text-green-500 mr-1"></i> Habilitar PIX
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="enable_credit_card" id="enable_credit_card" value="1"
                                       <?= ($settings['enable_credit_card'] ?? true) ? 'checked' : '' ?>
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="enable_credit_card" class="ml-2 block text-sm text-gray-700">
                                    <i class="fas fa-credit-card text-blue-500 mr-1"></i> Habilitar Cartão de Crédito
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="enable_boleto" id="enable_boleto" value="1"
                                       <?= ($settings['enable_boleto'] ?? true) ? 'checked' : '' ?>
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="enable_boleto" class="ml-2 block text-sm text-gray-700">
                                    <i class="fas fa-barcode text-gray-500 mr-1"></i> Habilitar Boleto
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Taxas da Plataforma -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Taxas da Plataforma</h3>
                            <p class="text-sm text-gray-500 mt-1">Porcentagem cobrada em cada doação por categoria</p>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-heartbeat text-red-500 mr-1"></i> Campanhas Médicas (%)
                                </label>
                                <input type="number" name="platform_fee_medical" value="<?= esc($settings['platform_fee_medical'] ?? 0) ?>"
                                       min="0" max="100" step="0.1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-hands-helping text-blue-500 mr-1"></i> Campanhas Sociais (%)
                                </label>
                                <input type="number" name="platform_fee_social" value="<?= esc($settings['platform_fee_social'] ?? 2) ?>"
                                       min="0" max="100" step="0.1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-folder text-gray-500 mr-1"></i> Outras Campanhas (%)
                                </label>
                                <input type="number" name="platform_fee_other" value="<?= esc($settings['platform_fee_other'] ?? 2) ?>"
                                       min="0" max="100" step="0.1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>

                    <!-- Limites -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Limites de Doação</h3>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Mínimo (R$)</label>
                                <input type="number" name="min_donation" value="<?= esc($settings['min_donation'] ?? 5) ?>"
                                       min="1" step="0.01"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Valor Máximo (R$)</label>
                                <input type="number" name="max_donation" value="<?= esc($settings['max_donation'] ?? 50000) ?>"
                                       min="1" step="0.01"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Campanhas -->
                <div x-show="activeTab === 'campaigns'" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Configurações de Campanha</h3>
                        </div>
                        <div class="px-6 py-4 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Duração Máxima (dias)</label>
                                    <input type="number" name="max_campaign_days" value="<?= esc($settings['max_campaign_days'] ?? 90) ?>"
                                           min="1" max="365"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meta Mínima (R$)</label>
                                    <input type="number" name="min_goal" value="<?= esc($settings['min_goal'] ?? 100) ?>"
                                           min="1" step="0.01"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meta Máxima (R$)</label>
                                    <input type="number" name="max_goal" value="<?= esc($settings['max_goal'] ?? 1000000) ?>"
                                           min="1" step="0.01"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Máximo de Imagens por Campanha</label>
                                    <input type="number" name="max_images_per_campaign" value="<?= esc($settings['max_images_per_campaign'] ?? 10) ?>"
                                           min="1" max="50"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="require_approval" id="require_approval" value="1"
                                           <?= ($settings['require_approval'] ?? true) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="require_approval" class="ml-2 block text-sm text-gray-700">
                                        Exigir aprovação do admin para novas campanhas
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="allow_flexible_goal" id="allow_flexible_goal" value="1"
                                           <?= ($settings['allow_flexible_goal'] ?? true) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="allow_flexible_goal" class="ml-2 block text-sm text-gray-700">
                                        Permitir meta flexível (receber mesmo sem atingir meta)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Rifas -->
                <div x-show="activeTab === 'raffles'" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Sistema de Rifas</h3>
                        </div>
                        <div class="px-6 py-4 space-y-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="raffles_enabled" id="raffles_enabled" value="1"
                                       <?= ($settings['raffles_enabled'] ?? true) ? 'checked' : '' ?>
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="raffles_enabled" class="ml-2 block text-sm font-medium text-gray-700">
                                    Habilitar Sistema de Rifas
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mínimo de Números</label>
                                    <input type="number" name="raffle_min_numbers" value="<?= esc($settings['raffle_min_numbers'] ?? 100) ?>"
                                           min="10"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Máximo de Números</label>
                                    <input type="number" name="raffle_max_numbers" value="<?= esc($settings['raffle_max_numbers'] ?? 1000000) ?>"
                                           min="100"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Preço Mínimo por Número (R$)</label>
                                    <input type="number" name="raffle_min_price" value="<?= esc($settings['raffle_min_price'] ?? 0.10) ?>"
                                           min="0.01" step="0.01"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Taxa da Plataforma em Rifas (%)</label>
                                    <input type="number" name="raffle_platform_fee" value="<?= esc($settings['raffle_platform_fee'] ?? 10) ?>"
                                           min="0" max="100" step="0.1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Timeout de Pagamento (minutos)</label>
                                    <input type="number" name="raffle_payment_timeout" value="<?= esc($settings['raffle_payment_timeout'] ?? 30) ?>"
                                           min="5" max="120"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <p class="mt-1 text-xs text-gray-500">Tempo para pagamento antes de liberar números reservados</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Notificações -->
                <div x-show="activeTab === 'notifications'" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Configurações de Notificação</h3>
                        </div>
                        <div class="px-6 py-4 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email do Administrador</label>
                                <input type="email" name="admin_email" value="<?= esc($settings['admin_email'] ?? '') ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <p class="mt-1 text-xs text-gray-500">Recebe notificações importantes do sistema</p>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="email_notifications" id="email_notifications" value="1"
                                           <?= ($settings['email_notifications'] ?? true) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="email_notifications" class="ml-2 block text-sm text-gray-700">
                                        Habilitar notificações por email
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="push_notifications" id="push_notifications" value="1"
                                           <?= ($settings['push_notifications'] ?? true) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="push_notifications" class="ml-2 block text-sm text-gray-700">
                                        Habilitar notificações push
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="notify_new_campaign" id="notify_new_campaign" value="1"
                                           <?= ($settings['notify_new_campaign'] ?? true) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="notify_new_campaign" class="ml-2 block text-sm text-gray-700">
                                        Notificar sobre novas campanhas
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="notify_new_donation" id="notify_new_donation" value="1"
                                           <?= ($settings['notify_new_donation'] ?? false) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="notify_new_donation" class="ml-2 block text-sm text-gray-700">
                                        Notificar sobre novas doações
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" name="notify_new_user" id="notify_new_user" value="1"
                                           <?= ($settings['notify_new_user'] ?? false) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="notify_new_user" class="ml-2 block text-sm text-gray-700">
                                        Notificar sobre novos usuários
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Segurança -->
                <div x-show="activeTab === 'security'" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Configurações de Segurança</h3>
                        </div>
                        <div class="px-6 py-4 space-y-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="recaptcha_enabled" id="recaptcha_enabled" value="1"
                                       <?= ($settings['recaptcha_enabled'] ?? false) ? 'checked' : '' ?>
                                       class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                <label for="recaptcha_enabled" class="ml-2 block text-sm text-gray-700">
                                    Habilitar reCAPTCHA em formulários
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Threshold reCAPTCHA</label>
                                    <input type="number" name="recaptcha_threshold" value="<?= esc($settings['recaptcha_threshold'] ?? 0.5) ?>"
                                           min="0" max="1" step="0.1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <p class="mt-1 text-xs text-gray-500">0 = menos rigoroso, 1 = mais rigoroso</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Máximo Tentativas de Login</label>
                                    <input type="number" name="max_login_attempts" value="<?= esc($settings['max_login_attempts'] ?? 5) ?>"
                                           min="1" max="20"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tempo de Bloqueio (minutos)</label>
                                    <input type="number" name="lockout_time" value="<?= esc($settings['lockout_time'] ?? 15) ?>"
                                           min="1" max="120"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Aparência -->
                <div x-show="activeTab === 'appearance'" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Personalização Visual</h3>
                        </div>
                        <div class="px-6 py-4 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cor Primária</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" name="primary_color" value="<?= esc($settings['primary_color'] ?? '#667eea') ?>"
                                               class="h-10 w-20 rounded border-gray-300">
                                        <input type="text" value="<?= esc($settings['primary_color'] ?? '#667eea') ?>"
                                               class="ml-2 block w-32 rounded-md border-gray-300 shadow-sm" readonly>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cor Secundária</label>
                                    <div class="mt-1 flex items-center">
                                        <input type="color" name="secondary_color" value="<?= esc($settings['secondary_color'] ?? '#764ba2') ?>"
                                               class="h-10 w-20 rounded border-gray-300">
                                        <input type="text" value="<?= esc($settings['secondary_color'] ?? '#764ba2') ?>"
                                               class="ml-2 block w-32 rounded-md border-gray-300 shadow-sm" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">URL do Logo</label>
                                    <input type="text" name="logo_url" value="<?= esc($settings['logo_url'] ?? '') ?>"
                                           placeholder="https://..."
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">URL do Favicon</label>
                                    <input type="text" name="favicon_url" value="<?= esc($settings['favicon_url'] ?? '') ?>"
                                           placeholder="https://..."
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Redes Sociais -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Redes Sociais</h3>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fab fa-facebook text-blue-600 mr-1"></i> Facebook
                                </label>
                                <input type="url" name="facebook_url" value="<?= esc($settings['facebook_url'] ?? '') ?>"
                                       placeholder="https://facebook.com/..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fab fa-instagram text-pink-600 mr-1"></i> Instagram
                                </label>
                                <input type="url" name="instagram_url" value="<?= esc($settings['instagram_url'] ?? '') ?>"
                                       placeholder="https://instagram.com/..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fab fa-twitter text-blue-400 mr-1"></i> Twitter/X
                                </label>
                                <input type="url" name="twitter_url" value="<?= esc($settings['twitter_url'] ?? '') ?>"
                                       placeholder="https://x.com/..."
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fab fa-whatsapp text-green-500 mr-1"></i> WhatsApp
                                </label>
                                <input type="text" name="whatsapp_number" value="<?= esc($settings['whatsapp_number'] ?? '') ?>"
                                       placeholder="5511999999999"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <p class="mt-1 text-xs text-gray-500">Número com código do país, sem +</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: SEO -->
                <div x-show="activeTab === 'seo'" class="space-y-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">SEO e Analytics</h3>
                        </div>
                        <div class="px-6 py-4 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Meta Título Padrão</label>
                                <input type="text" name="meta_title" value="<?= esc($settings['meta_title'] ?? '') ?>"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Meta Descrição Padrão</label>
                                <textarea name="meta_description" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"><?= esc($settings['meta_description'] ?? '') ?></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Google Analytics ID</label>
                                <input type="text" name="google_analytics_id" value="<?= esc($settings['google_analytics_id'] ?? '') ?>"
                                       placeholder="G-XXXXXXXXXX"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end pt-6">
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Todas as Configurações
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
