<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-100 py-8">
    <div class="container-custom max-w-3xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= base_url('admin/raffles') ?>" class="text-gray-600 hover:text-gray-900 mb-2 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Nova Rifa</h1>
            <p class="text-gray-600">Configure os detalhes da nova rifa "Numeros da Sorte"</p>
        </div>

        <!-- Alertas -->
        <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <?= session()->getFlashdata('error') ?>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form action="<?= base_url('admin/raffles/create') ?>" method="post" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Informacoes Basicas -->
            <div class="bg-white rounded-xl shadow-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-teal-500 mr-2"></i>
                    Informacoes Basicas
                </h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titulo da Rifa *</label>
                        <input type="text" name="title" value="<?= old('title', 'Numeros da Sorte - Dezembro 2024') ?>"
                               class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descricao</label>
                        <textarea name="description" rows="3" class="form-input"><?= old('description', 'Concorra a premios incriveis e ajude campanhas sociais!') ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade de Numeros *</label>
                            <input type="number" name="total_numbers" value="<?= old('total_numbers', '100000') ?>"
                                   min="1000" max="1000000" class="form-input" required>
                            <p class="text-xs text-gray-500 mt-1">De 000001 ate o numero maximo</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preco por Cota (R$) *</label>
                            <input type="number" name="number_price" value="<?= old('number_price', '1.10') ?>"
                                   min="0.10" step="0.01" class="form-input" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data da Loteria Federal</label>
                        <input type="date" name="federal_lottery_date"
                               value="<?= old('federal_lottery_date', '') ?>"
                               class="form-input">
                        <p class="text-xs text-gray-500 mt-1">Opcional - Defina quando todas as cotas forem vendidas ou quando decidir realizar o sorteio</p>
                    </div>
                </div>
            </div>

            <!-- Distribuicao de Valores -->
            <div class="bg-white rounded-xl shadow-card p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-pie text-green-500 mr-2"></i>
                    Distribuicao de Valores (deve somar 100%)
                </h2>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-trophy text-yellow-500 mr-1"></i>
                            Premio Principal (%)
                        </label>
                        <input type="number" name="main_prize_percentage" value="<?= old('main_prize_percentage', '40') ?>"
                               min="0" max="100" step="1" class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-hand-holding-heart text-red-500 mr-1"></i>
                            Campanhas (%)
                        </label>
                        <input type="number" name="campaign_percentage" value="<?= old('campaign_percentage', '40') ?>"
                               min="0" max="100" step="1" class="form-input" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-building text-blue-500 mr-1"></i>
                            Plataforma (%)
                        </label>
                        <input type="number" name="platform_percentage" value="<?= old('platform_percentage', '20') ?>"
                               min="0" max="100" step="1" class="form-input" required>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Exemplo com 100.000 numeros vendidos a R$ 1,10:</strong><br>
                        Total: R$ 110.000 | Premio: R$ 44.000 | Campanhas: R$ 44.000 | Plataforma: R$ 22.000
                    </p>
                </div>
            </div>

            <!-- Botoes -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 btn-primary py-4 text-lg">
                    <i class="fas fa-check mr-2"></i>
                    Criar Rifa e Gerar Numeros
                </button>
                <a href="<?= base_url('admin/raffles') ?>" class="btn-outline py-4">
                    Cancelar
                </a>
            </div>

            <p class="text-sm text-gray-500 text-center">
                <i class="fas fa-info-circle mr-1"></i>
                Apos criar, configure os pacotes de desconto e premios especiais.
            </p>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
