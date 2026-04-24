<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<?php
  $isEdit = $isEdit ?? false;
  $p = $post ?? [];
?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <!-- Cabeçalho -->
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center space-x-3">
      <a href="<?= base_url('admin/blog') ?>" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left text-lg"></i></a>
      <h1 class="text-2xl font-bold text-gray-900"><?= $isEdit ? 'Editar Artigo' : 'Novo Artigo' ?></h1>
    </div>
    <div class="flex items-center space-x-2">
      <?php if ($isEdit && ($p['status'] ?? '') === 'published'): ?>
        <a href="<?= base_url('blog/' . ($p['slug'] ?? '')) ?>" target="_blank" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
          <i class="fas fa-eye mr-1"></i>Ver no site
        </a>
      <?php endif; ?>
    </div>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
      <i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
      <i class="fas fa-exclamation-circle mr-2"></i><?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= $isEdit ? base_url('admin/blog/update/' . $p['id']) : base_url('admin/blog/create') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Coluna Principal (2/3) -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Título -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <label class="text-sm font-medium text-gray-700 block mb-2">Título do Artigo *</label>
          <input type="text" name="title" value="<?= esc($p['title'] ?? '') ?>" required
            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-lg font-medium focus:border-emerald-500 focus:ring-0 transition"
            placeholder="Digite um título chamativo...">
        </div>

        <!-- Resumo / Excerpt -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <label class="text-sm font-medium text-gray-700 block mb-2">Resumo <span class="text-gray-400 font-normal">(aparece nas listagens)</span></label>
          <textarea name="excerpt" rows="3" class="w-full px-4 py-3 border rounded-xl text-sm focus:border-emerald-500"
            placeholder="Um resumo breve do artigo..."><?= esc($p['excerpt'] ?? '') ?></textarea>
        </div>

        <!-- Conteúdo (Editor Rico) -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <label class="text-sm font-medium text-gray-700 block mb-2">Conteúdo *</label>
          <!-- Toolbar do editor -->
          <div class="border border-b-0 rounded-t-lg bg-gray-50 p-2 flex flex-wrap gap-1">
            <button type="button" onclick="execCmd('bold')" class="px-2 py-1 rounded hover:bg-gray-200" title="Negrito"><i class="fas fa-bold"></i></button>
            <button type="button" onclick="execCmd('italic')" class="px-2 py-1 rounded hover:bg-gray-200" title="Itálico"><i class="fas fa-italic"></i></button>
            <button type="button" onclick="execCmd('underline')" class="px-2 py-1 rounded hover:bg-gray-200" title="Sublinhado"><i class="fas fa-underline"></i></button>
            <span class="border-r mx-1"></span>
            <button type="button" onclick="execCmd('formatBlock', 'H2')" class="px-2 py-1 rounded hover:bg-gray-200 text-xs font-bold" title="H2">H2</button>
            <button type="button" onclick="execCmd('formatBlock', 'H3')" class="px-2 py-1 rounded hover:bg-gray-200 text-xs font-bold" title="H3">H3</button>
            <button type="button" onclick="execCmd('formatBlock', 'P')" class="px-2 py-1 rounded hover:bg-gray-200 text-xs" title="Parágrafo">P</button>
            <span class="border-r mx-1"></span>
            <button type="button" onclick="execCmd('insertUnorderedList')" class="px-2 py-1 rounded hover:bg-gray-200" title="Lista"><i class="fas fa-list-ul"></i></button>
            <button type="button" onclick="execCmd('insertOrderedList')" class="px-2 py-1 rounded hover:bg-gray-200" title="Lista numerada"><i class="fas fa-list-ol"></i></button>
            <span class="border-r mx-1"></span>
            <button type="button" onclick="insertLink()" class="px-2 py-1 rounded hover:bg-gray-200" title="Link"><i class="fas fa-link"></i></button>
            <button type="button" onclick="insertImage()" class="px-2 py-1 rounded hover:bg-gray-200" title="Imagem"><i class="fas fa-image"></i></button>
            <button type="button" onclick="execCmd('formatBlock', 'BLOCKQUOTE')" class="px-2 py-1 rounded hover:bg-gray-200" title="Citação"><i class="fas fa-quote-left"></i></button>
            <span class="border-r mx-1"></span>
            <button type="button" onclick="toggleSource()" class="px-2 py-1 rounded hover:bg-gray-200 text-xs" title="HTML">HTML</button>
          </div>
          <div id="editor" contenteditable="true" class="w-full min-h-[400px] p-4 border rounded-b-lg focus:outline-none focus:border-emerald-500 prose max-w-none"
            style="overflow-y:auto"><?= $p['content'] ?? '' ?></div>
          <textarea name="content" id="content-hidden" class="hidden"><?= esc($p['content'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Sidebar (1/3) -->
      <div class="space-y-6">
        <!-- Publicação -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <h3 class="text-sm font-semibold text-gray-700 mb-4"><i class="fas fa-cog mr-1"></i>Publicação</h3>

          <div class="space-y-4">
            <div>
              <label class="text-xs text-gray-500 block mb-1">Status</label>
              <select name="status" class="w-full px-3 py-2 border rounded-lg text-sm">
                <option value="draft" <?= ($p['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                <option value="published" <?= ($p['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publicado</option>
                <option value="scheduled" <?= ($p['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Agendado</option>
              </select>
            </div>

            <div>
              <label class="text-xs text-gray-500 block mb-1">Categoria</label>
              <select name="category_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                <option value="">— Selecionar —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>" <?= ($p['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="text-xs text-gray-500 block mb-1">Data de Publicação</label>
              <input type="datetime-local" name="published_at" value="<?= !empty($p['published_at']) ? date('Y-m-d\TH:i', strtotime($p['published_at'])) : '' ?>" class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>

            <div class="flex items-center space-x-2">
              <input type="checkbox" name="is_featured" id="is_featured" value="1" <?= ($p['is_featured'] ?? 0) ? 'checked' : '' ?> class="rounded text-emerald-600">
              <label for="is_featured" class="text-sm text-gray-600">Artigo destaque</label>
            </div>

            <div class="flex items-center space-x-2">
              <input type="checkbox" name="allow_comments" id="allow_comments" value="1" <?= ($p['allow_comments'] ?? 1) ? 'checked' : '' ?> class="rounded text-emerald-600">
              <label for="allow_comments" class="text-sm text-gray-600">Permitir comentários</label>
            </div>
          </div>

          <button type="submit" class="w-full mt-4 px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium text-sm">
            <i class="fas fa-save mr-1"></i><?= $isEdit ? 'Atualizar Artigo' : 'Salvar Artigo' ?>
          </button>
        </div>

        <!-- Imagem Destaque -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <h3 class="text-sm font-semibold text-gray-700 mb-4"><i class="fas fa-image mr-1"></i>Imagem Destaque</h3>

          <?php if (!empty($p['featured_image'])): ?>
            <img src="<?= esc($p['featured_image']) ?>" class="w-full h-40 object-cover rounded-lg mb-3" alt="Preview">
          <?php endif; ?>

          <input type="file" name="featured_image" accept="image/*" class="w-full text-sm">

          <div class="mt-3 space-y-2">
            <div>
              <label class="text-xs text-gray-500">Alt Text (SEO imagem)</label>
              <input type="text" name="image_alt" value="<?= esc($p['image_alt'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded text-xs" placeholder="Descreva a imagem...">
            </div>
            <div>
              <label class="text-xs text-gray-500">Legenda</label>
              <input type="text" name="image_caption" value="<?= esc($p['image_caption'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded text-xs">
            </div>
            <div>
              <label class="text-xs text-gray-500">Créditos</label>
              <input type="text" name="image_credit" value="<?= esc($p['image_credit'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded text-xs">
            </div>
          </div>
        </div>

        <!-- SEO -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
          <h3 class="text-sm font-semibold text-gray-700 mb-4"><i class="fas fa-search text-blue-500 mr-1"></i>SEO (Automático)</h3>
          <p class="text-xs text-gray-400 mb-3">Se deixar vazio, será gerado automaticamente</p>

          <div class="space-y-3">
            <div>
              <label class="text-xs text-gray-500">Meta Title <span class="text-gray-300">(max 70 chars)</span></label>
              <input type="text" name="meta_title" value="<?= esc($p['meta_title'] ?? '') ?>" maxlength="70" class="w-full px-3 py-1.5 border rounded text-xs" placeholder="Auto: título + Blog DoarFazBem">
              <div class="text-xs text-gray-300 mt-0.5" x-data="{ len: '<?= strlen($p['meta_title'] ?? '') ?>' }">
                <span x-text="$el.previousElementSibling.previousElementSibling.value.length"></span>/70
              </div>
            </div>
            <div>
              <label class="text-xs text-gray-500">Meta Description <span class="text-gray-300">(max 160 chars)</span></label>
              <textarea name="meta_description" maxlength="160" rows="2" class="w-full px-3 py-1.5 border rounded text-xs" placeholder="Auto: excerpt do conteúdo"><?= esc($p['meta_description'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="text-xs text-gray-500">Keywords</label>
              <input type="text" name="meta_keywords" value="<?= esc($p['meta_keywords'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded text-xs" placeholder="doação, campanha, solidariedade">
            </div>
            <div>
              <label class="text-xs text-gray-500">Tags</label>
              <input type="text" name="tags" value="<?= esc(is_string($p['tags'] ?? '') ? implode(', ', json_decode($p['tags'] ?? '[]', true) ?: []) : '') ?>" class="w-full px-3 py-1.5 border rounded text-xs" placeholder="tag1, tag2, tag3">
            </div>
          </div>

          <!-- Preview Google -->
          <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-400 mb-1">Preview no Google:</p>
            <p class="text-sm text-blue-700 font-medium leading-tight" id="seo-preview-title"><?= esc($p['meta_title'] ?? $p['title'] ?? 'Título do Artigo') ?> | Blog DoarFazBem</p>
            <p class="text-xs text-green-700"><?= base_url('blog/') ?><span id="seo-preview-slug"><?= esc($p['slug'] ?? 'slug-do-artigo') ?></span></p>
            <p class="text-xs text-gray-600 mt-0.5" id="seo-preview-desc"><?= esc(mb_substr($p['meta_description'] ?? $p['excerpt'] ?? 'Descrição do artigo...', 0, 160)) ?></p>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
// Editor de conteúdo rico
function execCmd(command, value = null) {
  document.execCommand(command, false, value);
  document.getElementById('editor').focus();
}

function insertLink() {
  const url = prompt('URL do link:');
  if (url) execCmd('createLink', url);
}

function insertImage() {
  const url = prompt('URL da imagem:');
  if (url) execCmd('insertImage', url);
}

function toggleSource() {
  const editor = document.getElementById('editor');
  const hidden = document.getElementById('content-hidden');
  if (editor.contentEditable === 'true') {
    editor.textContent = editor.innerHTML;
    editor.contentEditable = 'false';
    editor.classList.add('font-mono', 'text-xs', 'bg-gray-900', 'text-green-400');
  } else {
    editor.innerHTML = editor.textContent;
    editor.contentEditable = 'true';
    editor.classList.remove('font-mono', 'text-xs', 'bg-gray-900', 'text-green-400');
  }
}

// Sincronizar editor com textarea oculto ao submeter
document.querySelector('form').addEventListener('submit', function() {
  const editor = document.getElementById('editor');
  document.getElementById('content-hidden').value = editor.innerHTML;
});

// Preview SEO em tempo real
const titleInput = document.querySelector('input[name="title"]');
if (titleInput) {
  titleInput.addEventListener('input', function() {
    document.getElementById('seo-preview-title').textContent = this.value + ' | Blog DoarFazBem';
  });
}
</script>

<?= $this->endSection() ?>
