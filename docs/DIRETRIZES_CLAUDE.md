# 🤖 DIRETRIZES ATUALIZADAS - Claude para DoarFazBem

---

## 🎯 OBJETIVO PRINCIPAL

**Ser disruptivo no mercado de crowdfunding brasileiro oferecendo:**
- Taxa ZERO para campanhas médicas e sociais
- Taxa de apenas 1% para outras campanhas
- Transparência total
- Múltiplas funcionalidades (doações, rifas, apoio recorrente, projetos com recompensas)

---

## ✅ REGRAS DE ATUAÇÃO

### **1. SEMPRE CRIAR TUDO O QUE FOR NECESSÁRIO**

❌ **NÃO FAZER:**
- Pedir para o usuário criar arquivos
- Pedir para o usuário executar comandos simples
- Deixar tarefas incompletas
- Criar apenas views sem controllers
- Criar apenas controllers sem views
- Esquecer de atualizar rotas

✅ **SEMPRE FAZER:**
- Criar controllers + views + models + rotas COMPLETOS
- Criar migrations se necessário
- Criar seeds com dados de teste
- Atualizar documentação
- Limpar cache após mudanças
- Testar tudo criando dados de exemplo

---

### **2. QUANDO PEDIR PERMISSÃO/ACESSO**

**Solicitar acesso APENAS para:**
- 🔐 Acessar banco de dados externo (produção)
- 🔐 Modificar arquivos de sistema (fora do projeto)
- 🔐 Executar comandos que alterem configurações do servidor
- 🔐 Acessar APIs externas com credenciais reais
- 🔐 Fazer deploy em produção
- 🔐 Alterar DNS ou configurações de domínio

**NÃO pedir para:**
- ✅ Criar/editar arquivos do projeto
- ✅ Executar comandos no Laragon (local)
- ✅ Limpar cache
- ✅ Rodar migrations locais
- ✅ Criar seeds
- ✅ Recompilar CSS/JS

---

### **3. SEMPRE TESTAR TUDO**

**Antes de considerar uma tarefa completa:**

1. ✅ Criar dados de teste realistas
2. ✅ Criar usuários de exemplo (admin, criador, doador)
3. ✅ Criar campanhas de exemplo (todas as categorias)
4. ✅ Criar doações de exemplo (vários métodos)
5. ✅ Testar todas as rotas criadas
6. ✅ Verificar se dados aparecem corretamente
7. ✅ Testar responsividade (mobile, tablet, desktop)
8. ✅ Verificar animações Alpine.js
9. ✅ Testar todos os CTAs e links

---

### **4. ESTRUTURA DE CRIAÇÃO**

**Ao criar uma nova funcionalidade, SEMPRE fazer nesta ordem:**

```
1. Migration (se necessário)
   ↓
2. Model (com validações e relationships)
   ↓
3. Controller (com todos os métodos)
   ↓
4. Views (todas as páginas necessárias)
   ↓
5. Routes (adicionar todas as rotas)
   ↓
6. Seed (dados de teste)
   ↓
7. Documentação
   ↓
8. Limpar cache
   ↓
9. Testar tudo
```

---

### **5. PADRÕES DE CÓDIGO**

#### **Controllers:**
```php
<?php

namespace App\Controllers;

use App\Models\ExampleModel;

class ExampleController extends BaseController
{
    protected $exampleModel;

    public function __construct()
    {
        $this->exampleModel = new ExampleModel();
    }

    /**
     * Lista todos os exemplos
     * GET /examples
     */
    public function index()
    {
        $data = [
            'title' => 'Exemplos',
            'examples' => $this->exampleModel->findAll()
        ];

        return view('examples/index', $data);
    }

    // ... outros métodos
}
```

#### **Views:**
```php
<?= $this->extend('layout/app') ?>

<?= $this->section('content') ?>

<!-- Seção com Alpine.js -->
<section class="py-24 bg-gray-50" x-data="{ show: false }" x-init="setTimeout(() => show = true, 200)">
    <div class="container-custom" x-show="show" x-transition>
        <h2 class="text-5xl font-black text-gray-900 mb-6">Título</h2>
        <!-- Conteúdo -->
    </div>
</section>

<?= $this->endSection() ?>
```

#### **Models:**
```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ExampleModel extends Model
{
    protected $table = 'examples';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['name', 'description', 'status'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validações
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'description' => 'required'
    ];

    // Relationships
    public function getCampaign($id)
    {
        // ...
    }
}
```

---

### **6. SEMPRE RESPONDER EM PORTUGUÊS**

❌ **NUNCA FAZER:**
- Responder em inglês
- Usar termos técnicos em inglês sem tradução
- Criar documentação em inglês

✅ **SEMPRE FAZER:**
- Responder 100% em português do Brasil
- Traduzir termos técnicos quando possível
- Explicar de forma clara e simples
- Usar exemplos práticos

---

### **7. DADOS DE TESTE REALISTAS**

**Ao criar seeds, usar dados BRASILEIROS e REALISTAS:**

#### **Usuários:**
```php
'name' => 'João Pedro Silva',
'email' => 'joao.silva@example.com',
'phone' => '(11) 98765-4321',
'cpf' => '123.456.789-00'
```

#### **Campanhas:**
```php
'title' => 'Tratamento de Câncer para Maria',
'description' => 'Maria, 45 anos, mãe de 3 filhos...',
'goal_amount' => 50000.00,
'category' => 'medica',
'city' => 'São Paulo',
'state' => 'SP'
```

#### **Doações:**
```php
'donor_name' => 'Carlos Santos',
'donor_email' => 'carlos@example.com',
'amount' => 100.00,
'payment_method' => 'pix',
'status' => 'confirmed'
```

---

### **8. DOCUMENTAÇÃO OBRIGATÓRIA**

**Ao criar qualquer funcionalidade nova, SEMPRE criar:**

1. ✅ **README específico** da funcionalidade
2. ✅ **Documentação de rotas** (URL, método, params)
3. ✅ **Documentação de API** (se aplicável)
4. ✅ **Guia de uso** para o usuário
5. ✅ **Checklist de teste**

---

### **9. PRIORIDADES**

**Ordem de prioridade ao desenvolver:**

1. 🔴 **Funcionalidades core** (doações, campanhas)
2. 🟠 **Dashboard e analytics**
3. 🟡 **Rifas e ações entre amigos** (FASE 2)
4. 🟢 **Apoio recorrente** (FASE 3)
5. 🔵 **Projetos com recompensas** (FASE 4)
6. 🟣 **Melhorias de UX/UI**
7. ⚪ **Otimizações de performance**

---

### **10. CHECKLIST PRÉ-ENTREGA**

**Antes de considerar QUALQUER tarefa completa:**

- [ ] Controllers criados com todos os métodos
- [ ] Views criadas (todas as telas necessárias)
- [ ] Rotas adicionadas e testadas
- [ ] Models com validações
- [ ] Migrations executadas (se necessário)
- [ ] Seeds criados com dados de teste
- [ ] Cache limpo
- [ ] Documentação criada/atualizada
- [ ] Teste manual feito
- [ ] Responsividade testada
- [ ] Animações funcionando
- [ ] Links e CTAs testados
- [ ] Erros corrigidos
- [ ] Código comentado (quando necessário)
- [ ] Segurança verificada (SQL injection, XSS, etc)

---

## 🎨 PADRÕES DE DESIGN

### **Cores do Projeto:**
```css
Primária: Teal (#14B8A6)
Secundária: Esmeralda (#10B981)
Acento: Amarelo (#FCD34D)
Médica: Vermelho (#EF4444)
Social: Azul (#3B82F6)
Sucesso: Verde (#22C55E)
```

### **Componentes Alpine.js:**
- ✅ Sempre usar `x-data` para estado
- ✅ Usar `x-init` para inicialização
- ✅ Usar `x-show` + `x-transition` para animações
- ✅ Usar `x-for` para loops
- ✅ Usar `x-model` para inputs
- ✅ Usar `@click`, `@mouseenter`, etc para eventos

### **Animações Padrão:**
```javascript
// Fade in ao carregar
x-data="{ show: false }"
x-init="setTimeout(() => show = true, 200)"
x-show="show"
x-transition

// Contador animado
animateNumbers() {
    const duration = 2500;
    const steps = 60;
    const interval = duration / steps;
    // ... lógica de contagem
}

// Hover effect
@mouseenter="hoveredCard = id"
@mouseleave="hoveredCard = null"
:class="hoveredCard === id ? 'scale-110' : 'scale-100'"
```

---

## 🚀 WORKFLOWS ESPECÍFICOS

### **Workflow: Criar Nova Página**

1. Criar controller method
2. Criar view estendendo layout
3. Adicionar rota
4. Criar seed com dados de teste
5. Testar acesso à URL
6. Verificar dados na página
7. Testar responsividade
8. Documentar

### **Workflow: Adicionar Nova Seção na Home**

1. Planejar dados necessários
2. Adicionar métodos no HomeController (se necessário)
3. Adicionar seção na view com Alpine.js
4. Testar animações
5. Verificar responsividade
6. Limpar cache
7. Recarregar página

### **Workflow: Corrigir Bug**

1. Identificar causa raiz
2. Criar teste que reproduz o bug
3. Corrigir o código
4. Testar a correção
5. Verificar se não quebrou outras funcionalidades
6. Limpar cache
7. Documentar o fix

---

## 📝 TEMPLATES DE RESPOSTAS

### **Ao completar uma tarefa:**
```
✅ Tarefa Completa: [Nome da Tarefa]

O que foi feito:
1. [Item 1]
2. [Item 2]
3. [Item 3]

Arquivos criados/modificados:
- [arquivo 1]
- [arquivo 2]

Como testar:
1. Acessar http://doarfazbem.test/[url]
2. [Passo 2]
3. [Passo 3]

Resultado esperado:
- [Expectativa 1]
- [Expectativa 2]
```

### **Ao encontrar um erro:**
```
🐛 Erro Encontrado: [Descrição]

Causa:
- [Explicação da causa]

Correção aplicada:
- [O que foi feito]

Status: ✅ Corrigido

Como verificar:
- [Passos para verificar]
```

---

## 🎯 MISSÃO DO PROJETO

> "Democratizar o acesso ao crowdfunding no Brasil, cobrando taxa ZERO para quem mais precisa (médicas e sociais) e apenas 1% para os demais. Fazer a diferença na vida de milhões de brasileiros oferecendo a plataforma mais justa, transparente e completa do mercado."

---

## ✅ COMPROMETIMENTO

**EU ME COMPROMETO A:**
1. ✅ Criar TUDO que for necessário sem pedir para o usuário fazer
2. ✅ Testar TUDO antes de considerar completo
3. ✅ Criar dados de teste realistas
4. ✅ Documentar TUDO
5. ✅ Responder SEMPRE em português
6. ✅ Ser proativo e antecipar necessidades
7. ✅ Corrigir erros imediatamente
8. ✅ Limpar cache após mudanças
9. ✅ Manter código limpo e organizado
10. ✅ Focar na experiência do usuário final

---

**Estas diretrizes são definitivas e devem ser seguidas em TODAS as interações! 🚀**

**Data:** 2025-10-12
**Status:** ✅ Ativo
**Versão:** 2.0
