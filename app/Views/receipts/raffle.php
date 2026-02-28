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
            background: #8B5CF6;
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
            color: #8B5CF6;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #8B5CF6;
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
            color: #8B5CF6;
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

        .receipt-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .receipt-title h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 5px;
        }

        .badge {
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
            background: linear-gradient(135deg, #8B5CF6, #7C3AED);
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

        .numbers-box {
            background: #faf5ff;
            border: 1px solid #e9d5ff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .numbers-box h4 {
            color: #7c3aed;
            margin-bottom: 15px;
            font-size: 14px;
            text-transform: uppercase;
        }

        .numbers-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .number-badge {
            background: linear-gradient(135deg, #8B5CF6, #7C3AED);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            font-weight: 600;
        }

        .raffle-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .raffle-info h4 {
            color: #065f46;
            margin-bottom: 5px;
        }

        .raffle-info p {
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

            .amount-box, .number-badge {
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
                    <h1>Números da Sorte</h1>
                    <p>DoarFazBem - Crowdfunding Solidário</p>
                </div>
            </div>
            <div class="receipt-info">
                <div class="receipt-number"><strong><?= esc($receiptNumber) ?></strong></div>
                <div class="receipt-date" style="font-size: 12px; color: #999;">
                    Emitido em: <?= date('d/m/Y H:i', strtotime($purchase['created_at'])) ?>
                </div>
            </div>
        </div>

        <div class="receipt-title">
            <h2>Comprovante de Compra de Cotas</h2>
            <?php
            $statusClass = 'badge-pending';
            $statusText = 'Pendente';
            if ($purchase['payment_status'] === 'paid' || $purchase['payment_status'] === 'confirmed') {
                $statusClass = 'badge-success';
                $statusText = 'Confirmada';
            }
            ?>
            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
        </div>

        <div class="amount-box">
            <label>Valor Total</label>
            <div class="value">R$ <?= number_format($purchase['total_amount'], 2, ',', '.') ?></div>
            <div style="font-size: 14px; opacity: 0.8; margin-top: 5px;">
                <?= $purchase['quantity'] ?> cota(s) × R$ <?= number_format($purchase['unit_price'], 2, ',', '.') ?>
            </div>
        </div>

        <?php if ($raffle): ?>
        <div class="raffle-info">
            <h4><?= esc($raffle['title']) ?></h4>
            <p>Data do sorteio: <?= !empty($raffle['federal_lottery_date']) ? date('d/m/Y', strtotime($raffle['federal_lottery_date'])) : 'A definir' ?></p>
        </div>
        <?php endif; ?>

        <div class="numbers-box">
            <h4>Seus Números da Sorte (<?= count($numbers) ?>)</h4>
            <div class="numbers-grid">
                <?php foreach ($numbers as $number): ?>
                    <span class="number-badge"><?= esc($number['number']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Dados do Comprador</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Nome</label>
                    <span><?= esc($purchase['buyer_name']) ?></span>
                </div>
                <div class="info-item">
                    <label>E-mail</label>
                    <span><?= esc($purchase['buyer_email']) ?></span>
                </div>
                <?php if (!empty($purchase['buyer_phone'])): ?>
                <div class="info-item">
                    <label>Telefone</label>
                    <span><?= esc($purchase['buyer_phone']) ?></span>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <label>CPF</label>
                    <span><?= substr($purchase['buyer_cpf'], 0, 3) . '.***.***-' . substr($purchase['buyer_cpf'], -2) ?></span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Dados do Pagamento</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Método de Pagamento</label>
                    <span>PIX</span>
                </div>
                <div class="info-item">
                    <label>Status</label>
                    <span><?= $statusText ?></span>
                </div>
                <?php if (!empty($purchase['payment_id'])): ?>
                <div class="info-item">
                    <label>ID da Transação</label>
                    <span><?= esc($purchase['payment_id']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($purchase['paid_at'])): ?>
                <div class="info-item">
                    <label>Data de Confirmação</label>
                    <span><?= date('d/m/Y \à\s H:i', strtotime($purchase['paid_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($purchase['discount_applied']) && $purchase['discount_applied'] > 0): ?>
        <div class="section">
            <div class="section-title">Desconto Aplicado</div>
            <p style="padding: 15px; background: #fef3c7; border-radius: 6px; color: #92400e;">
                Você economizou <strong>R$ <?= number_format($purchase['discount_applied'], 2, ',', '.') ?></strong> nesta compra!
            </p>
        </div>
        <?php endif; ?>

        <div class="footer">
            <p><strong>Este documento é um comprovante de participação no sorteio Números da Sorte.</strong></p>
            <p>Guarde seus números! O sorteio será realizado na data indicada acima.</p>
            <p>Dúvidas? Entre em contato: contato@doarfazbem.com.br</p>
        </div>
    </div>
</body>
</html>
