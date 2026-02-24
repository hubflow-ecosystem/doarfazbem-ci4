<?php
echo "=== VERIFICAÇÃO DE CORREÇÕES DE TAXAS ===\n\n";

$files = [
    'app/Views/layout/app.php' => '2%',
    'app/Views/home/index.php' => '2%',
    'app/Views/pages/como_funciona.php' => '2%',
    'app/Views/pages/sobre.php' => '2%',
    'app/Views/pages/termos.php' => '2%',
    'app/Views/campaigns/create.php' => '2%',
    'app/Views/donations/success.php' => 'médicas e sociais'
];

$errors = 0;
foreach ($files as $file => $expected) {
    $fullPath = 'c:/laragon/www/doarfazbem/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (strpos($content, $expected) !== false) {
            echo "✅ $file - OK\n";
        } else {
            echo "❌ $file - NÃO encontrou '$expected'\n";
            $errors++;
        }
    } else {
        echo "⚠️  $file - Arquivo não existe\n";
    }
}

echo "\n=== RESUMO ===\n";
if ($errors === 0) {
    echo "✅ Todas as correções foram aplicadas com sucesso!\n";
} else {
    echo "❌ $errors arquivo(s) com problemas\n";
}
