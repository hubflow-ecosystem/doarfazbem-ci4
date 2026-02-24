<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-50 py-12">
    <div class="container-custom max-w-2xl">

        <!-- Cabeçalho -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                <i class="fas fa-credit-card text-primary-500 mr-2"></i>
                Pagamento com Cartão
            </h1>
            <p class="text-gray-600">Complete os dados do seu cartão para finalizar a doação</p>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-8">

            <!-- Resumo da Doação -->
            <div class="bg-gradient-to-r from-primary-50 to-secondary-50 rounded-xl p-6 mb-8">
                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-info-circle text-primary-500 mr-2"></i>
                    Resumo da Doação
                </h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Campanha:</span>
                        <span class="font-semibold text-gray-900 text-right max-w-xs"><?= esc($campaign['title']) ?></span>
                    </div>

                    <div class="border-t border-gray-300 pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="text-gray-700">Valor da doação:</span>
                            <span class="font-semibold text-gray-900">
                                R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                            </span>
                        </div>

                        <?php if (isset($donation['donor_pays_fees']) && $donation['donor_pays_fees']): ?>
                            <div class="flex justify-between text-green-600 mt-2">
                                <span>
                                    <i class="fas fa-plus-circle text-xs mr-1"></i>
                                    Taxa do gateway (coberta por você)
                                </span>
                                <span>R$ <?= number_format($donation['gateway_fee'] ?? 0, 2, ',', '.') ?></span>
                            </div>

                            <?php
                            // Calcular o arredondamento
                            $subtotal = $donation['amount'] + ($donation['gateway_fee'] ?? 0);
                            $roundingExtra = ceil($subtotal) - $subtotal;
                            if ($roundingExtra > 0.01):
                            ?>
                            <div class="flex justify-between text-blue-600 mt-1">
                                <span>
                                    <i class="fas fa-arrow-up text-xs mr-1"></i>
                                    Arredondamento
                                </span>
                                <span>R$ <?= number_format($roundingExtra, 2, ',', '.') ?></span>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="border-t-2 border-primary-300 pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="font-bold text-gray-900 text-lg">Total a pagar:</span>
                            <span class="font-bold text-primary-600 text-2xl">
                                R$ <?= number_format($donation['charged_amount'], 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Informação sobre o que vai para a campanha -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mt-3">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-heart text-green-600 mt-1"></i>
                            <div class="text-xs text-green-800">
                                <strong>R$ <?= number_format($donation['amount'], 2, ',', '.') ?></strong>
                                vão diretamente para a campanha
                                <?php if ($campaign['category'] !== 'medica'): ?>
                                <br><span class="text-green-700">(2% de taxa da plataforma será deduzido após aprovação do pagamento)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-error mb-6">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Cartões de Teste (apenas em desenvolvimento) -->
            <?php if (ENVIRONMENT !== 'production'): ?>
            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4 mb-6">
                <h4 class="font-bold text-yellow-800 mb-2 flex items-center">
                    <i class="fas fa-flask text-yellow-600 mr-2"></i>
                    Cartões de Teste - Ambiente de Desenvolvimento
                </h4>
                <p class="text-sm text-yellow-700 mb-3">
                    Clique em um dos cartões abaixo para preencher automaticamente:
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillTestCard('approved')"
                            class="bg-green-100 hover:bg-green-200 text-green-800 text-xs font-semibold py-2 px-3 rounded border border-green-300 transition">
                        ✅ Aprovado<br>
                        <span class="text-xs font-normal">5162 3060 4829 9858</span>
                    </button>
                    <button type="button" onclick="fillTestCard('insufficient')"
                            class="bg-red-100 hover:bg-red-200 text-red-800 text-xs font-semibold py-2 px-3 rounded border border-red-300 transition">
                        ❌ Saldo Insuficiente<br>
                        <span class="text-xs font-normal">5162 3060 4829 9866</span>
                    </button>
                    <button type="button" onclick="fillTestCard('generic_error')"
                            class="bg-orange-100 hover:bg-orange-200 text-orange-800 text-xs font-semibold py-2 px-3 rounded border border-orange-300 transition">
                        ⚠️ Erro Genérico<br>
                        <span class="text-xs font-normal">5162 3060 4829 9874</span>
                    </button>
                    <button type="button" onclick="fillTestCard('always_ask_cvv')"
                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-semibold py-2 px-3 rounded border border-blue-300 transition">
                        🔐 Sempre pede CVV<br>
                        <span class="text-xs font-normal">5162 3060 4829 9882</span>
                    </button>
                </div>
                <p class="text-xs text-yellow-600 mt-2">
                    <i class="fas fa-info-circle"></i>
                    Validade: <strong>12/2030</strong> | CVV: <strong>123</strong>
                </p>
            </div>
            <?php endif; ?>

            <!-- Formulário de Cartão -->
            <form action="<?= base_url('donations/process-card') ?>" method="POST" id="card-form">
                <?= csrf_field() ?>
                <input type="hidden" name="donation_id" value="<?= $donation['id'] ?>">

                <div class="space-y-6">

                    <!-- Número do Cartão -->
                    <div>
                        <label for="card_number" class="form-label">Número do Cartão *</label>
                        <div class="relative">
                            <input type="text"
                                   id="card_number"
                                   name="card_number"
                                   required
                                   maxlength="19"
                                   placeholder="0000 0000 0000 0000"
                                   class="form-input pl-12">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-credit-card text-xl"></i>
                            </div>
                            <div id="card-brand" class="absolute right-4 top-1/2 -translate-y-1/2">
                                <!-- Brand icon will appear here -->
                            </div>
                        </div>
                    </div>

                    <!-- Nome no Cartão -->
                    <div>
                        <label for="card_holder" class="form-label">Nome no Cartão *</label>
                        <input type="text"
                               id="card_holder"
                               name="card_holder"
                               required
                               placeholder="Nome exatamente como no cartão"
                               class="form-input uppercase"
                               value="<?= old('card_holder', $donation['donor_name']) ?>">
                    </div>

                    <!-- Validade e CVV -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="expiry_month" class="form-label">Validade *</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text"
                                       id="expiry_month"
                                       name="expiry_month"
                                       required
                                       maxlength="2"
                                       placeholder="MM"
                                       class="form-input text-center">
                                <input type="text"
                                       id="expiry_year"
                                       name="expiry_year"
                                       required
                                       maxlength="4"
                                       placeholder="AAAA"
                                       class="form-input text-center">
                            </div>
                        </div>

                        <div>
                            <label for="cvv" class="form-label">
                                CVV *
                                <i class="fas fa-question-circle text-gray-400 cursor-help" title="Código de 3 dígitos atrás do cartão"></i>
                            </label>
                            <input type="text"
                                   id="cvv"
                                   name="cvv"
                                   required
                                   maxlength="4"
                                   placeholder="123"
                                   class="form-input text-center">
                        </div>
                    </div>

                    <!-- Parcelamento -->
                    <div>
                        <label for="installments" class="form-label">Parcelamento *</label>
                        <select id="installments" name="installments" class="form-input">
                            <option value="1">1x de R$ <?= number_format($donation['charged_amount'] ?? $donation['amount'], 2, ',', '.') ?> (à vista)</option>
                            <?php
                            $amount = $donation['charged_amount'] ?? $donation['amount'];
                            for ($i = 2; $i <= 12; $i++):
                                $installmentValue = $amount / $i;
                            ?>
                                <option value="<?= $i ?>">
                                    <?= $i ?>x de R$ <?= number_format($installmentValue, 2, ',', '.') ?>
                                    <?php if ($i >= 2 && $i <= 6): ?>
                                        (taxa de 2,49%)
                                    <?php elseif ($i >= 7): ?>
                                        (taxa de 2,99%)
                                    <?php endif; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle"></i>
                            Parcelamento em até 12x. Taxas aplicadas conforme parcelas.
                        </p>
                    </div>

                    <!-- Dados do Titular (Requeridos para Cartão) -->
                    <div class="border-t-2 border-gray-200 pt-6">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-shield text-primary-500 mr-2"></i>
                            Dados do Titular do Cartão
                        </h3>

                        <div class="space-y-4">
                            <!-- CPF -->
                            <div>
                                <label for="holder_cpf" class="form-label">CPF *</label>
                                <input type="text"
                                       id="holder_cpf"
                                       name="holder_cpf"
                                       required
                                       maxlength="14"
                                       placeholder="000.000.000-00"
                                       class="form-input"
                                       value="<?= old('holder_cpf', isset($user['cpf']) ? $user['cpf'] : (isset($donation['donor_cpf']) ? $donation['donor_cpf'] : '')) ?>">
                            </div>

                            <!-- Telefone -->
                            <div>
                                <label for="holder_phone" class="form-label">Telefone/Celular *</label>
                                <input type="text"
                                       id="holder_phone"
                                       name="holder_phone"
                                       required
                                       maxlength="15"
                                       placeholder="(00) 00000-0000"
                                       class="form-input"
                                       value="<?= old('holder_phone', isset($user['phone']) ? $user['phone'] : '') ?>">
                            </div>

                            <!-- CEP -->
                            <div>
                                <label for="holder_postal_code" class="form-label">CEP *</label>
                                <input type="text"
                                       id="holder_postal_code"
                                       name="holder_postal_code"
                                       required
                                       maxlength="9"
                                       placeholder="00000-000"
                                       class="form-input"
                                       value="<?= old('holder_postal_code', isset($user['postal_code']) ? $user['postal_code'] : '') ?>">
                                <p class="text-xs text-gray-500 mt-1">
                                    <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" class="text-primary-500 hover:underline">
                                        <i class="fas fa-search"></i> Não sabe seu CEP?
                                    </a>
                                </p>
                            </div>

                            <!-- Endereço e Número (Grid) -->
                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-2">
                                    <label for="holder_address" class="form-label">Endereço *</label>
                                    <input type="text"
                                           id="holder_address"
                                           name="holder_address"
                                           required
                                           placeholder="Rua, Avenida..."
                                           class="form-input"
                                           value="<?= old('holder_address', isset($user['address']) ? $user['address'] : '') ?>">
                                </div>
                                <div>
                                    <label for="holder_address_number" class="form-label">Número *</label>
                                    <input type="text"
                                           id="holder_address_number"
                                           name="holder_address_number"
                                           required
                                           placeholder="123"
                                           class="form-input"
                                           value="<?= old('holder_address_number', isset($user['address_number']) ? $user['address_number'] : '') ?>">
                                </div>
                            </div>

                            <!-- Complemento -->
                            <div>
                                <label for="holder_address_complement" class="form-label">Complemento (opcional)</label>
                                <input type="text"
                                       id="holder_address_complement"
                                       name="holder_address_complement"
                                       placeholder="Apto, Bloco..."
                                       class="form-input"
                                       value="<?= old('holder_address_complement', isset($user['address_complement']) ? $user['address_complement'] : '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Botão Finalizar -->
                    <button type="submit" id="submit-btn" class="w-full btn-primary text-lg py-4 mt-6">
                        <i class="fas fa-lock mr-2"></i>
                        Finalizar Doação
                    </button>

                    <!-- Aviso de Segurança -->
                    <div class="text-center text-sm text-gray-500">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Pagamento 100% seguro. Seus dados são criptografados.
                    </div>

                </div>
            </form>

        </div>

    </div>
</div>

<script>
// Função para preencher cartões de teste
function fillTestCard(type) {
    const cards = {
        approved: '5162306048299858',
        insufficient: '5162306048299866',
        generic_error: '5162306048299874',
        always_ask_cvv: '5162306048299882'
    };

    const cardNumber = cards[type];

    // Preencher campos
    document.getElementById('card_number').value = cardNumber.replace(/(\d{4})/g, '$1 ').trim();
    document.getElementById('card_holder').value = 'TESTE CARTAO ASAAS';
    document.getElementById('expiry_month').value = '12';
    document.getElementById('expiry_year').value = '2030';
    document.getElementById('cvv').value = '123';

    // Trigger events para atualizar máscaras
    document.getElementById('card_number').dispatchEvent(new Event('input'));
}

document.addEventListener('DOMContentLoaded', function() {
    const cardNumber = document.getElementById('card_number');
    const expiryMonth = document.getElementById('expiry_month');
    const expiryYear = document.getElementById('expiry_year');
    const cvv = document.getElementById('cvv');
    const cardHolder = document.getElementById('card_holder');
    const cardBrand = document.getElementById('card-brand');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('card-form');

    // Máscara do número do cartão
    cardNumber.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = value;

        // Detectar bandeira
        detectCardBrand(value.replace(/\s/g, ''));
    });

    // Detectar bandeira do cartão
    function detectCardBrand(number) {
        const brands = {
            visa: /^4/,
            mastercard: /^5[1-5]/,
            amex: /^3[47]/,
            elo: /^(4011|4312|4389|4514|4576|5041|5066|5090|6277|6362|6363|6504|6505|6516)/,
            hipercard: /^(384100|384140|384160|606282|637095|637568|637599|637609|637612)/,
            diners: /^(36|38|30[0-5])/,
            discover: /^(6011|622|64|65)/
        };

        let detectedBrand = '';
        for (let brand in brands) {
            if (brands[brand].test(number)) {
                detectedBrand = brand;
                break;
            }
        }

        if (detectedBrand) {
            const icons = {
                visa: '<i class="fab fa-cc-visa text-3xl text-blue-600"></i>',
                mastercard: '<i class="fab fa-cc-mastercard text-3xl text-red-600"></i>',
                amex: '<i class="fab fa-cc-amex text-3xl text-blue-500"></i>',
                elo: '<span class="text-xs font-bold text-yellow-600">ELO</span>',
                hipercard: '<span class="text-xs font-bold text-red-600">HIPER</span>',
                diners: '<i class="fab fa-cc-diners-club text-3xl text-blue-800"></i>',
                discover: '<i class="fab fa-cc-discover text-3xl text-orange-600"></i>'
            };
            cardBrand.innerHTML = icons[detectedBrand] || '';
        } else {
            cardBrand.innerHTML = '';
        }
    }

    // Apenas números no mês
    expiryMonth.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 2);
        if (parseInt(e.target.value) > 12) {
            e.target.value = '12';
        }
    });

    // Apenas números no ano
    expiryYear.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
    });

    // Apenas números no CVV
    cvv.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
    });

    // Uppercase no nome
    cardHolder.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Máscaras para dados do titular
    const holderCpf = document.getElementById('holder_cpf');
    const holderPhone = document.getElementById('holder_phone');
    const holderPostalCode = document.getElementById('holder_postal_code');

    // Máscara CPF: 000.000.000-00
    if (holderCpf) {
        holderCpf.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Máscara Telefone: (00) 00000-0000
    if (holderPhone) {
        holderPhone.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Máscara CEP: 00000-000
    if (holderPostalCode) {
        holderPostalCode.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Prevenir submit duplo
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processando...';
    });
});
</script>

<?= $this->endSection() ?>
