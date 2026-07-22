#!/bin/sh
set -eu

certificate_name="${CERTIFICATE_NAME:-emitapi.us.to}"
certificate_dir="/etc/letsencrypt/live/${certificate_name}"
template_dir="/etc/nginx/noma-templates"

mkdir -p /etc/nginx/templates /etc/nginx/basic-auth

auth_user="${NOMA_PROXY_BASIC_AUTH_USER:-noma-admin}"
auth_password="${NOMA_PROXY_BASIC_AUTH_PASSWORD:-change-this-admin-proxy-password}"
auth_hash="$(openssl passwd -apr1 "${auth_password}")"
printf '%s:%s\n' "${auth_user}" "${auth_hash}" > /etc/nginx/basic-auth/admin.htpasswd

if [ -f "${certificate_dir}/fullchain.pem" ] && [ -f "${certificate_dir}/privkey.pem" ]; then
    cp "${template_dir}/https.conf.template" /etc/nginx/templates/default.conf.template
else
    cp "${template_dir}/http-only.conf.template" /etc/nginx/templates/default.conf.template
fi
