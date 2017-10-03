<?php


namespace Sample\BusinessApp;

use \Dcp\AttributeIdentifiers\Ba_client as Client;
use \Dcp\AttributeIdentifiers\Iuser as Iuser;
use Dcp\HttpApi\V1\DocManager\DocManager;

class WClient extends \Dcp\Family\Wdoc
{
    public $attrPrefix = "WCLI";


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
            "ask"=>array("WCLI_ASSIGN"),
            "m1" => "",
            "m2" => "copyAssign",
            "nr" => true
        ),

        self::T_BA_INCOMPLETE => array(
            "ask"=>array("WCLI_CMPMSG"),
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
            "ask"=>array("WCLI_CHECKLIST"),
            "m1" => "",
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
        $this->doc->addArrayRow(Client::cli_t_cmpmsg, array(
            Client::cli_cmpmsg=>$this->getRawValue("wcli_cmpmsg"),
            Client::cli_cmpmsgdate=>date("Y-m-d H:i:s")
        ));
        $this->doc->modify();


        DocManager::cache()->addDocument($this->doc);
        $pass=uniqid("ba");
        $this->doc->fields[]='tmppass';
        $this->doc->tmppass=$pass;

        $u=new \Account();
        if ($u->setLoginName($this->doc->getRawValue(Client::cli_mail))) {
             $puser = DocManager::getDocument($u->fid);
            $puser->disableEditControl();
        } else {
            $puser = DocManager::createDocument("IUSER", false);
            $puser->disableEditControl();
            $puser->setValue(Iuser::us_login, $this->doc->getRawValue(Client::cli_mail));
        }
        $puser->setValue(Iuser::us_lname, $this->doc->getRawValue(Client::cli_name));
        $puser->setValue(Iuser::us_passwd1, $pass);
        $puser->setValue(Iuser::us_passwd2, $pass);
        return $puser->store();
    }
    public function copyAssign() {
        $this->doc->setValue(Client::cli_assign, $this->getRawValue("wcli_assign"));
        $this->doc->modify();
    }

    public function generateCourrier() {
        global $action;
        $fileInfo=$this->getFileInfo($this->getRawValue("wcli_courrier"),"", "object");


        $ooo=new \OOoLayout($fileInfo->path, $action, $this->doc);

        $this->doc->lay=$ooo;
        $this->doc->viewdefaultcard("ooo");

        $this->doc->setFile(Client::cli_courrier, $ooo->gen(), sprintf("Accord %s.odt", $this->doc->getTitle()));
        $this->doc->modify();
    }
}
