<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Linkagem Interna Automática</h1>
      <p class="text-sm text-gray-500 mt-1">Regras de auto-linking para SEO — keywords automaticamente viram links no blog</p>
    </div>
    <div class="flex space-x-2">
      <a href="<?= base_url('admin/seo') ?>" class="px-4 py-2 text-sm text-gray-600 bg-white border rounded-lg hover:bg-gray-50">
        <i class="fas fa-arrow-left mr-1"></i>SEO
      </a>
      <a href="<?= base_url('admin/seo/internal-links/category-map') ?>" class="px-4 py-2 text-sm text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
        <i class="fas fa-project-diagram mr-1"></i>Mapeamento
      </a>
      <button onclick="openNewRule()" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
        <i class="fas fa-plus mr-1"></i>Nova Regra
      </button>
    </div>
  </div>

  <!-- KPIs -->
  <div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border p-4 text-center">
      <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?? 0 ?></p>
      <p class="text-xs text-gray-500">Total de Regras</p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
      <p class="text-2xl font-bold text-emerald-600"><?= $stats['active'] ?? 0 ?></p>
      <p class="text-xs text-gray-500">Regras Ativas</p>
    </div>
    <div class="bg-white rounded-xl border p-4 text-center">
      <p class="text-2xl font-bold text-blue-600"><?= number_format($stats['total_hits'] ?? 0) ?></p>
      <p class="text-xs text-gray-500">Links Aplicados</p>
    </div>
  </div>

  <!-- Filtros -->
  <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
    <form method="get" class="flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-gray-500 mb-1">Buscar</label>
        <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Keyword ou URL..."
          class="w-full px-3 py-2 border rounded-lg text-sm">
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Status</label>
        <select name="status" class="px-3 py-2 border rounded-lg text-sm">
          <option value="">Todos</option>
          <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Ativo</option>
          <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Inativo</option>
        </select>
      </div>
      <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
        <i class="fas fa-search mr-1"></i>Filtrar
      </button>
    </form>
  </div>

  <!-- Info -->
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
    <div class="flex items-start space-x-3">
      <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
      <div class="text-sm text-blue-700">
        <p class="font-medium">Como funciona?</p>
        <p>As keywords cadastradas são automaticamente transformadas em links nos artigos do blog (apenas a primeira ocorrência). Links não são inseridos dentro de tags <code class="bg-blue-100 px-1 rounded">&lt;a&gt;</code>, <code class="bg-blue-100 px-1 rounded">&lt;code&gt;</code>, <code class="bg-blue-100 px-1 rounded">&lt;pre&gt;</code> ou headings. O conteúdo original não é alterado — os links são aplicados em tempo de renderização.</p>
      </div>
    </div>
  </div>

  <!-- Tabela -->
  <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-700">Keyword</th>
            <th class="text-left px-4 py-3 font-medium text-gray-700">URL Destino</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Escopo</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Prior.</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Hits</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ativo</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($rules)): ?>
          <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
              <i class="fas fa-link text-3xl mb-2"></i>
              <p>Nenhuma regra de linkagem cadastrada.</p>
              <button onclick="openNewRule()" class="mt-3 text-emerald-600 hover:underline text-sm">Criar primeira regra</button>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($rules as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <span class="font-medium text-gray-900"><?= esc($r['keyword']) ?></span>
              <div class="flex items-center gap-1 mt-1">
                <?php if ($r['match_whole_word']): ?><span class="text-[10px] px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded">palavra inteira</span><?php endif; ?>
                <?php if ($r['case_sensitive']): ?><span class="text-[10px] px-1.5 py-0.5 bg-yellow-100 text-yellow-600 rounded">case sensitive</span><?php endif; ?>
                <?php if ($r['open_new_tab']): ?><span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-600 rounded">nova aba</span><?php endif; ?>
              </div>
            </td>
            <td class="px-4 py-3">
              <code class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded break-all"><?= esc($r['target_url']) ?></code>
              <?php if (!empty($r['target_label'])): ?>
                <p class="text-[10px] text-gray-400 mt-0.5">title: <?= esc($r['target_label']) ?></p>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="text-xs <?= empty($r['category_scope']) ? 'text-purple-600' : 'text-blue-600' ?>">
                <?= empty($r['category_scope']) ? 'Global' : esc($r['category_scope']) ?>
              </span>
            </td>
            <td class="px-4 py-3 text-center text-gray-500"><?= $r['priority'] ?></td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center justify-center min-w-[32px] h-7 bg-blue-100 text-blue-700 rounded-full text-xs font-bold"><?= number_format($r['hit_count']) ?></span>
            </td>
            <td class="px-4 py-3 text-center">
              <button onclick="toggleRule(<?= $r['id'] ?>)" class="<?= $r['is_active'] ? 'text-green-600' : 'text-gray-400' ?>" title="Clique para alternar">
                <i class="fas <?= $r['is_active'] ? 'fa-toggle-on text-xl' : 'fa-toggle-off text-xl' ?>"></i>
              </button>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center space-x-1">
                <button onclick='editRule(<?= json_encode($r) ?>)' class="p-1.5 text-blue-400 hover:text-blue-700 rounded" title="Editar">
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <form action="<?= base_url('admin/seo/internal-links/delete/' . $r['id']) ?>" method="post" class="inline"
                  onsubmit="return confirm('Excluir esta regra?')">
                  <?= csrf_field() ?>
                  <button type="submit" class="p-1.5 text-red-400 hover:text-red-700 rounded" title="Excluir">
                    <i class="fas fa-trash text-xs"></i>
                  </button>
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

  <!-- Preview -->
  <div class="bg-white rounded-xl shadow-sm border p-6 mt-6">
    <h3 class="font-semibold text-gray-900 mb-3"><i class="fas fa-eye text-emerald-500 mr-2"></i>Testar Auto-Linking</h3>
    <textarea id="previewInput" rows="3" placeholder="Cole um texto para testar as regras de linkagem..."
      class="w-full px-3 py-2 border rounded-lg text-sm mb-3"></textarea>
    <button onclick="testPreview()" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
      <i class="fas fa-magic mr-1"></i>Testar
    </button>
    <div id="previewOutput" class="mt-4 p-4 bg-gray-50 rounded-lg border text-sm prose prose-sm max-w-none hidden"></div>
  </div>
</div>

<!-- Modal Regra -->
<div id="ruleModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
    <h3 id="ruleModalTitle" class="text-lg font-bold text-gray-900 mb-4">Nova Regra de Linkagem</h3>
    <form action="<?= base_url('admin/seo/internal-links/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="ruleId">
      <div class="space-y-4">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Palavra-chave *</label>
          <input type="text" name="keyword" id="ruleKeyword" required placeholder="Ex: campanha médica"
            class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">URL de Destino *</label>
          <input type="text" name="target_url" id="ruleUrl" required placeholder="/campaigns?category=medica"
            class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Título do link (atributo title)</label>
          <input type="text" name="target_label" id="ruleLabel" placeholder="Veja campanhas médicas"
            class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Escopo (categoria)</label>
            <select name="category_scope" id="ruleScope" class="w-full px-3 py-2 border rounded-lg text-sm">
              <option value="">Global (todas)</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= esc($cat['slug']) ?>"><?= esc($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Prioridade</label>
            <input type="number" name="priority" id="rulePriority" value="100" min="1"
              class="w-full px-3 py-2 border rounded-lg text-sm">
            <p class="text-[10px] text-gray-400 mt-0.5">Menor = mais prioridade</p>
          </div>
        </div>
        <div class="flex flex-wrap gap-4">
          <label class="flex items-center space-x-2 text-sm text-gray-700">
            <input type="checkbox" name="match_whole_word" id="ruleWholeWord" value="1" checked class="rounded">
            <span>Palavra inteira</span>
          </label>
          <label class="flex items-center space-x-2 text-sm text-gray-700">
            <input type="checkbox" name="case_sensitive" id="ruleCaseSensitive" value="1" class="rounded">
            <span>Case sensitive</span>
          </label>
          <label class="flex items-center space-x-2 text-sm text-gray-700">
            <input type="checkbox" name="open_new_tab" id="ruleNewTab" value="1" class="rounded">
            <span>Abrir em nova aba</span>
          </label>
          <label class="flex items-center space-x-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" id="ruleActive" value="1" checked class="rounded">
            <span>Ativo</span>
          </label>
        </div>
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button type="button" onclick="closeRuleModal()" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Cancelar</button>
        <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openNewRule() {
  document.getElementById('ruleModalTitle').textContent = 'Nova Regra de Linkagem';
  document.getElementById('ruleId').value = '';
  document.getElementById('ruleKeyword').value = '';
  document.getElementById('ruleUrl').value = '';
  document.getElementById('ruleLabel').value = '';
  document.getElementById('ruleScope').value = '';
  document.getElementById('rulePriority').value = '100';
  document.getElementById('ruleWholeWord').checked = true;
  document.getElementById('ruleCaseSensitive').checked = false;
  document.getElementById('ruleNewTab').checked = false;
  document.getElementById('ruleActive').checked = true;
  document.getElementById('ruleModal').classList.remove('hidden');
}

function editRule(r) {
  document.getElementById('ruleModalTitle').textContent = 'Editar Regra';
  document.getElementById('ruleId').value = r.id;
  document.getElementById('ruleKeyword').value = r.keyword;
  document.getElementById('ruleUrl').value = r.target_url;
  document.getElementById('ruleLabel').value = r.target_label || '';
  document.getElementById('ruleScope').value = r.category_scope || '';
  document.getElementById('rulePriority').value = r.priority || 100;
  document.getElementById('ruleWholeWord').checked = r.match_whole_word == 1;
  document.getElementById('ruleCaseSensitive').checked = r.case_sensitive == 1;
  document.getElementById('ruleNewTab').checked = r.open_new_tab == 1;
  document.getElementById('ruleActive').checked = r.is_active == 1;
  document.getElementById('ruleModal').classList.remove('hidden');
}

function closeRuleModal() {
  document.getElementById('ruleModal').classList.add('hidden');
}

function toggleRule(id) {
  fetch('<?= base_url('admin/seo/internal-links/toggle') ?>/' + id, {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
    body: JSON.stringify({})
  })
  .then(r => r.json())
  .then(data => { if (data.success) location.reload(); });
}

function testPreview() {
  const text = document.getElementById('previewInput').value;
  if (!text) return;

  fetch('<?= base_url('admin/seo/internal-links/preview') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
    body: 'text=' + encodeURIComponent(text)
  })
  .then(r => r.json())
  .then(data => {
    const output = document.getElementById('previewOutput');
    output.innerHTML = data.html || '<em class="text-gray-400">Sem resultado</em>';
    output.classList.remove('hidden');
  });
}
</script>

<?= $this->endSection() ?>
