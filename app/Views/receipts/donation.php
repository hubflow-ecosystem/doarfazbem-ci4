<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .no-print {
            background: #10B981;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .no-print button {
            background: white;
            color: #10B981;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .no-print button:hover {
            background: #f0fdf4;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #10B981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
        }
        .logo-icon img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .logo-text h1 {
            font-size: 24px;
            color: #10B981;
        }

        .logo-text p {
            font-size: 12px;
            color: #666;
        }

        .receipt-info {
            text-align: right;
        }

        .receipt-number {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .receipt-date {
            font-size: 12px;
            color: #999;
        }

        .receipt-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .receipt-title h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 5px;
        }

        .receipt-title .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            padding: 10px;
            background: #f9fafb;
            border-radius: 6px;
        }

        .info-item label {
            display: block;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .info-item span {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .amount-box {
            background: linear-gradient(135deg, #10B981, #059669);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }

        .amount-box label {
            display: block;
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .amount-box .value {
            font-size: 36px;
            font-weight: 700;
        }

        .campaign-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .campaign-box h4 {
            color: #065f46;
            margin-bottom: 5px;
        }

        .campaign-box p {
            color: #666;
            font-size: 13px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px dashed #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .footer p {
            margin-bottom: 5px;
        }

        .qr-placeholder {
            width: 100px;
            height: 100px;
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #9ca3af;
        }

        .signature-line {
            margin-top: 40px;
            text-align: center;
        }

        .signature-line hr {
            width: 200px;
            margin: 0 auto 10px;
            border: none;
            border-top: 1px solid #333;
        }

        .signature-line p {
            font-size: 12px;
            color: #666;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                padding: 20px;
            }

            .no-print {
                display: none !important;
            }

            .amount-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <span>Clique no botão para imprimir ou salvar como PDF</span>
        <button onclick="window.print()">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd"/>
            </svg>
            Imprimir / Salvar PDF
        </button>
    </div>

    <div class="receipt-container">
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
                    <img src="<?= base_url('assets/images/Logo-favicon-doarfazbem.png') ?>" alt="DoarFazBem">
                </div>
                <div class="logo-text">
                    <h1>DoarFazBem</h1>
                    <p>Plataforma de Crowdfunding Solidário</p>
                </div>
            </div>
            <div class="receipt-info">
                <div class="receipt-number"><strong><?= esc($receiptNumber) ?></strong></div>
                <div class="receipt-date">
                    Emitido em: <?= date('d/m/Y H:i', strtotime($donation['created_at'])) ?>
                </div>
            </div>
        </div>

        <div class="receipt-title">
            <h2>Comprovante de Doação</h2>
            <?php
            $statusClass = 'badge-pending';
            $statusText = 'Pendente';
            if ($donation['status'] === 'received' || $donation['status'] === 'paid') {
                $statusClass = 'badge-success';
                $statusText = 'Confirmada';
            } elseif ($donation['status'] === 'failed' || $donation['status'] === 'cancelled') {
                $statusClass = 'badge-failed';
                $statusText = 'Cancelada';
            }
            ?>
            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
        </div>

        <div class="amount-box">
            <label>Valor da Doação</label>
            <div class="value">R$ <?= number_format($donation['amount'], 2, ',', '.') ?></div>
        </div>

        <?php if ($campaign): ?>
        <div class="campaign-box">
            <h4><?= esc($campaign['title']) ?></h4>
            <p>Campanha criada por: <?= esc($creator['name'] ?? 'Não informado') ?></p>
        </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-title">Dados do Doador</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Nome</label>
                    <span><?= $donation['is_anonymous'] ? 'Doação Anônima' : esc($donation['donor_name']) ?></span>
                </div>
                <div class="info-item">
                    <label>E-mail</label>
                    <span><?= $donation['is_anonymous'] ? '***@***.com' : esc($donation['donor_email']) ?></span>
                </div>
                <?php if (!empty($donation['donor_cpf']) && !$donation['is_anonymous']): ?>
                <div class="info-item">
                    <label>CPF</label>
                    <span><?= substr($donation['donor_cpf'], 0, 3) . '.***.***-' . substr($donation['donor_cpf'], -2) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <label>Data da Doação</label>
                    <span><?= date('d/m/Y \à\s H:i', strtotime($donation['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Dados do Pagamento</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Método de Pagamento</label>
                    <span>
                        <?php
                        $methods = [
                            'pix' => 'PIX',
                            'credit_card' => 'Cartão de Crédito',
                            'boleto' => 'Boleto Bancário',
                        ];
                        echo $methods[$donation['payment_method']] ?? ucfirst($donation['payment_method']);
                        ?>
                    </span>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <span><?= $statusText ?></span>
                </div>
                <?php if (!empty($donation['asaas_payment_id'])): ?>
                <div class="info-item">
                    <label>ID da Transação</label>
                    <span><?= esc($donation['asaas_payment_id']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($donation['paid_at'])): ?>
                <div class="info-item">
                    <label>Data de Confirmação</label>
                    <span><?= date('d/m/Y \à\s H:i', strtotime($donation['paid_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($donation['message'])): ?>
        <div class="section">
            <div class="section-title">Mensagem do Doador</div>
            <p style="padding: 15px; background: #f9fafb; border-radius: 6px; font-style: italic; color: #555;">
                "<?= esc($donation['message']) ?>"
            </p>
        </div>
        <?php endif; ?>

        <div class="signature-line">
            <hr>
            <p>DoarFazBem - CNPJ: XX.XXX.XXX/0001-XX</p>
        </div>

        <div class="footer">
            <p><strong>Este documento é um comprovante de doação realizada através da plataforma DoarFazBem.</strong></p>
            <p>Para verificar a autenticidade, acesse: <?= base_url('verify/' . $receiptNumber) ?></p>
            <p>Dúvidas? Entre em contato: contato@doarfazbem.com.br</p>
        </div>
    </div>
</body>
</html>
