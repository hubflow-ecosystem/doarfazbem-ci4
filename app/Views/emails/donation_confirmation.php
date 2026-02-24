<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Doação</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            border-left: 4px solid #667eea;
        }
        .amount {
            font-size: 32px;
            color: #667eea;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            background: #667eea;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Doação Registrada! ❤️</h1>
    </div>

    <div class="content">
        <p>Olá, <strong><?= esc($donation['donor_name']) ?></strong>!</p>

        <p>Recebemos sua intenção de doação para a campanha:</p>

        <div class="box">
            <h2 style="margin-top: 0;"><?= esc($campaign['title']) ?></h2>
            <p><strong>Valor da doação:</strong> <span class="amount">R$ <?= number_format($donation['amount'], 2, ',', '.') ?></span></p>
            <p><strong>Método de pagamento:</strong> <?= esc($payment_method) ?></p>
            <?php if ($donation['payment_method'] === 'pix'): ?>
                <p><strong>Status:</strong> Aguardando pagamento via PIX</p>
            <?php elseif ($donation['payment_method'] === 'boleto'): ?>
                <p><strong>Status:</strong> Aguardando pagamento do boleto</p>
            <?php else: ?>
                <p><strong>Status:</strong> Processando pagamento</p>
            <?php endif; ?>
        </div>

        <?php if ($donation['payment_method'] === 'pix' && !empty($donation['pix_copy_paste'])): ?>
            <div class="box" style="border-left-color: #10b981;">
                <h3>Pagar via PIX</h3>
                <p>Use o QR Code ou copie o código Pix Copia e Cola para realizar o pagamento:</p>
                <p style="text-align: center; margin: 20px 0;">
                    <?php if (!empty($donation['pix_qr_code'])): ?>
                        <img src="<?= $donation['pix_qr_code'] ?>" alt="QR Code PIX" style="max-width: 200px;">
                    <?php endif; ?>
                </p>
                <p style="background: #f0f0f0; padding: 15px; border-radius: 5px; word-break: break-all; font-family: monospace; font-size: 11px;">
                    <?= esc($donation['pix_copy_paste']) ?>
                </p>
                <p style="text-align: center;">
                    <a href="<?= base_url('donations/pix/' . $donation['id']) ?>" class="button">Ver Página de Pagamento</a>
                </p>
            </div>
        <?php elseif ($donation['payment_method'] === 'boleto' && !empty($donation['boleto_url'])): ?>
            <div class="box" style="border-left-color: #f59e0b;">
                <h3>Pagar Boleto</h3>
                <p>Clique no botão abaixo para visualizar e imprimir seu boleto:</p>
                <p style="text-align: center;">
                    <a href="<?= $donation['boleto_url'] ?>" target="_blank" class="button">Ver Boleto</a>
                </p>
                <p><small>O boleto também está disponível em: <a href="<?= base_url('donations/boleto/' . $donation['id']) ?>">Página de Pagamento</a></small></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($donation['message'])): ?>
            <div class="box">
                <h3>Sua mensagem:</h3>
                <p><em>"<?= esc($donation['message']) ?>"</em></p>
            </div>
        <?php endif; ?>

        <p>Assim que o pagamento for confirmado, você receberá um email de agradecimento e o criador da campanha será notificado sobre sua contribuição.</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="<?= base_url('campaigns/' . $campaign['id']) ?>" class="button">Ver Campanha</a>
        </p>

        <p>Obrigado por fazer a diferença! 💙</p>

        <p>Equipe DoarFazBem</p>
    </div>

    <div class="footer">
        <p>DoarFazBem - Conectando corações, transformando vidas</p>
        <p><?= base_url() ?></p>
    </div>
</body>
</html>
