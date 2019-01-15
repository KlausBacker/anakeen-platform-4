<?php

namespace Anakeen\Ui;

/**
 * Class ExportRenderAllConfiguration
 *
 * Export Smart Structure All configuration in Xml
 */
class ExportRenderAllConfiguration extends ExportRenderAccessConfiguration
{
    public function extract()
    {
        $this->domConfig->setAttribute("xmlns:" . self::NS, self::NSURL);
        $this->domConfig->setAttribute("xmlns:" . self::NSUI, self::NSUIURL);

        $this->extractCvRef();
        $this->extractDefaultCvData();

        $this->setComment("View control accesses");
        $this->extractCvAccess();

        $this->setComment("Structure Profil Accesses");
        $this->extractProfil();

        $this->extractProps();
        $this->extractAttr();
        $this->extractModAttr();
        $this->extractHooks();
        $this->extractAutoComplete();
        $this->extractDefaults();
        $this->extractEnums();

        $this->domConfig->appendChild($this->structConfig);
    }
}
