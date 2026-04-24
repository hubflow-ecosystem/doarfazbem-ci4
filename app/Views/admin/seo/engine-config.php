<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="fas fa-sliders-h text-gray-600 mr-2"></i>Configurações do Motor SEO
      </h1>
      <p class="text-gray-500 mt-1">Thresholds, limites e conexões de API</p>
    </div>
    <a href="/admin/seo-engine" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
      <i class="fas fa-arrow-left mr-1"></i> Voltar ao Dashboard
    </a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <p class="text-green-700"><i class="fas fa-check-circle mr-2"></i><?= session()->getFlashdata('success') ?></p>
  </div>
  <?php endif; ?>

  <form action="/admin/seo-engine/config" method="post" class="space-y-8">
    <?= csrf_field() ?>

    <!-- Motor SEO -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-power-off text-green-600 mr-2"></i>Motor SEO
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Motor Habilitado</label>
          <select name="engine_enabled" class="w-full px-3 py-2 border rounded-lg">
            <option value="true" <?= ($configs['engine_enabled'] ?? '') === 'true' ? 'selected' : '' ?>>Ativo</option>
            <option value="false" <?= ($configs['engine_enabled'] ?? '') === 'false' ? 'selected' : '' ?>>Inativo</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Auto-publicar Meta Tags</label>
          <select name="auto_publish" class="w-full px-3 py-2 border rounded-lg">
            <option value="true" <?= ($configs['auto_publish'] ?? '') === 'true' ? 'selected' : '' ?>>Sim</option>
            <option value="false" <?= ($configs['auto_publish'] ?? '') === 'false' ? 'selected' : '' ?>>Não</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Thresholds -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-ruler text-blue-600 mr-2"></i>Thresholds de Análise
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">CTR Target (%)</label>
          <input type="number" step="0.1" name="target_ctr" value="<?= esc($configs['target_ctr'] ?? '5.0') ?>" class="w-full px-3 py-2 border rounded-lg">
          <p class="text-xs text-gray-400 mt-1">CTR considerado satisfatório</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mín. Impressões (Content Gap)</label>
          <input type="number" name="min_impressions_content_gap" value="<?= esc($configs['min_impressions_content_gap'] ?? '20') ?>" class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mín. Impressões (Low CTR)</label>
          <input type="number" name="min_impressions_low_ctr" value="<?= esc($configs['min_impressions_low_ctr'] ?? '50') ?>" class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Striking Distance (mín-máx)</label>
          <div class="flex gap-2">
            <input type="number" name="striking_distance_min" value="<?= esc($configs['striking_distance_min'] ?? '4') ?>" class="w-full px-3 py-2 border rounded-lg" placeholder="Min">
            <span class="self-center text-gray-400">—</span>
            <input type="number" name="striking_distance_max" value="<?= esc($configs['striking_distance_max'] ?? '20') ?>" class="w-full px-3 py-2 border rounded-lg" placeholder="Max">
          </div>
        </div>
      </div>
    </div>

    <!-- Limites Diários -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">
        <i class="fas fa-tachometer-alt text-orange-600 mr-2"></i>Limites Diários
      </h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Blog Posts/dia</label>
          <input type="number" name="max_articles_per_day" value="<?= esc($configs['max_articles_per_day'] ?? '5') ?>" class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Enrichments/dia</label>
          <input type="number" name="max_enrichments_per_day" value="<?= esc($configs['max_enrichments_per_day'] ?? '9999') ?>" class="w-full px-3 py-2 border rounded-lg">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Top Positions/dia</label>
          <input type="number" name="max_top_positions_per_day" value="<?= esc($configs['max_top_positions_per_day'] ?? '9999') ?>" class="w-full px-3 py-2 border rounded-lg">
        </div>
      </div>
    </div>

    <div class="flex justify-end">
      <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
        <i class="fas fa-save mr-2"></i>Salvar Configurações
      </button>
    </div>
  </form>

  <!-- Testes de API -->
  <div class="bg-white rounded-xl shadow-sm border p-6 mt-8">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
      <i class="fas fa-plug text-green-600 mr-2"></i>Teste de Conexões
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <button onclick="testApi('gsc')" id="btn-gsc" class="w-full px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 text-left">
          <div class="flex items-center gap-3">
            <i class="fab fa-google text-blue-600 text-xl"></i>
            <div>
              <p class="font-medium text-gray-900">Google Search Console</p>
              <p class="text-xs text-gray-500" id="status-gsc">Clique para testar</p>
            </div>
          </div>
        </button>
      </div>

      <div>
        <button onclick="testApi('bing')" id="btn-bing" class="w-full px-4 py-3 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100 text-left">
          <div class="flex items-center gap-3">
            <i class="fab fa-microsoft text-teal-600 text-xl"></i>
            <div>
              <p class="font-medium text-gray-900">Bing Webmaster</p>
              <p class="text-xs text-gray-500" id="status-bing">Clique para testar</p>
            </div>
          </div>
        </button>
      </div>

      <div>
        <button onclick="testApi('grok')" id="btn-grok" class="w-full px-4 py-3 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 text-left">
          <div class="flex items-center gap-3">
            <i class="fas fa-robot text-purple-600 text-xl"></i>
            <div>
              <p class="font-medium text-gray-900">Groq AI (LLaMA 3.3)</p>
              <p class="text-xs text-gray-500" id="status-grok">Clique para testar</p>
            </div>
          </div>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function testApi(type) {
  const statusEl = document.getElementById('status-' + type);
  statusEl.textContent = 'Testando...';
  statusEl.className = 'text-xs text-yellow-600';

  fetch('/admin/seo-engine/test-' + type, {
    method: 'POST',
    headers: {'X-Requested-With': 'XMLHttpRequest'}
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      statusEl.textContent = 'Conectado!';
      statusEl.className = 'text-xs text-green-600 font-medium';
    } else {
      statusEl.textContent = res.error || 'Falha na conexão';
      statusEl.className = 'text-xs text-red-600';
    }
  })
  .catch(e => {
    statusEl.textContent = 'Erro: ' + e.message;
    statusEl.className = 'text-xs text-red-600';
  });
}
</script>

<?= $this->endSection() ?>
