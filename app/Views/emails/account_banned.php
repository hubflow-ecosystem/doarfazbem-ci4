<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Conta Banida</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #EF4444, #DC2626); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">Conta Banida Permanentemente</h1>
    </div>

    <div style="background: #fff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 10px 10px;">
        <p>Ola, <strong><?= esc($user['name']) ?></strong>,</p>

        <p>Informamos que sua conta na plataforma <strong>DoarFazBem</strong> foi <strong style="color: #EF4444;">banida permanentemente</strong> devido a violacao de nossos termos de uso.</p>

        <div style="background: #FEE2E2; border-left: 4px solid #EF4444; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; font-weight: bold; color: #991B1B;">Motivo do banimento:</p>
            <p style="margin: 10px 0 0 0; color: #991B1B;"><?= esc($reason) ?></p>
        </div>

        <p>Como consequencia, voce nao podera mais:</p>
        <ul style="color: #666;">
            <li>Acessar sua conta</li>
            <li>Criar novas contas com o mesmo email/CPF</li>
            <li>Utilizar qualquer servico da plataforma</li>
        </ul>

        <p>Esta decisao foi tomada apos analise cuidadosa da situacao. Se voce acredita que houve um erro grave, voce pode entrar em contato conosco, porem a decisao de banimento e tipicamente definitiva.</p>

        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

        <p style="color: #666; font-size: 12px; text-align: center;">
            Este email foi enviado automaticamente pela plataforma DoarFazBem.<br>
            Em caso de duvidas, entre em contato: contato@doarfazbem.com.br
        </p>
    </div>
</body>
</html>
