# Deploy na Hostinger — homolog e produção

Guia para subir o Cantina na Hostinger **sem deploy automático**.
Você faz o primeiro setup uma vez; depois cada atualização é um comando no SSH.

| Ambiente | Domínio | Branch | Pasta do projeto |
|----------|---------|--------|------------------|
| Homolog | `https://homolog.bablifs.com` | `develop` | `~/domains/bablifs.com/apps/homolog` |
| Produção | `https://bablifs.com` | `main` | `~/domains/bablifs.com/apps/production` |

O document root público **não** é a pasta do Laravel. Só `public/` fica acessível na web.

```
/home/u349036361/domains/bablifs.com/
├── apps/
│   ├── homolog/          ← git clone (branch develop)
│   │   ├── .env          ← NUNCA vai para o Git
│   │   ├── app/
│   │   └── public/       ← único diretório público
│   └── production/       ← git clone (branch main) — depois
└── public_html/
    ├── homolog  → symlink para ../apps/homolog/public
    └── index.php / ...   → depois vira symlink para produção
```

**Por que não clonar dentro de `public_html/homolog`?**
Lá ficariam `.env`, `vendor/` e `app/` acessíveis pela URL. Isso é falha de segurança.

---

## Fluxo do dia a dia (depois do setup)

```
PC: commit em develop → push
        ↓
SSH homolog → ./scripts/deploy.sh
        ↓
testa em https://homolog.bablifs.com
        ↓
quando estiver ok: merge develop → main no GitHub
        ↓
SSH produção → ./scripts/deploy.sh
```

---

## Parte 0 — No seu PC (antes de tudo)

1. Commit e push do que está em `develop` (incluindo `scripts/deploy.sh` e os ajustes de HTTPS).
2. Confirme no GitHub que o branch `develop` está atualizado:
   `https://github.com/rafaelcacote/cantina`

---

## Parte 1 — Painel da Hostinger (uma vez)

### 1.1 PHP 8.2+

hPanel → **Avançado** → **Configuração do PHP**

- Versão: **8.2** ou **8.3**
- Extensões ativas: `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `openssl`, `zip`

### 1.2 SSH

hPanel → **Avançado** → **Acesso SSH** → ative.

Anote:

- host (ex.: `ssh.bablifs.com` ou o IP)
- usuário: `u349036361`
- porta: em geral `65002` (Hostinger **não** usa 22)

Teste no seu PC:

```bash
ssh -p 65002 u349036361@SEU_HOST
```

### 1.3 Subdomínio homolog

hPanel → **Domínios** → **Subdomínios** → criar:

- Subdomínio: `homolog`
- Pasta: pode deixar o padrão (`public_html/homolog`). Vamos trocar por um symlink no SSH.

Depois: **SSL** → instalar certificado em `homolog.bablifs.com` (Let's Encrypt).

### 1.4 Banco MySQL só da homolog

hPanel → **Bancos de dados** → **MySQL** → criar:

- Banco: algo como `u349036361_homolog`
- Usuário: algo como `u349036361_homolog`
- Senha forte (guarde)

Marque **todos os privilégios** desse usuário nesse banco.

Produção terá **outro banco**, outro usuário. Nunca compartilhe.

### 1.5 Cron (fila + scheduler)

hPanel → **Avançado** → **Cron Jobs** → a cada 1 minuto:

```bash
cd /home/u349036361/domains/bablifs.com/apps/homolog && php artisan schedule:run >> /dev/null 2>&1
```

E outro, também a cada 1 minuto (fila `database` sem processo longo na shared):

```bash
cd /home/u349036361/domains/bablifs.com/apps/homolog && php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

Quando subir produção, crie os mesmos dois crons apontando para `apps/production`.

---

## Parte 2 — Primeiro deploy da homolog (SSH)

Conecte:

```bash
ssh -p 65002 u349036361@SEU_HOST
```

### 2.1 Chave SSH do servidor → GitHub (deploy key)

Assim o servidor puxa o repo **sem senha** e só com leitura.

```bash
ssh-keygen -t ed25519 -C "hostinger-homolog" -f ~/.ssh/github_cantina -N ""
cat ~/.ssh/github_cantina.pub
```

No GitHub: repo **cantina** → **Settings** → **Deploy keys** → **Add deploy key**

- Title: `hostinger-homolog`
- Key: cole o conteúdo do `.pub`
- **Não** marque write access

No servidor:

```bash
cat >> ~/.ssh/config << 'EOF'
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/github_cantina
  IdentitiesOnly yes
EOF

chmod 600 ~/.ssh/config
ssh -T git@github.com
```

Deve aparecer: `Hi rafaelcacote/cantina! You've successfully authenticated...`

### 2.2 Clonar fora do public_html

```bash
mkdir -p ~/domains/bablifs.com/apps
cd ~/domains/bablifs.com/apps
git clone -b develop git@github.com:rafaelcacote/cantina.git homolog
cd homolog
```

### 2.3 Apontar o subdomínio só para `public/`

```bash
# remove a pasta vazia que a Hostinger criou
rm -rf ~/domains/bablifs.com/public_html/homolog

# homolog.bablifs.com passa a servir apenas o public do Laravel
ln -s ~/domains/bablifs.com/apps/homolog/public ~/domains/bablifs.com/public_html/homolog
```

Confira:

```bash
ls -la ~/domains/bablifs.com/public_html/homolog
# deve mostrar index.php e .htaccess do Laravel
```

### 2.4 `.env` da homolog

```bash
cd ~/domains/bablifs.com/apps/homolog
cp .env.example .env
nano .env
```

Ajuste no mínimo isto (troque usuário/senha/banco pelos da Hostinger):

```env
APP_NAME=Cantina
APP_ENV=staging
APP_KEY=
APP_DEBUG=false
APP_URL=https://homolog.bablifs.com

APP_TIMEZONE=America/Manaus
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u349036361_homolog
DB_USERNAME=u349036361_homolog
DB_PASSWORD=SENHA_DO_BANCO_HOMOLOG

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@bablifs.com
MAIL_FROM_NAME="${APP_NAME}"
```

`APP_DEBUG=false` mesmo na homolog: o domínio é público. Se precisar depurar, ligue por alguns minutos e desligue.

Gere a key e instale dependências:

```bash
php artisan key:generate
composer install --no-dev --optimize-autoloader --no-interaction
```

### 2.5 Assets (CSS/JS)

No SSH, teste se tem Node:

```bash
node -v
npm -v
```

**Se tiver Node 18+:**

```bash
cd ~/domains/bablifs.com/apps/homolog
npm ci
npm run build
```

**Se não tiver Node** (comum em plano mais barato), no seu PC:

```bash
cd /home/rafa/projetos/cantina
npm ci
npm run build
rsync -avz -e "ssh -p 65002" public/build/ u349036361@SEU_HOST:~/domains/bablifs.com/apps/homolog/public/build/
```

### 2.6 Banco, storage e cache

```bash
cd ~/domains/bablifs.com/apps/homolog
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
```

`--seed` só nesta **primeira** vez. Na homolog o `DemoTenantSeeder` roda porque `APP_ENV` não é `production`.

Credenciais de teste (homolog):

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Super Admin | `superadmin@cantina.local` | `password` |
| Tenant Admin | `admin@demo.local` | `password` |

Troque a senha do super admin depois do primeiro login.

### 2.7 Permissões

```bash
cd ~/domains/bablifs.com/apps/homolog
chmod -R ug+rwx storage bootstrap/cache
```

Se der erro de permissão, o usuário SSH e o PHP-FPM costumam ser o mesmo na Hostinger. Se não forem:

```bash
chmod -R 775 storage bootstrap/cache
```

### 2.8 Teste

Abra `https://homolog.bablifs.com`

- Deve abrir o app (não listagem de pastas, não JSON de erro)
- Login em `/admin` e `/tenant`
- CSS/JS carregando (se a tela sair sem estilo, faltou o `npm run build` / `public/build`)

---

## Parte 3 — Atualizar a homolog (toda vez que commitar)

No PC:

```bash
git checkout develop
git add .
git commit -m "sua mensagem"
git push origin develop
```

No servidor:

```bash
ssh -p 65002 u349036361@SEU_HOST
cd ~/domains/bablifs.com/apps/homolog
chmod +x scripts/deploy.sh
./scripts/deploy.sh develop
```

O script faz: `git pull` → `composer install --no-dev` → `npm run build` → `migrate --force` → cache. **Não roda seeder** (não duplica dados nem reseta senha).

Se o servidor não tiver Node, depois do `./scripts/deploy.sh` (ou antes, se o script abortar) envie o build do PC com o `rsync` da seção 2.5.

---

## Parte 4 — Produção (quando a homolog estiver ok)

Mesma receita, pastas e banco **separados**.

### 4.1 No GitHub

Abra um PR `develop` → `main` (ou merge local) e faça push de `main`.

### 4.2 No painel

- Crie banco `u349036361_prod` (usuário e senha novos)
- SSL já deve existir em `bablifs.com`
- Crons iguais, apontando para `apps/production`

### 4.3 No SSH

```bash
cd ~/domains/bablifs.com/apps
git clone -b main git@github.com:rafaelcacote/cantina.git production
cd production
cp .env.example .env
nano .env
```

Diferenças do `.env` de produção:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bablifs.com

DB_DATABASE=u349036361_prod
DB_USERNAME=u349036361_prod
DB_PASSWORD=SENHA_DO_BANCO_PROD
```

```bash
php artisan key:generate
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build   # ou rsync do public/build
php artisan migrate --seed --force
php artisan storage:link
php artisan optimize
chmod -R ug+rwx storage bootstrap/cache
```

`APP_ENV=production` **não** cria o tenant demo. Só planos + super admin (`superadmin@cantina.local` / `password`). Troque e-mail e senha imediatamente.

### 4.4 Ligar `bablifs.com` no `public/` da produção

Faça isso quando estiver pronto para o domínio principal apontar para o Laravel (o `public_html` atual deixa de ser o site):

```bash
cd ~/domains/bablifs.com
mv public_html public_html.bak
ln -s ~/domains/bablifs.com/apps/production/public public_html
```

Se algo der errado:

```bash
rm public_html
mv public_html.bak public_html
```

O symlink `public_html/homolog` some junto com o `mv`. Recrie:

```bash
ln -s ~/domains/bablifs.com/apps/homolog/public ~/domains/bablifs.com/public_html/homolog
```

Na verdade: se `public_html` virar symlink para `apps/production/public`, o caminho `public_html/homolog` passaria a ser `apps/production/public/homolog` — errado.

**Jeito certo em produção:** no hPanel, altere o **document root** de `bablifs.com` para a pasta `apps/production/public` (se o plano permitir), **sem** substituir o `public_html` inteiro. Assim `public_html/homolog` continua intacto.

Se o plano **não** deixar trocar o document root do domínio principal, use esta alternativa:

```bash
# esvazia só o conteúdo atual do site (faça backup antes)
mkdir -p ~/domains/bablifs.com/public_html.bak
# copie/mova o que estiver solto em public_html, exceto a pasta homolog

# coloque o public da produção DENTRO de public_html sem apagar homolog
# via arquivos: index.php e .htaccess apontando para ../apps/production
```

Nesse caso o `index.php` em `public_html/index.php` deve ser uma cópia do `public/index.php` com paths ajustados para `../apps/production`. Prefira trocar o document root no painel — é mais limpo.

### 4.5 Atualizar produção

```bash
cd ~/domains/bablifs.com/apps/production
./scripts/deploy.sh main
```

---

## Checklist rápido

**Homolog (agora)**

- [ ] PHP 8.2+ e extensões
- [ ] SSH funcionando
- [ ] Subdomínio + SSL
- [ ] Banco MySQL só da homolog
- [ ] Deploy key no GitHub
- [ ] Clone em `apps/homolog` no branch `develop`
- [ ] Symlink `public_html/homolog` → `apps/homolog/public`
- [ ] `.env` com `APP_ENV=staging` e `APP_URL=https://homolog.bablifs.com`
- [ ] `composer install --no-dev` + `npm run build`
- [ ] `migrate --seed` (só a primeira vez)
- [ ] Crons de schedule e queue
- [ ] Login em `https://homolog.bablifs.com`

**Produção (depois)**

- [ ] Merge `develop` → `main`
- [ ] Banco separado
- [ ] Clone em `apps/production` no branch `main`
- [ ] `.env` com `APP_ENV=production` e `APP_DEBUG=false`
- [ ] Document root do domínio = `apps/production/public`
- [ ] Trocar senha do super admin
- [ ] Crons apontando para `apps/production`

---

## Problemas comuns

| Sintoma | Causa provável |
|---------|----------------|
| Lista pastas ou 403 em `homolog.bablifs.com` | Symlink não aponta para `public/` |
| Tela sem CSS | Faltou `npm run build` / `public/build` |
| `500` / `No application encryption key` | Não rodou `php artisan key:generate` |
| Mixed content / login em loop | `APP_URL` sem `https://` ou cache antigo: `php artisan optimize:clear && php artisan optimize` |
| `SQLSTATE` / conexão recusada | Banco/usuário/senha do `.env` diferentes do hPanel. Host na Hostinger costuma ser `127.0.0.1` |
| Seeder resetou a senha | Você rodou `migrate --seed` de novo. Use só `./scripts/deploy.sh` nas atualizações |
| `git pull` pede senha | Deploy key / `~/.ssh/config` não configurados |

Ver log:

```bash
tail -n 80 ~/domains/bablifs.com/apps/homolog/storage/logs/laravel.log
```
