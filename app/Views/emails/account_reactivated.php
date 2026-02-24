<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conta Reativada</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #10B981, #059669); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Conta Reativada!</h1>
    </div>

    <div style="background: #fff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p>Ola, <strong><?= esc($user['name']) ?></strong>,</p>

        <p>Temos boas noticias! Sua conta na plataforma <strong>DoarFazBem</strong> foi <strong style="color: #10B981;">reativada com sucesso</strong>.</p>

        <div style="background: #D1FAE5; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: #065F46;">
                Voce ja pode fazer login e utilizar todos os recursos da plataforma normalmente.
            </p>
        </div>

        <p>Agora voce pode:</p>
        <ul style="color: #666;">
            <li>Fazer login na plataforma</li>
            <li>Criar e gerenciar campanhas</li>
            <li>Realizar doacoes</li>
            <li>Solicitar saques</li>
        </ul>

        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= base_url('login') ?>"
               style="display: inline-block; background: #10B981; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">
                Acessar Minha Conta
            </a>
        </div>

        <p>Agradecemos sua compreensao e esperamos que tenha uma otima experiencia na plataforma.</p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

        <p style="color: #666; font-size: 12px; text-align: center;">
            Este email foi enviado automaticamente pela plataforma DoarFazBem.<br>
            Em caso de duvidas, entre em contato: contato@doarfazbem.com.br
        </p>
    </div>
</body>
</html>
