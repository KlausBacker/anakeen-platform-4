init:
	$(_CONTROL_CMD) init --pg-service=docker --password=anakeen

register-local-repo:
	$(_CONTROL_CMD) registry add localRepo /var/www/html/localRepo/

install:
	$(_CONTROL_CMD) install