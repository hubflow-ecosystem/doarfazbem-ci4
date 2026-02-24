# Refatoração AsaasService - Implementação Medlife

## Resumo das Mudanças

Todo o código relacionado ao Asaas foi refatorado para seguir o padrão testado e funcionando do projeto Medlife.

## Principais Alterações

### 1. AsaasService.php - SUBSTITUÍDO COMPLETAMENTE

**Origem:** `C:/laragon/www/medlife/app/Libraries/Asaas/AsaasService.php`
**Destino:** `c:\laragon\www\doarfazbem\app\Libraries\AsaasService.php`

#### Mudanças no Padrão de Resposta:

**ANTES (DoarFazBem):**
```php
return [
    'success' => true,
    'data' => $responseData
];
```

**DEPOIS (Medlife):**
```php
// Retorna dados diretamente da API
return $responseData;

// Erros lançam exceções
if ($httpCode >= 400) {
    throw new \Exception("Erro Asaas: {$errorMessage}");
}
```

#### Melhorias Adicionais:
- Adicionado suporte a `function_exists('log_message')` para scripts standalone
- Método `createOrUpdateCustomer()` implementado
- Método `getCustomerByCpfCnpj()` implementado
- Suporte a split payments
- Melhor tratamento de erros

### 2. Donation.php - Refatorado para Try/Catch

#### Mudança 1: Criação de Customer (linha ~171)

**ANTES:**
```php
$customerResult = $this->asaasLib->createOrUpdateCustomer($customerData);
if (!$customerResult['success']) {
    return redirect()->back()->with('error', '...');
}
$customerId = $customerResult['data']['id'];
```

**DEPOIS:**
```php
try {
    $customerResult = $this->asaasService->createOrUpdateCustomer($customerData);
    $customerId = $customerResult['id'];
} catch (\Exception $e) {
    log_message('error', 'Erro ao criar customer: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
}
```

#### Mudança 2: Criação de Pagamentos PIX/Boleto/Cartão (linha ~211)

**ANTES:**
```php
$paymentResult = $this->asaasLib->createPixPayment($paymentData);
if (!$paymentResult['success']) {
    return redirect()->back()->with('error', '...');
}
$asaasPaymentId = $paymentResult['data']['id'];
$pixData = $this->asaasService->getPixQrCode($asaasPaymentId);
$pixQrCode = $pixData['data']['encodedImage'];
```

**DEPOIS:**
```php
try {
    if ($paymentMethod === 'pix') {
        $paymentResult = $this->asaasService->createPixPayment($paymentData);
        $asaasPaymentId = $paymentResult['id'];

        $pixData = $this->asaasService->getPixQrCode($asaasPaymentId);
        if ($pixData && isset($pixData['encodedImage'])) {
            $pixQrCode = $pixData['encodedImage'];
            $pixCopyPaste = $pixData['payload'] ?? null;
        }
    } elseif ($paymentMethod === 'boleto') {
        $paymentResult = $this->asaasService->createBoletoPayment($paymentData);
        $asaasPaymentId = $paymentResult['id'];
        $boletoUrl = $paymentResult['bankSlipUrl'] ?? null;
        $boletoBarcode = $paymentResult['identificationField'] ?? null;
    }
} catch (\Exception $e) {
    log_message('error', 'Erro Asaas: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
}
```

#### Mudança 3: Processamento de Cartão (linha ~492)

**ANTES:**
```php
$result = $this->asaasService->payWithCreditCard($paymentData);
if (!$result['success']) {
    return redirect()->back()->with('error', '...');
}
```

**DEPOIS:**
```php
try {
    $result = $this->asaasService->payWithCreditCard($paymentData);
    $this->donationModel->update($donationId, ['status' => 'confirmed']);
} catch (\Exception $e) {
    log_message('error', 'Erro ao processar cartão: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
}
```

#### Mudança 4: Status PIX (linha ~556)

**ANTES:**
```php
$paymentData = $this->asaasService->getPayment($transaction['asaas_payment_id']);
if ($paymentData && isset($paymentData['status'])) {
    $status = $paymentData['status'];
    // ...
}
```

**DEPOIS:**
```php
try {
    $paymentData = $this->asaasService->getPayment($transaction['asaas_payment_id']);
    $status = $paymentData['status'] ?? null;

    if (!$status) {
        return $this->response->setJSON(['success' => false, 'message' => 'Status não disponível']);
    }
    // ...
} catch (\Exception $e) {
    log_message('error', 'Erro ao consultar status PIX: ' . $e->getMessage());
    return $this->response->setJSON(['success' => false, 'message' => 'Erro ao consultar status']);
}
```

#### Mudança 5: Criação de Subscription (linha ~614, ~661)

**ANTES:**
```php
$customerResult = $this->asaasService->createOrUpdateCustomer($customerData);
if (!$customerResult['success']) {
    return redirect()->back()->with('error', '...');
}
$customerId = $customerResult['data']['id'];

$result = $this->asaasService->createSubscription($subscriptionData);
if (!$result['success']) {
    return redirect()->back()->with('error', '...');
}
$subscriptionId = $result['data']['id'];
```

**DEPOIS:**
```php
try {
    $customerResult = $this->asaasService->createOrUpdateCustomer($customerData);
    $customerId = $customerResult['id'];
} catch (\Exception $e) {
    log_message('error', 'Erro ao criar customer: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
}

try {
    $result = $this->asaasService->createSubscription($subscriptionData);
    $subscriptionId = $result['id'];
} catch (\Exception $e) {
    log_message('error', 'Erro ao criar assinatura: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Erro: ' . $e->getMessage());
}
```

### 3. Correções de Nomenclatura

**ANTES:** `$this->asaasLib`
**DEPOIS:** `$this->asaasService`

Total de 4 ocorrências corrigidas usando replace_all.

### 4. Dados Salvos no Banco

Adicionados campos essenciais para PIX e Boleto:

```php
$donationData = [
    // ... campos existentes ...
    'pix_qr_code' => $pixQrCode ?? null,
    'pix_copy_paste' => $pixCopyPaste ?? null,
    'boleto_url' => $boletoUrl ?? null,
    'boleto_barcode' => $boletoBarcode ?? null,
];
```

## Benefícios da Refatoração

1. **Código testado e funcionando** - Implementação copiada do Medlife que já está em produção
2. **Melhor tratamento de erros** - Exceções fornecem mensagens mais claras
3. **Menos verificações condicionais** - Try/catch é mais limpo que verificar `['success']`
4. **Acesso direto aos dados** - Não precisa acessar `['data']` toda vez
5. **Logs mais informativos** - Mensagens de erro do Asaas são capturadas diretamente
6. **Compatibilidade com scripts standalone** - Verificação de `function_exists('log_message')`

## Testes Realizados

✅ AsaasService inicializa corretamente
✅ Conexão com API Asaas (sandbox) estabelecida
✅ Todos os métodos essenciais existem:
  - createOrUpdateCustomer
  - createPixPayment
  - createBoletoPayment
  - createCreditCardPayment
  - payWithCreditCard
  - getPixQrCode

## Próximos Passos

1. Testar fluxo PIX completo
2. Testar fluxo Boleto completo
3. Testar fluxo Cartão de Crédito completo
4. Verificar que QR Code aparece corretamente
5. Verificar que erros são exibidos com mensagens claras

## Data da Refatoração

17 de novembro de 2025
