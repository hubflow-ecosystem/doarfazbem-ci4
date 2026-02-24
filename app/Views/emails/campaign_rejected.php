<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campanha Rejeitada</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f7;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td style="padding: 40px 0;">
                <table align="center" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #10B981 0%, #3B82F6 100%); padding: 30px 40px; border-radius: 8px 8px 0 0; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">DoarFazBem</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #DC2626; margin: 0 0 20px; font-size: 22px;">
                                Campanha Rejeitada
                            </h2>

                            <p style="color: #4B5563; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                Ola, <strong><?= esc($creator_name) ?></strong>!
                            </p>

                            <p style="color: #4B5563; font-size: 16px; line-height: 1.6; margin: 0 0 20px;">
                                Infelizmente, sua campanha <strong>"<?= esc($campaign_title) ?>"</strong> foi rejeitada pela nossa equipe de moderacao.
                            </p>

                            <!-- Reason Box -->
                            <div style="background-color: #FEF2F2; border-left: 4px solid #DC2626; padding: 20px; margin: 20px 0; border-radius: 0 8px 8px 0;">
                                <h3 style="color: #991B1B; margin: 0 0 10px; font-size: 16px;">
                                    Motivo da Rejeicao:
                                </h3>
                                <p style="color: #7F1D1D; font-size: 14px; line-height: 1.6; margin: 0;">
                                    <?= nl2br(esc($rejection_reason)) ?>
                                </p>
                            </div>

                            <p style="color: #4B5563; font-size: 16px; line-height: 1.6; margin: 20px 0;">
                                Voce pode corrigir os pontos mencionados e criar uma nova campanha. Se tiver duvidas sobre os motivos da rejeicao, entre em contato com nossa equipe de suporte.
                            </p>

                            <!-- Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 30px 0;">
                                <tr>
                                    <td style="border-radius: 6px; background-color: #10B981;">
                                        <a href="<?= base_url('campaigns/create') ?>" style="display: inline-block; padding: 14px 30px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 16px;">
                                            Criar Nova Campanha
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #6B7280; font-size: 14px; line-height: 1.6; margin: 20px 0 0;">
                                Atenciosamente,<br>
                                <strong>Equipe DoarFazBem</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #F3F4F6; padding: 20px 40px; border-radius: 0 0 8px 8px; text-align: center;">
                            <p style="color: #9CA3AF; font-size: 12px; margin: 0;">
                                Este email foi enviado automaticamente pelo DoarFazBem.
                            </p>
                            <p style="color: #9CA3AF; font-size: 12px; margin: 10px 0 0;">
                                Duvidas? Entre em contato: contato@doarfazbem.com.br
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
