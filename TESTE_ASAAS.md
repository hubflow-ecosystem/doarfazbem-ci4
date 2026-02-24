# 🧪 Guia de Teste - Asaas Payment Gateway

Guia rápido para testar o sistema de pagamento Asaas configurado.

---

## ✅ Pré-requisitos

- [x] Credenciais Asaas configuradas no `.env`
- [x] Webhook implementado em `/webhook/asaas`
- [x] Servidor local rodando em `http://doarfazbem.ai`

---

## 🔧 PASSO 1: Testar Webhook Localmente

### 1.1 Verificar se o endpoint está acessível

```bash
# Testar endpoint do webhook (deve retornar erro 400 pois não tem payload)
curl -X POST http://doarfazbem.ai/webhook/asaas \
  -H "asaas-access-token: @GAd8EDSS5Ypn4er@" \
  -H "Content-Type: application/json"
```

**Resposta esperada:**
```json
{"error":"Invalid webhook"}
```

### 1.2 Testar com token inválido

```bash
curl -X POST http://doarfazbem.ai/webhook/asaas \
  -H "asaas-access-token: token_invalido" \
  -H "Content-Type: application/json" \
  -d '{"event":"PAYMENT_CONFIRMED"}'
```

**Resposta esperada:**
```json
{"error":"Unauthorized"}
```

Status Code: `401`

### 1.3 Testar com payload válido (sem doação cadastrada)

```bash
curl -X POST http://doarfazbem.ai/webhook/asaas \
  -H "asaas-access-token: @GAd8EDSS5Ypn4er@" \
  -H "Content-Type: application/json" \
  -d '{"event":"PAYMENT_CONFIRMED","payment":{"id":"pay_test_123"}}'
```

**Resposta esperada:**
```json
{"error":"Donation not found"}
```

Status Code: `404`

---

## 📝 PASSO 2: Criar Campanha de Teste

### 2.1 Fazer login na plataforma

1. Acesse: http://doarfazbem.ai/login
2. Faça login com suas credenciais

### 2.2 Criar campanha

1. Acesse: http://doarfazbem.ai/campaigns/create
2. Preencha:
   - **Título**: Teste de Doação PIX
   - **Categoria**: Médica (taxa 0%)
   - **Meta**: R$ 100,00
   - **Descrição**: Campanha de teste para validar integração com Asaas
   - **Data de Término**: 30 dias no futuro
3. Clique em **Criar Campanha**
4. Anote o ID da campanha criada (ex: `123`)

---

## 💰 PASSO 3: Testar Doação via PIX

### 3.1 Iniciar processo de doação

1. Acesse a campanha criada: http://doarfazbem.ai/campaigns/teste-de-doacao-pix
2. Clique em **Doar Agora**
3. Preencha:
   - **Nome**: Seu Nome
   - **Email**: seu@email.com
   - **CPF**: 000.000.000-00 (ou seu CPF real)
   - **Valor**: R$ 10,00
   - **Método**: PIX
4. Clique em **Doar com PIX**

### 3.2 Verificar QR Code gerado

- Sistema deve redirecionar para `/donations/pix/{id}`
- Deve aparecer:
  - QR Code para escanear
  - Código Pix Copia e Cola
  - Status "Aguardando pagamento"

### 3.3 Verificar logs

```bash
# Ver logs da aplicação
tail -f c:\laragon\www\doarfazbem\writable\logs\log-*.log
```

Deve aparecer algo como:
```
INFO - Criando pagamento PIX no Asaas
INFO - Resposta Asaas: {"id":"pay_xxx","status":"PENDING",...}
```

### 3.4 Pagar o PIX

**IMPORTANTE:** Para testar em ambiente de produção Asaas:

1. **Use um CPF diferente do CPF da conta Asaas** (Asaas não permite self-payment)
2. Abra o app do seu banco
3. Escaneie o QR Code OU copie o código Pix
4. Confirme o pagamento

### 3.5 Verificar confirmação automática

1. Após pagar, aguarde 5-10 segundos
2. Recarregue a página da campanha
3. **Valor arrecadado deve atualizar automaticamente!**

---

## 🔍 PASSO 4: Verificar Webhook Funcionou

### 4.1 Ver logs de webhook

```bash
# Filtrar apenas logs de webhook
grep "Webhook Asaas recebido" c:\laragon\www\doarfazbem\writable\logs\log-*.log
```

Deve mostrar:
```
INFO - Webhook Asaas recebido: {"event":"PAYMENT_CONFIRMED","payment":{"id":"pay_xxx",...}}
INFO - Pagamento confirmado: pay_xxx
INFO - Pagamento recebido: pay_xxx - R$ 10.00
```

### 4.2 Verificar no banco de dados

```sql
-- Ver doação criada
SELECT * FROM donations
WHERE asaas_payment_id = 'pay_xxx';

-- Verificar status da doação (deve ser 'confirmed')
-- Verificar se paid_at foi preenchido
```

### 4.3 Verificar campanha atualizada

```sql
-- Ver campanha atualizada
SELECT id, title, current_amount, donors_count
FROM campaigns
WHERE id = 123;

-- current_amount deve ter aumentado R$ 10,00
-- donors_count deve ter aumentado +1
```

---

## 📊 PASSO 5: Testar Outros Métodos de Pagamento

### 5.1 Testar Boleto

1. Crie nova doação escolhendo **Boleto**
2. Sistema deve redirecionar para `/donations/boleto/{id}`
3. Deve aparecer:
   - Botão para baixar PDF do boleto
   - Código de barras
   - Linha digitável
   - Data de vencimento

**Nota:** Boleto leva 1-3 dias úteis para confirmar após pagamento.

### 5.2 Testar Cartão de Crédito

1. Crie nova doação escolhendo **Cartão de Crédito**
2. Preencha dados do cartão:
   - **Número**: Use cartão de teste Asaas
   - **CVV**: 123
   - **Validade**: Futuro
3. Clique em **Processar Pagamento**
4. Sistema deve confirmar **imediatamente** se aprovado

**Cartões de teste Asaas (produção):**
- Aprovado: `5162306219378829` (Mastercard)
- Recusado: Use qualquer cartão inválido

---

## 🛡️ PASSO 6: Testar Segurança do Webhook

### 6.1 Testar sem token

```bash
curl -X POST http://doarfazbem.ai/webhook/asaas \
  -H "Content-Type: application/json" \
  -d '{"event":"PAYMENT_CONFIRMED","payment":{"id":"pay_xxx"}}'
```

**Deve retornar:** `401 Unauthorized` ✅

### 6.2 Testar com token errado

```bash
curl -X POST http://doarfazbem.ai/webhook/asaas \
  -H "asaas-access-token: senha_errada" \
  -H "Content-Type: application/json" \
  -d '{"event":"PAYMENT_CONFIRMED","payment":{"id":"pay_xxx"}}'
```

**Deve retornar:** `401 Unauthorized` ✅

---

## ⚙️ PASSO 7: Configurar Webhook no Painel Asaas

Agora que testamos localmente, configure o webhook no painel:

### 7.1 Acessar painel Asaas

1. Acesse: https://www.asaas.com
2. Faça login
3. Vá em **Configurações** > **Integrações** > **Webhooks**

### 7.2 Criar webhook

1. Clique em **+ Novo Webhook**
2. Preencha:
   - **Nome**: DoarFazBem - Notificações de Pagamento
   - **URL**: `http://doarfazbem.ai/webhook/asaas` (ou use ngrok para expor localmente)
   - **Token**: `@GAd8EDSS5Ypn4er@`
   - **Versão**: v3
3. Marque eventos:
   - [x] PAYMENT_CREATED
   - [x] PAYMENT_CONFIRMED ⭐
   - [x] PAYMENT_RECEIVED ⭐
   - [x] PAYMENT_REFUNDED
   - [x] PAYMENT_OVERDUE
   - [x] (Veja lista completa em ASAAS_CONFIG.md)
4. Salve

### 7.3 Testar webhook no painel

1. Clique nos **3 pontinhos** ao lado do webhook
2. Clique em **Testar Webhook**
3. Selecione evento: `PAYMENT_CONFIRMED`
4. Clique em **Enviar Teste**
5. Verifique nos logs se chegou

---

## 🌐 PASSO 8: Expor Localhost com Ngrok (Opcional)

Para receber webhooks do Asaas em localhost:

### 8.1 Instalar Ngrok

```bash
# Download: https://ngrok.com/download
# Ou via Chocolatey:
choco install ngrok
```

### 8.2 Expor porta 80

```bash
ngrok http doarfazbem.ai:80
```

### 8.3 Copiar URL pública

```
Forwarding: https://abc123.ngrok.io -> http://doarfazbem.ai:80
```

### 8.4 Atualizar webhook no Asaas

1. Acesse painel Asaas
2. Edite webhook
3. Altere URL para: `https://abc123.ngrok.io/webhook/asaas`
4. Salve

Agora o Asaas consegue enviar webhooks para seu localhost!

---

## ✅ CHECKLIST DE TESTES

### Testes Locais
- [ ] Webhook retorna 401 sem token
- [ ] Webhook retorna 404 para doação inexistente
- [ ] Campanha criada com sucesso
- [ ] Doação PIX gerada com QR Code

### Testes de Pagamento
- [ ] PIX pago e confirmado automaticamente
- [ ] Valor da campanha atualizado
- [ ] Doador aparece na lista
- [ ] Boleto gerado com PDF
- [ ] Cartão aprovado instantaneamente

### Testes de Segurança
- [ ] Token inválido = 401
- [ ] Token ausente = 401
- [ ] Payload inválido = 400

### Testes de Webhook
- [ ] Webhook recebido nos logs
- [ ] Status da doação atualizado
- [ ] Transação registrada no BD
- [ ] Email enviado (se configurado)

---

## 🐛 TROUBLESHOOTING

### Problema: QR Code não aparece

**Solução:**
1. Ver logs: `tail -f writable/logs/log-*.log`
2. Verificar resposta da API Asaas
3. Verificar se `ASAAS_API_KEY` está correta no `.env`

### Problema: Pagamento não confirma

**Solução:**
1. Verificar se webhook está configurado no Asaas
2. Usar ngrok se estiver em localhost
3. Ver logs de erro no painel Asaas (Webhooks > Histórico)

### Problema: Valor não atualiza na campanha

**Solução:**
1. Ver logs do webhook: `grep "Webhook" writable/logs/log-*.log`
2. Verificar se doação existe: `SELECT * FROM donations WHERE asaas_payment_id = 'pay_xxx'`
3. Verificar se método `updateDonationStats()` foi chamado

### Problema: Erro 404 no webhook

**Solução:**
1. Verificar rota: `app/Config/Routes.php` deve ter `$routes->post('webhook/asaas', 'Webhook::asaas');`
2. Limpar cache: `php spark cache:clear`
3. Reiniciar Apache: `Laragon > Stop All > Start All`

---

## 📞 Suporte

**Asaas:**
- Dashboard: https://www.asaas.com
- Docs: https://docs.asaas.com
- Suporte: suporte@asaas.com
- WhatsApp: (11) 4420-8350

**DoarFazBem:**
- Logs: `writable/logs/log-*.log`
- Email: contato@doarfazbem.com.br

---

## 📚 Próximos Passos

Após validar tudo localmente:

1. **Deploy para produção** (ver `DEPLOY.md`)
2. **Configurar webhook com URL pública** (https://doarfazbem.com.br/webhook/asaas)
3. **Configurar SSL/HTTPS** (Let's Encrypt)
4. **Testar em produção** com doação real
5. **Monitorar logs** de webhook e pagamentos

---

**Ambiente:** DESENVOLVIMENTO (Laragon)
**Credenciais:** PRODUÇÃO (Asaas)
**Webhook:** Token protegido ✅
**Status:** Pronto para testar 🚀
