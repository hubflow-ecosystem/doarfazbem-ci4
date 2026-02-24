<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Sistema de Backup</h1>
                    <p class="mt-2 text-gray-600">Gerencie backups automáticos com Google Drive</p>
                </div>
                <a href="<?= base_url('admin/dashboard') ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
                    <p class="text-green-700"><?= session()->getFlashdata('success') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-400 mr-3 mt-0.5"></i>
                    <p class="text-red-700"><?= session()->getFlashdata('error') ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Status do Google Drive -->
            <div class="lg:col-span-3">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fab fa-google-drive mr-2 text-blue-500"></i>
                        Status do Google Drive
                    </h3>

                    <?php if ($googleDriveConnected): ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Conectado
                                </span>
                                <span class="ml-4 text-gray-500 text-sm">Backups serão enviados automaticamente para o Google Drive</span>
                            </div>
                            <form action="<?= base_url('admin/backup/disconnect-drive') ?>" method="POST" onsubmit="return confirm('Deseja desconectar o Google Drive?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-unlink mr-1"></i>
                                    Desconectar
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Não Conectado
                                </span>
                                <span class="ml-4 text-gray-500 text-sm">Configure o Google Drive para backups na nuvem</span>
                            </div>
                            <a href="<?= $authUrl ?? '#' ?>" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700" target="_blank">
                                <i class="fab fa-google mr-2"></i>
                                Conectar Google Drive
                            </a>
                        </div>

                        <?php if (!empty($authUrl)): ?>
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">
                                    <strong>Após autorizar:</strong> Copie o código e cole abaixo:
                                </p>
                                <form action="<?= base_url('admin/backup/auth-code') ?>" method="POST" class="flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="text" name="code" placeholder="Cole o código aqui..."
                                           class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                        <i class="fas fa-key mr-1"></i>
                                        Autorizar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                        Backup Manual
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <form action="<?= base_url('admin/backup/run') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="full">
                            <button type="submit" class="w-full flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-400 hover:bg-purple-50 transition-colors">
                                <i class="fas fa-database text-3xl text-purple-500 mb-2"></i>
                                <span class="font-medium text-gray-900">Backup Completo</span>
                                <span class="text-xs text-gray-500 mt-1">BD + Arquivos</span>
                            </button>
                        </form>

                        <form action="<?= base_url('admin/backup/run') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="database">
                            <button type="submit" class="w-full flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-colors">
                                <i class="fas fa-table text-3xl text-blue-500 mb-2"></i>
                                <span class="font-medium text-gray-900">Apenas Banco</span>
                                <span class="text-xs text-gray-500 mt-1">SQL Dump</span>
                            </button>
                        </form>

                        <form action="<?= base_url('admin/backup/run') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="files">
                            <button type="submit" class="w-full flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-400 hover:bg-green-50 transition-colors">
                                <i class="fas fa-folder text-3xl text-green-500 mb-2"></i>
                                <span class="font-medium text-gray-900">Apenas Arquivos</span>
                                <span class="text-xs text-gray-500 mt-1">Uploads + App</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Configurações Rápidas -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-cog mr-2 text-gray-500"></i>
                        Retenção
                    </h3>

                    <form action="<?= base_url('admin/backup/settings') ?>" method="POST" class="space-y-4">
                        <?= csrf_field() ?>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Backups Locais</label>
                            <select name="keep_local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($settings['keepLocalBackups'] ?? 3) == $i ? 'selected' : '' ?>>
                                        <?= $i ?> backup<?= $i > 1 ? 's' : '' ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Backups mais antigos serão removidos</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Backups Google Drive</label>
                            <select name="keep_remote" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <?php for ($i = 1; $i <= 30; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($settings['keepRemoteBackups'] ?? 7) == $i ? 'selected' : '' ?>>
                                        <?= $i ?> backup<?= $i > 1 ? 's' : '' ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                            <i class="fas fa-save mr-2"></i>
                            Salvar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de Backups Locais -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-hdd mr-2 text-gray-500"></i>
                            Backups Locais
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <?php if (empty($localBackups)): ?>
                            <div class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Nenhum backup local encontrado</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($localBackups as $backup): ?>
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                                    <div class="flex items-center">
                                        <?php
                                        $icon = 'fa-file-archive';
                                        $color = 'text-purple-500';
                                        if (strpos($backup['name'], '_db_') !== false) {
                                            $icon = 'fa-database';
                                            $color = 'text-blue-500';
                                        } elseif (strpos($backup['name'], '_files_') !== false) {
                                            $icon = 'fa-folder';
                                            $color = 'text-green-500';
                                        }
                                        ?>
                                        <i class="fas <?= $icon ?> <?= $color ?> text-xl mr-4"></i>
                                        <div>
                                            <p class="font-medium text-gray-900"><?= esc($backup['name']) ?></p>
                                            <p class="text-sm text-gray-500">
                                                <?= date('d/m/Y H:i', strtotime($backup['created_at'])) ?>
                                                &bull;
                                                <?= number_format($backup['size'] / 1024 / 1024, 2) ?> MB
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= base_url('admin/backup/download/' . urlencode($backup['name'])) ?>"
                                           class="p-2 text-gray-400 hover:text-blue-600" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if ($googleDriveConnected): ?>
                                            <form action="<?= base_url('admin/backup/upload-drive') ?>" method="POST" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="file" value="<?= esc($backup['name']) ?>">
                                                <button type="submit" class="p-2 text-gray-400 hover:text-green-600" title="Enviar para Google Drive">
                                                    <i class="fas fa-cloud-upload-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?= base_url('admin/backup/delete') ?>" method="POST" class="inline" onsubmit="return confirm('Deletar este backup?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="file" value="<?= esc($backup['name']) ?>">
                                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600" title="Deletar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lista de Backups no Google Drive -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fab fa-google-drive mr-2 text-blue-500"></i>
                            Google Drive
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        <?php if (!$googleDriveConnected): ?>
                            <div class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-cloud text-4xl mb-2"></i>
                                <p>Conecte o Google Drive</p>
                            </div>
                        <?php elseif (empty($remoteBackups)): ?>
                            <div class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-cloud text-4xl mb-2"></i>
                                <p>Nenhum backup na nuvem</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($remoteBackups as $backup): ?>
                                <div class="px-6 py-3 hover:bg-gray-50">
                                    <p class="font-medium text-gray-900 text-sm truncate" title="<?= esc($backup['name']) ?>">
                                        <?= esc($backup['name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?= date('d/m/Y H:i', strtotime($backup['createdTime'])) ?>
                                        &bull;
                                        <?= number_format(($backup['size'] ?? 0) / 1024 / 1024, 2) ?> MB
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Configurações Avançadas - Linha separada -->
            <div class="lg:col-span-3">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-sliders-h mr-2 text-gray-500"></i>
                            Configurações Avançadas
                        </h3>
                    </div>
                    <div class="p-6">
                        <form action="<?= base_url('admin/backup/settings') ?>" method="POST">
                            <?= csrf_field() ?>

                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-end">
                                <!-- Email de Notificação -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email para Notificações</label>
                                    <input type="email" name="notification_email" value="<?= esc($settings['notificationEmail'] ?? '') ?>"
                                           placeholder="admin@example.com"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>

                                <!-- Notificar apenas em erro -->
                                <div class="flex items-center h-10">
                                    <input type="checkbox" name="notify_error_only" id="notify_error_only"
                                           <?= ($settings['notifyOnErrorOnly'] ?? false) ? 'checked' : '' ?>
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label for="notify_error_only" class="ml-2 block text-sm text-gray-700">
                                        Notificar apenas em caso de erro
                                    </label>
                                </div>

                                <!-- Espaço vazio para alinhamento -->
                                <div class="lg:col-span-2"></div>
                            </div>

                            <!-- Pastas Incluídas - Linha separada -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Pastas Incluídas no Backup</label>
                                <div class="flex flex-wrap gap-3">
                                    <?php
                                    $allFolders = ['app', 'public/uploads', 'public/assets', 'writable/uploads', 'public/images', 'config'];
                                    $includedFolders = $settings['includeFolders'] ?? ['app', 'public/uploads', 'public/assets', 'writable/uploads'];
                                    ?>
                                    <?php foreach ($allFolders as $folder): ?>
                                        <label class="inline-flex items-center px-4 py-2 border rounded-lg hover:bg-gray-50 cursor-pointer bg-white">
                                            <input type="checkbox" name="include_folders[]" value="<?= $folder ?>"
                                                   <?= in_array($folder, $includedFolders) ? 'checked' : '' ?>
                                                   class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-700"><?= $folder ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                                    <i class="fas fa-save mr-2"></i>
                                    Salvar Configurações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>
