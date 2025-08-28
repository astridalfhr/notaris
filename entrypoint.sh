#!/usr/bin/env bash
set -euo pipefail

ENV_PATH="/var/www/html/.env"

# baseURL fallback dari domain Railway kalau APP_BASE_URL kosong
APP_BASE_URL="${APP_BASE_URL:-}"
if [ -z "$APP_BASE_URL" ] && [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ]; then
  APP_BASE_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
fi

# default DB
DB_DRIVER="${DB_DRIVER:-MySQLi}"        # atau set Postgre
DB_PORT="${DB_PORT:-$([ "$DB_DRIVER" = "Postgre" ] && echo 5432 || echo 3306)}"

# tulis .env
cat > "$ENV_PATH" <<EOF
CI_ENVIRONMENT=${CI_ENVIRONMENT:-production}
app.baseURL="${APP_BASE_URL}"

database.default.hostname="${DB_HOST:-localhost}"
database.default.database="${DB_NAME:-}"
database.default.username="${DB_USER:-}"
database.default.password="${DB_PASS:-}"
database.default.DBDriver=${DB_DRIVER}
database.default.port=${DB_PORT}

GOOGLE_CLIENT_ID="${GOOGLE_CLIENT_ID:-}"
GOOGLE_CLIENT_SECRET="${GOOGLE_CLIENT_SECRET:-}"
GOOGLE_REDIRECT_URI="${GOOGLE_REDIRECT_URI:-}"

email.protocol=smtp
email.SMTPHost=smtp.gmail.com
email.SMTPUser="${SMTP_USER:-}"
email.SMTPPass="${SMTP_PASS:-}"
email.SMTPPort=587
email.SMTPCrypto=tls
email.fromEmail="${SMTP_FROM_EMAIL:-}"
email.fromName="${SMTP_FROM_NAME:-Notariss System}"
email.mailType=html
email.charset=UTF-8
EOF

# opsional: jalankan migrasi sekali jalan kalau diminta
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "[entrypoint] Running migrations..."
  php spark migrate --all --no-interaction || true
fi

echo "[entrypoint] Starting Apache..."
exec apache2-foreground
