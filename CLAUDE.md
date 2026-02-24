# CLAUDE.md - DoarFazBem (doarfazbem)

> **Porta Dev:** 3008
> **Domínio Local:** http://doarfazbem.ai

---

## REGRAS DE DESENVOLVIMENTO - IMPORTANTE

### Desenvolva TUDO Completo
Ao implementar funcionalidade, **SEMPRE** crie TODOS os componentes:
- Rotas, Controllers, Models, Migrations, Views, Validações, Types
- Teste cada parte após implementar
- Corrija erros ANTES de continuar

### Quando Pedir ao Usuário
**APENAS** para: decisões de negócio, credenciais, config manual externa, ambiguidade

---

## Planejamento Detalhado
- **Documento principal:** `C:\laragon\www\docs.hubflow\DOARFAZBEM.md`
- **Produto derivado:** RifaFlow

---

## Sobre o Projeto
- **Nome:** DoarFazBem
- **Domínio:** doarfazbem.com.br
- **URL Local:** http://doarfazbem.ai
- **Status:** Em Produção (80%)
- **Nota:** Este projeto serve de base para RifaFlow

---

## Arquitetura de Rotas

```
/                   → Landing page
/login              → Login
/register           → Registro
/dashboard          → Dashboard principal
/dashboard/campanhas → Minhas campanhas
/dashboard/criar    → Criar campanha
/dashboard/doacoes  → Doações recebidas
/dashboard/relatorios → Relatórios
/dashboard/configuracoes → Configurações
/c/[slug]           → Página pública da campanha
/c/[slug]/doar      → Checkout de doação
/api/*              → API Routes (CodeIgniter)
/webhooks/*         → Webhooks Asaas
```

---

## Stack Técnica

```yaml
Framework:
  - CodeIgniter 4.5+
  - PHP 8.2+

Frontend:
  - Blade templates
  - Tailwind CSS 3
  - Alpine.js

Pagamentos:
  - Asaas (PIX, Boleto, Cartão)

Email:
  - SMTP (via Asaas ou Mailtrap)

Auth:
  - Session-based
  - Google OAuth

Database:
  - MySQL 8
  - Host: localhost
  - Porta: 3306
  - Usuário: root
  - Senha: (vazia)
  - Database: doarfazbem
```

---

## Estrutura do Projeto (CodeIgniter 4)

```
doarfazbem/
├── app/
│   ├── Controllers/
│   │   ├── Home.php            # Página inicial
│   │   ├── Auth/               # Login, registro
│   │   ├── Campaigns.php       # Campanhas
│   │   ├── Donations.php       # Doações
│   │   ├── Webhooks.php        # Webhooks Asaas
│   │   └── Admin/              # Área admin
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── CampaignModel.php
│   │   ├── DonationModel.php
│   │   └── PaymentModel.php
│   ├── Libraries/
│   │   ├── AsaasService.php
│   │   └── ReceiptGenerator.php
│   └── Views/
│       ├── campaigns/
│       ├── donations/
│       └── admin/
├── public/
│   └── assets/
└── .env
```

---

## Tabelas Principais (MySQL)

```sql
users               -- Administradores
donors              -- Doadores
campaigns           -- Campanhas
donations           -- Doações
payments            -- Pagamentos Asaas
receipts            -- Recibos gerados
```

---

## Funcionalidades
1. **Campanhas** - Criar, editar, publicar
2. **Doações** - PIX, boleto, cartão
3. **Recibos** - Geração automática
4. **Transparência** - Relatórios públicos
5. **Multi-organizador** - Várias ONGs

---

## Sistema de Rifas/Sorteios (90% pronto)

### Funcionalidades existentes:
- [x] Criação de rifas/sorteios
- [x] Pacotes com desconto progressivo
- [x] Checkout Mercado Pago (PIX)
- [x] QR Code e Copia/Cola PIX
- [x] Prêmios instantâneos
- [x] Ranking de maiores compradores
- [x] Dashboard "Meus Números"
- [x] Verificador de resultado

### Modelo de cotas:
| Quantidade | Preço | Desconto |
|------------|-------|----------|
| 1 cota | R$ 2,00 | - |
| 5 cotas | R$ 9,00 | 10% |
| 10 cotas | R$ 15,00 | 25% |
| 25 cotas | R$ 30,00 | 40% |
| 100 cotas | R$ 90,00 | 55% |

---

## Produto Derivado

### RifaFlow (rifa.hubflow)
- Plataforma de rifas digitais
- 90% do código vem deste projeto
- Foco: ONGs, igrejas, escolas, comércio

---

## Comandos Úteis

```bash
# Rodar em desenvolvimento
php spark serve

# Migrations
php spark migrate
php spark migrate:rollback

# Seeds
php spark db:seed

# Cache
php spark cache:clear
```

---

## Padrões do Ecossistema

> **Documento completo:** `C:\laragon\www\docs.hubflow\STANDARDS.md`

### Programa de Afiliados
| Tipo | Comissão |
|------|----------|
| Mensal/Anual | 10% recorrente |

> **Nota:** DoarFazBem não oferece plano lifetime devido a taxas de gateway por transação.

### Usuário Coringa
- SuperAdmin pode marcar usuários como "coringa"
- Acesso configurável por plano/features
- Expiração configurável

### Credenciais SuperAdmin
```yaml
Email: cesar@hubflowai.com
Senha: @GAd8EDSS5Ypn4er@
```

---

## Idioma
- **SEMPRE responda em português do Brasil (pt-BR)**

---

**Última atualização:** 29/01/2026
