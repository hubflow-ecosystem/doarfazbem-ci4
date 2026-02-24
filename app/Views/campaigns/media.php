<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gerenciar Midia</h1>
                    <p class="mt-2 text-gray-600"><?= esc($campaign['title']) ?></p>
                </div>
                <a href="<?= base_url('dashboard/my-campaigns') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <!-- Upload de Imagem -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-image text-blue-500 mr-2"></i>Adicionar Imagem
                </h3>
            </div>
            <form action="<?= base_url('campaigns/' . $campaign['id'] . '/media/add') ?>" method="POST" enctype="multipart/form-data" class="p-6">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="image">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecione uma imagem</label>
                    <input type="file" name="image" accept="image/*" required
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <p class="mt-1 text-xs text-gray-500">PNG, JPG ou GIF ate 5MB</p>
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-upload mr-2"></i>Upload Imagem
                </button>
            </form>
        </div>

        <!-- Adicionar Video -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-video text-red-500 mr-2"></i>Adicionar Video do YouTube
                </h3>
            </div>
            <form action="<?= base_url('campaigns/' . $campaign['id'] . '/media/add') ?>" method="POST" class="p-6">
                <?= csrf_field() ?>
                <input type="hidden" name="type" value="video">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL do Video</label>
                    <input type="url" name="video_url" required
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="mt-1 text-xs text-gray-500">Cole a URL completa do video do YouTube</p>
                </div>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fas fa-plus mr-2"></i>Adicionar Video
                </button>
            </form>
        </div>

        <!-- Galeria Atual -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-images text-primary-500 mr-2"></i>Galeria Atual
                </h3>
            </div>

            <?php if (!empty($media)): ?>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($media as $item): ?>
                            <div class="relative group">
                                <?php if ($item['type'] === 'video'): ?>
                                    <div class="aspect-video bg-gray-900 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-play-circle text-4xl text-white"></i>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= esc($item['url']) ?>"
                                         class="w-full h-32 object-cover rounded-lg">
                                <?php endif; ?>

                                <?php if ($item['is_primary']): ?>
                                    <span class="absolute top-2 left-2 bg-primary-500 text-white text-xs px-2 py-1 rounded">
                                        Principal
                                    </span>
                                <?php endif; ?>

                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                                    <form action="<?= base_url('campaigns/media/' . $item['id'] . '/delete') ?>" method="POST"
                                          onsubmit="return confirm('Remover esta midia?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="p-2 bg-red-600 text-white rounded-full hover:bg-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="mt-2 text-xs text-gray-500 text-center">
                                    <?= $item['type'] === 'video' ? 'Video' : 'Imagem' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <i class="fas fa-images text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">Nenhuma midia adicionada</p>
                    <p class="text-sm text-gray-400 mt-2">Adicione imagens e videos para tornar sua campanha mais atrativa!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Links para outras configuracoes -->
        <div class="mt-6 flex gap-4">
            <a href="<?= base_url('campaigns/' . $campaign['id'] . '/rewards') ?>"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-gift mr-2"></i>Gerenciar Recompensas
            </a>
            <a href="<?= base_url('campaigns/edit/' . $campaign['id']) ?>"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-edit mr-2"></i>Editar Campanha
            </a>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
