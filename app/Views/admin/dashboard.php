<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Menu de Navegação Admin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8">
            <nav class="flex flex-wrap gap-2">
                <a href="<?= base_url('admin/dashboard') ?>"
                   class="px-4 py-2 rounded-lg bg-primary-500 text-white font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="<?= base_url('admin/campaigns?status=pending') ?>"
                   class="px-4 py-2 rounded-lg bg-yellow-100 text-yellow-800 hover:bg-yellow-200 font-medium">
                    <i class="fas fa-clock mr-2"></i>Pendentes
                </a>
                <a href="<?= base_url('admin/campaigns?status=active') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-bullhorn mr-2"></i>Campanhas
                </a>
                <a href="<?= base_url('admin/users') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-users mr-2"></i>Usuários
                </a>
                <a href="<?= base_url('admin/donations') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-hand-holding-heart mr-2"></i>Doações
                </a>
                <a href="<?= base_url('admin/comments') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-comments mr-2"></i>Comentários
                </a>
                <a href="<?= base_url('admin/withdrawals') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-money-bill-wave mr-2"></i>Saques
                </a>
                <a href="<?= base_url('admin/raffles') ?>"
                   class="px-4 py-2 rounded-lg bg-purple-100 text-purple-700 hover:bg-purple-200 font-medium">
                    <i class="fas fa-ticket-alt mr-2"></i>Rifas
                </a>
                <a href="<?= base_url('admin/reports') ?>"
                   class="px-4 py-2 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 font-medium">
                    <i class="fas fa-file-export mr-2"></i>Relatórios
                </a>
                <a href="<?= base_url('admin/audit-logs') ?>"
                   class="px-4 py-2 rounded-lg bg-orange-100 text-orange-700 hover:bg-orange-200 font-medium">
                    <i class="fas fa-shield-alt mr-2"></i>Logs
                </a>
                <a href="<?= base_url('admin/backup') ?>"
                   class="px-4 py-2 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 font-medium">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Backup
                </a>
                <a href="<?= base_url('admin/settings') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-cog mr-2"></i>Configurações
                </a>
            </nav>
        </div>

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 text-gray-900">Super Admin Dashboard</h1>
                <p class="text-gray-600 mt-2">Visão geral completa da plataforma DoarFazBem</p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= base_url('admin/campaigns?status=pending') ?>" class="btn-secondary">
                    <i class="fas fa-clock mr-2"></i>Ver Pendentes
                </a>
                <a href="<?= base_url('admin/settings') ?>" class="btn-primary">
                    <i class="fas fa-cog mr-2"></i>Configurações
                </a>
            </div>
        </div>

        <!-- Super KPIs (4 principais métricas) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total da Plataforma -->
            <div x-data="metricCard(<?= $platform_total ?? 0 ?>, <?= $prev_platform_total ?? 0 ?>, 'Volume Total', 'chart-line')"
                 class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i :class="`fas fa-${icon} text-2xl`"></i>
                    </div>
                    <div class="flex items-center space-x-1 bg-white/20 px-3 py-1 rounded-full">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-3xl font-bold">R$ <span x-text="formatNumber(value)"></span></h3>
                </div>
                <p class="text-sm text-white/80" x-text="title"></p>
            </div>

            <!-- Usuários Ativos -->
            <div x-data="metricCard(<?= $active_users ?? 0 ?>, <?= $prev_active_users ?? 0 ?>, 'Usuários Ativos', 'users')"
                 class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i :class="`fas fa-${icon} text-2xl`"></i>
                    </div>
                    <div class="flex items-center space-x-1 bg-white/20 px-3 py-1 rounded-full">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-3xl font-bold" x-text="formatNumber(value)"></h3>
                </div>
                <p class="text-sm text-white/80" x-text="title"></p>
            </div>

            <!-- Campanhas Total -->
            <div x-data="metricCard(<?= $total_campaigns ?? 0 ?>, <?= $prev_total_campaigns ?? 0 ?>, 'Total Campanhas', 'bullhorn')"
                 class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i :class="`fas fa-${icon} text-2xl`"></i>
                    </div>
                    <div class="flex items-center space-x-1 bg-white/20 px-3 py-1 rounded-full">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-3xl font-bold" x-text="formatNumber(value)"></h3>
                </div>
                <p class="text-sm text-white/80" x-text="title"></p>
            </div>

            <!-- Taxa Sucesso -->
            <div x-data="metricCard(<?= $success_rate ?? 0 ?>, <?= $prev_success_rate ?? 0 ?>, 'Taxa de Sucesso', 'trophy')"
                 class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-white/20 rounded-lg">
                        <i :class="`fas fa-${icon} text-2xl`"></i>
                    </div>
                    <div class="flex items-center space-x-1 bg-white/20 px-3 py-1 rounded-full">
                        <i :class="`fas ${trendIcon} text-sm`"></i>
                        <span class="text-sm font-semibold" x-text="Math.abs(changePercent).toFixed(1) + '%'"></span>
                    </div>
                </div>
                <div class="mb-1">
                    <h3 class="text-3xl font-bold"><span x-text="value.toFixed(1)"></span>%</h3>
                </div>
                <p class="text-sm text-white/80" x-text="title"></p>
            </div>
        </div>

        <!-- Gráfico Principal: Crescimento da Plataforma -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8"
             x-data="areaChart(
                 <?= json_encode($growth_labels ?? ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out']) ?>,
                 <?= json_encode($growth_data ?? [8500, 12000, 15500, 18900, 24500, 31200, 38900, 45600, 52300, 61800]) ?>,
                 'Volume Total (R$)'
             )"
             x-init="init()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Crescimento da Plataforma (Últimos 10 meses)</h3>
                <div class="flex space-x-2">
                    <button class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">7D</button>
                    <button class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">30D</button>
                    <button class="px-3 py-1 text-sm bg-primary-500 text-white rounded-lg">3M</button>
                    <button class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">1A</button>
                </div>
            </div>
            <div class="h-80">
                <canvas :id="chartId"></canvas>
            </div>
        </div>

        <!-- 3 Gráficos em Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Campanhas por Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                 x-data="donutChart(
                     <?= json_encode(['Ativas', 'Concluídas', 'Pausadas', 'Canceladas']) ?>,
                     <?= json_encode([342, 428, 89, 33]) ?>,
                     ['rgb(16, 185, 129)', 'rgb(59, 130, 246)', 'rgb(251, 146, 60)', 'rgb(239, 68, 68)']
                 )"
                 x-init="init()">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status das Campanhas</h3>
                <div class="h-56">
                    <canvas :id="chartId"></canvas>
                </div>
            </div>

            <!-- Top 5 Categorias -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                 x-data="barChart(
                     <?= json_encode(['Médica', 'Social', 'Educação', 'Negócio', 'Arte']) ?>,
                     <?= json_encode([45200, 31800, 24500, 18900, 12600]) ?>,
                     'Volume (R$)',
                     'rgb(139, 92, 246)'
                 )"
                 x-init="init()">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Categorias</h3>
                <div class="h-56">
                    <canvas :id="chartId"></canvas>
                </div>
            </div>

            <!-- Métodos de Pagamento -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Métodos de Pagamento</h3>
                <div class="space-y-4">
                    <!-- PIX -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">PIX</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">62%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 62%"></div>
                        </div>
                    </div>
                    <!-- Cartão -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Cartão</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">31%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 31%"></div>
                        </div>
                    </div>
                    <!-- Boleto -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700">Boleto</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">7%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-orange-500 h-2 rounded-full" style="width: 7%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela: Campanhas Recentes -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
             x-data="dataTable(
                 <?= json_encode($recent_campaigns ?? [
                     ['id' => 1, 'title' => 'Tratamento de Câncer - Maria', 'creator' => 'João Silva', 'category' => 'Médica', 'raised' => 45200, 'goal' => 50000, 'status' => 'Ativa'],
                     ['id' => 2, 'title' => 'Reconstrução de Escola', 'creator' => 'ONG Educar', 'category' => 'Social', 'raised' => 28900, 'goal' => 30000, 'status' => 'Ativa'],
                     ['id' => 3, 'title' => 'Bolsa de Estudos', 'creator' => 'Ana Costa', 'category' => 'Educação', 'raised' => 15000, 'goal' => 15000, 'status' => 'Concluída'],
                     ['id' => 4, 'title' => 'Startup Tech', 'creator' => 'Pedro Santos', 'category' => 'Negócio', 'raised' => 12300, 'goal' => 25000, 'status' => 'Ativa'],
                     ['id' => 5, 'title' => 'Filme Independente', 'creator' => 'Carlos Lima', 'category' => 'Arte', 'raised' => 8500, 'goal' => 20000, 'status' => 'Ativa']
                 ]) ?>,
                 [
                     { key: 'title', label: 'Campanha', sortable: true },
                     { key: 'creator', label: 'Criador', sortable: true },
                     { key: 'category', label: 'Categoria', sortable: true },
                     { key: 'raised', label: 'Arrecadado', sortable: true },
                     { key: 'goal', label: 'Meta', sortable: true },
                     { key: 'status', label: 'Status', sortable: false }
                 ]
             )">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Campanhas Recentes</h3>
                <div class="flex items-center space-x-4">
                    <input type="text"
                           x-model="search"
                           placeholder="Buscar campanhas..."
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="row in paginatedData" :key="row.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <div class="max-w-xs truncate" x-text="row.title"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="row.creator"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span x-text="row.category"
                                          :class="{
                                              'bg-red-100 text-red-800': row.category === 'Médica',
                                              'bg-blue-100 text-blue-800': row.category === 'Social',
                                              'bg-green-100 text-green-800': row.category === 'Educação',
                                              'bg-yellow-100 text-yellow-800': row.category === 'Negócio',
                                              'bg-purple-100 text-purple-800': row.category === 'Arte'
                                          }"
                                          class="px-2 py-1 text-xs font-semibold rounded-full"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                    R$ <span x-text="row.raised.toLocaleString('pt-BR')"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    R$ <span x-text="row.goal.toLocaleString('pt-BR')"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span x-text="row.status"
                                          :class="{
                                              'bg-green-100 text-green-800': row.status === 'Ativa',
                                              'bg-blue-100 text-blue-800': row.status === 'Concluída',
                                              'bg-gray-100 text-gray-800': row.status === 'Pausada'
                                          }"
                                          class="px-2 py-1 text-xs font-semibold rounded-full"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="text-primary-600 hover:text-primary-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
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

<?= $this->section('scripts') ?>
<script>
function areaChart(labels, data, label) {
    return {
        chartId: 'chart-' + Math.random().toString(36).substr(2, 9),
        chart: null,
        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById(this.chartId);
                if (ctx && typeof Chart !== 'undefined') {
                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: label,
                                data: data,
                                fill: true,
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderColor: 'rgb(16, 185, 129)',
                                borderWidth: 2,
                                tension: 0.4,
                                pointBackgroundColor: 'rgb(16, 185, 129)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'R$ ' + value.toLocaleString('pt-BR');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }
    };
}

function donutChart(labels, data, colors) {
    return {
        chartId: 'chart-' + Math.random().toString(36).substr(2, 9),
        chart: null,
        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById(this.chartId);
                if (ctx && typeof Chart !== 'undefined') {
                    this.chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { padding: 20 } }
                            },
                            cutout: '60%'
                        }
                    });
                }
            });
        }
    };
}

function barChart(labels, data, label, color) {
    return {
        chartId: 'chart-' + Math.random().toString(36).substr(2, 9),
        chart: null,
        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById(this.chartId);
                if (ctx && typeof Chart !== 'undefined') {
                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: label,
                                data: data,
                                backgroundColor: color,
                                borderRadius: 8,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'R$ ' + value.toLocaleString('pt-BR');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        }
    };
}

function metricCard(current, previous, label, icon) {
    return {
        value: current,
        title: label,
        icon: icon,
        changePercent: previous === 0 ? 0 : ((current - previous) / previous * 100),
        get trendIcon() {
            return this.changePercent >= 0 ? "fa-arrow-up" : "fa-arrow-down";
        },
        formatNumber(num) {
            return num.toLocaleString("pt-BR");
        }
    };
}
</script>
<?= $this->endSection() ?>
