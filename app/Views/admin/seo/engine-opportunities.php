<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">
        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>Oportunidades SEO
      </h1>
      <p class="text-gray-500 mt-1">Oportunidades detectadas pelo motor de análise</p>
    </div>
    <a href="/admin/seo-engine" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
      <i class="fas fa-arrow-left mr-1"></i> Voltar ao Dashboard
    </a>
  </div>

  <!-- Filtros por Status -->
  <div class="flex flex-wrap gap-2 mb-6">
    <?php
    $statusLabels = [
      'pending' => ['Pendentes', 'yellow'],
      'monitoring' => ['Monitorando', 'blue'],
      'failed' => ['Falhas', 'red'],
      'dismissed' => ['Dispensadas', 'gray'],
      '' => ['Todas', 'purple'],
    ];
    foreach ($statusLabels as $st => $info):
      $count = 0;
      foreach ($countByStatus as $cs) {
        if ($cs['status'] === $st || ($st === '' && true)) $count += (int)($cs['total'] ?? 0);
      }
      if ($st === '') {
        $count = 0;
        foreach ($countByStatus as $cs) $count += (int)($cs['total'] ?? 0);
      }
    ?>
    <a href="?status=<?= $st ?>&type=<?= $currentType ?>"
       class="px-3 py-1.5 rounded-full text-sm font-medium <?= $currentStatus === $st ? "bg-{$info[1]}-600 text-white" : "bg-{$info[1]}-100 text-{$info[1]}-700 hover:bg-{$info[1]}-200" ?>">
      <?= $info[0] ?> (<?= $count ?>)
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Filtros por Tipo -->
  <div class="flex flex-wrap gap-2 mb-6">
    <span class="text-sm text-gray-500 self-center mr-2">Tipo:</span>
    <?php
    $typeLabels = [
      '' => 'Todos',
      'content_gap' => 'Content Gap',
      'low_ctr' => 'Low CTR',
      'top_position' => 'Top Position',
      'striking_distance' => 'Striking Distance',
      'enrichment' => 'Enrichment',
    ];
    foreach ($typeLabels as $t => $label):
    ?>
    <a href="?status=<?= $currentStatus ?>&type=<?= $t ?>"
       class="px-3 py-1 rounded text-xs font-medium <?= $currentType === $t ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Tabela -->
  <?php if (empty($opportunities)): ?>
    <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
      <i class="fas fa-search text-gray-300 text-4xl mb-4"></i>
      <p class="text-gray-500">Nenhuma oportunidade encontrada com os filtros atuais.</p>
      <p class="text-gray-400 text-sm mt-1">Execute <code>php spark seo:analyze</code> para detectar oportunidades.</p>
    </div>
  <?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keyword</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Imp</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pos</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">CTR</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($opportunities as $opp): ?>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <?php
                  $score = (int)($opp['priority_score'] ?? 0);
                  $color = $score >= 800 ? 'red' : ($score >= 500 ? 'orange' : ($score >= 250 ? 'yellow' : 'gray'));
                ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-<?= $color ?>-100 text-<?= $color ?>-700">
                  <?= $score ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <?php
                  $typeBadge = match($opp['type'] ?? '') {
                    'content_gap' => ['bg-purple-100 text-purple-700', 'Gap'],
                    'low_ctr' => ['bg-orange-100 text-orange-700', 'Low CTR'],
                    'top_position' => ['bg-green-100 text-green-700', 'Top 3'],
                    'striking_distance' => ['bg-blue-100 text-blue-700', 'Striking'],
                    'enrichment' => ['bg-teal-100 text-teal-700', 'Enrich'],
                    default => ['bg-gray-100 text-gray-700', $opp['type'] ?? '?'],
                  };
                ?>
                <span class="px-2 py-0.5 rounded text-xs font-medium <?= $typeBadge[0] ?>"><?= $typeBadge[1] ?></span>
              </td>
              <td class="px-4 py-3">
                <p class="font-medium text-gray-900 max-w-[250px] truncate"><?= esc($opp['keyword'] ?? '') ?></p>
                <?php if (!empty($opp['current_page_url'])): ?>
                <p class="text-xs text-gray-400 truncate max-w-[250px]"><?= esc($opp['current_page_url']) ?></p>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3">
                <span class="text-xs text-gray-500"><?= esc($opp['target_type'] ?? '') ?></span>
              </td>
              <td class="px-4 py-3 text-right text-gray-600"><?= number_format((int)($opp['impressions'] ?? 0)) ?></td>
              <td class="px-4 py-3 text-right">
                <span class="<?= ($opp['current_position'] ?? 99) <= 10 ? 'text-green-600' : 'text-gray-600' ?>">
                  <?= round((float)($opp['current_position'] ?? 0), 1) ?>
                </span>
              </td>
              <td class="px-4 py-3 text-right text-gray-600"><?= $opp['current_ctr'] ?? 0 ?>%</td>
              <td class="px-4 py-3 text-center">
                <?php
                  $statusBadge = match($opp['status'] ?? '') {
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'monitoring' => 'bg-blue-100 text-blue-700',
                    'failed' => 'bg-red-100 text-red-700',
                    'dismissed' => 'bg-gray-100 text-gray-500',
                    default => 'bg-gray-100 text-gray-500',
                  };
                ?>
                <span class="px-2 py-0.5 rounded text-xs font-medium <?= $statusBadge ?>"><?= esc($opp['status'] ?? '') ?></span>
              </td>
              <td class="px-4 py-3 text-center">
                <?php if (($opp['status'] ?? '') === 'pending'): ?>
                <button onclick="dismissOpp(<?= (int)($opp['id'] ?? 0) ?>)" class="text-gray-400 hover:text-red-500" title="Dispensar">
                  <i class="fas fa-times"></i>
                </button>
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

<script>
function dismissOpp(id) {
  if (!confirm('Dispensar esta oportunidade?')) return;
  fetch('/admin/seo-engine/dismiss', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
    body: 'id=' + id
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) location.reload();
    else alert('Erro: ' + (res.error || 'Desconhecido'));
  });
}
</script>

<?= $this->endSection() ?>
