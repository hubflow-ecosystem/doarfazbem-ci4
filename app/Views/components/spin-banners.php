<?php
/**
 * Banners SPIN Selling para artigos do blog.
 *
 * Uso: <?= view('components/spin-banners', ['position' => 'mid-article', 'category' => 'doacoes']) ?>
 *
 * Posições: 'mid-article', 'end-article', 'sidebar'
 * Categorias: 'doacoes', 'campanhas', 'rifas', 'transparencia', 'impacto', 'historias'
 */

$position = $position ?? 'mid-article';
$category = $category ?? 'doacoes';

// Banners rotativos SPIN (Situação → Problema → Implicação → Necessidade)
$banners = [
  'doacoes' => [
    [
      'situacao' => 'Quer ajudar, mas não sabe por onde começar?',
      'problema' => 'Muitas plataformas cobram taxas abusivas ou não são transparentes.',
      'cta' => 'Doe com segurança no DoarFazBem',
      'cta_url' => '/campaigns',
      'cta_icon' => 'fa-heart',
      'color' => 'emerald',
    ],
    [
      'situacao' => 'Sabia que R$10 por mês muda uma vida?',
      'problema' => 'Uma doação única é boa, mas a recorrência transforma.',
      'cta' => 'Encontre uma causa para apoiar',
      'cta_url' => '/campaigns',
      'cta_icon' => 'fa-hand-holding-heart',
      'color' => 'emerald',
    ],
  ],
  'campanhas' => [
    [
      'situacao' => 'Precisa arrecadar dinheiro para uma causa?',
      'problema' => 'Criar uma campanha sozinho pode ser complicado e demorado.',
      'cta' => 'Crie sua campanha em 5 minutos',
      'cta_url' => '/campaigns/create',
      'cta_icon' => 'fa-rocket',
      'color' => 'blue',
    ],
    [
      'situacao' => 'Sua campanha não está arrecadando como esperava?',
      'problema' => 'Sem estratégia de divulgação, campanhas ficam invisíveis.',
      'cta' => 'Leia nossas dicas de divulgação',
      'cta_url' => '/blog/divulgar-campanha-doacao-redes-sociais',
      'cta_icon' => 'fa-bullhorn',
      'color' => 'blue',
    ],
  ],
  'rifas' => [
    [
      'situacao' => 'Quer arrecadar mais do que uma campanha comum?',
      'problema' => 'Rifas arrecadam até 10x mais, mas poucas pessoas sabem como criar.',
      'cta' => 'Veja as rifas ativas',
      'cta_url' => '/rifas',
      'cta_icon' => 'fa-ticket-alt',
      'color' => 'purple',
    ],
    [
      'situacao' => 'Sonha em concorrer a prêmios e ainda ajudar?',
      'problema' => 'Muitas rifas online não são transparentes ou legais.',
      'cta' => 'Participe de rifas verificadas',
      'cta_url' => '/rifas',
      'cta_icon' => 'fa-star',
      'color' => 'purple',
    ],
  ],
  'transparencia' => [
    [
      'situacao' => 'Tem medo de doar e o dinheiro não chegar?',
      'problema' => '38% dos brasileiros desconfiam de plataformas de doação.',
      'cta' => 'Conheça nossas garantias',
      'cta_url' => '/como-funciona',
      'cta_icon' => 'fa-shield-alt',
      'color' => 'teal',
    ],
  ],
  'impacto' => [
    [
      'situacao' => 'Quer saber se sua doação fez diferença?',
      'problema' => 'A maioria das plataformas não mostra o impacto real.',
      'cta' => 'Veja campanhas com resultados',
      'cta_url' => '/campaigns',
      'cta_icon' => 'fa-chart-line',
      'color' => 'orange',
    ],
  ],
  'historias' => [
    [
      'situacao' => 'Emocionado com esta história?',
      'problema' => 'Existem centenas de pessoas precisando agora mesmo.',
      'cta' => 'Ajude alguém hoje',
      'cta_url' => '/campaigns',
      'cta_icon' => 'fa-hands-helping',
      'color' => 'rose',
    ],
  ],
];

$categoryBanners = $banners[$category] ?? $banners['doacoes'];
$banner = $categoryBanners[array_rand($categoryBanners)];
$c = $banner['color'];
?>

<?php if ($position === 'mid-article'): ?>
<!-- Banner SPIN inline (meio do artigo) -->
<div class="my-8 rounded-2xl border-2 border-<?= $c ?>-200 bg-gradient-to-r from-<?= $c ?>-50 to-white p-6 md:p-8 not-prose">
  <p class="text-sm font-medium text-<?= $c ?>-600 mb-1"><?= $banner['situacao'] ?></p>
  <p class="text-gray-600 text-sm mb-4"><?= $banner['problema'] ?></p>
  <a href="<?= $banner['cta_url'] ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-<?= $c ?>-600 text-white rounded-lg font-medium text-sm hover:bg-<?= $c ?>-700 transition-colors">
    <i class="fas <?= $banner['cta_icon'] ?>"></i>
    <?= $banner['cta'] ?>
  </a>
</div>

<?php elseif ($position === 'end-article'): ?>
<!-- Banner SPIN CTA final (fim do artigo) -->
<div class="mt-10 rounded-2xl bg-gradient-to-br from-<?= $c ?>-600 to-<?= $c ?>-800 text-white p-8 md:p-10 not-prose">
  <h3 class="text-xl md:text-2xl font-bold mb-2"><?= $banner['situacao'] ?></h3>
  <p class="text-<?= $c ?>-100 mb-6"><?= $banner['problema'] ?></p>
  <div class="flex flex-wrap gap-3">
    <a href="<?= $banner['cta_url'] ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-<?= $c ?>-700 rounded-lg font-bold hover:bg-<?= $c ?>-50 transition-colors">
      <i class="fas <?= $banner['cta_icon'] ?>"></i>
      <?= $banner['cta'] ?>
    </a>
    <a href="/como-funciona" class="inline-flex items-center gap-2 px-6 py-3 border-2 border-white/30 text-white rounded-lg font-medium hover:bg-white/10 transition-colors">
      Como funciona <i class="fas fa-arrow-right ml-1"></i>
    </a>
  </div>
  <p class="text-xs text-<?= $c ?>-200 mt-4">Plataforma segura. Sem taxas escondidas. Transparência total.</p>
</div>

<?php elseif ($position === 'sidebar'): ?>
<!-- Banner SPIN sidebar (compacto) -->
<div class="rounded-xl border border-<?= $c ?>-200 bg-<?= $c ?>-50 p-5">
  <p class="text-xs font-semibold text-<?= $c ?>-600 uppercase tracking-wide mb-2"><?= $banner['situacao'] ?></p>
  <p class="text-sm text-gray-600 mb-3"><?= $banner['problema'] ?></p>
  <a href="<?= $banner['cta_url'] ?>" class="block w-full text-center px-4 py-2 bg-<?= $c ?>-600 text-white rounded-lg text-sm font-medium hover:bg-<?= $c ?>-700 transition-colors">
    <i class="fas <?= $banner['cta_icon'] ?> mr-1"></i>
    <?= $banner['cta'] ?>
  </a>
</div>
<?php endif; ?>
