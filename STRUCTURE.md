# 📁 Estrutura do Projeto DoarFazBem

## Estrutura Limpa e Organizada

```
doarfazbem/
├── app/                          # Aplicação principal (CodeIgniter 4)
│   ├── Config/                   # Configurações
│   │   ├── App.php
│   │   ├── Database.php
│   │   ├── Routes.php
│   │   ├── Asaas.php
│   │   └── Google.php
│   ├── Controllers/              # Controllers MVC
│   │   ├── Home.php
│   │   ├── Campaign.php
│   │   ├── Donation.php
│   │   ├── User.php
│   │   ├── Dashboard.php
│   │   ├── Webhook.php
│   │   └── Admin.php
│   ├── Models/                   # Models
│   │   ├── UserModel.php
│   │   ├── CampaignModel.php
│   │   ├── DonationModel.php
│   │   ├── Subscription.php
│   │   └── AsaasAccount.php
│   ├── Views/                    # Views (HTML + Alpine.js)
│   │   ├── layout/
│   │   ├── home/
│   │   ├── campaigns/
│   │   ├── donations/
│   │   ├── dashboard/
│   │   ├── user/
│   │   ├── admin/
│   │   └── emails/
│   ├── Libraries/                # Bibliotecas customizadas
│   │   └── AsaasLibrary.php
│   ├── Helpers/                  # Helper functions
│   └── Database/
│       └── Migrations/           # Migrations do banco
│
├── public/                       # Pasta pública (root do servidor)
│   ├── index.php                 # Entry point
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/
│   │   │   ├── input.css         # Input Tailwind
│   │   │   └── output.css        # Output compilado
│   │   ├── js/
│   │   │   └── app.js
│   │   └── images/
│   └── uploads/                  # Uploads de usuários
│       ├── campaigns/
│       └── profiles/
│
├── writable/                     # Cache, logs, sessions
│   ├── cache/
│   ├── logs/
│   └── session/
│
├── docs/                         # 📚 Documentação
│   ├── CORRECOES_DASHBOARD.md    # Log de correções
│   ├── CREDENCIAIS_ACESSO.md     # Usuários de teste
│   ├── DoarFazBem_Especificacoes_Completas.md
│   ├── SECURITY.md               # Segurança
│   ├── TAXAS_ASAAS_OFICIAL_2025.md
│   ├── TESTAR_GOOGLE_OAUTH.md
│   ├── wireframe.html
│   └── WORDPRESS_INTEGRATION.md
│
├── vendor/                       # Dependências Composer (ignorado)
├── node_modules/                 # Dependências NPM (ignorado)
├── system/                       # Core do CodeIgniter (não mexer)
│
├── .env                          # Variáveis de ambiente (NÃO commitar)
├── env                           # Template do .env
├── .gitignore                    # Arquivos ignorados pelo Git
├── composer.json                 # Dependências PHP
├── package.json                  # Dependências Node.js
├── tailwind.config.js            # Config do Tailwind CSS
├── spark                         # CLI do CodeIgniter
├── README.md                     # Documentação principal
├── LICENSE                       # Licença MIT
└── STRUCTURE.md                  # Este arquivo

```

## 📋 Arquivos Principais

### Raiz do Projeto

| Arquivo | Descrição |
|---------|-----------|
| `README.md` | Documentação completa do projeto |
| `STRUCTURE.md` | Estrutura de pastas (este arquivo) |
| `.env` | Variáveis de ambiente (NÃO commitar) |
| `env` | Template do .env para novos desenvolvedores |
| `.gitignore` | Arquivos ignorados pelo Git |
| `composer.json` | Dependências PHP (CodeIgniter, etc) |
| `package.json` | Dependências Node.js (Tailwind, Alpine, etc) |
| `tailwind.config.js` | Configuração do Tailwind CSS |
| `spark` | CLI do CodeIgniter (php spark migrate, etc) |
| `LICENSE` | Licença MIT |

### Pasta `docs/`

Toda documentação técnica foi movida para a pasta `docs/` para organização:

| Arquivo | Descrição |
|---------|-----------|
| `DoarFazBem_Especificacoes_Completas.md` | Especificações completas do projeto |
| `CORRECOES_DASHBOARD.md` | Log de correções recentes |
| `CREDENCIAIS_ACESSO.md` | Usuários de teste criados |
| `SECURITY.md` | Políticas de segurança |
| `TAXAS_ASAAS_OFICIAL_2025.md` | Taxas do Asaas |
| `TESTAR_GOOGLE_OAUTH.md` | Guia de testes OAuth |
| `WORDPRESS_INTEGRATION.md` | Integração com WordPress |
| `wireframe.html` | Wireframe visual do projeto |

## 🗑️ Arquivos Removidos

Os seguintes arquivos foram **removidos** por serem duplicados, desatualizados ou desnecessários:

- ❌ `ASAAS_CUSTOS_E_TAXAS.md` (duplicado)
- ❌ `ASAAS_INTEGRACAO_GUIA_COMPLETO.md` (desatualizado)
- ❌ `CHANGELOG.md` (não mantido)
- ❌ `CONFIGURAR_EMAIL_SMTP.md` (info está no README)
- ❌ `CORRECOES_APLICADAS.md` (duplicado)
- ❌ `criar_usuarios.php` (script temporário)
- ❌ `criar_usuarios.sql` (script temporário)
- ❌ `database_schema.sql` (usar migrations)
- ❌ `DOADOR_PAGA_TAXAS_IMPLEMENTACAO.md` (já implementado)
- ❌ `doarfazbem-f0015146da01.json` (arquivo temporário)
- ❌ `GOOGLE_APIS_IMPLEMENTACAO.md` (desatualizado)
- ❌ `GUIA_DE_TESTES.md` (redundante)
- ❌ `IMPLEMENTACOES_RECENTES.md` (desatualizado)
- ❌ `INICIO_RAPIDO.md` (info está no README)
- ❌ `INSTALL_CPANEL.md` (não usando cPanel)
- ❌ `INTEGRACAO_APIS_GOOGLE.md` (duplicado)
- ❌ `limpar_projeto.bat` (não necessário)
- ❌ `MVP_COMPLETO.md` (status está no README)
- ❌ `nul` (arquivo vazio)
- ❌ `PROMPT_DOAR_FAZ_BEM_COMPLETO.md` (muito grande)
- ❌ `PROXIMAS_IMPLEMENTACOES.md` (roadmap está no README)
- ❌ `RESUMO_SESSAO_ATUAL.md` (temporário)
- ❌ `SERVIDOR_HOSPEDAGEM.md` (info está no README)
- ❌ `setup.bat` (não necessário)
- ❌ `STATUS_DESENVOLVIMENTO.md` (info está no README)
- ❌ `changelogs/` (pasta vazia)

## 🎯 Convenções

### Nomenclatura de Arquivos

- **Configuração**: Arquivos na raiz (`.env`, `composer.json`, etc)
- **Documentação**: Pasta `docs/` com CAPS + underscores
- **Código**: Pasta `app/` com PascalCase (Controllers, Models)
- **Views**: Pasta `app/Views/` com snake_case

### Estrutura de Commits

Seguir [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: Adiciona nova funcionalidade
fix: Corrige bug
docs: Atualiza documentação
style: Formatação de código
refactor: Refatoração
test: Adiciona testes
chore: Tarefas auxiliares
```

## 📚 Documentação

**Principal**: Leia o [README.md](../README.md) para:
- Instruções de instalação
- Configuração completa
- Tecnologias utilizadas
- Roadmap do projeto

**Técnica**: Consulte a pasta [docs/](../docs/) para:
- Especificações detalhadas
- Guias de integração
- Logs de correções
- Credenciais de teste

## ✅ Status

- ✅ Projeto organizado
- ✅ Arquivos desnecessários removidos
- ✅ Documentação centralizada na pasta `docs/`
- ✅ `.gitignore` atualizado
- ✅ Estrutura limpa e profissional

---

**Última atualização**: 10/10/2025
