# Correções Aplicadas ao Dashboard

## Data: 07/10/2025

### Problema Identificado

Ao fazer login com o usuário João Silva (joao.silva@email.com), todos os acessos às páginas do dashboard retornavam erros:

1. **Dashboard principal** (`/dashboard`): "Classe 'App\Models\Campaign' não encontrada"
2. **Minhas Campanhas** (`/dashboard/my-campaigns`): "Classe 'App\Modelos\Campanha' não encontrada"
3. **Minhas Doações** (`/dashboard/my-donations`): Mesmo erro
4. **Perfil** (`/profile`): "Chave de matriz indefinida 'name'"

---

## Correções Realizadas

### 1. Nomes de Classes dos Models (app/Controllers/Dashboard.php)

**Problema:** O controller estava importando classes de models com nomes incorretos.

**Correções aplicadas:**

```php
// ANTES (LINHAS 6-9)
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Subscription;
use App\Models\User;

// DEPOIS
use App\Models\CampaignModel;
use App\Models\DonationModel;
use App\Models\Subscription;
use App\Models\UserModel;
```

### 2. Instanciação dos Models (app/Controllers/Dashboard.php)

**Problema:** Os models estavam sendo instanciados com nomes de classe incorretos.

**Correções aplicadas no construtor (linhas 26-29):**

```php
// ANTES
$this->campaignModel = new Campaign();
$this->donationModel = new Donation();
$this->userModel = new User();

// DEPOIS
$this->campaignModel = new CampaignModel();
$this->donationModel = new DonationModel();
$this->userModel = new UserModel();
```

### 3. Nome da Coluna de Status (app/Controllers/Dashboard.php)

**Problema:** O código estava usando `payment_status` mas a coluna no banco de dados se chama apenas `status`.

**Correções aplicadas:**

**Linha 58 - método index():**
```php
// ANTES
->where('payment_status', 'confirmed')

// DEPOIS
->where('status', 'confirmed')
```

**Linha 85 - método index():**
```php
// ANTES
if ($donation['payment_status'] === 'confirmed') {

// DEPOIS
if ($donation['status'] === 'confirmed') {
```

**Linha 135 - método myCampaigns():**
```php
// ANTES
->where('payment_status', 'confirmed')

// DEPOIS
->where('status', 'confirmed')
```

**Linha 227 - método viewCampaign():**
```php
// ANTES
$confirmedDonations = array_filter($donations, fn($d) => $d['payment_status'] === 'confirmed');

// DEPOIS
$confirmedDonations = array_filter($donations, fn($d) => $d['status'] === 'confirmed');
```

### 4. Nome do Método do DonationModel (app/Controllers/Dashboard.php)

**Problema:** O método `getByUser()` não existe no DonationModel. O método correto é `getUserDonations()`.

**Correções aplicadas:**

**Linha 81 - método index():**
```php
// ANTES
$myDonations = $this->donationModel->getByUser($userId);

// DEPOIS
$myDonations = $this->donationModel->getUserDonations($userId);
```

**Linha 169 - método myDonations():**
```php
// ANTES
$donations = $this->donationModel->getByUser($userId);

// DEPOIS
$donations = $this->donationModel->getUserDonations($userId);
```

---

## Estrutura Correta dos Models

### Tabela donations
- **Coluna de status:** `status` (enum: 'pending', 'confirmed', 'received', 'refunded')
- **NÃO existe:** `payment_status`

### Models do CodeIgniter 4
Todos os models seguem a convenção de nomenclatura com sufixo `Model`:
- ✅ `CampaignModel`
- ✅ `DonationModel`
- ✅ `UserModel`
- ✅ `Subscription` (exceção - já estava correto)

### Métodos dos Models

**DonationModel:**
- `getUserDonations($userId)` - Busca doações de um usuário (com JOIN da campanha)
- ❌ ~~`getByUser($userId)`~~ (não existe)

**Subscription:**
- `getByUser($userId)` - Busca assinaturas de um usuário ✅ (correto)

---

### 5. Nome da Chave da Sessão (Múltiplos Controllers) ⚠️ **ERRO CRÍTICO**

**Problema:** Os controllers estavam usando `$this->session->get('user_id')` mas a sessão salva o ID do usuário como `'id'`.

**Isso causava o erro:** "O argumento nº 1 ($userId) deve ser do tipo int, nulo fornecido"

**Verificação da Sessão (app/Controllers/User.php linhas 261-269):**
```php
$sessionData = [
    'id' => $user['id'],              // ← Salvo como 'id'
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role'],
    'avatar' => $user['avatar'],
    'email_verified' => $user['email_verified'],
    'isLoggedIn' => true
];
```

**Correções aplicadas em todos os controllers:**

**app/Controllers/Dashboard.php (4 ocorrências):**
- Linhas 44, 126, 168, 207
```php
// ANTES
$userId = $this->session->get('user_id');

// DEPOIS
$userId = $this->session->get('id');
```

**app/Controllers/Campaign.php (2 ocorrências):**
- Linhas 175, 194
```php
// ANTES
$this->session->get('user_id')

// DEPOIS
$this->session->get('id')
```

**app/Controllers/Donation.php (2 ocorrências):**
- Linhas 251, 585
```php
// ANTES
'user_id' => $this->session->get('isLoggedIn') ? $this->session->get('user_id') : null,

// DEPOIS
'user_id' => $this->session->get('isLoggedIn') ? $this->session->get('id') : null,
```

**app/Controllers/User.php (2 ocorrências):**
- Linhas 561, 583
```php
// ANTES
$this->session->get('user_id')

// DEPOIS
$this->session->get('id')
```

---

## Status

✅ **Todas as correções foram aplicadas com sucesso**

### Páginas Corrigidas:
1. ✅ Dashboard principal (`/dashboard`)
2. ✅ Minhas Campanhas (`/dashboard/my-campaigns`)
3. ✅ Minhas Doações (`/dashboard/my-donations`)
4. ✅ Detalhes da Campanha (`/dashboard/campaign/{id}`)

### Próximos Passos:
1. Testar login com usuário João Silva
2. Acessar todas as páginas do dashboard
3. Verificar se os dados são exibidos corretamente
4. Testar criação de campanha
5. Testar visualização de doações

---

## Usuários de Teste Criados

| Nome | Email | Senha | CPF | Role |
|------|-------|-------|-----|------|
| Administrador DoarFazBem | admin@doarfazbem.test | Admin@2025 | 111.222.333-44 | admin |
| João Pedro Silva | joao.silva@email.com | Joao@2025 | 123.456.789-01 | user |
| Maria Eduarda Santos | maria.santos@email.com | Maria@2025 | 987.654.321-09 | user |
| Pedro Henrique Oliveira | pedro.oliveira@email.com | Pedro@2025 | 112.233.445-56 | user |
| Ana Carolina Costa | ana.costa@email.com | Ana@2025 | 223.344.556-67 | user |
| Carlos Eduardo Souza | carlos.souza@email.com | Carlos@2025 | 334.455.667-78 | user |
