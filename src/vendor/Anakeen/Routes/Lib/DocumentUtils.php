<?php

namespace Anakeen\Routes\Core;

use Anakeen\Router\Exception;
use Dcp\Core\Settings;
use Anakeen\Router\URLUtils;

class DocumentUtils
{


    /**
     * Analyze the list of required attributes
     *
     * @param \Doc   $currentDoc
     * @param string $prefix
     * @param array  $fields
     *
     * @return array
     * @throws Exception
     */
    public static function getAttributesFields($currentDoc = null, $prefix = "document.attributes.", $fields = array())
    {
        $falseAttribute = array();
        // Compute the list of the attributes that should be displayed (if list is empty all will be displayed)
        $restrictedAttributes = array_filter($fields, function ($currentField) use ($prefix) {
            return mb_stripos($currentField, $prefix) === 0 && $currentField !== $prefix;
        });
        $restrictedAttributes = array_unique($restrictedAttributes);
        if (count($restrictedAttributes) === 1 && current($restrictedAttributes) === "document.attributes.all") {
            $restrictedAttributes = [];
        }

        // end compute list
        // Analyze if all the restricted attributes as a part of the current doc or the current fam
        if ($currentDoc) {
            $restrictedAttributes = array_map(function ($currentField) use ($prefix, &$currentDoc, &$falseAttribute) {
                $attributeId = str_replace($prefix, "", $currentField);
                /* @var \Doc $currentDoc */
                self::isAttribute($currentDoc, $attributeId);

                return $attributeId;
            }, $restrictedAttributes);
        }

        // if there is attributes that not valid throw exception
        if (!empty($falseAttribute)) {
            throw new Exception("CRUD0218", join(" and attribute ", $falseAttribute));
        }
        $attributes = array();
        // compute the list
        if ($currentDoc) {
            // get all attributes without the restricted and I and array (if we have a ref doc)
            $normalAttributes = $currentDoc->getNormalAttributes();
            foreach ($normalAttributes as $attrId => $attribute) {
                if ($attribute->type != "array" && $attribute->mvisibility !== "I") {
                    if (!empty($restrictedAttributes) && !in_array($attrId, $restrictedAttributes)) {
                        continue;
                    }
                    $attributes[] = $attrId;
                }
            }
        } else {
            // if we don't have a ref doc just return the asked attributes list
            $attributes = array_map(function ($currentField) use ($prefix, &$currentDoc, &$falseAttribute) {
                return str_replace($prefix, "", $currentField);
            }, $restrictedAttributes);
        }
        return $attributes;
    }

    /**
     * Analyze the order by
     *
     * @param      $orderBy
     * @param \Doc $currentDoc
     *
     * @return string
     * @throws Exception
     */
    public static function extractOrderBy($orderBy, \Doc $currentDoc = null)
    {
        // Explode the string orderBy in an array
        $orderElements = explode(",", $orderBy);
        $result = array();
        $hasId = false;
        // Check for earch element if the property or attributes exist and the order to
        $propertiesList = array_keys(\Doc::$infofields);
        foreach ($orderElements as $currentElement) {
            $detectOrder = explode(":", $currentElement);

            $orderBy = $detectOrder[0];
            $orderDirection = isset($detectOrder[1]) ? mb_strtolower($detectOrder[1]) : "asc";
            if ($orderDirection !== "asc" && $orderDirection !== "desc") {
                throw new Exception("CRUD0501", $orderDirection);
            }
            if (!in_array($orderBy, $propertiesList) && !self::isAttribute($currentDoc, $orderBy)) {
                throw new Exception("CRUD0506", $orderBy);
            }
            if ($orderBy === "id") {
                $hasId = true;
            }
            $result[] = sprintf("%s %s", pg_escape_string($orderBy), $orderDirection);
        }
        // if the id is not asked add it (for avoid double result in slice)
        if (!$hasId) {
            $result[] = sprintf("id desc");
        }
        return implode(", ", $result);
    }

    /**
     * Check if an attrid is an attribute of the currentDoc
     *
     * @param \Doc $currentDoc
     * @param      $currentElement
     *
     * @return bool
     * @throws Exception
     */
    protected static function isAttribute(\Doc $currentDoc, $currentElement)
    {
        if ($currentDoc) {
            $currentAttribute = $currentDoc->getAttribute($currentElement);
            if ($currentAttribute === false || $currentAttribute->type === "frame"
                || $currentAttribute->type === "array"
                || $currentAttribute->type === "tab"
                || $currentAttribute->type === "menu"
                || $currentAttribute->usefor === "Q"
                || $currentAttribute->mvisibility === "I") {
                if ($currentAttribute) {
                    /**
                     * @var \BasicAttribute $currentAttribute
                     */
                    if ($currentAttribute->mvisibility === "I") {
                        throw new Exception("CRUD0508", $currentElement, $currentAttribute->getLabel());
                    }
                    throw new Exception(
                        "CRUD0507",
                        $currentElement,
                        $currentAttribute->getLabel(),
                        $currentAttribute->type
                    );
                } else {
                    throw new Exception("CRUD0502", $currentElement);
                }
            }
        }
        return true;
    }


    public static function getURI($document, $prefix = Settings::ApiV2)
    {
        if ($document) {
            if ($document->defDoctype === "C") {
                return URLUtils::generateURL(sprintf("%s/families/%s.json", $prefix, $document->name));
            } else {
                if ($document->doctype === "Z") {
                    return URLUtils::generateURL(sprintf("%s/trash/%s.json", $prefix, $document->initid));
                } else {
                    if ($document->locked == -1) {
                        return URLUtils::generateURL(
                            sprintf(
                                "%s/documents/%s/revisions/%d.json",
                                $prefix,
                                $document->initid,
                                $document->revision
                            )
                        );
                    } else {
                        return URLUtils::generateURL(
                            sprintf(
                                "%s/documents/%s.json",
                                $prefix,
                                $document->initid
                            )
                        );
                    }
                }
            }
        }
        return "";
    }

    public static function verifyFamily($famName, \Doc $document)
    {
        $family = \Dcp\Core\DocManager::getFamily($famName);
        if (!$family) {
            $exception = new Exception("ROUTES0105", $famName);
            $exception->setHttpStatus("404", "Family not found");
            throw $exception;
        }
        if ($family && !is_a($document, sprintf("\\Dcp\\Family\\%s", $family->name))) {
            $exception = new Exception("ROUTES0104", $document->initid, $family->name);
            $exception->setHttpStatus("404", "Document is not a document of the family " . $family->name);
            throw $exception;
        }
    }
}
