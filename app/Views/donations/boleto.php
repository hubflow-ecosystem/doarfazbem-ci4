<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-50 py-12">
    <div class="container-custom max-w-2xl">

        <!-- Cabeçalho -->
        <div class="text-center mb-8">
            <div class="inline-block p-4 bg-orange-100 rounded-full mb-4">
                <i class="fas fa-barcode text-5xl text-orange-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                Boleto Bancário Gerado
            </h1>
            <p class="text-gray-600">Pague até a data de vencimento</p>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-8">

            <!-- Status -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full mb-4">
                    <i class="fas fa-clock"></i>
                    <span class="font-semibold">Aguardando pagamento</span>
                </div>
                <p class="text-sm text-gray-600">
                    O pagamento será confirmado em até 2 dias úteis após o pagamento
                </p>
            </div>

            <!-- Valor e Vencimento -->
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl p-6 mb-8">
                <div class="grid grid-cols-2 gap-6 text-center">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Valor</div>
                        <div class="text-3xl font-bold text-orange-600">
                            R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Vencimento</div>
                        <div class="text-2xl font-bold text-gray-900">
                            <?= date('d/m/Y', strtotime($donation['created_at'] . ' +3 days')) ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            (<?= date('d/m/Y', strtotime('+3 days')) ?>)
                        </div>
                    </div>
                </div>
                <div class="text-xs text-gray-500 text-center mt-4">
                    Doação para: <strong><?= esc($campaign['title']) ?></strong>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="space-y-3 mb-8">
                <?php if (!empty($donation['boleto_url'])): ?>
                    <a href="<?= esc($donation['boleto_url']) ?>"
                       target="_blank"
                       class="w-full btn-primary text-center block">
                        <i class="fas fa-download mr-2"></i>
                        Baixar Boleto (PDF)
                    </a>

                    <button type="button"
                            onclick="window.print()"
                            class="w-full btn-outline">
                        <i class="fas fa-print mr-2"></i>
                        Imprimir Boleto
                    </button>
                <?php else: ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        URL do boleto não disponível. Entre em contato com o suporte.
                    </div>
                <?php endif; ?>

                <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>"
                   class="w-full btn-secondary text-center block">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar para Campanha
                </a>
            </div>

            <!-- Código de Barras (se disponível) -->
            <?php if (!empty($donation['boleto_url'])): ?>
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 text-center">
                        Código de barras:
                    </label>
                    <div class="flex gap-2">
                        <input type="text"
                               id="barcode"
                               value="<?= esc($donation['asaas_payment_id']) ?>"
                               readonly
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-mono bg-gray-50">
                        <button type="button"
                                onclick="copyBarcode()"
                                class="px-6 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors font-semibold">
                            <i class="fas fa-copy mr-2"></i>
                            Copiar
                        </button>
                    </div>
                    <div id="copy-feedback" class="hidden text-sm text-green-600 text-center mt-2">
                        <i class="fas fa-check mr-1"></i>
                        Código copiado!
                    </div>
                </div>
            <?php endif; ?>

            <!-- Instruções -->
            <div class="bg-orange-50 border border-orange-200 rounded-xl p-6 mb-6">
                <h3 class="font-bold text-orange-900 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Como pagar o boleto:
                </h3>
                <ol class="space-y-2 text-sm text-orange-800">
                    <li class="flex items-start gap-2">
                        <span class="font-bold">1.</span>
                        <span>Baixe o boleto clicando no botão acima</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">2.</span>
                        <span>Imprima o boleto ou use o código de barras no app do banco</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">3.</span>
                        <span>Pague em qualquer banco, lotérica ou app de pagamentos</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="font-bold">4.</span>
                        <span>O pagamento será confirmado em até 2 dias úteis</span>
                    </li>
                </ol>
            </div>

            <!-- Aviso de Vencimento -->
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                <p class="text-sm text-red-800">
                    <strong>Atenção:</strong> Este boleto vence em <strong>3 dias</strong>.
                    Após o vencimento, será necessário gerar um novo boleto.
                </p>
            </div>

            <!-- Email de Confirmação -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                <i class="fas fa-envelope text-blue-500 text-2xl mb-2"></i>
                <p class="text-sm text-blue-800">
                    O boleto também foi enviado para seu email:
                    <strong><?= esc($donation['donor_email']) ?></strong>
                </p>
            </div>

            <!-- Ajuda -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-500">
                    Problemas com o boleto?
                    <a href="<?= base_url('contato') ?>" class="text-primary-600 hover:text-primary-700 font-semibold">
                        Entre em contato
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>

<script>
// Copiar código de barras
function copyBarcode() {
    const barcode = document.getElementById('barcode');
    barcode.select();
    barcode.setSelectionRange(0, 99999);

    document.execCommand('copy');

    const feedback = document.getElementById('copy-feedback');
    feedback.classList.remove('hidden');

    setTimeout(() => {
        feedback.classList.add('hidden');
    }, 3000);
}
</script>

<?= $this->endSection() ?>
