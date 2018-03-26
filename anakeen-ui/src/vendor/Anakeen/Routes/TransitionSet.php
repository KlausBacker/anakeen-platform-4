<?php

namespace Anakeen\Routes\Ui;

use Anakeen\Routes\Core\DocumentApiData;
use Anakeen\Routes\Core\WorkflowSetState;

/**
 * Class TransitionView
 * @note    Used by route : GET /api/v2/documents/{docid}/views/states/{state}
 * @package Anakeen\Routes\Ui
 */
class TransitionSet extends WorkflowSetState
{
    protected $attributeCount = 0;
    protected $workflowData;
    /**
     * @var \WDoc|\Dcp\Ui\IRenderTransitionAccess
     */
    protected $workflow;
    /**
     * @var \FormatCollection
     */
    protected $formatCollection;

    /**
     * Change state
     * @param \Anakeen\Routes\Core\ApiMessage[] $messages
     * @return mixed
     * @throws \Anakeen\Router\Exception
     */
    public function doRequest(&$messages = [])
    {
        $info = parent::doRequest($messages);
        $info["labels"] = array(
            "close" => ___("Close Transition Window", "ddui"),
            "success" => ___("Transition success", "ddui")
        );
        $info["workflow"] = $this->getWorkflowData();
        return $info;
    }

    protected function getWorkflowData()
    {

        if ($this->workflowData === null) {
            $this->getWorkflowDataObject();
        }
        return $this->workflowData["document"];
    }

    protected function getWorkflowDataObject()
    {
        $workflow = new DocumentApiData($this->workflow);

        $info = array(
            "document.properties",
            "document.properties.family"
        );

        $workflow->setFields($info);
        $this->workflowData = $workflow->getDocumentData();
        return $workflow;
    }
}
