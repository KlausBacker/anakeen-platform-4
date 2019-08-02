<?php

namespace Anakeen\SmartStructures\Task\Render;

use Anakeen\Core\Internal\SmartElement;
use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderAttributeVisibilities;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\TextRenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use SmartStructure\Fields\Task as TaskFields;

class TaskEditRender extends DefaultConfigEditRender
{
    use TaskRender;

    public function getOptions(\Anakeen\Core\Internal\SmartElement $document): RenderOptions
    {
        $options = parent::getOptions($document);

        $this->addCommonOptions($options, $document);
        $tpl = <<<HTML
        
<div>
  {{{attribute.htmlDefaultContent}}}
  
<div class="task-crontab-info">
    <div class="task-crontab">
       <div class="task-crontab-box">
           <span class="task-crontab-value">{{attribute.attributeValue.crontab.minutes}}</span>
           <span class="task-legend">Minutes</span>
        </div>
        
       <div class="task-crontab-box">
           <span class="task-crontab-value">{{attribute.attributeValue.crontab.hours}}</span>
           <span class="task-legend">Hours</span>
        </div>
        
       <div class="task-crontab-box">
           <span class="task-crontab-value">{{attribute.attributeValue.crontab.days}}</span>
           <span class="task-legend">Day of month</span>
        </div>
        
       <div class="task-crontab-box">
           <span class="task-crontab-value">{{attribute.attributeValue.crontab.months}}</span>
           <span class="task-legend">Month</span>
        </div>
        
       <div class="task-crontab-box">
           <span class="task-crontab-value">{{attribute.attributeValue.crontab.weekDays}}</span>
           <span class="task-legend">Day of week</span>
        </div>
    </div>
    <div class="task-next-dates">
        <p>Next execution dates :</p>
        <ol >
            {{#attribute.attributeValue.dates}}
            <li>{{.}}</li>
            {{/attribute.attributeValue.dates}}
        </ol>
    </div>
</div>
</div>
HTML;

        $options->text(TaskFields::task_crontab)->setTemplate($tpl);

        $tplHuman = <<<HTML
  {{{attribute.htmlDefaultContent}}}
<div class="task-dates"></div>
HTML;
        $options->text(TaskFields::task_humancrontab)->setTemplate($tplHuman);
        $options->text(TaskFields::task_crontab)->setPlaceHolder("5 * * * *")->setDescription(
            "<p>Schedule is defined by a cron tab expression. Set the five parts separed by space.</p>"
        )->setLabelPosition(TextRenderOptions::nonePosition);
        return $options;
    }

    public function getVisibilities(\Anakeen\Core\Internal\SmartElement $document, \SmartStructure\Mask $mask = null): RenderAttributeVisibilities
    {
        $vis = parent::getVisibilities($document, $mask);

        $vis->setVisibility(TaskFields::task_humancrontab, RenderAttributeVisibilities::StaticWriteVisibility);
        return $vis;
    }

    public function getJsReferences(SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["taskRenderEdit"] = $path["TaskEdit"]["js"];
        $js = $this->getCommonJSReferences($js);
        return $js;
    }
}
