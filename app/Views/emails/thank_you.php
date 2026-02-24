<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado pela sua Doação!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #10b981;
        }
        .amount {
            font-size: 32px;
            color: #10b981;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 12px;
        }
        .success-icon {
            font-size: 60px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="success-icon">✅</div>
        <h1>Pagamento Confirmado!</h1>
    </div>

    <div class="content">
        <p>Olá, <strong><?= esc($donation['donor_name']) ?></strong>!</p>

        <p>Sua doação foi <strong>confirmada com sucesso</strong>! 🎉</p>

        <div class="box">
            <h2 style="margin-top: 0;"><?= esc($campaign['title']) ?></h2>
            <p><strong>Valor doado:</strong> <span class="amount">R$ <?= number_format($donation['amount'], 2, ',', '.') ?></span></p>
            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?></p>
            <p><strong>Comprovante:</strong> #<?= $donation['id'] ?></p>
        </div>

        <p>Graças a você e outras pessoas generosas, esta campanha está cada vez mais perto de alcançar sua meta. Sua contribuição fará uma diferença real na vida de quem precisa.</p>

        <div class="box" style="background: #ecfdf5; border-color: #10b981;">
            <h3 style="color: #059669; margin-top: 0;">❤️ Impacto da sua doação</h3>
            <p>Você é parte de uma comunidade que está transformando vidas. O criador da campanha foi notificado sobre sua doação e em breve poderá utilizar os recursos para o fim proposto.</p>
        </div>

        <?php if (!empty($donation['message'])): ?>
            <div class="box">
                <h3>Sua mensagem de apoio:</h3>
                <p><em>"<?= esc($donation['message']) ?>"</em></p>
            </div>
        <?php endif; ?>

        <p style="text-align: center; margin: 30px 0;">
            <a href="<?= base_url('campaigns/' . $campaign['id']) ?>" class="button">Ver Campanha</a>
            <a href="<?= base_url('dashboard/my-donations') ?>" class="button" style="background: #667eea;">Minhas Doações</a>
        </p>

        <div class="box" style="background: #fef3c7; border-color: #f59e0b;">
            <h3 style="color: #d97706; margin-top: 0;">📢 Compartilhe esta causa!</h3>
            <p>Ajude a ampliar o impacto desta campanha compartilhando com seus amigos e familiares. Juntos podemos fazer ainda mais!</p>
            <p style="text-align: center;">
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= base_url('campaigns/' . $campaign['id']) ?>" style="color: #1877f2; text-decoration: none; margin: 0 10px; font-size: 24px;">📘</a>
                <a href="https://twitter.com/intent/tweet?url=<?= base_url('campaigns/' . $campaign['id']) ?>&text=<?= urlencode($campaign['title']) ?>" style="color: #1da1f2; text-decoration: none; margin: 0 10px; font-size: 24px;">🐦</a>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($campaign['title'] . ' - ' . base_url('campaigns/' . $campaign['id'])) ?>" style="color: #25d366; text-decoration: none; margin: 0 10px; font-size: 24px;">💬</a>
            </p>
        </div>

        <p><strong>Obrigado por fazer a diferença!</strong></p>

        <p>Sua generosidade inspira outros e transforma vidas. Continue acompanhando o progresso da campanha e saiba que você é parte essencial dessa história de sucesso.</p>

        <p>Com gratidão,<br>
        <strong>Equipe DoarFazBem</strong></p>
    </div>

    <div class="footer">
        <p>DoarFazBem - Conectando corações, transformando vidas</p>
        <p><?= base_url() ?></p>
        <p>Você está recebendo este email porque realizou uma doação através da nossa plataforma.</p>
    </div>
</body>
</html>
