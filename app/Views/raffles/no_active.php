<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<div class="min-h-screen bg-gradient-to-br from-purple-900 via-indigo-900 to-blue-900 py-20">
    <div class="container-custom text-center">
        <div class="max-w-xl mx-auto">
            <div class="w-24 h-24 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-8">
                <i class="fas fa-ticket-alt text-teal-300 text-5xl"></i>
            </div>

            <h1 class="text-4xl font-bold text-white mb-4">
                Em Breve!
            </h1>

            <p class="text-xl text-teal-200 mb-8">
                Nenhuma rifa ativa no momento. Fique ligado para a proxima edicao dos Numeros da Sorte!
            </p>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20">
                <h3 class="text-lg font-semibold text-white mb-4">
                    Quer ser avisado quando comecar?
                </h3>
                <form class="flex gap-3">
                    <input type="email" placeholder="Seu melhor e-mail"
                           class="flex-1 form-input bg-white/20 border-white/30 text-white placeholder-purple-300">
                    <button type="submit"
                            class="px-6 py-3 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition">
                        Avisar-me
                    </button>
                </form>
            </div>

            <a href="<?= base_url('campaigns') ?>"
               class="inline-flex items-center mt-8 text-teal-300 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Enquanto isso, conheca nossas campanhas
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
