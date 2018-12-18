<?php

namespace Anakeen\SmartStructures\Task\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Routes\Core\Lib\ApiMessage;
use Anakeen\Routes\Ui\CallMenuResponse;
use Anakeen\SmartStructures\Task\CrontabManager;
use Anakeen\Ui\DefaultConfigViewRender;
use Dcp\Ui\BarMenu;
use Dcp\Ui\CallableMenu;
use Dcp\Ui\RenderAttributeVisibilities;
use Dcp\Ui\RenderOptions;
use Dcp\Ui\TextRenderOptions;
use SmartStructure\Fields\Task as TaskFields;

class TaskViewRender extends DefaultConfigViewRender
{
    use TaskRender;
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $this->addCommonOptions($options, $document);

        $tpl = <<<HTML
<div class="task-crontab">
  
   <div class="task-crontab-box">
       <span class="task-crontab-value">{{minutes}}</span>
       <span class="task-legend">Minutes</span>
    </div>
    
   <div class="task-crontab-box">
       <span class="task-crontab-value">{{hours}}</span>
       <span class="task-legend">Hours</span>
    </div>
    
   <div class="task-crontab-box">
       <span class="task-crontab-value">{{days}}</span>
       <span class="task-legend">Day of month</span>
    </div>
    
   <div class="task-crontab-box">
       <span class="task-crontab-value">{{months}}</span>
       <span class="task-legend">Month</span>
    </div>
    
   <div class="task-crontab-box">
       <span class="task-crontab-value">{{weekDays}}</span>
       <span class="task-legend">Day of week</span>
    </div>
    

</div>
HTML;


        $crontab = $document->getRawValue(TaskFields::task_crontab);
        if ($crontab) {
            $dc=CrontabManager::getCrontabParts($crontab);

            $mustache = new \Mustache_Engine();
            $options->text(TaskFields::task_crontab)->setTemplate($mustache->render($tpl, $dc));
        }
        $options->text(TaskFields::task_humancrontab)->setLabelPosition(TextRenderOptions::nonePosition);


        $tplHuman = <<<HTML
    <div class="task-next-dates">
        {{{attribute.htmlDefaultContent}}}
        <p>Following execution dates :</p>
        <ol>
            {{#followDates}}
            <li>{{.}}</li>
            {{/followDates}}
        </ol>
    </div>
HTML;
        $follow["followDates"]=CrontabManager::getNextDates($crontab, 6, "l, F d Y, H:i");
        $options->text(TaskFields::task_humancrontab)->setTemplate($tplHuman, $follow);
        return $options;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $menu = parent::getMenu($document);

        /**
         * @var \SmartStructure\Task $document
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
                $response = new CallMenuResponse();
                $response->setReload(true);

                return $response->setMessage($msg);
            }
        );
        $menu->appendElement($item);
        return $menu;
    }


    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $vis= parent::getVisibilities($document, $mask);
        $vis->setVisibility(TaskFields::task_title, RenderAttributeVisibilities::HiddenVisibility);
        return $vis;
    }
}
