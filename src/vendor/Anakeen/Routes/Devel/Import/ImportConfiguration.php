<?php

namespace Anakeen\Routes\Devel\Import;

class ImportConfiguration extends \Anakeen\Workflow\ImportWorkflowConfiguration
{
    protected $verboseMessages = [];

    public function print($data)
    {
        $textData = "\n";
        foreach ($data as $line) {
            foreach ($line as $item) {
                $textData .= sprintf("%s, ", str_replace("\n", " ", print_r($item, true)));
            }
            $textData .= "\n";
        }
        $this->verboseMessages[] = $textData;
    }

    /**
     * @return array
     */
    public function getVerboseMessages(): array
    {
        return $this->verboseMessages;
    }
}
