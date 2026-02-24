<?php
/**
 * Popup de Rifa (para mostrar apos doacoes ou em eventos especificos)
 *
 * Uso: <?= view('components/raffle_popup', ['autoShow' => true, 'delay' => 2000]) ?>
 */

$autoShow = $autoShow ?? false;
$delay = $delay ?? 0;

// Buscar rifa ativa
$raffleModel = new \App\Models\RaffleModel();
$activeRaffle = $raffleModel->getActiveRaffle();

if (!$activeRaffle) return;

$stats = $raffleModel->getStats($activeRaffle['id']);
$mainPrize = $stats['main_prize'] ?? 0;
?>

<!-- Modal de Rifa -->
<div x-data="{ open: <?= $autoShow ? 'true' : 'false' ?> }"
     x-init="<?php if ($delay > 0 && $autoShow): ?>setTimeout(() => open = true, <?= $delay ?>)<?php endif; ?>"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[100] overflow-y-auto"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="open = false"></div>

    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="relative bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden">

            <!-- Botao Fechar -->
            <button @click="open = false"
                    class="absolute top-4 right-4 w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/30 transition z-10">
                <i class="fas fa-times text-xl"></i>
            </button>

            <!-- Confetti Animation (CSS) -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="confetti"></div>
            </div>

            <!-- Content -->
            <div class="relative p-8 text-center">
                <!-- Badge -->
                <span class="inline-flex items-center px-4 py-2 bg-yellow-500/30 text-yellow-300 rounded-full text-sm font-semibold mb-6 animate-pulse">
                    <i class="fas fa-star mr-2"></i> OFERTA ESPECIAL
                </span>

                <!-- Icone -->
                <div class="w-24 h-24 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-ticket-alt text-yellow-400 text-5xl"></i>
                </div>

                <!-- Titulo -->
                <h2 class="text-3xl font-black text-white mb-2">
                    Voce ja ajudou!
                </h2>
                <p class="text-xl text-purple-200 mb-6">
                    Agora concorra a premios incriveis
                </p>

                <!-- Premio -->
                <div class="bg-white/10 backdrop-blur rounded-2xl p-6 mb-6 border border-white/20">
                    <p class="text-purple-300 text-sm mb-1">Premio acumulado</p>
                    <p class="text-5xl font-black text-green-400 mb-2">
                        R$ <?= number_format($mainPrize, 0, ',', '.') ?>
                    </p>
                    <p class="text-purple-300 text-sm">
                        <i class="fas fa-chart-line mr-1"></i>
                        Cresce a cada venda!
                    </p>
                </div>

                <!-- Beneficios -->
                <div class="flex justify-center gap-6 mb-8 text-sm">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-hand-holding-heart text-green-400 text-xl"></i>
                        </div>
                        <p class="text-purple-200">40% para<br>campanhas</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-gift text-yellow-400 text-xl"></i>
                        </div>
                        <p class="text-purple-200">Premios<br>instantaneos</p>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-trophy text-blue-400 text-xl"></i>
                        </div>
                        <p class="text-purple-200">Top 3<br>ganham</p>
                    </div>
                </div>

                <!-- CTA -->
                <a href="<?= base_url('rifas/' . $activeRaffle['slug']) ?>"
                   class="block w-full py-4 bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-bold text-xl rounded-xl hover:from-yellow-600 hover:to-orange-600 transition transform hover:scale-105 shadow-lg mb-4">
                    <i class="fas fa-bolt mr-2"></i>
                    Comprar Cotas - A partir de R$ 1,10
                </a>

                <button @click="open = false" class="text-purple-300 hover:text-white transition">
                    Talvez mais tarde
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Animacao de confetti simples */
.confetti {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.confetti::before,
.confetti::after {
    content: '';
    position: absolute;
    width: 10px;
    height: 10px;
    background: linear-gradient(45deg, #ffd700, #ff6b6b, #4ecdc4, #45b7d1);
    animation: confetti-fall 3s ease-in-out infinite;
}
.confetti::before {
    left: 20%;
    animation-delay: 0s;
}
.confetti::after {
    left: 70%;
    animation-delay: 1.5s;
}
@keyframes confetti-fall {
    0% { transform: translateY(-100%) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}
</style>
