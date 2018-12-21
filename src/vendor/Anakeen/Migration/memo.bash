#!/bin/bash
# create extension postgres_fdw;
# CREATE SERVER dynacase FOREIGN DATA WRAPPER postgres_fdw OPTIONS (host 'ccfd-ebr.xen2.corp.anakeen.com', dbname 'dynacase');
# CREATE USER MAPPING FOR "postgres" SERVER dynacase OPTIONS (user 'postgres');
# CREATE USER MAPPING FOR "anakeen-platform" SERVER dynacase OPTIONS (user 'postgres');


./ank.php --route=Migration::InitTransfert --method=POST
./ank.php --script=cleanContext --full
./ank.php --script=generateDocumentClass


for S in  BASE DIR PDOC PDIR SEARCH PSEARCH FILE IMAGE MAIL DSEARCH MASK PFAM REPORT CVDOC MSEARCH SSEARCH MAILTEMPLATE TIMER IGROUP IUSER GROUP ROLE HELPPAGE SENTMESSAGE
do
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S
done

for S in  Permission Group DocHisto VGroup Anakeen\\Core\\Account
do
    ./ank.php --route=Migration::TableTransfert --method=POST --class=$S
done


for S in  QueryDir VaultDiskDirStorage VaultDiskStorage VaultDiskFsStorage DocVaultIndex DocUTag UserToken DocPerm DocPermExt DocTimer DocRel
do
    ./ank.php --route=Migration::TableTransfert --method=POST --query=clear=all --class=$S
done


# (ccfd32) update dynacase.docattr set ordered=483 where id='rapm_ctxt_array'; --correct order in source

for S in LDAPGROUP LDAPUSER CCFD_TECH_ENUMERES CCFD_GEP_STRATEGIE_DPL CCFD_GEP_STRATEGIE_PARTENARIALE  CCFD_GEP_PRODUCTION_DPL  CCFD_GEP_COFINANCEMENT  CCFD_GEP_SOUTIEN_CIRLONG    CCFD_GEP_ACC_TECH_SIDI  CCFD_GEP_RAPPORT_MISSION  CCFD_GEP_PARTENARIAT_SIDI  CCFD_GEP_FINANCEMENT_SIDI_GARANTIE   CCFD_GEP_FINANCEMENT_SIDI_PRET  CCFD_GEP_FINANCEMENT_SIDI_INVESTISSEMENT   CCFD_GEP_ACTEUR  CCFD_GEP_PERSONNE_MORALE  CCFD_GEP_PERSONNE_PHYSIQUE  CCFD_GEP_SOUTIEN_CIRCOURT      CCFD_GED_AUTEUR_MORAL  CCFD_GED_AUTEUR_PHYSIQUE  CCFD_GED_EDITEUR  CCFD_GED_TITRE_PERIODIQUE  CCFD_MODELES  CCFD_GED_NOTICE  CCFD_GEP_COMPTE_RENDU_RENCONTRE  CCFD_GEP_ZONE_GEOGRAPHIQUE  CCFD_GEP_TYPE_ACTION_SOUTENUE  CCFD_GEP_STRATEGIE_PLAN_ACTION   CCFD_GEP_TYPE_ACTION_SOUTENUE_CC  CCFD_SHAREDREPORT
do
    ./ank.php --script=destroyStructure --name=$S > /dev/null 2>&1
    ./ank.php --route=Migration::ConfigStructureTransfert --method=POST --structure=$S && \
    ./ank.php --script=generateDocumentClass --docid=$S && \
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S && \
    ./ank.php --route=Migration::UiStructureTransfert --method=POST --structure=$S
done

for S in  CCFD_GEP_W_FINANCEMENT_SIDI_PRET CCFD_GEP_W_FINANCEMENT_SIDI_INVESTISSEMENT CCFD_GEP_W_FINANCEMENT_SIDI_GARANTIE CCFD_GEP_SOUTIEN_COURT_CYCLE CCFD_GEP_PARTENARIAT_SIDI_CYCLE WSTRATEGIEDPL CCFD_GEP_SOUTIEN_LONG_CYCLE CCFD_GEP_RAPP_MISSION_CYCLE
do
    ./ank.php --script=destroyStructure --name=$S > /dev/null 2>&1
    ./ank.php --route=Migration::WorkflowTransfert --method=POST --structure=$S && \
    ./ank.php --script=generateDocumentClass --docid=$S && \
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S && \
    ./ank.php --route=Migration::UiStructureTransfert --method=POST --structure=$S
done
./ank.php --script=cleanContext --full
./ank.php --script=generateDocumentClass
./ank.php --route=Migration::ApplicationTransfert --method=POST --application=CCFD
./ank.php --route=Migration::ApplicationTransfert --method=POST --application=CCDL
./ank.php --route=Migration::ApplicationTransfert --method=POST --application=CCFD_LOGIN
./ank.php --route=Migration::RoutesV1Transfert --method=POST


./ank.php --route=Migration::FinalUpdates --method=POST

./ank.php --script=configureShowcase
# curl -u admin:anakeen http://localhost:10081/api/v2/migration/modules/Ccfd.zip --output ~/Bureau/Ccfd.zip
