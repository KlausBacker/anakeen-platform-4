<?php

namespace Anakeen\Ui;

/**
 * Class ExportRenderAllConfiguration
 *
 * Export Smart Structure All configuration in Xml
 */
class ExportRenderAllConfiguration extends ExportRenderAccessConfiguration
{
    protected function extract(\DOMElement $structConfig)
    {
        $this->domConfig->setAttribute("xmlns:" . self::NS, self::NSURL);
        $this->domConfig->setAttribute("xmlns:" . self::NSUI, self::NSUIURL);

        $this->extractCv($this->domConfig);

        $this->setComment("View control accesses");
        $this->extractCvAccess($this->domConfig);

        $this->setComment("Structure Profil Accesses");
        $this->extractProfil($structConfig);

        $this->extractProps($structConfig);
        $this->extractAttr($structConfig);
        $this->extractModAttr($structConfig);
        $this->extractHooks($structConfig);
        $this->extractAutoComplete($structConfig);
        $this->extractDefaults($structConfig);
        $this->extractEnums();

        $this->domConfig->appendChild($structConfig);
    }
}
