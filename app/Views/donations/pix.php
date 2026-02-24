<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-50 py-12">
    <div class="container-custom max-w-2xl">

        <!-- Cabeçalho -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-primary-100 rounded-full mb-4">
                <i class="fas fa-qrcode text-5xl text-primary-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Pague com PIX
            </h1>
            <p class="text-gray-600">Seu pagamento será confirmado em segundos</p>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-8">

            <!-- Status da Doação -->
            <div id="status-pending" class="text-center mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full mb-4">
                    <div class="animate-pulse">
                        <i class="fas fa-circle text-xs"></i>
                    </div>
                    <span class="font-semibold">Aguardando pagamento...</span>
                </div>
                <p class="text-sm text-gray-600">
                    Seu pagamento será confirmado automaticamente após a aprovação
                </p>
            </div>

            <div id="status-confirmed" class="text-center mb-8 hidden">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-full mb-4">
                    <i class="fas fa-check-circle"></i>
                    <span class="font-semibold">Pagamento confirmado!</span>
                </div>
                <p class="text-sm text-gray-600">
                    Obrigado pela sua doação! Redirecionando...
                </p>
            </div>

            <!-- Valor da Doação -->
            <div class="bg-gradient-to-r from-primary-50 to-secondary-50 rounded-xl p-6 mb-8 text-center">
                <div class="text-sm text-gray-600 mb-1">Valor a pagar</div>
                <div class="text-4xl font-bold text-primary-600">
                    R$ <?= number_format($donation['charged_amount'], 2, ',', '.') ?>
                </div>
                <div class="text-xs text-gray-500 mt-2">
                    Doação para: <strong><?= esc($campaign['title']) ?></strong>
                </div>
            </div>

            <!-- QR Code PIX -->
            <div class="text-center mb-6">
                <div class="inline-block p-4 bg-white border-4 border-gray-200 rounded-2xl">
                    <?php if (!empty($donation['pix_qr_code'])): ?>
                        <?php
                        // Detectar tipo de imagem (PNG, SVG, etc)
                        $qrCode = $donation['pix_qr_code'];
                        $imageType = 'svg+xml'; // Default para SVG

                        // Se começar com iVBOR (PNG em base64) ou tiver outros indicadores
                        if (substr($qrCode, 0, 5) === 'iVBOR') {
                            $imageType = 'png';
                        }
                        ?>
                        <img src="data:image/<?= $imageType ?>;base64,<?= $qrCode ?>"
                             alt="QR Code PIX"
                             class="w-64 h-64">
                    <?php else: ?>
                        <div class="w-64 h-64 flex items-center justify-center bg-gray-100 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-qrcode text-6xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">QR Code não disponível</p>
                                <p class="text-xs text-gray-400 mt-2">Use o código Copia e Cola abaixo</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-gray-600 mt-4">
                    <i class="fas fa-mobile-alt mr-1"></i>
                    Abra o app do seu banco e escaneie o QR Code
                </p>
            </div>

            <!-- Código PIX Copia e Cola -->
            <?php if (!empty($donation['pix_copy_paste'])): ?>
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 text-center">
                        Ou copie o código PIX:
                    </label>
                    <div class="flex gap-2">
                        <input type="text"
                               id="pix-code"
                               value="<?= esc($donation['pix_copy_paste']) ?>"
                               readonly
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-mono bg-gray-50">
                        <button type="button"
                                onclick="copyPixCode()"
                                class="px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors font-semibold">
                            <i class="fas fa-copy mr-2"></i>
                            Copiar
                        </button>
                    </div>
                    <div id="copy-feedback" class="hidden text-sm text-green-600 text-center mt-2">
                        <i class="fas fa-check mr-1"></i>
                        Código copiado! Cole no seu app de pagamentos.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Instruções -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                <h3 class="font-bold text-blue-900 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Como pagar com PIX:
                </h3>
                <ol class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start gap-2">
                        <span class="font-bold">1.</span>
                        <span>Abra o aplicativo do seu banco</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">2.</span>
                        <span>Selecione a opção <strong>PIX</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">3.</span>
                        <span>Escolha <strong>Pagar com QR Code</strong> ou <strong>PIX Copia e Cola</strong></span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">4.</span>
                        <span>Escaneie o QR Code ou cole o código copiado</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">5.</span>
                        <span>Confirme o pagamento</span>
                    </li>
                </ol>
            </div>

            <!-- Timer de Expiração -->
            <div class="text-center text-sm text-gray-500 mb-6">
                <i class="fas fa-clock mr-1"></i>
                Este QR Code expira em <strong id="timer">10:00</strong> minutos
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button"
                        onclick="checkPaymentStatus()"
                        class="flex-1 btn-outline">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Já Paguei - Verificar Status
                </button>
                <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>"
                   class="flex-1 btn-secondary text-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar para Campanha
                </a>
            </div>

            <!-- Ajuda -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500">
                    Problemas com o pagamento?
                    <a href="<?= base_url('contato') ?>" class="text-primary-600 hover:text-primary-700 font-semibold">
                        Entre em contato
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>

<script>
const donationId = <?= $donation['id'] ?>;
const maxTime = 600; // 10 minutos
let timeLeft = maxTime;
let checkInterval;

// Copiar código PIX
function copyPixCode() {
    const pixCode = document.getElementById('pix-code');
    pixCode.select();
    pixCode.setSelectionRange(0, 99999); // Para mobile

    document.execCommand('copy');

    // Feedback visual
    const feedback = document.getElementById('copy-feedback');
    feedback.classList.remove('hidden');

    setTimeout(() => {
        feedback.classList.add('hidden');
    }, 3000);
}

// Timer de expiração
function updateTimer() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    document.getElementById('timer').textContent =
        `${minutes}:${seconds.toString().padStart(2, '0')}`;

    timeLeft--;

    if (timeLeft < 0) {
        clearInterval(timerInterval);
        clearInterval(checkInterval);
        alert('O QR Code expirou. Por favor, gere uma nova doação.');
        window.location.href = '<?= base_url('campaigns/' . $campaign['slug'] . '/donate') ?>';
    }
}

const timerInterval = setInterval(updateTimer, 1000);

// Verificar status do pagamento
function checkPaymentStatus() {
    fetch('<?= base_url('donations/pix-status/' . $donation['id']) ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.status === 'confirmed') {
                // Pagamento confirmado!
                clearInterval(timerInterval);
                clearInterval(checkInterval);

                document.getElementById('status-pending').classList.add('hidden');
                document.getElementById('status-confirmed').classList.remove('hidden');

                // Redireciona após 2 segundos
                setTimeout(() => {
                    window.location.href = data.redirect || '<?= base_url('donations/success/' . $donation['id']) ?>';
                }, 2000);
            }
        })
        .catch(error => {
            console.error('Erro ao verificar status:', error);
        });
}

// Verificar automaticamente a cada 5 segundos
checkInterval = setInterval(checkPaymentStatus, 5000);

// Verificar imediatamente ao carregar
checkPaymentStatus();
</script>

<?= $this->endSection() ?>
