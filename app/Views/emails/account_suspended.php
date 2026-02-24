<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conta Suspensa</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #F59E0B, #D97706); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Conta Suspensa</h1>
    </div>

    <div style="background: #fff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p>Ola, <strong><?= esc($user['name']) ?></strong>,</p>

        <p>Informamos que sua conta na plataforma <strong>DoarFazBem</strong> foi <strong style="color: #F59E0B;">suspensa temporariamente</strong>.</p>

        <div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; font-weight: bold; color: #92400E;">Motivo da suspensao:</p>
            <p style="margin: 10px 0 0 0; color: #92400E;"><?= esc($reason) ?></p>
        </div>

        <p>Enquanto sua conta estiver suspensa, voce nao podera:</p>
        <ul style="color: #666;">
            <li>Fazer login na plataforma</li>
            <li>Criar ou editar campanhas</li>
            <li>Realizar doacoes</li>
            <li>Solicitar saques</li>
        </ul>

        <p>Se voce acredita que houve um erro ou deseja recorrer desta decisao, entre em contato conosco respondendo a este email.</p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

        <p style="color: #666; font-size: 12px; text-align: center;">
            Este email foi enviado automaticamente pela plataforma DoarFazBem.<br>
            Em caso de duvidas, entre em contato: contato@doarfazbem.com.br
        </p>
    </div>
</body>
</html>
