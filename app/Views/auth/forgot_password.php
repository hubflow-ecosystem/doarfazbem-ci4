<?= $this->extend('layout/app') ?>

<?php helper('recaptcha'); ?>

<?= $this->section('content') ?>
<div class="bg-gradient-to-br from-primary-50 to-secondary-50 min-h-screen flex items-center py-12">
    <div class="container-custom max-w-md">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-heading-1 text-center mb-2">Recuperar Senha</h1>
            <p class="text-gray-600 text-center mb-8">Digite seu email para receber o link de recuperação</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-error mb-6"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('forgot-password') ?>" method="POST" id="forgot-form">
                <?= csrf_field() ?>
                <?= recaptcha_field() ?>

                <div class="mb-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" required class="form-input"
                           placeholder="seu@email.com" value="<?= old('email') ?>">
                </div>

                <button type="submit" class="btn-primary w-full mb-4">Enviar Link de Recuperação</button>

                <div class="text-center">
                    <a href="<?= base_url('login') ?>" class="text-primary-600 hover:text-primary-700 text-sm">
                        ← Voltar para Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- reCAPTCHA v3 -->
<?= recaptcha_script() ?>
<?= recaptcha_execute('forgot-form', 'forgot_password') ?>

<?= $this->endSection() ?>
