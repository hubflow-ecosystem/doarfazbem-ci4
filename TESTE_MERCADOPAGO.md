# 🧪 Guia de Testes - Integração Mercado Pago

## 📋 Informações Gerais

- **Ambiente**: Sandbox (Testes)
- **Gateway**: Mercado Pago
- **Método**: PIX
- **Webhook**: `https://doarfazbem.ai/webhook/mercadopago/rifas`

## 🔑 Credenciais Configuradas

As credenciais já estão configuradas no arquivo `.env`:

```env
mercadopago.environment = sandbox
mercadopago.sandbox.public_key = TEST-da0a235f-9ef0-4063-b859-4f306d2361a3
mercadopago.sandbox.access_token = TEST-3987526622609082-120415-e7cdd66acf5a3ddffb64580c273b17f9-1651118957
```

## 👥 Contas de Teste do Mercado Pago

### Vendedor (Recebe Pagamentos)
- **User ID**: 3040726524
- **Usuário**: TESTUSER3078...
- **Senha**: MWn8ox2q2c

### Comprador 1 (Faz Pagamentos)
- **User ID**: 3110332639
- **Usuário**: TESTUSER7234350316522568034
- **Senha**: uW6608MO0H

### Comprador 2 (Alternativo)
- **User ID**: 3040726520
- **Usuário**: TESTUSER2610827093474899213
- **Senha**: lLtn2Mqasq

### Integrador
- **User ID**: 3041001410
- **Usuário**: TESTUSER62617898355729480
- **Senha**: nWFxjYvwFi

## 🚀 Métodos de Teste

### 1️⃣ Teste Automatizado via CLI

Execute o comando no terminal:

```bash
php spark test:mercadopago
```

**O que ele testa:**
- ✅ Verifica ambiente (sandbox)
- ✅ Valida credenciais
- ✅ Cria pagamento PIX
- ✅ Consulta status do pagamento
- ✅ Simula processamento de webhook

### 2️⃣ Teste Manual via Interface Web

**Acesse:** https://doarfazbem.ai/test-mercadopago.html

Este painel oferece:
- 📋 Lista de contas de teste
- 📝 Roteiro passo-a-passo
- 💳 Cartões de teste
- 🔗 Links úteis

### 3️⃣ Teste do Fluxo Completo

1. **Acesse a página de rifas:**
   ```
   https://doarfazbem.ai/numeros-da-sorte
   ```

2. **Compre alguns números:**
   - Selecione números disponíveis
   - Clique em "Comprar Números"
   - Preencha os dados

3. **Escolha PIX como forma de pagamento:**
   - Sistema gerará QR Code
   - Código PIX Copia e Cola disponível

4. **Simule o pagamento aprovado:**
   - Acesse: https://www.mercadopago.com.br/developers/panel/app
   - Vá em "Testes" → "Pagamentos de Teste"
   - Localize o pagamento criado
   - Clique em "Aprovar Pagamento"

5. **Verifique o webhook:**
   - O Mercado Pago enviará notificação para: `/webhook/mercadopago/rifas`
   - Sistema processa automaticamente
   - Números são creditados na conta

## 📊 Validar Qualidade da Integração

### No Painel do Mercado Pago

1. **Acessar:** https://mercadopago.com.br/developers/panel/app/3987526622609082/quality

2. **Clicar em "Avaliar qualidade"**

3. **Seguir 3 passos:**
   - ✅ Realizar 1 pagamento usando credenciais de produção
   - ✅ Inserir referência de pagamento produtivo
   - ✅ Trabalhar nas oportunidades de melhoria

### Métricas Avaliadas

- **Taxa de aprovação de pagamentos**
- **Experiência do usuário**
- **Segurança da integração**
- **Tratamento de erros**
- **Qualidade dos webhooks**

## 💳 Cartões de Teste (Caso precise)

| Resultado | Número | CVV | Validade |
|-----------|--------|-----|----------|
| ✅ Aprovado | 5031 4332 1540 6351 | 123 | 11/25 |
| ❌ Rejeitado | 5031 7557 3453 0604 | 123 | 11/25 |

## 🔍 Verificar Logs

### Logs do Sistema
```bash
tail -f writable/logs/log-2026-01-05.log
```

### Logs de Auditoria
```
https://doarfazbem.ai/admin/audit-logs
```

Filtrar por:
- **Ação**: `raffle_purchase_created`, `raffle_purchase_paid`
- **Entidade**: `raffle_purchases`

## ✅ Checklist de Validação

- [ ] Pagamento PIX criado com sucesso
- [ ] QR Code gerado corretamente
- [ ] Código Copia e Cola funciona
- [ ] Webhook recebe notificação de aprovação
- [ ] Status do pagamento atualiza para "paid"
- [ ] Números da sorte são creditados
- [ ] E-mail de confirmação enviado
- [ ] Log de auditoria registrado
- [ ] Taxas calculadas corretamente (1% Mercado Pago)
- [ ] Integração sem erros no painel do MP

## 🐛 Troubleshooting

### Erro: "Credenciais não configuradas"
- Verifique se o `.env` tem as chaves corretas
- Confirme que está usando credenciais de SANDBOX

### Webhook não recebe notificação
- Verifique se a URL está acessível externamente
- Confirme configuração no painel do Mercado Pago
- Use ngrok se estiver em localhost

### Pagamento não aprova automaticamente
- No sandbox, você precisa aprovar manualmente no painel
- Ou use as contas de teste para simular

### QR Code não aparece
- Verifique credenciais
- Confirme que está em modo sandbox
- Veja logs para mensagens de erro

## 📚 Documentação Oficial

- [Guia de Testes - Mercado Pago](https://www.mercadopago.com.br/developers/pt/docs/checkout-api/integration-test)
- [Contas de Teste](https://www.mercadopago.com.br/developers/pt/guides/online-payments/checkout-api/testing)
- [Webhooks](https://www.mercadopago.com.br/developers/pt/docs/checkout-api/additional-content/your-integrations/notifications/webhooks)

## 🎯 Próximos Passos

Após validar 100% dos testes:

1. **Obter aprovação de qualidade** no painel do MP
2. **Trocar credenciais** para PRODUÇÃO no `.env`:
   ```env
   mercadopago.environment = production
   ```
3. **Testar com valor real pequeno** (R$ 1,00)
4. **Monitorar primeiras transações** reais
5. **Ativar sistema para clientes**

---

**🎉 Boa sorte com os testes!**

Se encontrar problemas, verifique os logs ou consulte a documentação oficial.
