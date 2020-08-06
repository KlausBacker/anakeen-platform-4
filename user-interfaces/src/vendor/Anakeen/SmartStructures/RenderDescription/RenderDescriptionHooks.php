<?php


namespace Anakeen\SmartStructures\RenderDescription;

use Anakeen\Core\SEManager;
use Anakeen\Search\Filters\IsEqual;
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
        if ($this->initid) {
            $s->addFilter("initid != %d", $this->initid);
        }
        $s->addFilter(new IsEqual(DescriptionFields::rd_famid, $structureName));

        $modes = $this->rawValueToArray($mode);

        if ($lang) {
            $s->addFilter(new \Anakeen\Search\Filters\OrOperator(
                new \Anakeen\Search\Filters\IsEqual(DescriptionFields::rd_lang, substr($lang, 0, 2)),
                new \Anakeen\Search\Filters\IsEmpty(DescriptionFields::rd_lang)
            ));
        }

        $s->addFilter(new OneEqualsMulti(DescriptionFields::rd_mode, $modes));
        $s->search();

        if ($s->count() > 0) {
            $conflict = $s->getNextElement();
            return sprintf(
                ___("Already default mode set by another description \"%s\"", "renderdescription"),
                $conflict->getTitle()
            );
        }
        return "";
    }

    /**
     * Constraint for check default unicity
     * @param string $structureName
     * @param string $field
     * @return string
     */
    protected function checkFieldExists($structureName, $field)
    {
        $structure = SEManager::getFamily($structureName);
        if ($structure) {
            $oa=$structure->getAttribute($field);
            if (!$oa) {
                return sprintf(___("Field \"%s\" not exists in structure \"%s\"", "renderdescription"), $field, $structure->getTitle());
            }
        }
        return "";
    }
}
