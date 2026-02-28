<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900">

    <!-- Hero Section -->
    <div class="relative overflow-hidden py-12 lg:py-20">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,...')] opacity-10"></div>

        <div class="container-custom relative z-10">
            <div class="text-center mb-12">
                <span class="inline-flex items-center px-4 py-2 bg-yellow-500/20 text-yellow-300 rounded-full text-sm font-semibold mb-4">
                    <i class="fas fa-star mr-2"></i> CONCORRA A PREMIOS INCRIVEIS
                </span>
                <h1 class="text-4xl md:text-6xl font-black text-white mb-4">
                    <?= esc($raffle['title']) ?>
                </h1>
                <p class="text-xl text-teal-200 max-w-3xl mx-auto">
                    <?= esc($raffle['description'] ?? 'Compre suas cotas, ajude campanhas sociais e concorra a premios!') ?>
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-center border border-white/20">
                    <div class="text-3xl md:text-4xl font-black text-green-400 mb-2">
                        R$ <?= number_format($stats['main_prize'], 0, ',', '.') ?>
                    </div>
                    <div class="text-teal-200 text-sm">Premio Atual</div>
                </div>
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-center border border-white/20">
                    <div class="text-3xl md:text-4xl font-black text-yellow-400 mb-2">
                        <?= number_format($stats['numbers_sold'], 0, ',', '.') ?>
                    </div>
                    <div class="text-teal-200 text-sm">Cotas Vendidas</div>
                </div>
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-center border border-white/20">
                    <div class="text-3xl md:text-4xl font-black text-blue-400 mb-2">
                        <?= number_format($stats['percentage_sold'], 2) ?>%
                    </div>
                    <div class="text-teal-200 text-sm">Progresso</div>
                </div>
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-center border border-white/20">
                    <div class="text-3xl md:text-4xl font-black text-teal-400 mb-2">
                        <?= number_format($stats['numbers_available'], 0, ',', '.') ?>
                    </div>
                    <div class="text-teal-200 text-sm">Disponiveis</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="max-w-3xl mx-auto mb-12">
                <div class="bg-white/10 rounded-full h-6 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-green-500 via-yellow-500 to-green-500 rounded-full transition-all duration-1000 relative"
                         style="width: <?php
                             // Garantir largura mínima visível se houver vendas
                             $width = min($stats['percentage_sold'], 100);
                             if ($stats['numbers_sold'] > 0 && $width < 0.5) {
                                 $width = 0.5; // Largura mínima de 0.5% para ser visível
                             }
                             echo $width;
                         ?>%">
                        <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                </div>
                <div class="flex justify-between text-sm text-teal-300 mt-2">
                    <span>0</span>
                    <span class="font-bold text-white"><?= number_format($stats['percentage_sold'], 2) ?>% vendido</span>
                    <span><?= number_format($raffle['total_numbers'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-custom pb-20" x-data="rafflePurchaseForm()">
        <div class="grid lg:grid-cols-3 gap-8">

            <!-- Coluna Esquerda - Pacotes -->
            <div class="lg:col-span-2 space-y-8">

                <!-- Pacotes de Cotas -->
                <div class="bg-white rounded-3xl shadow-2xl p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-ticket-alt text-teal-500 mr-3"></i>
                        Escolha seu Pacote
                    </h2>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <?php foreach ($packages as $pkg): ?>
                        <div @click="selectPackage(<?= $pkg['quantity'] ?>, <?= $pkg['discount_price'] ?>, <?= $pkg['discount_percentage'] ?>)"
                             :class="quantity === <?= $pkg['quantity'] ?> ? 'ring-4 ring-purple-500 bg-emerald-50' : 'hover:border-emerald-300'"
                             class="relative cursor-pointer border-2 border-gray-200 rounded-2xl p-4 text-center transition-all <?= $pkg['is_popular'] ? 'border-yellow-400' : '' ?>">

                            <?php if ($pkg['is_popular']): ?>
                            <span class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-xs font-bold">
                                POPULAR
                            </span>
                            <?php endif; ?>

                            <div class="text-3xl font-black text-gray-800 mb-1"><?= $pkg['quantity'] ?></div>
                            <div class="text-sm text-gray-500 mb-2">cotas</div>

                            <?php if ($pkg['discount_percentage'] > 0): ?>
                            <div class="text-xs text-gray-400 line-through">R$ <?= number_format($pkg['original_price'], 2, ',', '.') ?></div>
                            <?php endif; ?>

                            <div class="text-xl font-bold text-green-600">R$ <?= number_format($pkg['discount_price'], 2, ',', '.') ?></div>

                            <?php if ($pkg['discount_percentage'] > 0): ?>
                            <span class="inline-block mt-2 bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">
                                -<?= $pkg['discount_percentage'] ?>%
                            </span>
                            <?php endif; ?>

                            <?php
                            $remaining = ($pkg['max_available'] !== null)
                                ? max(0, $pkg['max_available'] - ($pkg['sold_count'] ?? 0))
                                : null;
                            $threshold = ($pkg['max_available'] !== null) ? $pkg['max_available'] * 0.25 : 0;
                            ?>
                            <?php if ($remaining !== null): ?>
                            <div class="mt-2 text-xs <?php
                                if ($remaining <= 0) {
                                    echo 'text-red-600 font-bold';
                                } elseif ($remaining <= $threshold) {
                                    echo 'text-orange-600 font-semibold';
                                } else {
                                    echo 'text-green-600';
                                }
                            ?>">
                                <?php if ($remaining > 0): ?>
                                    Restam <?= number_format($remaining, 0, ',', '.') ?>
                                <?php else: ?>
                                    Esgotado
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Quantidade personalizada -->
                    <div class="border-t pt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ou digite a quantidade:</label>
                        <div class="flex items-center gap-4">
                            <input type="number" x-model="customQuantity" @input="calculateCustomPrice()"
                                   min="1" max="<?= $stats['numbers_available'] ?>"
                                   class="w-32 form-input text-center text-xl font-bold" placeholder="Ex: 15">
                            <button type="button" @click="useCustomQuantity()"
                                    class="px-6 py-3 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition">
                                Usar
                            </button>
                            <div x-show="customPrice > 0" class="text-lg">
                                Total: <span class="font-bold text-green-600" x-text="'R$ ' + customPrice.toFixed(2).replace('.', ',')"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selecionar Campanhas -->
                <div class="bg-white rounded-3xl shadow-2xl p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-heart text-red-500 mr-3"></i>
                        Apoie Campanhas (Opcional)
                    </h2>
                    <p class="text-gray-600 mb-6">40% do valor vai para as campanhas que voce escolher (max 5)</p>

                    <div class="grid md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
                        <?php foreach ($campaigns as $campaign): ?>
                        <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition"
                               :class="selectedCampaigns.includes(<?= $campaign['id'] ?>) ? 'border-primary-500 bg-primary-50' : 'border-gray-200'">
                            <input type="checkbox"
                                   value="<?= $campaign['id'] ?>"
                                   @change="toggleCampaign(<?= $campaign['id'] ?>)"
                                   :disabled="!selectedCampaigns.includes(<?= $campaign['id'] ?>) && selectedCampaigns.length >= 5"
                                   class="mt-1 rounded text-primary-600 focus:ring-primary-500">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 truncate"><?= esc($campaign['title']) ?></div>
                                <div class="text-sm text-gray-500">
                                    R$ <?= number_format($campaign['current_amount'], 0, ',', '.') ?> arrecadados
                                </div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4 text-sm text-gray-500" x-show="selectedCampaigns.length > 0">
                        <span x-text="selectedCampaigns.length"></span> campanha(s) selecionada(s)
                    </div>
                </div>

                <!-- Ranking -->
                <div class="bg-white rounded-3xl shadow-2xl p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-trophy text-yellow-500 mr-3"></i>
                        Top Compradores
                        <span class="ml-auto text-sm font-normal text-gray-500">Ganhe premios extras!</span>
                    </h2>

                    <div class="space-y-3">
                        <?php if (empty($ranking)): ?>
                        <p class="text-gray-500 text-center py-8">Seja o primeiro a comprar!</p>
                        <?php else: ?>
                        <?php
                        // Calcular prêmios do ranking dinamicamente
                        $mainPrizeRanking = !empty($raffle['main_prize_amount']) ? $raffle['main_prize_amount'] : $stats['main_prize'];
                        $rankingPrizesDisplay = \App\Models\RaffleRankingModel::getRankingPrizes($mainPrizeRanking);
                        ?>
                        <?php foreach ($ranking as $index => $entry): ?>
                        <div class="flex items-center gap-4 p-4 rounded-xl <?= $index < 3 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200' : 'bg-gray-50' ?>">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg
                                <?php if ($index === 0): ?> bg-yellow-400 text-yellow-900
                                <?php elseif ($index === 1): ?> bg-gray-300 text-gray-700
                                <?php elseif ($index === 2): ?> bg-orange-400 text-orange-900
                                <?php else: ?> bg-gray-200 text-gray-600 <?php endif; ?>">
                                <?= $index + 1 ?>
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800">
                                    <?= esc(substr($entry['buyer_name'], 0, 20)) ?>...
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?= number_format($entry['total_numbers'], 0, ',', '.') ?> cotas
                                </div>
                            </div>
                            <?php if ($index < 3): ?>
                            <div class="text-right">
                                <div class="text-green-600 font-bold">
                                    R$ <?= number_format($rankingPrizesDisplay[$index + 1] ?? 0, 0, ',', '.') ?>
                                </div>
                                <div class="text-xs text-gray-500">premio</div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Coluna Direita - Formulario de Compra (Sticky) -->
            <div class="lg:col-span-1">
                <div class="sticky top-24">
                    <form action="<?= base_url('rifas/comprar') ?>" method="POST"
                          class="bg-white rounded-3xl shadow-2xl p-8">
                        <?= csrf_field() ?>
                        <input type="hidden" name="raffle_id" value="<?= $raffle['id'] ?>">
                        <input type="hidden" name="quantity" x-model="quantity">
                        <template x-for="cid in selectedCampaigns" :key="cid">
                            <input type="hidden" name="campaigns[]" :value="cid">
                        </template>

                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-shopping-cart text-teal-500 mr-2"></i>
                            Finalizar Compra
                        </h3>

                        <!-- Resumo -->
                        <div class="bg-emerald-50 rounded-xl p-4 mb-6">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Quantidade:</span>
                                <span class="font-bold text-gray-800" x-text="quantity + ' cotas'"></span>
                            </div>
                            <div class="flex justify-between mb-2" x-show="discount > 0">
                                <span class="text-gray-600">Desconto:</span>
                                <span class="font-bold text-green-600" x-text="'-' + discount + '%'"></span>
                            </div>
                            <div class="flex justify-between text-lg border-t border-emerald-200 pt-2 mt-2">
                                <span class="font-semibold text-gray-800">Total:</span>
                                <span class="font-black text-2xl text-teal-600" x-text="'R$ ' + totalPrice.toFixed(2).replace('.', ',')"></span>
                            </div>
                        </div>

                        <!-- Dados do Comprador -->
                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                                <input type="text" name="buyer_name" required
                                       value="<?= esc($user['name'] ?? '') ?>"
                                       class="w-full form-input" placeholder="Seu nome">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                                <input type="email" name="buyer_email" required
                                       value="<?= esc($user['email'] ?? '') ?>"
                                       class="w-full form-input" placeholder="seu@email.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
                                <input type="text" name="buyer_cpf" required
                                       value="<?= esc($user['cpf'] ?? '') ?>"
                                       class="w-full form-input" placeholder="000.000.000-00"
                                       x-mask="999.999.999-99">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp (opcional)</label>
                                <input type="text" name="buyer_phone"
                                       value="<?= esc($user['phone'] ?? '') ?>"
                                       class="w-full form-input" placeholder="(00) 00000-0000"
                                       x-mask="(99) 99999-9999">
                            </div>
                        </div>

                        <!-- Botao de Compra -->
                        <button type="submit"
                                class="w-full py-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold text-lg rounded-xl hover:from-purple-700 hover:to-indigo-700 transition transform hover:scale-105 shadow-lg">
                            <i class="fas fa-bolt mr-2"></i>
                            COMPRAR AGORA
                        </button>

                        <div class="flex items-center justify-center gap-2 mt-4">
                            <i class="fas fa-lock text-gray-400"></i>
                            <span class="text-xs text-gray-500">Pagamento seguro via</span>
                            <img src="https://http2.mlstatic.com/frontend-assets/mp-web-navigation/ui-navigation/6.6.73/mercadopago/logo__large.png"
                                 alt="Mercado Pago" class="h-4">
                        </div>

                        <!-- Premios Top Compradores -->
                        <div class="mt-6 pt-6 border-t">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                                Premios Top Compradores
                            </h4>
                            <div class="text-sm text-gray-600 space-y-2">
                                <?php
                                // Calcular prêmios baseado no prêmio principal da rifa
                                $mainPrize = !empty($raffle['main_prize_amount']) ? $raffle['main_prize_amount'] : $stats['main_prize'];
                                $rankingPrizes = \App\Models\RaffleRankingModel::getRankingPrizes($mainPrize);
                                $medals = [1 => ['bg-yellow-400', 'text-yellow-900'], 2 => ['bg-gray-300', 'text-gray-700'], 3 => ['bg-orange-400', 'text-orange-900']];
                                foreach ($rankingPrizes as $position => $prize):
                                ?>
                                <div class="flex justify-between items-center">
                                    <span class="flex items-center gap-2">
                                        <span class="w-5 h-5 <?= $medals[$position][0] ?? 'bg-gray-200' ?> <?= $medals[$position][1] ?? 'text-gray-600' ?> rounded-full flex items-center justify-center text-xs font-bold"><?= $position ?></span>
                                        <?= $position ?>º lugar
                                    </span>
                                    <span class="font-bold text-green-600">R$ <?= number_format($prize, 0, ',', '.') ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 text-center">Quem comprar mais cotas ganha!</p>
                        </div>

                        <?php if (!empty($specialPrizes)): ?>
                        <!-- Premios Instantaneos (Cotas Premiadas) -->
                        <div class="mt-4 pt-4 border-t">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-gift text-pink-500 mr-2"></i>
                                Cotas Premiadas
                            </h4>
                            <div class="text-sm text-gray-600 space-y-1 max-h-32 overflow-y-auto">
                                <?php
                                // Agrupar por valor do prêmio
                                $groupedPrizes = [];
                                foreach ($specialPrizes as $prize) {
                                    $value = (float)$prize['prize_amount'];
                                    if (!isset($groupedPrizes[$value])) {
                                        $groupedPrizes[$value] = ['name' => $prize['prize_name'], 'numbers' => []];
                                    }
                                    $groupedPrizes[$value]['numbers'][] = $prize['number_pattern'];
                                }
                                krsort($groupedPrizes); // Ordenar por valor decrescente
                                foreach ($groupedPrizes as $value => $group):
                                    $numbersDisplay = count($group['numbers']) > 3
                                        ? implode(', ', array_slice($group['numbers'], 0, 3)) . '...'
                                        : implode(', ', $group['numbers']);
                                ?>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 truncate max-w-[60%]" title="<?= esc(implode(', ', $group['numbers'])) ?>">
                                        <?= esc($numbersDisplay) ?>
                                    </span>
                                    <span class="font-bold text-green-600">R$ <?= number_format($value, 0, ',', '.') ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 text-center">Premio instantaneo ao comprar!</p>
                        </div>
                        <?php endif; ?>
                    </form>

                    <!-- Meus Numeros (se logado) -->
                    <?php if (!empty($userNumbers)): ?>
                    <div class="bg-white rounded-3xl shadow-xl p-6 mt-6">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-ticket-alt text-teal-500 mr-2"></i>
                            Seus Numeros (<?= count($userNumbers) ?>)
                        </h4>
                        <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                            <?php foreach (array_slice($userNumbers, 0, 20) as $num): ?>
                            <span class="px-2 py-1 bg-emerald-100 text-teal-700 rounded font-mono text-sm <?= $num['is_special_prize'] ? 'bg-yellow-100 text-yellow-700 font-bold' : '' ?>">
                                <?= $num['number'] ?>
                            </span>
                            <?php endforeach; ?>
                            <?php if (count($userNumbers) > 20): ?>
                            <span class="text-gray-500 text-sm">+<?= count($userNumbers) - 20 ?> mais</span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= base_url('rifas/meus-numeros') ?>" class="text-teal-600 text-sm font-semibold hover:underline mt-3 inline-block">
                            Ver todos <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function rafflePurchaseForm() {
    return {
        quantity: 25,
        totalPrice: <?= $packages[3]['discount_price'] ?? 25 * floatval($raffle['number_price']) ?>,
        discount: <?= $packages[3]['discount_percentage'] ?? 10 ?>,
        customQuantity: '',
        customPrice: 0,
        selectedCampaigns: [],
        basePrice: <?= floatval($raffle['number_price']) ?>,

        packages: <?= json_encode($packages) ?>,

        selectPackage(qty, price, disc) {
            this.quantity = qty;
            this.totalPrice = price;
            this.discount = disc;
        },

        calculateCustomPrice() {
            const qty = parseInt(this.customQuantity) || 0;
            if (qty <= 0) {
                this.customPrice = 0;
                return;
            }

            // Encontrar melhor desconto
            let bestDiscount = 0;
            for (const pkg of this.packages) {
                if (pkg.quantity <= qty && pkg.discount_percentage > bestDiscount) {
                    bestDiscount = pkg.discount_percentage;
                }
            }

            this.customPrice = qty * this.basePrice * (1 - bestDiscount / 100);
        },

        useCustomQuantity() {
            const qty = parseInt(this.customQuantity) || 0;
            if (qty > 0) {
                this.calculateCustomPrice();
                this.quantity = qty;
                this.totalPrice = this.customPrice;

                // Calcular desconto
                let bestDiscount = 0;
                for (const pkg of this.packages) {
                    if (pkg.quantity <= qty && pkg.discount_percentage > bestDiscount) {
                        bestDiscount = pkg.discount_percentage;
                    }
                }
                this.discount = bestDiscount;
            }
        },

        toggleCampaign(id) {
            const index = this.selectedCampaigns.indexOf(id);
            if (index > -1) {
                this.selectedCampaigns.splice(index, 1);
            } else if (this.selectedCampaigns.length < 5) {
                this.selectedCampaigns.push(id);
            }
        }
    };
}
</script>

<!-- Widget Alex: abre direto no agente de rifas com contexto da rifa -->
<script>
    window.DoarFazBemChat = {
        agentId: 'doarfazbem-rifas',
        color: '#7c3aed',
        position: 'bottom-right',
        title: 'Alex',
        subtitle: '🎁 Compre suas cotas!',
        lang: 'pt',
        avatar: 'https://agents.hubflowai.com/avatars/alex.png',
        whatsappFallback: '5547992147138',
        welcome: 'Olá! 🎁 Você está na rifa "<?= esc($raffle['title'] ?? '') ?>". Posso te mostrar os pacotes de cotas e gerar o PIX na hora! Quantas cotas você quer comprar?',
        context: {
            page: 'raffle',
            raffleSlug: '<?= esc($raffle['slug'] ?? '') ?>',
            raffleTitle: '<?= esc($raffle['title'] ?? '') ?>'
        }
    };
</script>

<?= $this->endSection() ?>
