<?php


namespace Anakeen\SmartStructures\RenderDescription;

use Anakeen\Search\Filters\IsEqual;
use Anakeen\Search\Filters\OneContains;
use Anakeen\Search\Filters\OneEqualsMulti;
use Anakeen\Search\SearchElements;
use Anakeen\SmartHooks;
use SmartStructure\Fields\Renderdescription as DescriptionFields;

class RenderDescriptionHooks extends \Anakeen\SmartElement
{

    public function registerHooks()
    {
        parent::registerHooks();
        $this->getHooks()->addListener(SmartHooks::POSTSTORE, function () {
            //return $this->synchronizeSystemGroup();
        });
    }

    /**
     * Constraint for check default unicity
     * @param string $structureName
     * @param string $mode
     * @param string $lang
     * @return string
     * @throws \Anakeen\Search\Exception
     */
    protected function checkModeUnicity($structureName, $mode, $lang)
    {
        if (!$mode) {
            return "";
        }
        $s = new SearchElements($this->fromid);
        $s->overrideAccessControl();
        $s->addFilter("initid != %d", $this->initid);
        if ($this->initid) {
            $s->addFilter(new IsEqual(DescriptionFields::rd_famid, $structureName));
        }
        $modes=$this->rawValueToArray($mode);

       if ($lang) {
           $s->addFilter(new \Anakeen\Search\Filters\OrOperator(
               new \Anakeen\Search\Filters\IsEqual(DescriptionFields::rd_lang, substr($lang, 0, 2)),
               new \Anakeen\Search\Filters\IsEmpty(DescriptionFields::rd_lang)
           ));
       }

        $s->addFilter(new OneEqualsMulti(DescriptionFields::rd_mode,$modes ));
        $s->search();

       // var_dump($s->getSearchInfo());

        if ($s->count() > 0) {

            $conflict = $s->getNextElement();
            return sprintf(___("Already defaut mode set by another description \"%s\"", "renderdescription"),$conflict->getTitle() );
        }


    }
}
