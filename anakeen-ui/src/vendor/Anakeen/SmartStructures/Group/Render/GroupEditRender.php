<?php

namespace Anakeen\SmartStructures\Group\Render;

use Anakeen\SmartElementManager;
use Anakeen\Ui\DefaultConfigEditRender;
use \SmartStructure\Attributes\Group as myAttributes;

class GroupEditRender extends DefaultConfigEditRender
{
    protected $defaultGroup;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return \Dcp\Ui\RenderOptions
     * @throws \Dcp\Ui\Exception
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document)
    {
        $options = parent::getOptions($document);
        $options->enum(myAttributes::grp_hasmail)->setDisplay('bool');
        $options->enum(myAttributes::grp_hasmail)->displayDeleteButton(false);
        return $options;
    }

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @param mixed $data
     * @return mixed
     * @throws \Anakeen\Core\DocManager\Exception
     */
    public function setCustomClientData(\Anakeen\Core\Internal\SmartElement $document, $data)
    {
        if (!$document->getPropertyValue("initid") && isset($data["defaultGroup"])) {
            $this->defaultGroup = $data["defaultGroup"];
        }
        if (isset($data["setGroup"])) {
            $groupSE = SmartElementManager::getDocument($data["setGroup"]);
            /* @var $groupSE \SmartStructure\Igroup */
            $groupSE->insertDocument($document->getPropertyValue("initid"));
        }
        return $data;
    }

    public function getCustomServerData(\Anakeen\Core\Internal\SmartElement $document)
    {
        $data = parent::getCustomServerData($document);
        $data["EDIT_GROUP"] = true;
        if ($this->defaultGroup) {
            $data["defaultGroup"] = $this->defaultGroup;
        }
        return $data;
    }

    public function getJsReferences(\Anakeen\Core\Internal\SmartElement $smartElement = null)
    {
        $js = parent::getJsReferences();
        $version = \Anakeen\Core\ContextManager::getParameterValue("WVERSION");

        $js["iuser"] = '/uiAssets/Families/iuser/prod/iuser.js?ws=' . $version;
        if (\Dcp\Ui\UIGetAssetPath::isInDebug()) {
            $js["iuser"] = '/uiAssets/Families/iuser/debug/iuser.js?ws=' . $version;
        }

        return $js;
    }
}
