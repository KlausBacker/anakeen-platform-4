<?php


namespace Sample\BusinessApp;

use \Dcp\AttributeIdentifiers\Ba_certification as Certification;
use \Dcp\AttributeIdentifiers\Ba_client as Client;
use \Dcp\AttributeIdentifiers\Iuser as Iuser;
use Dcp\HttpApi\V1\DocManager\DocManager;

class WCertification extends \Dcp\Family\Wdoc
{
    public $attrPrefix = "WCERT";


    const E_BA_INIT = "e_ba_init"; # N_("e_ba_init")
    const E_BA_SENDED = "e_ba_sended"; # N_("e_ba_sended")
    const E_BA_CONTROL = "e_ba_control"; # N_("e_ba_control")
    const E_BA_SENDEDTOP = "e_ba_sendedtop"; # N_("e_ba_sendedtop")
    const E_BA_ACCORD = "e_ba_accord"; # N_("e_ba_accord")

    // Liste des transitions
    const T_BA_SEND = "t_ba_send"; # N_("t_ba_send")
    const T_BA_CONTROL= "t_ba_control"; # N_("t_ba_control")
    const T_BA_INCOMPLETE = "t_ba_incomplete"; # N_("t_ba_incomplete")
    const T_BA_COMPLETERETURN= "t_ba_completereturn"; # N_("t_ba_completereturn")
    const T_BA_ACCORD= "t_ba_accord"; # N_("t_ba_accord")



    public $transitions = array(

        self::T_BA_SEND => array(
            "ask"=>array(),
            "m1" => "",
            "m2" => "",
            "nr" => true
        ),
        self::T_BA_CONTROL => array(
            "ask"=>array("WCERT_ASSIGN"),
            "m1" => "",
            "m2" => "copyAssign",
            "nr" => true
        ),

        self::T_BA_INCOMPLETE => array(
            "ask"=>array("WCERT_CMPMSG"),
            "m1" => "",
            "m2" => "copyMsg",
            "nr" => true
        ),
        self::T_BA_COMPLETERETURN => array(
            "ask"=>array(),
            "m1" => "",
            "m2" => "",
            "nr" => true
        ),
        self::T_BA_ACCORD => array(
            "ask"=>array("WCERT_CHECKLIST"),
            "m1" => "",
            "m2" => "clientToApprouv",
            "m3" => "generateCourrier",
            "nr" => true
        ),
    );

    public $firstState=self::E_BA_INIT;
    public $cycle = array(
        array(
            "e1" => self::E_BA_INIT,
            "e2" => self::E_BA_SENDED,
            "t" => self::T_BA_SEND
        ),
        array(
            "e1" => self::E_BA_SENDED,
            "e2" => self::E_BA_CONTROL,
            "t" => self::T_BA_CONTROL
        ),
        array(
            "e1" => self::E_BA_CONTROL,
            "e2" => self::E_BA_SENDEDTOP,
            "t" => self::T_BA_INCOMPLETE
        ),
        array(
            "e1" => self::E_BA_SENDEDTOP,
            "e2" => self::E_BA_CONTROL,
            "t" => self::T_BA_COMPLETERETURN
        ),
        array(
            "e1" => self::E_BA_CONTROL,
            "e2" => self::E_BA_ACCORD,
            "t" => self::T_BA_ACCORD
        ),
    );


    public function copyMsg() {
        if ($this->getRawValue("wcert_cmpmsg")) {
            $this->doc->addArrayRow(Certification::cert_t_cmpmsg, array(
                Certification::cert_cmpmsg => $this->getRawValue("wcert_cmpmsg"),
                Certification::cert_cmpmsgdate => date("Y-m-d H:i:s")
            ));
            $this->doc->setValue(Certification::cert_lastmsg, $this->getRawValue("wcert_cmpmsg"));
            $this->doc->modify();
        }

        // Copy on client also
        $client=DocManager::getDocument($this->doc->getRawValue(Certification::cert_client));
        if ($client) {
            $client->addArrayRow(Client::cli_t_cmpmsg, array(
                Client::cli_cmpmsg=>$this->getRawValue("wcert_cmpmsg"),
                Client::cli_cmpmsgdate=>date("Y-m-d H:i:s")
            ));
            $client->modify();
        }

        DocManager::cache()->addDocument($this->doc);
        $pass=uniqid("ba");
        $this->doc->fields[]='tmppass';
        /** @noinspection PhpUndefinedFieldInspection */
        $this->doc->tmppass=$pass;

        $u=new \Account();
        if ($u->setLoginName($this->doc->getRawValue(Certification::cert_mail))) {
             $puser = DocManager::getDocument($u->fid);
            $puser->disableEditControl();
        } else {
            $puser = DocManager::createDocument("IUSER", false);
            $puser->disableEditControl();
            $puser->setValue(Iuser::us_login, $this->doc->getRawValue(Certification::cert_mail));
        }
        $puser->setValue(Iuser::us_lname, $this->doc->getRawValue(Certification::cert_name));
        $puser->setValue(Iuser::us_passwd1, $pass);
        $puser->setValue(Iuser::us_passwd2, $pass);

        $err= $puser->store();
        if (!$err) {
            /**
             * @var \Dcp\Family\Igroup $group
             */
            $group = DocManager::getDocument("GDEFAULT");
            $group->disableEditControl();
            $group->insertDocument($puser->initid, "latest", false, false, true);


        }
    }


    protected function getProspectUser() {

    }

    public function clientToApprouv() {
        $client=DocManager::getDocument($this->doc->getRawValue(Certification::cert_client));
        if ($client) {
            $client->setState(WClient::E_BA_ACCORD);

        }
    }
    public function copyAssign() {
        $this->doc->setValue(Certification::cert_assign, $this->getRawValue("wcert_assign"));
        $this->doc->modify();
    }

    public function generateCourrier() {
        global $action;
        $fileInfo=$this->getFileInfo($this->getRawValue("wcert_courrier"),"", "object");


        $ooo=new \OOoLayout($fileInfo->path, $action, $this->doc);

        $this->doc->lay=$ooo;
        $this->doc->viewdefaultcard("ooo");

        $this->doc->setFile(Certification::cert_courrier, $ooo->gen(), sprintf("Accord %s.odt", $this->doc->getTitle()));
        $this->doc->modify();
    }
}
