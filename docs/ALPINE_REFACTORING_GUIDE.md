# 🎨 Guia de Refatoração - Alpine.js + Tailwind CSS

## 📚 Índice

- [Visão Geral](#visão-geral)
- [Estrutura de Arquivos](#estrutura-de-arquivos)
- [Componentes Criados](#componentes-criados)
- [Exemplos Práticos](#exemplos-práticos)
- [Migração Passo a Passo](#migração-passo-a-passo)
- [Boas Práticas](#boas-práticas)

---

## 🎯 Visão Geral

Esta refatoração substitui jQuery por **Alpine.js** e implementa **Tailwind CSS** de forma profissional, seguindo as melhores práticas da indústria.

### Tecnologias

- **Alpine.js 3.13** - Framework JavaScript reativo (15kb)
- **Tailwind CSS 3.4** - Framework CSS utility-first
- **Chart.js 4.4** - Gráficos e visualizações
- **Alpine Plugins**:
  - `@alpinejs/persist` - Persistência de estado
  - `@alpinejs/focus` - Gerenciamento de foco
  - `@alpinejs/collapse` - Animações de colapso

---

## 📁 Estrutura de Arquivos

```
public/assets/
├── css/
│   ├── input.css          # Input Tailwind (já existente)
│   └── app.css            # Output compilado
│
└── js/
    ├── alpine-init.js     # ✨ NOVO - Inicialização Alpine
    ├── alpine-components.js # ✨ NOVO - Componentes reutilizáveis
    └── app.js             # JavaScript principal
```

---

## 🧩 Componentes Criados

### 1. **donationForm** - Formulário de Doação

Componente completo para processar doações com cálculo automático de taxas.

**Uso:**
```html
<div x-data="donationForm(<?= $campaign['id'] ?>, '<?= $campaign['category'] ?>')">
    <!-- Seleção de Valor -->
    <div class="grid grid-cols-3 gap-4">
        <button @click="selectAmount(50)"
                :class="{ 'ring-2 ring-primary-500': amount === 50 && !usingCustomAmount }"
                class="btn-outline">
            R$ 50
        </button>
        <button @click="selectAmount(100)"
                :class="{ 'ring-2 ring-primary-500': amount === 100 && !usingCustomAmount }"
                class="btn-outline">
            R$ 100
        </button>
        <button @click="useCustomAmount()"
                :class="{ 'ring-2 ring-primary-500': usingCustomAmount }"
                class="btn-outline">
            Outro valor
        </button>
    </div>

    <!-- Valor Personalizado -->
    <div x-show="usingCustomAmount" x-transition>
        <input x-model="customAmount"
               type="number"
               min="5"
               step="0.01"
               class="form-input"
               placeholder="Digite o valor">
    </div>

    <!-- Método de Pagamento -->
    <select x-model="paymentMethod" class="form-select">
        <option value="pix">PIX (R$ 0,95)</option>
        <option value="credit_card">Cartão de Crédito (4,99%)</option>
        <option value="boleto">Boleto (R$ 3,49)</option>
    </select>

    <!-- Doador Paga Taxas -->
    <label class="flex items-center">
        <input x-model="donorPaysGatewayFee" type="checkbox" class="form-checkbox">
        <span class="ml-2">Pagar taxas do gateway</span>
    </label>

    <!-- Resumo -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <div class="flex justify-between mb-2">
            <span>Valor da doação:</span>
            <span class="font-semibold" x-text="formatCurrency(usingCustomAmount ? parseFloat(customAmount) || 0 : amount)"></span>
        </div>
        <div x-show="donorPaysGatewayFee" class="flex justify-between mb-2">
            <span>Taxa do gateway:</span>
            <span x-text="formatCurrency(gatewayFee)"></span>
        </div>
        <div class="flex justify-between pt-2 border-t border-gray-200">
            <span class="font-bold">Total a pagar:</span>
            <span class="font-bold text-primary-600" x-text="formatCurrency(totalAmount)"></span>
        </div>
    </div>

    <!-- Botão Submit -->
    <button @click="submitDonation()"
            :disabled="!isValidAmount || loading"
            :class="{ 'opacity-50 cursor-not-allowed': !isValidAmount || loading }"
            class="btn-primary w-full">
        <span x-show="!loading">Doar Agora</span>
        <span x-show="loading" class="flex items-center justify-center">
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Processando...
        </span>
    </button>
</div>
```

**Propriedades:**
- `amount` - Valor da doação
- `paymentMethod` - Método de pagamento (pix/credit_card/boleto)
- `donorPaysGatewayFee` - Se o doador paga taxas
- `gatewayFee` - Taxa do gateway (computed)
- `platformFee` - Taxa da plataforma (computed)
- `totalAmount` - Valor total (computed)
- `netAmount` - Valor líquido para o criador (computed)

**Métodos:**
- `selectAmount(value)` - Seleciona valor predefinido
- `useCustomAmount()` - Ativa input customizado
- `formatCurrency(value)` - Formata valor em R$
- `validate()` - Valida formulário
- `submitDonation()` - Envia doação via AJAX

---

### 2. **campaignFilter** - Filtro de Campanhas

Filtro reativo para listagem de campanhas.

**Uso:**
```html
<div x-data="campaignFilter(<?= json_encode($campaigns) ?>)" x-init="init()">
    <!-- Filtros -->
    <div class="flex gap-4 mb-6">
        <!-- Busca -->
        <input x-model="search"
               type="text"
               placeholder="Buscar campanhas..."
               class="form-input flex-1">

        <!-- Categoria -->
        <select x-model="category" class="form-select w-48">
            <option value="all">Todas as categorias</option>
            <option value="medical">Médicas</option>
            <option value="social">Sociais</option>
            <option value="creative">Criativas</option>
            <option value="emergency">Emergenciais</option>
        </select>

        <!-- Ordenação -->
        <select x-model="sortBy" class="form-select w-48">
            <option value="recent">Mais recentes</option>
            <option value="progress">Maior progresso</option>
            <option value="goal">Maior meta</option>
        </select>
    </div>

    <!-- Contador -->
    <p class="text-gray-600 mb-4">
        <span x-text="campaignCount"></span> campanha(s) encontrada(s)
    </p>

    <!-- Grid de Campanhas -->
    <div class="grid-campaigns">
        <template x-for="campaign in filteredCampaigns" :key="campaign.id">
            <div class="campaign-card">
                <img :src="campaign.image" :alt="campaign.title" class="campaign-card-image">
                <div class="campaign-card-body">
                    <span class="badge" :class="`badge-${campaign.category}`" x-text="campaign.category_label"></span>
                    <h3 class="campaign-card-title" x-text="campaign.title"></h3>
                    <p class="campaign-card-description" x-text="campaign.description"></p>

                    <!-- Progress -->
                    <div x-data="progressBar(campaign.raised_amount, campaign.goal_amount)">
                        <div class="progress-bar mb-2">
                            <div class="progress-bar-fill"
                                 :class="progressColor"
                                 :style="`width: ${percentage}%`"></div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="font-semibold" x-text="formatCurrency(current)"></span>
                            <span class="text-gray-600" x-text="`${percentage}%`"></span>
                        </div>
                    </div>

                    <a :href="`/campaign/${campaign.slug}`" class="btn-primary w-full mt-4">
                        Ver Campanha
                    </a>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="campaignCount === 0" class="text-center py-12">
        <i class="fas fa-search text-gray-300 text-6xl mb-4"></i>
        <p class="text-gray-600 text-lg">Nenhuma campanha encontrada</p>
    </div>
</div>
```

---

### 3. **progressBar** - Barra de Progresso

Barra de progresso animada com cálculo automático de porcentagem.

**Uso:**
```html
<div x-data="progressBar(<?= $campaign['raised_amount'] ?>, <?= $campaign['goal_amount'] ?>)">
    <div class="progress-bar mb-2">
        <div class="progress-bar-fill"
             :class="progressColor"
             :style="`width: ${percentage}%`"
             x-transition></div>
    </div>

    <div class="flex justify-between text-sm">
        <span class="font-semibold" x-text="formatCurrency(current)"></span>
        <span class="text-gray-600">de <span x-text="formatCurrency(goal)"></span></span>
    </div>

    <p class="text-center text-lg font-bold mt-2" x-text="`${percentage}% atingido`"></p>
</div>
```

---

### 4. **modal** - Modal Reutilizável

Modal genérico para qualquer conteúdo.

**Uso:**
```html
<!-- Botão de Trigger -->
<button @click="$refs.myModal.show()" class="btn-primary">
    Abrir Modal
</button>

<!-- Modal -->
<div x-data="modal()" x-ref="myModal">
    <!-- Overlay -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="hide()"
         class="modal-overlay"
         x-cloak></div>

    <!-- Content -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="modal-content"
         x-cloak>
        <div class="modal-dialog">
            <div class="modal-panel" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Título do Modal</h2>
                    <button @click="hide()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <div class="mb-6">
                    <!-- Conteúdo do modal -->
                    <p>Seu conteúdo aqui...</p>
                </div>

                <div class="flex justify-end gap-4">
                    <button @click="hide()" class="btn-outline">Cancelar</button>
                    <button class="btn-primary">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

### 5. **dropdown** - Dropdown Menu

Menu dropdown responsivo.

**Uso:**
```html
<div x-data="dropdown()" @click.away="close()" class="dropdown">
    <!-- Trigger -->
    <button @click="toggle()" class="btn-outline">
        Menu <i class="fas fa-chevron-down ml-2"></i>
    </button>

    <!-- Menu -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="dropdown-menu"
         x-cloak>
        <a href="#" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Perfil
        </a>
        <a href="#" class="dropdown-item">
            <i class="fas fa-cog mr-2"></i> Configurações
        </a>
        <hr class="my-1">
        <a href="#" class="dropdown-item text-red-600">
            <i class="fas fa-sign-out-alt mr-2"></i> Sair
        </a>
    </div>
</div>
```

---

### 6. **tabs** - Sistema de Abas

Navegação por abas.

**Uso:**
```html
<div x-data="tabs(0)">
    <!-- Tab Headers -->
    <div class="flex border-b border-gray-200">
        <button @click="setActive(0)"
                :class="{ 'border-primary-500 text-primary-600': isActive(0) }"
                class="px-6 py-3 border-b-2 font-semibold transition-colors">
            Doações Únicas
        </button>
        <button @click="setActive(1)"
                :class="{ 'border-primary-500 text-primary-600': isActive(1) }"
                class="px-6 py-3 border-b-2 font-semibold transition-colors">
            Doações Recorrentes
        </button>
    </div>

    <!-- Tab Panels -->
    <div class="py-6">
        <!-- Panel 0 -->
        <div x-show="isActive(0)" x-transition>
            <p>Conteúdo das doações únicas...</p>
        </div>

        <!-- Panel 1 -->
        <div x-show="isActive(1)" x-transition>
            <p>Conteúdo das doações recorrentes...</p>
        </div>
    </div>
</div>
```

---

## 🔄 Migração Passo a Passo

### Passo 1: Instalar Dependências

```bash
cd c:/laragon/www/doarfazbem
npm install
```

Isso instalará:
- Alpine.js 3.13.5
- @alpinejs/persist
- @alpinejs/focus
- @alpinejs/collapse
- Chart.js 4.4.1

### Passo 2: Compilar Assets

```bash
npm run build
```

Ou para desenvolvimento com watch:
```bash
npm run dev
```

### Passo 3: Atualizar Layout Base

Editar `app/Views/layout/app.php`:

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'DoarFazBem') ?></title>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine.js Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine Components -->
    <script defer src="<?= base_url('assets/js/alpine-components.js') ?>"></script>
</head>
<body class="bg-gray-50" x-data x-cloak>
    <!-- Notifications -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="notification in $store.app.notifications" :key="notification.id">
            <div class="bg-white rounded-lg shadow-lg p-4 max-w-sm animate-slide-up">
                <div class="flex items-start">
                    <i :class="`fas ${notification.type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-red-500'} text-2xl mr-3`"></i>
                    <div class="flex-1">
                        <p class="font-semibold" x-text="notification.message"></p>
                    </div>
                    <button @click="$store.app.removeNotification(notification.id)" class="text-gray-400 hover:text-gray-600 ml-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Header -->
    <?= $this->include('layout/header') ?>

    <!-- Main Content -->
    <main>
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <?= $this->include('layout/footer') ?>

    <!-- Chart.js (se necessário) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</body>
</html>
```

### Passo 4: Migrar Componentes

#### Antes (jQuery):
```html
<script>
$(document).ready(function() {
    $('#donationAmount').on('change', function() {
        var amount = $(this).val();
        var fee = calculateFee(amount);
        $('#totalAmount').text(amount + fee);
    });
});
</script>
```

#### Depois (Alpine.js):
```html
<div x-data="{ amount: 50, get total() { return this.amount + this.calculateFee() } }">
    <input x-model.number="amount" type="number" class="form-input">
    <p>Total: <span x-text="total"></span></p>
</div>
```

---

## ✅ Boas Práticas

### 1. Use `x-cloak` para evitar flash de conteúdo não estilizado

```html
<div x-data="{ open: false }" x-cloak>
    <div x-show="open">Conteúdo...</div>
</div>
```

### 2. Use `@click.away` para fechar dropdowns e modals

```html
<div x-data="{ open: false }" @click.away="open = false">
    <!-- ... -->
</div>
```

### 3. Use transitions para animações suaves

```html
<div x-show="open"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100">
    <!-- ... -->
</div>
```

### 4. Use computed properties para lógica complexa

```javascript
get totalAmount() {
    return this.amount + this.fee;
}
```

### 5. Use Alpine Store para estado global

```javascript
// Acessar store
$store.app.user.name

// Modificar store
$store.app.setUser(userData)
```

---

## 📦 Próximos Passos

1. ✅ Instalar dependências: `npm install`
2. ✅ Compilar assets: `npm run build`
3. 🔄 Migrar views existentes para Alpine.js
4. 🔄 Testar todos os componentes
5. 🔄 Implementar dashboards com Tremor
6. 🔄 Adicionar gráficos com Chart.js

---

## 🆘 Suporte

**Documentação:**
- [Alpine.js Docs](https://alpinejs.dev/)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Chart.js Docs](https://www.chartjs.org/docs/)

**Problemas Comuns:**

**Q: Alpine não está funcionando**
A: Verifique se o script está com `defer` e se está no final do `<head>`

**Q: Tailwind classes não aplicam**
A: Execute `npm run build` para recompilar o CSS

**Q: Componente não encontrado**
A: Verifique se `alpine-components.js` está carregado antes do Alpine.js core

---

**Última atualização**: 10/10/2025
