<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
      <li><a href="<?= base_url() ?>" class="hover:text-emerald-600">Início</a></li>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li><a href="<?= base_url('blog') ?>" class="hover:text-emerald-600">Blog</a></li>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li class="font-medium text-gray-700">Busca</li>
    </ol>
  </nav>

  <h1 class="text-2xl font-bold text-gray-900 mb-2">Resultados para: <span class="text-emerald-600">"<?= esc($searchTerm) ?>"</span></h1>
  <p class="text-gray-500 mb-8"><?= count($posts) ?> resultado(s) encontrado(s)</p>

  <div class="mb-6">
    <form action="<?= base_url('blog/buscar') ?>" method="get" class="flex max-w-md">
      <input type="text" name="q" value="<?= esc($searchTerm) ?>" class="flex-1 px-4 py-2 border rounded-l-lg text-sm" placeholder="Buscar...">
      <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-r-lg hover:bg-emerald-700"><i class="fas fa-search"></i></button>
    </form>
  </div>

  <?php if (empty($posts)): ?>
    <div class="text-center py-12 bg-gray-50 rounded-2xl">
      <i class="fas fa-search text-4xl text-gray-300 mb-3"></i>
      <p class="text-gray-500">Nenhum artigo encontrado para esta busca.</p>
    </div>
  <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($posts as $post): ?>
      <a href="<?= base_url('blog/' . $post['slug']) ?>" class="block bg-white rounded-xl shadow-sm border p-4 hover:shadow-md transition group">
        <div class="flex items-start space-x-4">
          <img src="<?= !empty($post['featured_image']) ? esc($post['featured_image']) : base_url('assets/images/blog-default.jpg') ?>"
            alt="<?= esc($post['title']) ?>" class="w-20 h-20 object-cover rounded-lg flex-shrink-0" loading="lazy">
          <div>
            <h2 class="font-bold text-gray-900 group-hover:text-emerald-600 transition"><?= esc($post['title']) ?></h2>
            <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?= esc(mb_substr($post['excerpt'] ?? strip_tags($post['content']), 0, 160)) ?></p>
            <div class="flex items-center space-x-3 mt-2 text-xs text-gray-400">
              <span><?= $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : '' ?></span>
              <?php if (!empty($post['category_name'])): ?>
                <span class="px-2 py-0.5 bg-gray-100 rounded"><?= esc($post['category_name']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
