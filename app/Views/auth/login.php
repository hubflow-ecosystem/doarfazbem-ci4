<?= $this->extend('layout/app') ?>

<?php helper('recaptcha'); ?>

<?= $this->section('head') ?>
<style>
    @keyframes blob {
        0%, 100% {
            transform: translate(0, 0) scale(1);
        }
        25% {
            transform: translate(20px, -50px) scale(1.1);
        }
        50% {
            transform: translate(-20px, 20px) scale(0.9);
        }
        75% {
            transform: translate(50px, 30px) scale(1.05);
        }
    }

    .animate-blob {
        animation: blob 7s infinite;
    }

    .animation-delay-2000 {
        animation-delay: 2s;
    }

    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">

    <!-- Background decorativo com círculos animados -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-emerald-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-teal-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-cyan-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10">

        <!-- Cabeçalho -->
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-800">
                Entrar
            </h2>
            <p class="mt-2 text-base text-gray-600">
                Acesse sua conta para continuar
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

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert-success mb-6" id="success-alert">
                    <p class="text-green-800 text-sm"><?= session()->getFlashdata('success') ?></p>
                </div>
                <script>
                    setTimeout(function() {
                        var alert = document.getElementById('success-alert');
                        if (alert) {
                            alert.style.transition = 'opacity 0.5s ease-out';
                            alert.style.opacity = '0';
                            setTimeout(function() {
                                alert.remove();
                            }, 500);
                        }
                    }, 3000);
                </script>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST" class="space-y-6" id="login-form">
                <?= csrf_field() ?>
                <?= recaptcha_field() ?>

                <!-- Email -->
                <div>
                    <label for="email" class="form-label">
                        Email
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?= old('email') ?>"
                           class="form-input"
                           placeholder="seu@email.com"
                           required
                           autofocus>
                </div>

                <!-- Senha -->
                <div x-data="{ showPassword: false }">
                    <label for="password" class="form-label">
                        Senha
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'"
                               id="password"
                               name="password"
                               class="form-input pr-12"
                               placeholder="••••••••"
                               required>
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
                </div>

                <!-- Lembrar-me e Esqueci a senha -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember"
                               name="remember"
                               type="checkbox"
                               class="w-4 h-4 border border-gray-300 rounded bg-white focus:ring-2 focus:ring-primary-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Lembrar-me
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="<?= base_url('forgot-password') ?>" class="text-primary-600 hover:text-primary-700 font-semibold">
                            Esqueceu a senha?
                        </a>
                    </div>
                </div>

                <!-- Botão Entrar -->
                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transform transition-all duration-200 hover:scale-[1.02] hover:shadow-xl">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-emerald-200 group-hover:text-white transition-colors"></i>
                        </span>
                        Entrar na plataforma
                    </button>
                </div>

                <!-- Aviso reCAPTCHA -->
                <div class="text-xs text-gray-500 text-center">
                    Este site é protegido por reCAPTCHA
                </div>
            </form>

            <!-- Divisor "OU" -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500 font-medium">Ou continue com</span>
                </div>
            </div>

            <!-- Botão Login com Google -->
            <div>
                <a href="<?= base_url('auth/google') ?>"
                   class="group relative w-full flex justify-center items-center py-3 px-4 border-2 border-gray-200 text-base font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transform transition-all duration-200 hover:scale-[1.02] hover:shadow-lg">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-4">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    </span>
                    <span class="ml-3">Login com Google</span>
                </a>
            </div>

            <!-- Link para Cadastro -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Não tem uma conta?
                    <a href="<?= base_url('register') ?>" class="text-primary-600 hover:text-primary-700 font-semibold">
                        Cadastre-se gratuitamente
                    </a>
                </p>
            </div>
        </div>

        <!-- Diferenciais -->
        <div class="text-center">
            <div class="flex flex-wrap justify-center gap-3 mt-6">
                <span class="group bg-white hover:bg-emerald-50 px-5 py-2.5 rounded-full text-sm font-semibold text-gray-700 shadow-md hover:shadow-lg transition-all duration-200 border border-emerald-100">
                    <i class="fas fa-check-circle text-emerald-500 mr-1.5 group-hover:scale-110 transition-transform"></i> 0% Taxa Médicas
                </span>
                <span class="group bg-white hover:bg-blue-50 px-5 py-2.5 rounded-full text-sm font-semibold text-gray-700 shadow-md hover:shadow-lg transition-all duration-200 border border-blue-100">
                    <i class="fas fa-shield-alt text-blue-500 mr-1.5 group-hover:scale-110 transition-transform"></i> 100% Seguro
                </span>
                <span class="group bg-white hover:bg-teal-50 px-5 py-2.5 rounded-full text-sm font-semibold text-gray-700 shadow-md hover:shadow-lg transition-all duration-200 border border-teal-100">
                    <i class="fas fa-chart-line text-teal-500 mr-1.5 group-hover:scale-110 transition-transform"></i> Transparente
                </span>
            </div>
        </div>
    </div>
</div>

<!-- reCAPTCHA v3 -->
<?= recaptcha_script() ?>
<?= recaptcha_execute('login-form', 'login') ?>

<?= $this->endSection() ?>
