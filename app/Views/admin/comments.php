<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom">
        <!-- Menu de Navegação Admin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8">
            <nav class="flex flex-wrap gap-2">
                <a href="<?= base_url('admin/dashboard') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="<?= base_url('admin/campaigns?status=pending') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-bullhorn mr-2"></i>Campanhas
                </a>
                <a href="<?= base_url('admin/users') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-users mr-2"></i>Usuarios
                </a>
                <a href="<?= base_url('admin/comments') ?>"
                   class="px-4 py-2 rounded-lg bg-primary-500 text-white font-medium">
                    <i class="fas fa-comments mr-2"></i>Comentarios
                </a>
                <a href="<?= base_url('admin/raffles') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-ticket-alt mr-2"></i>Rifas
                </a>
                <a href="<?= base_url('admin/settings') ?>"
                   class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 font-medium">
                    <i class="fas fa-cog mr-2"></i>Configuracoes
                </a>
            </nav>
        </div>

        <h1 class="text-heading-1 mb-8"><?= esc($title) ?></h1>

        <!-- Estatisticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="<?= base_url('admin/comments?status=pending') ?>"
               class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition <?= $current_status === 'pending' ? 'ring-2 ring-yellow-500' : '' ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pendentes</p>
                        <p class="text-3xl font-bold text-yellow-600"><?= number_format($stats['pending']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </a>
            <a href="<?= base_url('admin/comments?status=approved') ?>"
               class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition <?= $current_status === 'approved' ? 'ring-2 ring-green-500' : '' ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Aprovados</p>
                        <p class="text-3xl font-bold text-green-600"><?= number_format($stats['approved']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </a>
            <a href="<?= base_url('admin/comments?status=rejected') ?>"
               class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition <?= $current_status === 'rejected' ? 'ring-2 ring-red-500' : '' ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Rejeitados</p>
                        <p class="text-3xl font-bold text-red-600"><?= number_format($stats['rejected']) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-red-600 text-xl"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Filtros de Status -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="<?= base_url('admin/comments?status=pending') ?>"
               class="px-4 py-2 rounded-lg <?= $current_status === 'pending' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' ?>">
                Pendentes
            </a>
            <a href="<?= base_url('admin/comments?status=approved') ?>"
               class="px-4 py-2 rounded-lg <?= $current_status === 'approved' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' ?>">
                Aprovados
            </a>
            <a href="<?= base_url('admin/comments?status=rejected') ?>"
               class="px-4 py-2 rounded-lg <?= $current_status === 'rejected' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' ?>">
                Rejeitados
            </a>
            <a href="<?= base_url('admin/comments?status=all') ?>"
               class="px-4 py-2 rounded-lg <?= $current_status === 'all' ? 'bg-primary-500 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' ?>">
                Todos
            </a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <span class="text-green-700"><?= session()->getFlashdata('success') ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <span class="text-red-700"><?= session()->getFlashdata('error') ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($comments)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="text-5xl text-gray-300 mb-4">
                <i class="fas fa-comments"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-600 mb-2">Nenhum comentario encontrado</h3>
            <p class="text-gray-500">
                <?php if ($current_status === 'pending'): ?>
                    Nao ha comentarios pendentes de moderacao.
                <?php elseif ($current_status === 'approved'): ?>
                    Nenhum comentario foi aprovado ainda.
                <?php elseif ($current_status === 'rejected'): ?>
                    Nenhum comentario foi rejeitado.
                <?php else: ?>
                    Nao ha comentarios no sistema.
                <?php endif; ?>
            </p>
        </div>
        <?php else: ?>

        <!-- Acoes em massa (apenas para pendentes) -->
        <?php if ($current_status === 'pending' && count($comments) > 0): ?>
        <form id="bulkForm" method="POST" class="mb-4">
            <?= csrf_field() ?>
            <div class="flex gap-2">
                <button type="submit" formaction="<?= base_url('admin/comments/bulk-approve') ?>"
                        class="btn-primary text-sm" onclick="return confirmBulkAction('aprovar')">
                    <i class="fas fa-check-double mr-1"></i>Aprovar Selecionados
                </button>
                <button type="submit" formaction="<?= base_url('admin/comments/bulk-reject') ?>"
                        class="btn-outline text-sm text-red-600 border-red-300 hover:bg-red-50" onclick="return confirmBulkAction('rejeitar')">
                    <i class="fas fa-times mr-1"></i>Rejeitar Selecionados
                </button>
            </div>
        <?php endif; ?>

        <!-- Lista de Comentarios -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="divide-y divide-gray-200">
                <?php foreach ($comments as $comment): ?>
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex items-start gap-4">
                        <!-- Checkbox para acoes em massa -->
                        <?php if ($current_status === 'pending'): ?>
                        <div class="pt-1">
                            <input type="checkbox" name="comment_ids[]" value="<?= $comment['id'] ?>"
                                   form="bulkForm" class="w-4 h-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500">
                        </div>
                        <?php endif; ?>

                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                        </div>

                        <!-- Conteudo -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium text-gray-900">
                                    <?php if ($comment['is_anonymous']): ?>
                                        Doador Anonimo
                                    <?php elseif (!empty($comment['user_name'])): ?>
                                        <?= esc($comment['user_name']) ?>
                                    <?php elseif (!empty($comment['donor_name'])): ?>
                                        <?= esc($comment['donor_name']) ?>
                                    <?php else: ?>
                                        Apoiador
                                    <?php endif; ?>
                                </span>

                                <!-- Status Badge -->
                                <?php
                                $statusClass = match($comment['status']) {
                                    'approved' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $statusLabel = match($comment['status']) {
                                    'approved' => 'Aprovado',
                                    'pending' => 'Pendente',
                                    'rejected' => 'Rejeitado',
                                    default => $comment['status']
                                };
                                ?>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full <?= $statusClass ?>">
                                    <?= $statusLabel ?>
                                </span>

                                <span class="text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                </span>
                            </div>

                            <!-- Link para campanha -->
                            <a href="<?= base_url('campaigns/' . $comment['campaign_slug']) ?>" target="_blank"
                               class="text-sm text-primary-600 hover:underline mb-2 inline-block">
                                <i class="fas fa-link mr-1"></i><?= esc($comment['campaign_title']) ?>
                            </a>

                            <!-- Texto do comentario -->
                            <p class="text-gray-700 bg-gray-50 rounded-lg p-3 mt-2">
                                <?= nl2br(esc($comment['comment'])) ?>
                            </p>

                            <!-- Email do autor (se disponivel) -->
                            <?php if (!empty($comment['donor_email'])): ?>
                            <p class="text-xs text-gray-400 mt-2">
                                <i class="fas fa-envelope mr-1"></i><?= esc($comment['donor_email']) ?>
                            </p>
                            <?php endif; ?>
                        </div>

                        <!-- Acoes -->
                        <div class="flex-shrink-0 flex gap-2">
                            <?php if ($comment['status'] === 'pending'): ?>
                            <form action="<?= base_url('admin/comments/approve/' . $comment['id']) ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Aprovar">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form action="<?= base_url('admin/comments/reject/' . $comment['id']) ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Rejeitar">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php elseif ($comment['status'] === 'approved'): ?>
                            <form action="<?= base_url('admin/comments/reject/' . $comment['id']) ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition" title="Revogar aprovacao">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                            <?php elseif ($comment['status'] === 'rejected'): ?>
                            <form action="<?= base_url('admin/comments/approve/' . $comment['id']) ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Aprovar">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <form action="<?= base_url('admin/comments/delete/' . $comment['id']) ?>" method="POST" class="inline"
                                  onsubmit="return confirm('Tem certeza que deseja DELETAR permanentemente este comentario?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Deletar permanentemente">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($current_status === 'pending' && count($comments) > 0): ?>
        </form>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<script>
function confirmBulkAction(action) {
    const checkboxes = document.querySelectorAll('input[name="comment_ids[]"]:checked');
    if (checkboxes.length === 0) {
        alert('Selecione pelo menos um comentario.');
        return false;
    }
    return confirm(`Tem certeza que deseja ${action} ${checkboxes.length} comentario(s)?`);
}

// Selecionar/Deselecionar todos
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="comment_ids[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
});
</script>

<?= $this->endSection() ?>
