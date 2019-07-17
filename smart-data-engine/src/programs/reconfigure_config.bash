#!/usr/bin/env bash

INSTALLDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )"/.. >/dev/null 2>&1 && pwd )"

core_db=`"$WIFF_ROOT"/anakeen-control get --module core_db`
if [ -z "$core_db" ]; then
    echo "Unexpected empty 'core_db' from wiff"
    exit 1
fi
vault_root=`"$WIFF_ROOT"/anakeen-control get --module vault_root`
if [ -z "$vault_root" ]; then
    echo "Unexpected empty 'vault_root' from wiff"
    exit 1
fi

client_name=`"$WIFF_ROOT"/anakeen-control get --module client_name`
vault_save=`"$WIFF_ROOT"/anakeen-control get --module vault_save`


. "$INSTALLDIR/programs/libutil.sh"

PGSERVICE="$core_db" psql -c "UPDATE paramv SET val = '$client_name' WHERE name = 'Core::CORE_CLIENT'"
RET=$?
if [ $RET -ne 0 ]; then
	echo "Error setting CORE_CLIENT"
    exit $RET
fi

echo "Updating vault free_entries..."
if [ "$vault_save" == "no" ]; then
    PGSERVICE="$core_db" psql -c "UPDATE vaultdiskdirstorage set isfull = 't';"
    RET=$?
    if [ $RET -ne 0 ]; then
	echo "Error reinitializing vault table"
	exit $RET
    fi
fi
echo "Updating vault r_path..."
PGSERVICE="$core_db" psql -c "UPDATE vaultdiskfsstorage SET r_path = '$vault_root' || '/' || id_fs; "

RET=$?
if [ $RET -ne 0 ]; then
echo "Error updating vault r_path"
    exit $RET
fi


echo "Setting DateStyle ..."
CURRENT_DATABASE=`PGSERVICE="$core_db" psql -tA -c "SELECT current_database()"`
CURRENT_DATABASE_QUOTED=$(echo "$CURRENT_DATABASE" | sed -e 's/"/""/g')

PGSERVICE="$core_db" psql -c "ALTER DATABASE \"$CURRENT_DATABASE_QUOTED\" SET DateStyle = 'ISO, DMY'"
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error setting DateStyle to 'ISO, DMY' on current database \"$CURRENT_DATABASE\""
    exit $RET
fi

echo "Setting standard_conforming_strings to 'off'..."
PGSERVICE="$core_db" psql -c "ALTER DATABASE \"$CURRENT_DATABASE_QUOTED\" SET standard_conforming_strings = 'off'"
RET=$?
if [ $RET -ne 0 ]; then
    echo "Error setting standard_conforming_strings to 'off' on current database \"$CURRENT_DATABASE\""
    exit $RET
fi


