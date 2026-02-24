<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12">
    <div class="container-custom">
        <div class="max-w-4xl mx-auto">

            <!-- Cabeçalho -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Meu Perfil</h1>
                <p class="text-gray-600">Gerencie suas informações pessoais</p>
            </div>

            <!-- Mensagens -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-2xl mr-3"></i>
                        <p class="font-semibold"><?= session()->getFlashdata('success') ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                        <p class="font-semibold"><?= session()->getFlashdata('error') ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-2xl mr-3 mt-1"></i>
                        <div>
                            <p class="font-semibold mb-2">Erros de validação:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Card do Perfil -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

                <!-- Header do Card -->
                <div class="bg-gradient-to-br from-primary-500 to-secondary-500 p-8 text-white">
                    <div class="flex items-center space-x-6">
                        <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center shadow-xl">
                            <?php if (session()->get('avatar')): ?>
                                <img src="<?= session()->get('avatar') ?>" alt="<?= esc($user['name']) ?>" class="w-full h-full rounded-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-user text-5xl text-white"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h2 class="text-3xl font-black mb-2"><?= esc($user['name']) ?></h2>
                            <p class="text-white/90"><i class="fas fa-envelope mr-2"></i><?= esc($user['email']) ?></p>
                            <p class="text-white/90 mt-1">
                                <i class="fas fa-calendar-alt mr-2"></i>Membro desde <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Formulário -->
                <div class="p-8">
                    <form action="<?= base_url('profile/update') ?>" method="POST">
                        <?= csrf_field() ?>

                        <div class="space-y-6">

                            <!-- Nome Completo -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user text-primary-500 mr-2"></i>Nome Completo
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="<?= esc($user['name']) ?>"
                                       class="form-input"
                                       required>
                            </div>

                            <!-- Email (Somente Leitura) -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-envelope text-primary-500 mr-2"></i>Email
                                </label>
                                <input type="email"
                                       id="email"
                                       value="<?= esc($user['email']) ?>"
                                       class="form-input bg-gray-100 cursor-not-allowed"
                                       disabled>
                                <p class="text-xs text-gray-500 mt-1">O email não pode ser alterado</p>
                            </div>

                            <!-- Telefone -->
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-phone text-primary-500 mr-2"></i>Telefone
                                </label>
                                <input type="text"
                                       id="phone"
                                       name="phone"
                                       value="<?= esc($user['phone'] ?? '') ?>"
                                       class="form-input"
                                       placeholder="(11) 98765-4321"
                                       maxlength="15">
                            </div>

                            <!-- CPF -->
                            <div>
                                <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-id-card text-primary-500 mr-2"></i>CPF
                                </label>
                                <?php if (!empty($user['cpf'])): ?>
                                <input type="text"
                                       id="cpf"
                                       value="<?= esc($user['cpf']) ?>"
                                       class="form-input bg-gray-100 cursor-not-allowed"
                                       disabled>
                                <p class="text-xs text-gray-500 mt-1">O CPF não pode ser alterado</p>
                                <?php else: ?>
                                <input type="text"
                                       id="cpf"
                                       name="cpf"
                                       value=""
                                       class="form-input"
                                       placeholder="000.000.000-00"
                                       maxlength="14">
                                <p class="text-xs text-gray-500 mt-1">Preencha seu CPF. Após salvar, não poderá ser alterado.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Data de Nascimento -->
                            <div>
                                <label for="birth_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-birthday-cake text-primary-500 mr-2"></i>Data de Nascimento
                                </label>
                                <input type="date"
                                       id="birth_date"
                                       name="birth_date"
                                       value="<?= esc($user['birth_date'] ?? '') ?>"
                                       class="form-input"
                                       max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                                <p class="text-xs text-gray-500 mt-1">Necessário para criar campanhas (mínimo 18 anos)</p>
                            </div>

                            <!-- Divider -->
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-map-marker-alt text-primary-500 mr-2"></i>Endereço
                                </h3>
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                                    <p class="text-sm text-blue-800">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Importante:</strong> Preencha seu endereço aqui para que ele seja preenchido automaticamente ao fazer doações com cartão de crédito.
                                    </p>
                                </div>
                            </div>

                            <!-- CEP -->
                            <div>
                                <label for="postal_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-mail-bulk text-primary-500 mr-2"></i>CEP
                                </label>
                                <input type="text"
                                       id="postal_code"
                                       name="postal_code"
                                       value="<?= esc($user['postal_code'] ?? '') ?>"
                                       class="form-input"
                                       placeholder="00000-000"
                                       maxlength="9">
                                <p class="text-xs text-gray-500 mt-1">
                                    <a href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" class="text-primary-500 hover:underline">
                                        <i class="fas fa-search"></i> Não sabe seu CEP?
                                    </a>
                                </p>
                            </div>

                            <!-- Endereço e Número -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-road text-primary-500 mr-2"></i>Endereço
                                    </label>
                                    <input type="text"
                                           id="address"
                                           name="address"
                                           value="<?= esc($user['address'] ?? '') ?>"
                                           class="form-input"
                                           placeholder="Rua, Avenida...">
                                </div>
                                <div>
                                    <label for="address_number" class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                                    <input type="text"
                                           id="address_number"
                                           name="address_number"
                                           value="<?= esc($user['address_number'] ?? '') ?>"
                                           class="form-input"
                                           placeholder="123">
                                </div>
                            </div>

                            <!-- Complemento e Bairro -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="address_complement" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-building text-primary-500 mr-2"></i>Complemento
                                    </label>
                                    <input type="text"
                                           id="address_complement"
                                           name="address_complement"
                                           value="<?= esc($user['address_complement'] ?? '') ?>"
                                           class="form-input"
                                           placeholder="Apto, Bloco, Casa...">
                                </div>
                                <div>
                                    <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-map text-primary-500 mr-2"></i>Bairro
                                    </label>
                                    <input type="text"
                                           id="province"
                                           name="province"
                                           value="<?= esc($user['province'] ?? '') ?>"
                                           class="form-input"
                                           placeholder="Centro">
                                </div>
                            </div>

                            <!-- Cidade e Estado -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-city text-primary-500 mr-2"></i>Cidade
                                    </label>
                                    <input type="text"
                                           id="city"
                                           name="city"
                                           value="<?= esc($user['city'] ?? '') ?>"
                                           class="form-input"
                                           placeholder="São Paulo">
                                </div>
                                <div>
                                    <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                                    <select id="state" name="state" class="form-input">
                                        <option value="">UF</option>
                                        <option value="AC" <?= ($user['state'] ?? '') === 'AC' ? 'selected' : '' ?>>AC</option>
                                        <option value="AL" <?= ($user['state'] ?? '') === 'AL' ? 'selected' : '' ?>>AL</option>
                                        <option value="AP" <?= ($user['state'] ?? '') === 'AP' ? 'selected' : '' ?>>AP</option>
                                        <option value="AM" <?= ($user['state'] ?? '') === 'AM' ? 'selected' : '' ?>>AM</option>
                                        <option value="BA" <?= ($user['state'] ?? '') === 'BA' ? 'selected' : '' ?>>BA</option>
                                        <option value="CE" <?= ($user['state'] ?? '') === 'CE' ? 'selected' : '' ?>>CE</option>
                                        <option value="DF" <?= ($user['state'] ?? '') === 'DF' ? 'selected' : '' ?>>DF</option>
                                        <option value="ES" <?= ($user['state'] ?? '') === 'ES' ? 'selected' : '' ?>>ES</option>
                                        <option value="GO" <?= ($user['state'] ?? '') === 'GO' ? 'selected' : '' ?>>GO</option>
                                        <option value="MA" <?= ($user['state'] ?? '') === 'MA' ? 'selected' : '' ?>>MA</option>
                                        <option value="MT" <?= ($user['state'] ?? '') === 'MT' ? 'selected' : '' ?>>MT</option>
                                        <option value="MS" <?= ($user['state'] ?? '') === 'MS' ? 'selected' : '' ?>>MS</option>
                                        <option value="MG" <?= ($user['state'] ?? '') === 'MG' ? 'selected' : '' ?>>MG</option>
                                        <option value="PA" <?= ($user['state'] ?? '') === 'PA' ? 'selected' : '' ?>>PA</option>
                                        <option value="PB" <?= ($user['state'] ?? '') === 'PB' ? 'selected' : '' ?>>PB</option>
                                        <option value="PR" <?= ($user['state'] ?? '') === 'PR' ? 'selected' : '' ?>>PR</option>
                                        <option value="PE" <?= ($user['state'] ?? '') === 'PE' ? 'selected' : '' ?>>PE</option>
                                        <option value="PI" <?= ($user['state'] ?? '') === 'PI' ? 'selected' : '' ?>>PI</option>
                                        <option value="RJ" <?= ($user['state'] ?? '') === 'RJ' ? 'selected' : '' ?>>RJ</option>
                                        <option value="RN" <?= ($user['state'] ?? '') === 'RN' ? 'selected' : '' ?>>RN</option>
                                        <option value="RS" <?= ($user['state'] ?? '') === 'RS' ? 'selected' : '' ?>>RS</option>
                                        <option value="RO" <?= ($user['state'] ?? '') === 'RO' ? 'selected' : '' ?>>RO</option>
                                        <option value="RR" <?= ($user['state'] ?? '') === 'RR' ? 'selected' : '' ?>>RR</option>
                                        <option value="SC" <?= ($user['state'] ?? '') === 'SC' ? 'selected' : '' ?>>SC</option>
                                        <option value="SP" <?= ($user['state'] ?? '') === 'SP' ? 'selected' : '' ?>>SP</option>
                                        <option value="SE" <?= ($user['state'] ?? '') === 'SE' ? 'selected' : '' ?>>SE</option>
                                        <option value="TO" <?= ($user['state'] ?? '') === 'TO' ? 'selected' : '' ?>>TO</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Botão Salvar -->
                            <div class="flex items-center justify-between pt-6 border-t">
                                <a href="<?= base_url('dashboard') ?>" class="text-gray-600 hover:text-gray-900 font-semibold transition-colors">
                                    <i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard
                                </a>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save mr-2"></i>Salvar Alterações
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cards de Informações Adicionais -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

                <!-- Status da Conta -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-shield-alt text-green-500 mr-2"></i>Status da Conta
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Email Verificado</span>
                            <?php if ($user['email_verified']): ?>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>Verificado
                                </span>
                            <?php else: ?>
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Pendente
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Tipo de Conta</span>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Segurança -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-lock text-blue-500 mr-2"></i>Segurança
                    </h3>
                    <div class="space-y-3">
                        <button onclick="openPasswordModal()" class="block text-primary-600 hover:text-primary-700 font-semibold transition-colors text-left">
                            <i class="fas fa-key mr-2"></i>Alterar Senha
                        </button>
                        <a href="#" class="block text-gray-400 cursor-not-allowed font-semibold">
                            <i class="fas fa-mobile-alt mr-2"></i>Autenticação em Dois Fatores
                            <span class="text-xs">(Em breve)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Alteração de Senha -->
<div id="passwordModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <!-- Header -->
        <div class="bg-gradient-to-br from-primary-500 to-secondary-500 p-6 rounded-t-2xl">
            <div class="flex items-center justify-between text-white">
                <h3 class="text-2xl font-black flex items-center">
                    <i class="fas fa-key mr-3"></i>Alterar Senha
                </h3>
                <button onclick="closePasswordModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Formulário -->
        <form action="<?= base_url('profile/change-password') ?>" method="POST" id="passwordForm" class="p-6">
            <?= csrf_field() ?>

            <div class="space-y-4">
                <!-- Senha Atual -->
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-primary-500 mr-2"></i>Senha Atual
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               class="form-input pr-10"
                               required>
                        <button type="button"
                                onclick="togglePassword('current_password')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Nova Senha -->
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-primary-500 mr-2"></i>Nova Senha
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="new_password"
                               name="new_password"
                               class="form-input pr-10"
                               minlength="8"
                               required>
                        <button type="button"
                                onclick="togglePassword('new_password')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Mínimo de 8 caracteres</p>
                </div>

                <!-- Confirmar Nova Senha -->
                <div>
                    <label for="confirm_password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-primary-500 mr-2"></i>Confirmar Nova Senha
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="form-input pr-10"
                               minlength="8"
                               required>
                        <button type="button"
                                onclick="togglePassword('confirm_password')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Alert de Segurança -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                Escolha uma senha forte com pelo menos 8 caracteres, incluindo letras e números.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t">
                <button type="button"
                        onclick="closePasswordModal()"
                        class="px-6 py-2 text-gray-700 hover:text-gray-900 font-semibold transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Alterar Senha
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= getenv('GOOGLE_MAPS_API_KEY') ?>&libraries=places"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    const cpfInput = document.getElementById('cpf');
    const postalCodeInput = document.getElementById('postal_code');
    const addressInput = document.getElementById('address');
    const provinceInput = document.getElementById('province');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');

    // Função para formatar telefone
    function formatPhone(value) {
        value = value.replace(/\D/g, '');

        if (value.length <= 2) {
            return value ? '(' + value : '';
        } else if (value.length <= 7) {
            return value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        } else if (value.length <= 10) {
            // Fixo: (XX) XXXX-XXXX
            return value.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else {
            // Celular com 9 dígitos: (XX) XXXXX-XXXX
            return value.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
        }
    }

    // Máscara de telefone: (00) 00000-0000 ou (00) 0000-0000
    if (phoneInput) {
        // Formatar valor inicial se existir
        if (phoneInput.value) {
            phoneInput.value = formatPhone(phoneInput.value);
        }

        // Aplicar máscara ao digitar
        phoneInput.addEventListener('input', function(e) {
            e.target.value = formatPhone(e.target.value);
        });
    }

    // Máscara de CPF: 000.000.000-00
    if (cpfInput && !cpfInput.disabled) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = value;
        });
    }

    // Máscara de CEP: 00000-000
    if (postalCodeInput) {
        postalCodeInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });

        // Buscar endereço automaticamente ao completar CEP
        postalCodeInput.addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');

            if (cep.length === 8) {
                // Buscar via ViaCEP (gratuito e sem limitações)
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            // Preencher campos automaticamente
                            if (addressInput) addressInput.value = data.logradouro || '';
                            if (provinceInput) provinceInput.value = data.bairro || '';
                            if (cityInput) cityInput.value = data.localidade || '';
                            if (stateInput) stateInput.value = data.uf || '';

                            // Focar no campo número
                            const numberInput = document.getElementById('address_number');
                            if (numberInput) numberInput.focus();

                            // Mostrar mensagem de sucesso
                            showToast('Endereço encontrado automaticamente!', 'success');
                        } else {
                            showToast('CEP não encontrado. Preencha manualmente.', 'warning');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar CEP:', error);
                        showToast('Erro ao buscar CEP. Preencha manualmente.', 'error');
                    });
            }
        });
    }

    // Função para mostrar mensagens toast
    function showToast(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});

// Funções do Modal de Senha
function openPasswordModal() {
    document.getElementById('passwordModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('passwordForm').reset();
}

// Toggle visibilidade da senha
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Validação do formulário de senha
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('As senhas não coincidem. Por favor, verifique.');
        return false;
    }

    if (newPassword.length < 8) {
        e.preventDefault();
        alert('A nova senha deve ter no mínimo 8 caracteres.');
        return false;
    }
});

// Fechar modal ao clicar fora
document.getElementById('passwordModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePasswordModal();
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('passwordModal').classList.contains('hidden')) {
        closePasswordModal();
    }
});
</script>

<?= $this->endSection() ?>
