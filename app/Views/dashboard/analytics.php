<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-heading-1 text-gray-900">Analytics Dashboard</h1>
            <p class="text-gray-600 mt-2">Acompanhe o desempenho das suas campanhas em tempo real</p>
        </div>

        <!-- KPI Cards (Tremor-style Metrics) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Card 1: Total Arrecadado -->
            <div x-data="metricCard(<?= $total_raised ?? 0 ?>, <?= $previous_total_raised ?? 0 ?>, 'Total Arrecadado', 'dollar-sign')"
                 class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i :class="`fas fa-${icon} text-green-600 text-xl`"></i>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1" :class="trendColor">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-2xl font-bold text-gray-900">
                        R$ <span x-text="formatNumber(value)"></span>
                    </h3>
                </div>
                <p class="text-sm text-gray-600" x-text="title"></p>
            </div>

            <!-- Card 2: Doações Recebidas -->
            <div x-data="metricCard(<?= $total_donations ?? 0 ?>, <?= $previous_donations ?? 0 ?>, 'Doações Recebidas', 'heart')"
                 class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-lg">
                            <i :class="`fas fa-${icon} text-red-600 text-xl`"></i>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1" :class="trendColor">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-2xl font-bold text-gray-900" x-text="formatNumber(value)"></h3>
                </div>
                <p class="text-sm text-gray-600" x-text="title"></p>
            </div>

            <!-- Card 3: Campanhas Ativas -->
            <div x-data="metricCard(<?= $active_campaigns ?? 0 ?>, <?= $previous_active ?? 0 ?>, 'Campanhas Ativas', 'bullhorn')"
                 class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i :class="`fas fa-${icon} text-blue-600 text-xl`"></i>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1" :class="trendColor">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-2xl font-bold text-gray-900" x-text="formatNumber(value)"></h3>
                </div>
                <p class="text-sm text-gray-600" x-text="title"></p>
            </div>

            <!-- Card 4: Taxa de Conversão -->
            <div x-data="metricCard(<?= $conversion_rate ?? 0 ?>, <?= $previous_conversion ?? 0 ?>, 'Taxa de Conversão', 'percentage')"
                 class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <i :class="`fas fa-${icon} text-purple-600 text-xl`"></i>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1" :class="trendColor">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-2xl font-bold text-gray-900">
                        <span x-text="value.toFixed(1)"></span>%
                    </h3>
                </div>
                <p class="text-sm text-gray-600" x-text="title"></p>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Area Chart: Doações ao Longo do Tempo -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                 x-data="areaChart(
                     <?= json_encode($donation_labels ?? ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']) ?>,
                     <?= json_encode($donation_data ?? [1200, 1900, 1500, 2400, 3200, 2800]) ?>,
                     'Doações (R$)'
                 )"
                 x-init="init()">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Doações ao Longo do Tempo</h3>
                <div class="h-64">
                    <canvas :id="chartId"></canvas>
                </div>
            </div>

            <!-- Bar Chart: Doações por Categoria -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                 x-data="barChart(
                     <?= json_encode($category_labels ?? ['Médica', 'Social', 'Educação', 'Negócio', 'Criativa']) ?>,
                     <?= json_encode($category_data ?? [4200, 3100, 2800, 1900, 1500]) ?>,
                     'Arrecadação (R$)',
                     'rgba(16, 185, 129, 0.8)'
                 )"
                 x-init="init()">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Arrecadação por Categoria</h3>
                <div class="h-64">
                    <canvas :id="chartId"></canvas>
                </div>
            </div>
        </div>

        <!-- Donut Chart e Progress Circle -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Donut Chart: Distribuição de Doações -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                 x-data="donutChart(
                     <?= json_encode($donut_labels ?? ['PIX', 'Cartão', 'Boleto']) ?>,
                     <?= json_encode($donut_data ?? [5200, 3800, 1500]) ?>,
                     ['rgb(16, 185, 129)', 'rgb(59, 130, 246)', 'rgb(251, 146, 60)']
                 )"
                 x-init="init()">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Métodos de Pagamento</h3>
                <div class="h-64">
                    <canvas :id="chartId"></canvas>
                </div>
            </div>

            <!-- Progress Circles: Metas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Metas de Campanhas</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div x-data="progressCircle(75, 100, 10)" class="flex flex-col items-center">
                        <svg :width="size" :height="size" class="transform -rotate-90">
                            <circle :cx="size/2" :cy="size/2" :r="radius"
                                    fill="none" stroke="#e5e7eb" :stroke-width="strokeWidth"></circle>
                            <circle :cx="size/2" :cy="size/2" :r="radius"
                                    fill="none" :stroke="color" :stroke-width="strokeWidth"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="strokeDashoffset"
                                    stroke-linecap="round"
                                    class="transition-all duration-500"></circle>
                        </svg>
                        <div class="mt-3 text-center">
                            <p class="text-2xl font-bold text-gray-900" x-text="percentage + '%'"></p>
                            <p class="text-sm text-gray-600">Campanha Médica</p>
                        </div>
                    </div>

                    <div x-data="progressCircle(45, 100, 10)" class="flex flex-col items-center">
                        <svg :width="size" :height="size" class="transform -rotate-90">
                            <circle :cx="size/2" :cy="size/2" :r="radius"
                                    fill="none" stroke="#e5e7eb" :stroke-width="strokeWidth"></circle>
                            <circle :cx="size/2" :cy="size/2" :r="radius"
                                    fill="none" :stroke="color" :stroke-width="strokeWidth"
                                    :stroke-dasharray="circumference"
                                    :stroke-dashoffset="strokeDashoffset"
                                    stroke-linecap="round"
                                    class="transition-all duration-500"></circle>
                        </svg>
                        <div class="mt-3 text-center">
                            <p class="text-2xl font-bold text-gray-900" x-text="percentage + '%'"></p>
                            <p class="text-sm text-gray-600">Campanha Social</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table: Últimas Doações -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
             x-data="dataTable(
                 <?= json_encode($recent_donations ?? [
                     ['id' => 1, 'donor' => 'João Silva', 'amount' => 150.00, 'campaign' => 'Tratamento Médico', 'date' => '2025-10-10', 'method' => 'PIX'],
                     ['id' => 2, 'donor' => 'Maria Santos', 'amount' => 200.00, 'campaign' => 'Educação', 'date' => '2025-10-09', 'method' => 'Cartão'],
                     ['id' => 3, 'donor' => 'Pedro Costa', 'amount' => 75.50, 'campaign' => 'Social', 'date' => '2025-10-09', 'method' => 'Boleto'],
                     ['id' => 4, 'donor' => 'Ana Oliveira', 'amount' => 500.00, 'campaign' => 'Tratamento Médico', 'date' => '2025-10-08', 'method' => 'PIX'],
                     ['id' => 5, 'donor' => 'Carlos Mendes', 'amount' => 120.00, 'campaign' => 'Educação', 'date' => '2025-10-08', 'method' => 'Cartão']
                 ]) ?>,
                 [
                     { key: 'donor', label: 'Doador', sortable: true },
                     { key: 'amount', label: 'Valor', sortable: true },
                     { key: 'campaign', label: 'Campanha', sortable: true },
                     { key: 'date', label: 'Data', sortable: true },
                     { key: 'method', label: 'Método', sortable: false }
                 ]
             )">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Últimas Doações</h3>
                <div class="flex items-center space-x-4">
                    <input type="text"
                           x-model="search"
                           placeholder="Buscar..."
                           class="form-input text-sm py-2">
                    <select x-model.number="perPage" class="form-input text-sm py-2">
                        <option value="5">5 por página</option>
                        <option value="10">10 por página</option>
                        <option value="20">20 por página</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <template x-for="column in columns" :key="column.key">
                                <th @click="column.sortable && sort(column.key)"
                                    :class="column.sortable ? 'cursor-pointer hover:bg-gray-100' : ''"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center space-x-1">
                                        <span x-text="column.label"></span>
                                        <i x-show="column.sortable" :class="`fas ${getSortIcon(column.key)} text-gray-400`"></i>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="row in paginatedData" :key="row.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="row.donor"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="font-semibold text-green-600">R$ <span x-text="row.amount.toFixed(2).replace('.', ',')"></span></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="row.campaign"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="row.date"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span x-text="row.method"
                                          :class="{
                                              'bg-green-100 text-green-800': row.method === 'PIX',
                                              'bg-blue-100 text-blue-800': row.method === 'Cartão',
                                              'bg-orange-100 text-orange-800': row.method === 'Boleto'
                                          }"
                                          class="px-2 py-1 text-xs font-semibold rounded-full"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6">
                <p class="text-sm text-gray-600">
                    Mostrando <span x-text="(currentPage - 1) * perPage + 1"></span> a
                    <span x-text="Math.min(currentPage * perPage, filteredData.length)"></span> de
                    <span x-text="filteredData.length"></span> resultados
                </p>
                <div class="flex space-x-2">
                    <button @click="currentPage = Math.max(1, currentPage - 1)"
                            :disabled="currentPage === 1"
                            :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                            class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                        Anterior
                    </button>
                    <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                            class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                        Próxima
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
