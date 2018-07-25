<?php
/*
 * @author Anakeen
 * @package DDUI
*/

namespace Anakeen\SmartStructures\Exec\Render;

use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Routes\Ui\CallMenuResponse;
use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\BarMenu;
use Dcp\Ui\CallableMenu;
use Dcp\Ui\RenderOptions;

class ExecViewRender extends DefaultConfigViewRender
{
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document) : RenderOptions
    {
        $options = parent::getOptions($document);
        return $options;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document) : BarMenu
    {
        $menu = parent::getMenu($document);

        /**
         * @var \SmartStructure\Exec $document
         */
        $item = new CallableMenu("bgExecute", ___("Execute now", "smart exec"));
        $item->setCallable(
            function () use ($document) : CallMenuResponse {
                $status = $document->bgExecute();

                $msg = new ApiMessage();
                if ($status !== 0) {
                    $msg->type = ApiMessage::ERROR;
                    $msg->contentText = sprintf(___("Execution failed", "smart exec"));
                } else {
                    $msg->type = ApiMessage::SUCCESS;
                    $msg->contentText = sprintf(___("Execution succeeded", "smart exec"));
                }
                $response=new CallMenuResponse();
                $response->setReload(true);

                return $response->setMessage($msg);
            }
        );
        $menu->appendElement($item);
        return $menu;
    }
}
