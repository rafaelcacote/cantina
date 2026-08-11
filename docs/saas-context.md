Crie o arquivo docs/saas-context.md com o contexto completo do projeto.

O projeto é um SaaS multi-tenant para gestão de cantinas escolares.

Stack:
- Laravel para painéis operacionais (admin/tenant/operator) em Blade
- Inertia.js + Vue 3 para painéis parent/student (web responsivo + PWA)
- PostgreSQL / MySQL como banco de dados
- API REST (futuro) e React Native + Expo (opcional, se PWA não bastar)
- O projeto Laravel já possui login e template web instalado

Objetivo do sistema:
Permitir que várias cantinas/escolas usem a mesma plataforma, cada uma como um tenant isolado.

Estratégia multi-tenant:
Usar banco único com coluna tenant_id nas tabelas principais.
Não usar schema separado por cliente neste momento.

Perfis:
- super_admin: dono da plataforma SaaS
- tenant_admin: dono/gestor da cantina
- manager: gerente
- operator: atendente/caixa
- parent: responsável
- student: aluno

Módulos principais:
1. SaaS:
- tenants
- plans
- subscriptions
- tenant_invitations

2. Core da cantina:
- schools
- students
- parents
- student_parents
- operators

3. Produtos e estoque:
- product_sections
- product_categories
- products
- stocks
- stock_movements

4. Cardápio e pedidos:
- daily_menus
- daily_menu_items
- orders
- order_items

5. Financeiro:
- student_wallets
- wallet_transactions
- student_tabs
- tab_entries
- payments

6. Controle parental:
- parental_controls
- parental_control_allowed_categories
- parental_control_blocked_products
- parental_preselected_orders
- parental_preselected_order_items

7. Segurança e auditoria:
- purchase_authorizations
- notifications
- audit_logs

Regras principais:
- cada tenant só acessa seus próprios dados
- todas as queries devem respeitar tenant_id
- fiado só pode ser usado em lanches
- conveniência não aceita fiado
- crédito permite compra em lanches e conveniência
- compra no fiado exige PIN do aluno
- responsável pode cadastrar filhos via convite
- responsável pode controlar categorias, produtos, limites e pedidos pré-definidos
- estoque deve baixar a cada venda
- estoque mínimo deve gerar alerta

Importante:
Antes de criar telas, implemente banco, models, relacionamentos, tenant scope, seeders básicos e rotas administrativas principais.