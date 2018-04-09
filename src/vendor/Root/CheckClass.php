<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckClass extends CheckData
{
    /**
     * @var string class name
     */
    protected $className;
    /**
     * @var string file where class is defined
     */
    protected $fileName;
    /**
     * @var DocFam
     */
    protected $doc;
    protected $disableInheritanceCondition = false;

    /**
     * @param array $data
     * @param Doc   $doc
     *
     * @return CheckClass
     */
    public function check(array $data, &$doc = null)
    {
        if (isset($data[2]) && $data[2] === "disableInheritanceCondition") {
            $this->disableInheritanceCondition = true;
        }
        if (!empty($data[1])) {
            $this->className = $data[1];

            $this->doc = $doc;
            $this->checkClassSyntax();
            $this->checkClassFile();
            $this->checkInherit();
        }
        return $this;
    }

    protected function checkClassSyntax()
    {
        if (!preg_match('/^[A-Z][A-Z_0-9\\\\]*$/i', $this->className)) {
            $this->addError(ErrorCode::getError('CLASS0001', $this->className, $this->doc->name));
        }
        return false;
    }

    protected function getClassFile()
    {
        return \Anakeen\Core\Internal\Autoloader::findFile($this->className);
    }

    /**
     * check if it is a folder
     *
     * @return void
     */
    protected function checkClassFile()
    {
        if ($this->className) {
            $classFile = $this->getClassFile();
            $fileName = realpath($classFile);
            if ($classFile && $fileName) {
                $this->fileName = $fileName;
                // Get the shell output from the syntax check command
                if (self::phpLintFile($fileName, $output) === false) {
                    $this->addError(ErrorCode::getError('CLASS0002', $classFile, $this->doc->name, implode("\n", $output)));
                }
            } else {
                $this->addError(ErrorCode::getError('CLASS0003', $this->className, $this->doc->name));
            }
        }
    }

    /**
     * Check PHP syntax of file (lint)
     *
     * @param string $fileName
     * @param array  $output Error message
     *
     * @return bool bool(true) if correct or bool(false) if error
     */
    public static function phpLintFile($fileName, &$output)
    {
        exec(sprintf('php -n -l %s 2>&1', escapeshellarg($fileName)), $output, $status);
        return ($status === 0);
    }

    protected function checkInherit()
    {
        try {
            $o = new ReflectionClass('\\' . $this->className);
            if (!$o->isInstantiable()) {
                $this->addError(ErrorCode::getError('CLASS0005', $this->className, $this->fileName, $this->doc->name));
            }
            if ($this->doc) {
                if ($this->doc->fromid > 0) {
                    $fromName = ucwords(strtolower(\Anakeen\Core\DocManager::getNameFromId($this->doc->fromid)));
                    if (!$fromName) {
                        $this->addError(ErrorCode::getError('CLASS0007', $this->className, $this->fileName, $this->doc->name));
                        return;
                    }

                    $parentClass = \Anakeen\Core\DocManager::getFamilyClassName($fromName);
                } else {
                    $parentClass = \Anakeen\SmartStructures\Document::class;
                }
                if ($this->disableInheritanceCondition) {
                    $parentClass = \Doc::class;

                }
                if (!$o->isSubclassOf($parentClass)) {
                    $this->addError(ErrorCode::getError('CLASS0006', $this->className, $this->fileName, $parentClass, $this->doc->name));
                }
            }
        } catch (\Exception $e) {
            $this->addError(ErrorCode::getError('CLASS0004', $this->className, $this->fileName, $this->doc->name, $e->getMessage()));
        }
    }
}
