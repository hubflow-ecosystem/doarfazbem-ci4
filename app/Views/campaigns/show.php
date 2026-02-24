<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-white py-8" x-data="{
    activeTab: 'about',
    showQRCode: false
}">
    <div class="container-custom">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Coluna Principal -->
            <div class="lg:col-span-2">
                <!-- Galeria de Mídia -->
                <div class="mb-6">
                    <?php if (!empty($media)): ?>
                        <div class="relative">
                            <div class="relative overflow-hidden rounded-xl">
                                <?php $primaryMedia = $media[0] ?? null; ?>
                                <?php if ($primaryMedia): ?>
                                    <?php if ($primaryMedia['type'] === 'video'): ?>
                                        <div class="aspect-video">
                                            <iframe src="<?= esc($primaryMedia['url']) ?>"
                                                    class="w-full h-full"
                                                    frameborder="0"
                                                    allowfullscreen></iframe>
                                        </div>
                                    <?php else: ?>
                                        <img src="<?= esc($primaryMedia['url']) ?>"
                                             alt="<?= esc($campaign['title']) ?>"
                                             class="w-full h-96 object-cover"
                                             id="mainMedia">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if (count($media) > 1): ?>
                                <div class="flex gap-2 mt-3 overflow-x-auto pb-2">
                                    <?php foreach ($media as $index => $item): ?>
                                        <button onclick="changeMedia(<?= $index ?>, '<?= esc($item['url']) ?>')"
                                                class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all hover:border-primary-500 <?= $index === 0 ? 'border-primary-500' : 'border-gray-200' ?>"
                                                id="thumb-<?= $index ?>">
                                            <?php if ($item['type'] === 'video'): ?>
                                                <div class="w-full h-full bg-gray-900 flex items-center justify-center">
                                                    <i class="fas fa-play text-white"></i>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= esc($item['thumbnail'] ?? $item['url']) ?>" class="w-full h-full object-cover">
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($campaign['image']): ?>
                        <img src="<?= base_url('uploads/campaigns/' . $campaign['image']) ?>"
                             alt="<?= esc($campaign['title']) ?>"
                             class="w-full h-96 object-cover rounded-xl">
                    <?php else: ?>
                        <div class="w-full h-96 bg-gradient-to-br from-primary-400 to-secondary-400 rounded-xl flex items-center justify-center">
                            <i class="fas fa-heart text-9xl text-white"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Título e Categoria -->
                <div class="mb-6">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="inline-block bg-primary-100 text-primary-600 px-3 py-1 rounded-full text-sm font-semibold">
                            <?= esc(ucfirst($campaign['category'])) ?>
                        </span>
                        <?php if ($campaign['status'] === 'active'): ?>
                            <span class="inline-block bg-green-100 text-green-600 px-3 py-1 rounded-full text-sm font-semibold">
                                <i class="fas fa-circle text-xs mr-1"></i>Ativa
                            </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4"><?= esc($campaign['title']) ?></h1>
                    <div class="flex items-center gap-4 text-gray-600">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-secondary-400 rounded-full flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($creator['name'], 0, 1)) ?>
                            </div>
                            <span>Por <strong><?= esc($creator['name']) ?></strong></span>
                        </div>
                        <?php if (!empty($campaign['city'])): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?= esc($campaign['city']) ?>, <?= esc($campaign['state']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Abas -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex gap-6">
                        <button @click="activeTab = 'about'"
                                :class="activeTab === 'about' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500'"
                                class="py-4 px-1 border-b-2 font-medium transition-colors">
                            Sobre
                        </button>
                        <?php if (!empty($updates)): ?>
                            <button @click="activeTab = 'updates'"
                                    :class="activeTab === 'updates' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500'"
                                    class="py-4 px-1 border-b-2 font-medium transition-colors">
                                Atualizações <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= count($updates) ?></span>
                            </button>
                        <?php endif; ?>
                        <button @click="activeTab = 'supporters'"
                                :class="activeTab === 'supporters' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500'"
                                class="py-4 px-1 border-b-2 font-medium transition-colors">
                            Apoiadores <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full"><?= count($donations) ?></span>
                        </button>
                    </nav>
                </div>

                <!-- Conteúdo das Abas -->
                <div>
                    <!-- Tab: Sobre -->
                    <div x-show="activeTab === 'about'">
                        <?php if (!empty($highlights)): ?>
                            <div class="bg-gradient-to-r from-primary-50 to-secondary-50 rounded-xl p-6 mb-8">
                                <h3 class="text-xl font-bold text-gray-900 mb-4">
                                    <i class="fas fa-star text-yellow-500 mr-2"></i>Por que apoiar?
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($highlights as $highlight): ?>
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm">
                                                <i class="<?= esc($highlight['icon'] ?? 'fas fa-check') ?> text-primary-500"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900"><?= esc($highlight['title']) ?></h4>
                                                <p class="text-sm text-gray-600"><?= esc($highlight['description']) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="prose max-w-none mb-8">
                            <h2 class="text-xl font-bold mb-4">Sobre esta campanha</h2>
                            <div class="text-gray-700 leading-relaxed whitespace-pre-line">
                                <?= nl2br(esc($campaign['description'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Atualizações -->
                    <div x-show="activeTab === 'updates'">
                        <?php if (!empty($updates)): ?>
                            <div class="space-y-6">
                                <?php foreach ($updates as $update): ?>
                                    <div class="border-l-4 border-primary-500 pl-4">
                                        <div class="text-sm text-gray-500 mb-1"><?= date('d/m/Y H:i', strtotime($update['created_at'])) ?></div>
                                        <h4 class="font-semibold text-gray-900 mb-2"><?= esc($update['title']) ?></h4>
                                        <p class="text-gray-700"><?= nl2br(esc($update['content'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-newspaper text-4xl mb-3"></i>
                                <p>Nenhuma atualização publicada ainda.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab: Apoiadores -->
                    <div x-show="activeTab === 'supporters'">
                        <?php if (!empty($donations)): ?>
                            <div class="space-y-4">
                                <?php foreach ($donations as $donation): ?>
                                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg">
                                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-primary-600 font-bold">
                                                <?= strtoupper(substr($donation['is_anonymous'] ? 'A' : $donation['donor_name'], 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <div class="font-semibold text-gray-900">
                                                    <?= $donation['is_anonymous'] ? 'Apoiador Anonimo' : esc($donation['donor_name']) ?>
                                                </div>
                                                <div class="text-primary-600 font-bold">
                                                    R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($donation['created_at'])) ?></div>
                                            <?php if (!empty($donation['message']) && !$donation['is_anonymous']): ?>
                                                <p class="text-sm text-gray-600 mt-2 italic">"<?= esc($donation['message']) ?>"</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-users text-4xl mb-3"></i>
                                <p>Seja o primeiro a apoiar esta campanha!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="sticky top-20 space-y-6">
                    <!-- Card de Progresso -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="mb-6" x-data="{ percentage: 0, target: <?= min($campaign['percentage'], 100) ?> }" x-init="setTimeout(() => percentage = target, 100)">
                            <div class="text-4xl font-black text-primary-600 mb-1">
                                R$ <?= number_format($campaign['current_amount'] ?? 0, 2, ',', '.') ?>
                            </div>
                            <div class="text-gray-600 mb-4">
                                arrecadados de <strong>R$ <?= number_format($campaign['goal_amount'], 2, ',', '.') ?></strong>
                            </div>
                            <div class="relative w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-primary-500 to-secondary-500 h-4 rounded-full transition-all duration-1000 ease-out"
                                     :style="'width: ' + percentage + '%'"></div>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600"><strong x-text="percentage.toFixed(1) + '%'"></strong> da meta</span>
                                <span class="text-gray-500"><?= max(0, (int)$campaign['days_left']) ?> dias restantes</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b border-gray-200">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900"><?= $campaign['total_donations'] ?? 0 ?></div>
                                <div class="text-xs text-gray-600">Apoiadores</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900"><?= max(0, (int)$campaign['days_left']) ?></div>
                                <div class="text-xs text-gray-600">Dias restantes</div>
                            </div>
                        </div>

                        <?php if ($campaign['status'] === 'active'): ?>
                            <a href="<?= base_url('campaigns/' . $campaign['id'] . '/donate') ?>"
                               class="block w-full bg-gradient-to-r from-primary-500 to-secondary-500 hover:from-primary-600 hover:to-secondary-600 text-white font-bold text-xl py-5 px-6 rounded-xl text-center transition-all transform hover:scale-105 shadow-lg mb-4">
                                <i class="fas fa-heart mr-2 animate-pulse"></i>APOIAR AGORA
                            </a>
                        <?php else: ?>
                            <div class="bg-gray-100 text-gray-600 font-semibold text-center py-4 px-6 rounded-xl mb-4">
                                <i class="fas fa-pause-circle mr-2"></i>Campanha Encerrada
                            </div>
                        <?php endif; ?>

                        <!-- Compartilhamento -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-sm font-medium text-gray-700 mb-3">Compartilhar:</div>
                            <div class="grid grid-cols-5 gap-2">
                                <a href="https://api.whatsapp.com/send?text=<?= urlencode($campaign['title'] . ' - ' . current_url()) ?>" target="_blank"
                                   class="flex items-center justify-center w-full h-10 bg-green-500 text-white rounded-lg hover:bg-green-600" title="WhatsApp">
                                    <i class="fab fa-whatsapp text-lg"></i>
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= current_url() ?>" target="_blank"
                                   class="flex items-center justify-center w-full h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700" title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= current_url() ?>&text=<?= urlencode($campaign['title']) ?>" target="_blank"
                                   class="flex items-center justify-center w-full h-10 bg-sky-500 text-white rounded-lg hover:bg-sky-600" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <button onclick="navigator.clipboard.writeText('<?= current_url() ?>').then(() => alert('Link copiado!'))"
                                        class="flex items-center justify-center w-full h-10 bg-gray-600 text-white rounded-lg hover:bg-gray-700" title="Copiar Link">
                                    <i class="fas fa-link"></i>
                                </button>
                                <button @click="showQRCode = true"
                                        class="flex items-center justify-center w-full h-10 bg-purple-600 text-white rounded-lg hover:bg-purple-700" title="QR Code">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recompensas -->
                    <?php if (!empty($rewards)): ?>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">
                                <i class="fas fa-gift text-primary-500 mr-2"></i>Escolha sua recompensa
                            </h3>
                            <div class="space-y-4">
                                <?php foreach ($rewards as $reward): ?>
                                    <div class="border-2 rounded-lg p-4 transition-all hover:border-primary-500 <?= $reward['is_sold_out'] ? 'opacity-50' : '' ?>">
                                        <div class="flex justify-between items-start mb-2">
                                            <span class="text-lg font-bold text-primary-600">R$ <?= number_format($reward['min_amount'], 0, ',', '.') ?>+</span>
                                            <?php if ($reward['max_quantity']): ?>
                                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                                    <?= $reward['remaining'] ?>/<?= $reward['max_quantity'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="font-semibold text-gray-900 mb-1"><?= esc($reward['title']) ?></h4>
                                        <p class="text-sm text-gray-600 mb-3"><?= esc($reward['description']) ?></p>
                                        <?php if ($reward['delivery_date']): ?>
                                            <div class="text-xs text-gray-500 mb-2">
                                                <i class="fas fa-truck mr-1"></i>Entrega: <?= date('m/Y', strtotime($reward['delivery_date'])) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-users mr-1"></i><?= $reward['claimed_quantity'] ?> apoiadores
                                        </div>
                                        <?php if (!$reward['is_sold_out'] && $campaign['status'] === 'active'): ?>
                                            <a href="<?= base_url('campaigns/' . $campaign['id'] . '/donate?reward=' . $reward['id']) ?>"
                                               class="block mt-3 text-center py-2 bg-primary-100 text-primary-600 rounded-lg font-medium hover:bg-primary-200">
                                                Selecionar
                                            </a>
                                        <?php elseif ($reward['is_sold_out']): ?>
                                            <div class="mt-3 text-center py-2 bg-gray-100 text-gray-500 rounded-lg">Esgotado</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div x-show="showQRCode" @click.self="showQRCode = false" x-transition
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl p-6 max-w-sm w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">QR Code da Campanha</h3>
            <button @click="showQRCode = false" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="flex justify-center mb-4">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode(current_url()) ?>" alt="QR Code" class="rounded-lg">
        </div>
        <p class="text-sm text-gray-500 text-center">Escaneie para acessar esta campanha</p>
    </div>
</div>

<script>
function changeMedia(index, url) {
    const mainMedia = document.getElementById('mainMedia');
    if (mainMedia) mainMedia.src = url;
    document.querySelectorAll('[id^="thumb-"]').forEach((thumb, i) => {
        thumb.classList.toggle('border-primary-500', i === index);
        thumb.classList.toggle('border-gray-200', i !== index);
    });
}
</script>

<?= $this->endSection() ?>
