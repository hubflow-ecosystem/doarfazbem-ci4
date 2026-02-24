# 🔑 Credenciais de Acesso - DoarFazBem

## ✅ Usuários Criados com Sucesso!

Acesse: **http://doarfazbem.test/login**

---

## 👑 SUPER ADMINISTRADOR

**Email:** admin@doarfazbem.test
**Senha:** Admin@2025
**Role:** Administrador do sistema
**Perfil:** Acesso total, gerenciamento de usuários e campanhas

---

## 👥 USUÁRIOS DE TESTE

### 1. João Pedro Silva
- **Email:** joao.silva@email.com
- **Senha:** Joao@2025
- **CPF:** 123.456.789-01
- **Telefone:** (11) 97654-3210
- **Cidade:** São Paulo/SP
- **Perfil:** Criador de campanhas médicas

### 2. Maria Eduarda Santos
- **Email:** maria.santos@email.com
- **Senha:** Maria@2025
- **CPF:** 987.654.321-09
- **Telefone:** (21) 98765-4321
- **Cidade:** Rio de Janeiro/RJ
- **Perfil:** Doadora frequente

### 3. Pedro Henrique Oliveira
- **Email:** pedro.oliveira@email.com
- **Senha:** Pedro@2025
- **CPF:** 112.233.445-56
- **Telefone:** (31) 91234-5678
- **Cidade:** Belo Horizonte/MG
- **Perfil:** Criador de campanhas sociais

### 4. Ana Carolina Costa
- **Email:** ana.costa@email.com
- **Senha:** Ana@2025
- **CPF:** 223.344.556-67
- **Telefone:** (41) 99876-5432
- **Cidade:** Curitiba/PR
- **Perfil:** Criadora de campanhas educacionais

### 5. Carlos Eduardo Souza
- **Email:** carlos.souza@email.com
- **Senha:** Carlos@2025
- **CPF:** 334.455.667-78
- **Telefone:** (51) 98712-3456
- **Cidade:** Porto Alegre/RS
- **Perfil:** Doador e criador

---

## 🔧 Problema Resolvido: reCAPTCHA

O erro "Verificação de segurança falhou" foi causado pelo reCAPTCHA não configurado.

**Solução aplicada:**
- reCAPTCHA **temporariamente desabilitado** em `app/Config/Google.php`
- Alteradas as chaves para strings vazias
- Threshold ajustado para 0.0 (aceita tudo)

**Solução aplicada:**

1. **Desabilitado em `app/Config/Google.php`**
   - Chaves alteradas para strings vazias
   - Threshold ajustado para 0.0

2. **Comentado no controller `app/Controllers/User.php`**
   - Linha 51-59: `processRegister()` - Verificação comentada
   - Linha 210-218: `processLogin()` - Verificação comentada

**Arquivos modificados:**
- `app/Config/Google.php` (linhas 48-51)
- `app/Controllers/User.php` (linhas 51-59, 210-218)

---

## 📝 Notas Importantes

1. **Padrão de Senhas:** PrimeiroNome@2025
2. **Todos os usuários estão verificados** (email_verified = 1)
3. **Status:** Todos ativos (status = active)
4. **reCAPTCHA:** Desabilitado para testes locais

---

## 🚀 Próximos Passos para Testar

### 1. Login como Admin
```
http://doarfazbem.test/login
Email: admin@doarfazbem.test
Senha: Admin@2025
```

### 2. Criar Campanha (como João)
```
Login: joao.silva@email.com
Senha: Joao@2025
CPF para subconta: 123.456.789-01
Telefone: 11976543210
```

### 3. Fazer Doação (como Maria)
```
Login: maria.santos@email.com
Senha: Maria@2025
Valor: R$ 100,00
Cartão teste: 5162306219378829
CVV: 318
Validade: 12/2030
```

---

## ⚠️ Reativar reCAPTCHA para Produção

Quando for para produção, edite `app/Config/Google.php`:

```php
// Restaurar as chaves originais
public string $recaptchaSiteKey = '6LfuhNwrAAAAAGTlJPOIOMzintlAQaua-p8PBV5z';
public string $recaptchaSecretKey = '6LfuhNwrAAAAAJcWyRB85nPg8PDhFbyFtJq4m2E-';
public float $recaptchaScoreThreshold = 0.5;
```

---

**Data de criação:** 07/10/2025
**Status:** ✅ Todos os usuários ativos e prontos para uso
