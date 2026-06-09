# Roadmap — Fases pendentes

Documento de acompanhamento para retomar o projeto **Cantina SaaS**.  
Atualize os checkboxes (`[ ]` → `[x]`) conforme cada item for concluído.

**Referência de contexto:** [saas-context.md](./saas-context.md)

**Última revisão:** março/2026

---

## Status atual (resumo)

| Camada | Progresso | Notas |
|--------|-----------|-------|
| Banco + Models + Migrations | ✅ ~95% | 32 models, multi-tenant com `tenant_id` |
| Seeders | ✅ | `SaasInitialSeeder` com tenant demo |
| Painel Super Admin (`/admin`) | ✅ ~95% | CRUD completo dos módulos principais |
| Painel Tenant Admin (`/tenant`) | ✅ ~95% | CRUD operacional completo (Fase 1 concluída) |
| Regras de negócio automáticas | ❌ ~5% | Só cadastro manual |
| Módulos SaaS (planos, convites) | ❌ | Models existem, sem telas |
| Outros perfis (manager, operator, parent, student) | ❌ | Sem painéis |
| API REST | ❌ | `routes/api.php` não existe |
| App mobile (Expo) | ❌ | Não iniciado |
| Testes automatizados | ❌ | Só exemplos padrão Laravel/Pest |

### Credenciais de teste (seeder)

| Perfil | E-mail | Senha | URL inicial |
|--------|--------|-------|-------------|
| Super Admin | `superadmin@cantina.local` | `password` | `/admin/tenants` |
| Tenant Admin | `admin@demo.local` | `password` | `/tenant/dashboard` |

```bash
php artisan migrate --seed   # banco vazio
php artisan serve
```

---

## Fase 0 — Já concluído (não refazer)

Use como referência do que **já existe**.

### Super Admin (`/admin`)

- [x] Tenants
- [x] Usuários
- [x] Escolas, Alunos, Responsáveis
- [x] Vínculos aluno ↔ responsável
- [x] Seções e Categorias de produtos
- [x] Produtos
- [x] Estoque + movimentações + ajuste
- [x] Cardápios + itens
- [x] Pedidos + itens + status
- [x] Financeiro: carteiras, extrato, fiados, lançamentos, pagamentos
- [x] Controle parental (5 submódulos)
- [x] Notificações e auditoria

### Tenant Admin (`/tenant`)

- [x] Dashboard com métricas
- [x] Escolas
- [x] Alunos
- [x] Responsáveis
- [x] Vínculos aluno ↔ responsável
- [x] Seções de produtos
- [x] Categorias de produtos
- [x] Produtos
- [x] Estoque + movimentações + ajuste
- [x] Cardápios + itens
- [x] Pedidos + itens + status
- [x] Financeiro: carteiras, extrato, fiados, lançamentos, pagamentos

### Padrão adotado nos módulos tenant

- Middleware: `auth`, `tenant.context`, `tenant.admin`
- `tenant_id` vem do usuário logado (não aparece no formulário)
- `show` / `edit` / `update` retornam **404** se registro for de outro tenant
- Sem delete de entidades principais (exceto itens vinculados quando aplicável)
- Views em `resources/views/pages/tenant/...`
- Menu em `app/Helpers/MenuHelper.php`

---

## Fase 1 — Lacunas do tenant (autonomia operacional)

**Objetivo:** o gestor da cantina operar **sem depender do super admin** para o dia a dia.

**Prioridade:** 🔴 Alta — fazer antes de regras de negócio ou API.

| # | Módulo | Referência admin | Criar no tenant |
|---|--------|------------------|-----------------|
| 1.1 | Seções de produtos | `Admin/ProductSectionController` | Controller, views, rotas, menu |
| 1.2 | Categorias de produtos | `Admin/ProductCategoryController` | Controller, views, rotas, menu |
| 1.3 | Vínculos aluno ↔ responsável | `Admin/StudentParentController` | Controller, views, rotas, menu |

### Checklist por módulo (repetir padrão tenant)

- [x] **1.1 Seções** — `tenant.product-sections.*`
- [x] **1.2 Categorias** — `tenant.product-categories.*`
- [x] **1.3 Vínculos** — `tenant.student-parents.*`

Para cada item:

- [x] Controller em `app/Http/Controllers/Tenant/`
- [x] Views em `resources/views/pages/tenant/...`
- [x] Rotas no grupo `Route::prefix('tenant')` em `routes/web.php`
- [x] Item no menu tenant (`MenuHelper.php`)
- [x] Validação com escopo `tenant_id`
- [ ] Teste manual no browser

**Dependências:** escolas, alunos e responsáveis já cadastrados.

---

## Fase 2 — Validação do fluxo ponta a ponta

**Objetivo:** confirmar que o CRUD suporta uma operação real de cantina (mesmo sem automação).

**Prioridade:** 🔴 Alta — fazer logo após a Fase 1.

**Roteiro detalhado:** [`docs/testes-fluxo-manual.md`](testes-fluxo-manual.md)

### Roteiro de teste manual

- [ ] Criar escola
- [ ] Cadastrar alunos
- [ ] Cadastrar responsáveis
- [ ] Vincular aluno ↔ responsável
- [ ] Criar seções (ex.: Lanches, Conveniência)
- [ ] Criar categorias por seção
- [ ] Cadastrar produtos com estoque controlado
- [ ] Registrar estoque inicial
- [ ] Montar cardápio do dia
- [ ] Criar pedido com itens
- [ ] Criar carteira do aluno
- [ ] Criar conta de fiado
- [ ] Registrar lançamento / pagamento

### Documentar problemas encontrados

- [x] Listar passos manuais demais (pré-mapeado em `testes-fluxo-manual.md`)
- [x] Listar campos/confusões de UX (pré-mapeado)
- [x] Listar bugs ou validações faltando (pré-mapeado; confirmar no teste)

### Correções aplicadas para desbloquear o teste

- [x] Auto-criação de `Stock` ao cadastrar produto com `stock_controlled`
- [x] Link e quantidade de estoque na página do produto
- [x] Extrato na página de detalhes da carteira
- [x] Lançamentos na página de detalhes do fiado

---

## Fase 3 — Regras de negócio automáticas

**Objetivo:** transformar cadastros isolados em **operação real** (conforme `saas-context.md`).

**Prioridade:** 🟠 Média-alta — após Fase 1 e 2.

**Sugestão de arquitetura:** criar `app/Services/` (ex.: `OrderService`, `WalletService`, `StockService`).

### 3.1 Pedidos ↔ Estoque

- [ ] Baixar estoque ao confirmar/entregar pedido (produtos com `stock_controlled`)
- [ ] Registrar `StockMovement` automaticamente
- [ ] Validar estoque insuficiente antes de confirmar
- [ ] Estorno de estoque em cancelamento (se aplicável)

### 3.2 Pedidos ↔ Financeiro

- [ ] Débito na carteira quando `payment_mode = wallet`
- [ ] Registrar `WalletTransaction` com saldo antes/depois
- [ ] Lançamento em fiado quando `payment_mode = tab`
- [ ] Atualizar `StudentTab.current_balance`
- [ ] Criar `TabEntry` vinculado ao pedido
- [ ] Registrar `Payment` quando pagamento à vista (cash/pix/card)

### 3.3 Regras de fiado e PIN

- [ ] Fiado só permitido em produtos da seção **Lanches** (não conveniência)
- [ ] Exigir PIN do aluno para compra no fiado
- [ ] Integrar com `PurchaseAuthorization` quando necessário

### 3.4 Controle parental na compra

- [ ] Validar categorias permitidas
- [ ] Validar produtos bloqueados
- [ ] Respeitar limites diários/semanais (se modelado)

### 3.5 Alertas

- [ ] Notificação ou destaque quando estoque ≤ mínimo do produto
- [ ] (Opcional) Job agendado para verificar estoques baixos

---

## Fase 4 — Módulos SaaS (gestão da plataforma)

**Objetivo:** telas para planos, assinaturas, convites e operadores.

**Prioridade:** 🟡 Média — models e migrations já existem.

| # | Entidade | Model | Status telas |
|---|----------|-------|--------------|
| 4.1 | Planos | `Plan` | ❌ Sem CRUD |
| 4.2 | Assinaturas | `Subscription` | ❌ Sem CRUD |
| 4.3 | Convites de tenant | `TenantInvitation` | ❌ Sem CRUD |
| 4.4 | Operadores | `Operator` | ❌ Sem CRUD |

### Checklist

- [ ] **4.1** CRUD Planos no `/admin`
- [ ] **4.2** CRUD Assinaturas no `/admin` (vincular tenant + plano)
- [ ] **4.3** Fluxo de convite (criar convite, aceitar, criar usuário tenant)
- [ ] **4.4** CRUD Operadores (vincular usuário + escola/permissões)
- [ ] (Opcional) Tenant admin visualizar própria assinatura

---

## Fase 5 — Outros perfis e permissões

**Objetivo:** painéis além de `super_admin` e `tenant_admin`.

**Prioridade:** 🟡 Média — após operação básica estável.

| Perfil | Descrição | Status |
|--------|-----------|--------|
| `manager` | Gerente da cantina | ❌ Sem painel |
| `operator` | Caixa / atendente | ❌ Sem painel |
| `parent` | Responsável (web ou app) | ❌ Sem painel |
| `student` | Aluno | ❌ Sem painel |

### Checklist

- [ ] Definir rotas e middleware por perfil (`EnsureManager`, `EnsureOperator`, etc.)
- [ ] Menu e dashboards específicos
- [ ] Escopo de dados (operador vê só sua escola?)
- [ ] Policies ou Gates granulares (substituir só `user_type` no middleware)
- [ ] Redirect no login por `user_type`

### Escopo sugerido por perfil

- **operator:** pedidos, consulta aluno/carteira, caixa rápido
- **manager:** quase tudo do tenant_admin, sem config SaaS
- **parent / student:** via API + app mobile (Fase 7)

---

## Fase 6 — Controle parental e sistema no tenant

**Objetivo:** espelhar no `/tenant` o que hoje só existe no `/admin` (se fizer sentido para o gestor).

**Prioridade:** 🟡 Média-baixa.

### Admin-only hoje → considerar no tenant

- [ ] Controles parentais
- [ ] Categorias permitidas
- [ ] Produtos bloqueados
- [ ] Pedidos pré-definidos
- [ ] Autorizações PIN
- [ ] Notificações (visualização)
- [ ] Audit logs (visualização)

**Nota:** responsáveis podem configurar via app no futuro; tenant admin pode precisar só de consulta/suporte.

---

## Fase 7 — API REST

**Objetivo:** backend para app mobile e integrações.

**Prioridade:** 🟡 Média — após regras de negócio (Fase 3).

### Checklist

- [ ] Criar `routes/api.php` e registrar em `bootstrap/app.php`
- [ ] Autenticação API (Sanctum ou Passport)
- [ ] Middleware de tenant na API
- [ ] Resources/Transformers para JSON consistente
- [ ] Endpoints principais:
  - [ ] Auth (login, logout, me)
  - [ ] Cardápio do dia
  - [ ] Produtos
  - [ ] Pedidos (criar, listar, status)
  - [ ] Carteira / extrato
  - [ ] Fiado
  - [ ] Controle parental (parent)
  - [ ] Notificações
- [ ] Documentação API (Scribe ou OpenAPI)
- [ ] Rate limiting e validação de tenant

---

## Fase 8 — App mobile (React Native + Expo)

**Objetivo:** app para responsáveis e alunos.

**Prioridade:** 🟢 Baixa-média — depende da Fase 7.

### Checklist

- [ ] Inicializar projeto Expo no repositório (ou monorepo)
- [ ] Auth contra API
- [ ] Telas responsável: filhos, saldo, pedidos, limites
- [ ] Telas aluno: cardápio, pedido, PIN
- [ ] Push notifications (opcional)
- [ ] Publicação (stores) — fora do escopo inicial

---

## Fase 9 — Qualidade, DevOps e polish

**Objetivo:** sustentabilidade do projeto.

**Prioridade:** 🟢 Contínua.

### Testes

- [ ] Feature tests dos fluxos críticos (tenant scope, pedido, estoque)
- [ ] Unit tests dos Services (Fase 3)
- [ ] CI rodando testes (GitHub Actions)

### UX / Produto

- [ ] Dashboard tenant com gráficos (pedidos/dia, receita)
- [ ] Dashboard super admin (tenants ativos, MRR)
- [ ] Remover itens de menu do template TailAdmin não usados (Ecommerce, Charts demo)
- [ ] Signup funcional ou remover rota `/signup`

### Infra

- [ ] README do projeto atualizado (setup, seed, perfis)
- [ ] `.env.example` documentado
- [ ] Deploy (staging/produção) — Docker, Forge, etc.

---

## Ordem recomendada de execução

```
Fase 1  → Lacunas tenant (seções, categorias, vínculos)
Fase 2  → Teste manual ponta a ponta
Fase 3  → Regras de negócio (Services)
Fase 4  → SaaS (planos, assinaturas, convites, operadores)
Fase 5  → Perfis manager/operator
Fase 6  → Parental/sistema no tenant (se necessário)
Fase 7  → API REST
Fase 8  → App mobile
Fase 9  → Testes + polish (paralelo às fases acima)
```

---

## Arquivos-chave do projeto

| Finalidade | Caminho |
|------------|---------|
| Contexto do produto | `docs/saas-context.md` |
| Este roadmap | `docs/roadmap-fases-pendentes.md` |
| Rotas web | `routes/web.php` |
| Menu tenant/admin | `app/Helpers/MenuHelper.php` |
| Controllers tenant | `app/Http/Controllers/Tenant/` |
| Controllers admin | `app/Http/Controllers/Admin/` |
| Seeder demo | `database/seeders/SaasInitialSeeder.php` |
| Middleware tenant | `app/Http/Middleware/EnsureTenantContext.php`, `EnsureTenantAdmin.php` |

---

## Como usar este documento com o Cursor

1. Ao retomar, diga: *"continua a Fase X do roadmap"* e aponte para `docs/roadmap-fases-pendentes.md`.
2. Ao concluir um item, marque `[x]` neste arquivo (commit opcional).
3. Para tarefas grandes, peça uma fase por vez (ex.: só Fase 1.1).
4. Após Fase 2, registre bugs em `docs/testes-fluxo-manual.md`.

---

## Histórico de atualizações

| Data | Alteração |
|------|-----------|
| mar/2026 | Documento inicial com Fases 0–9 após conclusão do CRUD tenant (incl. pedidos e financeiro) |
| mar/2026 | Fase 1 concluída: seções, categorias e vínculos no tenant |
| mar/2026 | Fase 2 iniciada: roteiro em `testes-fluxo-manual.md` + correções de estoque/UX |
