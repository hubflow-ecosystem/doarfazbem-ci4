<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="bg-gradient-to-br from-primary-50 to-secondary-50 py-16 min-h-screen flex items-center">
    <div class="container-custom max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <!-- Ícone de Sucesso -->
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6 animate-fade-in">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-heading-1 text-gray-900 mb-3">Doação Realizada com Sucesso!</h1>
            <p class="text-xl text-gray-600 mb-8">Obrigado por fazer a diferença! 💚</p>

            <!-- Detalhes da Doação -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
                <h3 class="font-semibold text-lg mb-4 text-center">Resumo da Doação</h3>

                <div class="space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Campanha:</span>
                        <span class="font-semibold text-right max-w-xs truncate"><?= esc($campaign['title']) ?></span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Valor:</span>
                        <span class="font-semibold text-primary-600 text-xl">
                            R$ <?= number_format($donation['amount'], 2, ',', '.') ?>
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Método:</span>
                        <span class="font-semibold">
                            <?php
                            $methods = [
                                'pix' => 'PIX',
                                'credit_card' => 'Cartão de Crédito',
                                'boleto' => 'Boleto'
                            ];
                            echo $methods[$donation['payment_method']] ?? 'N/A';
                            ?>
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Data:</span>
                        <span class="font-semibold"><?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?></span>
                    </div>
                </div>
            </div>

            <!-- Mensagem Motivacional -->
            <div class="bg-primary-50 border-l-4 border-primary-500 p-6 mb-8 text-left">
                <p class="text-gray-700 leading-relaxed">
                    <strong class="text-primary-700">Sua generosidade faz a diferença!</strong><br>
                    Você acabou de ajudar alguém a realizar um sonho. Sua doação foi registrada e o criador da campanha
                    será notificado. Obrigado por fazer parte desta corrente do bem! 🙏
                </p>
            </div>

            <!-- Compartilhar -->
            <div class="mb-8">
                <p class="text-gray-600 mb-4">Ajude a divulgar esta campanha:</p>
                <div class="flex justify-center gap-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= base_url('campaigns/' . $campaign['slug']) ?>" target="_blank"
                       class="w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= base_url('campaigns/' . $campaign['slug']) ?>&text=<?= urlencode('Acabei de doar para: ' . $campaign['title']) ?>" target="_blank"
                       class="w-12 h-12 bg-sky-500 text-white rounded-full flex items-center justify-center hover:bg-sky-600">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode('Acabei de doar para: ' . $campaign['title'] . ' - ' . base_url('campaigns/' . $campaign['slug'])) ?>" target="_blank"
                       class="w-12 h-12 bg-green-600 text-white rounded-full flex items-center justify-center hover:bg-green-700">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="<?= base_url('campaigns/' . $campaign['slug']) ?>" class="btn-outline flex-1">
                    Ver Campanha
                </a>
                <a href="<?= base_url('campaigns') ?>" class="btn-primary flex-1">
                    Explorar Mais Campanhas
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
