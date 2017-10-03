<?php


namespace Sample\BusinessApp;

use Dcp\HttpApi\V1\DocManager\DocManager;

use Dcp\AttributeIdentifiers\Ba_Client as Client;
use Dcp\AttributeIdentifiers\Ba_Certification as Certification;
use Dcp\AttributeIdentifiers\Ba_prospect as MyAttr;

class Prospect extends \Dcp\Family\Document
{
    public function postCreated()
    {
        parent::postCreated();

        if ($this->revision == 0) {
            $client = DocManager::createDocument("BA_CLIENT", false);

            $client->setValue(Client::cli_mail, $this->getRawValue(MyAttr::pr_mail));
            $client->setValue(Client::cli_name, $this->getRawValue(MyAttr::pr_society));
            $client->setValue(Client::cli_phone, $this->getRawValue(MyAttr::pr_phone));
            $client->setValue(Client::cli_addr, sprintf("%s %s", $this->getRawValue(MyAttr::pr_postalcode), $this->getRawValue(MyAttr::pr_town)));
            $client->setValue(Client::cli_prospect, $this->initid);
            $err = $client->store();
            if (!$err) {
                $err = $this->setValue(MyAttr::pr_client, $client->initid);
                if (!$err) $err = $this->modify();

            }


            $cert = DocManager::createDocument("BA_CERTIFICATION", false);

            $cert->setValue(Certification::cert_mail, $this->getRawValue(MyAttr::pr_mail));
            $cert->setValue(Certification::cert_name, $this->getRawValue(MyAttr::pr_society));
            $cert->setValue(Certification::cert_phone, $this->getRawValue(MyAttr::pr_phone));
            $cert->setValue(Certification::cert_client, $client->initid);
            $cert->setValue(Certification::cert_addr, sprintf("%s %s", $this->getRawValue(MyAttr::pr_postalcode), $this->getRawValue(MyAttr::pr_town)));
            $cert->setValue(Certification::cert_prospect, $this->initid);
            $err = $cert->store();
            if (!$err) {
                $err = $this->setValue(MyAttr::pr_cert, $cert->initid);
                if (!$err) $err = $this->modify();

                $client->setValue(Client::cli_cert, $cert->initid);
                $client->modify();
            }

            return $err;
        }
        return "";
    }


    public function postStore()
    {

        $cert = DocManager::getDocument($this->getRawValue(MyAttr::pr_cert), true, false);

        if ($cert->state === WCertification::E_BA_SENDED ||
            $cert->state === WCertification::E_BA_SENDEDTOP
        ) {
            if ($this->getRawValue(MyAttr::pr_assignedto)) {
                $cert->setValue(Certification::cert_assign, $this->getRawValue(MyAttr::pr_assignedto));
            }
            $cert->setValue(Certification::cert_question, $this->getRawValue(MyAttr::pr_question));
            $cert->setValue(Certification::cert_subject, $this->getRawValue(MyAttr::pr_subject));
            $cert->setValue(Certification::cert_filedesc, $this->getRawValue(MyAttr::pr_filedesc));
            $cert->setValue(Certification::cert_files, $this->getRawValue(MyAttr::pr_files));
            $cert->setValue(Certification::cert_pname,
                sprintf("%s %s",
                    $this->getRawValue(MyAttr::pr_firstname),
                    $this->getRawValue(MyAttr::pr_lastname)));
            $comFile = $this->getMultipleRawValues(MyAttr::pr_cmps);
            $comFileDesc = $this->getMultipleRawValues(MyAttr::pr_cmpdesc);
            $certFiles = $cert->getMultipleRawValues(Certification::cert_files);
            $certDesc = $cert->getMultipleRawValues(Certification::cert_filedesc);
            foreach ($certDesc as $k=>$v) {
                if (!$v) {
                    $certDesc[$k]="#$k";
                }
            }
            if ($comFile) {
                $certFiles = array_unique(array_merge($certFiles, $comFile));
                $certDesc =  array_unique(array_merge($certDesc, $comFileDesc));
                $cert->setValue(Certification::cert_filedesc, $certDesc);
                $cert->setValue(Certification::cert_files, $certFiles);
            }


            $cert->modify();

            $initState=$cert->state;
                $cert->setState(WCertification::E_BA_CONTROL);
                if  ($initState === WCertification::E_BA_SENDED) {
                    if ($this->getRawValue(MyAttr::pr_complement)) {
                        $cert->addArrayRow(Certification::cert_t_cmpmsg, array(
                            Certification::cert_cmpmsg=>$this->getRawValue(MyAttr::pr_complement),
                            Certification::cert_cmpmsgdate=>date("Y-m-d H:i:s")
                        ));

                        $cert->setValue(Certification::cert_lastmsg, $this->getRawValue(MyAttr::pr_complement));
                        $cert->setState(WCertification::E_BA_SENDEDTOP);
                    }
                }


        } elseif ($cert->state === WCertification::E_BA_INIT)  {

                $cert->setState(WCertification::E_BA_SENDED);
        }
    }
}
