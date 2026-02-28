<?= $this->extend('layout/app') ?>

<?php helper('recaptcha'); ?>

<?= $this->section('content') ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">

        <!-- Cabeçalho -->
        <div class="text-center">
            <div class="flex justify-center">
                <img src="<?= base_url('assets/images/Logo-favicon-doarfazbem-transparente.png') ?>" alt="DoarFazBem" class="h-20 w-20 object-contain">
            </div>
            <h2 class="mt-4 text-3xl font-bold text-gray-900">
                Crie sua conta
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Junte-se à maior plataforma de crowdfunding social do Brasil
            </p>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-2xl shadow-card p-8">

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-error mb-6">
                    <p class="text-red-800 text-sm"><?= session()->getFlashdata('error') ?></p>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert-error mb-6">
                    <ul class="text-red-800 text-sm space-y-1">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li>• <?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('register') ?>" method="POST" class="space-y-6" id="register-form"
                  x-data="{
                      showPassword: false,
                      showPasswordConfirm: false,
                      password: '',
                      passwordConfirm: '',
                      get passwordsMatch() {
                          return this.password === this.passwordConfirm && this.password.length >= 8;
                      }
                  }">
                <?= csrf_field() ?>
                <?= recaptcha_field() ?>

                <!-- Nome Completo -->
                <div>
                    <label for="name" class="form-label">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="<?= old('name') ?>"
                           class="form-input"
                           placeholder="João Silva"
                           required>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="form-label">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?= old('email') ?>"
                           class="form-input"
                           placeholder="seu@email.com"
                           required>
                </div>

                <!-- Telefone e CPF (Linha) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- Telefone -->
                    <div>
                        <label for="phone" class="form-label">
                            Telefone (Opcional)
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               value="<?= old('phone') ?>"
                               class="form-input"
                               placeholder="11987654321"
                               maxlength="11">
                        <p class="text-xs text-gray-500 mt-1">Apenas números (DDD + número)</p>
                    </div>

                    <!-- CPF -->
                    <div>
                        <label for="cpf" class="form-label">
                            CPF (Opcional)
                        </label>
                        <input type="text"
                               id="cpf"
                               name="cpf"
                               value="<?= old('cpf') ?>"
                               class="form-input"
                               placeholder="123.456.789-00"
                               maxlength="14">
                        <p class="text-xs text-gray-500 mt-1">Formato: 123.456.789-00</p>
                    </div>
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="form-label">
                        Senha <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'"
                               id="password"
                               name="password"
                               x-model="password"
                               class="form-input pr-12"
                               placeholder="••••••••"
                               required
                               minlength="8">
                        <button type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Mínimo de 8 caracteres</p>
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <label for="password_confirm" class="form-label">
                        Confirmar Senha <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input :type="showPasswordConfirm ? 'text' : 'password'"
                               id="password_confirm"
                               name="password_confirm"
                               x-model="passwordConfirm"
                               class="form-input pr-12"
                               :class="passwordConfirm && !passwordsMatch ? 'border-red-500' : ''"
                               placeholder="••••••••"
                               required
                               minlength="8">
                        <button type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg x-show="!showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg x-show="showPasswordConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                    <p x-show="passwordConfirm && !passwordsMatch" class="text-xs text-red-600 mt-1" style="display: none;">
                        As senhas não coincidem
                    </p>
                    <p x-show="passwordsMatch" class="text-xs text-green-600 mt-1" style="display: none;">
                        <i class="fas fa-check"></i> As senhas coincidem
                    </p>
                </div>

                <!-- Checkbox Termos de Uso -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms"
                               name="terms"
                               type="checkbox"
                               class="w-4 h-4 border border-gray-300 rounded bg-white focus:ring-2 focus:ring-primary-500"
                               required>
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="text-gray-700">
                            Eu concordo com os
                            <a href="<?= base_url('termos') ?>" class="text-primary-600 hover:text-primary-700 font-semibold" target="_blank">
                                Termos de Uso
                            </a>
                            e
                            <a href="<?= base_url('privacidade') ?>" class="text-primary-600 hover:text-primary-700 font-semibold" target="_blank">
                                Política de Privacidade
                            </a>
                            <span class="text-red-500">*</span>
                        </label>
                    </div>
                </div>

                <!-- Botão Cadastrar -->
                <div>
                    <button type="submit" class="w-full btn-primary" id="submit-btn">
                        <i class="fas fa-user-plus mr-2"></i>Criar Minha Conta
                    </button>
                </div>

                <!-- Aviso reCAPTCHA -->
                <div class="text-xs text-gray-500 text-center">
                    Este site é protegido por reCAPTCHA e as
                    <a href="https://policies.google.com/privacy" target="_blank" class="text-primary-600 hover:underline">Políticas de Privacidade</a> e
                    <a href="https://policies.google.com/terms" target="_blank" class="text-primary-600 hover:underline">Termos de Serviço</a>
                    do Google se aplicam.
                </div>
            </form>

            <!-- Divisor -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Ou cadastre-se com</span>
                </div>
            </div>

            <!-- Cadastro com Google -->
            <div>
                <a href="<?= base_url('auth/google') ?>" class="w-full flex items-center justify-center gap-3 px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuar com Google
                </a>
            </div>

            <!-- Link para Login -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Já tem uma conta?
                    <a href="<?= base_url('login') ?>" class="text-primary-600 hover:text-primary-700 font-semibold">
                        Faça login
                    </a>
                </p>
            </div>
        </div>

        <!-- Diferenciais -->
        <div class="text-center">
            <div class="flex flex-wrap justify-center gap-3 mt-6">
                <span class="bg-white px-4 py-2 rounded-full text-xs font-semibold text-gray-700 shadow-sm">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i> 0% Taxa Campanhas Médicas
                </span>
                <span class="bg-white px-4 py-2 rounded-full text-xs font-semibold text-gray-700 shadow-sm">
                    <i class="fas fa-lock text-blue-500 mr-1"></i> 100% Seguro
                </span>
                <span class="bg-white px-4 py-2 rounded-full text-xs font-semibold text-gray-700 shadow-sm">
                    <i class="fas fa-infinity text-secondary-500 mr-1"></i> Grátis para sempre
                </span>
            </div>
        </div>
    </div>
</div>

<!-- reCAPTCHA v3 -->
<?= recaptcha_script() ?>
<?= recaptcha_execute('register-form', 'register') ?>

<!-- Script para formatação automática de CPF -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Formatação automática de CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = value;
            }
        });
    }
    // Password validation is now handled by Alpine.js
});
</script>

<?= $this->endSection() ?>
