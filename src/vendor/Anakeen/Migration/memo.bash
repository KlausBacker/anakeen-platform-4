#!/bin/bash
./ank.php --route=Migration::InitTransfert --method=POST

./ank.php --script=cleanContext
./ank.php --script=generateDocumentClass

./ank.php --route=Migration::StructureTransfert --method=POST --structure=BASE
./ank.php --route=Migration::StructureTransfert --method=POST --structure=DIR
./ank.php --route=Migration::StructureTransfert --method=POST --structure=PDOC
./ank.php --route=Migration::StructureTransfert --method=POST --structure=PDIR
./ank.php --route=Migration::StructureTransfert --method=POST --structure=SEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=SEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=PSEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=FILE
./ank.php --route=Migration::StructureTransfert --method=POST --structure=IMAGE
./ank.php --route=Migration::StructureTransfert --method=POST --structure=MAIL
./ank.php --route=Migration::StructureTransfert --method=POST --structure=DSEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=DSEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=DSEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=MASK
./ank.php --route=Migration::StructureTransfert --method=POST --structure=PFAM
./ank.php --route=Migration::StructureTransfert --method=POST --structure=REPORT
./ank.php --route=Migration::StructureTransfert --method=POST --structure=CVDOC
./ank.php --route=Migration::StructureTransfert --method=POST --structure=MSEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=EXEC
./ank.php --route=Migration::StructureTransfert --method=POST --structure=SSSEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=SSEARCH
./ank.php --route=Migration::StructureTransfert --method=POST --structure=MAILTEMPLATE
./ank.php --route=Migration::StructureTransfert --method=POST --structure=IGROUP
./ank.php --route=Migration::StructureTransfert --method=POST --structure=IUSER

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

./ank.php --route=Migration::ConfigStructureTransfert --method=POST --structure=PORTAL_SERVICE
./ank.php --script=generateDocumentClass --docid=PORTAL_SERVICE
./ank.php --route=Migration::StructureTransfert --method=POST --structure=PORTAL_SERVICE
