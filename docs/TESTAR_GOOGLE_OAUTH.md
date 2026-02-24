# 🧪 Como Testar Google OAuth - Passo a Passo

---

## ✅ Configurações Já Realizadas

1. ✅ Biblioteca `league/oauth2-google` instalada
2. ✅ GoogleOAuth library criada
3. ✅ Controller GoogleAuth criado
4. ✅ Rotas configuradas
5. ✅ Migration executada (campo `google_id` adicionado)
6. ✅ Credenciais Google adicionadas no `.env`
7. ✅ Botões "Continuar com Google" nas telas de login e registro

---

## 🖥️ TESTAR LOCALMENTE (Desenvolvimento)

### 1️⃣ **Acessar via localhost (não doarfazbem.test)**

O Google OAuth **não aceita** `doarfazbem.test` porque não é um domínio público válido.

**✅ IMPORTANTE: O `.env` já foi configurado para:**
```env
app.baseURL = 'http://localhost/'
ASAAS_WEBHOOK_URL = http://localhost/webhook/asaas
```

**Acesse:**
```
http://localhost/
```

**IMPORTANTE:** O Laragon precisa estar rodando na porta 80 padrão.

**⚠️ NÃO USE:** `http://doarfazbem.test/` - Google OAuth não funcionará!

---

### 2️⃣ **Verificar se está usando localhost**

Abra o navegador e acesse:
```
http://localhost/login
```

Você deve ver a tela de login com o botão **"Continuar com Google"**.

---

### 3️⃣ **Clicar em "Continuar com Google"**

1. Clique no botão "Continuar com Google"
2. Você será redirecionado para a tela de login do Google
3. Faça login com sua conta Google
4. Autorize o aplicativo DoarFazBem
5. Será redirecionado de volta para `http://localhost/auth/google/callback`

---

### 4️⃣ **O que deve acontecer:**

✅ **Sucesso:**
```
1. Google valida suas credenciais
2. Você é redirecionado para /auth/google/callback
3. Sistema cria sua conta automaticamente
4. Você é logado e redirecionado para /dashboard
5. Mensagem: "Login realizado com sucesso!"
```

❌ **Erro comum:** "redirect_uri_mismatch"
```
Causa: URL de callback não configurada no Google Console
Solução: Verificar se http://localhost/auth/google/callback
         está nas "Authorized redirect URIs"
```

---

### 5️⃣ **Verificar se funcionou**

Após login bem-sucedido, verifique:

1. **Sessão criada:** Você está logado no dashboard
2. **Usuário no banco:** Abra HeidiSQL/phpMyAdmin e veja a tabela `users`
3. **Campos preenchidos:**
   - `name` → Seu nome do Google
   - `email` → Seu email do Google
   - `google_id` → ID único do Google
   - `avatar` → URL da sua foto do Google
   - `email_verified` → `true`

---

## 🌐 TESTAR EM PRODUÇÃO

### 1️⃣ **Fazer Deploy**

Siga o guia [INSTALL_CPANEL.md](INSTALL_CPANEL.md) para fazer deploy em `https://app.doarfazbem.com.br`

---

### 2️⃣ **Atualizar .env Produção**

```env
CI_ENVIRONMENT = production
app.baseURL = 'https://app.doarfazbem.com.br/'
app.forceGlobalSecureRequests = true

GOOGLE_CLIENT_ID = 835916261080-91p24272phdv7d9m0o20o8mg3897ser0.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET = 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
```

---

### 3️⃣ **Testar Login Google em Produção**

Acesse:
```
https://app.doarfazbem.com.br/login
```

Clique em "Continuar com Google" e teste o fluxo completo.

---

## 🐛 TROUBLESHOOTING

### ❌ **Erro: "redirect_uri_mismatch"**

**Mensagem:**
```
Error 400: redirect_uri_mismatch
The redirect URI in the request, http://localhost/auth/google/callback,
does not match the ones authorized for the OAuth client.
```

**Solução:**
1. Acesse: https://console.cloud.google.com/
2. APIs & Services → Credentials
3. Clique no seu OAuth Client ID
4. Em "Authorized redirect URIs", adicione exatamente:
   ```
   http://localhost/auth/google/callback
   ```
5. Salve e aguarde 5 minutos

---

### ❌ **Erro: "Invalid state parameter"**

**Causa:** Estado OAuth inválido (proteção CSRF)

**Solução:**
1. Limpe os cookies do navegador
2. Tente novamente
3. Se persistir, verifique se sessões estão funcionando

---

### ❌ **Erro: "Token inválido ou expirado"**

**Causa:** Credenciais erradas ou expiradas

**Solução:**
1. Verifique se `GOOGLE_CLIENT_ID` e `GOOGLE_CLIENT_SECRET` estão corretos no `.env`
2. Não use espaços ou aspas extras
3. Regenere as credenciais no Google Console se necessário

---

### ❌ **Erro: "User not created"**

**Causa:** Erro ao inserir usuário no banco

**Solução:**
1. Verifique se migration foi executada: `php spark migrate:status`
2. Verifique se campo `google_id` existe na tabela `users`
3. Veja logs em `writable/logs/log-*.php`

---

### ❌ **Botão do Google não aparece**

**Causa:** Cache do navegador ou CSS não compilado

**Solução:**
1. Limpe cache do navegador (Ctrl + Shift + R)
2. Compile Tailwind: `npm run build`
3. Verifique se o arquivo de view foi atualizado corretamente

---

## 📝 FLUXO COMPLETO DO OAUTH

```
1. Usuário clica em "Continuar com Google"
   ↓
2. Sistema redireciona para Google (auth/google)
   ↓
3. Google mostra tela de login/autorização
   ↓
4. Usuário autoriza
   ↓
5. Google redireciona de volta (auth/google/callback?code=XXX&state=YYY)
   ↓
6. Sistema valida state (CSRF protection)
   ↓
7. Sistema troca code por access_token
   ↓
8. Sistema obtém dados do usuário (nome, email, avatar)
   ↓
9. Sistema busca usuário no banco por email ou google_id
   ↓
10a. Se existe: atualiza google_id e avatar
10b. Se não existe: cria novo usuário
   ↓
11. Sistema cria sessão
   ↓
12. Redireciona para /dashboard
```

---

## 🧪 TESTE MANUAL - CHECKLIST

### ✅ Desenvolvimento (localhost)

- [ ] Acessar `http://localhost/login`
- [ ] Botão "Continuar com Google" aparece
- [ ] Clicar no botão redireciona para Google
- [ ] Login com Google funciona
- [ ] Redirecionamento de volta funciona
- [ ] Usuário criado no banco de dados
- [ ] Sessão criada (logado no dashboard)
- [ ] Dados corretos (nome, email, avatar)
- [ ] Fazer logout e login novamente (deve reconhecer usuário)

### ✅ Produção (app.doarfazbem.com.br)

- [ ] Acessar `https://app.doarfazbem.com.br/login`
- [ ] Botão "Continuar com Google" aparece
- [ ] HTTPS funcionando (SSL válido)
- [ ] Clicar no botão redireciona para Google
- [ ] Login com Google funciona
- [ ] Redirecionamento de volta funciona
- [ ] Usuário criado no banco de dados
- [ ] Sessão criada (logado no dashboard)
- [ ] Logout e login novamente funciona

---

## 🔐 SEGURANÇA IMPLEMENTADA

✅ **State Parameter (CSRF Protection)**
- Token único gerado a cada tentativa
- Validado no callback
- Previne ataques Cross-Site Request Forgery

✅ **HTTPS em Produção**
- `app.forceGlobalSecureRequests = true`
- OAuth só funciona em HTTPS (produção)

✅ **Email Verificado Automaticamente**
- Google já validou o email
- `email_verified = true` automaticamente

✅ **Senha Aleatória**
- Usuários OAuth não precisam de senha
- Senha aleatória de 64 caracteres gerada automaticamente
- Impossível de adivinhar

---

## 📊 LOGS ÚTEIS PARA DEBUG

### Ver logs do CodeIgniter:
```bash
# Windows (Laragon)
c:\laragon\www\doarfazbem\writable\logs\log-2025-10-02.php

# Produção (SSH)
tail -f /home/usuario/app.doarfazbem.com.br/writable/logs/log-*.php
```

### Logs importantes:
```
Google OAuth Error: [mensagem]
Error getting Google user details: [mensagem]
```

---

## ✅ PRÓXIMOS PASSOS

Após testar com sucesso:

1. ✅ Testar logout e login novamente
2. ✅ Testar com múltiplas contas Google
3. ✅ Testar vinculação de conta existente (mesmo email)
4. ✅ Fazer deploy em produção
5. ✅ Configurar email SMTP para notificações
6. ✅ Testar fluxo completo de doação

---

**Pronto! Agora você pode testar o Google OAuth localmente e em produção! 🚀**
