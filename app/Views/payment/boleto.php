<?= $this->extend('layout/app') ?>
<?= $this->section('content') ?>
<div class="bg-gray-50 py-12 min-h-screen">
    <div class="container-custom max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <h1 class="text-heading-1 mb-4">Boleto Gerado</h1>
            <div class="text-4xl font-bold text-primary-600 mb-6">
                R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
            </div>
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 text-left">
                <p class="text-sm text-yellow-700">
                    <strong>Atenção:</strong> O boleto vence em 3 dias. Após o pagamento, a confirmação pode levar até 2 dias úteis.
                </p>
            </div>
            <?php if ($donation['boleto_url']): ?>
                <a href="<?= $donation['boleto_url'] ?>" target="_blank" class="btn-primary w-full mb-4">
                    📄 Visualizar e Imprimir Boleto
                </a>
            <?php endif; ?>
            <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>" class="btn-outline w-full">
                Voltar para Campanha
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
