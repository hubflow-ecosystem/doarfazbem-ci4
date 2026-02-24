<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container-custom max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <h1 class="text-heading-1 text-gray-900 mb-2">Pagamento via PIX</h1>
                <p class="text-gray-600">Escaneie o QR Code ou use o código Pix Copia e Cola</p>
            </div>

            <!-- Valor -->
            <div class="mb-8 pb-6 border-b">
                <div class="text-sm text-gray-600 mb-1">Valor a pagar</div>
                <div class="text-4xl font-bold text-primary-600">
                    R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                </div>
            </div>

            <!-- QR Code -->
            <?php if (!empty($donation['pix_qr_code'])): ?>
                <div class="mb-6">
                    <img src="data:image/png;base64,<?= $donation['pix_qr_code'] ?>"
                         alt="QR Code PIX"
                         class="w-64 h-64 mx-auto border-4 border-gray-200 rounded-lg">
                </div>
            <?php endif; ?>

            <!-- Pix Copia e Cola -->
            <?php if (!empty($donation['pix_copy_paste'])): ?>
                <div class="mb-8">
                    <label class="form-label">Código Pix Copia e Cola</label>
                    <div class="flex gap-2">
                        <input type="text" id="pixCode" readonly
                               value="<?= $donation['pix_copy_paste'] ?>"
                               class="form-input font-mono text-sm">
                        <button onclick="copyPixCode()" class="btn-primary whitespace-nowrap">
                            Copiar
                        </button>
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Cole este código no app do seu banco</p>
                </div>
            <?php endif; ?>

            <!-- Instruções -->
            <div class="bg-blue-50 rounded-lg p-6 text-left mb-6">
                <h3 class="font-semibold mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Como pagar com PIX
                </h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                    <li>Abra o aplicativo do seu banco</li>
                    <li>Escolha a opção Pix</li>
                    <li>Escaneie o QR Code ou cole o código</li>
                    <li>Confirme o pagamento</li>
                    <li>Pronto! O pagamento é confirmado na hora</li>
                </ol>
            </div>

            <!-- Status -->
            <div id="paymentStatus" class="mb-6">
                <div class="flex items-center justify-center gap-2 text-yellow-600">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Aguardando pagamento...</span>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex gap-3">
                <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>" class="btn-outline flex-1">
                    Voltar para Campanha
                </a>
                <button onclick="checkPaymentStatus()" class="btn-secondary flex-1">
                    Verificar Pagamento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copyPixCode() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    document.execCommand('copy');
    alert('Código PIX copiado!');
}

// Verificar status do pagamento a cada 5 segundos
let checkInterval = setInterval(checkPaymentStatus, 5000);

function checkPaymentStatus() {
    fetch('<?= base_url('payment/check-status/' . $donation['id']) ?>')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'received' || data.status === 'confirmed') {
                clearInterval(checkInterval);
                window.location.href = '<?= base_url('payment/success/' . $donation['id']) ?>';
            }
        });
}
</script>

<?= $this->endSection() ?>
