#!/usr/bin/env bash

set -eo pipefail

cat <<EOF | psql -U "${POSTGRES_USER}"
CREATE ROLE "keycloak" WITH LOGIN ENCRYPTED PASSWORD 'keycloak';
CREATE DATABASE "keycloak" WITH OWNER "keycloak" ENCODING 'UTF8' TEMPLATE "template0";
EOF
