# 📄 Páginas Refatoradas - Alpine.js + Tailwind

## ✅ Páginas Já Refatoradas

### 1. Layout Base (`app/Views/layout/app.php`) ✅

**Mudanças:**
- Alpine.js plugins adicionados (persist, focus, collapse)
- Global Store inicializado com dados da sessão PHP
- Sistema de notificações global
- `x-cloak` para evitar flash
- Chart.js condicional

---

## 📋 Template de Refatoração

### Exemplo 1: Formulário de Doação (`donations/checkout.php`)

```php
<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="container-custom py-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-heading-1 mb-8">Fazer Doação</h1>

        <!-- Componente Alpine.js de Doação -->
        <div x-data="donationForm(<?= $campaign['id'] ?>, '<?= $campaign['category'] ?>')">

            <!-- Valor da Doação -->
            <div class="card mb-6">
                <div class="card-body">
                    <h2 class="text-xl font-bold mb-4">Escolha o valor da doação</h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <button @click="selectAmount(50)"
                                :class="{ 'ring-2 ring-primary-500 bg-primary-50': amount === 50 && !usingCustomAmount }"
                                class="btn-outline">
                            R$ 50
                        </button>
                        <button @click="selectAmount(100)"
                                :class="{ 'ring-2 ring-primary-500 bg-primary-50': amount === 100 && !usingCustomAmount }"
                                class="btn-outline">
                            R$ 100
                        </button>
                        <button @click="selectAmount(200)"
                                :class="{ 'ring-2 ring-primary-500 bg-primary-50': amount === 200 && !usingCustomAmount }"
                                class="btn-outline">
                            R$ 200
                        </button>
                        <button @click="useCustomAmount()"
                                :class="{ 'ring-2 ring-primary-500 bg-primary-50': usingCustomAmount }"
                                class="btn-outline">
                            Outro valor
                        </button>
                    </div>

                    <div x-show="usingCustomAmount" x-transition x-cloak>
                        <input x-model="customAmount"
                               type="number"
                               min="5"
                               step="0.01"
                               placeholder="Digite o valor (mínimo R$ 5,00)"
                               class="form-input">
                    </div>
                </div>
            </div>

            <!-- Método de Pagamento -->
            <div class="card mb-6">
                <div class="card-body">
                    <h2 class="text-xl font-bold mb-4">Método de pagamento</h2>

                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                               :class="{ 'border-primary-500 bg-primary-50': paymentMethod === 'pix' }">
                            <input x-model="paymentMethod"
                                   type="radio"
                                   value="pix"
                                   class="form-radio">
                            <span class="ml-3 flex-1">
                                <span class="font-semibold">PIX</span>
                                <span class="text-sm text-gray-600 ml-2">(Taxa: R$ 0,95)</span>
                            </span>
                        </label>

                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                               :class="{ 'border-primary-500 bg-primary-50': paymentMethod === 'credit_card' }">
                            <input x-model="paymentMethod"
                                   type="radio"
                                   value="credit_card"
                                   class="form-radio">
                            <span class="ml-3 flex-1">
                                <span class="font-semibold">Cartão de Crédito</span>
                                <span class="text-sm text-gray-600 ml-2">(Taxa: 4,99%)</span>
                            </span>
                        </label>

                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                               :class="{ 'border-primary-500 bg-primary-50': paymentMethod === 'boleto' }">
                            <input x-model="paymentMethod"
                                   type="radio"
                                   value="boleto"
                                   class="form-radio">
                            <span class="ml-3 flex-1">
                                <span class="font-semibold">Boleto Bancário</span>
                                <span class="text-sm text-gray-600 ml-2">(Taxa: R$ 3,49)</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Opções Adicionais -->
            <div class="card mb-6">
                <div class="card-body">
                    <label class="flex items-center">
                        <input x-model="donorPaysGatewayFee"
                               type="checkbox"
                               class="form-checkbox">
                        <span class="ml-3">
                            <span class="font-semibold">Pagar as taxas do gateway</span>
                            <p class="text-sm text-gray-600">Ao marcar esta opção, o criador receberá 100% do valor doado</p>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Resumo -->
            <div class="card mb-6 bg-gray-50">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4">Resumo da Doação</h3>

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Valor da doação:</span>
                            <span class="font-semibold" x-text="formatCurrency(usingCustomAmount ? parseFloat(customAmount) || 0 : amount)"></span>
                        </div>

                        <div x-show="platformFee > 0" class="flex justify-between text-sm text-gray-600">
                            <span>Taxa da plataforma (1%):</span>
                            <span x-text="formatCurrency(platformFee)"></span>
                        </div>

                        <div x-show="donorPaysGatewayFee" class="flex justify-between text-sm text-gray-600">
                            <span>Taxa do gateway:</span>
                            <span x-text="formatCurrency(gatewayFee)"></span>
                        </div>

                        <div class="pt-3 border-t border-gray-300">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-lg">Total a pagar:</span>
                                <span class="font-bold text-2xl text-primary-600" x-text="formatCurrency(totalAmount)"></span>
                            </div>
                        </div>

                        <div class="text-sm text-gray-600 pt-2">
                            <span>O criador receberá: </span>
                            <span class="font-semibold text-green-600" x-text="formatCurrency(netAmount)"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados do Doador -->
            <div class="card mb-6">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-4">Seus dados</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Nome completo</label>
                            <input x-model="donorName"
                                   type="text"
                                   class="form-input"
                                   :class="{ 'border-red-500': errors.donorName }">
                            <p x-show="errors.donorName" class="form-error" x-text="errors.donorName"></p>
                        </div>

                        <div>
                            <label class="form-label">Email</label>
                            <input x-model="donorEmail"
                                   type="email"
                                   class="form-input"
                                   :class="{ 'border-red-500': errors.donorEmail }">
                            <p x-show="errors.donorEmail" class="form-error" x-text="errors.donorEmail"></p>
                        </div>

                        <div>
                            <label class="form-label">CPF</label>
                            <input x-model="donorCpf"
                                   @input="formatCpf()"
                                   type="text"
                                   maxlength="14"
                                   class="form-input"
                                   :class="{ 'border-red-500': errors.donorCpf }">
                            <p x-show="errors.donorCpf" class="form-error" x-text="errors.donorCpf"></p>
                        </div>

                        <div>
                            <label class="form-label">Mensagem (opcional)</label>
                            <textarea x-model="message"
                                      rows="3"
                                      class="form-textarea"
                                      placeholder="Deixe uma mensagem de apoio..."></textarea>
                        </div>

                        <label class="flex items-center">
                            <input x-model="isAnonymous" type="checkbox" class="form-checkbox">
                            <span class="ml-2">Fazer doação anônima</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Botão de Envio -->
            <button @click="submitDonation()"
                    :disabled="!isValidAmount || loading"
                    :class="{ 'opacity-50 cursor-not-allowed': !isValidAmount || loading }"
                    class="btn-primary w-full py-4 text-lg">
                <span x-show="!loading">
                    <i class="fas fa-heart mr-2"></i>
                    Confirmar Doação
                </span>
                <span x-show="loading" class="flex items-center justify-center">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Processando...
                </span>
            </button>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
```

---

### Exemplo 2: Listagem de Campanhas (`campaigns/list.php`)

```php
<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-white py-6 border-b">
    <div class="container-custom">
        <h1 class="text-heading-1">Campanhas</h1>
        <p class="text-gray-600 mt-2">Ajude quem precisa com sua doação</p>
    </div>
</div>

<div class="container-custom py-12">

    <!-- Componente de Filtro Alpine.js -->
    <div x-data="campaignFilter(<?= json_encode($campaigns) ?>)" x-init="init()">

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- Busca -->
                <div>
                    <label class="form-label">Buscar</label>
                    <input x-model="search"
                           type="text"
                           placeholder="Digite o nome da campanha..."
                           class="form-input">
                </div>

                <!-- Categoria -->
                <div>
                    <label class="form-label">Categoria</label>
                    <select x-model="category" class="form-select">
                        <option value="all">Todas as categorias</option>
                        <option value="medical">Médicas</option>
                        <option value="social">Sociais</option>
                        <option value="creative">Criativas</option>
                        <option value="emergency">Emergenciais</option>
                        <option value="other">Outras</option>
                    </select>
                </div>

                <!-- Ordenação -->
                <div>
                    <label class="form-label">Ordenar por</label>
                    <select x-model="sortBy" class="form-select">
                        <option value="recent">Mais recentes</option>
                        <option value="progress">Maior progresso</option>
                        <option value="goal">Maior meta</option>
                    </select>
                </div>
            </div>

            <!-- Contador de Resultados -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-gray-600">
                    <span class="font-semibold" x-text="campaignCount"></span>
                    <span x-text="campaignCount === 1 ? 'campanha encontrada' : 'campanhas encontradas'"></span>
                </p>
            </div>
        </div>

        <!-- Grid de Campanhas -->
        <div class="grid-campaigns">
            <template x-for="campaign in filteredCampaigns" :key="campaign.id">
                <div class="campaign-card">
                    <!-- Imagem -->
                    <img :src="`/uploads/campaigns/${campaign.image}`"
                         :alt="campaign.title"
                         class="campaign-card-image">

                    <!-- Badge de Categoria -->
                    <div class="absolute top-4 left-4">
                        <span class="badge"
                              :class="`badge-${campaign.category}`"
                              x-text="campaign.category_label"></span>
                    </div>

                    <!-- Corpo do Card -->
                    <div class="campaign-card-body">
                        <h3 class="campaign-card-title" x-text="campaign.title"></h3>
                        <p class="campaign-card-description" x-text="campaign.description"></p>

                        <!-- Progress Bar -->
                        <div x-data="progressBar(campaign.raised_amount, campaign.goal_amount)" class="mt-4">
                            <div class="progress-bar mb-2">
                                <div class="progress-bar-fill"
                                     :class="progressColor"
                                     :style="`width: ${percentage}%`"
                                     x-transition></div>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="font-semibold text-primary-600" x-text="formatCurrency(current)"></span>
                                <span class="text-gray-600" x-text="`${percentage}% de ${formatCurrency(goal)}`"></span>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="campaign-card-footer">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-users mr-1"></i>
                                <span x-text="campaign.donors_count"></span> apoiadores
                            </div>
                            <a :href="`/campaign/${campaign.slug}`" class="btn-primary btn-sm">
                                Ver Campanha
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="campaignCount === 0" x-transition class="text-center py-16" x-cloak>
            <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-bold text-gray-700 mb-2">Nenhuma campanha encontrada</h3>
            <p class="text-gray-600">Tente ajustar os filtros de busca</p>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
```

---

### Exemplo 3: Dashboard (`dashboard/index.php`)

```php
<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-white border-b">
    <div class="container-custom py-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-heading-2">Meu Dashboard</h1>
                <p class="text-gray-600">Olá, <span x-data x-text="$store.app.user.name"></span>!</p>
            </div>
            <a href="/campaign/create" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Nova Campanha
            </a>
        </div>
    </div>
</div>

<div class="container-custom py-12">

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Total Arrecadado -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Arrecadado</p>
                        <p class="text-2xl font-bold text-green-600">
                            <?= number_format($total_raised, 2, ',', '.') ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campanhas Ativas -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Campanhas Ativas</p>
                        <p class="text-2xl font-bold text-blue-600"><?= $active_campaigns ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bullseye text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Doações -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Doações</p>
                        <p class="text-2xl font-bold text-primary-600"><?= $total_donations ?></p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-heart text-primary-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assinaturas Ativas -->
        <div class="card">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Assinaturas Ativas</p>
                        <p class="text-2xl font-bold text-purple-600"><?= $active_subscriptions ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-sync text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Alpine.js -->
    <div x-data="tabs(0)">
        <!-- Tab Headers -->
        <div class="flex border-b border-gray-200 mb-6">
            <button @click="setActive(0)"
                    :class="{ 'border-primary-500 text-primary-600': isActive(0), 'border-transparent text-gray-600': !isActive(0) }"
                    class="px-6 py-3 border-b-2 font-semibold transition-colors hover:text-primary-600">
                Minhas Campanhas
            </button>
            <button @click="setActive(1)"
                    :class="{ 'border-primary-500 text-primary-600': isActive(1), 'border-transparent text-gray-600': !isActive(1) }"
                    class="px-6 py-3 border-b-2 font-semibold transition-colors hover:text-primary-600">
                Minhas Doações
            </button>
        </div>

        <!-- Tab Panels -->
        <div>
            <!-- Panel: Minhas Campanhas -->
            <div x-show="isActive(0)" x-transition>
                <div class="grid-campaigns">
                    <?php foreach ($recent_campaigns as $campaign): ?>
                    <div class="campaign-card">
                        <img src="/uploads/campaigns/<?= $campaign['image'] ?>"
                             alt="<?= esc($campaign['title']) ?>"
                             class="campaign-card-image">

                        <div class="campaign-card-body">
                            <h3 class="campaign-card-title"><?= esc($campaign['title']) ?></h3>

                            <!-- Progress -->
                            <div x-data="progressBar(<?= $campaign['raised_amount'] ?>, <?= $campaign['goal_amount'] ?>)">
                                <div class="progress-bar mb-2">
                                    <div class="progress-bar-fill"
                                         :class="progressColor"
                                         :style="`width: ${percentage}%`"></div>
                                </div>
                                <p class="text-sm text-gray-600" x-text="`${percentage}% atingido`"></p>
                            </div>

                            <a href="/dashboard/campaign/<?= $campaign['id'] ?>" class="btn-primary w-full mt-4">
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Panel: Minhas Doações -->
            <div x-show="isActive(1)" x-transition x-cloak>
                <div class="space-y-4">
                    <?php foreach ($recent_donations as $donation): ?>
                    <div class="card card-hover">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-bold"><?= esc($donation['campaign_title']) ?></h4>
                                    <p class="text-sm text-gray-600">
                                        <?= date('d/m/Y', strtotime($donation['created_at'])) ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xl font-bold text-green-600">
                                        R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                                    </p>
                                    <span class="badge badge-success">Confirmada</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
```

---

## 🎯 Checklist de Refatoração

Para cada página, siga este checklist:

- [ ] Adicionar `x-data` ao container principal
- [ ] Substituir jQuery por Alpine.js
- [ ] Usar componentes reutilizáveis (donationForm, campaignFilter, etc)
- [ ] Adicionar `x-cloak` em elementos com `x-show`
- [ ] Usar `x-transition` para animações
- [ ] Validar formulários com Alpine
- [ ] Usar `$store.app` para estado global
- [ ] Remover scripts inline jQuery
- [ ] Testar responsividade
- [ ] Verificar acessibilidade

---

## 🚀 Próximos Passos

1. Refatorar `donations/checkout.php`
2. Refatorar `campaigns/list.php`
3. Refatorar `dashboard/index.php`
4. Refatorar `auth/login.php` e `auth/register.php`
5. Adicionar gráficos com Chart.js
6. Implementar lazy loading de imagens
7. Otimizar performance

---

**Última atualização**: 10/10/2025
