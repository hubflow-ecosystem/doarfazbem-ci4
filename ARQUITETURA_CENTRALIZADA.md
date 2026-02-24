# 🏗️ Arquitetura Centralizada - DoarFazBem

## ❌ Problema Identificado

**Sintoma:** Valores inconsistentes em diferentes partes do sistema
- Admin mostra 4 doadores
- Página da campanha mostra 2 doadores
- Banco tem 5 doações
- Valor real: **1 doador único** (Dr. Felipe que doou 2x)

**Causa Raiz:** Múltiplos métodos calculando as mesmas estatísticas de formas diferentes

### Locais Problemáticos ANTES

```php
// 1. Campaign::show() - linha 91-93
$campaign['percentage'] = $this->campaignModel->getPercentage($campaign['id']);
$campaign['days_left'] = max(0, (strtotime($campaign['end_date']) - time()) / 86400);
$campaign['donors_count'] = $donationModel->getUniqueDonorsByCampaign($campaign['id']);

// 2. Donation::getUniqueDonorsByCampaign() - linha 265-269
return $this->distinct()
    ->select('donor_email')
    ->where('campaign_id', $campaignId)
    ->where('status', 'received')
    ->countAllResults();
// ❌ Só conta por email, ignora user_id

// 3. Campaign::updateDonationStats() - linha 194-208
$newAmount = $campaign['current_amount'] + $amount;
$newDonorsCount = $campaign['donors_count'] + 1;
// ❌ Incrementa sem verificar se é doador repetido

// 4. sync-campaign-amounts.php
COALESCE(SUM(d.net_amount), 0) as total_donated
// ❌ Usava net_amount ao invés de amount
```

---

## ✅ Solução: Método Único Centralizado

### 1. Método Principal: `getCampaignWithStats($campaignId)`

**Localização:** `app/Models/CampaignModel.php:322-367`

**Responsabilidade:** Buscar campanha com TODAS as estatísticas calculadas em tempo real

**Retorna:**
```php
[
    'id' => 103,
    'title' => 'Reforma de Creche',
    'current_amount' => 1376.00,        // Soma de amount (bruto)
    'donors_count' => 1,                // Doadores únicos
    'total_donations' => 5,             // Total de doações
    'percentage' => 4.59,               // % da meta
    'days_left' => 120,                 // Dias restantes
    // ... outros campos da campanha
]
```

**Lógica SQL:**
```sql
SELECT
    COUNT(*) as total_donations,
    SUM(amount) as total_amount,
    COUNT(DISTINCT
        CASE
            WHEN user_id IS NOT NULL THEN CONCAT('user_', user_id)
            WHEN donor_email IS NOT NULL AND donor_email != '' THEN CONCAT('email_', donor_email)
            ELSE NULL  -- Anônimos sem ID não contam
        END
    ) as unique_donors
FROM donations
WHERE campaign_id = ? AND status = 'received'
```

### 2. Método Auxiliar: `recalculateStats($campaignId)`

**Localização:** `app/Models/CampaignModel.php:289-316`

**Responsabilidade:** Atualizar campos `current_amount` e `donors_count` na tabela

**Quando usar:**
- Após importação/migração de dados
- Em comando de manutenção (cron)
- Ao detectar inconsistências

---

## 📊 Regra de Contagem de Doadores

### Definição

**Doador Único = Pessoa física identificável**

### Lógica

```
Se donation.user_id existe:
    Identificador = "user_{user_id}"
Senão se donation.donor_email existe e não é vazio:
    Identificador = "email_{donor_email}"
Senão:
    Identificador = NULL (não conta)

Doadores Únicos = COUNT(DISTINCT Identificador WHERE Identificador IS NOT NULL)
```

### Exemplo (Campanha #103)

| Doação | user_id | donor_email | Identificador | Conta? |
|--------|---------|-------------|---------------|--------|
| #215 | 216 | user1@test... | user_216 | ✅ |
| #222 | 216 | user1@test... | user_216 | ✅ (mesmo) |
| #214 | NULL | NULL | NULL | ❌ |
| #228 | NULL | NULL | NULL | ❌ |
| #227 | NULL | NULL | NULL | ❌ |

**Resultado:** 1 doador único (user_216 aparece 2x mas conta 1)

---

## 🔄 Fluxo de Uso

### Controllers DEVEM usar

```php
// ✅ CORRETO - Método centralizado
$campaign = $this->campaignModel->getCampaignWithStats($campaignId);
```

```php
// ❌ ERRADO - Calcular manualmente
$campaign = $this->campaignModel->find($campaignId);
$campaign['percentage'] = ...;
$campaign['donors_count'] = ...;
```

### Views recebem

```php
// Tudo calculado, apenas exibir
<?= number_format($campaign['current_amount'], 2, ',', '.') ?>
<?= $campaign['donors_count'] ?> Doadores
<?= number_format($campaign['percentage'], 1) ?>% da meta
```

---

## 🎯 Controllers Atualizados

### Campaign::show()

**ANTES:**
```php
$campaign = $this->campaignModel->getCampaignBySlug($slug);
$campaign['percentage'] = $this->campaignModel->getPercentage($campaign['id']);
$campaign['days_left'] = max(0, (strtotime($campaign['end_date']) - time()) / 86400);
$campaign['donors_count'] = $donationModel->getUniqueDonorsByCampaign($campaign['id']);
```

**DEPOIS:**
```php
$campaignBasic = $this->campaignModel->getCampaignBySlug($slug);
$campaign = $this->campaignModel->getCampaignWithStats($campaignBasic['id']);
// Tudo calculado em 1 chamada centralizada
```

---

## 📋 Checklist para Novos Desenvolvedores

### ✅ Sempre Use

- `getCampaignWithStats($id)` para exibir dados de campanha
- `recalculateStats($id)` para sincronizar banco após alterações massivas

### ❌ Nunca Faça

- Calcular percentage manualmente em controllers
- Contar doadores com queries separadas
- Usar `current_amount` do banco sem recalcular (pode estar desatualizado)
- Incrementar `donors_count` diretamente

### 🔍 Como Verificar Consistência

```bash
# Testar lógica centralizada
php test-centralized-simple.php

# Recalcular todas as campanhas
php sync-campaign-amounts.php
```

---

## 🐛 Debugging

### Valor divergente na view?

1. Limpe cache: `php spark cache:clear`
2. Verifique se controller usa `getCampaignWithStats()`
3. Execute `test-centralized-simple.php` para ver SQL real

### Contagem de doadores errada?

1. Verifique doações anônimas: `SELECT * FROM donations WHERE user_id IS NULL AND donor_email IS NULL`
2. Doações sem identificação **não contam** como doadores únicos
3. Use `recalculateStats($id)` para forçar recálculo

### Valor bruto vs líquido?

- **Bruto (`amount`)**: O que o doador pagou
- **Líquido (`net_amount`)**: Após taxas gateway/plataforma
- **SEMPRE mostrar bruto** para o público

---

## 📦 Resumo Executivo

### Princípios

1. **Single Source of Truth**: Um único método calcula todas as estatísticas
2. **Tempo Real**: Sempre consulta tabela `donations`, não confia em campos cache
3. **Consistência**: Mesma query SQL em todos os locais
4. **Transparência**: Mostra valor bruto doado, não líquido

### Benefícios

- ✅ Valores sempre consistentes
- ✅ Fácil manutenção (alterar em 1 lugar)
- ✅ Menos bugs por divergência
- ✅ Performance (1 query vs múltiplas)

### Arquivos Modificados

```
app/Models/CampaignModel.php
├── getCampaignWithStats()    [NOVO - linha 322]
└── recalculateStats()         [NOVO - linha 289]

app/Controllers/Campaign.php
└── show()                     [MODIFICADO - usa método centralizado]

sync-campaign-amounts.php
└── [MODIFICADO - usa amount ao invés de net_amount]
```

---

**Autor:** Claude Code
**Data:** 2025-11-15
**Status:** ✅ Implementado e Testado
