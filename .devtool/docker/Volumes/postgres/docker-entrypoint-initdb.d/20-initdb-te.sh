#!/usr/bin/env bash

set -eo pipefail

cat <<EOF | psql -U "${POSTGRES_USER}"
CREATE DATABASE "te" WITH OWNER "${ANK_PG_USER}" ENCODING 'UTF8' TEMPLATE "template0";
EOF
