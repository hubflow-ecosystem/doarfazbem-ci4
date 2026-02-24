<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-green-500 via-emerald-600 to-teal-700 py-12">
    <div class="container-custom max-w-3xl">

        <!-- Animacao de Sucesso -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg animate-bounce">
                <i class="fas fa-check text-green-500 text-5xl"></i>
            </div>
            <h1 class="text-4xl font-black text-white mb-4">Pagamento Confirmado!</h1>
            <p class="text-xl text-green-100">Parabens! Seus numeros foram confirmados.</p>
        </div>

        <!-- Card com Numeros -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden mb-8">

            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <p class="text-teal-200 text-sm">Compra #<?= $purchase['id'] ?></p>
                        <p class="text-2xl font-bold"><?= $purchase['quantity'] ?> Cotas</p>
                    </div>
                    <div class="text-right">
                        <p class="text-teal-200 text-sm">Total pago</p>
                        <p class="text-2xl font-bold">R$ <?= number_format($purchase['total_amount'], 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>

            <!-- Numeros -->
            <div class="p-6">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-ticket-alt text-teal-500 mr-2"></i>
                    Seus Numeros da Sorte
                </h3>

                <div class="bg-emerald-50 rounded-xl p-4 mb-6">
                    <div class="flex flex-wrap gap-2 max-h-64 overflow-y-auto">
                        <?php foreach ($numbers as $num): ?>
                        <span class="px-3 py-2 rounded-lg font-mono text-sm font-bold
                            <?php if ($num['is_special_prize']): ?>
                                bg-yellow-200 text-yellow-800 border-2 border-yellow-400
                            <?php else: ?>
                                bg-white text-teal-700 border border-emerald-200
                            <?php endif; ?>">
                            <?= $num['number'] ?>
                            <?php if ($num['is_special_prize']): ?>
                                <i class="fas fa-star text-yellow-600 ml-1"></i>
                            <?php endif; ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($purchase['instant_prize_won'] > 0): ?>
                <!-- Premio Instantaneo! -->
                <div class="bg-gradient-to-r from-yellow-100 to-orange-100 border-2 border-yellow-400 rounded-xl p-6 mb-6 text-center">
                    <div class="animate-pulse">
                        <i class="fas fa-trophy text-yellow-500 text-5xl mb-4"></i>
                        <h4 class="text-2xl font-black text-yellow-800 mb-2">VOCE GANHOU!</h4>
                        <p class="text-yellow-700 mb-3">Um dos seus numeros e uma cota premiada!</p>
                        <p class="text-4xl font-black text-green-600">
                            R$ <?= number_format($purchase['instant_prize_won'], 2, ',', '.') ?>
                        </p>
                        <p class="text-sm text-yellow-600 mt-3">
                            O premio sera enviado via PIX em ate 48h
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Campanhas Beneficiadas -->
                <?php if (!empty($distributions)): ?>
                <div class="border-t pt-6">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-heart text-red-500 mr-2"></i>
                        Campanhas Beneficiadas
                    </h4>
                    <div class="space-y-3">
                        <?php foreach ($distributions as $dist): ?>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                            <span class="font-medium text-gray-700"><?= esc($dist['campaign_title']) ?></span>
                            <span class="font-bold text-green-600">R$ <?= number_format($dist['amount'], 2, ',', '.') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Acoes -->
            <div class="p-6 bg-gray-50 border-t">
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="<?= base_url('rifas') ?>"
                       class="flex-1 py-3 bg-emerald-600 text-white font-bold rounded-xl text-center hover:bg-emerald-700 transition">
                        <i class="fas fa-plus mr-2"></i> Comprar Mais Cotas
                    </a>
                    <a href="<?= base_url('rifas/meus-numeros') ?>"
                       class="flex-1 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl text-center hover:bg-gray-300 transition">
                        <i class="fas fa-list mr-2"></i> Ver Todos Meus Numeros
                    </a>
                </div>
            </div>
        </div>

        <!-- CTA Doação -->
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-white text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Que tal fazer ainda mais a diferenca?</h3>
                <p class="text-gray-600">Sua participacao ja ajuda muito! Mas voce pode transformar mais vidas com uma doacao direta.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <a href="<?= base_url('campaigns') ?>"
                   class="flex items-center gap-4 p-4 bg-gradient-to-r from-pink-50 to-red-50 border-2 border-pink-200 rounded-2xl hover:border-pink-400 transition group">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-red-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fas fa-hand-holding-heart text-white text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-800">Doar para Campanhas</div>
                        <div class="text-sm text-gray-500">Ajude quem mais precisa</div>
                    </div>
                    <i class="fas fa-arrow-right ml-auto text-pink-500"></i>
                </a>

                <a href="<?= base_url('doe-para-plataforma') ?>"
                   class="flex items-center gap-4 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 border-2 border-emerald-200 rounded-2xl hover:border-emerald-400 transition group">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fas fa-seedling text-white text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-800">Manter a Plataforma</div>
                        <div class="text-sm text-gray-500">Ajude o DoarFazBem a crescer</div>
                    </div>
                    <i class="fas fa-arrow-right ml-auto text-emerald-500"></i>
                </a>
            </div>
        </div>

        <!-- Banner de Compartilhamento -->
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 text-center border border-white/20">
            <h3 class="text-xl font-bold text-white mb-3">Compartilhe com amigos!</h3>
            <p class="text-green-100 mb-4">Convide seus amigos para concorrer tambem</p>
            <div class="flex justify-center gap-4">
                <a href="https://wa.me/?text=<?= urlencode('Estou concorrendo a premios incriveis no DoarFazBem! Participe voce tambem: ' . base_url('rifas')) ?>"
                   target="_blank"
                   class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white hover:bg-green-600 transition">
                    <i class="fab fa-whatsapp text-2xl"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(base_url('rifas')) ?>"
                   target="_blank"
                   class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white hover:bg-blue-700 transition">
                    <i class="fab fa-facebook-f text-xl"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?= urlencode('Estou concorrendo a premios incriveis no DoarFazBem! Participe: ' . base_url('rifas')) ?>"
                   target="_blank"
                   class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-white hover:bg-gray-800 transition">
                    <i class="fab fa-x-twitter text-xl"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
