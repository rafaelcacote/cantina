#!/usr/bin/env bash
set -euo pipefail

# Deploy manual no servidor (homolog ou produção).
# Uso:
#   ./scripts/deploy.sh              # atualiza o branch atual
#   ./scripts/deploy.sh develop      # força o branch develop (homolog)
#   ./scripts/deploy.sh main         # força o branch main (produção)

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

BRANCH="${1:-$(git rev-parse --abbrev-ref HEAD)}"

echo "==> Diretório: $APP_DIR"
echo "==> Branch:    $BRANCH"

php artisan down --retry=60 || true

git fetch origin
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

composer install --no-dev --optimize-autoloader --no-interaction

if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
else
    echo "!! npm não encontrado. Envie public/build do seu PC (veja docs/deploy-hostinger.md)."
    if [ ! -d public/build ]; then
        echo "ERRO: public/build não existe e o servidor não tem Node. Abortando."
        php artisan up || true
        exit 1
    fi
fi

php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php artisan optimize

php artisan up

echo "==> Deploy concluído em $BRANCH"
