<?php

namespace Anakeen\SmartStructures\Task\Render;

use Anakeen\SmartStructures\Task\CrontabManager;
use Anakeen\Ui\DefaultConfigViewRender;
use Anakeen\Ui\BarMenu;
use Anakeen\Ui\ItemMenu;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\TextRenderOptions;
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
        $options->text(TaskFields::task_status)->setLabelPosition(TextRenderOptions::leftPosition);


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
        if ($document->isFixed()) {
            $options->frame(TaskFields::task_fr_route)->setCollapse(true);
            $options->frame(TaskFields::task_fr_ident)->setCollapse(true);
            $options->frame(TaskFields::task_fr_result)->setResponsiveColumns(
                [
                    ["number" => 3, "minWidth" => "50rem", "grow" => true]
                ]
            );
        }
        return $options;
    }

    public function getMenu(\Anakeen\Core\Internal\SmartElement $document): BarMenu
    {
        $menu = parent::getMenu($document);

        /**
         * @var \SmartStructure\Task $document
         */
        if ($document->revision > 0) {
            $item = new ItemMenu("viewLast", ___("View previous results", "smart exec"));
            $item->setUrl(sprintf("#action/document.load:%d:%s:%d", $document->initid, \Anakeen\Routes\Ui\DocumentView::defaultViewConsultationId, $document->revision - 1));
            $item->setBeforeContent('<div class="fa fa-eye" />');
            $menu->appendElement($item);
        }
        if ($document->canExecuteRoute()) {
            $item = new ItemMenu("executeNow", ___("Execute now", "smart exec"));
            $item->setUrl("#action/task:executeNow");
            $item->useConfirm(sprintf(___("Execute now the task %s", "smart exec"), $document->getTitle()));
            $item->setBeforeContent('<div class="fa fa-cog" />');
            $menu->appendElement($item);
        }
        return $menu;
    }


    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $vis= parent::getVisibilities($document, $mask);
        $vis->setVisibility(TaskFields::task_title, RenderAttributeVisibilities::HiddenVisibility);
        if ($document->isFixed()) {
            $vis->setVisibility(TaskFields::task_fr_schedule, RenderAttributeVisibilities::HiddenVisibility);
            $vis->setVisibility(TaskFields::task_status, RenderAttributeVisibilities::HiddenVisibility);
        }
        return $vis;
    }
}
