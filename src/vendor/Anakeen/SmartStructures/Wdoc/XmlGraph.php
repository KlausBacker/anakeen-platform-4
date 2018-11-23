<?php


namespace Anakeen\SmartStructures\Wdoc;

use Anakeen\Core\Utils\Xml;
use Dcp\Exception;

class XmlGraph
{
    const NS = "https://platform.anakeen.com/4/schemas/workflow/1.0";
    protected static $nsPrefix;

    public static function setWorkflowGraph(WDocHooks $wfl, $xmlFilePath)
    {
        if (!is_file($xmlFilePath)) {
            throw new Exception("WFL0009", $xmlFilePath);
        }
        $xmlGraph = new \DOMDocument();
        $xmlGraph->load($xmlFilePath);


        self::$nsPrefix = Xml::getPrefix($xmlGraph, self::NS);
        $nsPrefix = self::$nsPrefix;
        // print_r($xmlGraph->saveXML());

        $wfl->graphModelName = self::evaluate($xmlGraph->documentElement, "string({$nsPrefix}:graph/@name)");

        $steps = $xmlGraph->getElementsByTagNameNS(self::NS, "step");
        $transitions = $xmlGraph->getElementsByTagNameNS(self::NS, "transition");

        /**  @var \DOMElement $transition  */
        foreach ($transitions as $transition) {
            $wfl->cycle[] = [
                "e1" => $transition->getAttribute("from"),
                "e2" => $transition->getAttribute("to"),
                "t" => $transition->getAttribute("name")
            ];

            $wfl->transitions[$transition->getAttribute("name")] = [
                "label" => $transition->getAttribute("label")
            ];
        }


        /** @var \DOMElement $step */
        foreach ($steps as $step) {
            $stepRef=$step->getAttribute("name");

            $wfl->stepLabels[$stepRef] = [
                "state" => $step->getAttribute("state-label"),
                "activity" => $step->getAttribute("activity-label")
            ];
        }

        $wfl->attrPrefix = self::evaluate($xmlGraph->documentElement, "string({$nsPrefix}:graph/@ns)");
        if ($wfl->attrPrefix) {
            $wfl->attrPrefix = strtolower($wfl->attrPrefix);
        } else {
            $wfl->attrPrefix = "wfl";
        }
    }

    protected static function evaluate(\DOMElement $e, $path)
    {
        if (self::$nsPrefix === null) {
            self::$nsPrefix = Xml::getPrefix($e->ownerDocument, self::NS);
        }
        $xpath = new \DOMXpath($e->ownerDocument);
        $xpath->registerNamespace(self::$nsPrefix, static::NS);
        return $xpath->evaluate($path, $e);
    }
}
