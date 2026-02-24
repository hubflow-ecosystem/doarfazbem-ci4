<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-white py-12">
    <div class="container-custom max-w-4xl">
        <div class="mb-8">
            <h1 class="text-heading-1 text-gray-900 mb-2">Editar Campanha</h1>
            <p class="text-gray-600">Atualize as informações da sua campanha</p>
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

        <form action="<?= base_url('campaigns/update/' . $campaign['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Título -->
            <div>
                <label for="title" class="form-label">Título da Campanha *</label>
                <input type="text" id="title" name="title" required
                       class="form-input" value="<?= old('title', $campaign['title']) ?>">
            </div>

            <!-- Descrição -->
            <div>
                <label for="description" class="form-label">Descrição Completa *</label>
                <textarea id="description" name="description" required rows="10"
                          class="form-input"><?= old('description', $campaign['description']) ?></textarea>
            </div>

            <!-- Data Final -->
            <div>
                <label for="end_date" class="form-label">Data Final *</label>
                <input type="date" id="end_date" name="end_date" required
                       class="form-input" value="<?= old('end_date', $campaign['end_date']) ?>">
            </div>

            <!-- Imagem Atual -->
            <?php if ($campaign['image']): ?>
                <div>
                    <label class="form-label">Imagem Atual</label>
                    <img src="<?= base_url('uploads/campaigns/' . $campaign['image']) ?>"
                         alt="Imagem atual" class="w-64 h-40 object-cover rounded-lg">
                </div>
            <?php endif; ?>

            <!-- Nova Imagem -->
            <div>
                <label for="image" class="form-label">Alterar Imagem (Opcional)</label>
                <input type="file" id="image" name="image" accept="image/*" class="form-input">
            </div>

            <!-- Vídeo -->
            <div>
                <label for="video_url" class="form-label">URL do Vídeo (Opcional)</label>
                <input type="url" id="video_url" name="video_url"
                       class="form-input" value="<?= old('video_url', $campaign['video_url']) ?>">
            </div>

            <!-- Botões -->
            <div class="flex gap-4">
                <button type="submit" class="btn-primary flex-1">
                    Salvar Alterações
                </button>
                <a href="<?= base_url('dashboard/my-campaigns') ?>" class="btn-outline flex-1 text-center">
                    Cancelar
                </a>
            </div>
        </form>

        <!-- Seção de Highlights (Por que apoiar?) -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4">
                <i class="fas fa-star text-yellow-500 mr-2"></i>Por que apoiar? (Destaques)
            </h2>
            <p class="text-gray-600 mb-4">Adicione até 4 motivos para convencer as pessoas a apoiarem sua campanha.</p>

            <?php
            $highlights = [];
            if (!empty($campaign['highlights'])) {
                $highlights = json_decode($campaign['highlights'], true) ?? [];
            }
            // Garantir que temos 4 slots
            while (count($highlights) < 4) {
                $highlights[] = ['title' => '', 'description' => '', 'icon' => 'fas fa-check'];
            }
            ?>

            <form action="<?= base_url('campaigns/' . $campaign['id'] . '/highlights') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <?php
            // Exemplos diferentes para cada destaque
            $exemplos = [
                [
                    'icone' => 'Coração',
                    'titulo' => 'Impacto Real',
                    'descricao' => 'Cada doação transforma vidas diretamente na comunidade'
                ],
                [
                    'icone' => 'Escudo',
                    'titulo' => 'Transparência Total',
                    'descricao' => 'Prestação de contas detalhada de cada centavo arrecadado'
                ],
                [
                    'icone' => 'Usuários',
                    'titulo' => 'Equipe Dedicada',
                    'descricao' => 'Voluntários comprometidos trabalhando pela causa'
                ],
                [
                    'icone' => 'Estrela',
                    'titulo' => 'Resultados Comprovados',
                    'descricao' => 'Mais de 500 famílias já foram beneficiadas'
                ]
            ];
            ?>

            <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="font-medium text-gray-700">Destaque <?= $i + 1 ?></span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Icone</label>
                                <select name="highlight_icon[]" class="form-input text-sm">
                                    <option value="fas fa-check" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-check' ? 'selected' : '' ?>>Check</option>
                                    <option value="fas fa-heart" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-heart' ? 'selected' : '' ?>>Coracao</option>
                                    <option value="fas fa-star" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-star' ? 'selected' : '' ?>>Estrela</option>
                                    <option value="fas fa-shield-alt" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-shield-alt' ? 'selected' : '' ?>>Escudo</option>
                                    <option value="fas fa-hand-holding-heart" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-hand-holding-heart' ? 'selected' : '' ?>>Mao com coracao</option>
                                    <option value="fas fa-users" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-users' ? 'selected' : '' ?>>Usuarios</option>
                                    <option value="fas fa-globe" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-globe' ? 'selected' : '' ?>>Globo</option>
                                    <option value="fas fa-lightbulb" <?= ($highlights[$i]['icon'] ?? '') === 'fas fa-lightbulb' ? 'selected' : '' ?>>Lampada</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Titulo</label>
                                <input type="text" name="highlight_title[]"
                                       value="<?= esc($highlights[$i]['title'] ?? '') ?>"
                                       placeholder="Ex: <?= $exemplos[$i]['titulo'] ?>"
                                       class="form-input text-sm">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Descricao</label>
                                <input type="text" name="highlight_description[]"
                                       value="<?= esc($highlights[$i]['description'] ?? '') ?>"
                                       placeholder="Ex: <?= $exemplos[$i]['descricao'] ?>"
                                       class="form-input text-sm">
                            </div>
                        </div>
                        <!-- Exemplo completo abaixo dos campos -->
                        <div class="mt-3 p-3 bg-white rounded border border-gray-200">
                            <p class="text-xs text-gray-500 mb-1"><i class="fas fa-lightbulb text-yellow-500 mr-1"></i>Exemplo:</p>
                            <div class="flex items-start gap-2 text-sm">
                                <span class="text-primary-600"><i class="fas fa-<?= strtolower(str_replace(['Coração', 'Escudo', 'Usuários', 'Estrela'], ['heart', 'shield-alt', 'users', 'star'], $exemplos[$i]['icone'])) ?>"></i></span>
                                <div>
                                    <span class="font-semibold text-gray-700"><?= $exemplos[$i]['titulo'] ?></span>
                                    <span class="text-gray-500"> - <?= $exemplos[$i]['descricao'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Salvar Destaques
                </button>
            </form>
        </div>

        <!-- Links rápidos para gerenciamento -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Gerenciamento Avancado</h2>
            <div class="flex flex-wrap gap-4">
                <a href="<?= base_url('campaigns/' . $campaign['id'] . '/rewards') ?>"
                   class="inline-flex items-center px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors">
                    <i class="fas fa-gift mr-2"></i>Gerenciar Recompensas
                </a>
                <a href="<?= base_url('campaigns/' . $campaign['id'] . '/media') ?>"
                   class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                    <i class="fas fa-images mr-2"></i>Gerenciar Midia
                </a>
                <a href="<?= base_url('campaigns/' . $campaign['id'] . '/donors') ?>"
                   class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                    <i class="fas fa-users mr-2"></i>Ver Doadores
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
