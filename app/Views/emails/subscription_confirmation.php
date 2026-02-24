<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura Ativada</title>
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
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
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
            border-left: 4px solid #8b5cf6;
        }
        .amount {
            font-size: 32px;
            color: #8b5cf6;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            background: #8b5cf6;
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
        .icon {
            font-size: 60px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="icon">🔄</div>
        <h1>Assinatura Ativada!</h1>
    </div>

    <div class="content">
        <p>Olá, <strong><?= esc($subscription['donor_name']) ?></strong>!</p>

        <p>Sua <strong>doação recorrente</strong> foi ativada com sucesso! 🎉</p>

        <div class="box">
            <h2 style="margin-top: 0;"><?= esc($campaign['title']) ?></h2>
            <p><strong>Valor mensal:</strong> <span class="amount">R$ <?= number_format($subscription['amount'], 2, ',', '.') ?></span></p>
            <p><strong>Frequência:</strong> <?= esc($cycle_label) ?></p>
            <p><strong>Próxima cobrança:</strong> <?= date('d/m/Y', strtotime($subscription['next_due_date'])) ?></p>
            <p><strong>Status:</strong> <span style="color: #10b981; font-weight: bold;">✓ Ativa</span></p>
        </div>

        <div class="box" style="background: #f5f3ff; border-color: #8b5cf6;">
            <h3 style="color: #6d28d9; margin-top: 0;">🌟 Obrigado por ser um apoiador recorrente!</h3>
            <p>Doações recorrentes são a espinha dorsal de campanhas sustentáveis. Sua contribuição mensal garante que o criador da campanha possa planejar e executar ações de longo prazo com mais segurança.</p>
        </div>

        <div class="box">
            <h3>Como funciona?</h3>
            <ul style="padding-left: 20px;">
                <li>Sua doação será <strong>renovada automaticamente</strong> a cada período</li>
                <li>Você receberá um <strong>email de confirmação</strong> antes de cada cobrança</li>
                <li>O valor será <strong>debitado na mesma forma de pagamento</strong> cadastrada</li>
                <li>Você pode <strong>cancelar a qualquer momento</strong> no seu painel</li>
            </ul>
        </div>

        <div class="box" style="background: #fef3c7; border-color: #f59e0b;">
            <h3 style="color: #d97706; margin-top: 0;">ℹ️ Informações Importantes</h3>
            <p><strong>Primeira cobrança:</strong> <?= date('d/m/Y', strtotime($subscription['next_due_date'])) ?></p>
            <p><strong>Gerenciar assinatura:</strong> Acesse seu painel para visualizar, pausar ou cancelar sua assinatura a qualquer momento.</p>
        </div>

        <p style="text-align: center; margin: 30px 0;">
            <a href="<?= base_url('dashboard/my-donations') ?>" class="button">Gerenciar Minhas Assinaturas</a>
            <a href="<?= base_url('campaigns/' . $campaign['id']) ?>" class="button" style="background: #667eea;">Ver Campanha</a>
        </p>

        <div class="box" style="background: #ecfdf5; border-color: #10b981;">
            <h3 style="color: #059669; margin-top: 0;">❤️ Seu Impacto Contínuo</h3>
            <p>Com sua assinatura ativa, você está proporcionando um apoio constante e previsível. Isso faz uma diferença enorme!</p>
            <p><strong>Total que você contribuirá em 1 ano:</strong> R$ <?= number_format($subscription['amount'] * 12, 2, ',', '.') ?></p>
        </div>

        <p><strong>Obrigado por ser parte desta transformação!</strong></p>

        <p>Sua generosidade recorrente nos inspira todos os dias. Continue acompanhando o progresso da campanha e saiba que você é fundamental para o sucesso desta causa.</p>

        <p>Com gratidão,<br>
        <strong>Equipe DoarFazBem</strong></p>
    </div>

    <div class="footer">
        <p>DoarFazBem - Conectando corações, transformando vidas</p>
        <p><?= base_url() ?></p>
        <p>Você está recebendo este email porque criou uma assinatura de doação recorrente.</p>
        <p>Para gerenciar ou cancelar sua assinatura, acesse: <a href="<?= base_url('dashboard/my-donations') ?>">Meu Painel</a></p>
    </div>
</body>
</html>
