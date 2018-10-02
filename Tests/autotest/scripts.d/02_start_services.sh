#!/bin/bash

set -eo pipefail

cat <<'EOF' > /etc/apache2/sites-available/anakeen-platform.conf
<VirtualHost *:80>
	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/html/dynacase/public

	Alias /dynacase-control /var/www/html/dynacase-control

	<Directory /var/www/html/dynacase/public>
		Require all granted
		AllowOverride all
	</Directory>

	<Directory /var/www/html/dynacase-control>
		Require all granted
		AllowOverride all
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF
a2dissite dynacase
a2ensite anakeen-platform

service apache2 restart
service postgresql restart

wait_tcp_server 5432

su - postgres -c 'psql "dynacase"  -c "CREATE EXTENSION IF NOT EXISTS unaccent"'
