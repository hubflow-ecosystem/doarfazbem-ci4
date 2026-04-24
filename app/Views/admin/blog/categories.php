<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Categorias do Blog</h1>
      <p class="text-sm text-gray-500 mt-1">Organize seus artigos por categorias</p>
    </div>
    <div class="flex space-x-2">
      <a href="<?= base_url('admin/blog') ?>" class="px-4 py-2 text-sm text-gray-600 bg-white border rounded-lg hover:bg-gray-50">
        <i class="fas fa-arrow-left mr-1"></i>Voltar
      </a>
      <button onclick="openNewCategory()" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
        <i class="fas fa-plus mr-1"></i>Nova Categoria
      </button>
    </div>
  </div>

  <!-- Tabela -->
  <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-700">Categoria</th>
            <th class="text-left px-4 py-3 font-medium text-gray-700">Slug</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Artigos</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ordem</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ativa</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($categories)): ?>
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
              <i class="fas fa-tags text-3xl mb-2"></i>
              <p>Nenhuma categoria cadastrada.</p>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($categories as $cat): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="flex items-center space-x-2">
                <?php if (!empty($cat['icon'])): ?>
                  <i class="<?= esc($cat['icon']) ?> text-emerald-500"></i>
                <?php endif; ?>
                <div>
                  <p class="font-medium text-gray-900"><?= esc($cat['name']) ?></p>
                  <?php if (!empty($cat['description'])): ?>
                    <p class="text-xs text-gray-400 line-clamp-1"><?= esc($cat['description']) ?></p>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td class="px-4 py-3">
              <code class="text-xs bg-gray-100 px-2 py-0.5 rounded"><?= esc($cat['slug']) ?></code>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center justify-center w-8 h-8 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">
                <?= $cat['posts_count'] ?? 0 ?>
              </span>
            </td>
            <td class="px-4 py-3 text-center text-gray-500"><?= $cat['sort_order'] ?? 0 ?></td>
            <td class="px-4 py-3 text-center">
              <?php if ($cat['is_active'] ?? 1): ?>
                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
              <?php else: ?>
                <span class="text-gray-400"><i class="fas fa-times-circle"></i></span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center space-x-1">
                <button onclick='editCategory(<?= json_encode($cat) ?>)' class="p-1.5 text-blue-400 hover:text-blue-700 rounded" title="Editar">
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <?php if (($cat['posts_count'] ?? 0) == 0): ?>
                <form action="<?= base_url('admin/blog/categories/delete/' . $cat['id']) ?>" method="post" class="inline"
                  onsubmit="return confirm('Excluir esta categoria?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="p-1.5 text-red-400 hover:text-red-700 rounded" title="Excluir">
                    <i class="fas fa-trash text-xs"></i>
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Categoria -->
<div id="catModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
    <h3 id="catModalTitle" class="text-lg font-bold text-gray-900 mb-4">Nova Categoria</h3>
    <form action="<?= base_url('admin/blog/categories/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="catId">
      <div class="space-y-4">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Nome *</label>
          <input type="text" name="name" id="catName" required placeholder="Ex: Histórias de Sucesso"
            class="w-full px-3 py-2 border rounded-lg text-sm" oninput="generateSlug(this.value)">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Slug</label>
          <input type="text" name="slug" id="catSlug" placeholder="historias-de-sucesso"
            class="w-full px-3 py-2 border rounded-lg text-sm bg-gray-50">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Descrição</label>
          <textarea name="description" id="catDesc" rows="2" placeholder="Breve descrição da categoria..."
            class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Ícone (FontAwesome)</label>
            <input type="text" name="icon" id="catIcon" placeholder="fas fa-star"
              class="w-full px-3 py-2 border rounded-lg text-sm">
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Ordem</label>
            <input type="number" name="sort_order" id="catOrder" value="0"
              class="w-full px-3 py-2 border rounded-lg text-sm">
          </div>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Meta Descrição (SEO)</label>
          <textarea name="meta_description" id="catMeta" rows="2" maxlength="160"
            placeholder="Descrição para mecanismos de busca (max 160 caracteres)"
            class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" name="is_active" id="catActive" value="1" checked class="rounded">
          <label for="catActive" class="text-sm text-gray-700">Ativa</label>
        </div>
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button type="button" onclick="closeCatModal()" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Cancelar</button>
        <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
function generateSlug(text) {
  const slug = text.toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  document.getElementById('catSlug').value = slug;
}

function openNewCategory() {
  document.getElementById('catModalTitle').textContent = 'Nova Categoria';
  document.getElementById('catId').value = '';
  document.getElementById('catName').value = '';
  document.getElementById('catSlug').value = '';
  document.getElementById('catDesc').value = '';
  document.getElementById('catIcon').value = '';
  document.getElementById('catOrder').value = '0';
  document.getElementById('catMeta').value = '';
  document.getElementById('catActive').checked = true;
  document.getElementById('catModal').classList.remove('hidden');
}

function editCategory(cat) {
  document.getElementById('catModalTitle').textContent = 'Editar Categoria';
  document.getElementById('catId').value = cat.id;
  document.getElementById('catName').value = cat.name;
  document.getElementById('catSlug').value = cat.slug;
  document.getElementById('catDesc').value = cat.description || '';
  document.getElementById('catIcon').value = cat.icon || '';
  document.getElementById('catOrder').value = cat.sort_order || 0;
  document.getElementById('catMeta').value = cat.meta_description || '';
  document.getElementById('catActive').checked = cat.is_active == 1;
  document.getElementById('catModal').classList.remove('hidden');
}

function closeCatModal() {
  document.getElementById('catModal').classList.add('hidden');
}
</script>

<?= $this->endSection() ?>
