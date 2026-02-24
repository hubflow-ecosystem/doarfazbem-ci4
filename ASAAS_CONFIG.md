# 🔐 Configuração Completa do Asaas

Guia passo-a-passo para configurar o gateway de pagamento Asaas na plataforma DoarFazBem.

---

## ✅ CREDENCIAIS CONFIGURADAS

As credenciais já estão salvas no `.env`:

```env
ASAAS_API_KEY = $aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmVhNjE4OWQ2LWExOGYtNDQ4Ny1iZGQ1LThjODZkZTdlM2U5MTo6JGFhY2hfMDdmNDgwYTgtNmU3Ny00MzY1LWFhMGItNzhjNmM5NmIyOTY2
ASAAS_ENVIRONMENT = production
ASAAS_WALLET_ID = 8e3acaa3-5040-436c-83fc-cff9b8c1b326
ASAAS_WEBHOOK_URL = http://doarfazbem.ai/webhook/asaas
ASAAS_WEBHOOK_TOKEN = @GAd8EDSS5Ypn4er@
```

---

## 🔧 PASSO 1: Configurar Webhook no Painel Asaas

### 1.1 Acessar Configurações de Webhook

1. Faça login em: https://www.asaas.com
2. Vá em **Configurações** (ícone de engrenagem no canto superior direito)
3. No menu lateral, clique em **Integrações**
4. Clique em **Webhooks**

### 1.2 Criar Novo Webhook

1. Clique no botão **+ Novo Webhook**

2. **Nome do Webhook**: `DoarFazBem - Notificações de Pagamento`

3. **URL do Webhook**:
   - **Desenvolvimento**: `http://doarfazbem.ai/webhook/asaas`
   - **Produção**: `https://doarfazbem.com.br/webhook/asaas`

4. **Token de Autenticação** (Access Token):
   ```
   @GAd8EDSS5Ypn4er@
   ```
   ⚠️ **Importante**: Este token será enviado no header `asaas-access-token` de cada requisição

5. **Versão da API**: Selecione a mais recente (v3)

6. **Eventos para Notificar**: Marque os seguintes eventos:

   #### ✅ Eventos de Pagamento:
   - [x] `PAYMENT_CREATED` - Pagamento criado
   - [x] `PAYMENT_AWAITING_RISK_ANALYSIS` - Aguardando análise de risco
   - [x] `PAYMENT_APPROVED_BY_RISK_ANALYSIS` - Aprovado pela análise
   - [x] `PAYMENT_CONFIRMED` - Pagamento confirmado ⭐
   - [x] `PAYMENT_RECEIVED` - Pagamento recebido ⭐
   - [x] `PAYMENT_OVERDUE` - Pagamento vencido
   - [x] `PAYMENT_REFUNDED` - Pagamento reembolsado ⭐
   - [x] `PAYMENT_RECEIVED_IN_CASH` - Recebido em dinheiro
   - [x] `PAYMENT_CHARGEBACK_REQUESTED` - Chargeback solicitado
   - [x] `PAYMENT_CHARGEBACK_DISPUTE` - Disputa de chargeback
   - [x] `PAYMENT_AWAITING_CHARGEBACK_REVERSAL` - Aguardando reversão
   - [x] `PAYMENT_DUNNING_RECEIVED` - Pagamento em atraso recebido
   - [x] `PAYMENT_DELETED` - Pagamento deletado
   - [x] `PAYMENT_RESTORED` - Pagamento restaurado

7. **Status**: Marque como **Ativo**

8. Clique em **Salvar**

---

## 🧪 PASSO 2: Testar Webhook

### 2.1 Teste Manual no Painel Asaas

1. Na lista de webhooks, clique nos **3 pontinhos** ao lado do webhook criado
2. Clique em **Testar Webhook**
3. Selecione um evento (ex: `PAYMENT_CONFIRMED`)
4. Clique em **Enviar Teste**

### 2.2 Verificar se Chegou

1. Acesse os logs da aplicação:
   ```bash
   tail -f writable/logs/log-*.log
   ```

2. Você deve ver algo como:
   ```
   INFO - Webhook Asaas recebido: {"event":"PAYMENT_CONFIRMED",...}
   ```

3. Se aparecer erro `401 Unauthorized`, verifique se o token está correto

---

## 💳 PASSO 3: Configurar Meios de Pagamento

### 3.1 PIX

✅ **Já está configurado automaticamente!**

O Asaas gera PIX automaticamente usando a chave PIX da sua conta.

### 3.2 Boleto Bancário

✅ **Já está configurado automaticamente!**

O Asaas gera boletos automaticamente.

**Configurações importantes:**

1. No painel Asaas, vá em **Configurações > Meios de Pagamento > Boleto**
2. Verifique:
   - **Multa por atraso**: 2%
   - **Juros ao dia**: 1%
   - **Dias após vencimento para cancelar**: 30 dias

### 3.3 Cartão de Crédito

1. No painel Asaas, vá em **Configurações > Meios de Pagamento > Cartão de Crédito**
2. Verifique se está **Ativo**
3. Configure:
   - **Parcelamento**: Até 12x (opcional)
   - **Taxas**: Conforme contrato Asaas
   - **Captura**: Automática (recomendado)

---

## 💰 PASSO 4: Configurar Taxas da Plataforma

### 4.1 Taxas do DoarFazBem

A plataforma já está configurada com as seguintes taxas:

```php
// Campanhas Médicas e Sociais
platform_fee = 0%

// Outras categorias (Educação, Negócio, Criativa, Esporte)
platform_fee = 1%
```

### 4.2 Taxas do Asaas (Gateway)

As taxas do Asaas são cobradas automaticamente:

- **PIX**: 0,99% (mínimo R$ 0,99)
- **Boleto**: R$ 3,49 por boleto
- **Cartão à vista**: 2,99%
- **Cartão parcelado**: 3,99% + juros parcelamento

**Estas taxas são pagas diretamente ao Asaas, não passam pela plataforma.**

---

## 🔄 PASSO 5: Como Funciona o Fluxo de Pagamento

### Fluxo PIX:

```
1. Usuário clica em "Doar com PIX"
   ↓
2. Sistema cria cobrança no Asaas via API
   ↓
3. Asaas retorna QR Code e código Copia/Cola
   ↓
4. Usuário paga o PIX
   ↓
5. Asaas detecta pagamento e envia webhook
   ↓
6. Sistema recebe webhook e confirma doação
   ↓
7. Valor é creditado na conta do criador da campanha
```

### Fluxo Boleto:

```
1. Usuário clica em "Doar com Boleto"
   ↓
2. Sistema cria cobrança no Asaas via API
   ↓
3. Asaas gera boleto (PDF + código de barras)
   ↓
4. Usuário imprime e paga no banco
   ↓
5. Banco confirma pagamento (1-3 dias úteis)
   ↓
6. Asaas detecta confirmação e envia webhook
   ↓
7. Sistema recebe webhook e confirma doação
```

### Fluxo Cartão:

```
1. Usuário clica em "Doar com Cartão"
   ↓
2. Usuário preenche dados do cartão
   ↓
3. Sistema envia dados para API Asaas
   ↓
4. Asaas processa com adquirente
   ↓
5. Se aprovado, webhook é enviado imediatamente
   ↓
6. Sistema confirma doação em tempo real
```

---

## 🛡️ PASSO 6: Segurança do Webhook

### 6.1 Validação Implementada

O webhook já está protegido com:

1. **Token de Autenticação**: Valida header `asaas-access-token`
2. **IP Whitelist** (opcional): Pode configurar no Asaas
3. **HTTPS em Produção**: Obrigatório para segurança

### 6.2 IPs do Asaas (para whitelist no firewall)

Se quiser restringir ainda mais, adicione no firewall:

```
177.12.178.0/24
177.12.179.0/24
```

---

## 📊 PASSO 7: Monitoramento e Logs

### 7.1 Ver Logs de Webhook

```bash
# Logs da aplicação
tail -f writable/logs/log-*.log | grep "Webhook"

# Ver webhooks recebidos
grep "Webhook Asaas recebido" writable/logs/log-*.log
```

### 7.2 Painel Asaas - Histórico de Webhooks

1. Acesse: https://www.asaas.com
2. Vá em **Configurações > Integrações > Webhooks**
3. Clique no webhook criado
4. Veja **Histórico de Envios**
   - ✅ Verde: Sucesso (status 200)
   - ❌ Vermelho: Erro (status 4xx/5xx)
   - 🔄 Amarelo: Pendente de reenvio

---

## 🔍 PASSO 8: Testar Pagamento Real

### 8.1 Criar Campanha de Teste

1. Faça login em: http://doarfazbem.ai
2. Vá em **Dashboard > Criar Campanha**
3. Preencha:
   - Título: "Teste de Doação"
   - Categoria: Médica (taxa 0%)
   - Meta: R$ 100,00
4. Clique em **Criar**

### 8.2 Fazer Doação de Teste - PIX

1. Acesse a campanha criada
2. Clique em **Doar Agora**
3. Escolha valor: R$ 10,00
4. Selecione **PIX**
5. Copie o código PIX
6. **Pague usando um CPF diferente do seu** (Asaas não permite self-payment)

### 8.3 Verificar Confirmação

1. Após pagar, aguarde 5-10 segundos
2. Recarregue a página da campanha
3. O valor arrecadado deve atualizar automaticamente!
4. Verifique nos logs:
   ```bash
   tail -f writable/logs/log-*.log
   ```

---

## ⚠️ TROUBLESHOOTING

### Problema: Webhook não está chegando

**Solução:**

1. Verificar URL do webhook:
   ```bash
   curl -X POST http://doarfazbem.ai/webhook/asaas \
     -H "asaas-access-token: @GAd8EDSS5Ypn4er@" \
     -H "Content-Type: application/json" \
     -d '{"event":"PAYMENT_CONFIRMED","payment":{"id":"test"}}'
   ```

2. Se retornar `404 Donation not found` → Webhook está funcionando! ✅

3. Se retornar `401 Unauthorized` → Token está errado

4. Se retornar erro de conexão → Firewall bloqueando

### Problema: Pagamento não confirma após webhook

**Solução:**

1. Verificar se doação existe no banco:
   ```sql
   SELECT * FROM donations WHERE asaas_payment_id = 'pay_xxx';
   ```

2. Verificar logs de erro:
   ```bash
   grep "ERROR" writable/logs/log-*.log
   ```

### Problema: Taxa incorreta sendo cobrada

**Solução:**

1. Verificar categoria da campanha:
   ```sql
   SELECT id, title, category FROM campaigns WHERE id = X;
   ```

2. Médica/Social = 0% taxa
3. Outras categorias = 1% taxa

---

## ✅ CHECKLIST FINAL

Antes de ir para produção, confirme:

- [ ] Webhook configurado no Asaas
- [ ] Token de segurança configurado
- [ ] URL do webhook correta (https:// em produção)
- [ ] Eventos de pagamento marcados
- [ ] Teste de webhook funcionando
- [ ] Doação de teste (PIX) concluída
- [ ] Valor atualizado automaticamente
- [ ] Logs sem erros
- [ ] Certificado SSL ativo (produção)

---

## 📞 Suporte

**Asaas:**
- Dashboard: https://www.asaas.com
- Suporte: suporte@asaas.com
- WhatsApp: (11) 4420-8350
- Documentação: https://docs.asaas.com

**DoarFazBem:**
- Email: contato@doarfazbem.com.br
- Logs: `writable/logs/log-*.log`

---

**Credenciais salvas em:** `.env` e `.env.production`
**Webhook protegido com:** Token de autenticação
**Ambiente:** PRODUÇÃO ⚠️
