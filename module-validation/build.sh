#!/bin/bash

set -e

function index_header {
	cat <<'EOF'
<!DOCTYPE html>
<html>
<body>
<h1>Xml Schemas for Anakeen Platform 4</h1>
<h2>Configuration XSD</h2>
<ul>
EOF
}

function index_footer {
	cat <<'EOF'
</ul>
</body>
</html>
EOF
}

function index_li {
	cat <<EOF
<li><a href="$1">https://platform.anakeen.com/4/schemas/$2</a></li>
EOF
}

function main {
	if [ ! -f "all.xsd" ]; then
		echo "Error: missing 'all.xsd' file in current working directory!" 1>&2
		return 1
	fi
	if [ ! -d "xsd" ]; then
		echo "Error: missing 'xsd' directory in current working directory!" 1>&2
		return 1
	fi

	echo "[+] Deleting 'DocumentRoot'..."
	rm -Rf DocumentRoot
	mkdir DocumentRoot
	echo "[+] Done."

	index_header >> "DocumentRoot/index.html"

	sed -rn 's/.*schemaLocation="\.\/(xsd\/[^"]*)"\s*namespace="[^"]*\/4\/schemas\/([^"]*)".*/\1\t\2/p' all.xsd \
	| while read SRC DST; do
		echo "[+] Deploying '$SRC' to 'DocumentRoot/$DST'..."
		SRC_DIR=$(dirname "$SRC")
		DST_DIR=$(dirname "$DST")
		mkdir -p "DocumentRoot/$DST_DIR"
		cat <<'EOF' > "DocumentRoot/$DST_DIR/.htaccess"
ForceType text/xml
EOF
		cp "$SRC" "DocumentRoot/$DST_DIR/config.xsd"
		ln -s "config.xsd" "DocumentRoot/$DST"
		index_li "$DST" "$DST" >> "DocumentRoot/index.html"
		echo "[+] Done."
	done

	index_footer >> "DocumentRoot/index.html"

	echo "[+] Setup '.htaccess'..."
	cat <<EOF > "DocumentRoot/.htaccess"
# DirectorySlash Off
AddType   text/xml    xsd                    
EOF
	echo "[+] Done."

	echo ""
	echo "All done."
	echo ""
}

main "$@"

