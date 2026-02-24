<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campanha Aprovada - DoarFazBem</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #ffffff;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .campaign-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10B981;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
        }
        .campaign-box h2 {
            color: #065f46;
            margin: 0 0 10px 0;
            font-size: 20px;
        }
        .campaign-box p {
            color: #047857;
            margin: 0;
            font-size: 14px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(16, 185, 129, 0.4);
        }
        .tips-section {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .tips-section h3 {
            color: #1e40af;
            margin: 0 0 15px 0;
            font-size: 18px;
        }
        .tips-section ul {
            margin: 0;
            padding-left: 20px;
            color: #1e3a8a;
        }
        .tips-section li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        .footer a {
            color: #10B981;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="success-icon">🎉</div>
            <h1>Campanha Aprovada!</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Olá, <strong><?= esc($creator_name) ?></strong>!</p>

            <p class="message">
                Temos ótimas notícias! Sua campanha foi analisada e <strong>aprovada</strong> por nossa equipe.
                Ela já está ativa e disponível para receber doações na plataforma DoarFazBem!
            </p>

            <!-- Campaign Info -->
            <div class="campaign-box">
                <h2>📢 <?= esc($campaign_title) ?></h2>
                <p>Sua campanha está pronta para fazer a diferença!</p>
            </div>

            <p class="message">
                Agora é hora de divulgar sua campanha e começar a receber o apoio necessário para alcançar sua meta.
                Quanto mais pessoas souberem sobre sua causa, maiores são as chances de sucesso!
            </p>

            <!-- Call to Action -->
            <div class="button-container">
                <a href="<?= base_url('campaigns/' . $campaign_slug) ?>" class="button">
                    Ver Minha Campanha
                </a>
            </div>

            <!-- Tips Section -->
            <div class="tips-section">
                <h3>💡 Dicas para aumentar suas doações:</h3>
                <ul>
                    <li><strong>Compartilhe nas redes sociais:</strong> Facebook, Instagram, WhatsApp e Twitter</li>
                    <li><strong>Poste atualizações frequentes:</strong> Mantenha seus apoiadores informados sobre o progresso</li>
                    <li><strong>Adicione fotos e vídeos:</strong> Conteúdo visual atrai mais atenção</li>
                    <li><strong>Agradeça seus doadores:</strong> Responda aos comentários e mostre gratidão</li>
                    <li><strong>Conte sua história:</strong> Seja transparente sobre por que precisa da ajuda</li>
                </ul>
            </div>

            <div class="divider"></div>

            <p class="message" style="font-size: 14px; color: #6b7280;">
                <strong>Próximos passos:</strong><br>
                • Acesse seu painel de controle para gerenciar sua campanha<br>
                • Configure métodos de recebimento (se ainda não fez)<br>
                • Comece a divulgar sua campanha para amigos e familiares<br>
                • Acompanhe as doações em tempo real no seu dashboard
            </p>

            <p class="message" style="margin-top: 30px;">
                Se tiver alguma dúvida ou precisar de ajuda, nossa equipe está à disposição!
            </p>

            <p class="message">
                Boa sorte com sua campanha e que você alcance sua meta rapidamente! 🚀
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>DoarFazBem</strong></p>
            <p>Transformando vidas através da solidariedade</p>
            <p style="margin-top: 15px;">
                <a href="<?= base_url() ?>">Visite nosso site</a> |
                <a href="<?= base_url('dashboard') ?>">Meu Dashboard</a> |
                <a href="mailto:contato@doarfazbem.com.br">Contato</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                © <?= date('Y') ?> DoarFazBem. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>
</html>
