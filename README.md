# migration tools

## Get access to Dynacase Database

## Initialize new context

*  Import `migration-tools` module

## Prepare A4 Database Before migrate

    (postgres#) create extension postgres_fdw ;
    (postgres#) alter role "anakeen-platform" superuser ; -- need to create mapping and foreign schema
    (postgres#) CREATE SERVER dynacase FOREIGN DATA WRAPPER postgres_fdw OPTIONS (dbname 'ccfd32');
    (postgres#) ALTER SERVER dynacase OWNER TO "anakeen-platform" ;
    
    (user) create schema dynacase;
    (user) -- CREATE USER MAPPING FOR postgres SERVER dynacase OPTIONS (user 'anakeen-platform', password 'secret');
    (user) CREATE USER MAPPING FOR "anakeen-platform" SERVER dynacase OPTIONS (user 'postgres');
    (user) IMPORT FOREIGN SCHEMA public LIMIT TO (doc1) FROM SERVER dynacase into dynacase;
    
  


## Some tools to migrate (see `memo.bash`)

### Snapshot
createdb -O "anakeen-platform" -T "anakeen-platform" a4tmp
dropdb "anakeen-platform" && psql -c 'ALTER DATABASE a4tmp RENAME TO "anakeen-platform"' && createdb -O "anakeen-platform" -T "anakeen-platform" a4tmp

    ./ank.php --route=Migration::InitTransfert --method=POST 
    ./ank.php --script=generateDocumentClass
    ./ank.php --route=Migration::StructureTransfert --method=POST --structure=BASE
    ./ank.php --route=Migration::TableTransfert --method=POST --table=Permission