# Teste manual — fluxo ponta a ponta (Fase 2)

**Objetivo:** validar que o painel tenant suporta uma operação real de cantina, do cadastro base até pedido e financeiro.

**Login:** `admin@demo.local` / `password` → `/tenant/dashboard`

**Data:** mar/2026

---

## Como usar este documento

1. Siga os passos na ordem (há dependências entre eles).
2. Marque cada checkbox ao concluir.
3. Anote problemas na seção [Problemas encontrados](#problemas-encontrados).
4. Ao terminar, atualize os checkboxes em `docs/roadmap-fases-pendentes.md` (Fase 2).

---

## Pré-requisitos

- [ ] App rodando (`php artisan serve` ou ambiente local equivalente)
- [ ] Banco migrado e com tenant demo (`php artisan migrate --seed` se necessário)
- [ ] Login tenant admin funcionando

---

## Roteiro (13 passos)

### 0 — Login

- [ ] Acessar `/signin`
- [ ] Entrar com `admin@demo.local` / `password`
- [ ] Confirmar redirecionamento para `/tenant/dashboard`

---

### 1 — Criar escola

**Menu:** Escolas → `/tenant/schools`

- [ ] Clicar em **Novo**
- [ ] Preencher: Nome (obrigatório), Ativo = Sim
- [ ] Opcional: documento, telefone, e-mail, endereço
- [ ] Salvar e verificar página de detalhes

**Dados de exemplo:**

| Campo | Valor sugerido |
|-------|----------------|
| Nome | Escola Teste Fase 2 |
| Ativo | Sim |

---

### 2 — Cadastrar aluno

**Menu:** Alunos → `/tenant/students`

- [ ] Clicar em **Novo**
- [ ] Selecionar a escola criada no passo 1
- [ ] Preencher nome e flags obrigatórias (fiado, conta, conveniência, lanches)
- [ ] Definir status = `active`
- [ ] Salvar

**Dados de exemplo:**

| Campo | Valor sugerido |
|-------|----------------|
| Escola | Escola Teste Fase 2 |
| Nome | Maria Silva Teste |
| Pode comprar no fiado | Sim |
| Pode comprar na conta | Sim |
| Acesso conveniência | Sim |
| Acesso lanches | Sim |
| Status | active |

---

### 3 — Cadastrar responsável

**Menu:** Responsáveis → `/tenant/parents`

- [ ] Clicar em **Novo**
- [ ] Preencher nome (obrigatório)
- [ ] Opcional: CPF, telefone, e-mail
- [ ] Salvar

**Dados de exemplo:**

| Campo | Valor sugerido |
|-------|----------------|
| Nome | João Silva |
| Telefone | (11) 99999-0000 |
| E-mail | joao.silva@teste.local |

---

### 4 — Vincular aluno ↔ responsável

**Menu:** Vínculos → `/tenant/student-parents`

- [ ] Clicar em **Novo**
- [ ] Selecionar aluno (passo 2) e responsável (passo 3)
- [ ] Marcar responsável principal e financeiro
- [ ] Salvar

---

### 5 — Criar seções de produtos

**Menu:** Seções → `/tenant/product-sections`

- [ ] Criar seção **Lanches** (ativa)
- [ ] Criar seção **Conveniência** (ativa)

---

### 6 — Criar categorias

**Menu:** Categorias → `/tenant/product-categories`

- [ ] Categoria **Sanduíches** na seção Lanches
- [ ] Categoria **Doces** na seção Conveniência

---

### 7 — Cadastrar produto com estoque controlado

**Menu:** Produtos → `/tenant/products`

- [ ] Clicar em **Novo**
- [ ] Selecionar seção e categoria compatíveis
- [ ] Preencher nome, tipo, preço
- [ ] Manter **Controla estoque** marcado (padrão)
- [ ] Salvar
- [ ] Na página de detalhes, confirmar link **Ver estoque** e quantidade = 0

> **Correção aplicada (mar/2026):** produtos com estoque controlado agora criam registro de estoque automaticamente.

**Dados de exemplo:**

| Campo | Valor sugerido |
|-------|----------------|
| Seção | Lanches |
| Categoria | Sanduíches |
| Nome | Sanduíche Teste |
| Tipo | Revenda |
| Venda | Unidade |
| Preço | 10,00 |
| Controla estoque | Sim |

---

### 8 — Registrar estoque inicial

**Menu:** Estoque → `/tenant/stocks` (ou **Ver estoque** no produto)

- [ ] Localizar o produto criado no passo 7
- [ ] Abrir detalhes do estoque
- [ ] Usar **Ajuste Rápido** com tipo **Entrada** (`in`)
- [ ] Informar quantidade (ex.: 50) e descrição
- [ ] Confirmar que a quantidade atualizou
- [ ] (Opcional) Verificar movimentação em `/tenant/stock-movements`

---

### 9 — Montar cardápio do dia

**Menu:** Cardápios → `/tenant/daily-menus`

- [ ] Clicar em **Novo**
- [ ] Selecionar escola (passo 1) e data de hoje
- [ ] Salvar o cardápio
- [ ] Na página de detalhes, adicionar item com o produto do passo 7
- [ ] Definir quantidade planejada/disponível

> **Nota:** pedidos ainda não exigem produto estar no cardápio — isso é esperado até a Fase 3.

---

### 10 — Criar pedido com itens

**Menu:** Pedidos → `/tenant/orders`

- [ ] Clicar em **Novo**
- [ ] Selecionar escola, aluno, canal e tipo
- [ ] Definir `payment_mode` (ex.: `wallet` ou `tab`)
- [ ] Salvar o cabeçalho do pedido
- [ ] Na página de detalhes, adicionar item com o produto do passo 7
- [ ] Verificar recálculo do total

> **Nota:** pedido não debita estoque/carteira/fiado automaticamente — Fase 3.

---

### 11 — Criar carteira do aluno

**Menu:** Financeiro → Carteiras → `/tenant/student-wallets`

- [ ] Clicar em **Novo**
- [ ] Selecionar aluno (passo 2)
- [ ] Definir saldo inicial (ex.: R$ 50,00)
- [ ] Salvar
- [ ] Verificar extrato vazio na página de detalhes

---

### 12 — Criar conta de fiado

**Menu:** Financeiro → Fiados → `/tenant/student-tabs`

- [ ] Clicar em **Novo**
- [ ] Selecionar aluno (passo 2)
- [ ] Definir saldo em aberto = 0 e ciclo (ex.: mensal)
- [ ] Salvar
- [ ] Verificar seção de lançamentos vazia

---

### 13 — Registrar lançamento e pagamento

#### 13a — Lançamento de fiado

**Menu:** Financeiro → Lançamentos → `/tenant/tab-entries`

- [ ] Clicar em **Novo**
- [ ] Selecionar conta de fiado (passo 12) e aluno correspondente
- [ ] Informar valor, data e status `open`
- [ ] Opcional: vincular ao pedido do passo 10
- [ ] Salvar
- [ ] **Manual:** editar saldo em aberto da conta de fiado para refletir o lançamento

#### 13b — Pagamento

**Menu:** Financeiro → Pagamentos → `/tenant/payments`

- [ ] Clicar em **Novo**
- [ ] Informar valor, método (ex.: pix) e status `completed`
- [ ] Vincular aluno e/ou responsável
- [ ] Salvar

> **Nota:** pagamento não fecha lançamentos nem credita carteira automaticamente — Fase 3.

---

## Critérios de sucesso da Fase 2

A fase é considerada **aprovada** quando:

- [ ] Todos os 13 passos foram executados sem erro bloqueante
- [ ] Dados persistem após recarregar a página
- [ ] Isolamento por tenant funciona (registros de outro tenant retornam 404)
- [ ] Problemas de UX/automação foram documentados abaixo

---

## Problemas encontrados

Preencha durante ou após o teste.

### Passos manuais demais

| # | Descrição | Passo |
|---|-----------|-------|
| 1 | Pedidos e cardápios exigem dois passos: criar cabeçalho, depois adicionar itens na tela de detalhes | 9, 10 |
| 2 | Saldo de fiado não atualiza ao criar lançamento — precisa editar manualmente | 13a |
| 3 | Carteira não gera extrato ao definir saldo inicial — só edição direta do campo | 11 |
| 4 | Não há tela para criar movimentação de carteira (extrato é somente leitura) | 11, 13 |

### Campos / confusões de UX

| # | Descrição | Passo |
|---|-----------|-------|
| 1 | Status do aluno exibido em inglês (`pending`, `active`, etc.) | 2 |
| 2 | Campo `personal_pin_hash` é texto cru, não um campo de PIN | 2 |
| 3 | Lançamento de fiado: selects de aluno e conta não sincronizam automaticamente | 13a |
| 4 | Pagamento permite salvar sem aluno nem responsável | 13b |
| 5 | Pedido não valida se aluno pertence à escola selecionada | 10 |

### Bugs ou validações faltando

| # | Descrição | Severidade | Passo |
|---|-----------|------------|-------|
| 1 | Pedido não debita estoque | Esperado (Fase 3) | 10 |
| 2 | Pedido não debita carteira/fiado | Esperado (Fase 3) | 10 |
| 3 | Lançamento não atualiza `current_balance` do fiado | Média | 13a |
| 4 | Pagamento não vincula a pedido/lançamento | Média | 13b |
| 5 | Edição direta de estoque (`stocks.update`) não gera movimentação | Baixa | 8 |
| 6 | Não há exclusão de registros em nenhum módulo principal | Baixa | todos |

### Corrigido antes do teste

| # | Descrição | Correção |
|---|-----------|----------|
| 1 | Produto com estoque controlado não aparecia em Estoque | Auto-criação de `Stock` ao salvar produto |
| 2 | Detalhe da carteira não mostrava extrato | Tabela de transações na view show |
| 3 | Detalhe do fiado não mostrava lançamentos | Tabela de entries na view show |

---

## Próximo passo após Fase 2

Com o fluxo validado, seguir para **Fase 3 — Regras de negócio automáticas** (`docs/roadmap-fases-pendentes.md`):

1. Pedidos ↔ Estoque
2. Pedidos ↔ Financeiro (carteira, fiado, pagamento)
3. Regras de fiado e PIN
4. Controle parental
