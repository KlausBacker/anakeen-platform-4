#!/bin/bash


echo $*


for S in $*
do
    ./ank.php --route=Migration::ConfigStructureTransfert --method=POST --structure=$S && \
    ./ank.php --script=generateDocumentClass --docid=$S && \
    ./ank.php --route=Migration::DataElementTransfert --method=POST --structure=$S
    ./ank.php --route=Migration::UiStructureTransfert --method=POST --structure=$S
done