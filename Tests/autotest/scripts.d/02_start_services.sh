#!/bin/bash

set -eo pipefail

service apache2 restart
service postgresql restart

sudo -u postgres psql "dynacase"  -c "CREATE EXTENSION IF NOT EXISTS unaccent"

