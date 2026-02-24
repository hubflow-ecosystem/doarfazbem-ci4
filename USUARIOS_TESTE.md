# 👥 Usuários para Teste - DoarFazBem

**Atualizado:** 2025-10-16 11:00

---

## ✅ BANCO DE DADOS LIMPO!

Todos os usuários antigos foram **excluídos**. Agora existe apenas o **Super Admin**.

---

## 🔐 Super Admin (Único usuário no sistema)

```
Email: admin@doarfazbem.ai
Senha: password
Role: admin
```

**Login:** http://doarfazbem.ai/login

---

## 🆕 NOVIDADE: Criação Automática de Conta Asaas!

### O que mudou:

Quando você criar um **novo usuário** (via registro), o sistema agora:

1. ✅ Cria o usuário no banco de dados local
2. ✅ **Cria automaticamente uma subconta no Asaas** (se tiver CPF)
3. ✅ Salva o `asaas_account_id` no banco
4. ✅ Registra tudo nos logs

### Requisitos para criar conta Asaas:

- ✅ Nome completo
- ✅ Email válido
- ✅ **CPF (obrigatório!)**
- ✅ Telefone (opcional, mas recomendado)

---

## 📝 Como Criar Novo Usuário (Com Conta Asaas Automática)

### Opção 1: Via Interface (Recomendado)

1. **Acessar:** http://doarfazbem.ai/register
2. **Preencher:**
   - Nome: Nome Completo
   - Email: seuemail@example.com
   - CPF: 123.456.789-00 (formato com pontos e traço)
   - Telefone: 11987654321 (opcional)
   - Senha: mínimo 8 caracteres
   - Confirmar senha
   - ✅ Aceitar termos
3. **Clicar em "Registrar"**

**O sistema vai:**
- Criar o usuário
- Criar a conta Asaas automaticamente
- Fazer login automático
- Redirecionar para o dashboard

### Opção 2: Via SQL (Rápido, mas SEM conta Asaas)

```sql
-- Senha: password
INSERT INTO users (name, email, cpf, password_hash, role, created_at, updated_at)
VALUES (
  'Nome Teste',
  'teste@example.com',
  '12345678900',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'user',
  NOW(),
  NOW()
);
```

⚠️ **Nota:** Criando via SQL, a conta Asaas **NÃO será criada**. Use a interface!

---

## 🔍 Verificar se Conta Asaas foi Criada

### Via Banco de Dados

```sql
SELECT
  id,
  name,
  email,
  cpf,
  asaas_account_id,
  created_at
FROM users
WHERE email = 'seuemail@example.com';
```

**Se `asaas_account_id` estiver preenchido = Conta Asaas criada! ✅**

### Via Logs

```bash
# Ver logs de criação de conta Asaas
grep "Conta Asaas criada" c:\laragon\www\doarfazbem\writable\logs\log-*.log

# Ver erros de criação (se houver)
grep "Erro ao criar conta Asaas" c:\laragon\www\doarfazbem\writable\logs\log-*.log
```

---

## 🎯 Fluxo Completo de Teste

### 1. Criar Usuário Normal (Criador de Campanha)

1. **Logout** (se estiver logado como admin): http://doarfazbem.ai/logout
2. **Registrar**: http://doarfazbem.ai/register
3. **Preencher:**
   - Nome: João Silva
   - Email: joao@example.com
   - **CPF: 123.456.789-00** ⭐ (importante!)
   - Telefone: 11987654321
   - Senha: senha1234
   - Confirmar senha
   - ✅ Aceitar termos
4. **Clicar em "Registrar"**

**Sistema vai criar:**
- ✅ Usuário local (id: 2)
- ✅ Conta Asaas (subconta para receber pagamentos)
- ✅ Login automático

### 2. Criar Campanha

1. Após login, ir em: http://doarfazbem.ai/campaigns/create
2. Preencher:
   - **Título:** "Ajuda para Tratamento Médico"
   - **Categoria:** Médica (taxa 0% da plataforma!)
   - **Meta:** R$ 1.000,00
   - **Descrição:** Mínimo 50 caracteres
   - **Data fim:** 30 dias no futuro
   - **Imagem:** Upload opcional
3. **Criar Campanha**

### 3. Fazer Doação (Como Outro Usuário)

1. **Logout** ou **abrir em aba anônima**
2. **Abrir a campanha** criada
3. **Clicar em "Doar Agora"**
4. **Preencher:**
   - Nome: Maria Doadora
   - Email: maria@example.com
   - CPF: 987.654.321-00
   - Valor: R$ 50,00
   - Método: **PIX**
5. **Clicar em "Doar com PIX"**

**Sistema vai:**
- Gerar QR Code PIX
- Criar cobrança no Asaas (sandbox = teste)
- Aguardar pagamento

### 4. Verificar Sandbox

Como está em **sandbox**, o PIX não funciona de verdade. Você pode:

- **Ver no painel Asaas Sandbox:** https://sandbox.asaas.com
- **Simular confirmação** manualmente
- **Ou aguardar webhook** (se configurou)

---

## 📊 Verificar Dados no Banco

### Ver todos os usuários

```sql
SELECT
  id,
  name,
  email,
  role,
  asaas_account_id,
  created_at
FROM users
ORDER BY created_at DESC;
```

### Ver campanhas criadas

```sql
SELECT
  c.id,
  c.title,
  c.goal_amount,
  c.current_amount,
  u.name as criador,
  u.asaas_account_id
FROM campaigns c
INNER JOIN users u ON c.user_id = u.id
ORDER BY c.created_at DESC;
```

### Ver doações

```sql
SELECT
  d.id,
  d.donor_name,
  d.amount,
  d.payment_method,
  d.payment_status,
  d.asaas_payment_id,
  c.title as campanha
FROM donations d
INNER JOIN campaigns c ON d.campaign_id = c.id
ORDER BY d.created_at DESC;
```

---

## ⚠️ Observações Importantes

### Sobre CPF

- ✅ **Obrigatório** para criar conta Asaas
- ✅ Formato: `123.456.789-00` (com pontos e traço)
- ✅ Será validado pelo sistema
- ⚠️ Se não tiver CPF, conta Asaas **NÃO será criada** (mas usuário sim)

### Sobre Sandbox Asaas

- ✅ Ambiente de **TESTES**
- ✅ Pagamentos **NÃO são reais**
- ✅ QR Codes PIX **NÃO funcionam** em apps bancários
- ✅ Você pode simular confirmações manualmente

### Sobre Logs

- ✅ Tudo é registrado em `writable/logs/log-*.log`
- ✅ Sucessos: `Conta Asaas criada para usuário X`
- ⚠️ Erros: `Erro ao criar conta Asaas` (não bloqueia registro)

---

## 🔧 Comandos Úteis

### Limpar usuários e resetar

```sql
-- Deletar todos os usuários
DELETE FROM users;

-- Criar apenas o super admin
INSERT INTO users (id, name, email, password_hash, role, created_at, updated_at)
VALUES (
  1,
  'Super Admin',
  'admin@doarfazbem.ai',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin',
  NOW(),
  NOW()
);
```

### Ver logs em tempo real

```bash
# Windows (PowerShell)
Get-Content c:\laragon\www\doarfazbem\writable\logs\log-2025-10-16.log -Wait -Tail 20

# Ou via comando Linux no Git Bash
tail -f c:\laragon\www\doarfazbem\writable\logs\log-*.log
```

### Limpar cache

```bash
del /Q "c:\laragon\www\doarfazbem\writable\cache\*.cache"
```

---

## 📄 Estrutura da Tabela Users (Atualizada)

```sql
+----------------------+------------------+------+-----+---------+
| Field                | Type             | Null | Key | Default |
+----------------------+------------------+------+-----+---------+
| id                   | int unsigned     | NO   | PRI | NULL    |
| name                 | varchar(255)     | NO   |     | NULL    |
| email                | varchar(255)     | NO   | UNI | NULL    |
| google_id            | varchar(255)     | YES  | UNI | NULL    |
| phone                | varchar(20)      | YES  |     | NULL    |
| cpf                  | varchar(14)      | YES  | UNI | NULL    |
| password_hash        | varchar(255)     | NO   |     | NULL    |
| asaas_customer_id    | varchar(100)     | YES  |     | NULL    |
| asaas_wallet_id      | varchar(100)     | YES  |     | NULL    |
| asaas_account_id     | varchar(100)     | YES  |     | NULL    | ⭐ NOVO
| email_verified       | tinyint(1)       | NO   |     | 0       |
| role                 | enum(...)        | NO   |     | user    |
| avatar               | varchar(255)     | YES  |     | NULL    |
| created_at           | datetime         | YES  |     | NULL    |
| updated_at           | datetime         | YES  |     | NULL    |
| last_login           | datetime         | YES  |     | NULL    |
| reset_token          | varchar(100)     | YES  |     | NULL    |
| reset_token_expiry   | datetime         | YES  |     | NULL    |
+----------------------+------------------+------+-----+---------+
```

---

## ✅ Resumo

**Situação Atual:**
- ✅ Banco limpo (só o Super Admin)
- ✅ Sistema cria conta Asaas automaticamente no registro
- ✅ Sandbox Asaas configurado
- ✅ Pronto para testes

**Próximo Passo:**
1. Criar usuário via registro (com CPF)
2. Verificar se `asaas_account_id` foi preenchido
3. Criar campanha
4. Fazer doação teste

**Credencial Super Admin:**
```
Email: admin@doarfazbem.ai
Senha: password
```

---

**Boa sorte nos testes! 🚀**
