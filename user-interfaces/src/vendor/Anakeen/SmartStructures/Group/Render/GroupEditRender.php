<?php

namespace Anakeen\SmartStructures\Group\Render;

use Anakeen\SmartElementManager;
use Anakeen\Ui\DefaultConfigEditRender;
use Anakeen\Ui\RenderOptions;
use Anakeen\Ui\UIGetAssetPath;
use \SmartStructure\Fields\Group as myAttributes;

class GroupEditRender extends DefaultConfigEditRender
{
    protected $defaultGroup;

    /**
     * @param \Anakeen\Core\Internal\SmartElement $document
     * @return \Anakeen\Ui\RenderOptions
     * @throws \Anakeen\Ui\Exception
     */
    public function getOptions(\Anakeen\Core\Internal\SmartElement $document):RenderOptions
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

        $path = UIGetAssetPath::getElementAssets("smartStructures", UIGetAssetPath::isInDebug() ? "dev" : "legacy");
        $js["dduiIuser"] = $path["Iuser"]["js"];

        return $js;
    }
}
