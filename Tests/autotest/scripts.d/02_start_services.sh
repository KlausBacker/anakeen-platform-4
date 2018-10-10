#!/bin/bash

set -eo pipefail

service apache2 restart
service postgresql restart

su - postgres -c 'psql "dynacase"  -c "CREATE EXTENSION IF NOT EXISTS unaccent"'