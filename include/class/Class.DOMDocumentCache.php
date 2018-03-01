<?php
/*
 * @author Anakeen
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
         * then committing by replacing final file
        */
        $filename = $this->documentURI;
        if ($filename === null) {
            throw new Exception(sprintf("documentURI location is not defined."));
        }
        $filename = rawurldecode($filename);
        $dirName = dirname($filename);
        $baseName = basename($filename);
        $tmpfile = WiffLibSystem::tempnam($dirName, 'tmp.' . $baseName . '.XXXXXX');
        if ($tmpfile === false) {
            throw new Exception(sprintf("Transaction error creating temporary file in '%s'.", $dirName));
        }
        /*
         * DOMDocument::save() returns bool(false) on error or the number of bytes successfully written.
         *
         * Therefore the number of bytes written can be < to the real content's length when not enough disk space
         * is available.
         *
         * The problem is that I do not know in advance what is the content's length, hence I cannot check that the
         * number of bytes written is exactly the content's length and not lower.
         *
         * So, I have the option of first serializing the XML to a string to compute the content's length, then write
         * it with save(), and finally compare the actual number of bytes written with the expected content's length.
         *
         * But am I sure that saveXML() and save() will always generate the exact same content?
         *
         * If I'm not 100% sure that saveMXL() and save() will yield the exact same content, then I can handle the
         * write myself with the XML content obtained previously from saveXML().
         *
         * PHP's file_put_contents() seems to return bool(false) when not enough free disk space is available: so, it
         * will be perfect for handling our "not enough free disk space" edge-case.
         *
         * However this will come with a negative counterpart in the form of a potential increase in memory usage as
         * we need to store the serialized XML content as a PHP string in memory before writing it to disk.
        */
        $xml = $this->saveXML(null, $options);
        $ret = file_put_contents($tmpfile, $xml);
        if ($ret === false || $xml === false || $ret !== strlen($xml)) {
            unlink($tmpfile);
            throw new Exception(sprintf("Transaction error saving content to temporary file '%s'.", $tmpfile));
        }
        if (rename($tmpfile, $filename) === false) {
            unlink($tmpfile);
            throw new Exception(sprintf("Transaction error renaming temporary file '%s' to '%s'.", $tmpfile, $filename));
        }
        $this->originalHashId = $this->_hashId();
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
