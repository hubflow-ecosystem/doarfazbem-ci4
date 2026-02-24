<?php
/**
 * Banner Global de Rifas + Notificações de Compras
 * Incluir no layout para exibir em todas as paginas
 *
 * Uso: <?= view('components/raffle_banner') ?>
 */

// Buscar rifa ativa
$raffleModel = new \App\Models\RaffleModel();
$activeRaffle = $raffleModel->getActiveRaffle();

if (!$activeRaffle) return;

$stats = $raffleModel->getStats($activeRaffle['id']);

// Prêmio principal: usa valor fixo cadastrado ou calcula por percentual
$numberPrice = $activeRaffle['number_price'] ?? 1.21;

if (!empty($activeRaffle['main_prize_amount'])) {
    // Valor fixo cadastrado no banco
    $mainPrize = $activeRaffle['main_prize_amount'];
} else {
    // Cálculo baseado no potencial total (100% vendido)
    $mainPrizePercentage = $activeRaffle['main_prize_percentage'] ?? 40;
    $totalNumbers = $activeRaffle['total_numbers'] ?? 1000000;
    $potentialRevenue = $totalNumbers * $numberPrice;
    $mainPrize = $potentialRevenue * ($mainPrizePercentage / 100);
}

// Calcular prêmios extras (10% do prêmio principal)
$totalExtraPrizes = $mainPrize * 0.10;

// Top Compradores (30% do total de prêmios extras)
$topPrizes = [
    ['position' => 1, 'amount' => $totalExtraPrizes * 0.15, 'color' => 'amber'],  // 15%
    ['position' => 2, 'amount' => $totalExtraPrizes * 0.10, 'color' => 'gray'],   // 10%
    ['position' => 3, 'amount' => $totalExtraPrizes * 0.05, 'color' => 'orange'], // 5%
];

// Buscar prêmios extras (cotas premiadas - 70% do total)
$db = \Config\Database::connect();
$specialPrizes = $db->table('raffle_special_prizes')
    ->where('raffle_id', $activeRaffle['id'])
    ->where('is_active', 1)
    ->orderBy('number_pattern', 'ASC')
    ->get()
    ->getResultArray();

// Nomes fictícios para as notificações
$nomes = ['Maria S.', 'João P.', 'Ana L.', 'Carlos M.', 'Fernanda R.', 'Pedro H.', 'Juliana C.', 'Lucas F.', 'Beatriz A.', 'Rafael O.', 'Camila T.', 'Bruno S.', 'Larissa M.', 'Thiago V.', 'Amanda G.'];
$cidades = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte', 'Curitiba', 'Porto Alegre', 'Salvador', 'Brasília', 'Fortaleza', 'Recife', 'Manaus'];
?>

<!-- ================================================ -->
<!-- NOTIFICAÇÕES DE COMPRAS (Canto inferior esquerdo) -->
<!-- ================================================ -->
<div x-data="purchaseNotifications()" x-init="startNotifications()" class="fixed bottom-4 left-4 z-50">
    <template x-for="(notification, index) in notifications" :key="notification.id">
        <div x-show="notification.visible"
             x-transition:enter="transition ease-out duration-500"
             x-transition:enter-start="opacity-0 transform -translate-x-full"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform -translate-x-full"
             :class="notification.type === 'comprou' ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-amber-500 to-orange-500'"
             class="mb-2 px-4 py-3 rounded-xl shadow-2xl text-white max-w-xs flex items-center gap-3 cursor-pointer hover:scale-105 transition-transform"
             @click="hideNotification(notification.id)">

            <!-- Avatar -->
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                <i class="fas" :class="notification.type === 'comprou' ? 'fa-check-circle' : 'fa-clock'"></i>
            </div>

            <!-- Conteúdo -->
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm truncate" x-text="notification.nome"></p>
                <p class="text-xs opacity-90">
                    <span x-text="notification.type === 'comprou' ? 'comprou' : 'reservou'"></span>
                    <span class="font-bold" x-text="notification.cotas + ' cota' + (notification.cotas > 1 ? 's' : '')"></span>
                </p>
                <p class="text-xs opacity-75" x-text="notification.cidade + ' • agora'"></p>
            </div>

            <!-- Ícone -->
            <div class="text-2xl">
                <i class="fas fa-ticket-alt"></i>
            </div>
        </div>
    </template>
</div>

<script>
function purchaseNotifications() {
    const nomes = <?= json_encode($nomes) ?>;
    const cidades = <?= json_encode($cidades) ?>;

    return {
        notifications: [],
        notificationId: 0,

        startNotifications() {
            // Primeira notificação após 5 segundos
            setTimeout(() => this.showRandomNotification(), 5000);
        },

        showRandomNotification() {
            const nome = nomes[Math.floor(Math.random() * nomes.length)];
            const cidade = cidades[Math.floor(Math.random() * cidades.length)];
            const cotas = Math.floor(Math.random() * 10) + 1; // 1 a 10 cotas
            const type = Math.random() > 0.3 ? 'comprou' : 'reservou'; // 70% comprou, 30% reservou

            const notification = {
                id: this.notificationId++,
                nome,
                cidade,
                cotas,
                type,
                visible: true
            };

            this.notifications.push(notification);

            // Remover após 4 segundos
            setTimeout(() => {
                this.hideNotification(notification.id);
            }, 4000);

            // Próxima notificação entre 8 e 20 segundos
            const nextDelay = Math.floor(Math.random() * 12000) + 8000;
            setTimeout(() => this.showRandomNotification(), nextDelay);
        },

        hideNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                notification.visible = false;
                // Remover do array após animação
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        }
    };
}

/**
 * Estado do Banner de Rifa com persistência em localStorage
 * - Minimizado: fica recolhido até clicar para expandir
 * - Fechado: fica oculto por 24h (ou até limpar o cache)
 */
function raffleBannerState() {
    return {
        show: true,
        minimized: false,
        showPrizes: false,

        initState() {
            const state = localStorage.getItem('raffleBannerState');
            if (state) {
                const parsed = JSON.parse(state);

                // Se foi fechado, verificar se já passou 24h
                if (parsed.closed) {
                    const closedAt = parsed.closedAt || 0;
                    const hoursPassed = (Date.now() - closedAt) / (1000 * 60 * 60);
                    if (hoursPassed < 24) {
                        this.show = false;
                        return;
                    } else {
                        // Passou 24h, resetar estado
                        localStorage.removeItem('raffleBannerState');
                    }
                }

                // Restaurar estado minimizado
                if (parsed.minimized) {
                    this.minimized = true;
                }
            }
        },

        minimize() {
            this.minimized = true;
            this.saveState();
        },

        expand() {
            this.minimized = false;
            this.saveState();
        },

        close() {
            this.show = false;
            localStorage.setItem('raffleBannerState', JSON.stringify({
                closed: true,
                closedAt: Date.now()
            }));
        },

        saveState() {
            localStorage.setItem('raffleBannerState', JSON.stringify({
                minimized: this.minimized,
                closed: false
            }));
        }
    };
}
</script>

<!-- ============================================== -->
<!-- BANNER FLUTUANTE DE RIFA (Lado direito da tela) -->
<!-- ============================================== -->
<div x-data="raffleBannerState()"
     x-init="initState()"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-full"
     x-transition:enter-end="opacity-100 transform translate-x-0"
     :class="minimized ? 'fixed right-4 top-1/2 -translate-y-1/2' : 'fixed bottom-4 right-4'"
     class="z-50">

    <!-- Versão Minimizada (Flutuante no meio lateral direito) -->
    <div x-show="minimized" @click="expand()"
         class="cursor-pointer bg-gradient-to-r from-orange-500 via-pink-500 to-purple-600 text-white px-5 py-3 rounded-full shadow-2xl hover:shadow-pink-500/50 transition-all flex items-center gap-3 hover:scale-110">
        <i class="fas fa-gift text-xl text-yellow-300 animate-pulse"></i>
        <span class="font-black text-lg">R$ <?= number_format($mainPrize, 0, ',', '.') ?></span>
        <i class="fas fa-chevron-left"></i>
    </div>

    <!-- Versão Completa -->
    <div x-show="!minimized"
         class="bg-white rounded-2xl shadow-2xl overflow-hidden w-80 border-4 border-transparent bg-clip-padding"
         style="background: linear-gradient(white, white) padding-box, linear-gradient(135deg, #f59e0b, #ec4899, #8b5cf6) border-box;">

        <!-- Header com cores vibrantes -->
        <div class="bg-gradient-to-r from-orange-500 via-pink-500 to-purple-600 p-4 relative overflow-hidden">
            <!-- Efeito de brilho -->
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -skew-x-12 animate-pulse"></div>

            <button @click="minimize()"
                    class="absolute top-2 right-2 w-7 h-7 bg-white/30 rounded-full flex items-center justify-center text-white hover:bg-white/50 transition z-10">
                <i class="fas fa-minus text-xs"></i>
            </button>
            <button @click="close()"
                    class="absolute top-2 right-11 w-7 h-7 bg-white/30 rounded-full flex items-center justify-center text-white hover:bg-white/50 transition z-10">
                <i class="fas fa-times text-xs"></i>
            </button>

            <div class="flex items-center gap-3 relative z-10">
                <div class="w-14 h-14 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg animate-pulse">
                    <i class="fas fa-gift text-orange-600 text-2xl"></i>
                </div>
                <div>
                    <p class="text-yellow-200 text-xs font-bold tracking-wider">🎄 ESPECIAL NATAL</p>
                    <p class="text-white font-black text-xl"><?= esc($activeRaffle['title']) ?></p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-5 bg-gradient-to-b from-gray-50 to-white">
            <!-- Prêmio do Ganhador -->
            <div class="text-center mb-3">
                <p class="text-gray-500 text-xs uppercase tracking-wide font-semibold">🏆 Prêmio Principal</p>
                <div class="bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl p-3 mt-2 shadow-lg">
                    <p class="text-4xl font-black text-white drop-shadow-lg">
                        R$ <?= number_format($mainPrize, 0, ',', '.') ?>
                    </p>
                </div>
            </div>

            <?php if ($totalExtraPrizes > 0): ?>
            <!-- Link para prêmios extras -->
            <div class="text-center mb-3">
                <button @click="showPrizes = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-amber-400 to-orange-500 text-white font-bold text-sm rounded-full hover:scale-105 transition-transform shadow-lg animate-pulse">
                    <i class="fas fa-star"></i>
                    + R$ <?= number_format($totalExtraPrizes, 0, ',', '.') ?> em prêmios extras!
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </div>
            <?php endif; ?>

            <!-- Destino dos valores: 50% Ganhador / 50% Campanhas+Despesas -->
            <div class="flex gap-2 mb-3 text-xs">
                <div class="flex-1 bg-gradient-to-br from-green-50 to-emerald-100 rounded-lg p-2 text-center border border-green-300">
                    <p class="text-green-600 font-bold text-lg">50%</p>
                    <p class="text-green-700 font-semibold">Você ganha</p>
                </div>
                <div class="flex-1 bg-gradient-to-br from-pink-50 to-purple-100 rounded-lg p-2 text-center border border-pink-300">
                    <p class="text-pink-600 font-bold text-lg">50%</p>
                    <p class="text-pink-700 font-semibold">Campanhas</p>
                </div>
            </div>

            <!-- Progress com cores vibrantes -->
            <div class="mb-4">
                <div class="flex justify-between text-sm font-semibold mb-2">
                    <span class="text-gray-700">
                        <i class="fas fa-ticket-alt text-pink-500 mr-1"></i>
                        <?= number_format($stats['numbers_sold'], 0, ',', '.') ?> vendidas
                    </span>
                    <span class="text-purple-600 font-bold"><?= number_format($stats['percentage_sold'], 1) ?>%</span>
                </div>
                <div class="bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-orange-400 via-pink-500 to-purple-600 h-full rounded-full relative"
                         style="width: <?= min($stats['percentage_sold'], 100) ?>%">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-pulse"></div>
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <a href="<?= base_url('rifas/' . $activeRaffle['slug']) ?>"
               class="group block w-full py-4 bg-gradient-to-r from-orange-500 via-pink-500 to-purple-600 text-white font-black text-lg rounded-xl text-center shadow-xl hover:shadow-2xl hover:shadow-pink-500/50 transition-all transform hover:scale-105 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -skew-x-12 group-hover:animate-pulse"></div>
                <span class="relative z-10">
                    <i class="fas fa-bolt mr-2 text-yellow-300"></i>
                    A partir de R$ <?= number_format($numberPrice, 2, ',', '.') ?>
                </span>
            </a>

            <!-- Info - Mercado Pago -->
            <div class="flex items-center justify-center gap-2 mt-3">
                <i class="fas fa-shield-alt text-green-500"></i>
                <span class="text-xs text-gray-500">Pagamento seguro via</span>
                <img src="https://http2.mlstatic.com/frontend-assets/mp-web-navigation/ui-navigation/6.6.73/mercadopago/logo__large.png"
                     alt="Mercado Pago"
                     class="h-4">
            </div>
        </div>
    </div>

    <!-- Modal de Prêmios Extras -->
    <?php if ($totalExtraPrizes > 0): ?>
    <div x-show="showPrizes"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showPrizes = false"
         class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-[60] p-4">

        <div x-show="showPrizes"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-90"
             @click.stop
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[80vh] overflow-hidden">

            <!-- Header do Modal -->
            <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 p-5 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -skew-x-12 animate-pulse"></div>
                <button @click="showPrizes = false"
                        class="absolute top-3 right-3 w-8 h-8 bg-white/30 rounded-full flex items-center justify-center text-white hover:bg-white/50 transition z-10">
                    <i class="fas fa-times"></i>
                </button>
                <div class="relative z-10 text-center">
                    <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-star text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-white">🎁 Prêmios Extras</h3>
                    <p class="text-yellow-200 text-sm mt-1">Além do prêmio principal de R$ <?= number_format($mainPrize, 0, ',', '.') ?></p>
                </div>
            </div>

            <!-- Lista de Prêmios -->
            <div class="p-5 overflow-y-auto max-h-[50vh]">
                <!-- Top Compradores -->
                <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-medal text-yellow-500"></i>
                    Top Compradores
                </h4>
                <div class="space-y-2 mb-6">
                    <?php foreach ($topPrizes as $top): ?>
                    <div class="flex items-center gap-3 p-3 bg-<?= $top['color'] ?>-50 rounded-lg border border-<?= $top['color'] ?>-200">
                        <div class="w-10 h-10 rounded-full bg-<?= $top['color'] ?>-500 text-white flex items-center justify-center font-black">
                            <?= $top['position'] ?>º
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800">R$ <?= number_format($top['amount'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cotas Premiadas -->
                <?php if (!empty($specialPrizes)): ?>
                <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-gift text-purple-500"></i>
                    Cotas Premiadas
                </h4>
                <div class="space-y-2">
                    <?php foreach ($specialPrizes as $index => $prize): ?>
                    <div class="flex items-center gap-3 p-3 bg-purple-50 rounded-lg border border-purple-200">
                        <div class="w-10 h-10 rounded-full bg-purple-500 text-white flex items-center justify-center">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800">R$ <?= number_format($prize['prize_amount'], 0, ',', '.') ?></p>
                            <p class="text-xs text-gray-500">Número <span class="font-mono font-bold text-purple-600"><?= esc($prize['number_pattern']) ?></span></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Total Geral -->
            <div class="px-5 pb-3">
                <div class="p-4 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl text-center text-white">
                    <p class="text-sm opacity-90">Total em prêmios extras</p>
                    <p class="text-3xl font-black">R$ <?= number_format($totalExtraPrizes, 0, ',', '.') ?></p>
                    <p class="text-xs opacity-75 mt-1">Top 3 Compradores + Cotas Premiadas</p>
                </div>
            </div>

            <!-- Footer do Modal -->
            <div class="p-5 bg-gray-50 border-t">
                <a href="<?= base_url('rifas/' . $activeRaffle['slug']) ?>"
                   class="block w-full py-4 bg-gradient-to-r from-orange-500 via-pink-500 to-purple-600 text-white font-black text-lg rounded-xl text-center shadow-xl hover:shadow-2xl hover:shadow-pink-500/50 transition-all transform hover:scale-105">
                    <i class="fas fa-bolt mr-2 text-yellow-300"></i>
                    Comprar Números Agora!
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
