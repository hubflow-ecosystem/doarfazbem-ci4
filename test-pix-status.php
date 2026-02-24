<?php
// Testar endpoint pix-status
$donationId = 244; // ID da doação de teste

$url = "https://doarfazbem.ai/donations/pix-status/{$donationId}";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== TESTE PIX STATUS ===\n\n";
echo "URL: $url\n";
echo "HTTP Code: $httpCode\n\n";
echo "Response:\n";
echo $response . "\n\n";

$data = json_decode($response, true);
if ($data) {
    echo "JSON Decodificado:\n";
    print_r($data);
} else {
    echo "Erro ao decodificar JSON\n";
}
