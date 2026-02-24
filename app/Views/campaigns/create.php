<?= $this->extend('layout/app') ?>

<?php helper('recaptcha'); ?>

<?= $this->section('content') ?>

<div class="bg-white py-12">
    <div class="container-custom max-w-4xl">
        <div class="mb-8">
            <h1 class="text-heading-1 text-gray-900 mb-2">Criar Nova Campanha</h1>
            <p class="text-gray-600">Preencha os dados abaixo para criar sua campanha de crowdfunding</p>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert-error mb-6">
                <ul class="list-disc list-inside">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('campaigns/create') ?>" method="POST" enctype="multipart/form-data" class="space-y-6" id="campaign-form">
            <?= csrf_field() ?>
            <?= recaptcha_field() ?>

            <!-- Título -->
            <div>
                <label for="title" class="form-label">Título da Campanha *</label>
                <input type="text" id="title" name="title" required
                       class="form-input" placeholder="Ex: Ajude Maria a realizar a cirurgia"
                       value="<?= old('title') ?>">
                <p class="text-sm text-gray-500 mt-1">Mínimo 10 caracteres</p>
            </div>

            <!-- Grid: Categoria e Tipo -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Categoria -->
                <div>
                    <label for="category" class="form-label">Categoria *</label>
                    <select id="category" name="category" required class="form-input">
                        <option value="">Selecione uma categoria</option>
                        <option value="medica" <?= old('category') === 'medica' ? 'selected' : '' ?>>Médica (0% de taxa)</option>
                        <option value="social" <?= old('category') === 'social' ? 'selected' : '' ?>>Social (2% de taxa)</option>
                        <option value="criativa" <?= old('category') === 'criativa' ? 'selected' : '' ?>>Criativa (2% de taxa)</option>
                        <option value="negocio" <?= old('category') === 'negocio' ? 'selected' : '' ?>>Negócio (2% de taxa)</option>
                        <option value="educacao" <?= old('category') === 'educacao' ? 'selected' : '' ?>>Educação (2% de taxa)</option>
                    </select>
                </div>

                <!-- Tipo de Campanha -->
                <div>
                    <label for="campaign_type" class="form-label">Tipo de Campanha *</label>
                    <select id="campaign_type" name="campaign_type" required class="form-input">
                        <option value="flexivel" <?= old('campaign_type') === 'flexivel' ? 'selected' : '' ?>>Flexível (recomendado)</option>
                        <option value="tudo_ou_tudo" <?= old('campaign_type') === 'tudo_ou_tudo' ? 'selected' : '' ?>>Tudo ou Tudo</option>
                        <option value="recorrente" <?= old('campaign_type') === 'recorrente' ? 'selected' : '' ?>>Doações Recorrentes</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="type-help-flexivel" class="type-help">Você recebe qualquer valor arrecadado</span>
                        <span id="type-help-tudo-ou-tudo" class="type-help hidden">Se não atingir meta, valores são redistribuídos</span>
                        <span id="type-help-recorrente" class="type-help hidden">Doadores contribuem mensalmente</span>
                    </p>
                    <p id="medical-warning" class="text-xs text-red-600 mt-2 hidden">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Campanhas médicas não podem usar "Tudo ou Tudo"
                    </p>
                </div>
            </div>

            <!-- Explicação dos Tipos -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-6">
                <h3 class="font-bold text-gray-900 mb-3 flex items-center text-lg">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Entenda os Tipos de Campanha
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-green-500 mt-1"></i>
                        <div>
                            <strong class="text-gray-900">Flexível (Recomendado):</strong>
                            <span class="text-gray-700">Você recebe todo o valor arrecadado, mesmo que não atinja a meta. Ideal para a maioria das campanhas.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-target text-orange-500 mt-1"></i>
                        <div>
                            <strong class="text-gray-900">Tudo ou Tudo:</strong>
                            <span class="text-gray-700">Se não atingir 100% da meta, os valores são redistribuídos: 2% para plataforma, 48% para Central Geral do Dízimo Pró-Vida, 50% para campanha médica escolhida pelo doador. <strong class="text-red-600">Não disponível para campanhas médicas.</strong></span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-sync text-purple-500 mt-1"></i>
                        <div>
                            <strong class="text-gray-900">Doações Recorrentes:</strong>
                            <span class="text-gray-700">Doadores contribuem mensalmente com valor fixo. Ideal para projetos de longo prazo e causas contínuas.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <div>
                <label for="description" class="form-label">Descrição Completa *</label>
                <textarea id="description" name="description" required rows="10"
                          class="form-input" placeholder="Conte sua história de forma detalhada..."><?= old('description') ?></textarea>
                <p class="text-sm text-gray-500 mt-1">Mínimo 50 caracteres. Seja detalhado e transparente.</p>
            </div>

            <!-- Grid: Meta e Data -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Meta -->
                <div>
                    <label for="goal_amount" class="form-label">Meta de Arrecadação (R$) *</label>
                    <input type="number" id="goal_amount" name="goal_amount" required min="1" step="0.01"
                           class="form-input" placeholder="1000.00"
                           value="<?= old('goal_amount') ?>">
                </div>

                <!-- Data Final -->
                <div>
                    <label for="end_date" class="form-label">Data Final *</label>
                    <input type="date" id="end_date" name="end_date" required
                           class="form-input" min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           value="<?= old('end_date') ?>">
                </div>
            </div>

            <!-- Grid: Localização -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center text-lg">
                    <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                    Localização (Opcional)
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                    Informar a localização ajuda pessoas da sua região a encontrar sua campanha no mapa
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Cidade -->
                    <div>
                        <label for="city" class="form-label">Cidade</label>
                        <input type="text" id="city" name="city"
                               class="form-input" placeholder="Ex: São Paulo"
                               value="<?= old('city') ?>">
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="state" class="form-label">Estado (UF)</label>
                        <select id="state" name="state" class="form-input">
                            <option value="">Selecione o estado</option>
                            <option value="AC" <?= old('state') === 'AC' ? 'selected' : '' ?>>Acre</option>
                            <option value="AL" <?= old('state') === 'AL' ? 'selected' : '' ?>>Alagoas</option>
                            <option value="AP" <?= old('state') === 'AP' ? 'selected' : '' ?>>Amapá</option>
                            <option value="AM" <?= old('state') === 'AM' ? 'selected' : '' ?>>Amazonas</option>
                            <option value="BA" <?= old('state') === 'BA' ? 'selected' : '' ?>>Bahia</option>
                            <option value="CE" <?= old('state') === 'CE' ? 'selected' : '' ?>>Ceará</option>
                            <option value="DF" <?= old('state') === 'DF' ? 'selected' : '' ?>>Distrito Federal</option>
                            <option value="ES" <?= old('state') === 'ES' ? 'selected' : '' ?>>Espírito Santo</option>
                            <option value="GO" <?= old('state') === 'GO' ? 'selected' : '' ?>>Goiás</option>
                            <option value="MA" <?= old('state') === 'MA' ? 'selected' : '' ?>>Maranhão</option>
                            <option value="MT" <?= old('state') === 'MT' ? 'selected' : '' ?>>Mato Grosso</option>
                            <option value="MS" <?= old('state') === 'MS' ? 'selected' : '' ?>>Mato Grosso do Sul</option>
                            <option value="MG" <?= old('state') === 'MG' ? 'selected' : '' ?>>Minas Gerais</option>
                            <option value="PA" <?= old('state') === 'PA' ? 'selected' : '' ?>>Pará</option>
                            <option value="PB" <?= old('state') === 'PB' ? 'selected' : '' ?>>Paraíba</option>
                            <option value="PR" <?= old('state') === 'PR' ? 'selected' : '' ?>>Paraná</option>
                            <option value="PE" <?= old('state') === 'PE' ? 'selected' : '' ?>>Pernambuco</option>
                            <option value="PI" <?= old('state') === 'PI' ? 'selected' : '' ?>>Piauí</option>
                            <option value="RJ" <?= old('state') === 'RJ' ? 'selected' : '' ?>>Rio de Janeiro</option>
                            <option value="RN" <?= old('state') === 'RN' ? 'selected' : '' ?>>Rio Grande do Norte</option>
                            <option value="RS" <?= old('state') === 'RS' ? 'selected' : '' ?>>Rio Grande do Sul</option>
                            <option value="RO" <?= old('state') === 'RO' ? 'selected' : '' ?>>Rondônia</option>
                            <option value="RR" <?= old('state') === 'RR' ? 'selected' : '' ?>>Roraima</option>
                            <option value="SC" <?= old('state') === 'SC' ? 'selected' : '' ?>>Santa Catarina</option>
                            <option value="SP" <?= old('state') === 'SP' ? 'selected' : '' ?>>São Paulo</option>
                            <option value="SE" <?= old('state') === 'SE' ? 'selected' : '' ?>>Sergipe</option>
                            <option value="TO" <?= old('state') === 'TO' ? 'selected' : '' ?>>Tocantins</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Imagem -->
            <div>
                <label for="image" class="form-label">Imagem Principal *</label>
                <input type="file" id="image" name="image" required accept="image/*"
                       class="form-input">
                <p class="text-sm text-gray-500 mt-1">Tamanho máximo: 2MB. Formatos: JPG, PNG</p>
            </div>

            <!-- Vídeo (Opcional) -->
            <div>
                <label for="video_url" class="form-label">URL do Vídeo (Opcional)</label>
                <input type="url" id="video_url" name="video_url"
                       class="form-input" placeholder="https://youtube.com/watch?v=..."
                       value="<?= old('video_url') ?>">
                <p class="text-sm text-gray-500 mt-1">URL do YouTube ou Vimeo</p>
            </div>

            <!-- Aviso -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Atenção:</strong> Sua campanha passará por análise antes de ser publicada.
                            Isso geralmente leva até 24 horas. Certifique-se de fornecer informações verdadeiras e detalhadas.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-4">
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-rocket mr-2"></i>Criar Campanha
                </button>
                <a href="<?= base_url('dashboard/my-campaigns') ?>" class="btn-outline flex-1 text-center">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- reCAPTCHA v3 -->
<?= recaptcha_script() ?>
<?= recaptcha_execute('campaign-form', 'create_campaign') ?>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const campaignTypeSelect = document.getElementById('campaign_type');
    const categorySelect = document.getElementById('category');
    const medicalWarning = document.getElementById('medical-warning');
    const tudoOuTudoOption = campaignTypeSelect.querySelector('option[value="tudo_ou_tudo"]');

    // Função para verificar se pode usar Tudo ou Tudo
    function checkTudoOuTudoAvailability() {
        const isMedical = categorySelect.value === 'medica';

        if (isMedical) {
            // Desabilita opção
            tudoOuTudoOption.disabled = true;
            medicalWarning.classList.remove('hidden');

            // Se estava selecionado, muda para flexível
            if (campaignTypeSelect.value === 'tudo_ou_tudo') {
                campaignTypeSelect.value = 'flexivel';
                updateTypeHelp('flexivel');
            }
        } else {
            tudoOuTudoOption.disabled = false;
            medicalWarning.classList.add('hidden');
        }
    }

    // Atualizar texto de ajuda do tipo de campanha
    function updateTypeHelp(type) {
        document.querySelectorAll('.type-help').forEach(el => el.classList.add('hidden'));
        const helpText = document.getElementById('type-help-' + type.replace('_', '-'));
        if (helpText) {
            helpText.classList.remove('hidden');
        }
    }

    campaignTypeSelect.addEventListener('change', function() {
        updateTypeHelp(this.value);
    });

    categorySelect.addEventListener('change', function() {
        checkTudoOuTudoAvailability();
    });

    // Verifica ao carregar
    checkTudoOuTudoAvailability();
});
</script>

<?= $this->endSection() ?>
