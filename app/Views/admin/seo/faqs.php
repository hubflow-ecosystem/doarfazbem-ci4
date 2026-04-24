<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">FAQs (Schema.org)</h1>
      <p class="text-sm text-gray-500 mt-1">Perguntas frequentes que geram Schema.org FAQPage automaticamente</p>
    </div>
    <div class="flex space-x-2">
      <a href="<?= base_url('admin/seo') ?>" class="px-4 py-2 text-sm text-gray-600 bg-white border rounded-lg hover:bg-gray-50">
        <i class="fas fa-arrow-left mr-1"></i>Voltar
      </a>
      <button onclick="openNewFaq()" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
        <i class="fas fa-plus mr-1"></i>Nova FAQ
      </button>
    </div>
  </div>

  <!-- Info -->
  <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
    <div class="flex items-start space-x-3">
      <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
      <div class="text-sm text-blue-700">
        <p class="font-medium">Como funciona?</p>
        <p>As FAQs cadastradas aqui geram automaticamente o Schema.org <code class="bg-blue-100 px-1 rounded">FAQPage</code> nas páginas do site, melhorando o posicionamento nos resultados de busca do Google com rich snippets.</p>
      </div>
    </div>
  </div>

  <!-- Lista de FAQs -->
  <div class="space-y-3">
    <?php if (empty($faqs)): ?>
    <div class="bg-white rounded-xl shadow-sm border p-8 text-center text-gray-400">
      <i class="fas fa-question-circle text-3xl mb-2"></i>
      <p>Nenhuma FAQ cadastrada ainda.</p>
      <button onclick="openNewFaq()" class="mt-3 text-emerald-600 hover:underline text-sm">Criar primeira FAQ</button>
    </div>
    <?php else: ?>
    <?php foreach ($faqs as $faq): ?>
    <div class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div class="flex items-center space-x-2 mb-1">
            <span class="text-xs px-2 py-0.5 rounded-full <?= ($faq['page_type'] ?? 'global') === 'global' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
              <?= esc($faq['page_type'] ?? 'global') ?>
            </span>
            <?php if (!($faq['is_active'] ?? 1)): ?>
              <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Inativo</span>
            <?php endif; ?>
            <span class="text-xs text-gray-400">Ordem: <?= $faq['sort_order'] ?? 0 ?></span>
          </div>
          <h3 class="font-semibold text-gray-900"><?= esc($faq['question']) ?></h3>
          <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= esc($faq['answer']) ?></p>
        </div>
        <div class="flex items-center space-x-1 ml-4 flex-shrink-0">
          <button onclick='editFaq(<?= json_encode($faq) ?>)' class="p-2 text-blue-400 hover:text-blue-700 rounded" title="Editar">
            <i class="fas fa-edit text-sm"></i>
          </button>
          <form action="<?= base_url('admin/seo/faqs/delete/' . $faq['id']) ?>" method="post" class="inline"
            onsubmit="return confirm('Excluir esta FAQ?')">
            <?= csrf_field() ?>
            <button type="submit" class="p-2 text-red-400 hover:text-red-700 rounded" title="Excluir">
              <i class="fas fa-trash text-sm"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Modal FAQ -->
<div id="faqModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
  <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
    <h3 id="faqModalTitle" class="text-lg font-bold text-gray-900 mb-4">Nova FAQ</h3>
    <form action="<?= base_url('admin/seo/faqs/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="faqId">
      <div class="space-y-4">
        <div>
          <label class="block text-xs text-gray-500 mb-1">Pergunta *</label>
          <input type="text" name="question" id="faqQuestion" required placeholder="Como posso criar uma campanha?"
            class="w-full px-3 py-2 border rounded-lg text-sm">
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Resposta *</label>
          <textarea name="answer" id="faqAnswer" required rows="4" placeholder="Escreva a resposta completa..."
            class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-500 mb-1">Página</label>
            <select name="page_type" id="faqPageType" class="w-full px-3 py-2 border rounded-lg text-sm">
              <option value="global">Global (todas)</option>
              <option value="home">Home</option>
              <option value="campaigns">Campanhas</option>
              <option value="blog">Blog</option>
              <option value="rifas">Rifas</option>
              <option value="about">Sobre</option>
              <option value="how-it-works">Como Funciona</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-gray-500 mb-1">Ordem</label>
            <input type="number" name="sort_order" id="faqOrder" value="0" class="w-full px-3 py-2 border rounded-lg text-sm">
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" name="is_active" id="faqActive" value="1" checked class="rounded">
          <label for="faqActive" class="text-sm text-gray-700">Ativo</label>
        </div>
      </div>
      <div class="flex justify-end space-x-2 mt-4">
        <button type="button" onclick="closeFaqModal()" class="px-4 py-2 text-sm text-gray-600 border rounded-lg hover:bg-gray-50">Cancelar</button>
        <button type="submit" class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Salvar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openNewFaq() {
  document.getElementById('faqModalTitle').textContent = 'Nova FAQ';
  document.getElementById('faqId').value = '';
  document.getElementById('faqQuestion').value = '';
  document.getElementById('faqAnswer').value = '';
  document.getElementById('faqPageType').value = 'global';
  document.getElementById('faqOrder').value = '0';
  document.getElementById('faqActive').checked = true;
  document.getElementById('faqModal').classList.remove('hidden');
}

function editFaq(faq) {
  document.getElementById('faqModalTitle').textContent = 'Editar FAQ';
  document.getElementById('faqId').value = faq.id;
  document.getElementById('faqQuestion').value = faq.question;
  document.getElementById('faqAnswer').value = faq.answer;
  document.getElementById('faqPageType').value = faq.page_type || 'global';
  document.getElementById('faqOrder').value = faq.sort_order || 0;
  document.getElementById('faqActive').checked = faq.is_active == 1;
  document.getElementById('faqModal').classList.remove('hidden');
}

function closeFaqModal() {
  document.getElementById('faqModal').classList.add('hidden');
}
</script>

<?= $this->endSection() ?>
