<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 py-12"
     x-data="paymentTimer(<?= $remainingSeconds ?>)">
    <div class="container-custom max-w-2xl">

        <!-- Card Principal -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-6 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clock text-white text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Aguardando Pagamento</h1>
                <p class="text-teal-200">Complete o pagamento para garantir seus numeros</p>
            </div>

            <!-- Timer -->
            <div class="p-6 border-b bg-gray-50">
                <div class="text-center">
                    <p class="text-sm text-gray-500 mb-2">Tempo restante para pagamento:</p>
                    <div class="text-5xl font-mono font-black"
                         :class="remainingSeconds < 300 ? 'text-red-600' : 'text-teal-600'"
                         x-text="formatTime(remainingSeconds)">
                        --:--
                    </div>

                    <!-- Progress bar do tempo -->
                    <div class="mt-4 bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="h-full transition-all duration-1000 rounded-full"
                             :class="remainingSeconds < 300 ? 'bg-red-500' : 'bg-emerald-500'"
                             :style="'width: ' + (remainingSeconds / 1800 * 100) + '%'"></div>
                    </div>
                </div>

                <!-- Alerta de tempo -->
                <div x-show="remainingSeconds < 300 && remainingSeconds > 0"
                     class="mt-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl text-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Faltam menos de 5 minutos! Complete o pagamento agora.
                </div>

                <!-- Expirado -->
                <div x-show="remainingSeconds <= 0"
                     class="mt-4 bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl text-center">
                    <i class="fas fa-times-circle mr-2"></i>
                    Tempo esgotado! Seus numeros foram liberados.
                    <a href="<?= base_url('rifas') ?>" class="block mt-2 text-red-800 font-semibold underline">
                        Fazer nova compra
                    </a>
                </div>
            </div>

            <!-- Resumo da Compra -->
            <div class="p-6 border-b">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-ticket-alt text-teal-500 mr-2"></i>
                    Resumo da Compra
                </h3>
                <div class="bg-emerald-50 rounded-xl p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Quantidade:</span>
                        <span class="font-bold text-gray-800"><?= $purchase['quantity'] ?> cotas</span>
                    </div>
                    <?php if ($purchase['discount_applied'] > 0): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Desconto:</span>
                        <span class="font-bold text-green-600">-R$ <?= number_format($purchase['discount_applied'], 2, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-lg border-t border-emerald-200 pt-2 mt-2">
                        <span class="font-semibold">Total a Pagar:</span>
                        <span class="font-black text-2xl text-teal-600">R$ <?= number_format($purchase['total_amount'], 2, ',', '.') ?></span>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <p class="text-yellow-800 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong><?= $purchase['quantity'] ?> numeros</strong> foram reservados para voce.
                        Eles serao revelados apos a confirmacao do pagamento.
                    </p>
                </div>
            </div>

            <!-- QR Code PIX -->
            <div class="p-6" x-show="remainingSeconds > 0">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-qrcode text-green-500 mr-2"></i>
                    Pague com PIX
                </h3>

                <div class="text-center mb-6">
                    <?php if ($purchase['pix_qrcode']): ?>
                    <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-2xl">
                        <img src="data:image/svg+xml;base64,<?= $purchase['pix_qrcode'] ?>"
                             alt="QR Code PIX" class="w-48 h-48 mx-auto">
                    </div>
                    <?php else: ?>
                    <div class="w-48 h-48 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-qrcode text-6xl text-gray-300"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Codigo Copia e Cola -->
                <?php if ($purchase['pix_code']): ?>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Ou copie o codigo PIX:
                    </label>
                    <div class="relative">
                        <input type="text" readonly value="<?= esc($purchase['pix_code']) ?>"
                               id="pix-code"
                               class="w-full form-input pr-24 text-sm font-mono bg-gray-50">
                        <button type="button" onclick="copyPixCode()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                            <i class="fas fa-copy mr-1"></i> Copiar
                        </button>
                    </div>
                    <div id="copy-feedback" class="text-green-600 text-sm mt-2 hidden">
                        <i class="fas fa-check-circle mr-1"></i> Codigo copiado!
                    </div>
                </div>
                <?php endif; ?>

                <!-- Instrucoes -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <h4 class="font-semibold text-gray-800 mb-3">Como pagar:</h4>
                    <ol class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="w-6 h-6 bg-emerald-100 text-teal-600 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0">1</span>
                            <span>Abra o app do seu banco</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-6 h-6 bg-emerald-100 text-teal-600 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0">2</span>
                            <span>Escolha pagar com PIX (QR Code ou Copia e Cola)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-6 h-6 bg-emerald-100 text-teal-600 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0">3</span>
                            <span>Escaneie o QR Code ou cole o codigo</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-6 h-6 bg-emerald-100 text-teal-600 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0">4</span>
                            <span>Confirme o pagamento</span>
                        </li>
                    </ol>
                </div>

                <!-- Status de verificacao -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-center"
                     x-show="!paymentConfirmed">
                    <div class="animate-pulse">
                        <i class="fas fa-spinner fa-spin text-blue-500 text-2xl mb-2"></i>
                        <p class="text-blue-700 font-semibold">Verificando pagamento...</p>
                        <p class="text-blue-600 text-sm">Esta pagina atualizara automaticamente</p>
                    </div>
                </div>

                <!-- Botao de simular (apenas em dev) -->
                <?php if (ENVIRONMENT === 'development'): ?>
                <div class="mt-6 p-4 bg-yellow-100 border border-yellow-300 rounded-xl">
                    <p class="text-yellow-800 text-sm mb-3">
                        <i class="fas fa-code mr-1"></i> Modo de desenvolvimento
                    </p>
                    <a href="<?= base_url('rifas/simular-pagamento/' . $purchase['id']) ?>"
                       class="block w-full py-3 bg-yellow-500 text-white font-bold rounded-xl text-center hover:bg-yellow-600 transition">
                        <i class="fas fa-bolt mr-2"></i> Simular Pagamento Confirmado
                    </a>
                </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<script>
function paymentTimer(initialSeconds) {
    return {
        remainingSeconds: initialSeconds,
        paymentConfirmed: false,
        interval: null,
        checkInterval: null,

        init() {
            // Countdown
            this.interval = setInterval(() => {
                if (this.remainingSeconds > 0) {
                    this.remainingSeconds--;
                } else {
                    clearInterval(this.interval);
                    clearInterval(this.checkInterval);
                }
            }, 1000);

            // Verificar status a cada 5 segundos
            this.checkInterval = setInterval(() => {
                this.checkPaymentStatus();
            }, 5000);
        },

        formatTime(seconds) {
            if (seconds <= 0) return '00:00';
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        async checkPaymentStatus() {
            try {
                const response = await fetch('<?= base_url('rifas/status/' . $purchase['id']) ?>');
                const data = await response.json();

                if (data.paid) {
                    this.paymentConfirmed = true;
                    clearInterval(this.interval);
                    clearInterval(this.checkInterval);
                    window.location.href = '<?= base_url('rifas/sucesso/' . $purchase['id']) ?>';
                } else if (data.expired) {
                    this.remainingSeconds = 0;
                    clearInterval(this.interval);
                    clearInterval(this.checkInterval);
                }
            } catch (error) {
                console.error('Erro ao verificar status:', error);
            }
        }
    };
}

function copyPixCode() {
    const input = document.getElementById('pix-code');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);

    const feedback = document.getElementById('copy-feedback');
    feedback.classList.remove('hidden');
    setTimeout(() => feedback.classList.add('hidden'), 3000);
}
</script>

<?= $this->endSection() ?>
