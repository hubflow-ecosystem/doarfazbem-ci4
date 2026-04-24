<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>

<!-- Schema.org -->
<?= $articleSchema ?? '' ?>
<?= $breadcrumbSchema ?? '' ?>
<?= $faqSchema ?? '' ?>

<div class="max-w-7xl mx-auto px-4 py-8">
  <!-- Breadcrumb -->
  <nav class="mb-6">
    <ol class="flex items-center flex-wrap space-x-2 text-sm text-gray-500">
      <li><a href="<?= base_url() ?>" class="hover:text-emerald-600">Início</a></li>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li><a href="<?= base_url('blog') ?>" class="hover:text-emerald-600">Blog</a></li>
      <?php if ($category): ?>
        <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
        <li><a href="<?= base_url('blog/categoria/' . $category['slug']) ?>" class="hover:text-emerald-600"><?= esc($category['name']) ?></a></li>
      <?php endif; ?>
      <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
      <li class="font-medium text-gray-700 truncate max-w-[200px]"><?= esc($post['title']) ?></li>
    </ol>
  </nav>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Artigo Principal -->
    <article class="lg:col-span-2">
      <!-- Header -->
      <header class="mb-8">
        <?php if ($category): ?>
          <a href="<?= base_url('blog/categoria/' . $category['slug']) ?>" class="inline-block px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-medium rounded-full mb-3 hover:bg-emerald-200">
            <?= esc($category['name']) ?>
          </a>
        <?php endif; ?>

        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 leading-tight mb-4"><?= esc($post['title']) ?></h1>

        <div class="flex items-center flex-wrap gap-4 text-sm text-gray-500">
          <span><i class="far fa-calendar mr-1"></i><?= $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : '' ?></span>
          <span><i class="far fa-clock mr-1"></i><?= $post['reading_time'] ?? 1 ?> min de leitura</span>
          <span><i class="far fa-eye mr-1"></i><?= number_format($post['views_count'] ?? 0) ?> visualizações</span>
          <?php if (!empty($post['author_name'])): ?>
            <span><i class="far fa-user mr-1"></i><?= esc($post['author_name']) ?></span>
          <?php endif; ?>
        </div>
      </header>

      <!-- Imagem Destaque -->
      <?php if (!empty($post['featured_image'])): ?>
      <figure class="mb-8">
        <img src="<?= esc($post['featured_image']) ?>"
          alt="<?= esc($post['image_alt'] ?? $post['title']) ?>"
          class="w-full rounded-2xl shadow-lg object-cover max-h-[500px]"
          loading="eager">
        <?php if (!empty($post['image_caption'])): ?>
          <figcaption class="text-sm text-gray-400 text-center mt-2">
            <?= esc($post['image_caption']) ?>
            <?php if (!empty($post['image_credit'])): ?>
              — <span class="italic"><?= esc($post['image_credit']) ?></span>
            <?php endif; ?>
          </figcaption>
        <?php endif; ?>
      </figure>
      <?php endif; ?>

      <!-- Conteúdo (com auto-linking de keywords) -->
      <div class="prose prose-lg max-w-none prose-headings:text-gray-900 prose-a:text-emerald-600 prose-img:rounded-xl">
        <?= $linkedContent ?? $post['content'] ?>
      </div>

      <!-- Tags -->
      <?php
        $tags = !empty($post['tags']) ? (is_string($post['tags']) ? json_decode($post['tags'], true) : $post['tags']) : [];
      ?>
      <?php if (!empty($tags) && is_array($tags)): ?>
      <div class="mt-8 pt-6 border-t">
        <div class="flex items-center flex-wrap gap-2">
          <i class="fas fa-tags text-gray-400 mr-1"></i>
          <?php foreach ($tags as $tag): ?>
            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full"><?= esc($tag) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Compartilhar -->
      <div class="mt-8 pt-6 border-t">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Compartilhe este artigo:</h3>
        <div class="flex items-center space-x-3">
          <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' - ' . base_url('blog/' . $post['slug'])) ?>" target="_blank"
            class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center hover:bg-green-600 transition">
            <i class="fab fa-whatsapp text-lg"></i>
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(base_url('blog/' . $post['slug'])) ?>" target="_blank"
            class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode(base_url('blog/' . $post['slug'])) ?>" target="_blank"
            class="w-10 h-10 bg-gray-900 text-white rounded-full flex items-center justify-center hover:bg-gray-800 transition">
            <i class="fab fa-x-twitter"></i>
          </a>
          <button onclick="navigator.clipboard.writeText('<?= base_url('blog/' . $post['slug']) ?>')" title="Copiar link"
            class="w-10 h-10 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-300 transition">
            <i class="fas fa-link"></i>
          </button>
        </div>
      </div>

      <!-- Artigos Relacionados -->
      <?php if (!empty($relatedPosts)): ?>
      <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Artigos Relacionados</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach ($relatedPosts as $rp): ?>
          <a href="<?= base_url('blog/' . $rp['slug']) ?>" class="group">
            <div class="aspect-video overflow-hidden rounded-xl mb-3">
              <img src="<?= !empty($rp['featured_image']) ? esc($rp['featured_image']) : base_url('assets/images/blog-default.jpg') ?>"
                alt="<?= esc($rp['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-emerald-600 transition line-clamp-2"><?= esc($rp['title']) ?></h3>
            <p class="text-xs text-gray-400 mt-1"><?= $rp['published_at'] ? date('d/m/Y', strtotime($rp['published_at'])) : '' ?></p>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Campanhas Relacionadas -->
      <?php if (!empty($relatedCampaigns)): ?>
      <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
          <i class="fas fa-heart text-emerald-500 mr-2"></i>Campanhas que Você Pode Apoiar
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach ($relatedCampaigns as $camp): ?>
          <a href="<?= base_url('campaigns/' . ($camp['slug'] ?? $camp['id'])) ?>" class="group bg-white rounded-xl shadow-sm border hover:shadow-md transition overflow-hidden">
            <?php if (!empty($camp['featured_image'])): ?>
            <div class="aspect-video overflow-hidden">
              <img src="<?= esc($camp['featured_image']) ?>" alt="<?= esc($camp['title']) ?>"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
            </div>
            <?php else: ?>
            <div class="aspect-video bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center">
              <i class="fas fa-hand-holding-heart text-emerald-400 text-3xl"></i>
            </div>
            <?php endif; ?>
            <div class="p-4">
              <span class="inline-block px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 mb-2"><?= esc(ucfirst($camp['category'] ?? 'social')) ?></span>
              <h3 class="font-semibold text-gray-900 group-hover:text-emerald-600 transition line-clamp-2 text-sm"><?= esc($camp['title']) ?></h3>
              <?php
                $percentage = ($camp['goal_amount'] ?? 0) > 0 ? min(100, round(($camp['current_amount'] ?? 0) / $camp['goal_amount'] * 100)) : 0;
              ?>
              <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-emerald-500 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                  <span>R$ <?= number_format($camp['current_amount'] ?? 0, 0, ',', '.') ?></span>
                  <span><?= $percentage ?>%</span>
                </div>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </article>

    <!-- Sidebar -->
    <aside class="space-y-6">
      <div class="bg-white rounded-2xl shadow-sm border p-6">
        <h3 class="font-semibold text-gray-900 mb-3"><i class="fas fa-tags text-blue-600 mr-2"></i>Categorias</h3>
        <ul class="space-y-2">
          <?php foreach ($categories as $cat): ?>
          <li>
            <a href="<?= base_url('blog/categoria/' . $cat['slug']) ?>" class="text-sm text-gray-600 hover:text-emerald-600 transition"><?= esc($cat['name']) ?></a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Widget Rifa Ativa -->
      <?php if (!empty($raffleWidget)): ?>
      <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 text-white">
        <div class="flex items-center space-x-2 mb-3">
          <i class="fas fa-ticket-alt text-yellow-300 text-xl"></i>
          <h3 class="font-bold text-lg">Rifa Ativa!</h3>
        </div>
        <p class="text-sm text-purple-100 mb-2"><?= esc($raffleWidget['title'] ?? 'Concorra a prêmios incríveis') ?></p>
        <?php if (!empty($raffleWidget['prize_description'])): ?>
          <p class="text-xs text-purple-200 mb-3"><?= esc($raffleWidget['prize_description']) ?></p>
        <?php endif; ?>
        <a href="<?= base_url('rifas/' . ($raffleWidget['slug'] ?? '')) ?>" class="block text-center px-4 py-3 bg-yellow-400 text-purple-900 rounded-lg font-bold text-sm hover:bg-yellow-300 transition">
          <i class="fas fa-star mr-1"></i>Participar Agora
        </a>
      </div>
      <?php endif; ?>

      <!-- CTA Criar Campanha -->
      <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white">
        <h3 class="font-bold text-lg mb-2">Precisa de ajuda?</h3>
        <p class="text-sm text-emerald-100 mb-4">Crie uma campanha de doação e receba apoio de todo o Brasil.</p>
        <a href="<?= base_url('campaigns/create') ?>" class="block text-center px-4 py-3 bg-white text-emerald-700 rounded-lg font-semibold text-sm hover:bg-emerald-50 transition">
          Criar Campanha Grátis
        </a>
      </div>
    </aside>
  </div>
</div>

<?= $this->endSection() ?>
