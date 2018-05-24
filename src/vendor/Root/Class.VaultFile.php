<?php
/**
 * Retrieve and store file in Vault
 *
 */

use \Anakeen\LogManager;

class VaultFile
{
    /**
     * @var int file ideentificator
     */
    public $idf;
    public $f_mode = 0600;
    public $d_mode = 0700;
    public $type = "fs";

    const VAULT_DMODE = 0700;
    /**
     * @var VaultDiskStorage
     */
    public $storage;
    protected $name;

    public function __construct($access = "", $vaultname = "Sample", $idf = -1)
    {

        $this->idf = $idf;
        $this->name = $vaultname;

        $this->f_mode = 0600;
        $this->d_mode = 0700;
        $this->type = "fs";
        switch ($this->type) {
            case "fs":
                $this->storage = new VaultDiskStorage();
                break;

            default:
                // Not implemented yet
        }
    }
    // ---------------------------------------------------------

    /**
     * @param int           $id_file    vault file identifier
     * @param vaultFileInfo $infos      file properties
     * @param string        $teng_lname engine name
     * @return string error message
     */
    public function show($id_file, &$infos, $teng_lname = "")
    {

        $msg = $this->storage->Show($id_file, $infos, $teng_lname);
        if ($msg != '') {
            LogManager::error(sprintf("File #%s : %s", $id_file, $msg));
        }

        return ($msg);
    }

    /**
     * Set access date to now
     * @param int $id_file vault file identifier
     * @return void
     */
    public function updateAccessDate($id_file)
    {
        $this->storage->updateAccessDate($id_file);
    }

    /**
     * retrieve information from vault id
     * @param int           $id_file
     * @param VaultFileInfo $infos
     * @return string error message
     */
    public function retrieve($id_file, &$infos)
    {
        // ---------------------------------------------------------

        if (isset($info)) {
            unset($infos);
        }

        $msg = $this->storage->Show($id_file, $infos);

        if ($msg != '') {
            LogManager::error($msg);
        }

        return ($msg);
    }

    // ---------------------------------------------------------
    public function store($infile, $public_access, &$id, $fsname = "", $te_name = "", $te_id_file = 0, $tmp = null)
    {
        // ---------------------------------------------------------

        $id = -1;
        if (!file_exists($infile) || !is_readable($infile) || !is_file($infile)) {
            LogManager::error("Can't access file [" . $infile . "].");
            $msg = _("can't access file");
        } else {
            if (!is_bool($public_access)) {
                $public_access = false;
                LogManager::warning("Access mode forced to RESTRICTED for " . $infile . "].");
            }
            $this->storage->id_tmp = $tmp;
            $msg = $this->storage->store($infile, $public_access, $id, $fsname, $te_name, $te_id_file);
            if ($msg) {
                LogManager::error($msg);
            }
        }

        return ($msg);
    }

    // ---------------------------------------------------------
    public function save($infile, $public_access, $id)
    {
        if (!is_bool($public_access)) {
            $public_access = false;
            LogManager::warning("Access mode forced to RESTRICTED for " . $infile . "].");
        }

        $msg = $this->storage->Save($infile, $public_access, $id);
        if ($msg) {
            LogManager::error($msg);
        }

        $this->storage->mime_t = \Anakeen\Core\Utils\FileMime::getTextMimeFile($infile);
        $this->storage->mime_s = \Anakeen\Core\Utils\FileMime::getSysMimeFile($infile, $this->storage->name);
        $msg = $this->storage->Modify();

        return ($msg);
    }

    /**
     * Modification of properties if file
     * @param int    $id_file vault id
     * @param string $newname new file name
     * @return string error message (empty if no error)
     */
    public function rename($id_file, $newname)
    {

        $msg = '';
        if ($newname != "") {
            $nn = str_replace(array(
                '/',
                '\\',
                '?',
                '*',
                ':'
            ), '-', $newname);
            if ($nn != $newname) {
                \Anakeen\Core\Utils\System::addWarningMsg(sprintf(_("Some characters are not authorized for file name %s. They are replaced by -"), $nn));
                $newname = $nn;
            }

            include_once("WHAT/Lib.FileMime.php");
            $infile = $this->storage->getPath();
            $oldname = $this->storage->name;
            $this->storage->Show($id_file, $infos);
            $this->storage->name = $newname;
            $this->storage->mime_t = \Anakeen\Core\Utils\FileMime::getTextMimeFile($infile, $this->storage->name);
            $this->storage->mime_s = \Anakeen\Core\Utils\FileMime::getSysMimeFile($infile, $this->storage->name);
            $msg = $this->storage->Modify();
            if ($msg == "") {
                $pio = pathinfo($oldname);
                $pin = pathinfo($newname);
                $epio = isset($pio['extension']) ? $pio['extension'] : "";
                if ($epio == "") {
                    $epio = "nop";
                }
                $epin = isset($pin['extension']) ? $pin['extension'] : "";
                if ($epin == "") {
                    $epin = "nop";
                }
                if ($epio != $epin) {
                    // need rename physically file
                    if (preg_match("|(.*)/([0-9]+)\\.[^\\.]*|", $infos->path, $reg)) {
                        $newpath = $reg[1] . "/" . $reg[2] . "." . $epin;
                        rename($infos->path, $newpath);
                    }
                }
            }
            if ($msg) {
                LogManager::error($msg);
            }
        }

        return ($msg);
    }

    // ---------------------------------------------------------
    public function listFiles(&$s)
    {

        $this->storage->listFiles($s);

        return '';
    }

    // ---------------------------------------------------------
    public function destroy($id)
    {

        $msg = $this->storage->destroy($id);
        if ($msg != '') {
            LogManager::error($msg);
        }
        return $msg;
    }
}
