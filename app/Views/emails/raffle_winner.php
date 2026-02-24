<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parabens! Voce Ganhou!</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f7;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td style="padding: 40px 0;">
                <table align="center" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #F59E0B 0%, #10B981 100%); padding: 40px; border-radius: 8px 8px 0 0; text-align: center;">
                            <div style="font-size: 60px; margin-bottom: 10px;">🎉</div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 32px;">PARABENS!</h1>
                            <p style="color: #ffffff; font-size: 18px; margin: 10px 0 0;">Voce foi o grande ganhador!</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #4B5563; font-size: 18px; line-height: 1.6; margin: 0 0 20px;">
                                Ola, <strong><?= esc($winner_name) ?></strong>!
                            </p>

                            <p style="color: #4B5563; font-size: 16px; line-height: 1.6; margin: 0 0 30px;">
                                Temos uma otima noticia! Voce foi sorteado como o <strong>GRANDE GANHADOR</strong> da rifa <strong>"<?= esc($raffle_title) ?>"</strong>!
                            </p>

                            <!-- Prize Box -->
                            <div style="background: linear-gradient(135deg, #10B981 0%, #059669 100%); padding: 30px; border-radius: 12px; text-align: center; margin: 20px 0;">
                                <p style="color: #ffffff; font-size: 14px; margin: 0 0 5px; text-transform: uppercase; letter-spacing: 2px;">Numero Sorteado</p>
                                <p style="color: #ffffff; font-size: 48px; font-weight: bold; margin: 0 0 20px; font-family: 'Courier New', monospace;"><?= esc($winning_number) ?></p>

                                <p style="color: #ffffff; font-size: 14px; margin: 0 0 5px; text-transform: uppercase; letter-spacing: 2px;">Seu Premio</p>
                                <p style="color: #ffffff; font-size: 36px; font-weight: bold; margin: 0;">R$ <?= number_format($prize_amount, 2, ',', '.') ?></p>
                            </div>

                            <h3 style="color: #1F2937; margin: 30px 0 15px; font-size: 18px;">Proximos Passos:</h3>

                            <ol style="color: #4B5563; font-size: 14px; line-height: 1.8; padding-left: 20px;">
                                <li>Nossa equipe entrara em contato em ate 48 horas uteis</li>
                                <li>Tenha em maos seu documento de identificacao (CPF)</li>
                                <li>Informe seus dados bancarios para receber o premio via PIX</li>
                                <li>O premio sera depositado em ate 5 dias uteis apos a confirmacao</li>
                            </ol>

                            <div style="background-color: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                                <p style="color: #92400E; font-size: 14px; margin: 0;">
                                    <strong>Importante:</strong> Guarde este email como comprovante. Seu numero sorteado foi o <strong><?= esc($winning_number) ?></strong>.
                                </p>
                            </div>

                            <p style="color: #6B7280; font-size: 14px; line-height: 1.6; margin: 30px 0 0;">
                                Obrigado por participar e ajudar causas incriveis!<br>
                                <strong>Equipe DoarFazBem</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F3F4F6; padding: 20px 40px; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="color: #9CA3AF; font-size: 12px; margin: 0;">
                                DoarFazBem - Transformando vidas atraves da solidariedade
                            </p>
                            <p style="color: #9CA3AF; font-size: 12px; margin: 10px 0 0;">
                                Duvidas? contato@doarfazbem.com.br
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
