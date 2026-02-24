# 🧪 Sistema de Testes Automatizados - DoarFazBem

**Versão:** 1.0
**Data:** 14/11/2025
**Status:** ✅ Sistema Completo de Testes Implementado

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Seeders de Dados](#seeders-de-dados)
3. [Scripts de Simulação](#scripts-de-simulação)
4. [Testes Automatizados](#testes-automatizados)
5. [Comandos Rápidos](#comandos-rápidos)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

Este sistema fornece uma suíte completa de ferramentas para teste do DoarFazBem, incluindo:

- ✅ **Seeders**: Popula banco de dados com dados realistas
- ✅ **Scripts de Simulação**: Simula ações de usuários
- ⏳ **Testes PHPUnit**: Testes automatizados (em desenvolvimento)
- ⏳ **Comandos Spark**: Comandos CLI personalizados (em desenvolvimento)

---

## 🌱 Seeders de Dados

### **1. TestUsersSeeder**

Cria 24 usuários de teste com diferentes perfis.

**Uso:**
```bash
php spark db:seed TestUsersSeeder
```

**Usuários Criados:**

| Email | Senha | Papel | Descrição |
|-------|-------|-------|-----------|
| `admin@test.doarfazbem.local` | `admin123` | Admin | Administrador do sistema |
| `criador@test.doarfazbem.local` | `teste123` | User | Criador de campanhas |
| `doadora@test.doarfazbem.local` | `teste123` | User | Doador VIP |
| `user1-20@test.doarfazbem.local` | `teste123` | User | 20 usuários aleatórios |
| `google@test.doarfazbem.local` | - | User | Usuário via Google OAuth |
| `nao-verificado@test.doarfazbem.local` | `teste123` | User | Email não verificado |

**Recursos:**
- Gera CPFs válidos automaticamente
- Usa Faker para dados realistas
- 80% dos usuários com email verificado
- Inclui usuários especiais para testes específicos

---

### **2. TestCampaignsSeeder**

Cria 10 campanhas de teste em diferentes categorias e status.

**Uso:**
```bash
php spark db:seed TestCampaignsSeeder
```

**IMPORTANTE:** Execute `TestUsersSeeder` primeiro!

**Campanhas Criadas:**

#### **Campanhas Médicas (0% taxa):**
1. Tratamento de Câncer - R$ 50.000 (30% concluído)
2. Cirurgia Cardíaca Urgente - R$ 80.000 (52% concluído)
3. Fisioterapia Pós-AVC - R$ 25.000 (100% concluída)
4. Nova Campanha - Sem Doações - R$ 35.000 (0%)

#### **Campanhas Sociais (1% taxa):**
5. Reforma de Creche Comunitária - R$ 30.000 (61%)
6. Doação de Cestas Básicas - R$ 15.000 (54%)
7. Projeto Educação Digital - R$ 45.000 (26%)

#### **Outras:**
8. Família vítima de incêndio (Emergência) - R$ 20.000 (27%)
9. Campanha Pausada - Teste (Pausada)
10. Campanha Cancelada - Teste (Cancelada)

**Status Disponíveis:**
- ✅ `active` - 7 campanhas
- 🎉 `completed` - 1 campanha
- ⏸️ `paused` - 1 campanha
- ❌ `cancelled` - 1 campanha

---

### **3. FullTestSeeder**

Executa TODOS os seeders em sequência.

**Uso:**
```bash
php spark db:seed FullTestSeeder
```

**O que faz:**
1. Cria 24 usuários de teste
2. Cria 10 campanhas de teste
3. (Em breve) Cria doações de teste

**Saída:**
```
╔════════════════════════════════════════════════════════════╗
║           🧪 DOARFAZBEM - TEST DATA SEEDER 🧪              ║
╚════════════════════════════════════════════════════════════╝

ETAPA 1/2: Criando Usuários de Teste
✅ Admin criado: admin@test.doarfazbem.local (senha: admin123)
✅ Criador de campanhas: criador@test.doarfazbem.local
...

ETAPA 2/2: Criando Campanhas de Teste
✅ [TESTE] Tratamento de Câncer - Maria Silva
   Meta: R$ 50.000,00 | Arrecadado: R$ 15.000,00 (30%)
...

✨ CONCLUÍDO ✨
⏱️  Tempo de execução: 2.34 segundos
```

---

## 🎬 Scripts de Simulação

### **1. simulate-donations.php**

Simula doações realistas em campanhas de teste.

**Uso:**
```bash
php tests/scripts/simulate-donations.php
```

**Pré-requisitos:**
- Seeders de usuários e campanhas executados

**O que faz:**
- Cria 20 doações aleatórias
- Distribui entre PIX (50%), Boleto (30%), Cartão (20%)
- 85% aprovadas, 10% pendentes, 5% canceladas
- 20% das doações são anônimas
- 30% incluem contribuição extra
- Atualiza `current_amount` das campanhas
- Calcula taxas corretamente (0% médicas, 1% outras)

**Saída:**
```
🎯 Criando 20 doações de teste...

💳 ✅ Doação #1: R$ 125 via pix
   👤 Maria Santos Doadora → 🎯 [TESTE] Tratamento de Câncer
   ➕ Extra: R$ 5

🧾 ⏳ Doação #2: R$ 80 via boleto (Anônima)
   👤 João Silva Criador → 🎯 [TESTE] Reforma de Creche
   💵 Taxa plataforma: R$ 0,80
...

✨ Total: 20 doações criadas com sucesso!
```

---

## 🧪 Testes Automatizados (PHPUnit)

### **Em Desenvolvimento**

Os seguintes testes serão implementados:

#### **Unit Tests** (Testes Unitários)
```
tests/Unit/
├── UserModelTest.php          - Testa modelo de usuários
├── CampaignModelTest.php      - Testa modelo de campanhas
├── DonationModelTest.php      - Testa modelo de doações
├── AsaasServiceTest.php       - Testa integração Asaas
└── FirebaseServiceTest.php    - Testa notificações Firebase
```

#### **Integration Tests** (Testes de Integração)
```
tests/Integration/
├── DonationFlowTest.php       - Testa fluxo completo de doação
├── PaymentWebhookTest.php     - Testa webhooks Asaas
├── GoogleOAuthTest.php        - Testa login com Google
└── EmailServiceTest.php       - Testa envio de emails
```

#### **Feature Tests** (Testes de Funcionalidades)
```
tests/Feature/
├── CreateCampaignTest.php     - Testa criação de campanhas
├── MakeDonationTest.php       - Testa realizar doação
├── UserDashboardTest.php      - Testa dashboard do usuário
└── AdminPanelTest.php         - Testa painel admin
```

**Executar testes:**
```bash
# Todos os testes
php spark test

# Apenas testes unitários
php spark test --group unit

# Apenas testes de integração
php spark test --group integration

# Teste específico
php spark test tests/Unit/UserModelTest.php
```

---

## ⚡ Comandos Rápidos

### **Setup Completo (Primeira Vez)**

```bash
# 1. Executar todas as migrations
php spark migrate

# 2. Criar dados de teste
php spark db:seed FullTestSeeder

# 3. Simular doações
php tests/scripts/simulate-donations.php

# 4. Acessar sistema
# URL: https://doarfazbem.ai/login
# User: admin@test.doarfazbem.local
# Pass: admin123
```

### **Limpar e Recriar Dados**

```bash
# Opção 1: Limpar TUDO e recriar
php spark migrate:refresh
php spark db:seed FullTestSeeder
php tests/scripts/simulate-donations.php

# Opção 2: Apenas recriar dados de teste
php spark db:seed FullTestSeeder --force
```

### **Comandos Individuais**

```bash
# Apenas usuários
php spark db:seed TestUsersSeeder

# Apenas campanhas
php spark db:seed TestCampaignsSeeder

# Apenas doações
php tests/scripts/simulate-donations.php
```

---

## 🔧 Troubleshooting

### **Erro: "Nenhum usuário de teste encontrado"**

**Causa:** Tentou criar campanhas/doações sem usuários

**Solução:**
```bash
php spark db:seed TestUsersSeeder
```

### **Erro: "Class 'Faker\Factory' not found"**

**Causa:** Biblioteca Faker não instalada

**Solução:**
```bash
composer require fakerphp/faker --dev
```

### **Erro: "Duplicate entry for key 'email'"**

**Causa:** Usuários já existem no banco

**Solução:**
```bash
# Opção 1: Executar seeder com force (recriar)
php spark db:seed TestUsersSeeder --force

# Opção 2: Limpar manualmente
DELETE FROM users WHERE email LIKE '%@test.doarfazbem.local';
```

### **Campanhas não aparecem no site**

**Verificar:**
1. Status está como `active`?
2. `start_date` é anterior a hoje?
3. `end_date` é posterior a hoje?

**SQL Debug:**
```sql
SELECT id, title, status, start_date, end_date
FROM campaigns
WHERE title LIKE '%[TESTE]%';
```

### **Doações não atualizam current_amount**

**Causa:** Doação não está com status `approved`

**Solução:**
Apenas doações aprovadas incrementam o valor arrecadado.

```sql
UPDATE donations SET status = 'approved' WHERE id = X;

-- Recalcular manualmente
UPDATE campaigns
SET current_amount = (
    SELECT COALESCE(SUM(total_amount), 0)
    FROM donations
    WHERE campaign_id = campaigns.id AND status = 'approved'
);
```

---

## 📊 Estatísticas

### **Dados Criados por Seeder Completo:**

| Tipo | Quantidade | Tempo Médio |
|------|------------|-------------|
| Usuários | 24 | 0.5s |
| Campanhas | 10 | 0.3s |
| Doações (script) | 20 | 2.5s |
| **TOTAL** | **54 registros** | **~3.3s** |

### **Distribuição de Doações Simuladas:**

| Método | % | Quantidade Estimada |
|--------|---|---------------------|
| PIX | 50% | ~10 doações |
| Boleto | 30% | ~6 doações |
| Cartão | 20% | ~4 doações |

| Status | % | Quantidade Estimada |
|--------|---|---------------------|
| Aprovado | 85% | ~17 doações |
| Pendente | 10% | ~2 doações |
| Cancelado | 5% | ~1 doação |

---

## 🚀 Próximos Passos

### **Em Desenvolvimento:**

1. ⏳ **TestDonationsSeeder** - Seeder de doações (alternativa ao script)
2. ⏳ **Testes PHPUnit** - Suite completa de testes automatizados
3. ⏳ **Comandos Spark** - Comandos CLI personalizados:
   ```bash
   php spark test:users          # Testar criação de usuários
   php spark test:donations      # Testar fluxo de doações
   php spark test:payments       # Testar pagamentos
   php spark test:webhooks       # Testar webhooks
   php spark test:full           # Suite completa
   php spark test:clean          # Limpar dados de teste
   ```

4. ⏳ **Teste de Carga** - Script para simular múltiplos usuários simultâneos
5. ⏳ **CI/CD Integration** - Integração com GitHub Actions
6. ⏳ **Relatórios de Teste** - Geração de relatórios HTML

---

## 📚 Referências

- [CodeIgniter 4 Testing](https://codeigniter.com/user_guide/testing/index.html)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Faker PHP](https://fakerphp.github.io/)
- [Database Seeding](https://codeigniter.com/user_guide/dbmgmt/seeds.html)

---

**Desenvolvido para DoarFazBem** 💚
**Última atualização:** 14/11/2025
