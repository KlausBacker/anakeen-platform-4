<?php
/**
 * Crontab class
 *
 * This class allows you to manipulate a user crontab by registering
 * and unregistering cron files
 *
 */

namespace Anakeen\Core\Crontab;

class CrontabManager
{
    public $user = null;
    public $crontab = '';

    public function __construct($user = null)
    {
        $this->user = $user;
        return $this;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this->user;
    }

    public function unsetUser()
    {
        $this->user = null;
        return $this->user;
    }

    private function load()
    {
        $cmd = 'crontab -l';
        if ($this->user != null) {
            $cmd .= ' -u ' . escapeshellarg($this->user);
        }
        $cmd .= ' 2> /dev/null';

        $ph = popen($cmd, 'r');
        if ($ph === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error popen");
            return false;
        }

        $crontab = stream_get_contents($ph);
        if ($crontab === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error stream_get_contents");
            return false;
        }

        $this->crontab = $crontab;

        return $crontab;
    }

    private function save()
    {
        $tmp = tempnam(\Anakeen\Core\ContextManager::getTmpDir(), 'crontab');
        if ($tmp === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error creating temporary file");
            return false;
        }

        $ret = file_put_contents($tmp, $this->crontab);
        if ($ret === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error writing content to file '" . $tmp . "'");
            return false;
        }

        $cmd = 'crontab';
        if ($this->user != null) {
            $cmd .= ' -u ' . escapeshellarg($this->user);
        }
        $cmd .= ' ' . escapeshellarg($tmp);
        $cmd .= ' > /dev/null 2>&1';

        system($cmd, $ret);
        if ($ret != 0) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error saving crontab '" . $tmp . "'");
            return false;
        }

        return $this->crontab;
    }

    public function registerFile($file)
    {

        $crontab = file_get_contents($file);
        if ($crontab === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error reading content from file '" . $file . "'");
            return false;
        }

        $newSectionElement = new CrontabSectionElement(DEFAULT_PUBDIR, $file);
        $element1 = new CrontabTextElement(sprintf("CONTEXT_ROOT=%s", DEFAULT_PUBDIR));
        $newSectionElement->appendChild($element1);
        $element2 = new CrontabTextElement($crontab);
        $newSectionElement->appendChild($element2);

        $crontabData = $this->load();
        if ($crontabData === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error reading active crontab");
            return false;
        }

        $parser = new CrontabParser();
        $crontabDocument = $parser->parse($crontabData);
        /* Remove existing sections for this context and file */
        $crontabDocument->childs = array_filter($crontabDocument->childs, function ($element) use ($file) {
            if (is_a($element, CrontabSectionElement::class)) {
                /**
                 * @var CrontabSectionElement $element
                 */
                /* Keep sections that do not match this context/file */
                return !$element->match(DEFAULT_PUBDIR, $file);
            }
            /* Keep text elements */
            return true;
        });
        /* Add new section */
        $crontabDocument->appendChild($newSectionElement);

        $this->crontab = (string)$crontabDocument;

        printf("Saving crontab...\n");
        $ret = $this->save();
        if ($ret === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error saving crontab");
            return false;
        }
        printf("Done.\n");

        return $this->crontab;
    }

    public function unregisterFile($file)
    {
        $crontabData = $this->load();
        if ($crontabData === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error reading active crontab");
            return false;
        }

        $parser = new CrontabParser();
        $crontabDocument = $parser->parse($crontabData);
        /* Remove existing sections for this context/file */
        $crontabDocument->childs = array_filter($crontabDocument->childs, function (CrontabElement $element) use ($file) {
            if (is_a($element, CrontabSectionElement::class)) {
                /**
                 * @var CrontabSectionElement $element
                 */
                /* Keep sections that do not match this context/file */
                return !$element->match(DEFAULT_PUBDIR, $file);
            }
            /* Keep other elements */
            return true;
        });

        $this->crontab = (string)$crontabDocument;

        printf("Saving crontab...\n");
        $ret = $this->save();
        if ($ret === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error saving crontab");
            return false;
        }
        printf("Done.\n");

        return $this->crontab;
    }

    public function listAll()
    {
        $crontabs = $this->getActiveCrontab();
        if ($crontabs === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error retrieving active crontabs");
            return false;
        }

        print "\n";
        print "Active crontabs\n";
        print "---------------\n";
        print "\n";
        foreach ($crontabs as $crontab) {
            print "Crontab: " . $crontab['file'] . "\n";
            print "--8<--\n" . $crontab['content'] . "\n-->8--\n\n";
        }

        return true;
    }

    public function getActiveCrontab()
    {
        $crontabData = $this->load();
        if ($crontabData === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error reading active crontab");
            return false;
        }

        $parser = new CrontabParser();
        $crontabDocument = $parser->parse($crontabData);

        $crontabs = array();
        foreach ($crontabDocument->childs as & $element) {
            /**
             * @var $element CrontabSectionElement
             */
            if (is_a($element, CrontabSectionElement::class) && $element->matchContextRoot(DEFAULT_PUBDIR)) {
                array_push($crontabs, array(
                    'file' => $element->file,
                    'content' => (string)$element
                ));
            }
        }
        unset($element);

        return $crontabs;
    }

    public function unregisterAll()
    {
        $crontabData = $this->load();
        if ($crontabData === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error reading active crontab");
            return false;
        }

        $parser = new CrontabParser();
        $crontabDocument = $parser->parse($crontabData);
        /* Remove existing sections for this context */
        $crontabDocument->childs = array_filter($crontabDocument->childs, function (CrontabElement $element) {
            if (is_a($element, CrontabSectionElement::class)) {
                /**
                 * @var $element CrontabSectionElement
                 */
                /* Keep sections that do not match the current context */
                return !$element->matchContextRoot(DEFAULT_PUBDIR);
            }
            /* Keep other elements */
            return true;
        });

        $this->crontab = (string)$crontabDocument;

        printf("Saving crontab...\n");
        $ret = $this->save();
        if ($ret === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " Error saving crontab");
            return false;
        }
        printf("Done.\n");

        return $this->crontab;
    }
}
