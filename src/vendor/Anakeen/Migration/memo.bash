#!/bin/bash
./ank.php --route=Migration::InitTransfert --method=POST

./ank.php --script=cleanContext --full
./ank.php --script=generateDocumentClass

for S in  BASE DIR PDOC PDIR SEARCH PSEARCH FILE IMAGE MAIL DSEARCH WDOC MASK PFAM REPORT CVDOC MSEARCH EXEC SSEARCH MAILTEMPLATE TIMER IGROUP IUSER GROUP ROLE HELPPAGE SENTMESSAGE
do
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S
done


./ank.php --route=Migration::TableTransfert --method=POST --table=QueryDir --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=Permission
./ank.php --route=Migration::TableTransfert --method=POST --table=Anakeen\\Core\\Account
./ank.php --route=Migration::TableTransfert --method=POST --table=Group
./ank.php --route=Migration::TableTransfert --method=POST --table=DocHisto
./ank.php --route=Migration::TableTransfert --method=POST --table=VGroup
./ank.php --route=Migration::TableTransfert --method=POST --table=VaultDiskDirStorage --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=VaultDiskStorage --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=VaultDiskFsStorage --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=DocVaultIndex --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=DocUTag --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=UserToken --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=DocPerm --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=DocPermExt --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=DocTimer --query=clear=all
./ank.php --route=Migration::TableTransfert --method=POST --table=DocRel --query=clear=all

(ccfd32) update docattr set ordered=483 where id='rapm_ctxt_array'; --correct order in source

for S in LDAPGROUP LDAPUSER CCFD_TECH_ENUMERES CCFD_GEP_STRATEGIE_DPL CCFD_GEP_STRATEGIE_PARTENARIALE  CCFD_GEP_PRODUCTION_DPL  CCFD_GEP_COFINANCEMENT  CCFD_GEP_SOUTIEN_CIRLONG  CCFD_GEP_SOUTIEN_LONG_CYCLE  CCFD_GEP_ACC_TECH_SIDI  CCFD_GEP_RAPPORT_MISSION  CCFD_GEP_PARTENARIAT_SIDI  CCFD_GEP_FINANCEMENT_SIDI_GARANTIE  CCFD_GEP_W_FINANCEMENT_SIDI_GARANTIE  CCFD_GEP_FINANCEMENT_SIDI_PRET  CCFD_GEP_W_FINANCEMENT_SIDI_PRET  CCFD_GEP_FINANCEMENT_SIDI_INVESTISSEMENT  CCFD_GEP_W_FINANCEMENT_SIDI_INVESTISSEMENT  CCFD_GEP_ACTEUR  CCFD_GEP_PERSONNE_MORALE  CCFD_GEP_PERSONNE_PHYSIQUE  CCFD_GEP_SOUTIEN_CIRCOURT  CCFD_GEP_SOUTIEN_COURT_CYCLE  CCFD_GEP_PARTENARIAT_SIDI_CYCLE  CCFD_GED_AUTEUR_MORAL  CCFD_GED_AUTEUR_PHYSIQUE  CCFD_GED_EDITEUR  CCFD_GED_TITRE_PERIODIQUE  CCFD_MODELES  CCFD_GED_NOTICE  CCFD_GEP_COMPTE_RENDU_RENCONTRE  CCFD_GEP_ZONE_GEOGRAPHIQUE  CCFD_GEP_TYPE_ACTION_SOUTENUE  CCFD_GEP_STRATEGIE_PLAN_ACTION  CCFD_GEP_RAPP_MISSION_CYCLE  CCFD_GEP_TYPE_ACTION_SOUTENUE_CC  CCFD_SHAREDREPORT
do
    ./ank.php --route=Migration::ConfigStructureTransfert --method=POST --structure=$S && \
    ./ank.php --script=generateDocumentClass --docid=$S && \
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S \
    ./ank.php --route=Migration::UiStructureTransfert --method=POST --structure=$S
done


./ank.php --route=Migration::ApplicationTransfert --method=POST --application=CCFD
./ank.php --route=Migration::ApplicationTransfert --method=POST --application=CCDL
./ank.php --route=Migration::ApplicationTransfert --method=POST --application=CCFD_LOGIN