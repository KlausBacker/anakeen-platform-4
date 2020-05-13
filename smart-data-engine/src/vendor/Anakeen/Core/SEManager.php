<?php

namespace Anakeen\Core;

use Anakeen\Core\DocManager\Exception;
use SmartStructure\Wdoc;

class SEManager
{
    /**
     * @var \Anakeen\Core\DocManager\MemoryCache $localCache
     */
    protected static $localCache = null;
    protected static $firstgetIdFromName = true;
    protected static $firstgetNameFromId = true;
    protected static $familyNames = null;

    /**
     * Get document object identified by its identifier
     *
     * @param int|string $documentIdentifier
     * @param bool $latest
     * @param bool $useCache
     *
     * @return \Anakeen\Core\Internal\SmartElement | null
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     * @api Get document object from identifier
     */
    public static function getDocument($documentIdentifier, $latest = true, $useCache = true)
    {
        $id = self::getIdentifier($documentIdentifier, $latest);
        if ($id > 0) {
            if ($useCache && self::cache()->isDocumentIdInCache($id)) {
                $cacheDocument = self::cache()->getDocumentFromCache($id);
                if ($cacheDocument && $cacheDocument->id == $id) {
                    return $cacheDocument;
                }
            }

            $fromid = self::getFromId($id);
            $classname = '';
            if ($fromid > 0) {
                self::requireFamilyClass($fromid);
                $classname = "\\Doc$fromid";
            } elseif ($fromid == -1) {
                $classname = \Anakeen\Core\SmartStructure::class;
            }
            if ($classname) {
                /* @var  \Anakeen\Core\Internal\SmartElement $doc */
                $doc = new $classname("", $id);

                $doc->disableAccessControl();
                return $doc;
            }
        }

        return null;
    }

    /**
     * Get family object identified by its identifier
     *
     * @param int|string $familyIdentifier
     * @param bool $useCache to use and to add family in cache if not
     *
     * @return \Anakeen\Core\SmartStructure return null if id not match a family identifier
     * @throws Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     * @api Get document object from identifier
     */
    public static function getFamily($familyIdentifier, $useCache = true)
    {
        $id = self::getFamilyIdFromName($familyIdentifier);

        if ($id > 0) {
            if ($useCache && self::cache()->isDocumentIdInCache($id)) {
                $cacheDocument = self::cache()->getDocumentFromCache($id);
                if ($cacheDocument && $cacheDocument->id == $id) {
                    /**
                     * @var \Anakeen\Core\SmartStructure $cacheDocument
                     */
                    return $cacheDocument;
                }
            }

            $doc = new \Anakeen\Core\SmartStructure("", $id);

            if ($useCache && $doc->initid) {
                self::cache()->addDocument($doc);
            }

            return $doc;
        }

        return null;
    }

    /**
     * return latest id of document from its initid or other id
     *
     * @param int $initid document identificator
     *
     * @return int|null identifier relative to latest revision
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    protected static function getLatestDocumentId($initid)
    {
        if (!is_numeric($initid)) {
            throw new Exception("APIDM0100", print_r($initid, true));
        }
        // first more quick if alive
        DbManager::query(sprintf("select id from docread where initid='%d' and locked != -1", $initid), $id, true, true);
        if ($id > 0) {
            return intval($id);
        }
        // second for zombie document
        DbManager::query(sprintf("select id from docread where initid='%d' order by id desc limit 1", $initid), $id, true, true);
        if ($id > 0) {
            return intval($id);
        }
        // it is not really on initid
        DbManager::query(sprintf("select id from docread where initid=(select initid from docread where id=%d) and locked != -1", $initid), $id, true, true);
        if ($id > 0) {
            return intval($id);
        }
        // it is perhaps a temporary document
        DbManager::query(sprintf("select id from doc where initid='%d' and doctype = 'T'", $initid), $id, true, true);
        if ($id > 0) {
            return intval($id);
        }

        return null;
    }

    /**
     * return initial  id of document from its id or logical name
     *
     * @param string $name document identificator
     *
     * @return int|null initial document identifier
     * @throws \Anakeen\Database\Exception
     */
    public static function getInitIdFromIdOrName($name)
    {
        $id = $name;
        if (!is_numeric($name)) {
            return static::getInitIdFromName($name);
        } else {
            DbManager::query(sprintf("select initid from docread where id='%d' limit 1;", $id), $initid, true, true);
            if ($id > 0) {
                return intval($initid);
            }
        }
        return null;
    }


    /**
     * return initial  id of document from its id or logical name
     *
     * @param string $name document identificator
     *
     * @return int|null initial document identifier
     * @throws \Anakeen\Database\Exception
     */
    public static function getInitIdFromName($name)
    {
        if (is_numeric($name)) {
            return null;
        }
        DbManager::query(sprintf("select initid from docread where name='%s' limit 1;", pg_escape_string($name)), $initid, true, true);
        if ($initid) {
            return intval($initid);
        }
        return null;
    }


    /**
     * return  id of document identified by its revision
     *
     * @param int $initid document identificator
     * @param int $revision
     *
     * @return int|null identifier relative to latest revision
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function getRevisedDocumentId($initid, $revision)
    {
        if (!is_numeric($initid)) {
            $id = static::getIdFromName($initid);
            if ($id === 0) {
                throw new Exception("APIDM0100", print_r($initid, true));
            } else {
                $initid = $id;
            }
        }
        if (is_numeric($revision) && $revision >= 0) {
            // first more quick if alive
            DbManager::query(sprintf("select id from docread where initid='%d' and revision = %d", $initid, $revision), $id, true, true);

            if ($id > 0) {
                return intval($id);
            }
            // it is not really on initid
            DbManager::query(sprintf("select id from docread where initid=(select initid from docread where id=%d) and revision = %d", $initid, $revision), $id, true, true);

            if ($id > 0) {
                return intval($id);
            }
            // it is perhaps a temporary document
            DbManager::query(sprintf("select id from doc where initid='%d' and revision = %d and doctype = 'T'", $initid, $revision), $id, true, true);
            if ($id > 0) {
                return intval($id);
            }
        } else {
            if (preg_match('/^state:(.+)$/', $revision, $regStates)) {
                DbManager::query(
                    sprintf(
                        "select id from docread where initid='%d' and state = '%s' and locked = -1 order by id desc",
                        $initid,
                        pg_escape_string($regStates[1])
                    ),
                    $id,
                    true,
                    true
                );
                if ($id > 0) {
                    return intval($id);
                }
                // it is not really on initid
                DbManager::query(
                    sprintf(
                        "select id from docread where initid=(select initid from docread where id=%d) and state = '%s' and locked = -1 order by id desc",
                        $initid,
                        pg_escape_string($regStates[1])
                    ),
                    $id,
                    true,
                    true
                );

                if ($id > 0) {
                    return intval($id);
                }
            }
        }
        return null;
    }

    /**
     * Initialize Smart Element object
     *
     * The document is not yet recorded to database and has no identifier
     *
     * @param int|string $structureIdentifier
     *
     * @return \Anakeen\Core\Internal\SmartElement
     * @throws Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function initializeDocument($structureIdentifier)
    {
        $famId = self::getFamilyIdFromName($structureIdentifier);

        if (empty($famId)) {
            throw new Exception("APIDM0001", $structureIdentifier);
        }
        /**
         * @var \Anakeen\Core\SmartStructure $family
         */
        $family = self::getFamily($famId);
        if ($family === null) {
            throw new Exception("APIDM0002", $structureIdentifier, $famId);
        }

        $classname = "Doc" . $famId;
        self::requireFamilyClass($family->id);
        /* @var  \Anakeen\Core\Internal\SmartElement $doc */
        $doc = new $classname();

        $doc->revision = "0";
        $doc->doctype = $doc->defDoctype; // it is a new  document (not a familly)
        $doc->fromid = $famId;
        $doc->fromname = $doc->attributes->fromname;

        $doc->icon = $family->icon; // inherit from its familly
        $doc->usefor = $family->usefor; // inherit from its familly


        return $doc;
    }

    public static function requireFamilyClass($familyId)
    {
        if (!is_numeric($familyId)) {
            throw new Exception("APIDM0102", $familyId);
        }
        $classFilePath = sprintf("%s/%s/SmartStructure/Smart%d.php", DEFAULT_PUBDIR, Settings::DocumentGenDirectory, $familyId);
        /** @noinspection PhpIncludeInspection */
        require_once($classFilePath);
    }

    /**
     * Create Smart Element object
     *
     * The Smart Element is not yet recorded to database and has no identifier
     * The Smart Element is NOT under access control
     *
     * @param int|string $structureIdentifier
     * @param bool $useDefaultValues
     *
     * @return \Anakeen\Core\Internal\SmartElement
     * @throws Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Exception
     */
    public static function createDocument($structureIdentifier, $useDefaultValues = true)
    {
        $doc = self::initializeDocument($structureIdentifier);
        /**
         * @var \Anakeen\Core\SmartStructure $family
         */
        $family = self::getFamily($doc->fromid);

        $doc->wid = $family->wid;

        // inherit from its family
        $doc->accessControl()->setProfil($family->cprofid);
        $doc->accessControl()->setCvid($family->ccvid);
        $doc->accessControl()->setFallid($family->cfallid);
        if ($doc->wid) {
            // Set first state and workflow profiles
            $wdoc = SEManager::getDocument($doc->wid);
            if ($wdoc) {
                /** @var Wdoc $wdoc */
                $wdoc->set($doc);
            }
        }
        $doc->disableAccessControl(true);
        if ($useDefaultValues) {
            $doc->setDefaultValues($family->getDefValues());
        }

        return $doc;
    }

    /**
     * Create Smart Element object
     *
     * The Smart Element is not yet recorded to database and has no identifier
     * This Smart Element has no profile. It will be destroyed by schedule task program
     *
     * @param int|string $familyIdentifier
     * @param bool $useDefaultValues
     *
     * @return \Anakeen\Core\Internal\SmartElement
     * @throws Exception
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     * @throws \Anakeen\Exception
     */
    public static function createTemporaryDocument($familyIdentifier, $useDefaultValues = true)
    {
        $doc = self::initializeDocument($familyIdentifier);
        $doc->doctype = 'T';
        $doc->disableAccessControl(true);
        if ($useDefaultValues) {
            /**
             * @var \Anakeen\Core\SmartStructure $family
             */
            $family = self::getFamily($doc->fromid);
            $doc->setDefaultValues($family->getDefValues());
        }
        return $doc;
    }

    /**
     * Get document's values
     *
     * retrieve raw values directly from database
     *
     * @param int|string $documentIdentifier
     * @param bool $latest
     *
     * @return string[] indexed properties and attributes values
     * @throws \Anakeen\Database\Exception
     * @throws Exception
     * @api Get indexed array with property values and attribute values
     */
    public static function getRawDocument($documentIdentifier, $latest = true)
    {
        $id = self::getIdentifier($documentIdentifier, $latest);
        if ($id > 0) {
            $fromid = self::getFromId($id);
            if ($fromid > 0) {
                $table = "doc$fromid";
            } elseif ($fromid == -1) {
                $table = "docfam";
            } else {
                return [];
            } // no document can be found

            $sql = sprintf("select * from only \"%s\" where id=%d", $table, $id);

            DbManager::query($sql, $result, false, true);
            return $result;
        }
        return [];
    }

    /**
     * Create Smart Element object from data's values
     *
     * No call to database is done to retrieve attributes values
     *
     * @param string[] $rawDocument
     *
     * @return \Anakeen\Core\Internal\SmartElement
     * @throws Exception APIDM0104, APIDM0105
     * @throws \Anakeen\Core\Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function getDocumentFromRawDocument(array $rawDocument)
    {
        if (empty($rawDocument["id"]) || !self::getIdentifier($rawDocument["id"], false)) {
            throw new Exception("APIDM0104", print_r($rawDocument, true));
        }
        if ($rawDocument["doctype"] == "C") {
            $d = new \Anakeen\Core\SmartStructure();
        } else {
            if ($rawDocument["fromid"] > 0) {
                $d = self::initializeDocument($rawDocument["fromid"]);
            } else {
                throw new Exception("APIDM0105", print_r($rawDocument, true));
            }
        }
        $d->affect($rawDocument);
        return $d;
    }

    /**
     * Get Smart Element title
     *
     * Retrieve raw title of document directly from database.
     * No use any cache
     * No use \Anakeen\Core\Internal\SmartElement::getCustomTitle(), so dynamic title cannot be get with this method
     *
     * @param int|string $documentIdentifier
     * @param bool $latest
     *
     * @return string|null
     * @throws \Anakeen\Database\Exception
     * @throws Exception
     * @see \Anakeen\Core\Internal\SmartElement::getTitle()
     *
     */
    public static function getTitle($documentIdentifier, $latest = true)
    {
        $id = self::getIdentifier($documentIdentifier, $latest);
        if ($id > 0) {
            $sql = sprintf("select title from docread where id=%d", $id);
            DbManager::query($sql, $result, true, true);

            return $result;
        }

        return null;
    }

    /**
     * Get Smart Element properties
     *
     * Retrieve proterties of document directly from database.
     * No use any cache
     *
     * @param int|string $documentIdentifier
     * @param bool $latest
     * @param array $returnProperties list properties to return, if empty return all properties.
     *
     * @return string[] indexed array of properties
     * @throws \Anakeen\Database\Exception
     * @throws Exception
     */
    public static function getDocumentProperties($documentIdentifier, array $returnProperties, $latest = true)
    {
        $id = self::getIdentifier($documentIdentifier, $latest);
        if ($id > 0) {
            if (count($returnProperties) == 0) {
                $returnProperties = array_keys(\Anakeen\Core\Internal\SmartElement::$infofields);
            }
            $sqlSelect = array();
            foreach ($returnProperties as $rProp) {
                $sqlSelect[] = sprintf('"%s"', pg_escape_string($rProp));
            }
            $sql = sprintf("select %s from docread where id=%d", implode(',', $sqlSelect), $id);
            DbManager::query($sql, $result, false, true);

            return $result;
        }

        return null;
    }

    /**
     * Get raw value for a Smart Element
     *
     * Retrieve raw value of document directly from database
     *
     * @param string|int $documentIdentifier
     * @param string $dataIdentifier attribute or property identifier
     * @param bool $latest
     * @param bool $useCache if true use cache object if exists
     *
     * @return string the value
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function getRawValue($documentIdentifier, $dataIdentifier, $latest = true, $useCache = true)
    {
        $id = self::getIdentifier($documentIdentifier, $latest);
        if ($id > 0) {
            $dataIdentifier = strtolower($dataIdentifier);
            if ($useCache) {
                if (self::cache()->isDocumentIdInCache($id)) {
                    $cacheDoc = self::cache()->getDocumentFromCache($id);
                    return $cacheDoc->getRawValue($dataIdentifier);
                }
            }
            //$sql=sprintf("select avalues->'%s' from docread where id=%d", pg_escape_string($dataIdentifier), $id); // best perfo but cannot distinct null values and id not exists
            $fromid = self::getFromId($id);
            if ($fromid > 0) {
                $sql = sprintf("select %s from doc%d where id=%d", pg_escape_string($dataIdentifier), $fromid, $id);
                DbManager::query($sql, $result, true, true);
                if ($result === null) {
                    $result = '';
                } elseif ($result === false) {
                    $result = null;
                }
                return $result;
            }
        }

        return null;
    }


    /**
     * Get some raw data for a Smart Element
     *
     * Retrieve raw value of Smart Element
     *
     * @param string|int $documentIdentifier
     * @param string[] $dataIdentifiers list of attribute or property identifiers
     * @param bool $latest
     * @param bool $useCache if true use cache object if exists
     *
     * @return array|null the values indexed by attribute or property identifiers, null if not found
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function getRawData($documentIdentifier, array $dataIdentifiers, $latest = true, $useCache = true)
    {
        $id = self::getIdentifier($documentIdentifier, $latest);
        if ($id > 0) {
            if ($useCache) {
                if (self::cache()->isDocumentIdInCache($id)) {
                    $cacheDoc = self::cache()->getDocumentFromCache($id);
                    $data = [];
                    foreach ($dataIdentifiers as $dataIdentifier) {
                        $data[$dataIdentifier] = $cacheDoc->getRawValue($dataIdentifier);
                    }
                    return $data;
                }
            }
            //$sql=sprintf("select avalues->'%s' from docread where id=%d", pg_escape_string($dataIdentifier), $id); // best perfo but cannot distinct null values and id not exists
            $fromid = self::getFromId($id);

            if ($fromid !== null) {
                $selects = [];
                foreach ($dataIdentifiers as $dataIdentifier) {
                    $selects[] = pg_escape_identifier($dataIdentifier);
                }

                if ($fromid === -1) {
                    $table = "docfam";
                } else {
                    $table = sprintf("doc%d", $fromid);
                }
                $sql = sprintf("select %s from %s where id=%d", implode(",", $selects), $table, $id);
                DbManager::query($sql, $result, false, true);
                if ($result === false) {
                    $result = null;
                }
                return $result;
            }
        }

        return null;
    }


    /**
     * Return numerical id
     *
     * @param int|string $documentIdentifier document identifier
     * @param bool $latest if true search latest id
     *
     * @return int
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function getIdentifier($documentIdentifier, $latest)
    {
        if (empty($documentIdentifier)) {
            return 0;
        }
        if (!is_numeric($documentIdentifier)) {
            $id = self::getIdFromName($documentIdentifier);
        } else {
            $id = intval($documentIdentifier);
            if ($latest) {
                $lid = self::getLatestDocumentId($id);
                if ($lid > 0) {
                    $id = $lid;
                }
            }
        }
        return $id;
    }

    /**
     * Get latest id from Smart Element name (logical name)
     *
     * @param string $documentName
     *
     * @return int (return 0 if not found)
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     * @api Get document identifier fro logical name
     */
    public static function getIdFromName($documentName)
    {
        $documentName = trim($documentName);
        if (empty($documentName)) {
            return 0;
        }
        if (!is_string($documentName)) {
            throw new Exception("APIDM0101", print_r($documentName, true));
        }

        if (!preg_match('/^[a-z][a-z0-9_-]+$/i', $documentName)) {
            return 0;
        }

        $dbid = DbManager::getDbid();

        $id = 0;

        if (self::$firstgetIdFromName) {
            pg_prepare($dbid, "dm_getidfromname", 'select id from docname where name=$1');
            self::$firstgetIdFromName = false;
        }
        $result = pg_execute($dbid, "dm_getidfromname", array(
            trim($documentName)
        ));
        $n = pg_num_rows($result);
        if ($n > 0) {
            $arr = pg_fetch_array($result, ($n - 1), PGSQL_ASSOC);
            $id = intval($arr["id"]);
        }

        if ($id === 0) {
            // May be a deleted document
            DbManager::query(sprintf("select id from docread where name='%s' and doctype='Z' order by id desc limit 1", pg_escape_string($documentName)), $deletedId, true, true);

            if ($deletedId) {
                $id = intval($deletedId);
            }
        }
        return $id;
    }

    /**
     * Get Smart Element name (logical name) from numerical identifier
     *
     * @param int $documentId
     *
     * @return string|null return null if id not found
     * @throws \Anakeen\Database\Exception
     * @api Get logical name of a document
     */
    public static function getNameFromId($documentId)
    {
        $dbid = DbManager::getDbid();
        $id = intval($documentId);
        $name = null;
        if (self::$firstgetNameFromId) {
            pg_prepare($dbid, "dm_getNameFromId", 'select name from docread where id=$1');
            self::$firstgetNameFromId = false;
        }
        $result = pg_execute($dbid, "dm_getNameFromId", array(
            $id
        ));
        $n = pg_num_rows($result);
        if ($n > 0) {
            $arr = pg_fetch_array($result, ($n - 1), PGSQL_ASSOC);
            $name = $arr["name"];
        }
        return $name;
    }

    /**
     * Get Smart Structure Id
     *
     * @param string $famName familyName
     * @param bool $reset
     *
     * @return int return 0 if id not found
     * @throws \Anakeen\Database\Exception
     */
    public static function getFamilyIdFromName($famName, $reset = false)
    {
        if (!isset(self::$familyNames) || $reset) {
            self::$familyNames = array();
            DbManager::query("select id, name from docfam", $r);

            foreach ($r as $v) {
                if ($v["name"] != "") {
                    self::$familyNames[strtoupper($v["name"])] = intval($v["id"]);
                }
            }
        }
        if (is_numeric($famName)) {
            if (in_array($famName, self::$familyNames)) {
                return $famName;
            } else {
                if (!$reset) {
                    return self::getFamilyIdFromName($famName, true);
                }
            }
        } else {
            $name = strtoupper($famName);
            if (isset(self::$familyNames[$name])) {
                return self::$familyNames[$name];
            }
        }

        return 0;
    }

    public static function getFamilyClassName($famName)
    {
        return "\\SmartStructure\\" . ucwords(strtolower($famName));
    }

    public static function getAttributesClassName($famName)
    {
        return sprintf("\\SmartStructure\\%sAttributeList", ucwords(strtolower($famName)));
    }


    public static function getAttributesClassFilename($famName)
    {
        return sprintf("%s/%s/SmartStructure/%sAttributeList.php", DEFAULT_PUBDIR, Settings::DocumentGenDirectory, ucwords(strtolower($famName)));
    }

    public static function getDocumentClassFilename($famName)
    {
        return sprintf("%s/%s/SmartStructure/%s.php", DEFAULT_PUBDIR, Settings::DocumentGenDirectory, ucwords(strtolower($famName)));
    }

    /**
     * Get Smart Element fromid
     *
     * @param int|string $documentId document identifier
     *
     * @return null|int
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function getFromId($documentId)
    {
        if (!$documentId) {
            return null;
        }
        if (!is_numeric($documentId)) {
            $documentId = self::getIdFromName($documentId);
        }
        if (!$documentId) {
            return null;
        }
        $dbid = DbManager::getDbid();
        $fromid = null;

        $result = pg_query($dbid, sprintf("select fromid from docfrom where id=%d", $documentId));
        if ($result) {
            if (pg_num_rows($result) > 0) {
                $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
                $fromid = intval($arr["fromid"]);
            }
        }

        return $fromid;
    }


    /**
     * Get Smart Element from name
     *
     * @param int|string $documentId document identifier
     *
     * @return null|string
     * @throws Exception
     * @throws \Anakeen\Database\Exception
     */
    public static function getFromName($documentId)
    {
        if (!$documentId) {
            return null;
        }
        if (!is_numeric($documentId)) {
            $documentId = self::getIdFromName($documentId);
        }
        $dbid = DbManager::getDbid();
        $fromName = null;

        $result = pg_query($dbid, sprintf(
            "select docfam.name from docfrom, docfam where docfrom.id=%d and docfam.id=docfrom.fromid",
            $documentId
        ));
        if ($result) {
            if (pg_num_rows($result) > 0) {
                $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
                $fromName = $arr["name"];
            }
        }

        return $fromName;
    }

    /**
     * Return Smart Element Cache Object
     *
     * @return DocManager\Cache
     */
    public static function &cache()
    {
        static $documentCache = null;
        if ($documentCache === null) {
            $documentCache = new DocManager\Cache();
        }
        return $documentCache;
    }
}
