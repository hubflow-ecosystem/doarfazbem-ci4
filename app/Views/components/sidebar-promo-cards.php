<?php
/**
 * Sidebar Promo Cards com auto-scroll.
 *
 * Uso: <?= view('components/sidebar-promo-cards') ?>
 *
 * Mostra cards promocionais das campanhas ativas, rifas e serviços do ecossistema.
 * Auto-scroll a cada 5 segundos com animação suave.
 */

// Carregar campanhas ativas do banco
$db = \Config\Database::connect();
$campaigns = $db->table('campaigns')
  ->where('status', 'active')
  ->orderBy('RAND()')
  ->limit(4)
  ->get()
  ->getResultArray();

$raffles = $db->table('raffles')
  ->where('status', 'active')
  ->orderBy('RAND()')
  ->limit(2)
  ->get()
  ->getResultArray();
?>

<div x-data="promoCards()" class="space-y-4">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">
      <i class="fas fa-fire text-orange-500 mr-1"></i>Em destaque
    </h3>
    <div class="flex gap-1">
      <button @click="prev()" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xs text-gray-500">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button @click="next()" class="w-6 h-6 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-xs text-gray-500">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>

  <!-- Cards Container -->
  <div class="overflow-hidden rounded-xl">
    <div class="transition-transform duration-500 ease-in-out"
         :style="'transform: translateY(-' + (currentIndex * 100) + '%)'">

      <?php if (!empty($campaigns)): ?>
        <?php foreach ($campaigns as $camp): ?>
        <div class="mb-4">
          <a href="/campaigns/<?= esc($camp['slug'] ?? $camp['id']) ?>" class="block rounded-xl border hover:border-emerald-300 hover:shadow-md transition-all overflow-hidden">
            <?php if (!empty($camp['cover_image'])): ?>
            <img src="<?= esc($camp['cover_image']) ?>" alt="<?= esc($camp['title']) ?>" class="w-full h-32 object-cover" loading="lazy">
            <?php else: ?>
            <div class="w-full h-32 bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center">
              <i class="fas fa-hand-holding-heart text-white text-3xl"></i>
            </div>
            <?php endif; ?>
            <div class="p-3">
              <p class="font-medium text-gray-900 text-sm line-clamp-2 mb-2"><?= esc($camp['title']) ?></p>
              <?php
                $goal = (float)($camp['goal_amount'] ?? 0);
                $raised = (float)($camp['current_amount'] ?? 0);
                $pct = $goal > 0 ? min(100, round(($raised / $goal) * 100)) : 0;
              ?>
              <div class="w-full bg-gray-100 rounded-full h-1.5 mb-1">
                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>R$ <?= number_format($raised, 0, ',', '.') ?></span>
                <span><?= $pct ?>%</span>
              </div>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (!empty($raffles)): ?>
        <?php foreach ($raffles as $raffle): ?>
        <div class="mb-4">
          <a href="/rifas/<?= esc($raffle['slug'] ?? $raffle['id']) ?>" class="block rounded-xl border border-purple-200 bg-purple-50 hover:border-purple-300 hover:shadow-md transition-all overflow-hidden">
            <div class="p-4">
              <span class="inline-block px-2 py-0.5 bg-purple-600 text-white text-[10px] font-bold uppercase rounded mb-2">
                <i class="fas fa-ticket-alt mr-1"></i>Rifa Ativa
              </span>
              <p class="font-medium text-gray-900 text-sm line-clamp-2 mb-2"><?= esc($raffle['title'] ?? 'Rifa Solidária') ?></p>
              <p class="text-xs text-purple-600 font-medium">
                A partir de R$ <?= number_format((float)($raffle['price_per_number'] ?? 0), 2, ',', '.') ?>
              </p>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Card Fixo: Criar Campanha -->
      <div class="mb-4">
        <a href="/campaigns/create" class="block rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-4 text-white hover:from-emerald-600 hover:to-teal-700 transition-all">
          <i class="fas fa-plus-circle text-2xl mb-2"></i>
          <p class="font-bold text-sm">Crie sua campanha</p>
          <p class="text-emerald-100 text-xs mt-1">Grátis, rápido e seguro. Comece a arrecadar em 5 minutos.</p>
        </a>
      </div>

      <!-- Card Fixo: Ecossistema HubFlow -->
      <div class="mb-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Tecnologia por</p>
          <div class="space-y-2">
            <a href="https://hubflowai.com" target="_blank" rel="noopener" class="flex items-center gap-2 text-xs text-gray-600 hover:text-blue-600">
              <i class="fas fa-robot text-blue-500"></i>
              <span>HubFlow AI — Automação inteligente</span>
            </a>
            <a href="https://socialflowia.com" target="_blank" rel="noopener" class="flex items-center gap-2 text-xs text-gray-600 hover:text-purple-600">
              <i class="fas fa-share-alt text-purple-500"></i>
              <span>SocialFlow — Redes sociais com IA</span>
            </a>
            <a href="https://agents.hubflowai.com" target="_blank" rel="noopener" class="flex items-center gap-2 text-xs text-gray-600 hover:text-green-600">
              <i class="fas fa-comments text-green-500"></i>
              <span>AgentsFlow — Atendimento WhatsApp IA</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Indicadores -->
  <div class="flex justify-center gap-1.5">
    <template x-for="(_, i) in totalCards" :key="i">
      <button @click="goTo(i)"
              :class="currentIndex === i ? 'bg-emerald-500 w-4' : 'bg-gray-300 w-1.5'"
              class="h-1.5 rounded-full transition-all duration-300"></button>
    </template>
  </div>
</div>

<script>
function promoCards() {
  return {
    currentIndex: 0,
    totalCards: <?= count($campaigns) + count($raffles) + 2 ?>,
    autoPlayInterval: null,
    init() {
      this.startAutoPlay();
    },
    startAutoPlay() {
      this.autoPlayInterval = setInterval(() => this.next(), 5000);
    },
    stopAutoPlay() {
      clearInterval(this.autoPlayInterval);
    },
    next() {
      this.currentIndex = (this.currentIndex + 1) % this.totalCards;
    },
    prev() {
      this.currentIndex = (this.currentIndex - 1 + this.totalCards) % this.totalCards;
    },
    goTo(i) {
      this.currentIndex = i;
      this.stopAutoPlay();
      this.startAutoPlay();
    }
  };
}
</script>
