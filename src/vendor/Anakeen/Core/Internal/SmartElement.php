<?php

namespace Anakeen\Core\Internal;

/**
 * Document Object Definition
 *
 * @author Anakeen
 */

// define constant for search attributes in concordance with the file "init.freedom"
/**#@+
 * constant for document family identifier in concordance with the file "FDL/init.freedom"
 *
 */
define("FAM_ACCESSDOC", 3);
define("FAM_ACCESSDIR", 4);
define("FAM_SEARCH", 5);
define("FAM_ACCESSSEARCH", 6);
define("FAM_ACCESSFAM", 23);

define("DELVALUE", 'DEL??');

define("PREGEXPFILE", "/(?P<mime>[^\|]*)\|(?P<vid>[0-9]*)\|?(?P<name>.*)?/");


require_once "FDL/LegacyDocManager.php";

use \Anakeen\Core\DbManager;
use \Anakeen\Core\ContextManager;
use \Anakeen\Core\SEManager;
use Anakeen\Core\Internal\Format\StandardAttributeValue;
use Anakeen\Core\SmartStructure\Callables\InputArgument;
use Anakeen\Core\SmartStructure\FieldAccessManager;
use Anakeen\Core\Utils\Date;
use Anakeen\Core\Utils\MiscDoc;
use Anakeen\Core\Utils\Postgres;
use Anakeen\LogManager;
use Anakeen\Routes\Core\Lib\CollectionDataFormatter;
use Anakeen\SmartHooks;

class SmartElement extends \Anakeen\Core\Internal\DbObj implements SmartHooks
{
    const USEMASKCVVIEW = -1;
    const USEMASKCVEDIT = -2;
    public $fields
        = array(
            "id",
            "owner",
            "title",
            "revision",
            "version",
            "initid",
            "fromid",
            "doctype",
            "locked",
            "allocated",
            "icon",
            "lmodify",
            "profid",
            "usefor",
            "cdate",
            "mdate",
            "comment",
            "classname",
            "state",
            "wid",
            "postitid",
            "domainid",
            "lockdomainid",
            "cvid",
            "fallid",
            "name",
            "dprofid",
            "views",
            "atags",
            "prelid",
            "confidential",
            "ldapdn"
        );
    /**
     * @var string searchable values
     */
    protected $svalues;
    public $hooks = null;
    public $sup_fields
        = array(
            "fieldvalues"
        ); // not be in fields else trigger error
    public static $infofields
        = array(
            "id" => array(
                "type" => "integer",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_id"
            ), # N_("prop_id")
            "owner" => array(
                "type" => "uid",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_owner"
            ), # N_("prop_owner"),
            "icon" => array(
                "type" => "image",
                "displayable" => true,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_icon"
            ), # N_("prop_icon"),
            "title" => array(
                "type" => "text",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_title"
            ), # N_("prop_title"),
            "revision" => array(
                "type" => "integer",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_revision"
            ), # N_("prop_revision"),
            "version" => array(
                "type" => "text",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_version"
            ), # N_("prop_version"),
            "initid" => array(
                "type" => "docid",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_initid"
            ), # N_("prop_initid"),
            "fromid" => array(
                "type" => "docid",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_fromid"
            ), # N_("prop_fromid"),
            "doctype" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_doctype"
            ), # N_("prop_doctype"),
            "locked" => array(
                "type" => "uid",
                "displayable" => true,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_locked"
            ), # N_("prop_locked"),
            "allocated" => array(
                "type" => "uid",
                "displayable" => false,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_allocated"
            ), # N_("prop_allocated"),
            "lmodify" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_lmodify"
            ), # N_("prop_lmodify"),
            "profid" => array(
                "type" => "integer",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_profid"
            ), # N_("prop_profid"),
            "usefor" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_usefor"
            ), # N_("prop_usefor")
            "cdate" => array(
                "type" => "timestamp",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_cdate"
            ), # N_("prop_cdate")
            "mdate" => array(
                "type" => "timestamp",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_mdate"
            ), # N_("prop_mdate"),
            "comment" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_comment"
            ), # N_("prop_comment"),
            "classname" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_classname"
            ), # N_("prop_classname")
            "state" => array(
                "type" => "text",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_state"
            ), # N_("prop_state"),
            "wid" => array(
                "type" => "docid",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_wid"
            ), # N_("prop_wid")
            "postitid" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_postitid"
            ), # N_("prop_postitid")
            "cvid" => array(
                "type" => "integer",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_cvid"
            ), # N_("prop_cvid")
            "name" => array(
                "type" => "text",
                "displayable" => true,
                "sortable" => true,
                "filterable" => true,
                "label" => "prop_name"
            ), # N_("prop_name")
            "dprofid" => array(
                "type" => "docid",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_dprofid"
            ), # N_("prop_dprofid")
            "atags" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_atags"
            ), # N_("prop_atags")
            "prelid" => array(
                "type" => "docid",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_prelid"
            ), # N_("prop_prelid")
            "lockdomainid" => array(
                "type" => "docid",
                "displayable" => true,
                "sortable" => true,
                "filterable" => false,
                "label" => "prop_lockdomainid"
            ), # N_("prop_lockdomainid")
            "domainid" => array(
                "type" => "docid",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_domainid"
            ), # N_("prop_domainid")
            "confidential" => array(
                "type" => "integer",
                "displayable" => false,
                "sortable" => false,
                "filterable" => true,
                "label" => "prop_confidential"
            ), # N_("prop_confidential")
            "svalues" => array(
                "type" => "fulltext",
                "displayable" => false,
                "sortable" => false,
                "filterable" => true,
                "label" => "prop_svalues"
            ), # N_("prop_svalues")
            "ldapdn" => array(
                "type" => "text",
                "displayable" => false,
                "sortable" => false,
                "filterable" => false,
                "label" => "prop_ldapdn"
            )
        ); # N_("prop_ldapdn");

    /**
     * identifier of the document
     *
     * @var int
     */
    public $id;
    /**
     * user identifier for the creator
     *
     * @var int
     */
    public $owner;
    /**
     * the title of the document
     *
     * @var string
     */
    public $title;
    /**
     * number of the revision. First is zero
     *
     * @var int
     */
    public $revision;
    /**
     * tag for version
     *
     * @var string
     */
    public $version;
    /**
     * identifier of the first revision document
     *
     * @var int
     */
    public $initid;
    /**
     * identifier of the family document
     *
     * @var int
     */
    public $fromid;
    /**
     * domain where document is lock
     *
     * @var int
     */
    public $lockdomainid;
    /**
     * domain where document is attached
     *
     * @var string
     */
    public $domainid;
    /**
     * the type of document
     *
     * F : normal document (default)
     * C : family document
     * D : folder document
     * P : profil document
     * S : search document
     * T : temporary document
     * W : workflow document
     * Z : zombie document
     *
     * @var string single character
     */
    public $doctype;
    /**
     * user identifier for the locker
     *
     * @vart
     */
    public $locked;
    /**
     * filename or vault id for the icon
     *
     * @var string
     */
    public $icon;
    /**
     * set to 'Y' if the document has been modify until last revision
     *
     * @var string single character
     */
    public $lmodify;
    /**
     * identifier of the profil document
     *
     * @var int
     */
    public $profid;
    /**
     * user/group/role which can view document
     *
     * @var string
     */
    public $views;
    /**
     * to precise a special use of the document
     *
     * @var string single character
     */
    public $usefor;
    /**
     * date of the last modification (the revision date for fixed document)
     *
     * @var int
     */
    public $mdate;
    /**
     * date of creation
     *
     * @var string date 'YYYY-MM-DD'
     */
    public $cdate;
    /**
     * @deprecated old history notation
     * @var string
     */
    public $comment;
    /**
     * class name in case of special family (only set in family document)
     *
     * @var string
     */
    public $classname;
    /**
     * state of the document if it is associated with a workflow
     *
     * @var string
     */
    public $state;
    /**
     * identifier of the workflow document
     *
     * if 0 then no workflow
     *
     * @var int
     */
    public $wid;
    /**
     * identifier of the control view document
     *
     * if 0 then no special control view
     *
     * @var int
     */
    public $cvid;
    /**
     * identifier of the field access layer list
     *
     * if 0 then no field access layer
     *
     * @var int
     */
    public $fallid;
    /**
     * string identifier of the document
     *
     * @var string
     */
    public $name;
    /**
     * identifier of the mask document
     *
     * if 0 then no mask
     *
     * @var int
     */
    public $mid = 0;
    /**
     * identifier of dynamic profil
     *
     * if 0 then no dynamic profil
     *
     * @var int
     */
    public $dprofid = 0;
    /**
     * primary relation id
     *
     * generally towards a folder
     *
     * @var int
     */
    public $prelid = 0;
    /**
     * applications tag
     * use by specifics applications to search documents by these tags
     *
     * @var string
     */
    public $atags;
    /**
     * idengtificator of document's note
     *
     * @var int
     */
    public $postitid;
    /**
     * confidential level
     * if not 0 this document is confidential, only user with the permission 'confidential' can read this
     *
     * @var int
     */
    public $confidential;
    /**
     * Distinguish Name for LDAP use
     *
     * @var string
     */
    public $ldapdn;
    /**
     * Allocate user id
     *
     * @var int
     */
    public $allocated;

    /**
     * @var string logical name family
     */
    public $fromname;
    /**
     * @var string raw family title
     */
    public $fromtitle;
    /**
     * @var string fulltext vector
     */
    public $fulltext;
    /**
     * for system purpose only
     *
     * @var string array of all values
     */
    protected $fieldvalues;

    /**
     * extend acl definition
     * used in WDoc and CVDoc
     * @var array
     */
    public $extendedAcls = array();
    /**
     * @var int  current user id
     * @deprecated
     */
    public $userid;
    /**
     * @var int user permission mask
     */
    public $uperm;
    /**
     * param value cache
     *
     * @var array
     */
    private $_paramValue = array();
    /**
     * @var string last modify error when refresh
     */
    private $lastRefreshError = '';
    private $formaterLevel = 0;
    private $otherFormatter = array();

    /**
     * @var \Anakeen\SmartStructures\Wdoc\WDocHooks
     */
    public $wdoc = null;
    /**
     * @var \Anakeen\Core\SmartStructure\Attributes
     */
    public $attributes = null;
    public static $sqlindex
        = array(
            "doc_initid" => array(
                "unique" => false,
                "on" => "initid"
            ),
            "doc_title" => array(
                "unique" => false,
                "on" => "title"
            ),
            "doc_name" => array(
                "unique" => true,
                "on" => "name,revision,doctype"
            ),
            "doc_full" => array(
                "unique" => false,
                "using" => "gin",
                "on" => "fulltext"
            ),
            "doc_profid" => array(
                "unique" => false,
                "on" => "profid"
            )
        );
    public $id_fields
        = array(
            "id"
        );

    public $dbtable = "doc";

    public $order_by = "title, revision desc";

    public $fulltextfields
        = array(
            "title"
        );
    private $mvalues = array();
    /**
     * number of disabledEditControl
     */
    private $withoutControlN = 0;
    private $withoutControl = false;
    private $inHook = false;
    private $constraintbroken = false; // true if one constraint is not verified
    private $_oldvalue = array();
    private $fathers = null;
    private $childs = null;
    /**
     * @var \DocHtmlFormat
     */
    private $htmlFormater = null;
    /**
     * @var \DocOooFormat
     */
    private $oooFormater = null;
    /**
     * used by fulltext indexing
     *
     * @var array
     */
    private $textsend = array();
    private $vidNoSendTextToEngine = array();
    /**
     * to not detect changed when it is automatic setValue
     *
     * @var bool
     */
    private $_setValueCompleteArray = false;
    /**
     * list of availaible control
     *
     * @var array
     */
    public $acls = array();
    /**
     * document layout
     *
     * @var \Layout|\OooLayout
     */
    public $lay = null;
    /**
     * default family id for the profil access
     *
     * @var int
     */
    public $defProfFamId = FAM_ACCESSDOC;
    public $sqlcreate
        = "
create table doc ( id int not null,
                   primary key (id),
                   owner int,
                   title varchar(256),
                   revision int DEFAULT 0,
                   initid int,
                   fromid int,
                   doctype char DEFAULT 'F',
                   locked int DEFAULT 0,
                   allocated int DEFAULT 0,
                   icon text,
                   lmodify char DEFAULT 'N',
                   profid int DEFAULT 0,
                   usefor text DEFAULT 'N',
                   mdate timestamp,
                   version text,
                   cdate timestamp,
                   comment text,
                   classname text,
                   state text,
                   wid int DEFAULT 0,
                   fieldvalues jsonb,
                   fulltext tsvector,
                   postitid text,
                   domainid text,
                   lockdomainid int,
                   fallid int,
                   cvid int,
                   name text,
                   dprofid int DEFAULT 0,
                   views int[],
                   prelid int DEFAULT 0,
                   atags jsonb,
                   confidential int DEFAULT 0,
                   ldapdn text,
                   svalues text DEFAULT ''
                   );
create table docfrom ( id int not null,
                   primary key (id),
                   fromid int);
create table docname ( name text not null,
                   primary key (name),
                   id int,
                   fromid int);
create sequence seq_id_doc start 1000;
create sequence seq_id_tdoc start 1000000000;
create index i_docname on doc(name);
create unique index i_docir on doc(initid, revision);";


    public $defDoctype = 'F';
    /**
     * to indicate values modification
     *
     * @var bool
     * @access private
     */
    private $hasChanged = false;

    public $paramRefresh = array();
    /**
     * optimize: compute mask in needed only
     *
     * @var bool
     * @access private
     */
    private $_maskApplied = false; // optimize: compute mask if needed only

    /**
     * By default, setValue() will call completeArrayRow when setting
     * values of arrays columns.
     *
     * @var bool
     * @access private
     */
    private $_setValueNeedCompleteArray = true;


    public function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        if (!isset($this->attributes->attr)) {
            if (!isset($this->attributes)) {
                $this->attributes = new \stdClass();
            }
            $this->attributes->attr = array();
        }
        parent::__construct($dbaccess, $id, $res, $dbid);
    }

    /**
     * display document main properties as string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s "%s" [#%d]', $this->fromname, $this->getTitle(), $this->id);
    }

    /**
     * Increment sequence of family and call hooks  SmartHooks::POSTCREATED
     *
     * affect profil
     *
     *
     * @return void
     */
    final public function postInsert()
    {
        // controlled will be set explicitly
        //$this->SetControl();
        if (($this->revision == 0) && ($this->doctype != "T")) {
            // increment family sequence
            $this->nextSequence();
            $famDoc = $this->getFamilyDocument();
            $incumbentName = ContextManager::getCurrentUser()->getIncumbentPrivilege($famDoc, 'create');
            $createComment = _("document creation");
            if ($incumbentName) {
                $createComment = sprintf(_("(substitute of %s) : "), $incumbentName) . $createComment;
            }
            $this->addHistoryEntry($createComment, \DocHisto::INFO, "CREATE");
            if ($this->wdoc) {
                $this->wdoc->workflowSendMailTemplate($this->state, _("creation"));
                $this->wdoc->workflowAttachTimer($this->state);
                $this->wdoc->changeAllocateUser($this->state);
            }
            $this->addLog("create", array(
                "id" => $this->id,
                "title" => $this->title,
                "fromid" => $this->fromid,
                "fromname" => $this->fromname
            ));
        }
        unset($this->fields["svalues"]);
        $this->select($this->id);
        // set creation date
        $this->cdate = Date::getNow(true);
        $this->mdate = Date::getNow(true);
        $this->modify(true, array(
            "cdate",
            "mdate"
        ), true); // to force also execute sql trigger
        if ($this->doctype !== 'C') {
            if ($this->doctype !== "T") {
                if ($this->revision == 0) {
                    $err = $this->getHooks()->trigger(SmartHooks::POSTCREATED);
                    if ($err != "") {
                        \Anakeen\Core\Utils\System::addWarningMsg($err);
                    }
                    if ($this->hasChanged) {
                        //in case of change in postStore
                        $err = $this->modify();
                        if ($err) {
                            \Anakeen\Core\Utils\System::addWarningMsg($err);
                        }
                    }
                }
                $this->sendTextToEngine();
                if ($this->dprofid > 0) {
                    $this->accessControl()->setProfil($this->dprofid); // recompute profil if needed
                    $this->accessControl()->recomputeProfiledDocument();
                    $this->modify(true, array(
                        "profid"
                    ), true);
                }
                $this->modify(true, "", true);

                $this->UpdateVaultIndex();
                $this->updateRelations(true);
            }
        }
        $this->hasChanged = false;
    }

    public function setChanged()
    {
        $this->hasChanged = true;
    }

    /**
     * return true if document has changed after setValue/clearValue calling
     *
     * @api test if document attributes are changed
     * @return bool
     */
    public function isChanged()
    {
        return ($this->hasChanged === true);
    }

    /**
     * set default values and creation date
     * the access control is provided by {@see \Anakeen\Core\Internal\SmartElement::createDoc()} function.
     * call {@see \Anakeen\Core\Internal\SmartElement::PreCreated()} method before execution
     *
     * @return string error message, if no error empty string
     */
    final public function preInsert()
    {
        $err = $this->getHooks()->trigger(SmartHooks::PRECREATED);
        if ($err != "") {
            return $err;
        }
        // compute new \id
        if ($this->id == "") {
            if ($this->doctype == 'T') {
                $res = pg_query($this->initDbid(), "select nextval ('seq_id_tdoc')");
            } else {
                $res = pg_query($this->initDbid(), "select nextval ('seq_id_doc')");
            }
            $arr = pg_fetch_array($res, 0);
            $this->id = $arr[0];
        }
        // set default values
        if ($this->initid == "") {
            $this->initid = $this->id;
        }
        $this->RefreshTitle();
        if (chop($this->title) == "") {
            $fdoc = $this->getFamilyDocument();
            $this->title = sprintf(_("untitle %s %d"), $fdoc->title, $this->initid);
        }
        if ($this->doctype == "") {
            $this->doctype = $this->defDoctype;
        }
        if ($this->revision == "") {
            $this->revision = "0";
        }

        if ($this->profid == "") {
            $this->views = "{}";
            $this->profid = "0";
        }
        if ($this->usefor == "") {
            $this->usefor = "N";
        }

        if ($this->lmodify == "") {
            $this->lmodify = "N";
        }
        if ($this->locked == "") {
            $this->locked = "0";
        }
        if ($this->owner == "") {
            $this->owner = ContextManager::getCurrentUser()->id;
        }
        //      if ($this->state == "") $this->state=$this->firstState;
        $this->version = $this->getVersion();

        if ($this->name && $this->revision == 0) {
            $err = $this->setLogicalName($this->name, false, true);
            if ($err) {
                return $err;
            }
        }
        unset($this->fields["svalues"]);
        if ($this->doctype !== "T") {
            $this->svalues = $this->getExtraSearchableDisplayValues();
            $this->fields["svalues"] = "svalues";
        }
        if ($this->wid > 0) {
            /**
             * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
             */
            $wdoc = SEManager::getDocument($this->wid);
            $this->wdoc = $wdoc;
            if ($this->wdoc && $this->wdoc->isAlive()) {
                if ($this->wdoc->doctype != 'W') {
                    $err = sprintf(_("creation : document %s is not a workflow"), $this->wid);
                } else {
                    $this->wdoc->Set($this);
                } // set first state
            } else {
                $err = sprintf(_("creation : workflow %s not exists"), $this->wid);
            }
        } else {
            $this->wdoc = null;
        }
        return $err;
    }

    /**
     * Verify control edit
     *
     * if {@link \Anakeen\Core\Internal\SmartElement::disableEditControl()} is call before control permission is desactivated
     * if attribute values are changed the modification date is updated
     *
     * @return string error message, if no error empty string
     */
    public function preUpdate()
    {
        if ($this->id == "") {
            return _("cannot update no initialized document");
        }
        if ($this->doctype == 'I') {
            return _("cannot update inconsistent document");
        } // provides from waiting document or searchDOc with setReturns

        $err = $this->controlAccess("edit");
        if ($err) {
            return $err;
        }
        if ($this->locked == -1) {
            $this->lmodify = 'N';
        }
        $err = $this->docIsCleanToModify();
        if ($err) {
            return $err;
        }
        if ($this->constraintbroken) {
            return (sprintf(_("constraint broken %s"), $this->constraintbroken));
        }

        unset($this->fields["svalues"]);
        $this->RefreshTitle();
        if ($this->hasChanged) {
            if (chop($this->title) == "") {
                $this->title = _("untitle document");
            }
            // set modification date
            if ($this->doctype !== "T") {
                $this->svalues = $this->getExtraSearchableDisplayValues();
                $this->fields["svalues"] = "svalues";
            }
            $this->mdate = Date::getNow(true);
            $this->version = $this->getVersion();
            $this->lmodify = 'Y';
        }

        return '';
    }

    private function docIsCleanToModify()
    {
        if ($this->initid > 0 && $this->fromid > 0) {
            DbManager::query(
                sprintf(
                    "select initid, id, revision, locked from only doc%d where initid=%d",
                    $this->fromid,
                    $this->initid
                ),
                $r
            );

            $cAlive = 0;
            $imAlive = false;
            foreach ($r as $docInfo) {
                if (($docInfo["id"] == $this->id) && ($docInfo["locked"] == -1)) {
                    return \ErrorCode::getError('DOC0118', $this->getTitle(), $this->id);
                } elseif ($docInfo["locked"] != -1) {
                    if ($docInfo["id"] == $this->id) {
                        $imAlive = true;
                    }
                    $cAlive++;
                }
            }
            if ($this->locked != -1 && $cAlive == 1 && $imAlive) {
                return ''; // OK
            } elseif ($cAlive > 1) {
                // multiple alive already set : need fix it
                \Anakeen\Core\Utils\MiscDoc::fixMultipleAliveDocument($this);
                if ($this->isFixed()) { // if locked now ?
                    return \ErrorCode::getError('DOC0119', $this->getTitle(), $this->id);
                }
            }
        }
        return '';
    }

    /**
     * optimize for speed : memorize object for future use
     *
     * @return string
     */
    public function postUpdate()
    {
        \Anakeen\Core\Utils\MiscDoc::fixMultipleAliveDocument($this);

        if ($this->hasChanged) {
            $this->accessControl()->computeDProfil();
            if ($this->doctype != 'C') {
                $this->regenerateTemplates();
                $this->UpdateVaultIndex();
                $this->updateRelations();

                if ($this->getATag("DYNTIMER")) {
                    $this->resetDynamicTimers();
                }
                $this->addLog("changed", array_keys($this->getOldRawValues()));
            }
        }
        $this->sendTextToEngine();
        $this->hasChanged = false;
        return '';
    }

    /**
     * Regenerate the template referenced by an attribute
     *
     * @param string $aid   the name of the attribute holding the template
     * @param int    $index the value for $index row (default value -1 means all values)
     *
     * @return string error message, if no error empty string
     */
    public function regenerateTemplate($aid, $index = -1)
    {
        $layout = 'THIS:' . $aid;
        if ($index > -1) {
            $layout .= '[' . $index . ']';
        }
        $orifile = $this->getZoneFile($layout);
        if (!$orifile) {
            $err = sprintf(_("Dynamic template %s not found "), $orifile);
            return $err;
        }
        if (!file_exists($orifile)) {
            $err = sprintf(_("Dynamic template %s not found "), $orifile);
            \Anakeen\Core\Utils\System::addWarningMsg($err);
            return $err;
        }
        if (\Anakeen\Core\Utils\FileMime::getFileExtension($orifile) != 'odt') {
            $err = sprintf(_("Dynamic template %s not an odt file "), $orifile);
            \Anakeen\Core\Utils\System::addWarningMsg($err);
            return $err;
        }
        $outfile = $this->viewDoc($layout . ':B', 'ooo');
        if (!file_exists($outfile)) {
            $err = sprintf(_("viewDoc did not returned a valid file"));
            \Anakeen\Core\Utils\System::addWarningMsg($err);
            return $err;
        }
        $fh = fopen($outfile, 'rb');
        if ($fh === false) {
            $err = sprintf(_("Error opening %s file '%s'"), 'outfile', $outfile);
            \Anakeen\Core\Utils\System::addWarningMsg($err);
            return $err;
        }
        $err = $this->saveFile($aid, $fh, '', $index);
        if ($err != '') {
            \Anakeen\Core\Utils\System::addWarningMsg($err);
            return $err;
        }
        fclose($fh);
        $this->addHistoryEntry(sprintf(_('regeneration of file template %s'), $aid));
        return '';
    }

    /**
     * Regenerate all templates referenced by the document attributes
     *
     * @return string error message, if no error empty string
     */
    final public function regenerateTemplates()
    {
        $fa = $this->GetFileAttributes();
        $errorList = array();
        foreach ($fa as $aid => $oattr) {
            $opt = $oattr->getOption("template");
            if ($opt == "dynamic" || $opt == "form") {
                if ($oattr->inArray()) {
                    $ta = $this->getMultipleRawValues($aid);
                    foreach ($ta as $k => $v) {
                        $err = $this->regenerateTemplate($aid, $k);
                        if ($err != '') {
                            array_push($errorList, $err);
                        }
                    }
                } else {
                    $err = $this->regenerateTemplate($aid);
                    if ($err != '') {
                        array_push($errorList, $err);
                    }
                }
            }
        }
        if (count($errorList) > 0) {
            return join("\n", $errorList);
        }
        return '';
    }

    /**
     * Set relation doc id use on docrel table
     *
     * @param bool $force true to reinit all relations
     */
    public function updateRelations($force = false)
    {
        $or = new \DocRel($this->dbaccess);
        //    $or->resetRelations('',$this->initid); // not necessary now
        $or->initRelations($this, $force);
    }

    /**
     * get current sequence number :: number of doc for this family
     *
     * @return int
     */
    public function getCurSequence()
    {
        if ($this->doctype == 'C') {
            return 0;
        }
        if ($this->fromid == "") {
            return 0;
        }
        // cannot use currval if nextval is not use before
        $res = pg_query($this->initDbid(), "select nextval ('seq_doc" . $this->fromid . "')");
        $arr = pg_fetch_array($res, 0);
        $cur = intval($arr[0]) - 1;
        pg_query($this->initDbid(), "select setval ('seq_doc" . $this->fromid . "',$cur)");

        return $cur;
    }

    /**
     * set next sequence family
     *
     * @param int $fromid
     *
     * @return int
     */
    public function nextSequence($fromid = 0)
    {
        if ($fromid == 0) {
            $fromid = $this->fromid;
        }
        if ($this->fromid == 0) {
            return 0;
        }
        if ($this->doctype == 'C') {
            return 0;
        }
        // cannot use currval if nextval is not use before
        $res = pg_query($this->initDbid(), "select nextval ('seq_doc" . $fromid . "')");
        $arr = pg_fetch_array($res, 0);
        $cur = intval($arr[0]);
        return $cur;
    }

    /**
     * disable access control for setValue/modify/store/lock
     * the document can be modified without testing edit acl
     *
     * @see \Anakeen\Core\Internal\SmartElement::restoreAccessControl
     * @api disable edit control for setValue/modify/store
     * @param bool $disable if false restore control access immediatly
     */
    final public function disableAccessControl($disable = true)
    {
        if ($disable === true) {
            $this->withoutControlN++;
            $this->withoutControl = true;
        } elseif ($disable === false) {
            $this->withoutControlN = 0;
            $this->withoutControl = false;
        }
    }

    private function isUnderControl()
    {
        return $this->withoutControl === false && ContextManager::getCurrentUser()->id != \Anakeen\Core\Account::ADMIN_ID;
    }

    /**
     * default edit control enable
     * restore control which are disabled by disableAccessControl
     *
     * @see \Anakeen\Core\Internal\SmartElement::disableAccessControl
     * @api default edit control enable
     */
    final public function restoreAccessControl()
    {
        $this->withoutControlN--;
        if ($this->withoutControlN <= 0) {
            $this->withoutControlN = 0;
            $this->withoutControl = false;
        }
    }

    /**
     * to know if the document can be revised
     *
     * @return bool true is revisable
     */
    public function isRevisable()
    {
        if (($this->doctype == 'F') && ($this->usefor != 'P')) {
            $fdoc = $this->getFamilyDocument();
            if ($fdoc->schar != "S") {
                return true;
            }
        }
        return false;
    }

    /**
     * copy values from anothers document (must be same family or descendant)
     *
     * @param \Anakeen\Core\Internal\SmartElement &$from document source for the transfert
     *
     * @return string error message from setValue, if no error, empty string
     */
    final public function transfertValuesFrom(&$from)
    {
        $values = $from->getValues();
        $err = "";
        foreach ($values as $k => $v) {
            $err .= ($err ? '\n' : '') . $this->setValue($k, $v);
        }
        return $err;
    }

    /**
     * convert to another family
     * loose all revisions
     *
     * @param int   $fromid    family identifier where the document will be converted
     * @param array $prevalues values which will be added before conversion
     *
     * @return SmartElement|false|string the document converted (don't reuse $this) if error return string message
     */
    final public function convert($fromid, $prevalues = array())
    {
        $cdoc = SEManager::createDocument($fromid);

        if ($this->fromid == $cdoc->fromid) {
            return false;
        } // no convert if not needed
        if ($this->locked == -1) {
            return false;
        } // not revised document
        if ($cdoc->fromid == 0) {
            return false;
        }
        $f1doc = $this->getFamilyDocument();
        $f1from = $f1doc->title . "[" . $f1doc->id . "]";
        $f2doc = $cdoc->getFamilyDocument();
        $f2from = $f2doc->title . "[" . $f2doc->id . "]";

        $cdoc->id = $this->id;
        $cdoc->initid = $this->id;
        $cdoc->revision = 0;
        $cdoc->cdate = $this->cdate;
        $cdoc->mdate = $this->mdate;
        $cdoc->locked = $this->locked;
        $cdoc->profid = $this->profid;
        $cdoc->dprofid = $this->dprofid;
        $cdoc->prelid = $this->prelid;

        $values = $this->getValues();
        $point = "dcp:convert" . $this->id;
        DbManager::savePoint($point); // begin transaction in case of fail add
        $err = $this->delete(true, false, true); // delete before add to avoid double id (it is not authorized)
        if ($err != "") {
            return $err;
        }

        foreach ($prevalues as $k => $v) {
            $cdoc->setValue($k, $v);
        }
        $err = $cdoc->add(true, true);
        if ($err != "") {
            DbManager::rollbackPoint($point);
            return $err;
        }

        foreach ($values as $k => $v) {
            $cdoc->setValue($k, $v);
        }

        $err = $cdoc->Modify();
        if ($err == "") {
            if ($this->revision > 0) {
                $this->query(sprintf("update fld set childid=%d where childid=%d", $cdoc->id, $this->initid));
            }
        }
        $this->query(sprintf("update fld set fromid=%d where childid=%d", $cdoc->fromid, $this->initid));

        $cdoc->addHistoryEntry(sprintf(_("convertion from %s to %s family"), $f1from, $f2from));

        DbManager::commitPoint($point);
        if (\Anakeen\Core\SEManager::cache()->isDocumentIdInCache($this->id)) {
            \Anakeen\Core\SEManager::cache()->addDocument($cdoc);
        }

        return $cdoc;
    }


    /**
     * record new \document or update
     *
     * @api record new \document or update it in database
     *
     * @param StoreInfo $info           refresh and postStore messages
     * @param boolean   $skipConstraint set to true to not test constraints
     *
     * @return string error message
     */
    public function store(&$info = null, $skipConstraint = false)
    {
        $constraint = [];
        $info = new StoreInfo();


        $err = $this->getHooks()->trigger(SmartHooks::PRESTORE);
        if ($err) {
            $info->preStore = $err;
            $info->error = $err;
            $info->errorCode = StoreInfo::PRESTORE_ERROR;
            return $err;
        }
        if (!$skipConstraint) {
            $err = $this->verifyAllConstraints(false, $constraint);
        }
        if ($err == '') {
            $create = false;
            if (!$this->isAffected()) {
                $err = $this->add();
                $create = true;
            }
            if ($err == '') {
                $this->lastRefreshError = '';
                $this->disableAccessControl();
                $info->refresh = $this->refresh();
                $this->restoreAccessControl();
                $err = $this->lastRefreshError;
                if ($err) {
                    $info->errorCode = StoreInfo::UPDATE_ERROR;
                } else {
                    if ($this->hasChanged) {
                        //in case of change in postStore
                        $err = $this->modify();
                        if ($err) {
                            $info->errorCode = StoreInfo::UPDATE_ERROR;
                        }
                    }
                    if ($err == "" && (!$create)) {
                        $this->addHistoryEntry(_("save document"), \DocHisto::INFO, "MODIFY");
                    }
                    if (!$err) {
                        /**
                         * is not the postUpdate method
                         */
                        $info->postStore = $this->getHooks()->trigger(SmartHooks::POSTSTORE);
                        if ($this->hasChanged) {
                            $err = $this->modify();
                            if ($err) {
                                $info->errorCode = StoreInfo::UPDATE_ERROR;
                            }
                        }
                    }
                }
            } else {
                $info->errorCode = StoreInfo::CREATE_ERROR;
            }
        } else {
            $info->errorCode = StoreInfo::CONSTRAINT_ERROR;
        }
        $info->constraint = $constraint;
        $info->error = $err;
        return $err;
    }

    /**
     * test if the document can be modified by the current user
     * the document is not need to be locked
     *
     * @param bool $verifyDomain
     *
     * @return string empty means user can update else message of the raison
     */
    public function canEdit($verifyDomain = true)
    {
        if ($this->locked == -1) {
            $err = sprintf(
                _("cannot update file %s (rev %d) : fixed. Get the latest version"),
                $this->title,
                $this->revision
            );
            return ($err);
        }

        if (!$this->isUnderControl()) {
            return "";
        } // admin can do anything but not modify fixed doc
        if ($verifyDomain && ($this->lockdomainid > 0)) {
            $err = sprintf(_("document is booked in domain %s"), $this->getTitle($this->lockdomainid));
        } else {
            if (!$this->isUnderControl()) {
                return "";
            } // no more test if disableAccessControl activated
            if (($this->locked != 0) && (abs($this->locked) != ContextManager::getCurrentUser()->id)) {
                $user = new \Anakeen\Core\Account("", abs($this->locked));
                if ($this->locked < -1) {
                    $err = sprintf(
                        _("Document %s is in edition by %s."),
                        $this->getTitle(),
                        $user->firstname . " " . $user->lastname
                    );
                } else {
                    $err = sprintf(
                        _("you are not allowed to update the file %s (rev %d) is locked by %s."),
                        $this->getTitle(),
                        $this->revision,
                        $user->firstname . " " . $user->lastname
                    );
                }
            } else {
                $err = $this->controlAccess("edit");
            }
        }
        return ($err);
    }

    /**
     * test if the document can be locked
     * it is not locked before, and the current user can edit document
     *
     * @return string empty means user can update else message of the raison
     */
    final public function canLockFile()
    {
        $err = $this->canEdit();
        return $err;
    }

    /**
     * @see \Anakeen\Core\Internal\SmartElement::canUnLock
     * @return boolean true if current user can lock file
     */
    public function canLock()
    {
        return ($this->canLockFile() == "");
    }

    /**
     * @see \Anakeen\Core\Internal\SmartElement::canLock
     * @return bool true if current user can lock file
     */
    public function canUnLock()
    {
        return ($this->CanUnLockFile() == "");
    }

    /**
     * test if the document can be unlocked
     *
     * @see \Anakeen\Core\Internal\SmartElement::CanLockFile()
     * @see \Anakeen\Core\Internal\SmartElement::canEdit()
     * @return string empty means user can update else message of the raison
     */
    final public function canUnLockFile()
    {
        if (ContextManager::getCurrentUser()->id == \Anakeen\Core\Account::ADMIN_ID) {
            return "";
        } // admin can do anything
        $err = "";
        if ($this->locked != 0) { // if is already unlocked
            if ($this->profid > 0) {
                $err = $this->Control("unlock");
            } // first control unlock privilege
            else {
                $err = _("cannot unlock");
            } // not control unlock if the document is not controlled
        }
        if ($err != "") {
            $err = $this->canEdit();
        } else {
            $err = $this->Control("edit");
            if ($err != "") {
                if ($this->profid > 0) {
                    $err = $this->Control("unlock");
                }
            }
        }
        return ($err);
    }

    /**
     * test if the document is locked
     *
     * @see \Anakeen\Core\Internal\SmartElement::canLockFile()
     *
     * @param bool $my if true test if it is lock of current user
     *
     * @return bool true if locked. If $my return true if it is locked by another user
     */
    final public function isLocked($my = false)
    {
        if ($my) {
            if (ContextManager::getCurrentUser()->id == 1) {
                if ($this->locked == 1) {
                    return false;
                }
            } elseif (abs($this->locked) == ContextManager::getCurrentUser()->id) {
                return false;
            }
        }
        return (($this->locked > 0) || ($this->locked < -1));
    }

    /**
     * test if the document is confidential
     *
     * @return bool true if confidential and current user is not authorized
     */
    final public function isConfidential()
    {
        return (($this->confidential > 0) && ($this->accessControl()->controlId($this->profid, 'confidential') != ""));
    }


    /**
     * return the family document where the document comes from
     *
     * @api return family odcument
     * @return \Anakeen\Core\SmartStructure
     */
    final public function getFamilyDocument()
    {
        /**
         * @var \Anakeen\Core\SmartStructure $famdoc
         */
        static $famdoc = null;
        if (($famdoc === null) || ($famdoc->id != $this->fromid)) {
            $famdoc = SEManager::getFamily($this->fromid);
        }
        if (!$famdoc) {
            $famdoc = new \Anakeen\Core\SmartStructure();
        }
        return $famdoc;
    }


    /**
     * return family parameter
     *
     * @deprecated use {@link \Anakeen\Core\Internal\SmartElement::getFamilyParameterValue} instead
     * @see        \Anakeen\Core\Internal\SmartElement::getFamilyParameterValue
     *
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     *
     * @return string parameter value
     */
    public function getParamValue($idp, $def = "")
    {
        return $this->getFamilyParameterValue($idp, $def);
    }

    /**
     * return family parameter
     *
     * @api  return family parameter value
     *
     * @param string $idp parameter identifier
     * @param string $def default value if parameter not found or if it is null
     *
     * @note The value of parameter can come from inherited family if its own value is empty.
     * The value of parameter comes from default configuration value if no one value are set in its family
     * or in a parent family.
     * the default configuration value comes from inherited family if no default configuration.
     * In last case, if no values and no configurated default values, the $def argument is returned
     * @return string parameter value
     */
    public function getFamilyParameterValue($idp, $def = "")
    {
        if (isset($this->_paramValue[$idp])) {
            return $this->_paramValue[$idp];
        }
        $r = $this->getParameterFamilyRawValue($idp, $def);
        /* @var \Anakeen\Core\SmartStructure\NormalAttribute $paramAttr */
        $paramAttr = $this->getAttribute($idp);
        if (!$paramAttr) {
            return $def;
        }
        if ($paramAttr->phpfunc != "" && $paramAttr->phpfile == "" && $paramAttr->type !== "enum") {
            $this->_paramValue[$idp] = $r;
            if ($paramAttr->inArray()) {
                $attributes_array = $this->attributes->getArrayElements($paramAttr->fieldSet->id);
                $max = 0;
                foreach ($attributes_array as $attr) {
                    $count = count($this->rawValueToArray($this->getFamilyParameterValue($attr->id)));
                    if ($count > $max) {
                        $max = $count;
                    }
                }
                $tmpVal = "";
                for ($i = 0; $i < $max; $i++) {
                    $val = $this->applyMethod($paramAttr->phpfunc, "", $i);
                    if ($val != $paramAttr->phpfunc) {
                        if ($tmpVal) {
                            $tmpVal .= "\n";
                        }
                        $tmpVal .= $val;
                    }
                }
                $r = $tmpVal;
            } else {
                $val = $this->getValueMethod($paramAttr->phpfunc);
                if ($val != $paramAttr->phpfunc) {
                    $r = $val;
                }
            }
        } elseif ($r) {
            $this->_paramValue[$idp] = $r;
            $r = $this->getValueMethod($r);
        }
        $this->_paramValue[$idp] = $r;
        return $r;
    }

    protected function getParameterFamilyRawValue($idp, $def)
    {
        if (!$this->fromid) {
            return false;
        }
        $fdoc = $this->getFamilyDocument();
        if (!$fdoc->isAlive()) {
            $r = false;
        } else {
            $r = $fdoc->getParameterRawValue($idp, $def);
        }
        return $r;
    }

    /**
     * return similar documents
     *
     * @param string $key1 first attribute id to perform search
     * @param string $key2 second attribute id to perform search
     *
     * @return \Anakeen\Core\Internal\SmartElement [] similar documents
     */
    final public function getDocWithSameTitle($key1 = "title", $key2 = "")
    {
        $s = new \SearchDoc($this->dbaccess, $this->fromid);
        $s->overrideViewControl();
        $s->addFilter("doctype != 'T'");
        if ($this->initid > 0) {
            $s->addFilter("initid != %d", $this->initid);
        }
        $s->addFilter("%s=E'%s'", $key1, $this->getRawValue($key1));
        if ($key2 != "") {
            $s->addFilter("%s=E'%s'", $key2, $this->getRawValue($key2));
        }
        $s->setObjectReturn(true);
        $s->search();
        $dl = $s->getDocumentList();
        $t = array();
        foreach ($dl as $doc) {
            $t[] = clone ($doc);
        }
        return ($t);
    }

    /**
     * return the latest revision id with the indicated state
     * For the user the document is in the trash
     *
     * @param string $state wanted state
     * @param bool   $fixed set to true if not search in current state
     *
     * @return int document id (0 if no found)
     */
    final public function getRevisionState($state, $fixed = false)
    {
        $ldoc = $this->GetRevisions("TABLE");
        $vdocid = 0;

        foreach ($ldoc as $k => $v) {
            if ($v["state"] == $state) {
                if ((($v["locked"] == -1) && $fixed) || (!$fixed)) {
                    $vdocid = $v["id"];
                    break;
                }
            }
        }
        return $vdocid;
    }

    // --------------------------------------------------------------------
    final public function deleteTemporary()
    {
        // --------------------------------------------------------------------
        pg_query($this->initDbid(), "delete from doc where doctype='T'");
    }

    /**
     * Control if the doc can be deleted
     *
     * @access private
     * @return string error message, if no error empty string
     * @see    \Anakeen\Core\Internal\SmartElement::Delete()
     */
    public function controlDeleteAccess()
    {
        if ($this->doctype == 'Z') {
            return _("already deleted");
        }
        if ($this->isLocked(true)) {
            return _("locked");
        }
        if ($this->lockdomainid > 0) {
            return sprintf(_("document is booked in domain %s"), $this->getTitle($this->lockdomainid));
        }
        $err = $this->controlAccess("delete");

        return $err;
    }


    /**
     * Really delete document from database
     *
     * @param bool $nopost set to true if no need tu call postDelete methods
     *
     * @return string error message, if no error empty string
     */
    final private function _destroy($nopost)
    {
        $err = \Anakeen\Core\Internal\DbObj::delete($nopost);
        if ($err == "") {
            $dvi = new \DocVaultIndex($this->dbaccess);
            $err = $dvi->DeleteDoc($this->id);
            if ($this->name != '') {
                $this->query(sprintf("delete from docname where name='%s'", pg_escape_string($this->name)));
            }
            $this->query(sprintf("delete from docfrom where id='%s'", pg_escape_string($this->id)));
        }
        return $err;
    }

    /**
     * Set the document to zombie state
     * For the user the document is in the trash
     *
     * @api Delete document
     *
     * @param bool $really  if true  really delete from database
     * @param bool $control if false don't control 'delete' acl
     * @param bool $nopost  if true don't call {@link \Anakeen\Core\Internal\SmartElement::postDelete} and {@link \Anakeen\Core\Internal\SmartElement::preDelete}
     *
     * @return string error message
     */
    final public function delete($really = false, $control = true, $nopost = false)
    {
        $err = '';
        if ($control) {
            // Control if the doc can be deleted
            $err = $this->controlDeleteAccess();
            if ($err != '') {
                return $err;
            }
        }

        if ($really) {
            if ($this->id != "") {
                // delete all revision also
                global $_SERVER;
                $this->addHistoryEntry(
                    sprintf(
                        ___("Destroyed by route from %s", "sde"),
                        isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'bash mode'
                    ),
                    \DocHisto::NOTICE
                );
                $this->addHistoryEntry(_("Document destroyed"), \DocHisto::MESSAGE, "DELETE");
                $this->addLog('delete', array(
                    "really" => $really
                ));
                $rev = $this->GetRevisions();
                foreach ($rev as $k => $v) {
                    $v->_destroy($nopost);
                }
            }
        } else {
            // Control if the doc can be deleted
            if ($this->doctype == 'Z') {
                $err = _("already deleted");
            }
            if ($err != '') {
                return $err;
            }

            if (!$nopost) {
                $this->inHook = true;
                $err = $this->getHooks()->trigger(SmartHooks::PREDELETE);
                $this->inHook = false;
                if ($err != '') {
                    return $err;
                }
            }

            if ($this->doctype != 'Z') {
                if ($this->name != "") {
                    $this->query(sprintf(
                        "delete from doc%d where name='%s' and doctype='Z'",
                        $this->fromid,
                        pg_escape_string($this->name)
                    ));
                } // need to not have twice document with same name
                $this->doctype = 'Z'; // Zombie Doc
                $this->locked = -1;
                $this->lmodify = 'D'; // indicate last delete revision

                $this->mdate = Date::getNow(true);

                global $_SERVER;

                $this->addHistoryEntry(sprintf(
                    ___("Delete by route from %s", "sde"),
                    isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : 'bash mode'
                ), \DocHisto::NOTICE);
                $this->addHistoryEntry(_("document deleted"), \DocHisto::MESSAGE, "DELETE");
                $this->addLog('delete', array(
                    "really" => $really
                ));

                $err = $this->modify(true, array(
                    "doctype",
                    "mdate",
                    "locked",
                    "owner",
                    "lmodify"
                ), true);
                if ($err == "") {
                    if (!$nopost) {
                        $this->inHook = true;
                        $msg = $this->getHooks()->trigger(SmartHooks::POSTDELETE);
                        $this->inHook = false;
                        if ($msg != '') {
                            $this->addHistoryEntry($msg, \DocHisto::MESSAGE);
                        }
                    }
                    // delete all revision also
                    $rev = $this->GetRevisions();
                    foreach ($rev as $k => $v) {
                        if ($v->doctype != 'Z') {
                            $v->doctype = 'Z'; // Zombie Doc
                            if ($v->locked == -1) {
                                $v->modify(true, array(
                                    "doctype"
                                ), true);
                            }
                        }
                    }
                }
            }
        }
        return $err;
    }


    /**
     * To restore a document which is in the trash
     *
     * @api restore deleted document
     * @see \Anakeen\Core\Internal\SmartElement::delete
     * @return string error message (empty message if no errors);
     */
    final public function undelete()
    {
        if (($this->controlAccess('delete') == "") || (ContextManager::getCurrentUser()->id == 1)) {
            if (!$this->isAlive()) {
                $err = $this->getHooks()->trigger(SmartHooks::PREUNDELETE);
                if ($err) {
                    return $err;
                }
                DbManager::query(sprintf(
                    "SELECT id from only doc%d where initid = %d order by id desc limit 1",
                    $this->fromid,
                    $this->initid
                ), $latestId, true, true);

                if (!$latestId) {
                    $err = sprintf(_("document %s [%d] is strange"), $this->title, $this->id);
                } else {
                    $previousName = $this->name;
                    $this->doctype = $this->defDoctype;
                    $this->locked = 0;
                    $this->id = $latestId;
                    $this->name = '';
                    $this->lmodify = 'Y'; // indicate last restoration
                    $this->modify(true, array(
                        "doctype",
                        "locked",
                        "lmodify",
                        "name"
                    ), true);
                    $this->addHistoryEntry(_("revival document"), \DocHisto::MESSAGE, "REVIVE");
                    $msg = $this->getHooks()->trigger(SmartHooks::POSTUNDELETE);
                    if ($msg) {
                        $this->addHistoryEntry($msg, \DocHisto::MESSAGE);
                    }
                    $this->addLog('undelete');
                    $rev = $this->getRevisions();
                    /**
                     * @var \Anakeen\Core\Internal\SmartElement $v
                     */
                    foreach ($rev as $k => $v) {
                        if ($v->doctype == 'Z') {
                            $v->doctype = $v->defDoctype;
                            $v->name = '';
                            $err .= $v->modify(true, array(
                                "doctype",
                                "name"
                            ), true);
                        }
                    }
                    if ($previousName) {
                        // Reaffect logical name if can be
                        $this->setLogicalName($previousName);
                    }
                }
            } else {
                $err = sprintf(_("document %s [%d] is not in the trash"), $this->getTitle(), $this->id);
            }
        } else {
            $err = sprintf(_("need privilege delete to restore %s"), $this->getTitle());
        }
        return $err;
    }

    /**
     * Adaptation of affect Method from DbObj because of inheritance table
     * this function is call from QueryDb and all fields can not be instanciate
     *
     * @param array $array the data array
     * @param bool  $more  add values from values attributes needed only if cast document
     * @param bool  $reset reset all values before set and clean private variables
     *
     * @return void
     */
    final public function affect($array, $more = false, $reset = true)
    {
        if (is_array($array)) {
            $this->getHooks()->resetListeners();

            $this->inHook = true;
            $this->getHooks()->trigger(SmartHooks::PREAFFECT, $array, $more, $reset);

            if ($more) {
                $this->resetMoreValues();
            }
            if ($reset) {
                foreach ($this->fields as $key) {
                    $this->$key = null;
                }
            }
            unset($this->uperm); // force recompute privileges

            foreach ($array as $k => $v) {
                if (!is_integer($k)) {
                    $this->$k = $v;
                }
            }
            $this->complete();
            if ($more) {
                $this->getMoreValues();
            }

            if ($reset) {
                $this->_maskApplied = false;
                $this->_oldvalue = array();
                $this->_paramValue = array();
                $this->_setValueCompleteArray = false;
                $this->childs = null;
                $this->constraintbroken = false;
                $this->fathers = null;
                $this->hasChanged = false;
                $this->htmlFormater = null;
                $this->lastRefreshError = '';
                $this->mvalues = array();
                $this->oooFormater = null;
                $this->formaterLevel = 0;
                $this->otherFormatter = array();
                $this->mid = 0;
                $this->svalues = null;
                $this->vidNoSendTextToEngine = array();
                $this->textsend = array();
            }
            $this->isset = true;
            $this->getHooks()->trigger(SmartHooks::POSTAFFECT, $array, $more, $reset);
            $this->inHook = false;
        }
    }

    /**
     * Set to default values before add new \doc
     *
     * @return void
     */
    public function init()
    {
        $this->isset = false;
        $this->id = "";
        $this->initid = "";
        $nattr = $this->GetNormalAttributes();
        foreach ($nattr as $k => $v) {
            if (isset($this->$k) && ($this->$k != "")) {
                $this->$k = "";
            }
        }
    }

    // --------------------------------------------------------------------
    public function description()
    {
        // --------------------------------------------------------------------
        return $this->title . " - " . $this->revision;
    }

    /**
     * use for system purpose
     *  prefer ::getFromDoc instead
     *
     * @return int[]
     */
    final public function getFathersDoc()
    {
        // --------------------------------------------------------------------
        // Return array of father doc id : class document
        if ($this->fathers === null) {
            $this->fathers = array();
            if ($this->fromid > 0) {
                $fdoc = $this->getFamilyDocument();
                $this->fathers = $fdoc->GetFathersDoc();
                array_push($this->fathers, $this->fromid);
            }
        }
        return $this->fathers;
    }

    /**
     * Return array of fathers doc id : class document
     *
     * @return int[]
     */
    final public function getFromDoc()
    {
        return $this->attributes->fromids;
    }

    /**
     * Return array of child family raw documents
     *
     * @param int  $id            if -1 use child for current document else for the family identifier set
     * @param bool $controlcreate set to true to not return documents which cannot be created by current user
     *
     * @return array raw docfam values
     */
    final public function getChildFam($id = -1, $controlcreate = false)
    {
        if ($id == 0) {
            return array();
        }
        if (($id != -1) || (!isset($this->childs))) {
            if ($id == -1) {
                $id = $this->id;
            }
            if ($id == 0) {
                return array();
            }
            if (!isset($this->childs)) {
                $this->childs = array();
            }

            $s = new \SearchDoc($this->dbaccess, -1);
            $s->addFilter("fromid = %d", $id);
            $s->overrideViewControl();
            $table1 = $s->search();
            if ($table1) {
                foreach ($table1 as $k => $v) {
                    if ((!$controlcreate) || controlTdoc($v, "icreate")) {
                        $this->childs[$v["id"]] = $v;
                    }
                    $this->GetChildFam($v["id"], $controlcreate);
                }
            }
        }
        return $this->childs;
    }

    /**
     * return all revision documents
     *
     * @param string $type  LIST|TABLE il LIST return \Anakeen\Core\Internal\SmartElement object else if TABLE raw documents
     * @param int    $limit limit of revision (by default the 200 latest revisions)
     *
     * @return \Anakeen\Core\Internal\SmartElement []|array
     */
    final public function getRevisions($type = "LIST", $limit = 200)
    {
        // Return the document revision
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, strtolower(get_class($this)));
        //$query->AddQuery("revision <= ".$this->revision);
        $query->AddQuery("initid = " . $this->initid);
        $query->order_by = "revision DESC LIMIT $limit";

        $rev = $query->Query(0, 0, $type);
        if ($query->nb == 0) {
            return array();
        }
        return $rev;
    }


    /** get Latest Id of document
     *
     * @api get latest id of document
     *
     * @param bool $fixed      if true latest fixed revision
     * @param bool $forcequery if true force recompute of id (use it in case of modification by another program)
     *
     * @return int identifier of latest revision
     */
    final public function getLatestId($fixed = false, $forcequery = false)
    {
        if ($this->id == "") {
            return false;
        }
        if (!$forcequery) {
            if (($this->locked != -1) && (!$fixed)) {
                return $this->id;
            }
            if ($fixed && ($this->lmodify == "L")) {
                return $this->id;
            }
        }
        if (!$fixed) {
            return getLatestDocId($this->dbaccess, $this->initid);
        }
        $query = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, strtolower(get_class($this)));
        $query->AddQuery("initid = " . $this->initid);
        if ($fixed) {
            $query->AddQuery("lmodify = 'L'");
        } elseif ($this->doctype != 'Z') {
            $query->AddQuery("locked != -1");
        } else {
            $query->order_by = "id desc";
        }
        $rev = $query->Query(0, 2, "TABLE");

        if ($this->doctype != 'Z') {
            if (count($rev) > 1) {
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf("document %d : multiple alive revision", $this->initid));
            }
        }
        return $rev[0]["id"];
    }

    /**
     * get version of document
     * can be redefined by child document classes if needed
     *
     * @return string
     */
    public function getVersion()
    {
        $tversion = array();
        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                if ($v->isNormal && ($v->getOption("version") == "yes")) {
                    $tversion[] = $this->getRawValue($v->id);
                }
            }
        }
        if (count($tversion) > 0) {
            $version = implode(" ", $tversion);
        } else {
            $version = $this->version;
        }
        return $version;
    }

    /**
     * return the string label text for a id (label depends of current user locale)
     *
     * @param string $idAttr attribute identifier
     *
     * @return string
     */
    final public function getLabel($idAttr)
    {
        if (isset($this->attributes->attr[$idAttr])) {
            return $this->attributes->attr[$idAttr]->getLabel();
        }
        return _("unknow attribute");
    }


    /**
     * return the property value like id, initid, revision, ...
     *
     * @api get property value
     *
     * @param string $prop property identifier
     *
     * @return string false if not an property
     */
    final public function getPropertyValue($prop)
    {
        $prop = trim(strtolower($prop));
        if (!in_array($prop, $this->fields)) {
            return false;
        }
        if (isset($this->fields[$prop])) {
            return false;
        } // it's an attribute
        return $this->$prop;
    }

    /**
     * Return the tag object for the document
     *
     * @throws \Dcp\Exception
     * @return \TagManager &$tag object reference use to modify tags
     */
    final public function &tag()
    {
        /**
         * @var \TagManager $tag
         */
        static $tag = null;

        if (empty($tag) || $tag->docid != $this->initid) {
            if (class_exists("TagManager")) {
                $tag = new \TagManager($this, $this->initid);
            } else {
                throw new \Dcp\Exception("Need install dynacase-tags module.\n");
            }
        }
        return $tag;
    }

    /**
     * return the attribute object for a id
     * the attribute can be defined in fathers
     *
     * @api get attribute object
     *
     * @param string                                      $idAttr attribute identifier
     * @param \Anakeen\Core\SmartStructure\BasicAttribute &$oa    object reference use this if want to modify attribute
     *
     * @return \Anakeen\Core\SmartStructure\BasicAttribute|bool|\Anakeen\Core\SmartStructure\NormalAttribute
     */
    final public function &getAttribute($idAttr, &$oa = null)
    {
        if ($idAttr !== \Anakeen\Core\SmartStructure\Attributes::HIDDENFIELD) {
            $idAttr = strtolower($idAttr);
        }

        if (isset($this->attributes->attr[$idAttr])) {
            $oa = $this->attributes->attr[$idAttr];
        } else {
            $oa = false;
        }

        return $oa;
    }

    /**
     * return all the attributes object
     * the attribute can be defined in fathers
     *
     *
     * @return \Anakeen\Core\SmartStructure\BasicAttribute[]
     */
    final public function &getAttributes()
    {
        $fromname = ($this->doctype == 'C') ? $this->name : $this->fromname;
        $aFromName = isset($this->attributes->fromname) ? $this->attributes->fromname : '';
        if ($aFromName != $fromname) {
            // reset when use partial cache
            $adocClassName = \Anakeen\Core\SEManager::getAttributesClassName($fromname);
            // Workaround because autoload has eventually the class in its missing private key
            // Use file_exists instead class_exists
            $attFileClass = \Anakeen\Core\SEManager::getAttributesClassFilename($this->name);
            if (file_exists($attFileClass)) {
                $this->attributes = new $adocClassName();
            }
        }

        return $this->attributes->attr;
    }

    /**
     * return all the attributes except frame & menu & action
     *
     * @param boolean $onlyopt get only optional attributes
     *
     * @return \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    final public function getNormalAttributes($onlyopt = false)
    {
        if ((isset($this->attributes)) && (method_exists($this->attributes, "GetNormalAttributes"))) {
            return $this->attributes->GetNormalAttributes($onlyopt);
        } else {
            return array();
        }
    }

    /**
     * return  frame attributes
     *
     * @return  \Anakeen\Core\SmartStructure\FieldSetAttribute[]
     */
    final public function getFieldAttributes()
    {
        $tsa = array();

        foreach ($this->attributes->attr as $k => $v) {
            if (is_object($v) && get_class($v) == \Anakeen\Core\SmartStructure\FieldSetAttribute::class) {
                $tsa[$v->id] = $v;
            }
        }
        return $tsa;
    }


    /**
     * return all the attributes object for abstract
     * the attribute can be defined in fathers
     *
     * @return  \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    final public function getAbstractAttributes()
    {
        $tsa = array();

        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var \Anakeen\Core\SmartStructure\NormalAttribute $v
                 */
                if (is_object($v) && $v->isNormal && ($v->usefor != 'Q') && ($v->isInAbstract)) {
                    $tsa[$v->id] = $v;
                }
            }
        }
        return $tsa;
    }

    /**
     * return all the attributes object for title
     * the attribute can be defined in fathers
     *
     * @return  \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    final public function getTitleAttributes()
    {
        $tsa = array();
        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var \Anakeen\Core\SmartStructure\NormalAttribute $v
                 */
                if (is_object($v) && $v->isNormal && ($v->isInTitle)) {
                    $tsa[$v->id] = $v;
                }
            }
        }
        return $tsa;
    }

    /**
     * return all the attributes that can be use in profil
     *
     * @return  \Anakeen\Core\SmartStructure\BasicAttribute[]
     */
    final public function getProfilAttributes()
    {
        $tsa = array();
        $tsb = array();
        $wopt = false;
        if (isset($this->attributes->attr)) {
            foreach ($this->attributes->attr as $k => $v) {
                if ($v->type == "docid") {
                    if ($v->getOption("isuser") != "") {
                        if ($v->getOption("isuser") == "yes") {
                            $tsb[$v->id] = $v;
                        }
                        $wopt = true;
                    }
                } elseif ($v->type == "account") {
                    $wopt = true;
                    if ($v->getOption("isuser") != "no") {
                        $tsb[$v->id] = $v;
                    }
                }
            }
        }
        if ($wopt) {
            return $tsb;
        }
        return $tsa;
    }


    /**
     * return all the parameters definition for its family
     * the attribute can be defined in fathers
     *
     * @return  \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    final public function getParamAttributes()
    {
        if ((isset($this->attributes)) && (method_exists($this->attributes, "getParamAttributes"))) {
            return $this->attributes->getParamAttributes();
        } else {
            return array();
        }
    }

    /**
     * return all the attributes object for abstract
     * the attribute can be defined in fathers
     *
     * @param bool $onlyfile set to true if don't want images
     *
     * @return  \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    final public function getFileAttributes($onlyfile = false)
    {
        $tsa = array();

        foreach ($this->attributes->attr as $k => $v) {
            if (is_object($v) && $v->isNormal && ($v->usefor != 'Q')
                && ((($v->type == "image") && (!$onlyfile))
                    || ($v->type == "file"))) {
                $tsa[$v->id] = $v;
            }
        }
        return $tsa;
    }

    /**
     * return files properties of file attributes
     *
     * @return array
     */
    final public function getFilesProperties()
    {
        $dvi = new \DocVaultIndex($this->dbaccess);
        $tvid = $dvi->getVaultIds($this->id);
        $tinfo = array();
        $vf = newFreeVaultFile($this->dbaccess);
        foreach ($tvid as $vid) {
            $info = null;
            $err = $vf->Retrieve($vid, $info);
            $t = get_object_vars($info);
            $t["vid"] = $vid;
            if ($err == "") {
                $tinfo[] = $t;
            }
        }

        return $tinfo;
    }

    /**
     * verify if has some files waiting conversion
     *
     * @return bool
     */
    final public function hasWaitingFiles()
    {
        if (!\Anakeen\Core\Internal\Autoloader::classExists('Dcp\TransformationEngine\Client')) {
            return false;
        }
        $dvi = new \DocVaultIndex($this->dbaccess);
        $tvid = $dvi->getVaultIds($this->id);
        if (count($tvid) == 0) {
            return false;
        }
        $sql = sprintf(
            "select id_file from vaultdiskstorage where teng_state=%d and %s limit 1",
            \Dcp\TransformationEngine\Client::status_waiting,
            DbManager::getSqlOrCond($tvid, "id_file", true)
        );
        DbManager::query($sql, $waiting, true, true);
        return ($waiting != false);
    }

    /**
     * reset Conversion of file
     * update $attrid_txt table column
     *
     * @param string $attrid file attribute identifier
     * @param int    $index  index in case of multiple attribute
     *
     * @apiExpose
     * @return string error message
     */
    public function resetConvertVaultFile($attrid, $index)
    {
        $err = $this->canEdit();
        if ($err) {
            return $err;
        }
        $val = $this->getMultipleRawValues($attrid, false, $index);
        if (($index == -1) && (count($val) == 1)) {
            $val = $val[0];
        }

        if ($val) {
            $info = $this->getFileInfo($val);
            if ($info) {
                $ofout = new \VaultDiskStorage($this->dbaccess, $info["id_file"]);
                if ($ofout->isAffected()) {
                    $err = $ofout->delete();
                }
            }
        }
        return $err;
    }

    /**
     * send a request to TE to convert files
     * update $attrid_txt table column
     * waiting end of conversion
     *
     * @param string $va      value of file attribute like mime|vid|name
     * @param string $engine  the name of transformation
     * @param bool   $isimage set true if it is an image (error returns is not same)
     *
     * @return string new \file reference
     */
    public function convertVaultFile($va, $engine, $isimage = false)
    {

        $engine = strtolower($engine);
        $value = '';
        if (is_array($va)) {
            return "";
        }
        $err = '';
        if (ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "TE_ACTIVATE") == "yes"
            && \Anakeen\Core\Internal\Autoloader::classExists('Dcp\TransformationEngine\Client')) {
            if (preg_match(PREGEXPFILE, $va, $reg)) {
                $vidin = $reg[2];
                $vidout = 0;
                $info = \Dcp\VaultManager::getFileInfo($vidin, $engine);
                // in case of server not reach : try again
                if (!is_object($info)) {
                    // not found : create it
                    $info = new \VaultFileInfo();
                }
                if ($info->teng_state == \Dcp\TransformationEngine\Client::error_connect) {
                    $info->teng_state = \Dcp\TransformationEngine\Client::status_inprogress;
                }
                if ((!$info->teng_vid) || ($info->teng_state == \Dcp\TransformationEngine\Client::status_inprogress)) {
                    $vf = newFreeVaultFile($this->dbaccess);
                    if (!$info->teng_vid) {
                        // create temporary file
                        $value = sprintf(_("conversion %s in progress"), $engine);
                        if ($isimage) {
                            $filename = DEFAULT_PUBDIR . "/Images/workinprogress.png";
                        } else {
                            $filename = uniqid(ContextManager::getTmpDir() . "/conv") . ".txt";
                        }
                        file_put_contents($filename, $value);
                        $vidout = 0;
                        $err = $vf->Store($filename, false, $vidout, "", $engine, $vidin);
                        if ($err) {
                            $this->addHistoryEntry(sprintf(
                                _("convert file %s as %s failed : %s"),
                                $info->name,
                                $engine,
                                $err
                            ), \DocHisto::ERROR);
                            error_log($err);
                            return '';
                        }
                        $info = \Dcp\VaultManager::getFileInfo($vidin);
                        if (!$isimage) {
                            unlink($filename);
                            $mime = 'text/plain';
                        } else {
                            $mime = 'image/png';
                        }

                        $value = "$mime|$vidout";
                        if ($err == "") {
                            $vf->rename(
                                $vidout,
                                sprintf(_("conversion of %s in progress") . ".%s", $info->name, $engine)
                            );
                        }

                        $this->addHistoryEntry("value $engine : $value");
                        /* Do not index temporary vid "vidout" while waiting for transformation result */
                        $this->vidNoSendTextToEngine[$vidout] = true;
                    } else {
                        if ($err == "") {
                            $info1 = \Dcp\VaultManager::getFileInfo($vidin);
                            $vidout = $info->id_file;
                            $vf->rename($vidout, sprintf(_("update of %s in progress") . ".%s", $info1->name, $engine));
                            $value = $info->mime_s . '|' . $info->id_file;
                        }
                    }

                    $err = vault_generate($this->dbaccess, $engine, $vidin, $vidout, $isimage, $this->initid);
                    if ($err != "") {
                        $this->addHistoryEntry(sprintf(
                            _("convert file %s as %s failed : %s"),
                            $info->name,
                            $engine,
                            $err
                        ), \DocHisto::ERROR);
                    }
                } else {
                    if ($isimage) {
                        if ($info->teng_state < 0) {
                            if ($info->teng_state == \Dcp\TransformationEngine\Client::error_convert) {
                                $value = "convertfail.png";
                            } else {
                                $value = "convertimpossible.png";
                            }
                        } else {
                            if ($info->teng_state == \Dcp\TransformationEngine\Client::status_done) {
                                $value = $info->mime_s . '|' . $info->id_file . '|' . $info->name;
                            }
                        }
                    } else {
                        $value = $info->mime_s . '|' . $info->id_file . '|' . $info->name;
                    }
                    /* Do not index vid with failed or pending transformations */
                    if ($info->teng_state != \Dcp\TransformationEngine\Client::status_done) {
                        $this->vidNoSendTextToEngine[$info->id_file] = true;
                    }
                }
            }
        }
        return $value;
    }


    /**
     * return all the necessary attributes
     *
     * @param bool $parameters set to true if want parameters instead of attributes
     *
     * @return \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    final public function getNeededAttributes($parameters = false)
    {
        $tsa = array();

        if ($parameters) {
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var \Anakeen\Core\SmartStructure\NormalAttribute $v
                 */
                if (is_object($v) && $v->isNormal && ($v->needed) && ($v->usefor == 'Q')) {
                    $tsa[$v->id] = $v;
                }
            }
        } else {
            foreach ($this->attributes->attr as $k => $v) {
                /**
                 * @var \Anakeen\Core\SmartStructure\NormalAttribute $v
                 */
                if (is_object($v) && $v->isNormal && ($v->needed) && ($v->usefor != 'Q')) {
                    $tsa[$v->id] = $v;
                }
            }
        }
        return $tsa;
    }

    /**
     * verify if all needed attributes are set
     *
     * @return string error message if some needed attributes are empty
     */
    final public function isCompleteNeeded()
    {
        $tsa = $this->GetNeededAttributes();
        $err = "";
        foreach ($tsa as $k => $v) {
            if ($v->inArray()) {
                /* Check for empty cells in columns */
                $columnValues = $this->getMultipleRawValues($v->id);
                foreach ($columnValues as $value) {
                    if ($value == "") {
                        $err .= sprintf(_("%s needed\n"), $v->getLabel());
                        /* Do not report multiple errors for the same column */
                        break;
                    }
                }
            } else {
                if ($this->getRawValue($v->id) == "") {
                    $err .= sprintf(_("%s needed\n"), $v->getLabel());
                }
            }
        }
        return $err;
    }

    /**
     * verify if attribute equals $b
     * to be use in constraint
     *
     * @param string $a attribute identifier
     * @param string $b value
     *
     * @return bool
     */
    final public function equal($a, $b)
    {
        return ($this->$a == $b);
    }

    /**
     * return list of attribut which can be exported
     *
     * @param bool $withfile     true if export also file attribute
     * @param bool $forcedefault if true preference FREEDOM_EXPORTCOLS are not read
     *
     * @return \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    final public function getExportAttributes($withfile = false, $forcedefault = false)
    {


        $tsa = array();
        if (isset($this->attributes->attr)) {
            $pref = "";

            if ((!$forcedefault) && ($pref != "")) {
                $tpref = explode(";", $pref);

                foreach ($this->attributes->attr as $k => $v) {
                    if (in_array($v->id, $tpref)) {
                        $tsa[$v->id] = $v;
                    }
                }
            } else {
                foreach ($this->attributes->attr as $k => $v) {
                    if (is_object($v) && $v->isNormal && $v->usefor != 'Q') {
                        if (($v->type != "array") && ($withfile || (($v->type != "image") && ($v->type != "file")))) {
                            $tsa[$v->id] = $v;
                        }
                    }
                }
            }
        }
        return $tsa;
    }


    /**
     * return all the attributes which can be sorted
     *
     * @return \Anakeen\Core\SmartStructure\NormalAttribute[]
     */
    public function getSortAttributes()
    {
        $tsa = array();
        $nattr = $this->GetNormalAttributes();
        reset($nattr);

        foreach ($nattr as $k => $a) {
            if ($a->repeat || ($a->access == "I") || ($a->access == "O") || ($a->type == "longtext")
                || ($a->type == "xml")
                || ($a->type == "htmltext")
                || ($a->type == "image")
                || ($a->type == "file")
                || ($a->getOption('sortable') != 'asc' && $a->getOption('sortable') != 'desc')) {
                continue;
            }
            $tsa[$a->id] = $a;
        }
        return $tsa;
    }

    /**
     * recompute the title from attribute values
     */
    final public function refreshTitle()
    {
        if ($this->doctype == 'C') {
            return;
        } // no refresh for family  document
        $ltitle = $this->GetTitleAttributes();
        $title1 = "";
        foreach ($ltitle as $k => $v) {
            if ($this->getRawValue($v->id) != "") {
                if ($v->inArray() && ($v->getOption('multiple') == 'yes')) {
                    $titles = Postgres::stringToFlatArray($this->getRawValue($v->id));
                    $title1 .= \Anakeen\Core\Utils\Strings::mb_trim(implode(" ", $titles)) . " ";
                } else {
                    $title1 .= $this->getRawValue($v->id) . " ";
                }
            }
        }
        /* Replace control chars with spaces, and limit title to 256 chars */
        if (\Anakeen\Core\Utils\Strings::mb_trim($title1) != "") {
            $this->title = mb_substr(\Anakeen\Core\Utils\Strings::mb_trim(preg_replace('/\p{Cc}/u', ' ', $title1)), 0, 255);
        }
        $this->title = mb_substr(\Anakeen\Core\Utils\Strings::mb_trim(preg_replace('/\p{Cc}/u', ' ', $this->getCustomTitle())), 0, 255);
    }


    /**
     * set attribute title value
     * the first value of type text use for title will be modify to have the new \title
     *
     * @param string $title new \title
     */
    final public function setTitle($title)
    {
        $ltitle = $this->getTitleAttributes();
        $otitle = '';
        foreach ($ltitle as $at) {
            if (($at->type == 'text') && (!$at->inArray())) {
                $otitle = $at;
                break;
            }
        }
        if ($otitle) {
            /**
             * @var \Anakeen\Core\SmartStructure\NormalAttribute $otitle
             */
            $idt = $otitle->id;

            $this->title = str_replace("\n", " ", $title);
            $this->setvalue($idt, $title);
        }
    }

    /**
     * return all attribute values
     *
     * @return array all attribute values, index is attribute identifier
     */
    final public function getValues()
    {
        $lvalues = array();
        //    if (isset($this->id) && ($this->id>0)) {
        $nattr = $this->GetNormalAttributes();
        foreach ($nattr as $k => $v) {
            $lvalues[$v->id] = $this->getRawValue($v->id);
        }
        // }
        $lvalues = array_merge($lvalues, $this->mvalues); // add more values possibilities
        reset($lvalues);
        return $lvalues;
    }
    //-------------------------------------------------------------------


    /**
     * return the raw value (database value) of an field or properties document
     *
     * @api get the value of an attribute
     *
     * @param string $idAttr attribute identifier
     * @param string $def    default value returned if attribute not found or if is empty
     *
     * @code
     * $doc = new_Doc('',7498 );
     * if ($doc->isAlive()) {
     * $rev = $doc->getPropertyValue('revision');
     * $order = $doc->getRawValue("tst_order");
     * $level = $doc->getRawValue("tst_level","0");
     * }
     * @endcode
     * @see \Anakeen\Core\Internal\SmartElement::getAttributeValue
     * @return string the attribute value
     */
    final public function getRawValue($idAttr, $def = "")
    {
        $lidAttr = strtolower($idAttr);
        if (isset($this->$lidAttr) && ($this->$lidAttr != "")) {
            $oa = $this->getAttribute($idAttr);
            if ($oa && $this->isUnderControl() && FieldAccessManager::hasReadAccess($this, $oa) === false) {
                return $def;
            }
            return $this->$lidAttr;
        }

        return $def;
    }

    /**
     * get a typed value of an attribute
     *
     * return value of an attribute
     * return null if value is empty
     * return an array for multiple value
     * return date in DateTime format, number in int or double
     *
     * @api get typed value of an attribute
     *
     * @param string $idAttr attribute identifier
     *
     * @throws \Dcp\Exception DOC0114 code
     * @see ErrorCodeDoc::DOC0114
     * @return mixed the typed value
     */
    final public function getAttributeValue($idAttr)
    {
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
         */
        $oa = $this->getAttribute($idAttr);
        if (!$oa) {
            throw new \Dcp\Exception('DOC0114', $idAttr, $this->title, $this->fromname);
        }
        if ($this->isUnderControl() && FieldAccessManager::hasReadAccess($this, $oa) === false) {
            throw new \Dcp\Exception('DOC0133', $idAttr, $this->title, $this->fromname);
        }
        if (empty($oa->isNormal)) {
            throw new \Dcp\Exception('DOC0116', $idAttr, $this->title, $this->fromname);
        }
        return \Dcp\AttributeValue::getTypedValue($this, $oa);
    }

    /**
     * Set a value to a document's attribute
     * the affectation is only in object. To set modification in database the \Anakeen\Core\Internal\SmartElement::store() method must be
     * call after modification
     *
     * @api Set a value to an attribute
     *
     * @param string $idAttr attribute identifier
     * @param mixed  $value  the new \value - value format must be compatible with type
     *
     * @throws \Dcp\Exception
     * @see ErrorCodeDoc::DOC0115
     * @see ErrorCodeDoc::DOC0117
     * @return void
     */
    final public function setAttributeValue($idAttr, $value)
    {
        $localRecord = array();
        $oa = $this->getAttribute($idAttr);
        if (!$oa) {
            throw new \Dcp\Exception('DOC0115', $idAttr, $this->title, $this->fromname);
        }
        if (empty($oa->isNormal)) {
            throw new \Dcp\Exception('DOC0117', $idAttr, $this->title, $this->fromname);
        }
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
         */
        if ($oa->type === "array") {
            // record current array values
            $ta = $this->attributes->getArrayElements($oa->id);
            foreach ($ta as $k => $v) {
                $localRecord[$k] = $this->getRawValue($v->id);
            }
        }
        \Dcp\AttributeValue::setTypedValue($this, $oa, $value);
        if ($oa->type === "array") {
            foreach ($localRecord as $aid => $v) {
                if ($this->$aid !== $v) {
                    $this->_oldvalue[$aid] = $v;
                }
            }
        }
    }


    /**
     * return all values of a multiple value attribute
     *
     * @api return all values of a multiple value attribute
     * the attribute must be in an array or declared with multiple option
     *
     * @param string $idAttr identifier of list attribute
     * @param string $def    default value returned if attribute not found or if is empty
     * @param int    $index  the values for $index row (default value -1 means all values)
     *
     * @return array|string the list of attribute values
     */
    final public function getMultipleRawValues($idAttr, $def = [], $index = -1)
    {
        $v = $this->getRawValue($idAttr, null);
        if ($v === null) {
            if ($index == -1) {
                return array();
            } else {
                return $def;
            }
        }
        $oa = $this->getAttribute($idAttr);
        if ($oa->isMultiple() === false) {
            return [$v];
        }
        $t = $this->rawValueToArray($v);
        if ($index == -1) {
            return $t;
        }
        if (isset($t[$index])) {
            return $t[$index];
        } else {
            return $def;
        }
    }


    /**
     * return the array of values for an array attribute
     *
     * the attribute must  an array type
     *
     * @api get all values for an array attribute
     *
     * @param string $idAttr identifier of array attribute
     * @param int    $index  the values for $index row (default value -1 means all values)
     *
     * @return array|false all values of array order by rows (return false if not an array attribute)
     */
    final public function getArrayRawValues($idAttr, $index = -1)
    {
        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);
            $ti = $tv = array();
            $ix = 0;
            // transpose
            foreach ($ta as $k => $v) {
                $tv[$k] = $this->getMultipleRawValues($k);
                $ix = max($ix, count($tv[$k]));
            }
            for ($i = 0; $i < $ix; $i++) {
                $ti[$i] = array();
            }
            foreach ($ta as $k => $v) {
                for ($i = 0; $i < $ix; $i++) {
                    $ti[$i] += array(
                        $k => isset($tv[$k][$i]) ? $tv[$k][$i] : ''
                    );
                }
            }
            if ($index == -1) {
                return $ti;
            } else {
                return $ti[$index];
            }
        }
        return false;
    }

    /**
     * delete a row in an array attribute
     *
     * the attribute must an array type
     *
     * @param string $idAttr identifier of array attribute
     *
     * @api delete a row in an array attribute
     *
     * @param string $index  $index row (first is 0)
     *
     * @return string error message, if no error empty string
     */
    final public function removeArrayRow($idAttr, $index)
    {
        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);
            $err = "";
            // delete in each columns
            foreach ($ta as $k => $v) {
                $tv = $this->getMultipleRawValues($k);
                unset($tv[$index]);
                $tvu = array();
                foreach ($tv as $vv) {
                    $tvu[] = $vv;
                } // key reorder
                $err .= $this->setValue($k, $tvu);
            }
            return $err;
        }
        return sprintf(_("%s is not an array attribute"), $idAttr);
    }

    /**
     * in case of array where each column are not the same length
     *
     * the attribute must an array type
     * fill uncomplete column with null values
     *
     * @param string $idAttr              identifier of array attribute
     * @param bool   $deleteLastEmptyRows by default empty rows which are in the end are deleted
     *
     * @return string error message, if no error empty string
     */
    final public function completeArrayRow($idAttr, $deleteLastEmptyRows = true)
    {
        /* Prevent recursive calls of completeArrayRow() by setValue() */
        static $calls = array();
        if (array_key_exists(strtolower($idAttr), $calls)) {
            return '';
        } else {
            $calls[strtolower($idAttr)] = 1;
        }

        $err = '';
        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);

            $max = -1;
            $needRepad = false;
            $tValues = array();
            foreach ($ta as $k => $v) { // delete empty end values
                $tValues[$k] = $this->getMultipleRawValues($k);
                if ($deleteLastEmptyRows) {
                    $c = count($tValues[$k]);
                    for ($i = $c - 1; $i >= 0; $i--) {
                        if ($tValues[$k][$i] === '' || $tValues[$k][$i] === null) {
                            unset($tValues[$k][$i]);
                            $needRepad = true;
                        } else {
                            break;
                        }
                    }
                }
            }
            foreach ($ta as $k => $v) { // detect uncompleted rows
                $c = count($tValues[$k]);
                if ($max < 0) {
                    $max = $c;
                } else {
                    if ($c != $max) {
                        $needRepad = true;
                    }
                    if ($max < $c) {
                        $max = $c;
                    }
                }
            }
            if ($needRepad) {
                $oldComplete = $this->_setValueCompleteArray;
                $this->_setValueCompleteArray = true;
                foreach ($ta as $k => $v) { // fill uncompleted rows
                    $c = count($tValues[$k]);
                    if ($c < $max) {
                        if ($this->getAttribute($k)->isMultipleInArray()) {
                            $nt = array_pad($tValues[$k], $max, []);
                        } else {
                            $nt = array_pad($tValues[$k], $max, "");
                        }
                        $err .= $this->setValue($k, $nt);
                    } else {
                        $err .= $this->setValue($k, $tValues[$k]);
                    }
                }
                $this->_setValueCompleteArray = $oldComplete;
            }

            unset($calls[strtolower($idAttr)]);
            return $err;
        }

        unset($calls[strtolower($idAttr)]);
        return sprintf(_("%s is not an array attribute"), $idAttr);
    }

    /**
     * add new \row in an array attribute
     *
     * the attribute must be an array type
     *
     * @api add new \row in an array attribute
     *
     * @param string $idAttr identifier of array attribute
     * @param array  $tv     values of each column. Array index must be the attribute identifier
     * @param int    $index  $index row (first is 0) -1 at the end; x means before x row
     *
     * @return string error message, if no error empty string
     */
    final public function addArrayRow($idAttr, $tv, $index = -1)
    {
        if (!is_array($tv)) {
            return sprintf('values "%s" must be an array', $tv);
        }
        $old_setValueCompleteArrayRow = $this->_setValueNeedCompleteArray;
        $this->_setValueNeedCompleteArray = false;

        $tv = array_change_key_case($tv, CASE_LOWER);
        $a = $this->getAttribute($idAttr);
        if ((!empty($a)) && $a->type == "array") {
            $err = $this->completeArrayRow($idAttr, false);
            if ($err == "") {
                $ta = $this->attributes->getArrayElements($a->id);
                $attrOut = array_diff(array_keys($tv), array_keys($ta));
                if ($attrOut) {
                    $this->_setValueNeedCompleteArray = $old_setValueCompleteArrayRow;
                    return sprintf(_('attribute "%s" is not a part of array "%s"'), implode(', ', $attrOut), $idAttr);
                }

                $err = "";
                // add in each columns
                foreach ($ta as $k => $v) {
                    $k = strtolower($k);
                    $tnv = $this->getMultipleRawValues($k);
                    $val = isset($tv[$k]) ? $tv[$k] : '';
                    if ($index == 0) {
                        array_unshift($tnv, $val);
                    } elseif ($index > 0 && $index < count($tnv)) {
                        $t1 = array_slice($tnv, 0, $index);
                        $t2 = array_slice($tnv, $index);
                        $tnv = array_merge($t1, array(
                            $val
                        ), $t2);
                    } else {
                        $tnv[] = $val;
                    }
                    $err .= $this->setValue($k, $tnv);
                }
                if ($err == "") {
                    $err = $this->completeArrayRow($idAttr, false);
                }
            }
            $this->_setValueNeedCompleteArray = $old_setValueCompleteArrayRow;
            return $err;
        }
        $this->_setValueNeedCompleteArray = $old_setValueCompleteArrayRow;
        return sprintf(_("%s is not an array attribute"), $idAttr);
    }

    /**
     * delete all attributes values of an array
     *
     * the attribute must be an array type
     *
     * @api delete all attributes values of an array
     *
     * @param string $idAttr identifier of array attribute
     *
     * @return string error message, if no error empty string
     */
    final public function clearArrayValues($idAttr)
    {
        $old_setValueCompleteArrayRow = $this->_setValueNeedCompleteArray;
        $this->_setValueNeedCompleteArray = false;

        $a = $this->getAttribute($idAttr);
        if ($a->type == "array") {
            $ta = $this->attributes->getArrayElements($a->id);
            $err = "";
            // delete each columns
            foreach ($ta as $k => $v) {
                $err .= $this->clearValue($k);
            }
            $this->_setValueNeedCompleteArray = $old_setValueCompleteArrayRow;
            return $err;
        }
        $this->_setValueNeedCompleteArray = $old_setValueCompleteArrayRow;
        return sprintf(_("%s is not an array attribute"), $idAttr);
    }


    /**
     * affect value for $attrid attribute
     *
     * the affectation is only in object. To set modification in database the store method must be
     * call after modification
     * If value is empty no modification are set. To reset a value use \Anakeen\Core\Internal\SmartElement::clearValue method.
     * an array can be use as value for values which are in arrays
     *
     * @api affect value for an attribute
     * @see \Anakeen\Core\Internal\SmartElement::setAttributeValue
     *
     * @param string       $attrid  attribute identifier
     * @param string|array $value   new \value for the attribute
     * @param int          $index   only for array values affect value in a specific row
     * @param int          &$kvalue in case of error the index of error (for arrays)
     *
     * @return string error message, if no error empty string
     */
    final public function setValue($attrid, $value, $index = -1, &$kvalue = null)
    {
        $attrid = strtolower($attrid);
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oattr
         */
        $oattr = $this->GetAttribute($attrid);
        // control edit before set values
        if ($this->isUnderControl()) {
            if ($this->id > 0) { // no control yet if no effective doc
                $err = $this->controlAccess("edit");
                if ($err != "") {
                    return ($err);
                }
            }
            if ($oattr && FieldAccessManager::hasWriteAccess($this, $oattr) === false) {
                return \ErrorCode::getError("DOC0132", $this->getTitle(), $oattr->id);
            }
        }

        if ($index > -1) { // modify one value in a row
            $tval = $this->getMultipleRawValues($attrid);
            if (($index + 1) > count($tval)) {
                $tval = array_pad($tval, $index + 1, "");
            }
            $tval[$index] = $value;
            $value = $tval;
        }
        if (is_array($value)) {
            if (count($value) == 0) {
                $value = DELVALUE;
            } else {
                if ($oattr && $oattr->repeat && (count($value) == 1) && substr(key($value), 0, 1) == "s") {
                    $ov = $this->getMultipleRawValues($attrid);
                    $rank = intval(substr(key($value), 1));
                    if (count($ov) < ($rank - 1)) { // fill array if not set
                        $start = count($ov);
                        for ($i = $start; $i < $rank; $i++) {
                            $ov[$i] = "";
                        }
                    }
                    foreach ($value as $k => $v) {
                        $ov[substr($k, 1, 1)] = $v;
                    }
                    $value = $ov;
                }
                if ($oattr->isMultipleInArray()) {
                    foreach ($value as $k => $v) {
                        if ($v === "" || $v === null) {
                            // Need to cast to respect pg array constraint
                            $value[$k] = [];
                        }
                    }
                }
                $value = $this->arrayToRawValue($value);
            }
        }
        if (($value !== "") && ($value !== null)) {
            // Change only if value is not empty
            if ($oattr === false) {
                if ($this->id > 0) {
                    return sprintf(
                        _("attribute %s unknow in document \"%s\" [%s]"),
                        $attrid,
                        $this->getTitle(),
                        $this->fromname
                    );
                } else {
                    return sprintf(_("attribute %s unknow in family \"%s\""), $attrid, $this->fromname);
                }
            }

            if ($value === DELVALUE) {
                if ($oattr->type != "password") {
                    $value = " ";
                } else {
                    return '';
                }
            }
            if ($value === " ") {
                $value = ""; // erase value
                if ((!empty($this->$attrid)) || (isset($this->$attrid) && $this->$attrid === "0")) {
                    if ($this->_setValueCompleteArray === false) {
                        $this->hasChanged = true;
                        $this->_oldvalue[$attrid] = $this->$attrid;
                    }
                    $this->$attrid = $value;
                    if ($oattr->type == "file") {
                        // need clear computed column
                        $this->clearFullAttr($oattr->id);
                    }
                }
            } else {
                $value = trim($value, " \x0B\r"); // suppress white spaces end & begin
                if (!isset($this->$attrid)) {
                    $this->$attrid = "";
                }

                if (strcmp($this->$attrid, $value) != 0
                    && strcmp($this->$attrid, str_replace("\n ", "\n", $value)) != 0) {
                    if ($oattr->repeat) {
                        $tvalues = $this->rawValueToArray($value);
                    } else {
                        $tvalues[] = $value;
                    }

                    foreach ($tvalues as $kvalue => $avalue) {
                        if (($avalue != "") && ($avalue != "\t")) {
                            if ($oattr) {
                                if (is_string($avalue)) {
                                    $avalue = trim($avalue);
                                }
                                $tvalues[$kvalue] = $avalue;
                                switch ($oattr->type) {
                                    case 'account':
                                    case 'docid':
                                        $tvalues[$kvalue] = MiscDoc::resolveDocIdLogicalNames($oattr, $avalue);
                                        break;

                                    case 'enum':
                                        if ($oattr->getOption("etype") == "open") {
                                            // added new
                                            $tenum = $oattr->getEnum();
                                            $keys = array_keys($tenum);
                                            if (!in_array($avalue, $keys)) {
                                                $oattr->addEnum($avalue, $avalue);
                                            }
                                        }
                                        break;

                                    case 'double':
                                        if ($avalue == '-') {
                                            $avalue = 0;
                                        }
                                        $tvalues[$kvalue] = str_replace(",", ".", $avalue);
                                        $tvalues[$kvalue] = str_replace(" ", "", $tvalues[$kvalue]);
                                        if ($avalue != "\t") {
                                            if (!is_numeric($tvalues[$kvalue])) {
                                                return sprintf(_("value [%s] is not a number"), $tvalues[$kvalue]);
                                            } else {
                                                $tvalues[$kvalue]
                                                    = (string)((double)$tvalues[$kvalue]); // delete non signifiant zeros
                                            }
                                        }

                                        break;

                                    case 'money':
                                        if ($avalue == '-') {
                                            $avalue = 0;
                                        }
                                        $tvalues[$kvalue] = str_replace(",", ".", $avalue);
                                        $tvalues[$kvalue] = str_replace(" ", "", $tvalues[$kvalue]);
                                        if (($avalue != "\t") && (!is_numeric($tvalues[$kvalue]))) {
                                            return sprintf(_("value [%s] is not a number"), $tvalues[$kvalue]);
                                        }
                                        $tvalues[$kvalue] = round(doubleval($tvalues[$kvalue]), 2);
                                        break;

                                    case 'integer':
                                    case 'int':
                                        if ($avalue == '-') {
                                            $avalue = 0;
                                        }
                                        if (($avalue != "\t") && (!is_numeric($avalue))) {
                                            return sprintf(_("value [%s] is not a number"), $avalue);
                                        }
                                        if (floatval($avalue) < -floatval(pow(2, 31))
                                            || floatval($avalue) > floatval(pow(2, 31) - 1)) {
                                            // signed int32 overflow
                                            return sprintf(
                                                _("[%s] must be between %s and %s"),
                                                $avalue,
                                                -floatval(pow(2, 31)),
                                                floatval(pow(2, 31) - 1)
                                            );
                                        }
                                        if (intval($avalue) != floatval($avalue)) {
                                            return sprintf(_("[%s] must be a integer"), $avalue);
                                        }

                                        $tvalues[$kvalue] = intval($avalue);
                                        break;

                                    case 'time':
                                        if (preg_match('/^(\d\d?):(\d\d?):?(\d\d?)?$/', $avalue, $reg)) {
                                            $hh = intval($reg[1]);
                                            $mm = intval($reg[2]);
                                            $ss = isset($reg[3]) ? intval($reg[3]) : 0; // seconds are optionals
                                            if ($hh < 0 || $hh > 23 || $mm < 0 || $mm > 59 || $ss < 0 || $ss > 59) {
                                                return sprintf(_("value [%s] is out of limit time"), $avalue);
                                            }
                                            if (isset($reg[3])) {
                                                $tvalues[$kvalue] = sprintf("%02d:%02d:%02d", $hh, $mm, $ss);
                                            } else {
                                                $tvalues[$kvalue] = sprintf("%02d:%02d", $hh, $mm);
                                            }
                                        } else {
                                            return sprintf(_("value [%s] is not a valid time"), $avalue);
                                        }

                                        break;

                                    case 'date':
                                        if (trim($avalue) == "") {
                                            if (!$oattr->repeat) {
                                                $tvalues[$kvalue] = "";
                                            }
                                        } else {
                                            if (!isValidDate($avalue)) {
                                                return sprintf(_("value [%s] is not a valid date"), $avalue);
                                            }

                                            $localeconfig = ContextManager::getLocaleConfig();
                                            if ($localeconfig !== false) {
                                                $tvalues[$kvalue] = stringDateToIso(
                                                    $avalue,
                                                    $localeconfig['dateFormat']
                                                );
                                            } else {
                                                return sprintf(_("local config for date not found"));
                                            }
                                        }
                                        break;

                                    case 'timestamp':
                                        if (trim($avalue) == "") {
                                            if (!$oattr->repeat) {
                                                $tvalues[$kvalue] = "";
                                            }
                                        } else {
                                            if (!isValidDate($avalue)) {
                                                return sprintf(_("value [%s] is not a valid timestamp"), $avalue);
                                            }

                                            $localeconfig = ContextManager::getLocaleConfig();
                                            if ($localeconfig !== false) {
                                                $tvalues[$kvalue] = stringDateToIso(
                                                    $avalue,
                                                    $localeconfig['dateTimeFormat']
                                                );
                                            } else {
                                                return sprintf(_("local config for timestamp not found"));
                                            }
                                        }
                                        break;

                                    case 'file':
                                        // clear fulltext realtive column
                                        if ((!$oattr->repeat)
                                            || ($avalue != $this->getMultipleRawValues($attrid, "", $kvalue))) {
                                            // only if changed
                                            $this->clearFullAttr($oattr->id, ($oattr->repeat) ? $kvalue : -1);
                                        }
                                        $tvalues[$kvalue] = str_replace(
                                            '\\',
                                            '',
                                            $tvalues[$kvalue]
                                        ); // correct possible save error in old versions
                                        break;

                                    case 'image':
                                        $tvalues[$kvalue] = str_replace('\\', '', $tvalues[$kvalue]);
                                        break;

                                    case 'htmltext':
                                        $tvalues[$kvalue] = str_replace('&#39;', "'", $tvalues[$kvalue]);
                                        $tvalues[$kvalue] = preg_replace(
                                            "/<!--.*?-->/ms",
                                            "",
                                            $tvalues[$kvalue]
                                        ); //delete comments
                                        $tvalues[$kvalue] = \Dcp\Utils\htmlclean::xssClean($tvalues[$kvalue]);
                                        if ($oattr->getOption("htmlclean") == "yes") {
                                            $tvalues[$kvalue] = \Dcp\Utils\htmlclean::cleanStyle($tvalues[$kvalue]);
                                        }
                                        /* Check for malformed HTML */
                                        $html = \Dcp\Utils\htmlclean::normalizeHTMLFragment($tvalues[$kvalue], $error);
                                        if ($html === false) {
                                            $html = '';
                                        }
                                        /* Return error on malformed HTML */
                                        if ($error != '') {
                                            return _("Malformed HTML:") . "\n" . $error;
                                        }
                                        /* If htmlclean is set, then use the normalized HTML fragment instead */
                                        if ($oattr->getOption("htmlclean") == "yes") {
                                            $tvalues[$kvalue] = $html;
                                        }
                                        /* Encode '[' to prevent further layout interpretation/evaluation */
                                        $tvalues[$kvalue] = str_replace(
                                            "[",
                                            "&#x5B;",
                                            $tvalues[$kvalue]
                                        ); // need to stop auto instance
                                        break;

                                    case 'thesaurus':
                                        // reset cache of doccount

                                        $d = new \docCount($this->dbaccess);
                                        $d->famid = $this->fromid;
                                        $d->aid = $attrid;
                                        $d->deleteAll();
                                        break;

                                    case 'text':
                                        $tvalues[$kvalue] = str_replace("\r", " ", $tvalues[$kvalue]);
                                        break;
                                }
                            }
                        }
                    }
                    //print "<br/>change $attrid to :".$this->$attrid."->".implode("\n",$tvalues);
                    if ($oattr->isMultiple()) {
                        $rawValue = $this->arrayToRawValue($tvalues);
                    } else {
                        $rawValue = implode("\n", $tvalues);
                    }
                    if (!$this->_setValueCompleteArray && $this->$attrid != $rawValue) {
                        $this->_oldvalue[$attrid] = $this->$attrid;
                        $this->hasChanged = true;
                    }
                    $this->$attrid = $rawValue;
                }
            }
        }
        if ($this->_setValueNeedCompleteArray && $oattr && $oattr->inArray()) {
            return $this->completeArrayRow($oattr->fieldSet->id);
        }
        return '';
    }

    /**
     * clear $attrid_txt and $attrid_vec
     *
     * @param string $attrid identifier of file attribute
     * @param int    $index  in case of multiple values
     *
     * @return void
     */
    final private function clearFullAttr($attrid, $index = -1)
    {
        $attrid = strtolower($attrid);
        $oa = $this->getAttribute($attrid);
        if ($oa && $oa->usefor != 'Q') {
            if ($oa->getOption("search") != "no") {
                $ak = $attrid . '_txt';
                if ($index == -1) {
                    $this->$ak = '';
                } else {
                    if ($this->affectColumn(array(
                        $ak
                    ), false)) {
                        $this->$ak = sep_replace($this->$ak, $index);
                    }
                }
                $this->fields[$ak] = $ak;
                $ak = $attrid . '_vec';
                $this->$ak = '';
                $this->fields[$ak] = $ak;
                $this->fulltext = '';
                $this->fields['fulltext'] = 'fulltext'; // to enable trigger
                $this->textsend[$attrid . $index] = array(
                    "attrid" => $attrid,
                    "index" => $index
                );
            }
        }
    }

    /**
     * send text transformation
     * after ::clearFullAttr is called
     *
     */
    final private function sendTextToEngine()
    {
        $err = '';
        if (!empty($this->textsend)) {
            foreach ($this->textsend as $k => $v) {
                $index = $v["index"];
                if ($index > 0) {
                    $fval = $this->getMultipleRawValues($v["attrid"], "", $index);
                } else {
                    $fval = strtok($this->getRawValue($v["attrid"]), "\n");
                }
                if (preg_match(PREGEXPFILE, $fval, $reg)) {
                    $vid = $reg[2];
                    if (isset($this->vidNoSendTextToEngine[$vid])) {
                        return '';
                    }
                    $err = sendTextTransformation($this->dbaccess, $this->id, $v["attrid"], $index, $vid);
                    if ($err != "") {
                        $this->addHistoryEntry(_("error sending text conversion") . ": $err", \DocHisto::NOTICE);
                    }
                    $this->vidNoSendTextToEngine[$vid] = true;
                }
            }
            $this->textsend = array(); //reinit
        }
        return $err;
    }

    /**
     * force recompute all file text transformation
     *
     * @param string $aid file attribute identifier. If empty all files attributes will be reseted
     *
     * @return string error message, if no error empty string
     */
    final public function recomputeTextFiles($aid = '')
    {
        if (!$aid) {
            $afiles = $this->GetFileAttributes(true);
        } else {
            $afiles[$aid] = $this->getAttribute($aid);
        }

        $ttxt = array();
        foreach ($afiles as $k => $v) {
            $kt = $k . '_txt';
            $ttxt[] = $kt;
            if ($v->inArray()) {
                $tv = $this->getMultipleRawValues($k);
                foreach ($tv as $kv => $vv) {
                    $this->clearFullAttr($k, $kv);
                }
            } else {
                $this->clearFullAttr($k);
            }
            $this->$kt = '';
            $kv = $k . '_vec';
            $ttxt[] = $kv;
            $this->$kv = '';
        }
        $this->modify(true, $ttxt, true);
        $err = $this->sendTextToEngine();
        return $err;
    }

    /**
     * affect text value in $attrid file attribute
     *
     * create a new \file in Vault to replace old file
     *
     * @param string $attrid identifier of file attribute
     * @param string $value  new \value for the attribute
     * @param string $ftitle the name of file (if empty the same as before)
     *
     * @return string error message, if no error empty string
     */
    final public function setTextValueInFile($attrid, $value, $ftitle = "")
    {
        $err = '';
        $a = $this->getAttribute($attrid);
        if ($a->type == "file") {
            $vf = newFreeVaultFile($this->dbaccess);
            $fvalue = $this->getRawValue($attrid);
            $basename = "";
            if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
                $vaultid = $reg[2];
                //$mimetype = $reg[1];
                $info = new \vaultFileInfo();
                $err = $vf->Retrieve($vaultid, $info);

                if ($err == "") {
                    $basename = $info->name;
                }
            }
            $filename = uniqid(ContextManager::getTmpDir() . "/_html") . ".html";
            $nc = file_put_contents($filename, $value);
            /**
             * @var int $vid
             */
            $err = $vf->Store($filename, false, $vid);
            if ($ftitle != "") {
                $vf->Rename($vid, $ftitle);
                $basename = $ftitle;
            } else {
                if ($basename != "") { // keep same file name
                    $vf->Rename($vid, $basename);
                }
            }
            if ($err == "") {
                $mime = trim(shell_exec(sprintf("file -ib %s", escapeshellarg($filename))));
                $value = "$mime|$vid|$basename";
                $err = $this->setValue($attrid, $value);
                //$err="file conversion $mime|$vid";
                if ($err == "xx") {
                    $this->clearFullAttr($attrid); // because internal values not changed
                }
            }
            if ($nc > 0) {
                unlink($filename);
            }
        }
        return $err;
    }

    /**
     * get text value from $attrid file attribute
     *
     * get content of a file (must be an ascii file)
     *
     * @param string $attrid identifier of file attribute
     * @param string &$text  the content of the file
     *
     * @return string error message, if no error empty string
     */
    final public function getTextValueFromFile($attrid, &$text)
    {
        $err = '';
        $a = $this->getAttribute($attrid);
        if ($a->type == "file") {
            $vf = newFreeVaultFile($this->dbaccess);
            $fvalue = $this->getRawValue($attrid);
            if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
                $vaultid = $reg[2];
                $info = new \VaultFileInfo();
                /**
                 * VaultFileInfo $info
                 */
                $err = $vf->Retrieve($vaultid, $info);

                if (!$err) {
                    $filename = $info->path;
                    $text = file_get_contents($filename);
                }
            }
        }
        return $err;
    }

    /**
     * save stream file in an file attribute
     *
     * replace a new \file in Vault to replace old file
     *
     * @param string   $attrid identifier of file attribute
     * @param resource $stream file resource from fopen
     * @param string   $ftitle to change title of file also (empty to unchange)
     * @param int      $index  for array of file : modify in specific row
     *
     * @return string error message, if no error empty string
     */
    final public function saveFile($attrid, $stream, $ftitle = "", $index = -1)
    {
        $err = '';
        if (is_resource($stream) && get_resource_type($stream) == "stream") {
            $mimetype = $ext = $oftitle = $vaultid = '';
            $a = $this->getAttribute($attrid);
            if ($a->type == "file") {
                $vf = newFreeVaultFile($this->dbaccess);
                if ($index > -1) {
                    $fvalue = $this->getMultipleRawValues($attrid, '', $index);
                } else {
                    $fvalue = $this->getRawValue($attrid);
                }
                $basename = "";
                if (preg_match(PREGEXPFILE, $fvalue, $reg)) {
                    $vaultid = $reg[2];
                    $mimetype = $reg[1];
                    $oftitle = $reg[3];
                    $info = new \VaultFileInfo();
                    $err = $vf->Retrieve($vaultid, $info);

                    if ($err == "") {
                        $basename = $info->name;
                    }
                }
                if ($ftitle) {
                    $ext = \Anakeen\Core\Utils\FileMime::getFileExtension($ftitle);
                }
                if ($ext == "") {
                    $ext = "nop";
                }

                $filename = uniqid(ContextManager::getTmpDir() . "/_fdl") . ".$ext";
                $tmpstream = fopen($filename, "w");
                while (!feof($stream)) {
                    if (false === fwrite($tmpstream, fread($stream, 4096))) {
                        $err = "403 Forbidden";
                        break;
                    }
                }
                fclose($tmpstream);
                if (!$err) {
                    // verify if need to create new \file in case of revision
                    $newfile = ($basename == "");

                    if ($this->revision > 0) {
                        $trev = $this->GetRevisions("TABLE", 2);
                        /**
                         * @var $revdoc array
                         */
                        $revdoc = $trev[1];
                        $prevfile = getv($revdoc, strtolower($attrid));
                        if ($prevfile == $fvalue) {
                            $newfile = true;
                        }
                    }

                    if (!$newfile) {
                        $err = $vf->Save($filename, false, $vaultid);
                    } else {
                        $err = $vf->Store($filename, false, $vaultid);
                    }
                    if ($ftitle != "") {
                        $vf->Rename($vaultid, $ftitle);
                    } elseif ($basename != "") { // keep same file name
                        $vf->Rename($vaultid, $basename);
                    }
                    if ($err == "") {
                        if ($mimetype) {
                            $mime = $mimetype;
                        } else {
                            $mime = trim(shell_exec(sprintf("file -ib %s", escapeshellarg($filename))));
                        }
                        if ($ftitle) {
                            $value = "$mime|$vaultid|$ftitle";
                        } else {
                            $value = "$mime|$vaultid|$oftitle";
                        }
                        $err = $this->setValue($attrid, $value, $index);
                        if ($err == "") {
                            $this->clearFullAttr($attrid); // because internal values not changed
                        }
                        //$err="file conversion $mime|$vid";
                    }
                    unlink($filename);
                    $this->addHistoryEntry(sprintf(_("modify file %s"), $ftitle));
                    $this->hasChanged = true;
                }
            }
        }
        return $err;
    }

    /**
     * use for duplicate physicaly the file
     *
     * @param string $idattr  identifier of file attribute
     * @param string $newname basename if want change name of file
     * @param int    $index   in case of array
     *
     * @return string attribut value formated to be inserted into a file attribute
     */
    final public function copyFile($idattr, $newname = "", $index = -1)
    {
        if ($index >= 0) {
            $f = $this->getMultipleRawValues($idattr, "", $index);
        } else {
            $f = $this->getRawValue($idattr);
        }
        if ($f) {
            if (preg_match(PREGEXPFILE, $f, $reg)) {
                $vf = newFreeVaultFile($this->dbaccess);
                /**
                 * @var \VaultFileInfo $info
                 */
                if ($vf->Show($reg[2], $info) == "") {
                    $cible = $info->path;
                    if (file_exists($cible)) {
                        /**
                         * @var int $vid vault id
                         */
                        $err = $vf->Store($cible, false, $vid);
                        if ($err == "") {
                            if (!$newname) {
                                $newname = $info->name;
                            }
                            if ($newname) {
                                $vf->Rename($vid, $newname);
                            }
                            return $reg[1] . "|$vid|$newname";
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * rename physicaly the file
     *
     * @param string $idattr  identifier of file attribute
     * @param string $newname base name file
     * @param int    $index   in case of array of files
     *
     * @return string empty if no error
     */
    final public function renameFile($idattr, $newname, $index = -1)
    {
        if ($newname) {
            if ($index == -1) {
                $f = $this->getRawValue($idattr);
            } else {
                $f = $this->getMultipleRawValues($idattr, "", $index);
            }
            if ($f) {
                if (preg_match(PREGEXPFILE, $f, $reg)) {
                    $vf = newFreeVaultFile($this->dbaccess);
                    $vid = $reg[2];
                    /**
                     * @var \VaultFileInfo $info
                     */
                    if ($vf->Show($reg[2], $info) == "") {
                        $cible = $info->path;
                        if (file_exists($cible)) {
                            $vf->Rename($vid, $newname);
                            $this->setValue($idattr, $info->mime_s . '|' . $vid . '|' . $newname, $index);
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Register (store) a file in the vault and return the file's vault's informations
     *
     * @param string         $filename the file pathname
     * @param string         $ftitle   override the stored file name or empty string to keep the original file name
     * @param \VaultFileInfo $info     the vault's informations for the stored file or null if could not get informations
     *
     * @return string trigram of the file in the vault: "mime_s|id_file|name"
     * @throws \Exception on error
     */
    final public function vaultRegisterFile($filename, $ftitle = "", &$info = null)
    {
        $vaultid = \Dcp\VaultManager::storeFile($filename, $ftitle);

        $info = \Dcp\VaultManager::getFileInfo($vaultid);
        if (!is_object($info) || !is_a($info, 'VaultFileInfo')) {
            throw new \Exception(\ErrorCode::getError('FILE0010', $filename));
        }

        return sprintf("%s|%s|%s", $info->mime_s, $info->id_file, $info->name);
    }

    /**
     * Store a file in a file attribute
     *
     * @param string $attrid   identifier of file attribute
     * @param string $filename file path
     * @param string $ftitle   basename of file
     * @param int    $index    only for array values affect value in a specific row
     *
     * @return string error message, if no error empty string
     */
    final public function setFile($attrid, $filename, $ftitle = "", $index = -1)
    {
        try {
            $a = $this->getAttribute($attrid);
            if ($a) {
                if (($a->type == "file") || ($a->type == "image")) {
                    $info = null;
                    $vaultid = $this->vaultRegisterFile($filename, $ftitle, $info);
                    $err = $this->setValue($attrid, $vaultid, $index);
                } else {
                    $err = sprintf(_("attribute %s is not a file attribute"), $a->getLabel());
                }
            } else {
                $err = sprintf(_("unknow attribute %s"), $attrid);
            }
        } catch (\Exception $e) {
            \Anakeen\Core\LogException::writeLog($e);
            $err = $e->getMessage();
        }
        return $err;
    }


    /**
     * Duplicate physically all files of documents
     *
     */
    public function duplicateFiles()
    {
        $err = "";
        $fa = $this->GetFileAttributes();
        foreach ($fa as $aid => $oa) {
            if ($oa->inArray()) {
                $t = $this->getMultipleRawValues($oa->id);
                $tcopy = array();
                foreach ($t as $k => $v) {
                    $tcopy[$k] = $this->copyFile($oa->id, "", $k);
                }
                $this->setValue($oa->id, $tcopy);
            } else {
                $this->setValue($oa->id, $this->copyFile($oa->id));
            }
        }
        return $err;
    }

    /**
     * Return the related value by linked attributes.
     *
     * Can be used to retrieve a value by traversing multiple docid.
     *
     * For example,
     *
     * @code
     * $val = $this->getRValue("id1:id2:id3")
     * @endcode
     * is a shortcut for
     * @code
     * $id1 = $this->getRawValue("id1");
     * $doc1 = new_Doc('', $id1);
     * $id2 = $doc1->getRawValue("id2");
     * $doc2 = new_Doc('', $id2);
     * $val = $doc2->getRawValue('', "id3");
     * @endcode
     *
     * @warning
     * Each of the traversed docid **must** be a docid or an account, and **must not** be multiple.\n
     * Elsewhere, the returned value is $def
     * @endwarning
     *
     * @param string $RidAttr attributes identifier chain (separated by ':')
     * @param string $def     $def default return value
     * @param bool   $latest  always last revision of document
     * @param bool   $html    return formated value for html
     *
     * @return array|string
     */
    final public function getRValue($RidAttr, $def = "", $latest = true)
    {
        $tattrid = explode(":", $RidAttr);
        $lattrid = array_pop($tattrid); // last attribute
        $doc = $this;
        foreach ($tattrid as $k => $v) {
            $docid = $doc->getRawValue($v);
            if ($docid == "") {
                return $def;
            }
            $doc = SEManager::getDocument($docid, $latest);

            if (!$doc) {
                return $def;
            }
        }

        return $doc->getRawValue($lattrid, $def);

    }


    /**
     * return the previous value for a attibute set before \Anakeen\Core\Internal\SmartElement::SetValue
     * can be used in \Anakeen\Core\Internal\SmartElement::postModify generaly
     *
     * @api get previous value of an attribute
     *
     * @param string $attrid attribute identifier
     *
     * @return string the old value (false if not modified before)
     *
     */
    final public function getOldRawValue($attrid)
    {
        $attrid = strtolower($attrid);
        if (isset($this->_oldvalue[$attrid])) {
            return $this->_oldvalue[$attrid];
        }
        return false;
    }


    /**
     * return all modified values from last modify
     *
     * @api get all modified values from last modify
     * @return array indexed by attribute identifier (lowercase)
     */
    final public function getOldRawValues()
    {
        if (isset($this->_oldvalue)) {
            return $this->_oldvalue;
        }
        return array();
    }

    /**
     * delete a value of an attribute
     *
     * @see \Anakeen\Core\Internal\SmartElement::setValue
     *
     * @param string $attrid attribute identifier
     *
     * @api clear value of an attribute
     * @return string error message
     */
    final public function clearValue($attrid)
    {
        $oattr = $this->GetAttribute($attrid);
        if ($oattr->type == 'docid') {
            $doctitle = $oattr->getOption('doctitle');
            if ($doctitle == 'auto') {
                $doctitle = $attrid . '_title';
            }
            if (!empty($doctitle)) {
                $this->SetValue($doctitle, " ");
            }
        }
        return $this->SetValue($attrid, " ");
    }


    /**
     * add values present in values field
     */
    private function getMoreValues()
    {
        if (isset($this->fieldvalues)) {
            $moreValues=json_decode($this->fieldvalues, true);

            foreach ($moreValues as $attrid => $v) {
                if (empty($this->$attrid)) {
                    if (is_array($v)) {
                        $v=Postgres::arrayToString($v);
                    }
                    $this->$attrid = $v;
                    $this->mvalues[$attrid] = $v; // to be use in getValues()
                }
            }
        }
    }

    /**
     * reset values present in values field
     */
    private function resetMoreValues()
    {
        if (isset($this->fieldvalues) && $this->id) {
            $moreValues=json_decode($this->fieldvalues, true);
            foreach ($moreValues as $k => $v) {
                    $this->$k = null;
            }
        }
        $this->mvalues = array();
    }

    /**
     * @param $value
     *
     * @return string
     */
    final public function getValueMethod($value)
    {
        $value = $this->ApplyMethod($value, $value);
        return $value;
    }

    public static function seemsMethod($method)
    {
        return is_string($method) && preg_match('/([^:]*)::([^\(]+)\(([^\)]*)\)/', $method);
    }

    /**
     * apply a method to a doc
     * specified like ::getFoo(10)
     *
     * @param string $method  the method to apply
     * @param string $def     default value if no method
     * @param int    $index   index in case of value in row
     * @param array  $bargs   first arguments sent before for the method
     * @param array  $mapArgs indexed array to add more possibilities to map arguments
     * @param string $err     error message
     *
     * @return string the value
     */
    final public function applyMethod(
        $method,
        $def = "",
        $index = -1,
        array $bargs = array(),
        array $mapArgs = array(),
        &$err = ''
    ) {
        $value = $def;
        $err = '';

        if (self::seemsMethod($method)) {
            $parseMethod = new \Anakeen\Core\SmartStructure\Callables\ParseFamilyMethod();
            $parseMethod->parse($method);
            $err = $parseMethod->getError();
            if ($err) {
                return $err;
            }

            $staticClass = $parseMethod->className;
            if (!$staticClass) {
                $staticClass = $this;
            }
            $methodName = $parseMethod->methodName;
            if (method_exists($staticClass, $methodName)) {
                if ((count($parseMethod->inputs) == 0) && (empty($bargs))) {
                    // without argument
                    $value = call_user_func(array(
                        $staticClass,
                        $methodName
                    ));
                } else {
                    // with argument
                    $args = array();

                    $inputs = array();
                    foreach ($bargs as $extraArg) {
                        $inputs[] = new InputArgument($extraArg);
                    }
                    $inputs = array_merge($inputs, $parseMethod->inputs);
                    foreach ($inputs as $ki => $input) {
                        $args[$ki] = null;
                        if ($input->type == "string") {
                            $args[$ki] = $input->name;
                        } else {
                            $mapped = (isset($mapArgs[strtolower($input->name)])) ? $mapArgs[strtolower($input->name)]
                                : null;
                            if ($mapped) {
                                if (is_object($mapped)) {
                                    $args[$ki] = &$mapArgs[strtolower($input->name)];
                                } else {
                                    $args[$ki] = $mapped;
                                }
                            } elseif ($attr = $this->getAttribute($input->name)) {
                                if ($attr->usefor == 'Q') {
                                    if ($attr->inArray()) {
                                        $pas = $this->rawValueToArray($this->getFamilyParameterValue($input->name));
                                        if ($index == -1) {
                                            $args[$ki] = $pas;
                                        } else {
                                            $args[$ki] = isset($pas[$index]) ? $pas[$index] : null;
                                        }
                                    } else {
                                        $args[$ki] = $this->getFamilyParameterValue($input->name);
                                    }
                                } else {
                                    if ($attr->inArray()) {
                                        $args[$ki] = $this->getMultipleRawValues($input->name, "", $index);
                                        if ($index >= 0 && is_array($args[$ki])) {
                                            $args[$ki] = Postgres::arrayToString($args[$ki]);
                                        }
                                    } else {
                                        $args[$ki] = $this->getRawValue($input->name);
                                    }
                                }
                            } else {
                                if ($input->name == 'THIS') {
                                    $args[$ki] = &$this;
                                } elseif ($input->name == 'K') {
                                    $args[$ki] = $index;
                                } else {
                                    $args[$ki] = $input->name; // not an attribute just text
                                }
                            }
                        }
                    }
                    $value = call_user_func_array(array(
                        $staticClass,
                        $methodName,
                    ), $args);

                }
            } else {
                $err = sprintf(_("Method [%s] not exists"), $method);
                \Anakeen\Core\Utils\System::addWarningMsg($err);
                error_log($err . print_r(\Anakeen\Core\Internal\Debug::getDebugStack(), true));
                return null;
            }
        }
        return $value;
    }

    /**
     * verify attribute constraint
     *
     * @param string $attrid attribute identifier
     * @param int    $index  index in case of multiple values
     *
     * @return array array of 2 items ("err" + "sug").
     * The err is the string error message (empty means no error)
     * The sug is an array of possibles corrections
     */
    final public function verifyConstraint($attrid, $index = -1)
    {
        $ok = array(
            "err" => "",
            "sug" => array()
        );
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $oattr
         */
        $oattr = $this->getAttribute($attrid);
        if (strlen(trim($oattr->phpconstraint)) > 1) {
            $ko = array(
                "err" => sprintf(_("method %s not found"), $oattr->phpconstraint),
                "sug" => array()
            );
            $res = $this->applyMethod($oattr->phpconstraint, $ko, $index);

            if ($res !== true) {
                if (!is_array($res)) {
                    if ($res === false) {
                        $res = array(
                            "err" => _("constraint error"),
                            "sug" => array()
                        );
                    } elseif (is_string($res)) {
                        $res = array(
                            "err" => $res,
                            "sug" => array()
                        );
                    }
                } elseif (!empty($res["sug"]) && (!is_array($res["sug"]))) {
                    $res["sug"] = array(
                        $res["sug"]
                    );
                }
                if (is_array($res) && $res["err"] != "") {
                    $this->constraintbroken = "[$attrid] " . $res["err"];
                }
                return $res;
            }
        }

        return $ok;
    }

    /**
     * verify if constraint ore OK
     *
     * @param boolean $stoptofirst stop in first constraint error
     * @param array   &$info       set of information about constraint test
     *
     * @return string error message (empty means no error)
     */
    final public function verifyAllConstraints($stoptofirst = true, &$info = array())
    {
        $err = "";

        $listattr = $this->GetNormalAttributes();
        foreach ($listattr as $v) {
            if (strlen($v->phpconstraint) > 1) {
                if ($v->inArray()) {
                    $tv = $this->getMultipleRawValues($v->id);
                    for ($i = 0; $i < count($tv); $i++) {
                        $res = $this->verifyConstraint($v->id, $i);
                        if ($res["err"] != "") {
                            $info[$v->id . $i] = array(
                                "id" => $v->id,
                                "label" => $v->getLabel(),
                                "sug" => $res["sug"],
                                "err" => $res["err"],
                                "index" => $i,
                                "pid" => $v->fieldSet->id
                            );
                            if ($stoptofirst) {
                                return sprintf("[%s] %s", $v->getLabel(), $res["err"]);
                            }
                            $err = $res["err"];
                        }
                    }
                } else {
                    $res = $this->verifyConstraint($v->id);
                    if ($res["err"] != "") {
                        $info[$v->id] = array(
                            "id" => $v->id,
                            "label" => $v->getLabel(),
                            "pid" => $v->fieldSet->id,
                            "sug" => $res["sug"],
                            "err" => $res["err"]
                        );
                        if ($stoptofirst) {
                            return sprintf("[%s] %s", $v->getLabel(), $res["err"]);
                        }
                        $err = $res["err"];
                    }
                }
            }
        }
        return $err;
    }

    /**
     * return the first attribute of type 'file' false if no file
     *
     * @return \Anakeen\Core\SmartStructure\NormalAttribute|bool
     */
    final public function getFirstFileAttributes()
    {
        $t = $this->GetFileAttributes();
        if (count($t) > 0) {
            return current($t);
        }
        return false;
    }

    /**
     * Add a comment line in history document
     * note : modify is call automatically
     *
     * @api Add a comment message in history document
     *
     * @param string $comment the comment to add
     * @param int    $level   level of comment \DocHisto::INFO, \DocHisto::ERROR,
     *                        \DocHisto::NOTICE \DocHisto::MESSAGE, \DocHisto::WARNING
     * @param string $code    use when memorize notification
     * @param string $uid     user identifier : by default its the current user
     *
     * @return string error message
     */
    final public function addHistoryEntry($comment = '', $level = \DocHisto::INFO, $code = '', $uid = '')
    {
        if ($this->id == "") {
            return '';
        }

        $h = new \DocHisto($this->dbaccess);

        $h->id = $this->id;
        $h->initid = $this->initid;
        if (!\Anakeen\Core\Utils\Strings::isUTF8($comment)) {
            $comment = utf8_encode($comment);
        }
        $h->comment = $comment;
        $h->date = Date::getNow(true);
        if ($uid > 0) {
            $u = new \Anakeen\Core\Account("", $uid);
        } else {
            $u = ContextManager::getCurrentUser(true);
        }
        $h->uid = $u->id;
        $h->uname = sprintf("%s %s", $u->firstname, $u->lastname);
        $h->level = $level;
        $h->code = $code;

        $err = $h->add();
        if ($level == \DocHisto::ERROR) {
            error_log(sprintf("document %s [%d] : %s", $this->title, $this->id, $comment));
        }
        return $err;
    }


    /**
     * Add a log entry line in log document
     *
     * @param string $comment the comment to add
     * @param string $level   level of comment
     * @param string $code    use when memorize notification
     * @param string $arg     serialized object
     * @param string $uid     user identifier : by default its the current user
     *
     * @return string error message
     */
    final public function addLog($code = '', $arg = '', $comment = '', $level = '', $uid = '')
    {
        if (($this->id == "") || ($this->doctype == 'T')) {
            return '';
        }

        $h = new \DocLog($this->dbaccess);
        $h->id = $this->id;
        $h->initid = $this->initid;
        $h->title = $this->title;
        if (!\Anakeen\Core\Utils\Strings::isUTF8($comment)) {
            $comment = utf8_encode($comment);
        }
        $h->comment = $comment;
        if ($uid > 0) {
            $u = new \Anakeen\Core\Account("", $uid);
        } else {
            $u = ContextManager::getCurrentUser(true);
        }
        $h->uid = $u->id;
        $h->uname = sprintf("%s %s", $u->firstname, $u->lastname);

        $h->level = $level ? $level : \DocLog::LOG_NOTIFY;
        $h->code = $code;
        if ($arg) {
            $h->arg = serialize($arg);
        }

        $err = $h->add();
        return $err;
    }

    /**
     * Get history for the document
     *
     * @param bool   $allrev set true if want for all revision
     *
     * @param string $code   code filter
     * @param int    $limit  limit of items returned
     *
     * @return array of different comment
     */
    public function getHisto($allrev = false, $code = "", $limit = 0)
    {
        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \DocHisto::class);
        if ($allrev) {
            $q->AddQuery("initid=" . $this->initid);
        } else {
            $q->AddQuery("id=" . $this->id);
        }
        if ($code) {
            $q->addQuery(sprintf("code='%s'", pg_escape_string($code)));
        }
        $q->order_by = "date desc";
        $l = $q->Query(0, $limit, "TABLE");

        if (is_array($l)) {
            return $l;
        }
        return array();
    }

    /**
     * Add a application tag for the document
     * if it is already set no set twice
     * A application tag must not contains "\n" character
     *
     * @param string $tag the tag to add
     *
     * @param mixed   $value value of tag (true by default)
     * @return string error message
     */
    final public function addATag($tag, $value=true)
    {
        $err = "";
        if (strpos($tag, "\n") !== false) {
            return \ErrorCode::getError('DOC0121', $tag, $this->id);
        }
        if (!$tag) {
            return \ErrorCode::getError('DOC0122', $this->id);
        }
        if ($this->atags == "") {
            $this->atags = json_encode([$tag => $value]);
            $err = $this->modify(true, array(
                "atags"
            ), true);
        } else {
            $tags=json_decode($this->atags, true);
            if (!$this->getATag($tag)) {
                $tags[$tag]=$value;
                $this->atags = json_encode($tags);
                $err = $this->modify(true, array(
                    "atags"
                ), true);
            }
        }
        return $err;
    }

    /**
     * Return true if application tag is present
     *
     * @param string $tag the tag to search
     * @param string $value return tag value recorded
     * @return bool return true if found
     */
    final public function getATag($tag, &$value=null)
    {
        if ($this->atags == "") {
            return false;
        }
        $tags=json_decode($this->atags, true);
        if (isset($tags[$tag])){
            $value=$tags[$tag];
            return true;
        }
        return false;
    }

    /**
     * Delete a application tag for the document
     *
     * @param string $tag the tag to delete
     *
     * @return string error message
     */
    final public function delATag($tag)
    {
        $err = "";
        if ($this->atags == "") {
            return "";
        }
        $tags=json_decode($this->atags, true);
        if (isset($tags[$tag])){
            unset($tags[$tag]);
            $this->atags = json_encode($tags);
            $err = $this->modify(true, array(
                "atags"
            ), true);
        }

        return $err;
    }

    /**
     * Add a user tag for the document
     * if it is already set no set twice
     *
     * @param int    $uid         the system user identifier
     * @param string $tag         the key tag
     * @param string $datas       a comment or a value for the tag
     * @param bool   $allrevision set to false if attach a tag to a specific version
     *
     * @return string error message
     */
    final public function addUTag($uid, $tag, $datas = "", $allrevision = true)
    {
        if (!$this->initid) {
            return "";
        }
        if ($tag == "") {
            return _("no user tag specified");
        }
        $this->delUTag($uid, $tag, $allrevision);

        $h = new \DocUTag($this->dbaccess);

        $h->id = $this->id;
        $h->initid = $this->initid;
        $h->fixed = ($allrevision) ? 'false' : 'true';
        $h->date = date("d-m-Y H:i:s");
        if ($uid > 0) {
            $u = new \Anakeen\Core\Account("", $uid);
            $h->uid = $u->id;
            $h->uname = sprintf("%s %s", $u->firstname, $u->lastname);
        }
        $h->fromuid = ContextManager::getCurrentUser()->id;

        $h->tag = $tag;
        $h->comment = $datas;

        $err = $h->add();
        return $err;
    }

    /**
     * Test if current user has the user tag specified
     *
     * @param string $tag         the tag to verify
     * @param bool   $allrevision set to false to verify a tag to a specific version
     *
     * @return bool
     */
    final public function hasUTag($tag, $allrevision = true)
    {
        if (!$this->initid) {
            return false;
        }

        $docid = ($allrevision) ? $this->initid : $this->id;
        $utag = new \DocUTag($this->dbaccess, array(
            $docid,
            ContextManager::getCurrentUser()->id,
            $tag
        ));
        return $utag->isAffected();
    }

    /**
     * Get current user tag specified
     *
     * @param string $tag         the tag to verify
     * @param bool   $allrevision set to false to get a tag to a specific version
     * @param int    $uid         system user identifier
     *
     * @return bool|\DocUTag
     * @throws \Dcp\Db\Exception
     */
    final public function getUTag($tag, $allrevision = true, $uid = null)
    {
        if (!$this->initid) {
            return "";
        }
        if ($uid === null) {
            $uid = ContextManager::getCurrentUser()->id;
        }


        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \DocUTag::class);
        $q->addQuery("uid=" . intval($uid));
        if ($tag) {
            $q->addQuery("tag = '" . pg_escape_string($tag) . "'");
        }
        if ($allrevision) {
            $q->addQuery("initid = " . $this->initid);
        } else {
            $q->addQuery("id = " . $this->id);
        }
        $q->order_by = "id desc";
        $r = $q->Query(0, 1);
        if ($q->nb == 1) {
            return $r[0];
        }
        return false;
    }

    /**
     * Remove a user tag for the document
     * if it is already set no set twice
     *
     * @param int    $uid         the system user identifier
     * @param string $tag         the tag to add
     * @param bool   $allrevision set to false to del a tag to a specific version
     *
     * @return string error message
     */
    final public function delUTag($uid, $tag, $allrevision = true)
    {
        if ($tag == "") {
            return _("no user tag specified");
        }

        if ($allrevision) {
            $err = $this->query(sprintf(
                "delete from docutag where initid=%d and tag='%s' and uid=%d",
                $this->initid,
                pg_escape_string($tag),
                $uid
            ));
        } else {
            $err = $this->query(sprintf(
                "delete from docutag where id=%d and tag='%s' and uid=%d",
                $this->id,
                pg_escape_string($tag),
                $uid
            ));
        }
        return $err;
    }

    /**
     * Remove all user tag for the document
     *
     * @param int $uid the system user identifier
     *
     * @return string error message
     */
    final public function delUTags($uid = 0)
    {
        if (!$this->initid) {
            return "";
        }
        if (!$uid) {
            $uid = ContextManager::getCurrentUser()->id;
        }
        $err = $this->query(sprintf("delete from docutag where initid=%d and uid=%d", $this->initid, $uid));

        return $err;
    }

    /**
     * Refresh all user tag for the document in case of revision
     *
     * @return string error message
     */
    final public function refreshUTags()
    {
        $err = '';
        if (!$this->initid) {
            return "";
        }

        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \DocUTag::class);
        $q->Query(
            0,
            0,
            "TABLE",
            sprintf("update docutag set id=%d where initid=%d and (not fixed)", $this->id, $this->initid)
        );

        return $err;
    }

    /**
     * search all user tag for the document
     *
     * @param string  $tag         tag to search
     * @param boolean $allrevision view tags for all revision
     * @param boolean $allusers    view tags of all users
     *
     * @return array user tags key=>value
     */
    final public function searchUTags($tag = "", $allrevision = true, $allusers = false)
    {
        if (!$this->initid) {
            return [];
        }

        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \DocUTag::class);
        if (!$allusers) {
            $q->addQuery("uid=" . intval(ContextManager::getCurrentUser()->id));
        }
        if ($tag) {
            $q->addQuery("tag = '" . pg_escape_string($tag) . "'");
        }
        if ($allrevision) {
            $q->addQuery("initid = " . $this->initid);
        } else {
            $q->addQuery("id = " . $this->id);
        }
        $r = $q->Query(0, 0, "TABLE");
        if ($q->nb == 0) {
            $r = array();
        }
        return $r;
    }

    /**
     * verify if document is really fixed (verify in database)
     *
     * @return bool
     */
    public function isFixed()
    {
        return isFixedDoc($this->dbaccess, $this->id);
    }

    /**
     * Create a new \revision of a document
     * the current document is revised (became a fixed document)
     * a new \revision is created, a new \identifier if set
     *
     * @api Create a new \revision of a document
     *
     * @param string $comment the comment of the revision
     *
     * @return string error text (empty if no error)
     */
    final public function revise($comment = '')
    {
        // first control
        if ($this->locked == -1) {
            return _("document already revised");
        }
        if ($this->isFixed()) {
            $err = _("document already revised");
            $this->addHistoryEntry($err, \DocHisto::ERROR, "REVERROR");
            return $err;
        }
        $err = $this->getHooks()->trigger(SmartHooks::PREREVISE);
        if ($err) {
            return $err;
        }
        $err = $this->controlAccess("edit");
        if ($err != "") {
            return ($err);
        }

        $fdoc = $this->getFamilyDocument();

        if ($fdoc->schar == "S") {
            return sprintf(_("the document of %s family cannot be revised"), $fdoc->title);
        }
        $locked = $this->locked;
        $allocated = $this->allocated;
        $postitid = $this->postitid; // transfert post-it to latest revision
        $this->locked = -1; // the file is archived
        $this->lmodify = 'N'; // not locally modified
        $this->allocated = 0; // cannot allocated fixed document
        $this->owner = ContextManager::getCurrentUser()->id; // rev user
        $this->postitid = 0;
        $this->mdate = Date::getNow(true); // change rev date
        $point = "dcp:revision" . $this->id;
        DbManager::savePoint($point);
        if ($comment != '') {
            $this->addHistoryEntry($comment, \DocHisto::MESSAGE, "REVISION");
        }
        $err = $this->modify();
        if ($err != "") {
            DbManager::rollbackPoint($point);
            //$this->exec_query("rollback;");
            $this->select($this->id); // reset db values
            return $err;
        }
        // double control
        if (!$this->isFixed()) {
            $err = sprintf("track error revision [%s]", pg_last_error($this->dbid));
            $this->addHistoryEntry($err, \DocHisto::ERROR, "REVERROR");
            DbManager::commitPoint($point);
            return $err;
        }

        $fa = $this->GetFileAttributes(true); // copy cached values
        $ca = array();
        foreach ($fa as $k => $v) {
            $ca[] = $v->id . "_txt";
        }
        $this->affectColumn($ca, false);
        foreach ($ca as $a) {
            if ($this->$a != "") {
                $this->fields[$a] = $a;
            }
        }
        //$listvalue = $this->GetValues(); // save copy of values
        // duplicate values
        $olddocid = $this->id;
        $this->id = "";

        if ($locked > 0) {
            $this->locked = $locked;
        } // report the lock
        else {
            $this->locked = 0;
        }
        $this->allocated = $allocated; // report the allocate
        $this->revision = $this->revision + 1;
        $this->postitid = $postitid;

        // Remove last revision from cache to have coherent index.
        \Anakeen\Core\SEManager::cache()->removeDocumentById($olddocid);
        $err = $this->add();
        if ($err != "") {
            // restore last revision
            // $this->exec_query("rollback;");
            DbManager::rollbackPoint($point);

            $this->select($olddocid); // reset db values
            return $err;
        }

        DbManager::commitPoint($point);

        $this->refresh(); // to recompute possible dynamic profil variable
        if ($this->dprofid > 0) {
            $this->accessControl()->setProfil($this->dprofid);
        } // recompute profil if needed
        $err = $this->modify(); // need to applicate SQL triggers
        $this->UpdateVaultIndex();
        $this->refreshUTags();
        if ($err == "") {
            $this->addLog("revision", array(
                "id" => $this->id,
                "initid" => $this->initid,
                "revision" => $this->revision,
                "title" => $this->title,
                "fromid" => $this->fromid,
                "fromname" => $this->fromname
            ));
            // max revision
            $fdoc = $this->getFamilyDocument();
            $maxrev = intval($fdoc->maxrev);
            if ($maxrev > 0) {
                if ($this->revision > $maxrev) {
                    // need delete first revision

                    /**
                     * @var $revs array
                     */
                    $revs = $this->getRevisions("TABLE", "ALL");
                    for ($i = $maxrev; $i < count($revs); $i++) {
                        $d = SEManager::getDocumentFromRawDocument($revs[$i]);
                        if ($d) {
                            $d->_destroy(true);
                        }
                    }
                }
            }
            $msg = $this->getHooks()->trigger(SmartHooks::POSTREVISE);
            if ($msg) {
                $this->addHistoryEntry($msg, \DocHisto::MESSAGE, "POSTREVISE");
            }
            if ($this->hasChanged) {
                //in case of change in postStore
                $err = $this->modify();
                if ($err) {
                    \Anakeen\Core\Utils\System::addWarningMsg($err);
                }
            }
        }

        return $err;
    }


    /**
     * Set a free state to the document
     * for the document without workflow
     * a new \revision is created
     *
     * @param string $newstateid the document id of the state (FREESTATE family)
     * @param string $comment    the comment of the state change
     * @param bool   $revision   if false no revision are made
     *
     * @return string error text (empty if no error)
     */
    final public function changeFreeState($newstateid, $comment = '', $revision = true)
    {
        if ($this->wid > 0) {
            return sprintf(_("cannot set free state in workflow controlled document %s"), $this->title);
        }
        if ($this->wid == -1) {
            return sprintf(_("cannot set free state for document %s: workflow not allowed"), $this->title);
        }
        if (!$this->isRevisable()) {
            return sprintf(_("cannot set free state for document %s: document cannot be revised"), $this->title);
        }
        if ($newstateid == 0) {
            $this->state = "";
            $err = $this->modify(false, array(
                "state"
            ));
            if ($err == "") {
                $comment = sprintf(_("remove state : %s"), $comment);
                if ($revision) {
                    $err = $this->revise($comment);
                } else {
                    $err = $this->addHistoryEntry($comment);
                }
            }
        } else {
            $state = SEManager::getDocument($newstateid);
            if (!$state || !$state->isAlive()) {
                return sprintf(_("invalid freestate document %s"), $newstateid);
            }
            if ($state->fromid != 39) {
                return sprintf(_("not a freestate document %s"), $state->title);
            }

            $this->state = $state->id;
            $err = $this->modify(false, array(
                "state"
            ));
            if ($err == "") {
                $comment = sprintf(_("change state to %s : %s"), $state->title, $comment);
                if ($revision) {
                    $err = $this->revise($comment);
                } else {
                    $err = $this->addHistoryEntry($comment);
                }
            }
        }
        return $err;
    }

    /**
     * set state for a document controled by a workflow
     * apply associated transaction
     *
     * @api set state for a document controled by a workflow
     *
     * @param string $newstate    the new \state
     * @param string $comment     optional comment to set in history
     * @param bool   $force       is true when it is the second passage (without interactivity)
     * @param bool   $withcontrol set to false if you want to not verify control permission ot transition
     * @param bool   $wm1         set to false if you want to not apply m1 methods
     * @param bool   $wm2         set to false if you want to not apply m2 methods
     * @param bool   $wneed       set to false to not test required attributes
     * @param bool   $wm0         set to false if you want to not apply m0 methods
     * @param bool   $wm3         set to false if you want to not apply m3 methods
     * @param string $msg         return message from m2 or m3
     *
     * @return string error message empty if no error
     */
    final public function setState(
        $newstate,
        $comment = '',
        $force = false,
        $withcontrol = true,
        $wm1 = true,
        $wm2 = true,
        $wneed = true,
        $wm0 = true,
        $wm3 = true,
        &$msg = ''
    ) {
        if ($newstate == "") {
            return _("no state specified");
        }
        if (!$this->wid) {
            return _("document is not controlled by a workflow");
        }
        /**
         * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
         */
        $wdoc = SEManager::getDocument($this->wid);
        if (!$wdoc || !$wdoc->isAlive()) {
            return _("assigned workflow is not alive");
        }
        try {
            $wdoc->set($this);
            $err = $wdoc->changeState($newstate, $comment, $force, $withcontrol, $wm1, $wm2, $wneed, $wm0, $wm3, $msg);
        } catch (\Dcp\Exception $e) {
            $err = sprintf(
                _("Unexpected transition error on workflow %s [%d] : %s"),
                $wdoc->title,
                $wdoc->id,
                $e->getMessage()
            );
            \Anakeen\Core\LogException::writeLog($e);
        }
        return $err;
    }

    /**
     * return the state of a document
     * if document has workflow it is the key
     * if document state is a free state it is the name of the state
     *
     * @api get the state of a document
     * @return string the state - empty if no state
     */
    public function getState()
    {
        if ($this->wid > 0) {
            return $this->state;
        }
        if (is_numeric($this->state) && ($this->state > 0)) {
            $state = $this->getTitle($this->state);
            return $state;
        }

        return $this->state;
    }

    /**
     * return the color associated for the state of a document
     * if document has workflow : the color state
     * if document state is a free state the color
     *
     * @param string $def default color if state not found or color is empty
     *
     * @return string the color of the state - empty if no state
     */
    public function getStateColor($def = "")
    {
        if ($this->wid > 0) {
            /**
             * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
             */
            $wdoc = SEManager::getDocument($this->wid);
            if ($wdoc && $wdoc->isAffected()) {
                return $wdoc->getColor($this->state, $def);
            }
        } else {
            if (is_numeric($this->state) && ($this->state > 0)) {
                $state = $this->getDocValue($this->state, "frst_color", $def);
                return $state;
            }
        }
        return $def;
    }

    /**
     * return the action associated for the state of a document
     * if document has workflow : the action label description
     * if document state is a free state : state description
     *
     * @param string $def default activity is activity is empty
     *
     * @return string the color of the state - empty if no state
     */
    final public function getStateActivity($def = "")
    {
        if ($this->wid > 0) {
            /**
             * @var \Anakeen\SmartStructures\Wdoc\WDocHooks $wdoc
             */
            $wdoc = SEManager::getDocument($this->wid);
            if ($wdoc->isAffected()) {
                return $wdoc->getActivity($this->state, $def);
            }
        } else {
            if (is_numeric($this->state) && ($this->state > 0)) {
                $stateact = $this->getDocValue($this->state, "frst_desc", $def);
                return $stateact;
            }
        }
        return $def;
    }

    /**
     * return state label if ficed document else activity label
     * if not activity return state
     *
     * @return string localized state label
     */
    final public function getStatelabel()
    {
        if ($this->locked == -1) {
            $stateValue = $this->getState();
        } else {
            $stateValue = $this->getStateActivity($this->getState());
        }
        return (empty($stateValue) ? '' : _($stateValue));
    }


    /**
     * return the copy (duplication) of the document
     * the copy is created to the database
     * the profil of the copy is the default profil according to his family
     * the copy is not locked and if it is related to a workflow, his state is the first state
     *
     * @api duplicate document
     *
     * @param bool $temporary if true the document create it as temporary document
     * @param bool $control   if false don't control acl create (generaly use when temporary is true)
     * @param bool $linkfld   if true and document is a folder then documents included in folder
     *                        are also inserted in the copy (are not duplicated) just linked
     * @param bool $copyfile  if true duplicate files of the document
     *
     * @return \Anakeen\Core\Internal\SmartElement |string in case of error return a string that indicate the error
     * @throws \Dcp\Exception
     */
    final public function duplicate($temporary = false, $linkfld = false, $copyfile = false)
    {
        if ($this->fromid == '') {
            throw new \Dcp\Exception(\ErrorCode::getError('DOC0203'));
        }
        try {
            if ($this->isUnderControl()) {
                $family = SEManager::getFamily($this->fromid);

                $err = $family->controlAccess('create');
                if ($err != "") {
                    throw new \Dcp\Exception("DOC0131", $family->name);
                }
            }
            $copy = SEManager::createDocument($this->fromid);
        } catch (\Dcp\Core\Exception $e) {
            return false;
        }

        /**
         * @var \Anakeen\Core\Internal\SmartElement $copy
         */
        $err = $copy->transfertValuesFrom($this);
        if ($err != "") {
            return $err;
        }

        $copy->id = "";
        $copy->initid = "";
        $copy->revision = "0";
        $copy->locked = "0";
        $copy->allocated = "0";
        $copy->state = "";
        $copy->icon = $this->icon;

        if ($temporary) {
            $copy->doctype = "T";
            $copy->profid = 0;
            $copy->dprofid = 0;
        } else {
            $cdoc = $this->getFamilyDocument();
            $copy->accessControl()->setProfil($cdoc->cprofid);
        }

        $this->inHook = true;
        $err = $copy->getHooks()->trigger(SmartHooks::PREDUPLICATE, $this);
        $this->inHook = false;
        if ($err != "") {
            return $err;
        }

        $err = $copy->add();
        if ($err != "") {
            return $err;
        }
        $copy->addHistoryEntry(sprintf(_("copy from document #%d -%s-"), $this->id, $this->title));

        if ($copyfile) {
            $copy->duplicateFiles();
        }

        $copy->inHook = true;
        $msg = $copy->getHooks()->trigger(SmartHooks::POSTDUPLICATE);
        $copy->inHook = false;
        if ($msg != "") {
            $copy->addHistoryEntry($msg, \DocHisto::MESSAGE);
        }

        $copy->Modify();
        if ($linkfld && method_exists($copy, "insertFolder")) {
            /**
             * @var \Anakeen\SmartStructures\Dir\DirHooks $copy
             */
            $copy->insertFolder($this->initid);
        }

        return $copy;
    }


    final public function translate($docid, $translate)
    {
        $doc = SEManager::getDocument($docid);
        if ($doc && $doc->isAlive()) {
            foreach ($translate as $afrom => $ato) {
                $this->setValue($ato, $doc->getRawValue($afrom));
            }
        }
    }


    /**
     * lock document
     *
     * the auto lock is unlocked when the user discard edition or when he's modify document
     *
     * @param bool $auto   if true it is a automatic lock due to an edition (@see \Anakeen\Core\Internal\SmartElement::editcard()}
     * @param int  $userid if set lock with another userid, the edit control will be disabled
     *
     * @return string error message, if no error empty string, if message
     * @see \Anakeen\Core\Internal\SmartElement::CanLockFile()
     * @see \Anakeen\Core\Internal\SmartElement::unlock()
     */
    final public function lock($auto = false, $userid = 0)
    {
        $err = "";
        if ($userid == 0) {
            $err = $this->CanLockFile();
            if ($err != "") {
                return $err;
            }
            $userid = ContextManager::getCurrentUser()->id;
        } else {
            $this->disableAccessControl();
        }
        // test if is not already locked
        if ($auto) {
            if (($userid != 1) && ($this->locked == 0)) {
                $this->locked = -$userid; // in case of auto lock the locked id is negative
                $err = $this->modify(false, array(
                    "locked"
                ));
                if (!$err) {
                    $this->addLog('lock');
                }
            }
        } else {
            if (($this->locked != $userid) || ($this->lockdomainid)) {
                $this->locked = $userid;
                $err = $this->modify(false, array(
                    "locked"
                ));
                if (!$err) {
                    $this->addLog('lock');
                }
            }
        }
        $this->restoreAccessControl();

        return $err;
    }

    /**
     * unlock document
     *
     * the automatic unlock is done only if the lock has been set automatically also
     * the explicit unlock, unlock in all case (if CanUnLockFile)
     *
     * @param bool $auto  if true it is a automatic unlock
     * @param bool $force if true no control oif can unlock
     *
     * @return string error message, if no error empty string
     * @see \Anakeen\Core\Internal\SmartElement::CanUnLockFile()
     * @see \Anakeen\Core\Internal\SmartElement::lock()
     */
    final public function unLock($auto = false, $force = false)
    {
        $err = '';
        if ($this->locked == 0) {
            return "";
        }
        if (!$force) {
            $err = $this->CanUnLockFile();
        }
        if ($err != "") {
            return $err;
        }

        if ($auto) {
            if ($this->locked < -1) {
                $this->locked = "0";
                $this->modify(false, array(
                    "locked"
                ));
                if (!$err) {
                    $this->addLog('unlock');
                }
            }
        } else {
            if ($this->locked != -1) {
                $this->locked = "0";
                $this->lockdomainid = '';
                $this->modify(false, array(
                    "locked",
                    "lockdomainid"
                ));
                if (!$err) {
                    $this->addLog('unlock');
                }
            }
        }

        return "";
    }

    /**
     * allocate document
     *
     * affect a document to a user
     *
     * @param int    $userid   the system identifier of the user to affect
     * @param string $comment  message for allocation
     * @param bool   $revision if false no revision are made
     * @param bool   $autolock if false no lock are made
     *
     * @return string error message, if no error empty string, if message
     */
    final public function allocate($userid, $comment = "", $revision = false, $autolock = true)
    {
        $err = $this->canEdit();
        if ($err != "") {
            $err = _("Affectation aborded") . "\n" . $err;
        }
        if ($err == "") {
            $u = new \Anakeen\Core\Account("", $userid);
            if ($u->isAffected()) {
                if ($err != "") {
                    $err = _("Affectation aborded") . "\n" . $err;
                }
                // no test if allocated can edit document
                //$err=$this->ControlUser($u->id,"edit");
                if ($err == "") {
                    $this->addHistoryEntry(sprintf(_("Affected to %s %s"), $u->firstname, $u->lastname));
                    if ($comment) {
                        if ($revision) {
                            $this->revise(sprintf(_("Affected for %s"), $comment));
                        } else {
                            $this->addHistoryEntry(sprintf(_("Affected for %s"), $comment));
                        }
                    }
                    $this->addLog('allocate', array(
                        "allocated" => array(
                            "id" => $u->id,
                            "firstname" => $u->firstname,
                            "lastname" => $u->lastname
                        )
                    ));

                    $this->delUTag(ContextManager::getCurrentUser()->id, "AFFECTED"); // TODO need delete all AFFECTED tag
                    $this->addUTag($userid, "AFFECTED", $comment);
                    if ($autolock) {
                        $err = $this->lock(false, $userid);
                    }
                }
            } else {
                $err = _("Affectation aborded : user not know");
            }
        }
        if ($err == "") {
            $this->allocated = $userid;
            $this->modify(true, array(
                "allocated"
            ), true);
        }

        return $err;
    }

    /**
     * unallocate document
     *
     * unaffect a document to a user
     * only the allocated user can unallocate and also users which has unlock acl
     *
     * @param string $comment  message for unallocation
     * @param bool   $revision if false no revision are made
     *
     * @return string error message, if no error empty string, if message
     */
    final public function unallocate($comment = "", $revision = true)
    {
        if ($this->allocated == 0) {
            return "";
        }
        $err = $this->canEdit();
        if ($err == "") {
            if ($this->isUnderControl() && (ContextManager::getCurrentUser()->id != $this->allocated)) {
                $err = $this->controlAccess("unlock");
            }
        }

        if ($err == "") {
            $u = new \Anakeen\Core\Account("", $this->allocated);
            if ($u->isAffected()) {
                $err = $this->unlock();
                if ($err == "") {
                    $this->delUTag(ContextManager::getCurrentUser()->id, "AFFECTED"); // TODO need delete all AFFECTED tag
                    if ($revision) {
                        $this->revise(sprintf(_("Unallocated of %s %s : %s"), $u->firstname, $u->lastname, $comment));
                    } else {
                        $this->addHistoryEntry(sprintf(
                            _("Unallocated of %s %s: %s"),
                            $u->firstname,
                            $u->lastname,
                            $comment
                        ));
                    }
                }
            } else {
                $err = _("user not know");
            }
        }
        if ($err == "") {
            $this->allocated = 0;
            $this->modify(true, array(
                "allocated"
            ), true);
            $this->addLog('unallocate');
        }

        if ($err != "") {
            $err = _("Unallocate aborded") . "\n" . $err;
        }
        return $err;
    }

    /**
     * return icon url
     * if no icon found return doc.png
     *
     * @param string $idicon
     * @param int    $size    width size
     * @param int    $otherId icon for other document id
     *
     * @return string icon url
     */
    final public function getIcon($idicon = "", $size = null, $otherId = null)
    {
        $apiURL = '/' . CollectionDataFormatter::APIURL;
        $efile = null;

        if ($idicon == "") {
            $idicon = $this->icon;
        }
        if ($idicon == "") {
            $idicon = "doc.png";
        }

        if (preg_match(PREGEXPFILE, $idicon, $reg)) {
            if ($idicon[0] === "!") {
                if (!$size) {
                    $size = "20";
                }
                $efile = sprintf(
                    "%sdocuments/%d/images/%s/-1/sizes/%sx%s.png",
                    $apiURL,
                    ($otherId == null) ? $this->id : $otherId,
                    rawurlencode($reg["name"]),
                    $size,
                    $size
                );
                return $efile;
            }
            if ($size) {
                $efile = sprintf(
                    "%simages/recorded/sizes/%sx%sc/%s",
                    $apiURL,
                    $size,
                    $size,
                    $reg["vid"]
                );
            } else {
                $efile = sprintf(
                    "%simages/recorded/original/%s",
                    $apiURL,
                    $reg["vid"]
                );
            }
            return $efile;
        } else {
            if ($size) {
                $efile = sprintf(
                    "%simages/assets/sizes/%sx%sc/%s",
                    $apiURL,
                    $size,
                    $size,
                    $idicon
                );
            } else {
                $efile = sprintf(
                    "%simages/assets/original/%s",
                    $apiURL,
                    $idicon
                );
            }
            return $efile;
        }
    }

    /**
     * change icon for a class or a simple doc
     *
     * @param string $icon basename icon file
     *
     * @return string empty string on success, non-empty string on error
     */
    final public function changeIcon($icon)
    {
        $point = "dcp:changeIcon";

        DbManager::savePoint($point);

        if (preg_match(PREGEXPFILE, $icon, $reg)) {
            $fileData = \Dcp\VaultManager::getFileInfo($reg["vid"]);
            if (!$fileData->public_access) {
                $icon = "!" . $icon;
            }
        }

        if ($this->doctype == "C") { //  a class
            $fromid = $this->initid;
            $tableName = sprintf("doc%s", $fromid);
            if ($this->icon != "") {
                // need disabled triggers to increase speed
                $qt = array();
                $qt[] = sprintf("ALTER TABLE %s DISABLE TRIGGER ALL", pg_escape_identifier($tableName));
                $qt[] = sprintf(
                    "UPDATE %s SET icon = %s WHERE (fromid = %s) AND (doctype != 'C') AND ((icon = %s) OR (icon IS NULL))",
                    pg_escape_identifier($tableName),
                    pg_escape_literal($icon),
                    pg_escape_literal($fromid),
                    pg_escape_literal($this->icon)
                );
                $qt[] = sprintf("ALTER TABLE %s ENABLE TRIGGER ALL", pg_escape_identifier($tableName));
                $qt[] = sprintf(
                    "UPDATE DOCREAD SET icon = %s WHERE (fromid = %s) AND (doctype != 'C') AND ((icon = %s) OR (icon IS NULL))",
                    pg_escape_literal($icon),
                    pg_escape_literal($fromid),
                    pg_escape_literal($this->icon)
                );
                DbManager::query(implode("; ", $qt));
            } else {
                $q = sprintf(
                    "UPDATE %s SET icon = %s WHERE (fromid = %s) AND (doctype != 'C') AND (icon IS NULL)",
                    pg_escape_identifier($tableName),
                    pg_escape_literal($icon),
                    pg_escape_literal($fromid)
                );
                DbManager::query($q);
            }
        }
        //    $this->title = AddSlashes($this->title);
        $this->icon = $icon;
        if (($err = $this->Modify()) != '') {
            DbManager::rollbackPoint($point);
            return $err;
        }
        DbManager::commitPoint($point);

        $this->UpdateVaultIndex();
        return '';
    }

    /**
     * declare a dependance between several attributes
     *
     * @param string $in  attributes id use for compute $out attributes separates by commas
     * @param string $out attributes id calculated by $in attributes separates by commas
     */
    final public function addParamRefresh($in, $out)
    {
        // to know which attribut must be disabled in edit mode
        $tin = explode(",", strtolower($in));
        $tout = explode(",", strtolower($out));
        $this->paramRefresh["$in:$out"] = array(
            "in" => $tin,
            "out" => $tout
        );
    }


    /**
     * Special Refresh
     * called when refresh document : when view, modify document - generally when access to the document
     *
     * @note during preRefresh edit control is disabled
     * @see  \Anakeen\Core\Internal\SmartElement::refresh
     * @api  hook called in begining of refresh before update computed attributes
     */
    public function preRefresh()
    {
        return '';
    }

    /**
     * Special Refresh Generated automatically
     * is defined in generated child classes
     *
     * @param bool $onlyspec
     *
     * @return string
     */
    public function specRefreshGen($onlyspec = false) {
        return '';
    }

    /**
     * Special Refresh Generated for a single attribute
     *
     * @param string $attrId     Attribute's name
     * @param string $callMethod Method to apply
     *
     * @return string Error message or empty string on succcess
     * @throws \Dcp\Exception
     */
    protected function specRefreshGenAttribute($attrId, $callMethod)
    {
        $err = '';
        $oAttr = $this->getAttribute($attrId);
        if (!$oAttr) {
            throw new \Dcp\Exception(\ErrorCode::getError('ATTR1212', $callMethod, $this->fromname));
        }

        if ($oAttr->inArray()) {
            $this->completeArrayRow($oAttr->fieldSet->id);
            $t = $this->getMultipleRawValues($attrId);
            foreach ($t as $k => $v) {
                $err .= $this->setValue($attrId, $this->applyMethod($callMethod, '', $k), $k);
            }
        } else {
            $err .= $this->setValue($attrId, $this->applyMethod($callMethod));
        }

        return $err;
    }

    /**
     * recompute all computed attribut
     * and save the document in database if changes occurred
     *
     * @api refresh document by calling specRefresh and update computed attributes
     * @return string information message
     */
    final public function refresh()
    {
        if ($this->locked == -1) {
            return '';
        } // no refresh revised document
        if (($this->doctype == 'C') || ($this->doctype == 'Z')) {
            return '';
        } // no refresh for family  and zombie document
        if ($this->lockdomainid > 0) {
            return '';
        }
        $changed = $this->hasChanged;
        if (!$changed) {
            $this->disableAccessControl();
        } // disabled control just to refresh
        $msg = $this->getHooks()->trigger(SmartHooks::PREREFRESH);
        // if ($this->id == 0) return; // no refresh for no created document
        $msg .= $this->SpecRefreshGen();
        $msg .= $this->getHooks()->trigger(SmartHooks::POSTREFRESH);
        if ($this->hasChanged && $this->id > 0) {
            $this->lastRefreshError = $this->modify(); // refresh title
        }
        if (!$changed) {
            $this->restoreAccessControl();
        }
        return $msg;
    }

    /**
     * Recompute file name in concordance with rn option
     *
     */
    public function refreshRn()
    {
        $err = "";
        $fa = $this->GetFileAttributes();
        foreach ($fa as $aid => $oa) {
            $rn = $oa->getOption("rn");
            if ($rn) {
                if ($oa->inArray()) {
                    $t = $this->getMultipleRawValues($oa->id);
                    foreach ($t as $k => $v) {
                        $cfname = $this->vault_filename($oa->id, false, $k);
                        if ($cfname) {
                            $fname = $this->applyMethod($rn, "", $k, array(
                                $cfname
                            ));

                            if ($fname != $cfname) {
                                $err .= $this->renameFile($oa->id, $fname, $k);
                            }
                        }
                    }
                } else {
                    $cfname = $this->vault_filename($oa->id);
                    if ($cfname) {
                        $fname = $this->applyMethod($rn, "", -1, array(
                            $cfname
                        ));
                        if ($fname != $cfname) {
                            $err .= $this->renameFile($oa->id, $fname);
                        }
                    }
                }
            }
        }
        return $err;
    }

    /**
     * replace % tag of a link attribute
     *
     * @param string $link url to analyze
     * @param int    $k    index
     *
     * @return bool|string
     */
    final public function urlWhatEncode($link, $k = -1)
    {
        $urllink = "";
        $mi = strlen($link);
        for ($i = 0; $i < $mi; $i++) {
            switch ($link[$i]) {
                case '%':
                    $i++;

                    if (isset($link[$i]) && $link[$i] == "%") {
                        $urllink .= "%"; // %% is %
                    } else {
                        $optional = false;
                        if (isset($link[$i]) && $link[$i] == "?") {
                            $i++;
                            $optional = true;
                        }
                        if (isset($link[$i + 1]) && $link[$i + 1] == "%") {
                            // special link
                            switch ($link[$i]) {
                                case "B": // baseurl
                                    $urllink .= ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_BASEURL", "?");
                                    break;

                                case "S": // standurl
                                    $urllink .= ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_STANDURL", "?");
                                    break;

                                case "U": // extern url
                                    $urllink .= ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_EXTERNURL");
                                    break;

                                case "I": // id
                                    $urllink .= $this->id;
                                    break;

                                case "T": // title
                                    $urllink .= rawurlencode($this->title);
                                    break;

                                default:
                                    break;
                            }
                            $i++; // skip end '%'
                        } else {
                            $sattrid = "";
                            while (($i < $mi) && ($link[$i] != "%")) {
                                $sattrid .= $link[$i];
                                $i++;
                            }
                            if (preg_match('/^[a-z0-9_\\\\]*::/i', $sattrid)) {
                                $mapArgs = array();
                                foreach (self::$infofields as $propId => $prop) {
                                    $mapArgs[$propId] = $this->getPropertyValue($propId);
                                }
                                $urllink .= $this->applyMethod($sattrid, $sattrid, $k, array(), $mapArgs);
                            } else {
                                if (!in_array(mb_strtolower($sattrid), $this->fields)) {
                                    if (preg_match('/[0-9A-F][0-9A-F]/', $sattrid)) {
                                        $urllink .= '%' . $sattrid; // hexa code
                                        if (isset($link[$i]) && $link[$i] == '%') {
                                            $urllink .= '%';
                                        }
                                    } else {
                                        if (!$optional) {
                                            return false;
                                        }
                                    }
                                } else {
                                    /**
                                     * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
                                     */
                                    $oa = $this->GetAttribute($sattrid);
                                    if (($k >= 0) && ($oa && $oa->repeat)) {
                                        $tval = $this->getMultipleRawValues($sattrid);

                                        $ovalue = isset($tval[$k]) ? chop($tval[$k]) : '';
                                    } else {
                                        // get property also
                                        $ovalue = $this->getRawValue($sattrid);
                                    }
                                    if ($ovalue == "" && (!$optional)) {
                                        return false;
                                    }

                                    if (strstr($ovalue, "\n")) {
                                        $ovalue = str_replace("\n", '\n', $ovalue);
                                    }
                                    $urllink .= rawurlencode($ovalue); // need encode
                                }
                            }
                        }
                    }
                    break;

                case '{':
                    $i++;

                    $sattrid = "";
                    while ($link[$i] != '}') {
                        $sattrid .= $link[$i];
                        $i++;
                    }
                    //	  print "attr=$sattrid";
                    $ovalue = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, $sattrid);
                    $urllink .= rawurlencode($ovalue);

                    break;

                default:
                    $urllink .= $link[$i];
            }
        }
        $urllink = $this->urlWhatEncodeSpec($urllink); // complete in special case families
        return (chop($urllink));
    }

    /**
     * virtual method must be use in child families if needed complete url
     *
     * @param string $l url to encode
     *
     * @return string
     */
    public function urlWhatEncodeSpec($l)
    {
        return $l;
    }

    /**
     * convert flat attribute value to an array for multiple attributes
     *
     * use only for specific purpose. If need typed attributes use \Anakeen\Core\Internal\SmartElement::getAttribute()
     *
     * @api convert flat attribute value to an array
     * @see \Anakeen\Core\Internal\SmartElement::getAttributeValue
     *
     * @param string $v value
     *
     * @return array
     */
    public static function rawValueToArray($v)
    {
        if ($v === "" || $v === null) {
            return array();
        }
        return Postgres::stringToArray($v);
    }


    /**
     * convert array value to flat attribute value
     *
     * @api convert array value to flat attribute value
     *
     * @param array $v
     *
     * @return string
     */
    public static function arrayToRawValue($v)
    {
        if (count($v) == 0) {
            return "";
        }
        return Postgres::arrayToString($v);
    }


    /**
     * return an url to download for file attribute
     *
     * @param string         $attrid     attribute identifier
     * @param int            $index      set to row rank if it is in array else use -1
     * @param bool           $cache      set to true if file may be persistent in client cache
     * @param bool           $inline     set to true if file must be displayed in web browser
     * @param string         $otherValue use another file value instead of attribute value
     * @param \VaultFileInfo $info       extra file info
     *
     * @return string the url anchor
     */
    public function getFileLink($attrid, $index = -1, $cache = false, $inline = false, $otherValue = '', $info = null)
    {
        if ($index === '' || $index === null) {
            $index = -1;
        }
        if (!$otherValue) {
            if ($index >= 0) {
                $avalue = $this->getMultipleRawValues($attrid, "", $index);
            } else {
                $avalue = $this->getRawValue($attrid);
            }
        } else {
            if ($index >= 0) {
                if (is_array($otherValue)) {
                    $avalue = $otherValue[$index];
                } else {
                    $avalue = $otherValue;
                }
            } else {
                $avalue = $otherValue;
            }
        }
        $oa = $this->getAttribute($attrid);
        if ($oa->usefor === "Q" && $this->doctype !== "C") {
            $docid = $this->fromid;
        } else {
            $docid = $this->id;
        }

        if (preg_match(PREGEXPFILE, $avalue, $reg)) {
            $fileKey = 0;
            if ($info) {
                $fileKey = strtotime($info->mdate);
                // Double quote not supported by all browsers - replace by minus
                $fname = str_replace('"', '-', $info->name);
            } else {
                $fname = str_replace('"', '-', $reg[3]);
            }
            // will be rewrited by apache rules

            $url = sprintf(
                "file/%s/%d/%s/%s/%s?cache=%s&inline=%s",
                $docid,
                $fileKey,
                $attrid,
                $index,
                rawurlencode($fname),
                $cache ? "yes" : "no",
                $inline ? "yes" : "no"
            );
            if ($this->cvid > 0) {
                $viewId = getHttpVars("vid");
                if ($viewId) {
                    $url .= '&cvViewid=' . $viewId;
                }
            }
            return $url;
        }
        return '';
    }

    /**
     * return an html anchor to a document
     *
     * @api return an html anchor to a document
     *
     * @param int         $id       identifier of document
     * @param string      $target   window target
     * @param bool        $htmllink must be true else return nothing
     * @param bool|string $title    should we override default title
     * @param bool        $js       should we add a javascript contextual menu
     * @param string      $docrev   style of link (default:latest, other values: fixed or state(xxx))
     * @param bool        $viewIcon set to true to have icon in html link
     *
     * @return string the html anchor
     */
    final public function getDocAnchor(
        $id,
        $target = "_self",
        $htmllink = true,
        $title = false,
        $js = true,
        $docrev = "latest",
        $viewIcon = false
    ) {
        $latest = ($docrev == "latest" || $docrev == "");
        if ($htmllink) {
            if (!$title) {
                $title = $this->getHTMLTitle(strtok($id, '#'), '', $latest);
            } else {
                $title = $this->htmlEncode($title);
            }
            if (trim($title) == "") {
                if ($id < 0) {
                    $a = "<a>" . sprintf(_("document not exists yet")) . "</a>";
                } else {
                    $a = "<a>" . sprintf(_("unknown document id %s"), $id) . "</a>";
                }
            } else {
                /* Setup base URL */
                $ul = '?';
                $specialUl = false;
                switch ($target) {
                    case "mail":
                        $js = false;
                        $mUrl = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_MAILACTIONURL");
                        if (strstr($mUrl, '%')) {
                            if ($this->id != $id) {
                                $mDoc = SEManager::getDocument($id);
                            } else {
                                $mDoc = $this;
                            }
                            $ul = htmlspecialchars($mDoc->urlWhatEncode($mUrl));
                            $specialUl = true;
                        } else {
                            $ul = htmlspecialchars(ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_MAILACTIONURL"));
                            $ul .= "&amp;id=$id";
                        }
                        break;


                    default:
                        $ul .= sprintf("/api/v2/documents/%s.html", $id);
                }
                /* Add target's specific elements to base URL */

                if (!$specialUl) {
                    if ($docrev == "latest" || $docrev == "" || !$docrev) {
                        $ul .= "&amp;latest=Y";
                    } elseif ($docrev != "fixed") {
                        // validate that docrev looks like state(xxx)
                        if (preg_match("/^state\\(([a-zA-Z0-9_:-]+)\\)/", $docrev, $matches)) {
                            $ul .= "&amp;state=" . $matches[1];
                        }
                    }
                }
                if ($js) {
                    $ajs = "oncontextmenu=\"popdoc(event,'$ul');return false;\"";
                } else {
                    $ajs = "";
                }

                $ajs .= sprintf(' documentId="%s" ', $id);
                if ($viewIcon) {
                    DbManager::query(sprintf('select icon from docread where id=%d', $id), $iconValue, true, true);
                    $ajs .= sprintf(
                        'class="relation" style="background-image:url(%s)"',
                        $this->getIcon($iconValue, 14)
                    );
                }
                $a = "<a $ajs target=\"$target\" href=\"$ul\">$title</a>";
            }
        } else {
            if (!$title) {
                $a = $this->getHTMLTitle($id, '', $latest);
            } else {
                $a = $this->htmlEncode($title);
            }
        }
        return $a;
    }

    /**
     * return HTML formated value of an attribute
     *
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oattr
     * @param string                                       $value  raw value
     * @param string                                       $target html target in case of link
     * @param bool                                         $htmllink
     * @param int                                          $index
     * @param bool                                         $entities
     * @param bool                                         $abstract
     *
     * @return string the formated value
     */
    final public function getHtmlValue(
        $oattr,
        $value,
        $target = "_self",
        $htmllink = true,
        $index = -1,
        $entities = true,
        $abstract = false
    ) {
        if (!$this->htmlFormater) {
            $this->htmlFormater = new \DocHtmlFormat($this);
        }
        if ($this->formaterLevel == 0) {
            $htmlFormater = &$this->htmlFormater;
        } else {
            if (!isset($this->otherFormatter[$this->formaterLevel])) {
                $this->otherFormatter[$this->formaterLevel] = new \DocHtmlFormat($this);
            }
            $htmlFormater = $this->otherFormatter[$this->formaterLevel];
        }
        if ($htmlFormater->doc->id != $this->id) {
            $htmlFormater->setDoc($this);
        }
        $this->formaterLevel++;
        $r = $htmlFormater->getHtmlValue($oattr, $value, $target, $htmllink, $index, $entities, $abstract);
        $this->formaterLevel--;
        return $r;
    }

    /**
     * return an html anchor to a document
     *
     * @see \Anakeen\Core\Internal\SmartElement::getHtmlValue
     *
     * @param string $attrid attribute identifier
     * @param string $target html target in case of link
     * @param int    $htmllink
     * @param int    $index
     * @param bool   $entities
     * @param bool   $abstract
     *
     * @return string
     * @throws \Dcp\Exception
     */
    final public function getHtmlAttrValue(
        $attrid,
        $target = "_self",
        $htmllink = 2,
        $index = -1,
        $entities = true,
        $abstract = false
    ) {
        $oattr = $this->getAttribute($attrid);
        if (!$oattr) {
            throw new \Dcp\Exception('DOC0130', $attrid, $this->id, $this->fromid);
        }

        if ($index != -1) {
            $v = $this->getMultipleRawValues($attrid, "", $index);
        } else {
            $v = $this->getRawValue($attrid);
        }

        return $this->GetHtmlValue($oattr, $v, $target, $htmllink, $index, $entities, $abstract);
    }

    /**
     * Get a textual representation of the content of an attribute
     *
     * @param string $attrId        logical name of the attr
     * @param        $index
     * @param array  $configuration value config array : dateFormat => 'US' 'ISO', decimalSeparator => '.',
     *                              multipleSeparator => array(0 => 'arrayLine', 1 => 'multiple')
     *                              (defaultValue : dateFormat : 'US', decimalSeparator : '.',
     *                              multiple => array(0 => "\n", 1 => ", "))
     *
     * @return string|bool return false if attribute not found else the textual value
     */
    final public function getTextualAttrValue($attrId, $index = -1, array $configuration = array())
    {
        $objectAttr = $this->getAttribute($attrId);
        if ($objectAttr) {
            return $objectAttr->getTextualValue($this, $index, $configuration);
        } else {
            return $objectAttr;
        }
    }

    /**
     * get value for open document text template
     *
     * @param string $attrid   attribute identifier
     * @param string $target   unused
     * @param bool   $htmllink unused
     * @param int    $index    index rank in case of multiple attribute value
     *
     * @return string XML fragment
     */
    final public function getOooAttrValue(
        $attrid,
        /* @noinspection PhpUnusedParameterInspection */
        $target = "_self",
        /* @noinspection PhpUnusedParameterInspection */
        $htmllink = false,
        $index = -1
    ) {
        if ($index != -1) {
            $v = $this->getMultipleRawValues($attrid, "", $index);
        } else {
            $v = $this->getRawValue($attrid);
        }
        if ($v == "") {
            return $v;
        }
        return $this->getOooValue($this->getAttribute($attrid), $v, '', false, $index);
    }

    /**
     * return open document text format for attribute value
     *
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oattr
     * @param string                                       $value
     * @param string                                       $target   unused
     * @param bool                                         $htmllink unused
     * @param int                                          $index    index rank in case of multiple attribute value
     *
     * @return string XML fragment
     */
    final public function getOooValue(
        $oattr,
        $value,
        /* @noinspection PhpUnusedParameterInspection */
        $target = "_self",
        /* @noinspection PhpUnusedParameterInspection */
        $htmllink = false,
        $index = -1
    ) {
        if (!$this->oooFormater) {
            $this->oooFormater = new \DocOooFormat($this);
        }
        if ($this->oooFormater->doc->id != $this->id) {
            $this->oooFormater->setDoc($this);
        }
        return $this->oooFormater->getOooValue($oattr, $value, $index);
    }

    /**
     * Prevent displaying the document's title in the error message
     * if the user has no 'view' privilege
     *
     * @param \Anakeen\Core\Internal\SmartElement $doc
     * @param                                     $aclname
     *
     * @return string
     */
    protected function noPrivilegeMessage(\Anakeen\Core\Internal\SmartElement & $doc, $aclname)
    {
        /*
         * If the error message concerns the 'view' privilege, or the document
         * is confidential, or the user has no 'view' privilege on the
         * document, then we should not display the document's title
        */
        if (($aclname == 'view') || ($doc->isConfidential()) || ($doc->control('view') !== '')) {
            return sprintf(_("no privilege %s for document with id %d"), $aclname, $doc->id);
        }
        /*
         * Otherwise, display the error message with the document's title
        */
        return sprintf(_("no privilege %s for %s [%d]"), $aclname, $doc->getTitle(), $doc->id);
    }

    /**
     * Control Access privilege for document for current user
     *
     * @param string $aclname identifier of the privilege to test
     * @param bool   $strict  set tio true to test without notion of account susbstitute
     *
     * @return string empty means access granted else it is an error message (access unavailable)
     */
    public function control($aclname, $strict = false)
    {
        if (!$this->isAffected()) {
            return '';
        }

        if (ContextManager::getCurrentUser()->id == \Anakeen\Core\Account::ADMIN_ID) {
            return ""; // no profil or admin
        }
        if ($this->profid <= 0) {
            return ___("Profil not configured", "ank"); // no profil enabled
        }

        $err = $this->accessControl()->controlId($this->profid, $aclname, $strict);
        if ($err != "") {
            return $this->noPrivilegeMessage($this, $aclname);
        } else {
            // Edit rights on profiles must also be controlled by the 'modifyacl' acl
            if (($aclname == 'edit' || $aclname == 'delete' || $aclname == 'unlock') && $this->accessControl()->isRealProfile()) {
                return $this->accessControl()->controlId($this->profid, 'modifyacl', $strict);
            }
        }
        return '';
    }

    private function controlAccess($aclname, $strict = false)
    {
        if (!$this->isUnderControl()) {
            return ""; // uncontrolled mode
        }
        return $this->control($aclname, $strict);
    }

    /**
     * Control Access privilege for document for current user
     *
     * @api control document access
     *
     * @param string $aclName identifier of the privilege to test
     * @param bool   $strict  set tio true to test without notion of account susbstitute
     *
     * @return bool return true if access $aclName is granted, false else
     */
    public function hasPermission($aclName, $strict = false)
    {
        return ($this->control($aclName, $strict) == "");
    }

    /**
     * verify that the document exists and is not in trash (not a zombie)
     *
     * @api verify that the document exists and is not in trash
     * @return bool
     */
    final public function isAlive()
    {
        return ((\Anakeen\Core\Internal\DbObj::isAffected()) && ($this->doctype != 'Z'));
    }

    /**
     * add several triggers to update different tables (such as docread) or attributes (such as values)
     *
     * @param bool $onlydrop set to false for only drop triggers
     * @param bool $code
     *
     * @return string sql commands
     */
    final public function sqlTrigger($onlydrop = false, $code = false)
    {
        if (get_class($this) === \Anakeen\Core\SmartStructure::class) {
            $cid = "fam";
            $famId = $this->id;
        } else {
            if ($this->doctype == 'C') {
                return '';
            }
            if (intval($this->fromid) == 0) {
                return '';
            }

            $cid = $this->fromid;
            $famId = $this->fromid;
        }

        $sql = "";
        // delete all relative triggers
        $sql .= "select droptrigger('doc" . $cid . "');";
        if ($onlydrop) {
            return $sql;
        } // only drop
        if ($code) {
            $files = array();
            $lay = new \Layout("vendor/Anakeen/Core/Layout/sqltrigger.sql");
            $na = $this->GetNormalAttributes();
            $tvalues = array();
            $tsearch = array();
            $fulltext_c = array();
            foreach ($na as $k => $v) {
                $opt_searchcriteria = $v->getOption("searchcriteria", "");
                if (($v->type !== "array") && ($v->type !== "frame") && ($v->type !== "tab")) {
                    // values += any attribute
                        $tvalues[] = array(
                            "attrid" => $k,
                            "casttype" => ($v->isMultiple()===true)?"text[]":"text"
                        );
                    // svalues += attribute allowed to be indexed
                    if (($v->type != "file") && ($v->type != "image") && ($v->type != "password")
                        && ($opt_searchcriteria != "hidden")) {
                        $tsearch[] = array(
                            "attrid" => $k
                        );

                        $fulltext_c[] = array(
                            "attrid" => $k
                        );
                    }
                }
                if ($v->type == "file" && $opt_searchcriteria != "hidden") {
                    // fulltext += file attributes
                    $files[] = array(
                        "attrid" => $k . "_txt",
                        "vecid" => $k . "_vec"
                    );
                    // svalues += file attributes
                    $tsearch[] = array(
                        "attrid" => $k . "_txt"
                    );
                }
            }
            // fulltext += abstract attributes
            $tabstract = array();
            $na = $this->GetAbstractAttributes();
            foreach ($na as $k => $v) {
                $opt_searchcriteria = $v->getOption("searchcriteria", "");
                if ($opt_searchcriteria == "hidden") {
                    continue;
                }
                if (($v->type != "array") && ($v->type != "file") && ($v->type != "image")
                    && ($v->type != "password")) {
                    $tabstract[] = array(
                        "attrid" => $k
                    );
                }
            }
            $lay->setBlockData("ATTRFIELD", $tvalues);
            $lay->setBlockData("SEARCHFIELD", $tsearch);
            $lay->setBlockData("ABSATTR", $tabstract);
            $lay->setBlockData("FILEATTR", $files);
            $lay->setBlockData("FILEATTR2", $files);
            $lay->setBlockData("FILEATTR3", $files);
            $lay->setBlockData("FULLTEXT_C", $fulltext_c);
            $lay->set("hasattr", (count($tvalues) > 0));
            $lay->set("hassattr", (count($tsearch) > 0));
            $lay->set("hasabsattr", (count($tabstract) > 0));
            $lay->set("docid", $this->fromid);
            $sql = $lay->gen();
        } else {
            // the reset trigger must begin with 'A' letter to be proceed first (pgsql 7.3.2)
            if ($cid != "fam") {
                $sql .= "create trigger AUVR{$cid} BEFORE UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE resetlogicalname();";
                $sql .= "create trigger VSEARCH{$cid} BEFORE INSERT OR UPDATE  ON doc$cid FOR EACH ROW EXECUTE PROCEDURE searchvalues$cid();";
                 $sql.= "create trigger beforeiu{$cid} BEFORE INSERT OR UPDATE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE doc{$cid}_fieldvalues();";
            } else {
                $sql .= "create trigger UVdocfam before insert or update on docfam FOR EACH ROW EXECUTE PROCEDURE upvaldocfam();";
            }
            $sql .= "create trigger zread{$cid} AFTER INSERT OR UPDATE OR DELETE ON doc$cid FOR EACH ROW EXECUTE PROCEDURE setread();";
            $sql .= "create trigger FIXDOC{$cid} AFTER INSERT ON doc$cid FOR EACH ROW EXECUTE PROCEDURE fixeddoc();";
        }
        return $sql;
    }

    /**
     * add specials SQL indexes
     *
     * @return array sqls queries to create indexes
     */
    final public function getSqlIndex()
    {
        $t = array();
        $id = $this->fromid;
        if (static::$sqlindex) {
            $sqlindex = array_merge(static::$sqlindex, \Anakeen\Core\Internal\SmartElement::$sqlindex);
        } else {
            $sqlindex = \Anakeen\Core\Internal\SmartElement::$sqlindex;
        }
        foreach ($sqlindex as $k => $v) {
            if (!empty($v["unique"])) {
                $unique = "unique";
            } else {
                $unique = "";
            }
            if (!empty($v["using"])) {
                if ($v["using"][0] == "@") {
                    $v["using"] = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, substr($v["using"], 1));
                }
                $t[] = sprintf("CREATE $unique INDEX %s$id on  doc$id using %s(%s);\n", $k, $v["using"], $v["on"]);
            } else {
                $t[] = sprintf("CREATE $unique INDEX %s$id on  doc$id(%s);\n", $k, $v["on"]);
            }
        }
        return $t;
    }

    /**
     * return the basename of template file
     *
     * @param string $zone zone to parse
     *
     * @return string|null (return null if template not found)
     */
    public function getZoneFile($zone)
    {
        $index = -1;
        if ($zone == "") {
            return null;
        }

        $reg = $this->parseZone($zone);
        if (is_array($reg)) {
            $aid = $reg['layout'];
            if ($reg['index'] != '') {
                $index = $reg['index'];
            }
            $oa = $this->getAttribute($aid);
            if ($oa) {
                if ($oa->usefor != 'Q') {
                    $template = $this->getRawValue($oa->id);
                } else {
                    $template = $this->getFamilyParameterValue($aid);
                }
                if ($index >= 0) {
                    $tt = $this->rawValueToArray($template);
                    $template = $tt[$index];
                }

                if ($template == "") {
                    return null;
                }

                return $this->vault_filename_fromvalue($template, true);
            }
            return sprintf("%s/Apps/%s/Layout/%s", DEFAULT_PUBDIR, $reg['app'], $aid);
        }
        return null;
    }

    /**
     * return the character in third part of zone
     *
     * @param string $zone APP:LAYOUT:OPTIONS
     *
     * @return string single character
     */
    public function getZoneOption($zone = "")
    {


        $zoneElements = $this->parseZone($zone);
        if ($zoneElements === false) {
            return '';
        }

        return $zoneElements['modifier'];
    }

    /**
     * return the characters in fourth part of zone
     *
     * @param string $zone APP:LAYOUT:OPTIONS
     *
     * @return string
     */
    public function getZoneTransform($zone)
    {
        $zoneElements = $this->parseZone($zone);
        if ($zoneElements === false) {
            return '';
        }

        return $zoneElements['transform'];
    }

    /**
     * set default values define in family document
     * the format of the string which define default values is like
     * [US_ROLE|director][US_SOCIETY|alwaysNet]...
     *
     * @param array $tdefval      the default values
     * @param bool  $method       set to false if don't want interpreted values
     * @param bool  $forcedefault force default values
     *
     * @throws \Dcp\Exception
     */
    final public function setDefaultValues($tdefval, $method = true, $forcedefault = false)
    {
        if (is_array($tdefval)) {
            foreach ($tdefval as $aid => $dval) {
                /**
                 * @var \Anakeen\Core\SmartStructure\BasicAttribute $oattr
                 */
                $oattr = $this->getAttribute($aid);

                $ok = false;
                if (empty($oattr)) {
                    $ok = false;
                } elseif ($dval === '' || $dval === null) {
                    $ok = false;
                } elseif (!is_a($oattr, "Anakeen\Core\SmartStructure\BasicAttribute")) {
                    $ok = false;
                } elseif ($forcedefault) {
                    $ok = true;
                } elseif (!$oattr->inArray()) {
                    $ok = true;
                } elseif ($oattr->fieldSet->format != "empty" && $oattr->fieldSet->getOption("empty") != "yes") {
                    if (empty($tdefval[$oattr->fieldSet->id])) {
                        $ok = true;
                    } else {
                        $ok = false;
                    }
                }
                if ($ok) {
                    if ($oattr->type == "array") {
                        if ($method) {
                            $values = $dval;
                            if (is_string($values) && $values[0] === ':') {
                                $values = $this->applyMethod($dval, null);
                                if ($values === null) {
                                    throw new \Dcp\Exception("DFLT0007", $aid, $dval, $this->fromname);
                                }
                            }
                            if (!is_array($values)) {
                                throw new \Dcp\Exception("DFLT0008", $aid, $dval, $values, $this->fromname);
                            }
                            $terr = [];
                            foreach ($values as $row) {
                                $err = $this->addArrayRow($aid, $row);
                                if ($err) {
                                    $terr[] = $err;
                                }
                            }
                            if ($terr) {
                                throw new \Dcp\Exception(
                                    "DFLT0009",
                                    $aid,
                                    $dval,
                                    $values,
                                    $this->fromname,
                                    implode('; ', $terr)
                                );
                            }
                        }
                    } else {
                        if ($method) {
                            $val = $this->GetValueMethod($dval);
                            if ($oattr->isMultiple()) {
                                $val = [$val];
                            }
                            $this->setValue($aid, $val);
                        } else {
                            if ($oattr->isMultiple()) {
                                $dval = [$dval];
                            }
                            $this->setValue($aid, $dval); // raw data
                        }
                    }
                }
            }
        }
    }

    /**
     * set default name reference
     * if no name a new \name will ne computed from its initid and family name
     * the new \name is set to name attribute
     *
     * @param boolean $temporary compute a temporary logical name that will be deleted by the cleanContext API
     *
     * @return string error message (empty means OK).
     */
    final public function setNameAuto($temporary = false)
    {
        $err = '';
        if (($this->name == "") && ($this->initid > 0)) {
            $dfam = $this->getFamilyDocument();
            if ($dfam->name == "") {
                return sprintf("no family name %s", $dfam->id);
            }
            if ($temporary) {
                $this->name = sprintf('TEMPORARY_%s_%s_%s', $dfam->name, $this->initid, uniqid());
            } else {
                $this->name = $dfam->name . '_' . $this->initid;
            }
            $err = $this->modify(true, array(
                "name"
            ), true);
        }
        return $err;
    }

    /**
     * Return the main path relation
     * list of prelid properties (primary relation)
     * the first item is the direct parent, the second:the grand-parent , etc.
     *
     * @return array key=id , value=title of relation
     */
    public function getMainPath()
    {
        $tr = array();

        if ($this->prelid > 0) {
            $d = SEManager::getRawData($this->prelid, ["initid", "title", "prelid", "profid"]);
            $fini = false;
            while (!$fini) {
                if ($d) {
                    if (controlTDoc($d, "view")) {
                        if (!in_array($d["initid"], array_keys($tr))) {
                            $tr[$d["initid"]] = $d["title"];
                            if ($d["prelid"] > 0) {
                                $d = SEManager::getRawData($d["prelid"], ["initid", "title", "prelid", "profid"]);
                            } else {
                                $fini = true;
                            }
                        } else {
                            $fini = true;
                        }
                    } else {
                        $fini = true;
                    }
                } else {
                    $fini = true;
                }
            }
        }
        return $tr;
    }

    /**
     * generate HTML code for view doc
     *
     * @param string $layout       layout to use to view document
     * @param string $target       window target name for hyperlink destination
     * @param bool   $ulink        if false hyperlink are not generated
     * @param bool   $abstract     if true only abstract attribute are generated
     * @param bool   $changelayout if true the internal layout ($this->lay) will be replace by the new \layout
     *
     * @throws \Exception
     * @return string genererated template . If target is binary, return file path of temporary generated file
     */
    final public function viewDoc(
        $layout = "FDL:VIEWBODYCARD",
        $target = "_self",
        $ulink = true,
        $abstract = false,
        $changelayout = false
    ) {
        $reg = $this->parseZone($layout);
        if ($reg === false) {
            return htmlspecialchars(sprintf(_("error in pzone format %s"), $layout), ENT_QUOTES);
        }

        if (array_key_exists('args', $reg)) {
            // in case of arguments in zone
            global $ZONE_ARGS;
            $layout = $reg['fulllayout'];
            if (array_key_exists('argv', $reg)) {
                foreach ($reg['argv'] as $k => $v) {
                    $ZONE_ARGS[$k] = $v;
                }
            }
        }
        $play = null;
        if (!$changelayout) {
            $play = $this->lay;
        }
        $binary = ($this->getZoneOption($layout) == "B");

        $tplfile = $this->getZoneFile($layout);

        $ext = \Anakeen\Core\Utils\FileMime::getFileExtension($tplfile);
        if (strtolower($ext) == "odt") {
            $target = "ooo";
            $ulink = false;
            $this->lay = new \OOoLayout($tplfile, $this);
        } else {
            $this->lay = new \Layout($tplfile, "");
        }
        //if (! file_exists($this->lay->file)) return sprintf(_("template file (layout [%s]) not found"), $layout);
        $this->lay->set("_readonly", ($this->Control('edit') != ""));
        $method = strtok(strtolower($reg['layout']), '.');
        if (method_exists($this, $method)) {
            try {
                $refMeth = new \ReflectionMethod(get_class($this), $method);
                if (preg_match('/@templateController\b/', $refMeth->getDocComment())) {
                    $this->$method($target, $ulink, $abstract);
                } else {
                    $syserr = \ErrorCode::getError(
                        "DOC1101",
                        $refMeth->getDeclaringClass()->getName(),
                        $refMeth->getName(),
                        $this
                    );
                    LogManager::error($syserr);
                    $err = htmlspecialchars(sprintf(_("Layout \"%s\" : Controller not allowed"), $layout), ENT_QUOTES);
                    return $err;
                }
            } catch (\Exception $e) {
                if ((!file_exists($this->lay->file) && (!$this->lay->template))) {
                    return htmlspecialchars(sprintf(
                        _("template file (layout [%s]) not found") . ": %s",
                        $layout,
                        $e->getMessage()
                    ), ENT_QUOTES);
                } else {
                    throw $e;
                }
            }
        } else {
            $this->viewdefaultcard($target, $ulink, $abstract);
        }

        if ((!file_exists($this->lay->file) && (!$this->lay->template))) {
            return htmlspecialchars(sprintf(_("template file (layout [%s]) not found"), $layout), ENT_QUOTES);
        }

        $laygen = $this->lay->gen();

        if (!$changelayout) {
            $this->lay = $play;
        }

        if (!$ulink) {
            // suppress href attributes
            return preg_replace(array(
                "/href=\"index\\.php[^\"]*\"/i",
                "/onclick=\"[^\"]*\"/i",
                "/ondblclick=\"[^\"]*\"/i"
            ), array(
                "",
                "",
                ""
            ), $laygen);
        }
        if ($target == "mail") {
            // suppress session id
            return preg_replace("/\\?session=[^&]*&/", "?", $laygen);
        }
        if ($binary && ($target != "ooo")) {
            // set result into file
            $tmpfile = uniqid(ContextManager::getTmpDir() . "/fdllay") . ".html";
            file_put_contents($tmpfile, $laygen);
            $laygen = $tmpfile;
        }

        return $laygen;
    }

    /**
     * default construct layout for view card containt
     *
     * @templateController default controller view
     * @deprecated
     *
     * @param string $target     window target name for hyperlink destination
     * @param bool   $ulink      if false hyperlink are not generated
     * @param bool   $abstract   if true only abstract attribute are generated
     * @param bool   $viewhidden if true view also hidden attributes
     */
    final public function viewdefaultcard($target = "_self", $ulink = true, $abstract = false, $viewhidden = false)
    {
        $this->viewattr($target, $ulink, $abstract, $viewhidden);
        $this->viewprop($target, $ulink, $abstract);
    }


    /**
     * set V_<attrid> and L_<attrid> keys for current layout
     * the keys are in uppercase letters
     *
     * @param string $target     HTML target for links
     * @param bool   $ulink      set to true to have HTML hyperlink when it is possible
     * @param bool   $abstract   set to true to restrict to abstract attributes
     * @param bool   $viewhidden set to true to return also hidden attribute (visibility H)
     */
    final public function viewattr($target = "_self", $ulink = true, $abstract = false, $viewhidden = false)
    {
        $listattr = $this->GetNormalAttributes();
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ($listattr as $k => $v) {
            $value = chop($this->getRawValue($v->id));
            //------------------------------
            // Set the table value elements
            $this->lay->Set("S_" . strtoupper($v->id), ($value != ""));
            // don't see  non abstract if not
            if (FieldAccessManager::hasReadAccess($this, $v) === false || (($abstract) && (!$v->isInAbstract))) {
                $this->lay->Set("V_" . strtoupper($v->id), "");
                $this->lay->Set("L_" . strtoupper($v->id), "");
            } else {
                if ($target == "ooo") {
                    if ($v->type == "array") {
                        $tva = $this->getArrayRawValues($v->id);

                        $tmkeys = array();
                        foreach ($tva as $kindex => $kvalues) {
                            foreach ($kvalues as $kaid => $va) {
                                /**
                                 * @var \Anakeen\Core\SmartStructure\NormalAttribute $oa
                                 */
                                $oa = $this->getAttribute($kaid);
                                if ($oa->getOption("multiple") == "yes") {
                                    // second level
                                    $oa->setOption("multiple", "no"); //  needto have values like first level
                                    $values = $va;
                                    $ovalues = array();
                                    foreach ($values as $ka => $vaa) {
                                        $ovalues[] = htmlspecialchars_decode($this->GetOOoValue($oa, $vaa), ENT_QUOTES);
                                    }
                                    $tmkeys[$kindex]["V_" . strtoupper($kaid)] = $ovalues;
                                    $oa->setOption("multiple", "yes"); //  needto have values like first level
                                } else {
                                    $oooValue = $this->GetOOoValue($oa, $va);
                                    if ($oa->type !== "htmltext") {
                                        $oooValue = htmlspecialchars_decode($oooValue, ENT_QUOTES);
                                    }
                                    $tmkeys[$kindex]["V_" . strtoupper($kaid)] = $oooValue;
                                }
                            }
                        }
                        $this->lay->setRepeatable($tmkeys);
                    } else {
                        $ovalue = $this->GetOOoValue($v, $value);
                        if ($v->isMultiple()) {
                            $ovalue = str_replace("<text:tab/>", ', ', $ovalue);
                        }
                        $this->lay->Set("V_" . strtoupper($v->id), $ovalue);
                        if ((!$v->inArray()) && ($v->getOption("multiple") == "yes")) {
                            $values = $this->getMultipleRawValues($v->id);
                            $ovalues = array();
                            $v->setOption("multiple", "no");
                            foreach ($values as $ka => $va) {
                                $ovalues[] = htmlspecialchars_decode($this->GetOOoValue($v, $va), ENT_QUOTES);
                            }
                            $v->setOption("multiple", "yes");
                            $this->lay->setColumn("V_" . strtoupper($v->id), $ovalues);
                        }
                    }
                } else {
                    $this->lay->Set("V_" . strtoupper($v->id), $this->GetHtmlValue($v, $value, $target, $ulink));
                }
                $this->lay->Set("L_" . strtoupper($v->id), $v->getLabel());
            }
        }
        $listattr = $this->GetFieldAttributes();
        // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
        foreach ($listattr as $k => $v) {
            $this->lay->Set("L_" . strtoupper($v->id), $v->getLabel());
        }
    }

    /**
     * set properties keys in current layout
     *  the keys are in uppercase letters
     * produce alse V_TITLE key to have a HTML link to document (for HTML layout)
     *
     * @param string $target
     * @param bool   $ulink    for the V_TITLE key
     * @param bool   $abstract unused
     */
    final public function viewprop(
        $target = "_self",
        $ulink = true,
        /* @noinspection PhpUnusedParameterInspection */
        $abstract = false
    ) {
        foreach ($this->fields as $k => $v) {
            if ($target == 'ooo') {
                $this->lay->Set(strtoupper($v), ($this->$v === null)
                    ? false
                    : str_replace(array(
                        "<",
                        ">",
                        '&'
                    ), array(
                        "&lt;",
                        "&gt;",
                        "&amp;"
                    ), $this->$v));
            } else {
                $this->lay->Set(strtoupper($v), ($this->$v === null) ? false : $this->$v);
            }
        }
        if ($target == 'ooo') {
            $this->lay->Set("V_TITLE", $this->lay->get("TITLE"));
        } else {
            $this->lay->Set("V_TITLE", $this->getDocAnchor($this->id, $target, $ulink, false, false));
        }
    }


    /**
     * Affect a logical name that can be use as unique reference of a document independant of database.
     *
     * The logical name is affected only if it's not an empty string or NULL:
     * if empty or NULL, then the affectation is silently bypassed.
     *
     * @param string $name       new \logical name
     * @param bool   $reset      set to true to accept change
     * @param bool   $verifyOnly if true only verify syntax and unicity
     *
     * @return string error message if cannot be
     */
    public function setLogicalName($name, $reset = false, $verifyOnly = false)
    {
        if ($name === "" || $name === null) {
            return '';
        }
        if (!\CheckDoc::isWellformedLogicalName($name)) {
            if (!$this->isAffected()) {
                $this->name = $name; // affect to be controlled in add and return error also
            }
            return (sprintf(
                _("name must begin with a letter and contain only alphanumeric characters or - and _: invalid  [%s]"),
                $name
            ));
        } elseif (!$verifyOnly && !$this->isAffected()) {
            $this->name = $name;

            return "";
        } elseif (!$verifyOnly && $this->isAffected() && ($this->name != "") && ($this->doctype != 'Z') && !$reset) {
            return (sprintf(
                _("Logical name %s already set for %s. Use reset parameter to overhide it"),
                $name,
                $this->title
            ));
        } else {
            // verify not use yet
            $d = SEManager::getRawDocument($name);

            if ($d && $d["doctype"] != 'Z') {
                return sprintf(_("Logical name %s already use in document %s"), $name, $d["title"]);
            } elseif (!$verifyOnly) {
                if ($this->name) {
                    DbManager::query(sprintf(
                        "UPDATE docname SET name = '%s' WHERE name = '%s'",
                        pg_escape_string($name),
                        pg_escape_string($this->name)
                    ));
                }
                $this->name = $name;
                DbManager::query(sprintf(
                    "update %s set name='%s' where initid=%d",
                    pg_escape_string($this->dbtable),
                    pg_escape_string($name),
                    $this->initid
                ));
                DbManager::query(sprintf("select name from docname where id=%d", $this->id), $dbdocname, true, true);

                if (!$dbdocname) {
                    $sql = sprintf(
                        "delete from docname where name='%s';insert into docname (id,fromid,name) select id, fromid, name from docread where name='%s' and locked != -1",
                        pg_escape_string($name),
                        pg_escape_string($name)
                    );
                    DbManager::query($sql);
                }
            }
        }
        return "";
    }


    /**
     * get vault file name or server path of filename
     *
     * @param string $attrid identifier of file attribute
     * @param bool   $path   false return original file name (basename) , true the real path
     * @param int    $index  in case of array of files
     *
     * @return string the file name of the attribute
     */
    final public function vault_filename($attrid, $path = false, $index = -1)
    {
        if ($index == -1) {
            $fileid = $this->getRawValue($attrid);
        } else {
            $fileid = $this->getMultipleRawValues($attrid, '', $index);
        }
        return $this->vault_filename_fromvalue($fileid, $path);
    }

    /**
     * get vault file name or server path of filename
     *
     * @param string $fileid value of file attribute
     * @param bool   $path   false return original file name (basename) , true the real path
     *
     * @return string the file name of the attribute
     */
    final public function vault_filename_fromvalue($fileid, $path = false)
    {
        $fname = "";
        if (preg_match(PREGEXPFILE, $fileid, $reg)) {
            // reg[1] is mime type
            $vf = newFreeVaultFile($this->dbaccess);
            /**
             * @var \VaultFileInfo $info
             */
            if ($vf->Show($reg[2], $info) == "") {
                if ($path) {
                    $fname = $info->path;
                } else {
                    $fname = $info->name;
                }
            }
        }
        return $fname;
    }

    /**
     * get vault file name or server path of filename
     *
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $attr identifier of file attribute
     *
     * @return array of properties :
     * [0]=>
     * [name] => TP_Users.pdf
     * [size] => 179435
     * [public_access] =>
     * [mime_t] => PDF document, version 1.4
     * [mime_s] => application/pdf
     * [cdate] => 24/12/2010 11:44:36
     * [mdate] => 24/12/2010 11:44:41
     * [teng_state] => 1
     * [teng_lname] => pdf
     * [teng_vid] => 15
     * [teng_comment] =>
     * [path] => /var/www/eric/vaultfs/1/16.pdf
     * [vid] => 16
     */
    final public function vault_properties(\Anakeen\Core\SmartStructure\NormalAttribute $attr)
    {
        if ($attr->inArray()) {
            $fileids = $this->getMultipleRawValues($attr->id);
        } else {
            $fileids[] = $this->getRawValue($attr->id);
        }

        $tinfo = array();
        foreach ($fileids as $k => $fileid) {
            if (preg_match(PREGEXPFILE, $fileid, $reg)) {
                // reg[1] is mime type
                $vf = newFreeVaultFile($this->dbaccess);
                /**
                 * @var \VaultFileInfo $info
                 */
                if ($vf->Show($reg[2], $info) == "") {
                    $tinfo[$k] = get_object_vars($info);
                    $tinfo[$k]["vid"] = $reg[2];
                }
            }
        }

        return $tinfo;
    }

    /**
     * return a property of vault file value
     *
     * @param string $filesvalue the file value : like application/pdf|12345
     * @param string $key        one of property id_file, name, size, public_access, mime_t, mime_s, cdate, mdate, teng_state, teng_lname, teng_vid, teng_comment, path
     * @param string $returnType if "array" return indexed array else return VaultFileInfo object
     *
     * @return array|string|\VaultFileInfo value of property or array of all properties if no key
     */
    final public function getFileInfo($filesvalue, $key = "", $returnType = "array")
    {
        if (!is_string($filesvalue)) {
            return false;
        }
        if (preg_match(PREGEXPFILE, $filesvalue, $reg)) {
            $vid = $reg[2];
            $info = \Dcp\VaultManager::getFileInfo($vid);
            if (!$info) {
                return false;
            }
            if ($key != "") {
                if (isset($info->$key)) {
                    return $info->$key;
                } else {
                    return sprintf(_("unknow %s file property"), $key);
                }
            } else {
                if ($returnType === "array") {
                    return get_object_vars($info);
                } else {
                    return $info;
                }
            }
        }
        return $key ? '' : array();
    }

    /**
     *
     * @param string  &$xml             content xml (empty if $outfile is not empty
     * @param boolean $withfile         include files in base64 encoded
     * @param string  $outfile          if not empty means content is put into this file
     * @param bool    $wident           set true to ident xml
     * @param bool    $flat             set to true if don't want structure
     * @param array   $exportAttributes to export only a part of attributes
     *
     * @return string error message (empty if no error)
     */
    public function exportXml(
        &$xml,
        $withfile = false,
        $outfile = "",
        $wident = true,
        $flat = false,
        $exportAttributes = array()
    ) {
        try {
            $exd = new \Dcp\ExportXmlDocument();
            $exd->setDocument($this);
            $exd->setExportFiles($withfile);
            $exd->setExportDocumentNumericIdentiers($wident);
            $exd->setStructureAttributes(!$flat);
            $exd->setIncludeSchemaReference(!$flat);
            $exd->setAttributeToExport($exportAttributes);


            if ($outfile) {
                $exd->writeTo($outfile);
            } else {
                $xml = $exd->getXml();
            }
        } catch (\Dcp\Exception $e) {
            \Anakeen\Core\LogException::writeLog($e);
            return $e->getMessage();
        }
        return '';
    }

    /**
     * define custom title used to set title propert when update or create document
     *
     * @api hook called in refresh title
     * this method can be redefined in child family to compose specific title
     */
    public function getCustomTitle()
    {
        return $this->title;
    }

    /**
     * define custom title used to set title propert when update or create document
     *
     * @deprecated This hook may be replaced by getCustomTitle in the the next version.
     * this method can be redefined in child family to compose specific title
     */
    public function getSpecTitle()
    {
        return $this->title;
    }




    //----------------------------------------------------------------------
    //   USUAL METHODS USE FOR CALCULATED ATTRIBUTES OR FUNCTION SEARCHES
    //----------------------------------------------------------------------
    // ALL THESE METHODS NAME MUST BEGIN WITH 'GET'

    /**
     * return title of document in latest revision
     *
     * @param string $id  identifier of document
     * @param string $def default value if document not found
     *
     * @return string
     */
    final public function getLastTitle($id = "-1", $def = "")
    {
        return $this->getTitle($id, $def, true);
    }

    /**
     * return title of document
     *
     * @api get document's title
     *
     * @param string  $id     identifier of document (if not set use current document)
     * @param string  $def    default value if document not found
     * @param boolean $latest search title in latest revision
     *
     * @return string
     * @see \Anakeen\Core\Internal\SmartElement::getCustomTitle()
     */
    final public function getTitle($id = "-1", $def = "", $latest = false)
    {
        if (is_array($id)) {
            return $def;
        }
        if ($id == "") {
            return $def;
        }
        if ($id == "-1") {
            if ($this->locked != -1 || (!$latest)) {
                if ($this->isConfidential()) {
                    return _("confidential document");
                }
                return $this->getCustomTitle();
            } else {
                // search latest
                $id = $this->getLatestId();
            }
        }
        if ($id[0] === '{') {
            $tid = Postgres::stringToFlatArray($id);
            $ttitle = array();
            foreach ($tid as $idone) {
                $ttitle[] = $this->getTitle($idone, $def, $latest);
            }
            return implode("\n", $ttitle);
        } else {
            if (!is_numeric($id)) {
                $id = SEManager::getIdFromName($id);
            }
            if ($id > 0) {
                $title = getDocTitle($id, $latest);
                if (!$title) {
                    return " ";
                } // delete title
                return $title;
            }
        }
        return $def;
    }

    /**
     * Same as ::getTitle()
     * the < & > characters as replace by entities
     *
     * @param string $id     docuemnt identifier to set else use current document title
     * @param string $def    default value if document not found
     * @param bool   $latest force use latest revision of document
     *
     * @see \Anakeen\Core\Internal\SmartElement::getTitle
     * @return string
     */
    public function getHTMLTitle($id = "-1", $def = "", $latest = false)
    {
        $t = $this->getTitle($id, $def, $latest);
        return $this->htmlEncode($t);
    }

    /**
     * the < > & characters as replace by entities
     *
     * @static
     *
     * @param $s
     *
     * @return mixed
     */
    public static function htmlEncode($s)
    {
        $s = htmlspecialchars($s, ENT_QUOTES);
        return str_replace("[", "&#091;", $s);
    }

    /**
     * return the today date with european format DD/MM/YYYY
     *
     * @searchLabel today
     * @searchType  date
     * @searchType  timestamp
     * @api         get date
     *
     * @param int        $daydelta  to have the current date more or less day (-1 means yesterday, 1 tomorrow)
     * @param int|string $dayhour   hours of day
     * @param int|string $daymin    minutes of day
     * @param bool       $getlocale whether to return locale date or not
     *
     * @return string YYYY-MM-DD or DD/MM/YYYY (depend of CORE_LCDATE parameter) or locale dateDD/MM/YYYY or locale date
     */
    public static function getDate($daydelta = 0, $dayhour = "", $daymin = "", $getlocale = false)
    {
        $delta = abs(intval($daydelta));
        if ($daydelta > 0) {
            $nd = strtotime("+$delta day");
        } elseif ($daydelta < 0) {
            $nd = strtotime("-$delta day");
        } else {
            $nd = time();
        }
        if ($dayhour !== "" || $daymin !== "") {
            $delta = abs(intval($dayhour));
            if ($dayhour > 0) {
                $nd = strtotime("+$delta hour", $nd);
            } elseif ($dayhour < 0) {
                $nd = strtotime("-$delta hour", $nd);
            }
            $delta = abs(intval($daymin));
            if ($daymin > 0) {
                $nd = strtotime("+$delta min", $nd);
            } elseif ($daymin < 0) {
                $nd = strtotime("-$delta min", $nd);
            }

            if ($getlocale) {
                return stringDateToLocaleDate(date("Y-m-d H:i", $nd));
            } else {
                return date("Y-m-d H:i", $nd);
            }
        } else {
            if ($getlocale) {
                return stringDateToLocaleDate(date("Y-m-d", $nd));
            } else {
                return date("Y-m-d", $nd);
            }
        }
    }

    /**
     * return the today date and time with european format DD/MM/YYYY HH:MM
     *
     * @param int  $hourdelta to have the current date more or less hour  (-1 means one hour before, 1 one hour after)
     * @param bool $second    if true format DD/MM/YYYY HH:MM
     *
     * @return string DD/MM/YYYY HH:MM or YYYY-MM-DD HH:MM (depend of CORE_LCDATE parameter)
     */
    public static function getTimeDate($hourdelta = 0, $second = false)
    {
        $delta = abs(intval($hourdelta));
        if ($second) {
            $format = "Y-m-d H:i:s";
        } else {
            $format = "Y-m-d H:i";
        }
        if ($hourdelta > 0) {
            if (is_float($hourdelta)) {
                $dm = intval((abs($hourdelta) - $delta) * 60);
                return date($format, strtotime("+$delta hour $dm minute"));
            } else {
                return date($format, strtotime("+$delta hour"));
            }
        } elseif ($hourdelta < 0) {
            if (is_float($hourdelta)) {
                $dm = intval((abs($hourdelta) - $delta) * 60);
                return date($format, strtotime("-$delta hour $dm minute"));
            } else {
                return date($format, strtotime("-$delta hour"));
            }
        }
        return date($format);
    }

    /**
     * Return the related value by linked attributes starting from referenced document.
     *
     * Can be used to retrieve a value by traversing multiple docid.
     *
     * For example,
     *
     * @code
     * $val = $this->getDocValue("id", "id1:id2:id3")
     * @endcode
     * is a shortcut for
     * @code
     * $doc = new_Doc('', "id");
     * $val = $doc->getRValue("id1:id2:id3");
     * @endcode
     *
     * @warning
     * Each of the traversed docid **must** be a docid or an account, and **must not** be multiple.\n
     * Elsewhere, the returned value is $def
     * @endwarning
     * @see \Anakeen\Core\Internal\SmartElement::getRValue
     *
     * @param int    $docid  document identifier
     * @param string $attrid attributes identifier chain (separated by ':')
     * @param string $def    $def default return value
     * @param bool   $latest always last revision of document
     *
     * @return array|string
     */
    final public function getDocValue($docid, $attrid, $def = " ", $latest = false)
    {
        if ((!is_numeric($docid)) && ($docid != "")) {
            $docid = SEManager::getIdFromName($docid);
        }
        if (intval($docid) > 0) {
            if (strpos(':', $attrid) === false) {
                $attrid = strtolower($attrid);
                return SEManager::getRawValue($docid, $attrid, $latest);
            } else {
                $doc = SEManager::getDocument($docid, $latest);
                if ($doc) {
                    SEManager::cache()->addDocument($doc);
                    return $doc->getRValue($attrid, $def, $latest);
                }
            }
        }
        return "";
    }

    /**
     * return value of an property for the document referenced
     *
     * @see \Anakeen\Core\Internal\SmartElement::getPropertyValue
     *
     * @param int    $docid  document identifier
     * @param string $propid property identifier
     * @param bool   $latest always last revision of document if true
     *
     * @return string
     */
    final public function getDocProp($docid, $propid, $latest = false)
    {
        if ($docid) {
            $propid = strtolower($propid);
            $data = SEManager::getRawData($docid, [$propid], $latest);
            return $data[$propid];
        }
        return "";
    }


    /**
     * concatenate and format string
     * to be use in computed attribute
     *
     * @param string $fmt like sprintf format
     *
     * @return string the composed string
     */
    public function formatString($fmt)
    {
        $nargs = func_num_args();

        if ($nargs < 1) {
            return "";
        }
        $fmt = func_get_arg(0);
        $sp = array();
        for ($ip = 1; $ip < $nargs; $ip++) {
            $vip = func_get_arg($ip);
            if (gettype($vip) != "array") {
                $sp[] = $vip;
            }
        }
        $r = vsprintf($fmt, $sp);
        return $r;
    }

    /**
     * update internal vault index relation table
     * Delete temporary file property
     */
    public function updateVaultIndex()
    {
        if (empty($this->id)) {
            return;
        }
        $dvi = new \DocVaultIndex($this->dbaccess);

        $point = uniqid("dcp:updateVaultIndex");
        DbManager::savePoint($point);
        DbManager::lockPoint($this->initid, "UPVI");
        // Need to lock to avoid constraint errors when concurrent docvaultindex update
        $dvi->DeleteDoc($this->id);

        $tvid = \Dcp\Core\Utils\VidExtractor::getVidsFromDoc($this);

        $vids = array();
        foreach ($tvid as $vid) {
            if ($vid > 0) {
                $dvi->docid = $this->id;
                $dvi->vaultid = $vid;
                $dvi->add();
                $vids[] = intval($vid);
            }
        }
        DbManager::commitPoint($point);
        if (count($vids) > 0) {
            \Dcp\VaultManager::setFilesPersitent($vids);
        }
    }
    // ===================
    // Timer Part

    /**
     * attach timer to a document
     *
     * @param \Anakeen\SmartStructures\Timer\TimerHooks &$timer   the timer document
     * @param \Anakeen\Core\Internal\SmartElement       &$origin  the document which comes from the attachement
     * @param string                                    $execdate date to execute first action YYYY-MM-DD HH:MM:SS
     *
     * @api Attach timer to a document
     * @return string error - empty if no error -
     */
    final public function attachTimer(&$timer, $origin = null, $execdate = null)
    {
        $dyn = false;
        if ($execdate == null) {
            $dyn = trim(strtok($timer->getRawValue("tm_dyndate"), " "));
            if ($dyn) {
                $execdate = $this->getRawValue($dyn);
                if (empty($execdate)) {
                    $execdate = '';
                }
            }
        }
        if (method_exists($timer, 'attachDocument')) {
            $err = $timer->attachDocument($this, $origin, $execdate);
            if ($err == "") {
                if ($dyn) {
                    $this->addATag("DYNTIMER");
                }
                $this->addHistoryEntry(sprintf(_("attach timer %s [%d]"), $timer->title, $timer->id), \DocHisto::NOTICE);
                $this->addLog("attachtimer", array(
                    "timer" => $timer->id
                ));
            }
        } else {
            $err = sprintf(_("attachTimer : the timer parameter is not a document of TIMER family"));
        }
        return $err;
    }

    /**
     * unattach timer of a document
     *
     * @param \Anakeen\SmartStructures\Timer\TimerHooks &$timer the timer document
     *
     * @api Unattach timer of a document
     * @return string error - empty if no error -
     */
    final public function unattachTimer(&$timer)
    {
        if (method_exists($timer, 'unattachDocument')) {
            $err = $timer->unattachDocument($this);
            if ($err == "") {
                $this->addHistoryEntry(
                    sprintf(_("unattach timer %s [%d]"), $timer->title, $timer->id),
                    \DocHisto::NOTICE
                );
                $this->addLog("unattachtimer", array(
                    "timer" => $timer->id
                ));
            }
        } else {
            $err = sprintf(_("unattachTimer : the timer parameter is not a document of TIMER family"));
        }
        return $err;
    }

    /**
     * Recompute timer's delay for all attached dynamic timers
     */
    final public function resetDynamicTimers()
    {
        $tms = $this->getAttachedTimers();
        if (count($tms) == 0) {
            $this->delATag("DYNTIMER");
        } else {
            foreach ($tms as $k => $v) {
                /**
                 * @var \Anakeen\SmartStructures\Timer\TimerHooks $t
                 */
                $t = SEManager::getDocument($v["timerid"]);
                if ($t && $t->isAlive()) {
                    $dynDateAttr = trim(strtok($t->getRawValue("tm_dyndate"), " "));
                    if ($dynDateAttr) {
                        $execdate = $this->getRawValue($dynDateAttr);
                        $previousExecdate = $this->getOldRawValue($dynDateAttr);
                        // detect if need reset timer : when date has changed
                        if ($previousExecdate !== false && ($execdate != $previousExecdate)) {
                            if ($v["originid"]) {
                                $ori = SEManager::getDocument($v["originid"]);
                            } else {
                                $ori = null;
                            }
                            $this->unattachTimer($t);
                            $this->attachTimer($t, $ori);
                        }
                    }
                } else {
                    $this->unattachTimer($t);
                }
            }
        }
    }

    /**
     * unattach several timers to a document
     *
     * @param \Anakeen\Core\Internal\SmartElement &$origin if set unattach all timer which comes from this origin
     *
     * @api Unattach all times of the document
     * @return string error - empty if no error -
     */
    final public function unattachAllTimers($origin = null)
    {
        /**
         * @var \Anakeen\SmartStructures\Timer\TimerHooks $timer
         */
        $timer = SEManager::createTemporaryDocument("TIMER");
        $c = 0;
        $err = $timer->unattachAllDocument($this, $origin, $c);
        if ($err == "" && $c > 0) {
            if ($origin) {
                $this->addHistoryEntry(
                    sprintf(_("unattach %d timers associated to %s"), $c, $origin->title),
                    \DocHisto::NOTICE
                );
            } else {
                $this->addHistoryEntry(sprintf(_("unattach all timers [%s]"), $c), \DocHisto::NOTICE);
            }
            $this->addLog("unattachtimer", array(
                "timer" => "all",
                "number" => $c
            ));
        }
        return $err;
    }

    /**
     * return all activated document timer
     *
     * @api Get all timer attached to the document
     * @return array of doctimer values
     */
    final public function getAttachedTimers()
    {


        $q = new \Anakeen\Core\Internal\QueryDb($this->dbaccess, \DocTimer::class);
        $q->AddQuery("docid=" . $this->initid);
        $q->AddQuery("donedate is null");
        $l = $q->Query(0, 0, "TABLE");

        if (is_array($l)) {
            return $l;
        }
        return array();
    }

    /**
     * return folder where document is set into
     *
     * @return array of folder identifiers
     */
    public function getParentFolderIds()
    {
        $fldids = array();
        DbManager::query(
            sprintf("select dirid from fld where qtype='S' and childid=%d", $this->initid),
            $fldids,
            true,
            false
        );
        return $fldids;
    }


    /**
     * Parse a zone string "FOO:BAR[-1]:B:PDF?k1=v1,k2=v2" into an array:
     *
     * array(
     *     'fulllayout' => 'FOO:BAR[-1]:B:PDF',
     *     'args' => 'k1=v1,k2=v2',
     *     'argv' => array(
     *         'k1' => 'v1',
     *         'k2' => 'v2
     *      ),
     *     'app' => 'FOO',
     *     'layout' => 'BAR',
     *     'index' => '-1',
     *     'modifier' => 'B',
     *     'transform' => 'PDF'
     *  )
     *
     * @param string $zone "APP:LAYOUT:etc." $zone
     *
     * @return bool|array false on error or an array containing the components
     */
    public static function parseZone($zone)
    {
        $p = array();
        // Separate layout (left) from args (right)
        $split = preg_split('/\?/', $zone, 2);
        $left = $split[0];
        if (count($split) > 1) {
            $right = $split[1];
        } else {
            $right = '';
        }
        // Check that the layout part has al least 2 elements
        $el = preg_split('/:/', $left);
        if (count($el) < 2) {
            return false;
        }
        $p['fulllayout'] = $left;
        $p['index'] = -1;
        // Parse args into argv (k => v)
        if ($right != "") {
            $p['args'] = $right;
            $argList = preg_split('/&/', $p['args']);
            $p['argv'] = array();
            foreach ($argList as $arg) {
                $split = preg_split('/=/', $arg, 2);
                $left = urldecode($split[0]);
                $right = urldecode($split[1]);
                $p['argv'][$left] = $right;
            }
        }
        // Parse layout
        $parts = array(
            0 => 'app',
            1 => 'layout',
            2 => 'modifier',
            3 => 'transform'
        );
        foreach ($parts as $aPart) {
            $p[$aPart] = null;
        }
        $match = array();
        $i = 0;
        while ($i < count($el)) {
            if (!array_key_exists($i, $parts)) {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " .
                    sprintf(
                        "Unexpected part '%s' in zone '%s'.",
                        $el[$i],
                        $zone
                    ));
                return false;
            }
            // Extract index from 'layout' part if present
            if ($i == 1 && preg_match("/^(?P<name>.*?)\[(?P<index>-?\d)\]$/", $el[$i], $match)) {
                $p[$parts[$i]] = $match['name'];
                $p['index'] = $match['index'];
                $i++;
                continue;
            }
            // Store part
            $p[$parts[$i]] = $el[$i];
            $i++;
        }

        return $p;
    }

    /**
     * Get the helppage document associated to the document family.
     *
     * @param string $fromid get the helppage for this family id (default is the family of the current document)
     *
     * @return \SmartStructure\Helppage the helppage document on success, or a non-alive document if no helppage is associated with the family
     */
    public function getHelpPage($fromid = "")
    {
        if ($fromid === "") {
            $fromid = $this->fromid;
        }
        $s = new \SearchDoc($this->dbaccess, "HELPPAGE");
        $s->addFilter("help_family='%d'", $fromid);
        $help = $s->search();
        $helpId = "";
        if ($s->count() > 0) {
            $helpId = $help[0]["id"];
        }
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return SEManager::getDocument($helpId);
    }

    /**
     * Get the list of compatible search methods for a given attribute type
     *
     * @param string $attrId   attribute name
     * @param string $attrType empty string to returns all methods or attribute type
     *                         (e.g. 'date', 'docid', 'docid("IUSER")', etc.) to restrict search to methods supporting this type
     *
     * @return array list of array('method' => '::foo()', 'label' => 'Foo Bar Baz')
     */
    public function getSearchMethods(
        $attrId,
        $attrType = ''
    ) {
        // Strip format strings for non-docid types
        $pType = \Dcp\FamilyImport::parseType($attrType);
        if ($pType['type'] != 'docid') {
            $attrType = $pType['type'];
        }

        $collator = new \Collator(ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, 'CORE_LANG', 'fr_FR'));

        $compatibleMethods = array();

        if ($attrType == 'date' || $attrType == 'timestamp') {
            $compatibleMethods = array_merge($compatibleMethods, array(
                array(
                    'label' => _("yesterday"),
                    'method' => '::getDate(-1)'
                ),
                array(
                    'label' => _("tomorrow"),
                    'method' => '::getDate(1)'
                )
            ));
        }

        try {
            $rc = new \ReflectionClass(get_class($this));
        } catch (\Exception $e) {
            return $compatibleMethods;
        }

        $methods = array_filter($rc->getMethods(), function ($aMethod) {
            /**
             * @var \ReflectionMethod $aMethod
             */
            $methodName = $aMethod->getName();
            return ($aMethod->isPublic() && $methodName != '__construct');
        });
        /**
         * @var \ReflectionMethod[] $methods
         */
        foreach ($methods as $method) {
            $tags = self::getDocCommentTags($method->getDocComment());

            $searchLabel = null;
            $searchTypes = array();

            foreach ($tags as $tag) {
                if ($tag['name'] == 'searchLabel') {
                    $searchLabel = $tag['value'];
                } elseif ($tag['name'] == 'searchType') {
                    $searchTypes[] = $tag['value'];
                }
            }

            if ($searchLabel === null) {
                continue;
            }

            if ($attrType == '' || in_array($attrType, $searchTypes)) {
                $compatibleMethods[] = array(
                    'label' => _($searchLabel),
                    'method' => sprintf('::%s()', $method->getName())
                );
            }
        }

        usort($compatibleMethods, function ($a, $b) use ($collator) {
            /**
             * @var \Collator $collator
             */
            return $collator->compare($a['label'], $b['label']);
        });

        return $compatibleMethods;
    }

    /**
     * Check if a specific method from a specific class is a valid search method
     *
     * @param string|object $className  the class name
     * @param string        $methodName the method name
     *
     * @return bool boolean 'true' if valid, boolean 'false' is not valid
     */
    public function isValidSearchMethod($className, $methodName)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        try {
            $rc = new \ReflectionClass($className);
            $method = $rc->getMethod($methodName);
            $tags = self::getDocCommentTags($method->getDocComment());

            foreach ($tags as $tag) {
                if ($tag['name'] == 'searchLabel') {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Extract tags names/values from methods doc comments text
     *
     * @static
     *
     * @param string $docComment the doc comment text
     *
     * @return array|null list of array('name' => $tagName, 'value' => $tagValue)
     */
    final private static function getDocCommentTags($docComment = '')
    {
        if (!preg_match_all(
            '/^.*?@(?P<name>[a-zA-Z0-9_-]+)\s+(?P<value>.*?)\s*$/m',
            $docComment,
            $tags,
            PREG_SET_ORDER
        )) {
            return array();
        }
        $tags = array_map(function ($tag) {
            return array(
                'name' => $tag['name'],
                'value' => $tag['value']
            );
        }, $tags);
        return $tags;
    }


    /**
     * get display values for general searches
     *
     * @param bool $withLocale use all defined locale
     *
     * @return string
     * @throws \Dcp\Exception
     * @throws \Dcp\Fmtc\Exception
     *
     */
    protected function getExtraSearchableDisplayValues($withLocale = true)
    {
        $moreSearchValues = [];

        $fmt = new \Anakeen\Core\Internal\FormatCollection($this);
        $attributes = $this->getNormalAttributes();
        $datesValues = [];
        $oneAttributeAtLeast = false;
        foreach ($attributes as $attr) {
            if ($attr->type !== "array" && $attr->getOption("searchcriteria") !== "hidden"
                && $this->getRawValue($attr->id)) {
                $fmt->addAttribute($attr->id);
                $oneAttributeAtLeast = true;
                if ($attr->type === "date") {
                    if ($attr->isMultiple()) {
                        $datesValues = array_merge($datesValues, $this->getMultipleRawValues($attr->id));
                    } else {
                        $datesValues[] = $this->getRawValue($attr->id);
                    }
                }
            }
        }

        if ($oneAttributeAtLeast) {
            $datesValues = array_unique($datesValues);
            if ($withLocale) {
                $currentLocale = ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_LANG", "fr_FR");
                $lang = ContextManager::getLocales();

                $locales = array_keys($lang);
                // set current at then end to get same locale when function finished
                unset($locales[$currentLocale]);
                $locales[] = $currentLocale;
            } else {
                $locales = array(
                    "current"
                );
            }
            foreach ($locales as $klang) {
                if ($withLocale) {
                    ContextManager::setLanguage($klang);
                }
                $moreSearchValues[] = $this->getTitle();
                $r = $fmt->render();

                foreach ($datesValues as $date) {
                    $moreSearchValues[] = strftime("%A %B %Y %m %d", strtotime($date));
                }
                /**
                 * @var StandardAttributeValue $renderInfo
                 */
                foreach ($r[0]["attributes"] as $renderInfo) {
                    if (isset($renderInfo->value) && $renderInfo->displayValue !== $renderInfo->value) {
                        $moreSearchValues[] = $renderInfo->displayValue;
                    } elseif ($renderInfo && is_array($renderInfo)) {
                        foreach ($renderInfo as $rowInfo) {
                            if (isset($rowInfo->value) && $rowInfo->displayValue !== $rowInfo->value) {
                                $moreSearchValues[] = $rowInfo->displayValue;
                            } elseif ($rowInfo && is_array($rowInfo)) {
                                foreach ($rowInfo as $subRowInfo) {
                                    if (isset($subRowInfo->value) && $subRowInfo->displayValue !== $subRowInfo->value) {
                                        $moreSearchValues[] = $subRowInfo->displayValue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $custom = $this->getCustomSearchValues();
        if ($custom) {
            if (!is_array($custom)) {
                throw new \Dcp\Exception("DOC0126", gettype($custom));
            }
            $moreSearchValues = array_merge($moreSearchValues, $custom);
        }
        return implode("£", array_unique($moreSearchValues));
    }

    /**
     * @api Hook to add values used in general searches
     * @return string[]
     */
    protected function getCustomSearchValues()
    {
        return [];
    }

    /**
     * @return \Anakeen\Core\Internal\DocumentAccess
     */
    public function accessControl()
    {
        static $ac;
        if (!$ac) {
            $ac = new \Anakeen\Core\Internal\DocumentAccess();
        }
        return $ac->setDocument($this);
    }

    /**
     * @return \Anakeen\Core\Internal\SmartElementHooks
     */
    public function getHooks()
    {
        static $ac;
        if (!$ac) {
            $ac = new \Anakeen\Core\Internal\SmartElementHooks();
        }
        return $ac->setDocument($this);
    }

    public function registerHooks()
    {
        // Nothing To DO by default
    }
}
