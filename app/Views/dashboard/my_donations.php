<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-heading-1 text-gray-900">Minhas Doações</h1>
                <p class="text-gray-600 mt-2">Histórico completo das suas contribuições</p>
            </div>
            <?php if (!empty($donations)): ?>
            <div class="bg-white px-6 py-3 rounded-xl shadow-sm border border-gray-200">
                <div class="text-sm text-gray-600">Total Doado</div>
                <div class="text-2xl font-bold text-primary-600">
                    R$ <?= number_format($total_donated ?? 0, 2, ',', '.') ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($donations)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="text-6xl mb-4 text-gray-300">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-2">Você ainda não fez nenhuma doação</h3>
                <p class="text-gray-600 mb-6">Explore as campanhas e faça a diferença na vida de alguém!</p>
                <a href="<?= base_url('campaigns') ?>" class="btn-primary inline-flex items-center">
                    <i class="fas fa-search mr-2"></i>Explorar Campanhas
                </a>
            </div>
        <?php else: ?>
            <!-- Donations Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                 x-data='dataTable(
                     <?= json_encode(array_map(function($d) {
                         return [
                             "id" => $d["id"],
                             "campaign_title" => $d["campaign_title"],
                             "campaign_slug" => $d["campaign_slug"],
                             "amount" => (float)$d["amount"],
                             "payment_method" => $d["payment_method"],
                             "status" => $d["status"],
                             "created_at" => $d["created_at"],
                             "message" => $d["message"] ?? "",
                             "is_anonymous" => $d["is_anonymous"] ?? false
                         ];
                     }, $donations)) ?>,
                     [
                         { key: "campaign_title", label: "Campanha", sortable: true },
                         { key: "amount", label: "Valor", sortable: true },
                         { key: "created_at", label: "Data", sortable: true },
                         { key: "status", label: "Status", sortable: false }
                     ]
                 )'>

                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Total: <span x-text="filteredData.length"></span> doação(ões)
                        </h3>
                        <div class="flex items-center space-x-4">
                            <input type="text"
                                   x-model="search"
                                   placeholder="Buscar doações..."
                                   class="form-input text-sm py-2">
                            <select x-model.number="perPage" class="form-input text-sm py-2">
                                <option value="10">10 por página</option>
                                <option value="20">20 por página</option>
                                <option value="50">50 por página</option>
                            </select>
                        </div>
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
                                    <!-- Campanha -->
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900" x-text="row.campaign_title"></div>
                                        <div class="text-sm text-gray-500">
                                            <span x-text="row.payment_method === 'pix' ? 'PIX' : (row.payment_method === 'credit_card' ? 'Cartão' : 'Boleto')"></span>
                                        </div>
                                    </td>

                                    <!-- Valor -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold text-green-600 text-lg">
                                            R$ <span x-text="row.amount.toFixed(2).replace('.', ',')"></span>
                                        </span>
                                    </td>

                                    <!-- Data -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span x-text="new Date(row.created_at).toLocaleDateString('pt-BR')"></span><br>
                                        <span class="text-xs text-gray-400" x-text="new Date(row.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})"></span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span x-text="row.status === 'paid' ? 'Confirmado' : (row.status === 'pending' ? 'Pendente' : row.status)"
                                              :class="{
                                                  'bg-green-100 text-green-800': row.status === 'paid',
                                                  'bg-yellow-100 text-yellow-800': row.status === 'pending',
                                                  'bg-blue-100 text-blue-800': row.status === 'received',
                                                  'bg-gray-100 text-gray-800': !['paid', 'pending', 'received'].includes(row.status)
                                              }"
                                              class="px-3 py-1 text-xs font-semibold rounded-full"></span>
                                    </td>

                                    <!-- Ações -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center space-x-3">
                                            <a :href="`<?= base_url('campaigns/') ?>${row.campaign_slug}`"
                                               class="text-primary-600 hover:text-primary-900 font-medium"
                                               title="Ver Campanha">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a :href="`<?= base_url('receipt/donation/') ?>${row.id}`"
                                               x-show="row.status === 'paid' || row.status === 'received'"
                                               class="text-gray-600 hover:text-gray-900 font-medium"
                                               title="Baixar Recibo"
                                               target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Mostrando <span x-text="(currentPage - 1) * perPage + 1"></span> a
                        <span x-text="Math.min(currentPage * perPage, filteredData.length)"></span> de
                        <span x-text="filteredData.length"></span> resultados
                    </p>
                    <div class="flex space-x-2">
                        <button @click="currentPage = Math.max(1, currentPage - 1)"
                                :disabled="currentPage === 1"
                                :class="currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                                Anterior
                        </button>
                        <button @click="currentPage = Math.min(totalPages, currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                :class="currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                                Próxima
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
