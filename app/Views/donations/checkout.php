<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-50 py-12"
     x-data="donationForm(<?= $campaign['id'] ?>, '<?= $campaign['category'] ?>', '<?= $campaign['slug'] ?>', <?= isset($selectedReward) && $selectedReward ? $selectedReward['id'] : 'null' ?>, <?= $minAmount ?? 10 ?>)">
    <div class="container-custom max-w-4xl">

        <!-- Cabeçalho -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                <i class="fas fa-heart text-red-500 mr-2"></i>
                Faça sua Doação
            </h1>
            <p class="text-gray-600">Ajude esta causa e faça a diferença</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Resumo da Campanha (Sidebar) -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-card p-6 sticky top-4">
                    <img src="<?= base_url('uploads/campaigns/' . $campaign['image']) ?>"
                         alt="<?= esc($campaign['title']) ?>"
                         class="w-full h-48 object-cover rounded-xl mb-4">

                    <h3 class="font-bold text-lg text-gray-900 mb-2">
                        <?= esc($campaign['title']) ?>
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Meta:</span>
                            <span class="font-bold text-gray-900">
                                R$ <?= number_format($campaign['goal_amount'], 2, ',', '.') ?>
                            </span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Arrecadado:</span>
                            <span class="font-bold text-primary-600">
                                R$ <?= number_format($campaign['current_amount'] ?? 0, 2, ',', '.') ?>
                            </span>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full transition-all duration-500"
                                 style="width: <?= min(100, $campaign['percentage']) ?>%"></div>
                        </div>

                        <div class="text-center">
                            <span class="text-2xl font-bold text-primary-600">
                                <?= number_format($campaign['percentage'], 1) ?>%
                            </span>
                            <span class="text-gray-600 text-sm block">alcançado</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulário de Doação -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-card p-8">

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert-error mb-6">
                            <?= session()->getFlashdata('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert-error mb-6">
                            <ul class="list-disc list-inside">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('donations/process') ?>" method="POST" id="donation-form" class="space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">
                        <input type="hidden" name="reward_id" x-model="selectedRewardId" value="<?= isset($selectedReward) && $selectedReward ? $selectedReward['id'] : '' ?>">

                        <?php if (!empty($rewards)): ?>
                        <!-- Recompensas Disponíveis -->
                        <div class="mb-6">
                            <label class="form-label flex items-center">
                                <i class="fas fa-gift text-purple-500 mr-2"></i>
                                Escolha uma Recompensa (Opcional)
                            </label>

                            <div class="space-y-3">
                                <!-- Opção: Sem Recompensa -->
                                <div @click="selectReward(null, <?= $minAmount ?? 10 ?>)"
                                     :class="selectedRewardId === null ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                                     class="border-2 rounded-xl p-4 cursor-pointer transition-all hover:border-primary-300">
                                    <div class="flex items-center">
                                        <div class="w-5 h-5 rounded-full border-2 mr-3 flex items-center justify-center"
                                             :class="selectedRewardId === null ? 'border-primary-500 bg-primary-500' : 'border-gray-300'">
                                            <i x-show="selectedRewardId === null" class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-gray-900">Doar sem recompensa</span>
                                            <span class="text-sm text-gray-500 ml-2">Qualquer valor a partir de R$ <?= number_format($minAmount ?? 10, 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lista de Recompensas -->
                                <?php foreach ($rewards as $reward): ?>
                                <div @click="<?= !$reward['is_sold_out'] ? "selectReward({$reward['id']}, {$reward['min_amount']})" : '' ?>"
                                     :class="selectedRewardId === <?= $reward['id'] ?> ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                                     class="border-2 rounded-xl p-4 transition-all <?= $reward['is_sold_out'] ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-primary-300' ?>">
                                    <div class="flex items-start">
                                        <div class="w-5 h-5 rounded-full border-2 mr-3 mt-1 flex items-center justify-center flex-shrink-0"
                                             :class="selectedRewardId === <?= $reward['id'] ?> ? 'border-primary-500 bg-primary-500' : 'border-gray-300'">
                                            <i x-show="selectedRewardId === <?= $reward['id'] ?>" class="fas fa-check text-white text-xs"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="font-bold text-primary-600">R$ <?= number_format($reward['min_amount'], 0, ',', '.') ?>+</span>
                                                <?php if ($reward['max_quantity']): ?>
                                                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded">
                                                        <?= $reward['remaining'] ?>/<?= $reward['max_quantity'] ?> disponíveis
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <h4 class="font-semibold text-gray-900"><?= esc($reward['title']) ?></h4>
                                            <p class="text-sm text-gray-600 mt-1"><?= esc($reward['description']) ?></p>
                                            <?php if ($reward['delivery_date']): ?>
                                                <p class="text-xs text-gray-500 mt-2">
                                                    <i class="fas fa-truck mr-1"></i>Entrega: <?= date('m/Y', strtotime($reward['delivery_date'])) ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($reward['is_sold_out']): ?>
                                                <span class="inline-block mt-2 text-xs bg-red-100 text-red-600 px-2 py-1 rounded font-semibold">ESGOTADO</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Valor da Doação -->
                        <div>
                            <label class="form-label">Valor da Doação *</label>

                            <!-- Valores sugeridos -->
                            <div class="grid grid-cols-4 gap-3 mb-3">
                                <button type="button"
                                        @click="amount = 20"
                                        :class="amount === 20 ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
                                        class="py-3 px-4 border-2 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                                    R$ 20
                                </button>
                                <button type="button"
                                        @click="amount = 50"
                                        :class="amount === 50 ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
                                        class="py-3 px-4 border-2 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                                    R$ 50
                                </button>
                                <button type="button"
                                        @click="amount = 100"
                                        :class="amount === 100 ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
                                        class="py-3 px-4 border-2 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                                    R$ 100
                                </button>
                                <button type="button"
                                        @click="amount = 200"
                                        :class="amount === 200 ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
                                        class="py-3 px-4 border-2 rounded-lg text-center hover:border-primary-500 hover:bg-primary-50 transition-colors">
                                    R$ 200
                                </button>
                            </div>

                            <!-- Valor customizado -->
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg">R$</span>
                                <input type="number"
                                       id="amount"
                                       name="amount"
                                       x-model.number="amount"
                                       required
                                       :min="minAmount"
                                       step="0.01"
                                       class="form-input pl-12 text-lg font-bold text-primary-600"
                                       :placeholder="minAmount.toFixed(2).replace('.', ',')">
                            </div>
                        </div>

                        <!-- Método de Pagamento -->
                        <div>
                            <label class="form-label">Método de Pagamento *</label>
                            <div class="grid grid-cols-3 gap-4">
                                <div @click="paymentMethod = 'pix'"
                                     :class="paymentMethod === 'pix' ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
                                     class="border-2 rounded-xl p-4 text-center transition-all hover:border-primary-300 cursor-pointer">
                                    <input type="radio" name="payment_method" value="pix" x-model="paymentMethod" class="hidden">
                                    <i class="fas fa-qrcode text-3xl text-primary-500 mb-2"></i>
                                    <div class="font-bold text-gray-900">PIX</div>
                                    <div class="text-xs text-gray-600">Aprovação instantânea</div>
                                    <div class="text-xs text-primary-600 font-semibold mt-1">Taxa: R$ 0,95</div>
                                </div>

                                <div @click="paymentMethod = 'credit_card'"
                                     :class="paymentMethod === 'credit_card' ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
                                     class="border-2 rounded-xl p-4 text-center transition-all hover:border-primary-300 cursor-pointer">
                                    <input type="radio" name="payment_method" value="credit_card" x-model="paymentMethod" class="hidden">
                                    <i class="fas fa-credit-card text-3xl text-primary-500 mb-2"></i>
                                    <div class="font-bold text-gray-900">Cartão</div>
                                    <div class="text-xs text-gray-600">Crédito ou débito</div>
                                    <div class="text-xs text-primary-600 font-semibold mt-1">Taxa: R$ 0,49 + 1,99%</div>
                                </div>

                                <div @click="paymentMethod = 'boleto'"
                                     :class="paymentMethod === 'boleto' ? 'border-primary-500 bg-primary-50' : 'border-gray-300'"
                                     class="border-2 rounded-xl p-4 text-center transition-all hover:border-primary-300 cursor-pointer">
                                    <input type="radio" name="payment_method" value="boleto" x-model="paymentMethod" class="hidden">
                                    <i class="fas fa-barcode text-3xl text-primary-500 mb-2"></i>
                                    <div class="font-bold text-gray-900">Boleto</div>
                                    <div class="text-xs text-gray-600">Vence em 3 dias</div>
                                    <div class="text-xs text-primary-600 font-semibold mt-1">Taxa: R$ 0,99</div>
                                </div>
                            </div>
                        </div>

                        <!-- Opção: Doação Recorrente (apenas para campanhas recorrentes) -->
                        <?php if ($campaign['campaign_type'] === 'recorrente'): ?>
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-6">
                            <div class="flex items-start">
                                <input type="checkbox"
                                       id="is_recurring"
                                       name="is_recurring"
                                       value="1"
                                       x-model="isRecurring"
                                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                                <div class="ml-3 flex-1">
                                    <label for="is_recurring" class="cursor-pointer">
                                        <div class="flex items-center mb-1">
                                            <span class="font-bold text-gray-900 text-lg">
                                                <i class="fas fa-redo text-blue-600 mr-2"></i>
                                                Fazer doação recorrente
                                            </span>
                                            <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded-full">
                                                Apoio contínuo
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3">
                                            Sua doação será <strong>renovada automaticamente</strong> todo mês.
                                            Você pode cancelar a qualquer momento.
                                        </p>
                                    </label>

                                    <!-- Seleção de Frequência -->
                                    <div x-show="isRecurring" x-collapse class="mt-4">
                                        <label for="cycle" class="form-label text-sm">Frequência da doação:</label>
                                        <select id="cycle" name="cycle" class="form-input text-sm">
                                            <option value="monthly">Mensal</option>
                                            <option value="quarterly">Trimestral (a cada 3 meses)</option>
                                            <option value="semiannual">Semestral (a cada 6 meses)</option>
                                            <option value="yearly">Anual</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-info-circle"></i>
                                            A primeira cobrança ocorre imediatamente. As próximas seguem o ciclo escolhido.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Opção: Doador Paga as Taxas -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6">
                            <div class="flex items-start">
                                <input type="checkbox"
                                       id="donor_pays_fees"
                                       name="donor_pays_fees"
                                       value="1"
                                       x-model="donorPaysGatewayFee"
                                       class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500 mt-1">
                                <div class="ml-3 flex-1">
                                    <label for="donor_pays_fees" class="cursor-pointer">
                                        <div class="flex items-center mb-1">
                                            <span class="font-bold text-gray-900 text-lg">
                                                <i class="fas fa-hand-holding-heart text-green-600 mr-2"></i>
                                                Eu quero pagar as taxas do gateway
                                            </span>
                                            <span class="ml-2 bg-blue-500 text-white text-sm font-bold px-3 py-1.5 rounded-full shadow-md">
                                                RECOMENDADO
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            Ao marcar esta opção, você paga as taxas do gateway de pagamento. Assim,
                                            <strong>100% do valor da sua doação</strong> vai direto para o criador da campanha!
                                            Além disso, você <strong>PODE contribuir com um adicional</strong> para manter a plataforma ativa.
                                        </p>
                                    </label>

                                    <!-- Cálculo Detalhado (aparece quando checkbox marcado) -->
                                    <div x-show="donorPaysGatewayFee && amount > 0" x-collapse class="mt-4 bg-white rounded-lg p-4 border border-green-200">
                                        <div class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                            <i class="fas fa-calculator text-green-600 mr-2"></i>
                                            Detalhamento Transparente:
                                        </div>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between items-center bg-green-50 p-2 rounded">
                                                <span class="text-gray-700 font-semibold">Valor da doação (para o criador):</span>
                                                <span class="font-bold text-green-700 text-lg" x-text="formatMoney(amount)">R$ 0,00</span>
                                            </div>
                                            <div class="flex justify-between items-center text-orange-600">
                                                <span class="flex items-center">
                                                    <i class="fas fa-plus text-xs mr-1"></i>
                                                    Taxa do gateway (<span x-text="paymentMethodName"></span>):
                                                </span>
                                                <span class="font-semibold" x-text="formatMoney(gatewayFee)">R$ 0,00</span>
                                            </div>

                                            <!-- Checkbox Doar para Plataforma (não aparece se já estiver doando para a plataforma) -->
                                            <?php if ($campaign['slug'] !== 'mantenha-a-plataforma-ativa'): ?>
                                            <div class="border-t border-gray-300 pt-3 mt-3">
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox"
                                                           id="donate_to_platform"
                                                           name="donate_to_platform"
                                                           value="1"
                                                           x-model="donateToPlatform"
                                                           class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500 mr-2">
                                                    <span class="text-sm text-gray-700 font-semibold">
                                                        Doar para a plataforma após essa doação
                                                    </span>
                                                </label>
                                                <p class="text-xs text-gray-500 ml-6 mt-1">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    Você será redirecionado para contribuir com a manutenção da plataforma
                                                </p>
                                            </div>
                                            <?php endif; ?>

                                            <div class="flex justify-between items-center text-blue-600 text-xs mt-2">
                                                <span class="flex items-center">
                                                    <i class="fas fa-plus text-xs mr-1"></i>
                                                    Arredondamento:
                                                </span>
                                                <span class="font-semibold" x-text="formatMoney(roundingExtra)">R$ 0,00</span>
                                            </div>

                                            <!-- Total Final -->
                                            <div class="border-t-2 border-gray-400 pt-2 mt-2">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-900 font-black text-base">TOTAL A PAGAR:</span>
                                                    <span class="font-black text-primary-600 text-2xl" x-text="formatMoney(totalAmount)">R$ 0,00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 text-xs text-gray-500 bg-blue-50 rounded p-2 border border-blue-200">
                                            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                            <strong>Valores sempre arredondados para cima:</strong> Facilitamos o pagamento sem centavos.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dados do Doador -->
                        <div class="border-t pt-6">
                            <h3 class="font-bold text-gray-900 mb-4">
                                <i class="fas fa-user mr-2 text-primary-500"></i>
                                Seus Dados
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label for="donor_name" class="form-label">Nome Completo *</label>
                                    <input type="text"
                                           id="donor_name"
                                           name="donor_name"
                                           required
                                           class="form-input"
                                           placeholder="Seu nome completo"
                                           value="<?= old('donor_name', isset($user['name']) ? $user['name'] : '') ?>">
                                </div>

                                <div>
                                    <label for="donor_email" class="form-label">Email *</label>
                                    <input type="email"
                                           id="donor_email"
                                           name="donor_email"
                                           required
                                           class="form-input"
                                           placeholder="seu@email.com"
                                           value="<?= old('donor_email', isset($user['email']) ? $user['email'] : '') ?>">
                                </div>

                                <div>
                                    <label for="donor_cpf" class="form-label">CPF *</label>
                                    <input type="text"
                                           id="donor_cpf"
                                           name="donor_cpf"
                                           class="form-input"
                                           placeholder="000.000.000-00"
                                           maxlength="14"
                                           required
                                           value="<?= old('donor_cpf', isset($user['cpf']) ? $user['cpf'] : '') ?>">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="message" class="form-label">Mensagem (opcional)</label>
                                    <textarea id="message"
                                              name="message"
                                              rows="3"
                                              class="form-input"
                                              placeholder="Deixe uma mensagem de apoio..."><?= old('message') ?></textarea>
                                </div>

                                <!-- Preferências de Notificações -->
                                <div class="md:col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <label class="form-label text-base mb-3 flex items-center">
                                        <i class="fas fa-bell text-blue-600 mr-2"></i>
                                        Como você gostaria de receber atualizações sobre a campanha?
                                    </label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="checkbox"
                                                   id="notify_push"
                                                   name="notify_push"
                                                   value="1"
                                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <label for="notify_push" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                                <i class="fas fa-mobile-alt text-blue-500 mr-1"></i>
                                                Receber notificações push no navegador
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox"
                                                   id="notify_email"
                                                   name="notify_email"
                                                   value="1"
                                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <label for="notify_email" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                                <i class="fas fa-envelope text-blue-500 mr-1"></i>
                                                Receber atualizações por email
                                            </label>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="fas fa-info-circle"></i>
                                        Você receberá atualizações sobre o progresso da campanha e agradecimentos do criador.
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="is_anonymous"
                                               value="1"
                                               class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                        <span class="ml-2 text-sm text-gray-700">
                                            Doar anonimamente (seu nome não será exibido)
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Botão Doar -->
                        <button type="submit" class="w-full btn-primary text-lg py-4">
                            <i class="fas fa-heart mr-2"></i>
                            Continuar para Pagamento
                        </button>

                        <!-- Aviso de Segurança -->
                        <div class="text-center text-sm text-gray-500">
                            <i class="fas fa-lock mr-1"></i>
                            Pagamento 100% seguro via Asaas
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
// CPF Mask with Alpine.js (optional enhancement)
document.addEventListener('alpine:init', () => {
    const cpfInput = document.getElementById('donor_cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });
    }
});
</script>

<?= $this->endSection() ?>
