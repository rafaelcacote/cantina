# Cantina

SaaS multi-tenant para **gestão de cantinas escolares**. Uma plataforma única onde cada escola/cantina opera de forma isolada, com controle de produtos, cardápio, pedidos, financeiro e controle parental.

## Visão geral

O Cantina permite que várias cantinas usem a mesma plataforma, cada uma como um **tenant** independente. O painel web cobre a operação do dia a dia — do cadastro de alunos ao fechamento financeiro — com isolamento de dados por `tenant_id`.

### Principais funcionalidades

| Módulo | Descrição |
|--------|-----------|
| **Multi-tenant** | Gestão de tenants, planos e assinaturas (estrutura de dados pronta) |
| **Escolas e pessoas** | Escolas, alunos, responsáveis e vínculos familiares |
| **Produtos e estoque** | Seções, categorias, produtos, movimentações e alertas de estoque mínimo |
| **Cardápio e pedidos** | Cardápios diários, pedidos com itens e controle de status |
| **Financeiro** | Carteira digital, extrato, fiado, lançamentos e pagamentos |
| **Controle parental** | Categorias permitidas, produtos bloqueados e pedidos pré-definidos |
| **Auditoria** | Notificações e logs de auditoria |

### Perfis de acesso

| Perfil | Descrição | Status |
|--------|-----------|--------|
| `super_admin` | Dono da plataforma SaaS | Painel `/admin` |
| `tenant_admin` | Gestor da cantina | Painel `/tenant` |
| `manager` | Gerente operacional | Planejado |
| `operator` | Atendente / caixa | Planejado |
| `parent` | Responsável pelo aluno | Planejado |
| `student` | Aluno | Planejado |

## Stack

- **Backend:** [Laravel 12](https://laravel.com) (PHP 8.2+)
- **Frontend:** [Tailwind CSS v4](https://tailwindcss.com) + [Alpine.js](https://alpinejs.dev) + [Vite](https://vitejs.dev)
- **Banco de dados:** PostgreSQL (recomendado) — compatível com MySQL e SQLite
- **Testes:** [Pest](https://pestphp.com)
- **Mobile (planejado):** React Native com Expo

O painel web utiliza o template [TailAdmin Laravel](https://tailadmin.com/laravel) como base de UI.

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+ e npm
- PostgreSQL, MySQL ou SQLite

## Instalação

```bash
# Clonar o repositório
git clone https://github.com/rafaelcacote/cantina.git
cd cantina

# Dependências PHP
composer install

# Dependências frontend
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate
```

Configure o banco no `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cantina
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Execute as migrations e o seeder inicial:

```bash
php artisan migrate --seed
php artisan storage:link
```

## Executando em desenvolvimento

O comando abaixo sobe servidor Laravel, Vite (HMR), fila e logs em paralelo:

```bash
composer run dev
```

Acesse: [http://localhost:8000](http://localhost:8000)

### Credenciais de teste

Após rodar o seeder (`SaasInitialSeeder`):

| Perfil | E-mail | Senha | URL inicial |
|--------|--------|-------|-------------|
| Super Admin | `superadmin@cantina.local` | `password` | `/admin/tenants` |
| Tenant Admin | `admin@demo.local` | `password` | `/tenant/dashboard` |

## Estrutura do projeto

```
cantina/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Painel super admin (/admin)
│   │   └── Tenant/         # Painel da cantina (/tenant)
│   ├── Models/             # 32+ models com tenant_id
│   └── ...
├── database/
│   ├── migrations/
│   └── seeders/
│       └── SaasInitialSeeder.php
├── docs/
│   ├── saas-context.md     # Contexto e regras de negócio
│   └── roadmap-fases-pendentes.md
├── resources/views/pages/
│   ├── admin/
│   └── tenant/
└── routes/web.php
```

## Regras de negócio (resumo)

- Cada tenant acessa **apenas seus próprios dados** — todas as queries respeitam `tenant_id`
- **Fiado** só em lanches; conveniência não aceita fiado
- **Crédito** permite compra em lanches e conveniência
- Compra no fiado exige **PIN do aluno**
- Responsável pode controlar categorias, produtos bloqueados e pedidos pré-definidos
- Estoque baixa a cada venda; estoque mínimo gera alerta

Detalhes completos em [`docs/saas-context.md`](docs/saas-context.md).

## Roadmap

Consulte [`docs/roadmap-fases-pendentes.md`](docs/roadmap-fases-pendentes.md) para o status detalhado.

**Concluído:** banco + models, painéis Super Admin e Tenant Admin (~95%), seeders.

**Em planejamento:** regras de negócio automáticas, módulos SaaS (planos/convites), perfis operacionais, API REST, app mobile e testes automatizados.

## Testes

```bash
composer run test
# ou
php artisan test
```

## Build para produção

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Licença

Este projeto é de uso privado. O template TailAdmin possui [licença própria](https://tailadmin.com/license).
