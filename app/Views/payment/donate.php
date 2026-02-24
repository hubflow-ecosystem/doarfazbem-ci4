<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-12">
    <div class="container-custom max-w-5xl">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Coluna Principal - Formulário -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h1 class="text-heading-1 text-gray-900 mb-6">Fazer uma Doação</h1>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert-error mb-6">
                            <ul class="list-disc list-inside">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('payment/process') ?>" method="POST" id="paymentForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">

                        <!-- Valor da Doação -->
                        <div class="mb-6">
                            <label class="form-label">Valor da Doação (R$) *</label>
                            <div class="grid grid-cols-3 gap-3 mb-3">
                                <button type="button" onclick="setAmount(10)" class="amount-btn">R$ 10</button>
                                <button type="button" onclick="setAmount(25)" class="amount-btn">R$ 25</button>
                                <button type="button" onclick="setAmount(50)" class="amount-btn">R$ 50</button>
                                <button type="button" onclick="setAmount(100)" class="amount-btn">R$ 100</button>
                                <button type="button" onclick="setAmount(250)" class="amount-btn">R$ 250</button>
                                <button type="button" onclick="setAmount(500)" class="amount-btn">R$ 500</button>
                            </div>
                            <input type="number" id="amount" name="amount" required min="1" step="0.01"
                                   class="form-input text-2xl font-bold" placeholder="0.00">
                        </div>

                        <!-- Método de Pagamento -->
                        <div class="mb-6">
                            <label class="form-label">Método de Pagamento *</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="pix" required class="hidden">
                                    <div class="payment-method-content">
                                        <div class="text-3xl mb-2">💳</div>
                                        <div class="font-semibold">PIX</div>
                                        <div class="text-xs">Instantâneo</div>
                                    </div>
                                </label>
                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="credit_card" class="hidden">
                                    <div class="payment-method-content">
                                        <div class="text-3xl mb-2">💳</div>
                                        <div class="font-semibold">Cartão</div>
                                        <div class="text-xs">Crédito</div>
                                    </div>
                                </label>
                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="boleto" class="hidden">
                                    <div class="payment-method-content">
                                        <div class="text-3xl mb-2">📄</div>
                                        <div class="font-semibold">Boleto</div>
                                        <div class="text-xs">3 dias úteis</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Dados do Doador -->
                        <div class="space-y-4 mb-6">
                            <h3 class="text-lg font-semibold">Seus Dados</h3>

                            <div>
                                <label for="donor_name" class="form-label">Nome Completo *</label>
                                <input type="text" id="donor_name" name="donor_name" required class="form-input">
                            </div>

                            <div>
                                <label for="donor_email" class="form-label">Email *</label>
                                <input type="email" id="donor_email" name="donor_email" required class="form-input">
                            </div>

                            <div>
                                <label for="donor_phone" class="form-label">Telefone *</label>
                                <input type="tel" id="donor_phone" name="donor_phone" required class="form-input" placeholder="11987654321">
                            </div>

                            <div>
                                <label for="cpf" class="form-label">CPF *</label>
                                <input type="text" id="cpf" name="cpf" required class="form-input" placeholder="000.000.000-00">
                            </div>
                        </div>

                        <!-- Mensagem (Opcional) -->
                        <div class="mb-6">
                            <label for="message" class="form-label">Mensagem de Apoio (Opcional)</label>
                            <textarea id="message" name="message" rows="3" class="form-input"
                                      placeholder="Deixe uma mensagem de apoio..."></textarea>
                        </div>

                        <!-- Doação Anônima -->
                        <div class="mb-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_anonymous" value="1" class="rounded">
                                <span class="text-sm text-gray-700">Fazer doação anônima</span>
                            </label>
                        </div>

                        <!-- Botão -->
                        <button type="submit" class="btn-primary w-full text-lg py-4">
                            Continuar para Pagamento
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar - Resumo -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-20">
                    <h3 class="font-semibold text-lg mb-4">Resumo da Campanha</h3>

                    <?php if ($campaign['image']): ?>
                        <img src="<?= base_url('uploads/campaigns/' . $campaign['image']) ?>"
                             alt="<?= esc($campaign['title']) ?>"
                             class="w-full h-32 object-cover rounded-lg mb-4">
                    <?php endif; ?>

                    <h4 class="font-semibold mb-2"><?= esc($campaign['title']) ?></h4>

                    <div class="text-sm text-gray-600 mb-4">
                        Categoria: <span class="font-semibold"><?= esc($campaign['category']) ?></span>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Taxa da plataforma:</span>
                            <span class="font-semibold text-primary-600">
                                <?= $campaign['category'] === 'medica' ? '0%' : '2%' ?>
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?= $campaign['category'] === 'medica' ?
                                '✅ Campanha médica: 100% do valor vai para o beneficiário!' :
                                '98% do valor vai para o beneficiário' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.amount-btn {
    @apply px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-primary-100 hover:text-primary-700 transition-colors;
}
.payment-method-card {
    @apply cursor-pointer;
}
.payment-method-content {
    @apply p-4 border-2 border-gray-200 rounded-lg text-center transition-all hover:border-primary-300;
}
.payment-method-card input:checked + .payment-method-content {
    @apply border-primary-500 bg-primary-50;
}
</style>

<script>
function setAmount(value) {
    document.getElementById('amount').value = value;
}
</script>

<?= $this->endSection() ?>
