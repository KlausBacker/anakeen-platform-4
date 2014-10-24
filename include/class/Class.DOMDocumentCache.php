<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package CONTROL
*/

class DOMDocumentCache extends DOMDocument
{
    private $originalHashId = null;
    public function load($filename, $options = 0)
    {
        $ret = parent::load($filename, $options);
        if ($ret === false) {
            return false;
        }
        $this->originalHashId = $this->_hashId();
        return $ret;
    }
    public function save($filename, $options = 0)
    {
        /*
         * Prohibit direct calls to save() as
         * it would allow to save the XML
         * to a different filename and thus
         * break the caching mechanism
        */
        throw new Exception("Method not allowed. Use commit() instead to write back changes.");
    }
    public function commit($options = 0)
    {
        require_once 'lib/Lib.System.php';
        
        if (!$this->_hasBeenModified()) {
            /*
             * No need to save if the document has not
             * been modified.
            */
            return 0;
        }
        /*
         * Try to safely write content to disk
         * by first writing content to a temporary file
         * then commiting by replacing final file
        */
        $filename = $this->documentURI;
        if ($filename === null) {
            throw new Exception(sprintf("documentURI location is not defined."));
        }
        $dirName = dirname($filename);
        $baseName = basename($filename);
        $tmpfile = WiffLibSystem::tempnam($dirName, 'tmp.' . $baseName . '.XXXXXX');
        if ($tmpfile === false) {
            throw new Exception(sprintf("Transaction error creating temporary file in '%s'.", $dirName));
        }
        $ret = parent::save($tmpfile, $options);
        if ($ret === false) {
            unlink($tmpfile);
            throw new Exception(sprintf("Transaction error saving content to temporary file '%s'.", $tmpfile));
        }
        if (rename($tmpfile, $filename) === false) {
            unlink($tmpfile);
            throw new Exception(sprintf("Transaction error renaming temporary file '%s' to '%s'.", $tmpfile, $filename));
        }
        return $ret;
    }
    private function _hashId()
    {
        $str = $this->saveXML();
        return sprintf("%s:%s", sha1($str) , md5($str));
    }
    private function _hasBeenModified()
    {
        return ($this->originalHashId != $this->_hashId());
    }
}
