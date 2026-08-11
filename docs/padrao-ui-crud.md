# Padrão de UI/UX para CRUDs (base: Escolas)

Documento de referência das alterações feitas no módulo **Escolas** (`/tenant/schools`) e no **Dashboard**. Use este guia ao padronizar outros CRUDs (Alunos, Produtos, Pedidos, etc.).

**Referência de implementação:**
- Views: `resources/views/pages/tenant/schools/`
- Controller: `app/Http/Controllers/Tenant/SchoolController.php`
- Ícones do menu: `app/Helpers/MenuHelper.php`
- Toast global: `resources/views/components/ui/toast.blade.php`
- Layout: `resources/views/layouts/app.blade.php`
- Validações PT: `lang/pt_BR/validation.php` + `APP_LOCALE=pt_BR`

---

## 1. Checklist rápido (novo CRUD)

- [ ] Ícone no menu (`MenuHelper`)
- [ ] Mesmo ícone no título das páginas (index, create, edit, show)
- [ ] Botão “Novo …” com ícone `+`
- [ ] Ações da lista só com ícones (visualizar / editar), hover e `title`
- [ ] Hierarquia de botões (primário / secundário / cancelar)
- [ ] Formulário: campos relacionados lado a lado (`flex`)
- [ ] Campos obrigatórios com `*` e `@error` por campo
- [ ] `novalidate` no form (mensagem via Laravel em PT)
- [ ] Sucesso via `->with('success', '...')` (toast global)
- [ ] Remover banner verde antigo de `session('success')` da view

---

## 2. Ícones

### 2.1 Biblioteca

Não há pacote de ícones (Lucide, Heroicons, etc.). Os ícones são **SVGs inline**, vindos do template TailAdmin e/ou adicionados em `MenuHelper::getIconSvg()`.

### 2.2 Novo ícone no menu

1. Adicionar a chave SVG em `MenuHelper::getIconSvg()`:
```php
'school' => '<svg width="24" height="24" ...>...</svg>',
```

2. Usar no item do menu:
```php
[
    'icon' => 'school',
    'name' => 'Escolas',
    'path' => '/tenant/schools',
],
```

3. Reutilizar no título da página:
```blade
<h1 class="flex items-center gap-2.5 text-2xl font-semibold text-gray-800 dark:text-white/90">
    <span class="inline-flex size-8 items-center justify-center text-brand-500 dark:text-brand-400">
        {!! \App\Helpers\MenuHelper::getIconSvg('school') !!}
    </span>
    Escolas
</h1>
```

---

## 3. Listagem (index)

### 3.1 Cabeçalho

- Título com ícone
- Subtítulo curto
- CTA primário à direita: **Novo registro** com ícone `+`

```blade
<a href="{{ route('tenant.xxx.create') }}"
   class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
    Novo Registro
</a>
```

### 3.2 Ações da tabela (só ícone)

- Sem texto “Visualizar” / “Editar”
- Ícone ~22px, área clicável `size-10`
- `title` + `sr-only` para acessibilidade
- Hover muda cor / fundo

**Visualizar (olho):**
```blade
<a href="..." title="Visualizar"
   class="inline-flex size-10 items-center justify-center rounded-lg text-brand-500 transition-colors hover:bg-brand-50 hover:text-brand-700 dark:text-brand-400 dark:hover:bg-white/5 dark:hover:text-brand-300">
    <!-- SVG olho 22x22 -->
    <span class="sr-only">Visualizar</span>
</a>
```

**Editar (lápis):**
```blade
<a href="..." title="Editar"
   class="inline-flex size-10 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
    <!-- SVG lápis 22x22 -->
    <span class="sr-only">Editar</span>
</a>
```

SVGs prontos: ver `resources/views/pages/tenant/schools/index.blade.php`.

---

## 4. Create / Edit / Show — botões e layout

### 4.1 Hierarquia

| Tipo | Uso | Estilo |
|------|-----|--------|
| Primário | Salvar / Atualizar / Editar (ação principal) | `bg-brand-500 text-white` |
| Secundário | Voltar / Visualizar / Cancelar | `border border-gray-300 bg-white` |

### 4.2 Create / Edit

- Título com ícone
- Botão **Voltar** (secundário) no header
- Card do formulário com **barra de ações no rodapé**:
  - `Cancelar` (secundário)
  - `Salvar` / `Salvar alterações` (primário + ícone)

```blade
<div class="flex flex-col-reverse gap-2 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end dark:border-gray-800 dark:bg-white/[0.02]">
    <a href="..." class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium ...">
        Cancelar
    </a>
    <button type="submit" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
        <!-- ícone + ou check -->
        Salvar
    </button>
</div>
```

### 4.3 Show

- Título com ícone
- Header: **Voltar** (secundário) + **Editar** (primário)

---

## 5. Formulários

### 5.1 Campos lado a lado

Preferir `flex` (mais previsível neste projeto que grids responsivos do Tailwind customizado):

```blade
{{-- 3 campos iguais --}}
<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1">...</div>
    <div class="min-w-0 flex-1">...</div>
    <div class="min-w-0 flex-1">...</div>
</div>

{{-- Logradouro + Nº fixo + Bairro --}}
<div class="flex w-full flex-row gap-3">
    <div class="min-w-0 flex-1 basis-0">Logradouro</div>
    <div class="w-24 shrink-0">Nº</div>
    <div class="min-w-0 flex-1 basis-0">Bairro</div>
</div>
```

### 5.2 Validação (obrigatório)

- Locale: `APP_LOCALE=pt_BR` (arquivo `lang/pt_BR/validation.php`)
- Mensagem padrão: `O campo :attribute é obrigatório.`
- Marcar label com `<span class="text-error-500">*</span>`
- Exibir `@error('campo')` sob o input
- Destacar borda vermelha quando houver erro
- Usar `novalidate` no `<form>` para priorizar mensagens do Laravel (não as do browser)

```blade
<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
    Nome <span class="text-error-500">*</span>
</label>
<input name="name" ...
    class="... {{ $errors->has('name') ? 'border-error-500' : 'border-gray-300 dark:border-gray-700' }}">
@error('name')
    <p class="mt-1.5 text-sm text-error-500">{{ $message }}</p>
@enderror
```

Alert opcional no topo do create/edit:
```blade
@if ($errors->any())
    <div class="rounded-xl border border-error-500 bg-error-50 px-4 py-3 text-sm text-error-700 ...">
        Verifique os campos obrigatórios destacados abaixo.
    </div>
@endif
```

Adicionar nomes amigáveis em `lang/pt_BR/validation.php` → `attributes`.

---

## 6. Toast de sucesso (global)

### 6.1 Como funciona

- Componente: `<x-ui.toast />` (já incluído no `layouts/app.blade.php`)
- Lê flash: `success`, `error`, `warning`, `info`
- Posição: canto superior direito, `z-999999` (acima do header)
- Auto-fecha em 5s com **barra de progresso**

### 6.2 No controller

```php
return redirect()
    ->route('tenant.xxx.show', $model)
    ->with('success', 'Registro criado com sucesso.');
```

Variantes:
```php
->with('error', 'Não foi possível salvar.')
->with('warning', 'Atenção: ...')
->with('info', 'Informação: ...')
```

### 6.3 Na view

**Não** renderizar mais o banner verde:
```blade
{{-- REMOVER isto --}}
@if (session('success'))
    <div class="...">{{ session('success') }}</div>
@endif
```

O toast global cuida disso.

---

## 7. Endereço em 3 campos / 1 coluna no banco

Padrão temporário usado em Escolas (sem migration):

**Form:** `street`, `number`, `neighborhood`  
**Banco:** coluna única `address`  
**Formato salvo:** `Logradouro, Número - Bairro`  
Ex.: `Rua das Flores, 123 - Centro`

Helpers no model (`School`):
- `composeAddress($street, $number, $neighborhood): ?string`
- `parseAddress(?string $address): array`
- `addressParts(): array`

No controller (store/update):
```php
$validated['address'] = School::composeAddress(
    $validated['street'] ?? null,
    $validated['number'] ?? null,
    $validated['neighborhood'] ?? null,
);
unset($validated['street'], $validated['number'], $validated['neighborhood']);
```

Na view de formulário/show, usar `$addressParts`.

> Quando precisar filtrar por bairro/CEP, migrar para colunas separadas.

---

## 8. Dashboard (compacto)

Referência: `resources/views/pages/tenant/dashboard.blade.php`

- Cards menores (`text-lg`, padding reduzido)
- Agrupar métricas (Cadastros / Pedidos / Financeiro)
- Linhas lado a lado com `flex flex-row` + `flex-1` quando o grid responsivo não cooperar com os breakpoints customizados do tema

---

## 9. Ordem sugerida ao migrar outro CRUD

1. Definir/criar ícone no `MenuHelper`
2. Atualizar `index` (título, botão novo, ações ícone)
3. Atualizar `create` / `edit` / `show` (título, botões, rodapé do form)
4. Ajustar partial do form (layout + validação visual)
5. Garantir `->with('success', ...)` e remover banner antigo
6. Se houver endereço composto, reutilizar helpers do model

---

## 10. Arquivos-modelo (copiar/adaptar)

| Peça | Arquivo |
|------|---------|
| Index | `resources/views/pages/tenant/schools/index.blade.php` |
| Create | `resources/views/pages/tenant/schools/create.blade.php` |
| Edit | `resources/views/pages/tenant/schools/edit.blade.php` |
| Show | `resources/views/pages/tenant/schools/show.blade.php` |
| Form | `resources/views/pages/tenant/schools/partials/form.blade.php` |
| Controller | `app/Http/Controllers/Tenant/SchoolController.php` |
| Toast | `resources/views/components/ui/toast.blade.php` |
| Ícones | `app/Helpers/MenuHelper.php` |
