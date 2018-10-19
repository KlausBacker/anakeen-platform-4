# migration tools

Some tools to migrate

    ./ank.php --route=Migration::InitTransfert --method=POST 
    ./ank.php --script=generateDocumentClass
    ./ank.php --route=Migration::StructureTransfert --method=POST --structure=BASE
    ./ank.php --route=Migration::StructureTransfert --method=POST --structure=DIR

    (root) create extension postgres_fdw ;
    (root) alter role "anakeen-platform" superuser ;
    (user) create schema dynacase;
    (root) CREATE SERVER dynacase FOREIGN DATA WRAPPER postgres_fdw OPTIONS (dbname 'db32');
    (root) ALTER SERVER dynacase OWNER TO "anakeen-platform" ;
    (user) CREATE USER MAPPING FOR postgres SERVER dynacase OPTIONS (user 'anakeen-platform', password 'secret');
    (user) CREATE USER MAPPING FOR 'anakeen-platform' SERVER dynacase OPTIONS (user 'postgres');
    (user) IMPORT FOREIGN SCHEMA public LIMIT TO (doc1) FROM SERVER dynacase into dynacase;
    
        name         | id4  | id32  
---------------------+------+-------
 MAIL                | 1000 |    15
 MASK                | 1012 |    21
 EXEC                | 1003 |    37
 MAILTEMPLATE        | 1001 |    41
 PRF_ADMIN_EDIT      | 1022 |   504
 PRF_ADMIN_DIR       | 1025 |   505
 MSK_IUSER_ADMIN     | 1013 |   506
 CV_IUSER_ACCOUNT    | 1015 |   508
 PRF_IUSER_OWNER     | 1028 |   509
 PRF_ADMIN_CREATION  | 1024 |   510
 PRF_HELPPAGE        | 1026 |  1000
 HELPPAGE            | 1004 |  1001
 mskfld              | 1020 |  1006
 cvfld               | 1021 |  1007
 USER_ADMIN          | 1006 |  1011
 USER_GUEST          | 1007 |  1012
 GDEFAULT            | 1008 |  1013
 GADMIN              | 1009 |  1014
 AUTH_TPLMAILASKPWD  | 1017 | 73281
 HELP_DSEARCH        | 1011 | 73282
 FLD_ACCOUNTS        | 1005 | 73283
 MSK_IUSERSUBSTITUTE | 1014 | 73284
