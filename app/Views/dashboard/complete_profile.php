<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4 md:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-check text-3xl text-emerald-600"></i>
                </div>
                <h1 class="text-3xl font-black text-gray-900 mb-2">Complete seu Perfil</h1>
                <p class="text-gray-600">
                    Para criar campanhas e receber doações, precisamos de alguns dados adicionais.
                </p>
            </div>

            <!-- Mensagens -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700"><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg">
                    <p class="text-red-700 font-semibold mb-2">Por favor, corrija os erros:</p>
                    <ul class="list-disc list-inside text-red-600 text-sm">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form action="<?= base_url('dashboard/complete-profile') ?>" method="POST" class="bg-white rounded-xl shadow-sm p-6 md:p-8">

                <!-- Info do Usuário -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-user mr-2 text-emerald-500"></i>
                        Cadastrando como: <strong><?= esc($user['name']) ?></strong> (<?= esc($user['email']) ?>)
                    </p>
                </div>

                <!-- Dados Pessoais -->
                <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-id-card text-emerald-500 mr-2"></i>
                    Dados Pessoais
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- CPF/CNPJ -->
                    <div>
                        <label for="cpf_cnpj" class="block text-sm font-semibold text-gray-700 mb-1">CPF ou CNPJ *</label>
                        <input type="text"
                               id="cpf_cnpj"
                               name="cpf_cnpj"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="000.000.000-00"
                               maxlength="18"
                               value="<?= old('cpf_cnpj') ?: esc($user['cpf'] ?? '') ?>">
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Telefone com DDD *</label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="(47) 99999-9999"
                               maxlength="15"
                               value="<?= old('phone') ?: esc($user['phone'] ?? '') ?>">
                    </div>
                </div>

                <!-- Data de Nascimento -->
                <div class="mb-6">
                    <label for="birth_date" class="block text-sm font-semibold text-gray-700 mb-1">Data de Nascimento *</label>
                    <input type="date"
                           id="birth_date"
                           name="birth_date"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                           value="<?= old('birth_date') ?: esc($user['birth_date'] ?? '') ?>">
                </div>

                <!-- Endereço -->
                <h3 class="font-bold text-gray-900 mb-4 flex items-center border-t border-gray-200 pt-6">
                    <i class="fas fa-map-marker-alt text-emerald-500 mr-2"></i>
                    Endereço Completo
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- CEP -->
                    <div>
                        <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-1">CEP *</label>
                        <input type="text"
                               id="postal_code"
                               name="postal_code"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="00000-000"
                               maxlength="9"
                               value="<?= old('postal_code') ?: esc($user['postal_code'] ?? '') ?>">
                    </div>

                    <!-- Rua -->
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-semibold text-gray-700 mb-1">Rua/Logradouro *</label>
                        <input type="text"
                               id="address"
                               name="address"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="Ex: Rua das Flores"
                               value="<?= old('address') ?: esc($user['address'] ?? '') ?>">
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <!-- Número -->
                    <div>
                        <label for="address_number" class="block text-sm font-semibold text-gray-700 mb-1">Número *</label>
                        <input type="text"
                               id="address_number"
                               name="address_number"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="123"
                               maxlength="20"
                               value="<?= old('address_number') ?: esc($user['address_number'] ?? '') ?>">
                    </div>

                    <!-- Complemento -->
                    <div>
                        <label for="address_complement" class="block text-sm font-semibold text-gray-700 mb-1">Complemento</label>
                        <input type="text"
                               id="address_complement"
                               name="address_complement"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="Apto, Sala..."
                               maxlength="100"
                               value="<?= old('address_complement') ?: esc($user['address_complement'] ?? '') ?>">
                    </div>

                    <!-- Bairro -->
                    <div>
                        <label for="neighborhood" class="block text-sm font-semibold text-gray-700 mb-1">Bairro *</label>
                        <input type="text"
                               id="neighborhood"
                               name="neighborhood"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="Centro"
                               value="<?= old('neighborhood') ?: esc($user['province'] ?? '') ?>">
                    </div>

                    <!-- Cidade -->
                    <div>
                        <label for="address_city" class="block text-sm font-semibold text-gray-700 mb-1">Cidade *</label>
                        <input type="text"
                               id="address_city"
                               name="address_city"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"
                               placeholder="Blumenau"
                               value="<?= old('address_city') ?: esc($user['city'] ?? '') ?>">
                    </div>
                </div>

                <?php $selectedState = old('address_state') ?: ($user['state'] ?? ''); ?>
                <div class="mb-6">
                    <!-- Estado -->
                    <label for="address_state" class="block text-sm font-semibold text-gray-700 mb-1">Estado *</label>
                    <select id="address_state" name="address_state" required class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                        <option value="">Selecione o estado</option>
                        <option value="AC" <?= $selectedState === 'AC' ? 'selected' : '' ?>>Acre</option>
                        <option value="AL" <?= $selectedState === 'AL' ? 'selected' : '' ?>>Alagoas</option>
                        <option value="AP" <?= $selectedState === 'AP' ? 'selected' : '' ?>>Amapa</option>
                        <option value="AM" <?= $selectedState === 'AM' ? 'selected' : '' ?>>Amazonas</option>
                        <option value="BA" <?= $selectedState === 'BA' ? 'selected' : '' ?>>Bahia</option>
                        <option value="CE" <?= $selectedState === 'CE' ? 'selected' : '' ?>>Ceara</option>
                        <option value="DF" <?= $selectedState === 'DF' ? 'selected' : '' ?>>Distrito Federal</option>
                        <option value="ES" <?= $selectedState === 'ES' ? 'selected' : '' ?>>Espirito Santo</option>
                        <option value="GO" <?= $selectedState === 'GO' ? 'selected' : '' ?>>Goias</option>
                        <option value="MA" <?= $selectedState === 'MA' ? 'selected' : '' ?>>Maranhao</option>
                        <option value="MT" <?= $selectedState === 'MT' ? 'selected' : '' ?>>Mato Grosso</option>
                        <option value="MS" <?= $selectedState === 'MS' ? 'selected' : '' ?>>Mato Grosso do Sul</option>
                        <option value="MG" <?= $selectedState === 'MG' ? 'selected' : '' ?>>Minas Gerais</option>
                        <option value="PA" <?= $selectedState === 'PA' ? 'selected' : '' ?>>Para</option>
                        <option value="PB" <?= $selectedState === 'PB' ? 'selected' : '' ?>>Paraiba</option>
                        <option value="PR" <?= $selectedState === 'PR' ? 'selected' : '' ?>>Parana</option>
                        <option value="PE" <?= $selectedState === 'PE' ? 'selected' : '' ?>>Pernambuco</option>
                        <option value="PI" <?= $selectedState === 'PI' ? 'selected' : '' ?>>Piaui</option>
                        <option value="RJ" <?= $selectedState === 'RJ' ? 'selected' : '' ?>>Rio de Janeiro</option>
                        <option value="RN" <?= $selectedState === 'RN' ? 'selected' : '' ?>>Rio Grande do Norte</option>
                        <option value="RS" <?= $selectedState === 'RS' ? 'selected' : '' ?>>Rio Grande do Sul</option>
                        <option value="RO" <?= $selectedState === 'RO' ? 'selected' : '' ?>>Rondonia</option>
                        <option value="RR" <?= $selectedState === 'RR' ? 'selected' : '' ?>>Roraima</option>
                        <option value="SC" <?= $selectedState === 'SC' ? 'selected' : '' ?>>Santa Catarina</option>
                        <option value="SP" <?= $selectedState === 'SP' ? 'selected' : '' ?>>Sao Paulo</option>
                        <option value="SE" <?= $selectedState === 'SE' ? 'selected' : '' ?>>Sergipe</option>
                        <option value="TO" <?= $selectedState === 'TO' ? 'selected' : '' ?>>Tocantins</option>
                    </select>
                </div>

                <!-- Aviso -->
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
                    <p class="text-emerald-800 text-sm">
                        <i class="fas fa-shield-alt mr-2"></i>
                        <strong>Seus dados estao seguros!</strong> Usamos criptografia e nao compartilhamos suas informacoes com terceiros.
                    </p>
                </div>

                <!-- Botões -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 px-6 py-3 bg-emerald-500 text-white font-bold rounded-lg hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-check mr-2"></i>Salvar e Continuar
                    </button>
                    <a href="<?= base_url('dashboard') ?>" class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-colors text-center">
                        Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Máscara CPF/CNPJ
    const cpfCnpjInput = document.getElementById('cpf_cnpj');
    cpfCnpjInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');

        if (value.length <= 11) {
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        }

        e.target.value = value;
    });

    // Máscara Telefone
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');

        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }

        e.target.value = value;
    });

    // Máscara CEP e busca automática
    const postalCodeInput = document.getElementById('postal_code');
    const addressInput = document.getElementById('address');
    const neighborhoodInput = document.getElementById('neighborhood');
    const cityInput = document.getElementById('address_city');
    const stateSelect = document.getElementById('address_state');

    postalCodeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');

        if (value.length > 5) {
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }

        e.target.value = value;

        if (value.replace(/\D/g, '').length === 8) {
            buscarCep(value.replace(/\D/g, ''));
        }
    });

    async function buscarCep(cep) {
        try {
            postalCodeInput.classList.add('animate-pulse');

            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();

            if (data.erro) {
                alert('CEP nao encontrado. Por favor, preencha o endereco manualmente.');
                postalCodeInput.classList.remove('animate-pulse');
                return;
            }

            addressInput.value = data.logradouro || '';
            neighborhoodInput.value = data.bairro || '';
            cityInput.value = data.localidade || '';

            if (data.uf) {
                stateSelect.value = data.uf;
            }

            document.getElementById('address_number').focus();

            postalCodeInput.classList.remove('animate-pulse');

        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
            postalCodeInput.classList.remove('animate-pulse');
        }
    }
});
</script>

<?= $this->endSection() ?>
