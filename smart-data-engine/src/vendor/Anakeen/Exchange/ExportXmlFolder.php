<?php
/**
 * Export documents in xml
 *
 */

namespace Anakeen\Exchange;

class ExportXmlFolder
{
    const zipFormat = 'X';
    const xmlFormat = 'Y';
    private $dbaccess;
    private $exported = false;
    private $outputFile = '';
    private $useIdentificator = false;
    /**
     * @var string output format X or Y
     */
    private $format = self::xmlFormat;
    
    public function __construct()
    {
        $this->dbaccess = \Anakeen\Core\DbManager::getDbAccess();
    }
    /**
     * export format xml or zip
     * @param string $xy
     * @throws \Anakeen\Exception
     */
    public function setOutputFormat($xy)
    {
        if ($xy == self::zipFormat || $xy == self::xmlFormat) {
            $this->format = $xy;
        } else {
            throw new \Anakeen\Exception(sprintf("format must be %s or %s", self::zipFormat, self::xmlFormat));
        }
    }
    /**
     * export (or not) system document identifier
     * @param bool $exportIdentificator export option
     */
    public function useIdentificator($exportIdentificator = true)
    {
        $this->useIdentificator = $exportIdentificator;
    }
    /**
     * return exported file name
     * @return string file path
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }
    private function setOutputFile($outputFile = '')
    {
        if (!$outputFile) {
            $ext = 'nop';
            if ($this->format == self::xmlFormat) {
                $ext = "xml";
            } elseif ($this->format == self::zipFormat) {
                $ext = 'zip';
            }
            $this->outputFile = uniqid(\Anakeen\Core\ContextManager::getTmpDir() . "/export") . ".$ext";
        } else {
            $this->outputFile = $outputFile;
        }
        return $this->outputFile;
    }
    /**
     * return content of xml file to be use only with xml format
     * @return string
     * @throws \Anakeen\Exception
     */
    public function getXmlContent()
    {
        if (!$this->exported) {
            throw new \Anakeen\Exception(sprintf(_("nothing to export. Do export before")));
        }
        if ($this->format != self::xmlFormat) {
            throw new \Anakeen\Exception(sprintf(_("not in XML format")));
        }
        return file_get_contents($this->outputFile);
    }
    /**
     * export documents from search object
     *
     * @param \Anakeen\Search\Internal\SearchSmartData $search     search to export
     * @param string         $outputFile path to output file
     *
     * @return void
     */
    public function exportFromSearch(\Anakeen\Search\Internal\SearchSmartData &$search, $outputFile = '')
    {
        $this->setOutputFile($outputFile);
        \Anakeen\Exchange\ExportXml::exportxmlfld($folder = "0", $famid = "", $search, $this->outputFile, $this->format, $this->useIdentificator ? 'Y' : 'N');
    }
    /**
     * export documents from search object
     * @param string $folderId collection identifier
     * @param string $outputFile path to output file
     * @return void
     */
    public function exportFromFolder($folderId, $outputFile = '')
    {
        $this->setOutputFile($outputFile);
        \Anakeen\Exchange\ExportXml::exportxmlfld($folderId, $famid = "", null, $this->outputFile, $this->format, $this->useIdentificator ? 'Y' : 'N');
    }
}
