<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Monitor de 404</h1>
      <p class="text-sm text-gray-500 mt-1">URLs que retornaram erro 404 no site</p>
    </div>
    <a href="<?= base_url('admin/seo') ?>" class="px-4 py-2 text-sm text-gray-600 bg-white border rounded-lg hover:bg-gray-50">
      <i class="fas fa-arrow-left mr-1"></i>Voltar ao SEO
    </a>
  </div>

  <!-- Filtros -->
  <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
    <form method="get" class="flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-gray-500 mb-1">Buscar URL</label>
        <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Filtrar por URL..."
          class="w-full px-3 py-2 border rounded-lg text-sm">
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Status</label>
        <select name="status" class="px-3 py-2 border rounded-lg text-sm">
          <option value="">Todos</option>
          <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pendente</option>
          <option value="ignored" <?= ($filters['status'] ?? '') === 'ignored' ? 'selected' : '' ?>>Ignorado</option>
          <option value="redirected" <?= ($filters['status'] ?? '') === 'redirected' ? 'selected' : '' ?>>Redirecionado</option>
        </select>
      </div>
      <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700">
        <i class="fas fa-search mr-1"></i>Filtrar
      </button>
    </form>
  </div>

  <!-- Tabela -->
  <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-700">URL</th>
            <th class="text-left px-4 py-3 font-medium text-gray-700">Referrer</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Hits</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Status</th>
            <th class="text-left px-4 py-3 font-medium text-gray-700">Último Acesso</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($errors404)): ?>
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
              <i class="fas fa-check-circle text-3xl mb-2"></i>
              <p>Nenhum erro 404 registrado.</p>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($errors404 as $err): ?>
          <tr class="hover:bg-gray-50" id="row-404-<?= $err['id'] ?>">
            <td class="px-4 py-3">
              <code class="text-xs bg-gray-100 px-2 py-0.5 rounded break-all"><?= esc($err['url']) ?></code>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500 max-w-[200px] truncate"><?= esc($err['referrer'] ?? '-') ?></td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-700 rounded-full text-xs font-bold"><?= $err['hit_count'] ?></span>
            </td>
            <td class="px-4 py-3 text-center">
              <?php
                $statusColors = ['pending' => 'bg-yellow-100 text-yellow-700', 'ignored' => 'bg-gray-100 text-gray-600', 'redirected' => 'bg-green-100 text-green-700'];
                $statusLabels = ['pending' => 'Pendente', 'ignored' => 'Ignorado', 'redirected' => 'Redirecionado'];
                $st = $err['status'] ?? 'pending';
              ?>
              <span class="px-2 py-0.5 text-xs rounded-full <?= $statusColors[$st] ?? '' ?>"><?= $statusLabels[$st] ?? $st ?></span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($err['last_hit_at'] ?? $err['created_at'])) ?></td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center space-x-1">
                <button onclick="action404(<?= $err['id'] ?>, 'ignore')" class="p-1.5 text-gray-400 hover:text-gray-700 rounded" title="Ignorar">
                  <i class="fas fa-eye-slash text-xs"></i>
                </button>
                <button onclick="action404(<?= $err['id'] ?>, 'redirect')" class="p-1.5 text-blue-400 hover:text-blue-700 rounded" title="Criar Redirect">
                  <i class="fas fa-external-link-alt text-xs"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="flex justify-center p-4 border-t space-x-2">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&q=<?= esc($filters['q'] ?? '') ?>&status=<?= esc($filters['status'] ?? '') ?>"
          class="px-3 py-1 rounded text-sm <?= $i == ($currentPage ?? 1) ? 'bg-emerald-600 text-white' : 'bg-white border hover:bg-gray-50 text-gray-700' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal de Redirect -->
<div id="redirectModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Criar Redirecionamento</h3>
    <input type="hidden" id="redirect404Id">
    <div class="space-y-3">
      <div>
        <label class="block text-xs text-gray-500 mb-1">URL de Origem</label>
        <input type="text" id="redirectFrom" readonly class="w-full px-3 py-2 border rounded-lg text-sm bg-gray-50">
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Redirecionar para</label>
        <input type="text" id="redirectTo" placeholder="/nova-pagina" class="w-full px-3 py-2 border rounded-lg text-sm">
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">Tipo</label>
        <select id="redirectType" class="w-full px-3 py-2 border rounded-lg text-sm">
          <option value="301">301 - Permanente</option>
          <option value="302">302 - Temporário</option>
        </select>
      </div>
    </div>
    <div class="flex justify-end space-x-2 mt-4">
      <button onclick="closeRedirectModal()" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Cancelar</button>
      <button onclick="saveRedirect()" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Salvar</button>
    </div>
  </div>
</div>

<script>
function action404(id, action) {
  if (action === 'redirect') {
    const row = document.getElementById('row-404-' + id);
    const url = row.querySelector('code').textContent;
    document.getElementById('redirect404Id').value = id;
    document.getElementById('redirectFrom').value = url;
    document.getElementById('redirectTo').value = '';
    document.getElementById('redirectModal').classList.remove('hidden');
    return;
  }

  fetch('<?= base_url('admin/seo/404/action') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
    body: JSON.stringify({id: id, action: action})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Erro ao processar');
  });
}

function closeRedirectModal() {
  document.getElementById('redirectModal').classList.add('hidden');
}

function saveRedirect() {
  const id = document.getElementById('redirect404Id').value;
  const from = document.getElementById('redirectFrom').value;
  const to = document.getElementById('redirectTo').value;
  const type = document.getElementById('redirectType').value;

  if (!to) { alert('Informe a URL de destino'); return; }

  fetch('<?= base_url('admin/seo/redirects/save') ?>', {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
    body: JSON.stringify({from_url: from, to_url: to, type: type, error_404_id: id})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeRedirectModal();
      location.reload();
    } else {
      alert(data.message || 'Erro ao salvar');
    }
  });
}
</script>

<?= $this->endSection() ?>
