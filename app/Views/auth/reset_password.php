<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="bg-gradient-to-br from-primary-50 to-secondary-50 min-h-screen flex items-center py-12">
    <div class="container-custom max-w-md">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-heading-1 text-center mb-2">Redefinir Senha</h1>
            <p class="text-gray-600 text-center mb-8">Digite sua nova senha</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-error mb-6"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="<?= base_url('reset-password') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= $token ?>">

                <div class="mb-4">
                    <label for="password" class="form-label">Nova Senha</label>
                    <input type="password" id="password" name="password" required class="form-input"
                           placeholder="Mínimo 8 caracteres">
                </div>

                <div class="mb-6">
                    <label for="password_confirm" class="form-label">Confirmar Senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" required class="form-input">
                </div>

                <button type="submit" class="btn-primary w-full">Redefinir Senha</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
