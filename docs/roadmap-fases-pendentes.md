# Roadmap — Fases pendentes

Documento de acompanhamento para retomar o projeto **Cantina SaaS**.  
Atualize os checkboxes (`[ ]` → `[x]`) conforme cada item for concluído.

**Referência de contexto:** [saas-context.md](./saas-context.md)

**Última revisão:** ago/2026

---

## Status atual (resumo)

| Camada | Progresso | Notas |
|--------|-----------|-------|
| Banco + Models + Migrations | ✅ ~95% | 32 models, multi-tenant com `tenant_id` |
| Seeders | ✅ | `SaasInitialSeeder` com tenant demo |
| Painel Super Admin (`/admin`) | ✅ ~95% | CRUD completo dos módulos principais |
| Painel Tenant Admin (`/tenant`) | ✅ ~95% | CRUD operacional + parental/sistema (Fase 6) |
| Regras de negócio automáticas | ✅ ~95% | OrderService na confirmação/cancelamento do pedido |
| Módulos SaaS (planos, convites) | ✅ ~95% | CRUD admin + aceite de convite; falta visão no tenant |
| Outros perfis (manager, operator, parent, student) | ✅ ~70% | Manager/operator ok; parent/student → **Fase 7 (web + PWA)** |
| Parent/Student web responsivo + PWA | 🟡 ~55% | Portal parent: convite, filhos, carteira, pedidos |
| API REST | ❌ | Adiada para Fase 8 |
| App nativo (Expo) | ❌ | Opcional — Fase 9 |
| Testes automatizados | 🟡 | Pest em fases 3–6; falta CI e cobertura ampla |

### Credenciais de teste (seeder)

| Perfil | E-mail | Senha | URL inicial |
|--------|--------|-------|-------------|
| Super Admin | `superadmin@cantina.local` | `password` | `/admin/tenants` |
| Tenant Admin | `admin@demo.local` | `password` | `/tenant/dashboard` |
| Gerente (manager) | `manager@demo.local` | `password` | `/tenant/dashboard` |
| Operador | `operator@demo.local` | `password` | `/operator/dashboard` |
| Responsável (parent) | `parent@demo.local` | `password` | `/parent` |
| Aluno (student) | `student@demo.local` | `password` | `/student` |

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

**Arquitetura:** `app/Services/` — `OrderService`, `StockService`, `WalletService`, `TabService`, `PinService`, `ParentalControlService`.

### 3.1 Pedidos ↔ Estoque

- [x] Baixar estoque ao confirmar/entregar pedido (produtos com `stock_controlled`)
- [x] Registrar `StockMovement` automaticamente
- [x] Validar estoque insuficiente antes de confirmar
- [x] Estorno de estoque em cancelamento (se aplicável)

### 3.2 Pedidos ↔ Financeiro

- [x] Débito na carteira quando `payment_mode = wallet`
- [x] Registrar `WalletTransaction` com saldo antes/depois
- [x] Lançamento em fiado quando `payment_mode = tab`
- [x] Atualizar `StudentTab.current_balance`
- [x] Criar `TabEntry` vinculado ao pedido
- [x] Registrar `Payment` quando pagamento à vista (cash/pix/card)

### 3.3 Regras de fiado e PIN

- [x] Fiado só permitido em produtos da seção **Lanches** (não conveniência)
- [x] Exigir PIN do aluno para compra no fiado
- [x] Integrar com `PurchaseAuthorization` quando necessário

### 3.4 Controle parental na compra

- [x] Validar categorias permitidas
- [x] Validar produtos bloqueados
- [x] Respeitar limites diários/semanais (se modelado)

### 3.5 Alertas

- [x] Notificação ou destaque quando estoque ≤ mínimo do produto
- [ ] (Opcional) Job agendado para verificar estoques baixos

**Gatilho:** transição de status do pedido para `confirmed` / `preparing` / `ready` / `delivered` via `OrderService::transitionStatus` (tenant e admin). Cancelamento estorna efeitos.
---

## Fase 4 — Módulos SaaS (gestão da plataforma)

**Objetivo:** telas para planos, assinaturas, convites e operadores.

**Prioridade:** 🟡 Média — models e migrations já existem.

| # | Entidade | Model | Status telas |
|---|----------|-------|--------------|
| 4.1 | Planos | `Plan` | ✅ CRUD `/admin/plans` |
| 4.2 | Assinaturas | `Subscription` | ✅ CRUD `/admin/subscriptions` |
| 4.3 | Convites de tenant | `TenantInvitation` | ✅ CRUD + aceite público `/invite/{token}` |
| 4.4 | Operadores | `Operator` | ✅ CRUD `/admin/operators` |

### Checklist

- [x] **4.1** CRUD Planos no `/admin`
- [x] **4.2** CRUD Assinaturas no `/admin` (vincular tenant + plano)
- [x] **4.3** Fluxo de convite (criar convite, aceitar, criar usuário tenant)
- [x] **4.4** CRUD Operadores (vincular usuário + escola/permissões)
- [ ] (Opcional) Tenant admin visualizar própria assinatura

---

## Fase 5 — Outros perfis e permissões

**Objetivo:** painéis além de `super_admin` e `tenant_admin`.

**Prioridade:** 🟡 Média — após operação básica estável.

| Perfil | Descrição | Status |
|--------|-----------|--------|
| `manager` | Gerente da cantina | ✅ Painel `/tenant` (mesmo do tenant_admin) |
| `operator` | Caixa / atendente | ✅ Painel `/operator` (pedidos, alunos, carteiras) |
| `parent` | Responsável (web PWA) | ⏸ Próximo — **Fase 7** |
| `student` | Aluno (web PWA) | ⏸ Próximo — **Fase 7** |

### Checklist

- [x] Definir rotas e middleware por perfil (`EnsureOperator`; manager via `EnsureTenantAdmin`)
- [x] Menu e dashboards específicos
- [x] Escopo de dados (operador vê só sua escola quando `operators.school_id` definido)
- [ ] Policies ou Gates granulares (substituir só `user_type` no middleware)
- [x] Redirect no login por `user_type`

### Escopo sugerido por perfil

- **operator:** pedidos, consulta aluno/carteira, caixa rápido
- **manager:** quase tudo do tenant_admin, sem config SaaS
- **parent / student:** web responsivo + PWA (**Fase 7**); API/Expo depois se necessário

---

## Fase 6 — Controle parental e sistema no tenant

**Objetivo:** espelhar no `/tenant` o que hoje só existe no `/admin` (se fizer sentido para o gestor).

**Prioridade:** 🟡 Média-baixa.  
**Status:** ✅ Concluída (exceto pedidos pré-definidos).

### Admin-only hoje → considerar no tenant

- [x] Controles parentais
- [x] Categorias permitidas
- [x] Produtos bloqueados
- [ ] Pedidos pré-definidos *(adiado — Fase 7, painel parent)*
- [x] Autorizações PIN
- [x] Notificações (visualização)
- [x] Audit logs (visualização)

**Nota:** responsável configura no painel parent (Fase 7); tenant admin mantém consulta/suporte.

---

## Fase 7 — Parent/Student web responsivo + PWA (Vue)

**Objetivo:** painéis mobile-first para responsável e aluno, instaláveis como PWA.  
**Stack prevista:** Laravel + **Inertia.js + Vue 3** (área `/parent` e `/student`); painéis admin/tenant/operator continuam em Blade.

**Prioridade:** 🔴 Alta — próxima fase.  
**Por quê Vue/Inertia:** UX mais fluida no mobile sem reescrever o backend; Vue entra só na área parent/student (pode coexistir com Blade).

### Setup técnico

- [x] Instalar Inertia + Vue 3 + Vite (adapter Laravel)
- [x] Layout mobile-first (Vue) separado do TailAdmin
- [x] Middleware `EnsureParent` / `EnsureStudent` + redirect no login
- [x] Manifest + service worker (PWA: ícone, offline leve, "Adicionar à tela inicial")
- [x] Seeders: usuários `parent` e `student` de demo

### Painel responsável (`/parent`)

- [x] Dashboard: filhos, saldos, alertas *(MVP inicial)*
- [x] Convite do tenant: gerar link, WhatsApp/e-mail, aceite mobile
- [x] Cadastro de acesso + filhos no convite (alunos pendentes)
- [x] Filhos vinculados (leitura + cadastro extra)
- [x] Enviar acesso do filho (WhatsApp / copiar / compartilhar)
- [x] Carteira / extrato por filho
- [x] Recarga Pix (solicitação + comprovante + aprovação do gestor)
- [x] Pedidos (listar / acompanhar status)
- [ ] Controle parental (limites, categorias, bloqueios)
- [ ] Pedidos pré-definidos
- [ ] Notificações

### Painel aluno (`/student`)

- [x] Cardápio do dia
- [x] Fazer / acompanhar pedido
- [x] Saldo da carteira *(MVP no dashboard)*
- [ ] PIN / autorizações (conforme regras)

### Qualidade

- [x] Feature tests de escopo tenant + perfil *(Phase7PortalTest)*
- [x] UX ok em viewport mobile (375px+) *(layout MobileShell)*

---

## Fase 8 — API REST

**Objetivo:** backend JSON para integrações e, se necessário, app nativo.

**Prioridade:** 🟡 Média — após MVP parent/student (Fase 7).

### Checklist

- [ ] Criar `routes/api.php` e registrar em `bootstrap/app.php`
- [ ] Autenticação API (Sanctum)
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

## Fase 9 — App nativo (React Native + Expo) — opcional

**Objetivo:** app nas stores **somente se** PWA não atender (push avançado, retenção, exigência de loja).

**Prioridade:** 🟢 Baixa — depende da Fase 8.

### Checklist

- [ ] Avaliar se PWA cobre o uso real (go/no-go)
- [ ] Inicializar projeto Expo (ou monorepo)
- [ ] Auth contra API
- [ ] Telas responsável e aluno (espelhar Fase 7)
- [ ] Push notifications
- [ ] Publicação (stores) — fora do escopo inicial

---

## Fase 10 — Qualidade, DevOps e polish

**Objetivo:** sustentabilidade do projeto.

**Prioridade:** 🟢 Contínua (paralela às fases acima).

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
Fase 1  → Lacunas tenant (seções, categorias, vínculos)     ✅
Fase 2  → Teste manual ponta a ponta                         ✅
Fase 3  → Regras de negócio (Services)                       ✅
Fase 4  → SaaS (planos, assinaturas, convites, operadores)   ✅
Fase 5  → Perfis manager/operator                            ✅
Fase 6  → Parental/sistema no tenant                         ✅
Fase 7  → Parent/Student web + PWA (Vue + Inertia)           ← agora
Fase 8  → API REST
Fase 9  → App nativo Expo (opcional)
Fase 10 → Testes + devops (paralelo)
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
| Regras de negócio | `app/Services/` (`OrderService`, `StockService`, …) |
| Seeder demo | `database/seeders/SaasInitialSeeder.php` |
| Middleware tenant | `app/Http/Middleware/EnsureTenantContext.php`, `EnsureTenantAdmin.php` |
| Parent/Student (Fase 7) | Inertia + Vue 3 em `/parent` e `/student` (a criar) |

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
| ago/2026 | Fase 3: Services (estoque, carteira, fiado, PIN, parental) no `updateStatus` do pedido |
| ago/2026 | Fase 4: CRUD Planos, Assinaturas, Convites e Operadores no `/admin` + aceite `/invite/{token}` |
| ago/2026 | Fase 5 (MVP): manager no `/tenant`, operador no `/operator`, escopo por escola |
| ago/2026 | Fase 6: parental + notificações + auditoria no `/tenant` |
| ago/2026 | Roadmap reordenado: Fase 7 = web+PWA (Vue/Inertia); API → 8; Expo opcional → 9 |
| ago/2026 | Fase 7 setup: Inertia+Vue, layout MobileShell, dashboards `/parent` e `/student`, PWA |
| ago/2026 | Portal responsável: convite do tenant + cadastro de filhos + filhos/pedidos/conta |
| ago/2026 | Recarga Pix: pedido no app, comprovante e fila de aprovação no tenant |
