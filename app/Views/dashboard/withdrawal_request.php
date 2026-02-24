<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 py-8">
    <div class="container-custom max-w-3xl">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?= base_url('dashboard/withdrawals') ?>" class="text-gray-600 hover:text-gray-900 mb-4 inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Voltar para Saques
            </a>
            <h1 class="text-heading-1 text-gray-900 mt-4">Solicitar Saque</h1>
            <p class="text-gray-600 mt-2">Preencha os dados para receber o valor das suas doações</p>
        </div>

        <?php if (empty($campaigns)): ?>
        <!-- Sem saldo disponível -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="text-5xl text-gray-300 mb-4">
                <i class="fas fa-wallet"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-600 mb-2">Nenhum saldo disponível</h3>
            <p class="text-gray-500 mb-6">Você não possui saldo disponível para saque no momento.</p>
            <a href="<?= base_url('dashboard/withdrawals') ?>" class="btn-secondary">
                Voltar
            </a>
        </div>
        <?php else: ?>

        <form action="<?= base_url('dashboard/withdrawals/store') ?>" method="POST"
              x-data="withdrawalForm()" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Selecionar Campanha -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">1. Selecione a Campanha</h3>

                <div class="space-y-3">
                    <?php foreach ($campaigns as $campaign): ?>
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition"
                           :class="selectedCampaign == <?= $campaign['id'] ?> ? 'border-primary-500 bg-primary-50' : 'border-gray-200'">
                        <input type="radio" name="campaign_id" value="<?= $campaign['id'] ?>"
                               x-model="selectedCampaign"
                               @change="maxAmount = <?= $campaign['balance']['available'] ?>"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500">
                        <div class="ml-4 flex-1">
                            <p class="font-medium text-gray-900"><?= esc($campaign['title']) ?></p>
                            <p class="text-sm text-gray-500">Disponível para saque</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-green-600">
                                R$ <?= number_format($campaign['balance']['available'], 2, ',', '.') ?>
                            </p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Valor do Saque -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-show="selectedCampaign">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">2. Valor do Saque</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor a sacar (R$)</label>
                        <input type="number" name="amount" x-model="amount" step="0.01" min="20" :max="maxAmount"
                               class="form-input text-lg font-semibold"
                               placeholder="0,00" required>
                        <p class="text-sm text-gray-500 mt-1">
                            Mínimo: R$ 20,00 | Máximo: R$ <span x-text="maxAmount.toFixed(2).replace('.', ',')"></span>
                        </p>
                    </div>

                    <!-- Preview da taxa -->
                    <div class="bg-gray-50 rounded-lg p-4" x-show="amount > 0">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Valor solicitado:</span>
                            <span class="font-medium">R$ <span x-text="parseFloat(amount).toFixed(2).replace('.', ',')"></span></span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">Taxa (2,5% + R$ 2,00):</span>
                            <span class="text-red-600">- R$ <span x-text="calcFee().toFixed(2).replace('.', ',')"></span></span>
                        </div>
                        <hr class="my-2">
                        <div class="flex justify-between font-bold">
                            <span>Você receberá:</span>
                            <span class="text-green-600">R$ <span x-text="calcNet().toFixed(2).replace('.', ',')"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Método de Pagamento -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-show="selectedCampaign">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">3. Como deseja receber?</h3>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                           :class="paymentMethod === 'pix' ? 'border-primary-500 bg-primary-50' : 'border-gray-200'">
                        <input type="radio" name="payment_method" value="pix" x-model="paymentMethod"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500">
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">PIX</p>
                            <p class="text-xs text-gray-500">Mais rápido</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                           :class="paymentMethod === 'bank_transfer' ? 'border-primary-500 bg-primary-50' : 'border-gray-200'">
                        <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod"
                               class="h-4 w-4 text-primary-600 focus:ring-primary-500">
                        <div class="ml-3">
                            <p class="font-medium text-gray-900">TED/DOC</p>
                            <p class="text-xs text-gray-500">Transferência bancária</p>
                        </div>
                    </label>
                </div>

                <!-- Dados PIX -->
                <div x-show="paymentMethod === 'pix'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo da Chave PIX</label>
                        <select name="pix_key_type" class="form-input">
                            <option value="cpf">CPF</option>
                            <option value="cnpj">CNPJ</option>
                            <option value="email">E-mail</option>
                            <option value="phone">Telefone</option>
                            <option value="random">Chave Aleatória</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chave PIX</label>
                        <input type="text" name="pix_key" class="form-input"
                               value="<?= esc($user['cpf'] ?? '') ?>"
                               placeholder="Digite sua chave PIX">
                    </div>
                </div>

                <!-- Dados Bancários -->
                <div x-show="paymentMethod === 'bank_transfer'" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                            <select name="bank_code" class="form-input">
                                <option value="">Selecione</option>
                                <option value="001">Banco do Brasil</option>
                                <option value="033">Santander</option>
                                <option value="104">Caixa Econômica</option>
                                <option value="237">Bradesco</option>
                                <option value="341">Itaú</option>
                                <option value="260">Nubank</option>
                                <option value="077">Inter</option>
                                <option value="336">C6 Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Conta</label>
                            <select name="bank_account_type" class="form-input">
                                <option value="checking">Conta Corrente</option>
                                <option value="savings">Conta Poupança</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Agência</label>
                            <input type="text" name="bank_agency" class="form-input" placeholder="0000">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conta</label>
                            <input type="text" name="bank_account" class="form-input" placeholder="00000-0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observações -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-show="selectedCampaign">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">4. Observações (opcional)</h3>
                <textarea name="notes" rows="3" class="form-input"
                          placeholder="Alguma observação sobre este saque?"></textarea>
            </div>

            <!-- Botão Submit -->
            <div class="flex justify-end space-x-4" x-show="selectedCampaign">
                <a href="<?= base_url('dashboard/withdrawals') ?>" class="btn-secondary">Cancelar</a>
                <button type="submit" class="btn-primary"
                        :disabled="!selectedCampaign || amount < 20 || amount > maxAmount">
                    <i class="fas fa-paper-plane mr-2"></i>Solicitar Saque
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
function withdrawalForm() {
    return {
        selectedCampaign: null,
        maxAmount: 0,
        amount: 0,
        paymentMethod: 'pix',

        calcFee() {
            if (this.amount <= 0) return 0;
            return (parseFloat(this.amount) * 0.025) + 2.00;
        },

        calcNet() {
            if (this.amount <= 0) return 0;
            return parseFloat(this.amount) - this.calcFee();
        }
    };
}
</script>

<?= $this->endSection() ?>
