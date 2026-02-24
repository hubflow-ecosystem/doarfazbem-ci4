# TODAS AS CORREÇÕES NECESSÁRIAS - Análise Completa

## Tabela donations - Colunas Existentes
```
id, campaign_id, user_id, donor_name, donor_email, amount, charged_amount,
platform_fee, payment_gateway_fee, net_amount, donor_pays_fees, payment_method,
asaas_payment_id, status, is_anonymous, message, pix_qr_code, pix_copy_paste,
boleto_url, boleto_barcode, paid_at, created_at, updated_at
```

## Tabela asaas_transactions - Colunas Existentes
```
id, donation_id, subscription_id, asaas_payment_id, asaas_customer_id, amount,
payment_method, status, webhook_data, processed_at, created_at, updated_at
```

## PROBLEMAS ENCONTRADOS

### 1. WebhookController.php - payment_date (NÃO EXISTE)
**Linhas:** 222, 284
**Problema:** Tentando atualizar `payment_date` que não existe
**Solução:** Trocar por `paid_at` (que existe)

### 2. Campaign.php - api_response em asaas_accounts
**Linhas:** 498, 524
**Problema:** Tentando salvar `api_response` em tabela que pode não ter essa coluna
**Solução:** Verificar se coluna existe ou remover

### 3. Donation.php - api_response em subscriptions (linha 703)
**Problema:** Tentando salvar `api_response` em subscriptions
**Solução:** Verificar se coluna existe

### 4. Views - gateway_fee vs payment_gateway_fee
**Arquivo:** credit_card.php
**Problema:** View usa `gateway_fee` mas tabela tem `payment_gateway_fee`
**Solução:** Padronizar nomenclatura

## AÇÕES CORRETIVAS

### A. Corrigir WebhookController.php
- Trocar todas as ocorrências de `'payment_date'` por `'paid_at'`

### B. Verificar estrutura de asaas_accounts
- Checar se tem coluna `api_response`
- Se não tiver, remover do código ou adicionar coluna

### C. Verificar estrutura de subscriptions
- Checar se tem coluna `api_response`
- Se não tiver, remover do código ou adicionar coluna

### D. Padronizar nomenclatura de taxas
- Decidir: `gateway_fee` OU `payment_gateway_fee`
- Atualizar código e views de acordo
