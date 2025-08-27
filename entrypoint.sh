#!/usr/bin/env bash
set -e

cat > /var/www/html/.env <<EOF
CI_ENVIRONMENT=${CI_ENVIRONMENT:-production}
app.baseURL="${APP_BASE_URL}"

database.default.hostname="${DB_HOST}"
database.default.database="${DB_NAME}"
database.default.username="${DB_USER}"
database.default.password="${DB_PASS}"
database.default.DBDriver=MySQLi
database.default.port=${DB_PORT}

GOOGLE_CLIENT_ID="${GOOGLE_CLIENT_ID}"
GOOGLE_CLIENT_SECRET="${GOOGLE_CLIENT_SECRET}"
GOOGLE_REDIRECT_URI="${GOOGLE_REDIRECT_URI}"

email.protocol=smtp
email.SMTPHost=smtp.gmail.com
email.SMTPUser="${SMTP_USER}"
email.SMTPPass="${SMTP_PASS}"
email.SMTPPort=587
email.SMTPCrypto=tls
email.fromEmail="${SMTP_FROM_EMAIL}"
email.fromName="${SMTP_FROM_NAME}"
email.mailType=html
email.charset=UTF-8
EOF

# php spark migrate --all --no-interaction || true   # kalau mau otomatis migrasi

exec apache2-foreground