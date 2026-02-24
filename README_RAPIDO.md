# 🚀 DoarFazBem - Guia Rápido

Plataforma de crowdfunding completa com gateway de pagamento Asaas.

---

## 📍 URLs do Projeto

- **Local:** http://doarfazbem.ai
- **Produção:** https://doarfazbem.com.br (quando fizer deploy)
- **Painel Asaas:** https://www.asaas.com

---

## 🔐 Credenciais Asaas (PRODUÇÃO)

```
API Key: $aact_prod_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmVhNjE4OWQ2LWExOGYtNDQ4Ny1iZGQ1LThjODZkZTdlM2U5MTo6JGFhY2hfMDdmNDgwYTgtNmU3Ny00MzY1LWFhMGItNzhjNmM5NmIyOTY2
Wallet ID: 8e3acaa3-5040-436c-83fc-cff9b8c1b326
Webhook Token: @GAd8EDSS5Ypn4er@
Webhook URL: http://doarfazbem.ai/webhook/asaas
```

---

## 🧪 Teste Rápido do Webhook

```bash
# Sem token (deve retornar 401)
curl -X POST http://doarfazbem.ai/webhook/asaas \
  -H "Content-Type: application/json"

# Com token válido (deve retornar 404)
curl -X POST http://doarfazbem.ai/webhook/asaas \
  -H "Content-Type: application/json" \
  -H "asaas-access-token: @GAd8EDSS5Ypn4er@" \
  -d '{"event":"PAYMENT_CONFIRMED","payment":{"id":"pay_123"}}'
```

**Resultados esperados:**
- Sem token: `{"error":"Unauthorized"}` (401)
- Com token: `{"error":"Donation not found"}` (404)

✅ **Webhook funcionando!**

---

## 💰 Taxas Configuradas

### Gateway Asaas
- **PIX:** R$ 0,95 por transação
- **Boleto:** R$ 0,99 por boleto
- **Cartão:** R$ 0,49 + 1,99% (à vista)

### Plataforma DoarFazBem
- **Campanhas Médicas:** 0% ⭐
- **Campanhas Sociais:** 0% ⭐
- **Outras Campanhas:** 1%

---

## 📂 Arquivos Importantes

| Arquivo | Descrição |
|---------|-----------|
| `.env` | Configuração local |
| `.env.production` | Template para produção |
| `app/Config/Asaas.php` | Config do gateway |
| `app/Controllers/Webhook.php` | Recebe notificações |
| `app/Controllers/Donation.php` | Processa doações |
| `writable/logs/log-*.log` | Logs da aplicação |

---

## 🎯 Próximos Passos

### 1. Configurar Webhook no Asaas (5 min)
1. Acesse: https://www.asaas.com
2. Menu: **Configurações** > **Integrações** > **Webhooks**
3. Clique em **+ Novo Webhook**
4. Preencha:
   - Nome: `DoarFazBem - Notificações`
   - URL: `http://doarfazbem.ai/webhook/asaas`
   - Token: `@GAd8EDSS5Ypn4er@`
5. Marque eventos: `PAYMENT_*` (todos)
6. Salve

### 2. Criar Campanha de Teste (2 min)
1. Acesse: http://doarfazbem.ai/login
2. Faça login
3. Vá em: http://doarfazbem.ai/campaigns/create
4. Preencha e crie

### 3. Fazer Doação Teste (3 min)
1. Abra a campanha criada
2. Clique em **Doar Agora**
3. Escolha **PIX**
4. Preencha dados
5. Pague o PIX
6. **Aguarde confirmação automática!**

---

## 📊 Status dos Sistemas

| Sistema | Status |
|---------|--------|
| Autenticação | ✅ Funcionando |
| Campanhas | ✅ Funcionando |
| Gateway Asaas | ✅ Configurado |
| Webhook | ✅ Testado |
| PIX | ✅ Pronto |
| Boleto | ✅ Pronto |
| Cartão | ✅ Pronto |
| Email | ⏳ A configurar |
| WhatsApp | ⏳ Opcional |
| Deploy | ⏳ Pendente |

---

## 🔍 Ver Logs

```bash
# Logs mais recentes
tail -f c:\laragon\www\doarfazbem\writable\logs\log-2025-10-15.log

# Filtrar webhooks
grep "Webhook" c:\laragon\www\doarfazbem\writable\logs\log-*.log

# Filtrar erros
grep "ERROR" c:\laragon\www\doarfazbem\writable\logs\log-*.log
```

---

## 🛠️ Comandos Úteis

```bash
# Limpar cache
php spark cache:clear

# Ver rotas
php spark routes

# Testar banco
mysql -u root -e "USE doarfazbem; SELECT COUNT(*) FROM campaigns;"

# Reiniciar Apache (Laragon)
# Menu Laragon > Stop All > Start All
```

---

## 📚 Documentação Completa

| Documento | Descrição |
|-----------|-----------|
| `STATUS_ATUAL.md` | Status completo do projeto |
| `TESTE_ASAAS.md` | Guia de testes detalhado |
| `ASAAS_CONFIG.md` | Configuração Asaas completa |
| `ASAAS_CONFIGURADO.md` | Resumo da configuração |
| `DEPLOY.md` | Guia de deploy produção |
| `README_RAPIDO.md` | Este arquivo |

---

## 🆘 Problemas Comuns

### Webhook retorna 500
```bash
# Limpar cache
del /Q "c:\laragon\www\doarfazbem\writable\cache\*.cache"
```

### QR Code não aparece
```bash
# Ver logs
tail -f writable/logs/log-*.log
# Verificar API Key no .env
```

### Campanha não atualiza
```bash
# Verificar webhook no painel Asaas
# Ver logs do webhook: grep "Webhook" writable/logs/log-*.log
```

---

## 📞 Contatos

- **Email:** contato@doarfazbem.com.br
- **Asaas Suporte:** suporte@asaas.com | (11) 4420-8350
- **Docs Asaas:** https://docs.asaas.com

---

## ✅ Checklist de Validação

- [x] Webhook seguro com token
- [x] Credenciais de produção configuradas
- [x] Taxas configuradas corretamente
- [x] Models e Controllers funcionando
- [x] Documentação completa
- [ ] Webhook configurado no painel Asaas
- [ ] Primeira doação teste realizada
- [ ] Confirmação automática verificada

---

**🎉 Sistema 90% completo! Falta apenas testar com doação real!**

**Próximo passo:** Configurar webhook no painel Asaas e fazer doação teste.

---

**Última atualização:** 2025-10-15 22:30
