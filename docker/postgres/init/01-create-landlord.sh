#!/bin/sh
set -eu

LANDLORD_DB_DATABASE="${LANDLORD_DB_DATABASE:-noma_landlord}"

sed -i 's/^host all all all scram-sha-256$/host all all all trust/' /var/lib/postgresql/data/pg_hba.conf

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<SQL
CREATE DATABASE "$LANDLORD_DB_DATABASE";
SQL
