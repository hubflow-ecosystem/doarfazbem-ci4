<?= $this->extend('layout/auth') ?>

<?php helper('recaptcha'); ?>

<?= $this->section('head') ?>
<style>
  /* Animação suave de gradiente no painel esquerdo */
  @keyframes gradient-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }
  .animate-gradient {
    background-size: 200% 200%;
    animation: gradient-shift 15s ease infinite;
  }

  /* Animação de entrada dos elementos */
  @keyframes fade-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .animate-fade-up {
    animation: fade-up 0.6s ease-out forwards;
  }
  .animate-fade-up-delay-1 { animation-delay: 0.1s; opacity: 0; }
  .animate-fade-up-delay-2 { animation-delay: 0.2s; opacity: 0; }
  .animate-fade-up-delay-3 { animation-delay: 0.3s; opacity: 0; }
  .animate-fade-up-delay-4 { animation-delay: 0.4s; opacity: 0; }

  /* Formas decorativas */
  .circle-decoration {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
    pointer-events: none;
  }

  /* Input focus ring verde */
  .auth-input:focus {
    border-color: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="min-h-screen flex">

  <!-- ============================================ -->
  <!-- PAINEL ESQUERDO - Marketing / Branding       -->
  <!-- ============================================ -->
  <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-900 animate-gradient">

    <!-- Formas decorativas de fundo -->
    <div class="circle-decoration w-96 h-96 bg-white -top-20 -left-20" style="opacity: 0.05;"></div>
    <div class="circle-decoration w-72 h-72 bg-emerald-300 bottom-20 -right-10" style="opacity: 0.08;"></div>
    <div class="circle-decoration w-48 h-48 bg-teal-400 top-1/3 left-1/4" style="opacity: 0.06;"></div>

    <!-- Conteúdo do painel esquerdo -->
    <div class="relative z-10 flex flex-col justify-between p-12 xl:p-16 w-full">

      <!-- Logo -->
      <div class="animate-fade-up">
        <a href="<?= base_url() ?>" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center group-hover:bg-white/30 transition-all">
            <i class="fas fa-heart text-white text-lg"></i>
          </div>
          <span class="text-white font-bold text-xl tracking-tight">DoarFazBem</span>
        </a>
      </div>

      <!-- Texto principal com SPIN Selling -->
      <div class="space-y-8 my-auto">
        <div class="animate-fade-up animate-fade-up-delay-1">
          <p class="text-emerald-200 text-sm font-semibold uppercase tracking-wider mb-4">
            A plataforma de doações mais justa do Brasil
          </p>
          <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight">
            Transforme vidas<br>
            <span class="text-emerald-300">com um clique</span>
          </h1>
          <p class="text-emerald-100/80 text-lg mt-6 max-w-md leading-relaxed">
            Crie campanhas, receba doações e ajude quem mais precisa. Taxas justas, total transparência.
          </p>
        </div>

        <!-- Diferenciais com ícones -->
        <div class="space-y-4 animate-fade-up animate-fade-up-delay-2">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-emerald-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <i class="fas fa-check text-emerald-300 text-sm"></i>
            </div>
            <span class="text-white/90 text-base">Campanhas médicas com <strong class="text-emerald-300">0% de taxa</strong></span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-emerald-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <i class="fas fa-check text-emerald-300 text-sm"></i>
            </div>
            <span class="text-white/90 text-base">PIX, cartão e boleto <strong class="text-emerald-300">100% seguros</strong></span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-emerald-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <i class="fas fa-check text-emerald-300 text-sm"></i>
            </div>
            <span class="text-white/90 text-base">Saque em até <strong class="text-emerald-300">24 horas</strong> após confirmação</span>
          </div>
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-emerald-500/30 rounded-lg flex items-center justify-center flex-shrink-0">
              <i class="fas fa-check text-emerald-300 text-sm"></i>
            </div>
            <span class="text-white/90 text-base">Relatórios de <strong class="text-emerald-300">transparência total</strong> para doadores</span>
          </div>
        </div>
      </div>

      <!-- Depoimento -->
      <div class="animate-fade-up animate-fade-up-delay-3">
        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
          <div class="flex items-start space-x-1 mb-3">
            <i class="fas fa-star text-yellow-400 text-sm"></i>
            <i class="fas fa-star text-yellow-400 text-sm"></i>
            <i class="fas fa-star text-yellow-400 text-sm"></i>
            <i class="fas fa-star text-yellow-400 text-sm"></i>
            <i class="fas fa-star text-yellow-400 text-sm"></i>
          </div>
          <p class="text-white/90 text-sm italic leading-relaxed">
            "Conseguimos arrecadar R$ 47.000 em apenas 3 semanas para o tratamento da minha mãe.
            A plataforma é simples, segura e a equipe nos apoiou em cada etapa."
          </p>
          <div class="flex items-center mt-4">
            <div class="w-10 h-10 bg-emerald-400/30 rounded-full flex items-center justify-center">
              <span class="text-white font-semibold text-sm">AF</span>
            </div>
            <div class="ml-3">
              <p class="text-white font-semibold text-sm">Ana Ferreira</p>
              <p class="text-emerald-200/70 text-xs">Campanha Médica - Itajaí, SC</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- ============================================ -->
  <!-- PAINEL DIREITO - Formulário de Login          -->
  <!-- ============================================ -->
  <div class="w-full lg:w-1/2 flex items-center justify-center bg-white px-6 py-12 sm:px-12">
    <div class="w-full max-w-md">

      <!-- Logo mobile (aparece só em telas pequenas) -->
      <div class="lg:hidden flex items-center justify-center mb-8">
        <a href="<?= base_url() ?>" class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center">
            <i class="fas fa-heart text-white text-lg"></i>
          </div>
          <span class="text-gray-800 font-bold text-xl tracking-tight">DoarFazBem</span>
        </a>
      </div>

      <!-- Cabeçalho -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">
          Entrar
        </h2>
        <p class="text-gray-500 mt-2">
          Digite suas credenciais para acessar sua conta
        </p>
      </div>

      <!-- Alertas de erro/sucesso -->
      <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-start">
          <i class="fas fa-exclamation-circle mt-0.5 mr-2 flex-shrink-0"></i>
          <span><?= session()->getFlashdata('error') ?></span>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('errors')): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
          <ul class="space-y-1">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
              <li class="flex items-start"><i class="fas fa-circle text-[4px] mt-2 mr-2"></i><?= esc($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm flex items-start" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)">
          <i class="fas fa-check-circle mt-0.5 mr-2 flex-shrink-0"></i>
          <span><?= session()->getFlashdata('success') ?></span>
        </div>
      <?php endif; ?>

      <!-- Formulário -->
      <form action="<?= base_url('login') ?>" method="POST" id="login-form" class="space-y-5">
        <?= csrf_field() ?>
        <?= recaptcha_field() ?>

        <!-- E-mail -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
            E-mail
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <i class="fas fa-envelope text-gray-400 text-sm"></i>
            </div>
            <input type="email"
                   id="email"
                   name="email"
                   value="<?= old('email') ?>"
                   class="auth-input w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 text-sm transition-all duration-200 outline-none focus:border-emerald-500"
                   placeholder="seu@email.com"
                   required
                   autofocus>
          </div>
        </div>

        <!-- Senha -->
        <div x-data="{ showPassword: false }">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
            Senha
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <i class="fas fa-lock text-gray-400 text-sm"></i>
            </div>
            <input :type="showPassword ? 'text' : 'password'"
                   id="password"
                   name="password"
                   class="auth-input w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 text-sm transition-all duration-200 outline-none focus:border-emerald-500"
                   placeholder="Digite sua senha"
                   required
                   minlength="8">
            <button type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
              <i x-show="!showPassword" class="fas fa-eye text-sm"></i>
              <i x-show="showPassword" class="fas fa-eye-slash text-sm" style="display: none;"></i>
            </button>
          </div>
        </div>

        <!-- Lembrar-me e Esqueci a senha -->
        <div class="flex items-center justify-between">
          <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="remember" id="remember"
                   class="w-4 h-4 border-gray-300 rounded text-emerald-600 focus:ring-emerald-500 focus:ring-2 cursor-pointer">
            <span class="ml-2 text-sm text-gray-600">Lembrar de mim</span>
          </label>
          <a href="<?= base_url('forgot-password') ?>" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
            Esqueci minha senha
          </a>
        </div>

        <!-- Botão Entrar -->
        <button type="submit"
                class="w-full flex items-center justify-center py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-all duration-200 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
          Entrar
        </button>

        <!-- Aviso reCAPTCHA -->
        <p class="text-xs text-gray-400 text-center">
          Este site é protegido por reCAPTCHA
        </p>
      </form>

      <!-- Divisor OU -->
      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
          <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center">
          <span class="px-4 bg-white text-sm text-gray-500 font-medium">OU CONTINUE COM</span>
        </div>
      </div>

      <!-- Login com Google -->
      <a href="<?= base_url('auth/google') ?>"
         class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 rounded-xl text-gray-700 font-medium text-sm hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
        <svg class="w-5 h-5 mr-2.5" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        Continuar com Google
      </a>

      <!-- Link para Cadastro -->
      <p class="text-center mt-8 text-sm text-gray-600">
        Não tem uma conta?
        <a href="<?= base_url('register') ?>" class="text-emerald-600 hover:text-emerald-700 font-semibold transition-colors">
          Criar conta
        </a>
      </p>

    </div>
  </div>

</div>

<!-- reCAPTCHA v3 -->
<?= recaptcha_script() ?>
<?= recaptcha_execute('login-form', 'login') ?>

<?= $this->endSection() ?>
