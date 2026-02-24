<?php
/**
 * Callback para OAuth do Google Drive
 * Captura o código de autorização e exibe na tela
 */

$code = $_GET['code'] ?? null;
$error = $_GET['error'] ?? null;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DoarFazBem - Autorização Google Drive</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .code-box {
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
        }
        .command {
            background: #1f2937;
            color: #10b981;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 13px;
            text-align: left;
            overflow-x: auto;
        }
        .copy-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        .copy-btn:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($error): ?>
            <h1 class="error">Erro na Autorização</h1>
            <p>O Google retornou um erro:</p>
            <div class="code-box"><?= htmlspecialchars($error) ?></div>
            <p>Tente novamente ou verifique as configurações do OAuth.</p>

        <?php elseif ($code): ?>
            <h1 class="success">Autorização Concedida!</h1>
            <p>Copie o código abaixo e execute no terminal:</p>

            <div class="code-box" id="code"><?= htmlspecialchars($code) ?></div>

            <button class="copy-btn" onclick="copyCode()">Copiar Código</button>

            <div class="command">
                php spark backup:auth <?= htmlspecialchars($code) ?>
            </div>

            <p style="color: #6b7280; font-size: 14px;">
                Após executar o comando, o Google Drive estará configurado para backups.
            </p>

            <script>
                function copyCode() {
                    const code = document.getElementById('code').textContent;
                    navigator.clipboard.writeText(code).then(() => {
                        alert('Código copiado!');
                    });
                }
            </script>

        <?php else: ?>
            <h1>Aguardando Autorização</h1>
            <p>Esta página receberá o código de autorização do Google.</p>
            <p>Se você chegou aqui diretamente, execute primeiro:</p>
            <div class="command">php spark backup:auth</div>
        <?php endif; ?>
    </div>
</body>
</html>
