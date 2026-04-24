<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="fas fa-history text-blue-600 mr-2"></i>Histórico de Ações SEO
      </h1>
      <p class="text-gray-500 mt-1">Ações executadas pelo motor autônomo</p>
    </div>
    <a href="/admin/seo-engine" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
      <i class="fas fa-arrow-left mr-1"></i> Voltar ao Dashboard
    </a>
  </div>

  <!-- KPIs -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border p-5">
      <p class="text-xs text-gray-500 uppercase tracking-wide">Custo IA (30 dias)</p>
      <p class="text-2xl font-bold text-green-600">$<?= $aiCost30 ?></p>
      <p class="text-xs text-gray-400">Groq é gratuito</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-5">
      <p class="text-xs text-gray-500 uppercase tracking-wide">Custo IA (total)</p>
      <p class="text-2xl font-bold text-gray-900">$<?= $aiCostTotal ?></p>
    </div>

    <?php foreach (array_slice($actionsByType, 0, 2) as $at): ?>
    <div class="bg-white rounded-xl shadow-sm border p-5">
      <p class="text-xs text-gray-500 uppercase tracking-wide"><?= esc($at['action_type'] ?? '?') ?></p>
      <p class="text-2xl font-bold text-gray-900"><?= $at['total'] ?? 0 ?></p>
      <p class="text-xs text-green-600"><?= $at['success_count'] ?? 0 ?> com sucesso</p>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Tabela de Ações -->
  <?php if (empty($actions)): ?>
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
      <i class="fas fa-history text-gray-300 text-4xl mb-4"></i>
      <p class="text-gray-500">Nenhuma ação executada ainda.</p>
      <p class="text-gray-400 text-sm mt-1">Execute <code>php spark seo:execute</code> para processar oportunidades.</p>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ação</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keyword</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo IA</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($actions as $action): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">
                <?= date('d/m H:i', strtotime($action['executed_at'] ?? '')) ?>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                  <?= esc($action['action_type'] ?? '') ?>
                </span>
              </td>
              <td class="px-4 py-3 font-medium text-gray-900 max-w-[200px] truncate">
                <?= esc($action['keyword'] ?? '') ?>
              </td>
              <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate text-xs">
                <?= esc($action['target_url'] ?? '') ?>
              </td>
              <td class="px-4 py-3 text-gray-500 text-xs">
                <?= esc($action['ai_model'] ?? '-') ?>
              </td>
              <td class="px-4 py-3 text-center">
                <?php if (!empty($action['success'])): ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                    <i class="fas fa-check mr-1"></i> OK
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700" title="<?= esc($action['error_message'] ?? '') ?>">
                    <i class="fas fa-times mr-1"></i> Falha
                  </span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
