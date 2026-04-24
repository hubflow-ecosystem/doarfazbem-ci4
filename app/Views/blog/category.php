<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<?= $breadcrumbSchema ?? '' ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
      <li><a href="<?= base_url() ?>" class="hover:text-emerald-600">Início</a></li>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li><a href="<?= base_url('blog') ?>" class="hover:text-emerald-600">Blog</a></li>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li class="font-medium text-gray-700"><?= esc($category['name']) ?></li>
    </ol>
  </nav>

  <div class="text-center mb-10">
    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= esc($category['name']) ?></h1>
    <?php if (!empty($category['description'])): ?>
      <p class="text-gray-500 max-w-xl mx-auto"><?= esc($category['description']) ?></p>
    <?php endif; ?>
  </div>

  <?php if (empty($posts)): ?>
    <div class="text-center py-12 bg-gray-50 rounded-2xl">
      <p class="text-gray-500">Nenhum artigo nesta categoria ainda.</p>
      <a href="<?= base_url('blog') ?>" class="text-emerald-600 hover:underline mt-2 inline-block">Voltar ao blog</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($posts as $post): ?>
      <article class="bg-white rounded-2xl shadow-sm border hover:shadow-md transition group overflow-hidden">
        <a href="<?= base_url('blog/' . $post['slug']) ?>">
          <div class="aspect-video overflow-hidden">
            <img src="<?= !empty($post['featured_image']) ? esc($post['featured_image']) : base_url('assets/images/blog-default.jpg') ?>"
              alt="<?= esc($post['image_alt'] ?? $post['title']) ?>"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
          </div>
        </a>
        <div class="p-5">
          <a href="<?= base_url('blog/' . $post['slug']) ?>">
            <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-600 transition leading-tight"><?= esc($post['title']) ?></h3>
          </a>
          <p class="text-gray-500 text-sm mt-2 line-clamp-2"><?= esc(mb_substr($post['excerpt'] ?? '', 0, 120)) ?></p>
          <div class="flex items-center justify-between mt-4 text-xs text-gray-400">
            <span><i class="far fa-calendar mr-1"></i><?= $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : '' ?></span>
            <span><i class="far fa-clock mr-1"></i><?= $post['reading_time'] ?? 1 ?> min</span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
