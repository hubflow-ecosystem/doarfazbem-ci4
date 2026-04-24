<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<!-- SEO Meta -->
<?php if (!empty($seoMeta)): ?>
  <?php /* Meta tags já aplicadas no layout via $title e $description */ ?>
<?php endif; ?>

<!-- Schema.org -->
<?= $breadcrumbSchema ?? '' ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <!-- Breadcrumb -->
  <nav class="mb-6">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
      <li><a href="<?= base_url() ?>" class="hover:text-emerald-600">Início</a></li>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li class="font-medium text-gray-700">Blog</li>
    </ol>
  </nav>

  <!-- Cabeçalho -->
  <div class="text-center mb-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-3">Blog DoarFazBem</h1>
    <p class="text-xl text-gray-500 max-w-2xl mx-auto">Histórias de solidariedade, dicas para campanhas e o impacto das doações na sociedade</p>
  </div>

  <!-- Posts Destaque -->
  <?php if (!empty($featuredPosts)): ?>
  <div class="mb-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($featuredPosts as $i => $post): ?>
      <a href="<?= base_url('blog/' . $post['slug']) ?>" class="group <?= $i === 0 ? 'md:col-span-2 md:row-span-2' : '' ?>">
        <div class="relative overflow-hidden rounded-2xl h-full">
          <img src="<?= !empty($post['featured_image']) ? esc($post['featured_image']) : base_url('assets/images/blog-default.jpg') ?>"
            alt="<?= esc($post['image_alt'] ?? $post['title']) ?>"
            class="w-full h-full object-cover <?= $i === 0 ? 'min-h-[400px]' : 'min-h-[190px]' ?> group-hover:scale-105 transition-transform duration-300">
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
          <div class="absolute bottom-0 left-0 right-0 p-6">
            <?php if (!empty($post['category_name'])): ?>
              <span class="inline-block px-3 py-1 bg-emerald-500 text-white text-xs font-medium rounded-full mb-2"><?= esc($post['category_name']) ?></span>
            <?php endif; ?>
            <h2 class="text-white font-bold <?= $i === 0 ? 'text-2xl' : 'text-lg' ?> leading-tight"><?= esc($post['title']) ?></h2>
            <?php if ($i === 0 && !empty($post['excerpt'])): ?>
              <p class="text-gray-200 text-sm mt-2"><?= esc(mb_substr($post['excerpt'], 0, 120)) ?>...</p>
            <?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Posts -->
    <div class="lg:col-span-2">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Artigos Recentes</h2>

      <?php if (empty($posts)): ?>
        <div class="text-center py-12 bg-gray-50 rounded-2xl">
          <i class="fas fa-newspaper text-4xl text-gray-300 mb-3"></i>
          <p class="text-gray-500">Nenhum artigo publicado ainda.</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <?php foreach ($posts as $post): ?>
          <article class="bg-white rounded-2xl shadow-sm border hover:shadow-md transition group overflow-hidden">
            <a href="<?= base_url('blog/' . $post['slug']) ?>">
              <div class="aspect-video overflow-hidden">
                <img src="<?= !empty($post['featured_image']) ? esc($post['featured_image']) : base_url('assets/images/blog-default.jpg') ?>"
                  alt="<?= esc($post['image_alt'] ?? $post['title']) ?>"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  loading="lazy">
              </div>
            </a>
            <div class="p-5">
              <?php if (!empty($post['category_name'])): ?>
                <a href="<?= base_url('blog/categoria/' . ($post['category_slug'] ?? '')) ?>" class="text-xs text-emerald-600 font-medium hover:underline"><?= esc($post['category_name']) ?></a>
              <?php endif; ?>
              <a href="<?= base_url('blog/' . $post['slug']) ?>">
                <h3 class="text-lg font-bold text-gray-900 mt-1 group-hover:text-emerald-600 transition leading-tight"><?= esc($post['title']) ?></h3>
              </a>
              <?php if (!empty($post['excerpt'])): ?>
                <p class="text-gray-500 text-sm mt-2 line-clamp-2"><?= esc(mb_substr($post['excerpt'], 0, 120)) ?></p>
              <?php endif; ?>
              <div class="flex items-center justify-between mt-4 text-xs text-gray-400">
                <span><i class="far fa-calendar mr-1"></i><?= $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : '' ?></span>
                <span><i class="far fa-clock mr-1"></i><?= $post['reading_time'] ?? 1 ?> min de leitura</span>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>

        <!-- Paginação -->
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="flex justify-center mt-10 space-x-2">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="px-4 py-2 rounded-lg text-sm <?= $i == ($currentPage ?? 1) ? 'bg-emerald-600 text-white' : 'bg-white border hover:bg-gray-50 text-gray-700' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <aside class="space-y-6">
      <!-- Busca -->
      <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h3 class="font-semibold text-gray-900 mb-3"><i class="fas fa-search text-emerald-600 mr-2"></i>Buscar</h3>
        <form action="<?= base_url('blog/buscar') ?>" method="get">
          <div class="flex">
            <input type="text" name="q" placeholder="Buscar artigos..." class="flex-1 px-4 py-2 border rounded-l-lg text-sm focus:border-emerald-500">
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-r-lg hover:bg-emerald-700"><i class="fas fa-search"></i></button>
          </div>
        </form>
      </div>

      <!-- Categorias -->
      <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h3 class="font-semibold text-gray-900 mb-3"><i class="fas fa-tags text-blue-600 mr-2"></i>Categorias</h3>
        <ul class="space-y-2">
          <?php foreach ($categories as $cat): ?>
          <li>
            <a href="<?= base_url('blog/categoria/' . $cat['slug']) ?>" class="flex items-center justify-between py-1.5 text-sm text-gray-600 hover:text-emerald-600 transition">
              <span><?= esc($cat['name']) ?></span>
              <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full"><?= $cat['post_count'] ?? 0 ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Posts Populares -->
      <?php if (!empty($popularPosts)): ?>
      <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h3 class="font-semibold text-gray-900 mb-3"><i class="fas fa-fire text-orange-500 mr-2"></i>Mais Lidos</h3>
        <ul class="space-y-3">
          <?php foreach ($popularPosts as $pop): ?>
          <li>
            <a href="<?= base_url('blog/' . $pop['slug']) ?>" class="flex items-start space-x-3 group">
              <img src="<?= !empty($pop['featured_image']) ? esc($pop['featured_image']) : base_url('assets/images/blog-default.jpg') ?>"
                alt="<?= esc($pop['title']) ?>" class="w-16 h-16 object-cover rounded-lg flex-shrink-0" loading="lazy">
              <div>
                <p class="text-sm font-medium text-gray-900 group-hover:text-emerald-600 transition line-clamp-2"><?= esc($pop['title']) ?></p>
                <p class="text-xs text-gray-400 mt-1"><i class="far fa-eye mr-1"></i><?= number_format($pop['views_count'] ?? 0) ?> views</p>
              </div>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <!-- Newsletter -->
      <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white">
        <h3 class="font-semibold mb-2"><i class="fas fa-envelope mr-2"></i>Newsletter</h3>
        <p class="text-sm text-emerald-100 mb-4">Receba dicas e histórias de solidariedade</p>
        <div class="flex flex-col gap-2">
          <input type="email" placeholder="Seu email..." class="px-4 py-2 rounded-lg text-gray-900 text-sm">
          <button class="px-4 py-2 bg-white text-emerald-700 rounded-lg font-medium text-sm hover:bg-emerald-50">Inscrever-se</button>
        </div>
      </div>

      <!-- RSS -->
      <div class="text-center">
        <a href="<?= base_url('blog/feed') ?>" class="text-sm text-orange-500 hover:text-orange-600">
          <i class="fas fa-rss mr-1"></i>Feed RSS
        </a>
      </div>
    </aside>
  </div>
</div>

<?= $this->endSection() ?>
