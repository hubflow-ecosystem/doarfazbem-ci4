<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div x-data='campaignFilter(<?= json_encode($campaigns) ?>)'>
<!-- Breadcrumb e Filtros -->
<section class="bg-white border-b">
    <div class="container-custom py-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-heading-1 text-gray-900">Campanhas Ativas</h1>
                <p class="text-gray-600 mt-2">Encontre uma causa para apoiar</p>
            </div>

            <a href="<?= base_url('campaigns/create') ?>" class="btn-primary">
                + Criar Campanha
            </a>
        </div>

        <!-- Filtros e Busca -->
        <div class="mt-6 space-y-4">
            <!-- Busca -->
            <div class="relative max-w-md">
                <input type="text"
                       x-model="search"
                       placeholder="Buscar campanhas..."
                       class="form-input pl-10">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>

            <!-- Filtros de Categoria -->
            <div class="flex flex-wrap gap-2">
                <button @click="category = 'all'"
                        :class="category === 'all' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    Todas
                </button>
                <button @click="category = 'medica'"
                        :class="category === 'medica' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-heartbeat text-red-400"></i> Médicas
                </button>
                <button @click="category = 'social'"
                        :class="category === 'social' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-hands-helping text-blue-400"></i> Sociais
                </button>
                <button @click="category = 'criativa'"
                        :class="category === 'criativa' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-palette text-purple-400"></i> Criativas
                </button>
                <button @click="category = 'negocio'"
                        :class="category === 'negocio' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-briefcase text-yellow-400"></i> Negócios
                </button>
                <button @click="category = 'educacao'"
                        :class="category === 'educacao' ? 'bg-primary-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-graduation-cap text-green-400"></i> Educação
                </button>
            </div>

            <!-- Ordenação -->
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Ordenar por:</label>
                <select x-model="sortBy" class="form-input text-sm">
                    <option value="recent">Mais Recentes</option>
                    <option value="progress">Maior Progresso</option>
                    <option value="amount">Maior Arrecadação</option>
                    <option value="urgent">Urgentes Primeiro</option>
                </select>
            </div>

            <!-- Contador de Resultados -->
            <div class="text-sm text-gray-600">
                Mostrando <span class="font-semibold" x-text="campaignCount"></span> campanha(s)
            </div>
        </div>
    </div>
</section>

<!-- Grid de Campanhas -->
<section class="py-12">
    <div class="container-custom">
        <!-- Empty State -->
        <div x-show="filteredCampaigns.length === 0" class="text-center py-16">
            <div class="text-6xl mb-4">
                <i class="fas fa-search text-gray-300"></i>
            </div>
            <h3 class="text-2xl font-semibold text-gray-700 mb-2">Nenhuma campanha encontrada</h3>
            <p class="text-gray-600 mb-6">Tente ajustar os filtros ou crie uma nova campanha</p>
            <a href="<?= base_url('campaigns/create') ?>" class="btn-primary">
                Criar Campanha
            </a>
        </div>

        <!-- Campaigns Grid -->
        <div x-show="filteredCampaigns.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <template x-for="campaign in filteredCampaigns" :key="campaign.id">
                <div class="bg-white rounded-xl shadow-card hover:shadow-card-hover transition-shadow overflow-hidden">
                    <!-- Imagem -->
                    <div class="relative h-48 bg-gray-200">
                        <template x-if="campaign.image">
                            <img :src="`<?= base_url('uploads/campaigns/') ?>${campaign.image}`"
                                 :alt="campaign.title"
                                 class="w-full h-full object-cover">
                        </template>
                        <template x-if="!campaign.image">
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-400 to-secondary-400">
                                <i class="fas fa-heart text-6xl text-white"></i>
                            </div>
                        </template>

                        <!-- Badge Urgente -->
                        <div x-show="campaign.is_urgent"
                             class="absolute top-3 right-3 bg-urgent-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-bolt"></i> Urgente
                        </div>
                    </div>

                    <!-- Conteúdo -->
                    <div class="p-6">
                        <!-- Categoria -->
                        <div class="mb-3">
                            <span class="text-xs font-semibold text-primary-600 uppercase" x-text="campaign.category"></span>
                        </div>

                        <!-- Título -->
                        <h3 class="text-xl font-semibold text-gray-900 mb-3 line-clamp-2" x-text="campaign.title"></h3>

                        <!-- Barra de Progresso -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span class="font-semibold text-primary-600" x-text="formatCurrency(campaign.current_amount || 0)"></span>
                                <span class="text-gray-500" x-text="`Meta: ${formatCurrency(campaign.goal_amount)}`"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary-500 h-2 rounded-full transition-all"
                                     :style="`width: ${Math.min(campaign.percentage || 0, 100)}%`"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1" x-text="`${(campaign.percentage || 0).toFixed(1)}% arrecadado`"></div>
                        </div>

                        <!-- Estatísticas -->
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-4">
                            <span><i class="fas fa-users"></i> <span x-text="campaign.donors_count || 0"></span> doadores</span>
                            <span><i class="fas fa-eye"></i> <span x-text="campaign.views_count || 0"></span> visualizações</span>
                        </div>

                        <!-- Botão -->
                        <a :href="`<?= base_url('campaigns/') ?>${campaign.slug}`"
                           class="btn-primary w-full text-center block">
                            Ver Campanha
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
</div>

<?= $this->endSection() ?>
