<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">Redirecionamentos</h1>
      <p class="text-sm text-gray-500 mt-1">Gerencie redirects 301/302 do site</p>
    </div>
    <div class="flex space-x-2">
      <a href="<?= base_url('admin/seo') ?>" class="px-4 py-2 text-sm text-gray-600 bg-white border rounded-lg hover:bg-gray-50">
        <i class="fas fa-arrow-left mr-1"></i>Voltar
      </a>
      <button onclick="openNewRedirect()" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
        <i class="fas fa-plus mr-1"></i>Novo Redirect
      </button>
    </div>
  </div>

  <!-- Tabela -->
  <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="text-left px-4 py-3 font-medium text-gray-700">De (Origem)</th>
            <th class="text-left px-4 py-3 font-medium text-gray-700">Para (Destino)</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Tipo</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Hits</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ativo</th>
            <th class="text-center px-4 py-3 font-medium text-gray-700">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php if (empty($redirects)): ?>
          <tr>
            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
              <i class="fas fa-directions text-3xl mb-2"></i>
              <p>Nenhum redirecionamento cadastrado.</p>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($redirects as $r): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <code class="text-xs bg-red-50 text-red-600 px-2 py-0.5 rounded break-all"><?= esc($r['from_url']) ?></code>
            </td>
            <td class="px-4 py-3">
              <code class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded break-all"><?= esc($r['to_url']) ?></code>
            </td>
            <td class="px-4 py-3 text-center">
              <span class="px-2 py-0.5 text-xs rounded-full <?= ($r['type'] ?? '301') === '301' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' ?>">
                <?= $r['type'] ?? '301' ?>
              </span>
            </td>
            <td class="px-4 py-3 text-center text-gray-500"><?= number_format($r['hit_count'] ?? 0) ?></td>
            <td class="px-4 py-3 text-center">
              <?php if ($r['is_active'] ?? 1): ?>
                <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
              <?php else: ?>
                <span class="text-gray-400"><i class="fas fa-times-circle"></i></span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex items-center justify-center space-x-1">
                <button onclick="editRedirect(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)"
                  class="p-1.5 text-blue-400 hover:text-blue-700 rounded" title="Editar">
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <form action="<?= base_url('admin/seo/redirects/delete/' . $r['id']) ?>" method="post" class="inline"
                  onsubmit="return confirm('Excluir este redirecionamento?')">
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
</div>

<!-- Modal Novo/Editar Redirect -->
<div id="redirectModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
    <h3 id="modalTitle" class="text-lg font-bold text-gray-900 mb-4">Novo Redirecionamento</h3>
    <form id="redirectForm" action="<?= base_url('admin/seo/redirects/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="redirectId">
      <div class="space-y-3">
        <div>
          <label class="block text-xs text-gray-500 mb-1">URL de Origem *</label>
          <input type="text" name="from_url" id="formFrom" required placeholder="/pagina-antiga"
            class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">URL de Destino *</label>
          <input type="text" name="to_url" id="formTo" required placeholder="/pagina-nova"
            class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Tipo</label>
          <select name="type" id="formType" class="w-full px-3 py-2 border rounded-lg text-sm">
            <option value="301">301 - Permanente (recomendado)</option>
            <option value="302">302 - Temporário</option>
          </select>
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" name="is_active" id="formActive" value="1" checked class="rounded">
          <label for="formActive" class="text-sm text-gray-700">Ativo</label>
        </div>
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Cancelar</button>
        <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openNewRedirect() {
  document.getElementById('modalTitle').textContent = 'Novo Redirecionamento';
  document.getElementById('redirectId').value = '';
  document.getElementById('formFrom').value = '';
  document.getElementById('formTo').value = '';
  document.getElementById('formType').value = '301';
  document.getElementById('formActive').checked = true;
  document.getElementById('redirectModal').classList.remove('hidden');
}

function editRedirect(r) {
  document.getElementById('modalTitle').textContent = 'Editar Redirecionamento';
  document.getElementById('redirectId').value = r.id;
  document.getElementById('formFrom').value = r.from_url;
  document.getElementById('formTo').value = r.to_url;
  document.getElementById('formType').value = r.type || '301';
  document.getElementById('formActive').checked = r.is_active == 1;
  document.getElementById('redirectModal').classList.remove('hidden');
}

function closeModal() {
  document.getElementById('redirectModal').classList.add('hidden');
}
</script>

<?= $this->endSection() ?>
