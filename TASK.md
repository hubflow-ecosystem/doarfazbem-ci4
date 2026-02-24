# TASK.md - DoarFazBem

> Rastreamento de tarefas da plataforma de doações.
> Base para RifaFlow.
> Atualizado: 29/01/2026

---

## Status Geral

| Componente | Status | Prioridade |
|------------|--------|------------|
| Campanhas | 🟢 90% | - |
| Doações | 🟢 85% | - |
| Rifas/Sorteios | 🟢 90% | - |
| Relatórios | 🟡 70% | MÉDIA |

---

## Implementado

- [x] Criação de campanhas
- [x] Checkout PIX/Boleto/Cartão
- [x] Recibos automáticos
- [x] Sistema de rifas completo
- [x] Pacotes com desconto
- [x] Prêmios instantâneos
- [x] Ranking de compradores

---

## Pendente

- [ ] Relatórios de transparência
- [ ] Dashboard público
- [ ] Multi-organizador completo
- [ ] API para integrações

---

## Produto Derivado

Este código serve de base para:
- **RifaFlow** (rifa.hubflow) - 90% código pronto

---

## Fase 2: Padrões do Ecossistema

> Ver `docs.hubflow/STANDARDS.md` para detalhes

### Afiliados
- [ ] Criar tabela `affiliates`
- [ ] Criar tabela `affiliate_commissions`
- [ ] Dashboard de afiliados para usuários
- [ ] Gestão de afiliados para admin
- [ ] Comissão padrão: 10%

> **Nota:** DoarFazBem não oferece plano lifetime.

### Usuário Coringa
- [ ] Adicionar campos wildcard em users
- [ ] Interface de gestão no SuperAdmin
- [ ] Lógica de acesso configurável

### Credenciais
- [ ] Criar SuperAdmin (cesar@hubflowai.com)
- [ ] Padronizar senha MySQL

---

## Próxima Ação

Extrair módulo de rifas para RifaFlow como produto independente.
