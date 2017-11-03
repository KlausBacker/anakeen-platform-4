#!/bin/bash

set -eo pipefail

service apache2 restart
service postgresql restart
