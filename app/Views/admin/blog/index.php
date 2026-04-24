<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="fas fa-newspaper text-emerald-600 mr-2"></i>Blog — Artigos
      </h1>
      <p class="text-gray-500 mt-1"><?= count($posts ?? []) ?> artigo(s) encontrado(s)</p>
    </div>
    <div class="flex space-x-2">
      <a href="<?= base_url('admin/blog/categories') ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
        <i class="fas fa-tags mr-1"></i> Categorias
      </a>
      <a href="<?= base_url('admin/blog/create') ?>" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm">
        <i class="fas fa-plus mr-1"></i> Novo Artigo
      </a>
    </div>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
      <i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?>
    </div>
  <?php endif; ?>

  <!-- Filtros -->
  <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
    <form method="get" class="flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="text-xs text-gray-500 block mb-1">Buscar</label>
        <input type="text" name="q" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Buscar artigos..." class="w-full px-3 py-2 border rounded-lg text-sm">
      </div>
      <div>
        <label class="text-xs text-gray-500 block mb-1">Status</label>
        <select name="status" class="px-3 py-2 border rounded-lg text-sm">
          <option value="">Todos</option>
          <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicados</option>
          <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Rascunhos</option>
          <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendados</option>
        </select>
      </div>
      <div>
        <label class="text-xs text-gray-500 block mb-1">Categoria</label>
        <select name="category_id" class="px-3 py-2 border rounded-lg text-sm">
          <option value="">Todas</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm"><i class="fas fa-search mr-1"></i>Filtrar</button>
    </form>
  </div>

  <!-- Lista de artigos -->
  <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Artigo</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
          <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Views</th>
          <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Data</th>
          <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <?php if (empty($posts)): ?>
          <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Nenhum artigo encontrado. <a href="<?= base_url('admin/blog/create') ?>" class="text-emerald-600 hover:underline">Criar primeiro artigo</a></td></tr>
        <?php else: ?>
          <?php foreach ($posts as $post): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center space-x-3">
                <?php if (!empty($post['featured_image'])): ?>
                  <img src="<?= esc($post['featured_image']) ?>" class="w-12 h-12 object-cover rounded-lg" alt="">
                <?php else: ?>
                  <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"><i class="fas fa-image text-gray-400"></i></div>
                <?php endif; ?>
                <div>
                  <p class="font-medium text-gray-900"><?= esc(mb_substr($post['title'], 0, 60)) ?></p>
                  <p class="text-xs text-gray-400">/blog/<?= esc($post['slug']) ?></p>
                </div>
              </div>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600"><?= esc($post['category_name'] ?? '—') ?></td>
            <td class="px-4 py-3 text-center">
              <?php
                $badges = ['published' => 'bg-green-100 text-green-700', 'draft' => 'bg-gray-100 text-gray-600', 'scheduled' => 'bg-blue-100 text-blue-700'];
                $labels = ['published' => 'Publicado', 'draft' => 'Rascunho', 'scheduled' => 'Agendado'];
              ?>
              <span class="px-2 py-1 text-xs rounded-full <?= $badges[$post['status']] ?? '' ?>"><?= $labels[$post['status']] ?? $post['status'] ?></span>
            </td>
            <td class="px-4 py-3 text-sm text-right text-gray-500"><?= number_format($post['views_count'] ?? 0) ?></td>
            <td class="px-4 py-3 text-sm text-center text-gray-500"><?= $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : '—' ?></td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center space-x-2">
                <a href="<?= base_url('admin/blog/edit/' . $post['id']) ?>" class="text-blue-600 hover:text-blue-800" title="Editar"><i class="fas fa-edit"></i></a>
                <?php if ($post['status'] === 'published'): ?>
                  <a href="<?= base_url('blog/' . $post['slug']) ?>" target="_blank" class="text-green-600 hover:text-green-800" title="Ver"><i class="fas fa-external-link-alt"></i></a>
                <?php endif; ?>
                <form method="post" action="<?= base_url('admin/blog/duplicate/' . $post['id']) ?>" class="inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="text-purple-600 hover:text-purple-800" title="Duplicar"><i class="fas fa-copy"></i></button>
                </form>
                <form method="post" action="<?= base_url('admin/blog/delete/' . $post['id']) ?>" class="inline" onsubmit="return confirm('Mover para lixeira?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="text-red-600 hover:text-red-800" title="Excluir"><i class="fas fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
