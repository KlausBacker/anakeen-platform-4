<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Routes\Core\Lib;

use Anakeen\Core\Internal\Format\DateAttributeValue;
use Anakeen\Core\Internal\Format\DocidAttributeValue;
use Anakeen\Core\Internal\Format\ImageAttributeValue;
use Anakeen\Core\Internal\Format\StandardAttributeValue;
use Anakeen\Router\URLUtils;
use Anakeen\Core\Settings;
use Anakeen\Router\Exception;

/**
 * Class DocumentFormatter
 * This class is a facade of \Anakeen\Core\Internal\FormatCollection (had format for REST collection)
 *
 * @package Dcp\HttpApi\V1\Crud
 */
class CollectionDataFormatter
{
    const APIURL = Settings::ApiV2;
    protected static $uselessProperties
        = array(
            "ldapdn",
            "comment",
            "classname"
        );
    /* @var \Anakeen\Core\Internal\FormatCollection $formatCollection */
    protected $formatCollection;
    protected $defaultProperties
        = array(
            "initid",
            "title",
            "revision",
            "status",
            "state",
            "icon",
            "name"
        );
    protected $properties = array();
    protected $generateUrl = null;
    protected $rootPath;

    public function __construct($source)
    {
        if (is_a($source, \Anakeen\Core\Internal\SmartElement::class)) {
            /* if the $source is a doc, we want to render only one document*/
            $this->formatCollection = new \Anakeen\Core\Internal\FormatCollection($source);
            if ($source->mid > 0) {
                // mask already set no need to set default mask
                $this->formatCollection->setVerifyAttributeAccess(false);
            }
        } elseif (is_a($source, "DocumentList")) {
            $this->formatCollection = new \Anakeen\Core\Internal\FormatCollection();
            $this->formatCollection->useCollection($source);
        } elseif (is_a($source, "\Anakeen\Search\Internal\SearchSmartData")) {
            $this->formatCollection = new \Anakeen\Core\Internal\FormatCollection();
            /* @var \Anakeen\Search\Internal\SearchSmartData $source */
            $docList = $source->getDocumentList();
            $this->formatCollection->useCollection($docList);
        } else {
            /* the source is not a handled kind of source */
            throw new Exception("CRUD0500");
        }


        $this->formatCollection->setDecimalSeparator('.');
        $this->formatCollection->mimeTypeIconSize = 20;
        $this->formatCollection->useShowEmptyOption = false;
        $this->formatCollection->setPropDateStyle(DateAttributeValue::isoStyle);

        $this->rootPath = \Anakeen\Core\ContextManager::getParameterValue(\Anakeen\Core\Settings::NsSde, "CORE_URLINDEX") . "/" . static::APIURL;
        $this->rootPath = URLUtils::stripUrlSlahes($this->rootPath);
        /* init the standard generator of url (redirect to the documents collection */
        $this->generateUrl = function ($document) {
            return \Anakeen\Routes\Core\Lib\DocumentUtils::getURI($document, static::APIURL);
        };
        $this->setDefaultHooks();
    }

    /**
     * Add a callable function that generate the document uri propertie
     * The callable take a \Anakeen\Core\Internal\SmartElement $document and return the uri
     *
     * @param $callable
     */
    public function setGenerateURI($callable)
    {
        $this->generateUrl = $callable;
    }

    /**
     * add a set of attributes
     * Attributes that not part of the documents can be added
     *
     * @param array $attributes (array of attribute id)
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $currentAttribute) {
            $this->formatCollection->addAttribute($currentAttribute);
        }
    }

    /**
     * Add properties
     *
     * @param array $properties
     * @param bool  $withDefault (add the standard default list)
     */
    public function setProperties(array $properties, $withDefault = false)
    {
        $this->properties = array();
        foreach ($properties as $currentProperty) {
            $this->addProperty($currentProperty);
        }
        if ($withDefault) {
            $this->properties = array_merge($this->properties, $this->defaultProperties);
        }
    }

    /**
     * Add a property
     *
     * @param string $propertyId
     *
     * @throws Exception
     */
    public function addProperty($propertyId)
    {
        $propertyKeys = \Anakeen\Core\Internal\FormatCollection::getAvailableProperties();
        /* handle the non standard property all : all the usable properties */
        if ($propertyId === "all") {
            foreach ($propertyKeys as $propertyKey) {
                if (!in_array($propertyKey, static::$uselessProperties)) {
                    $this->properties[] = $propertyKey;
                }
            }
        } else {
            if (!in_array($propertyId, $propertyKeys) || in_array($propertyId, static::$uselessProperties)) {
                throw new Exception("CRUD0202", $propertyId);
            }
            $this->properties[] = $propertyId;
        }
    }

    /**
     * Add the default properties
     */
    public function useDefaultProperties()
    {
        $this->properties = $this->defaultProperties;
    }

    /**
     * Format the collection and return the array of result
     *
     * @return array
     */
    public function format()
    {
        sort($this->properties);

        $formatCollection = $this->getFormatCollection();

        foreach ($this->properties as $currentProperty) {
            $this->formatCollection->addProperty($currentProperty);
        }

        return $formatCollection->render();
    }

    protected function documentHook($values, \Anakeen\Core\Internal\SmartElement $document)
    {
        $generateUrl = $this->generateUrl;
        $values["uri"] = $generateUrl($document);
        if (isset($values["properties"]["state"]) && !$values["properties"]["state"]->reference) {
            unset($values["properties"]["state"]);
        }

        if (isset($values["properties"]["icon"])) {
            $this->rewriteImageUrl($values["properties"]["icon"]);
        }
        foreach ($values["properties"] as & $subProp) {
            if (is_array($subProp) && !empty($subProp["icon"])) {
                $this->rewriteImageUrl($subProp["icon"]);
            }
        }
        return $values;
    }

    protected function attributeHook($info, $attribute, $doc)
    {
        if ($attribute === null) {
            return $info;
        }
        /**
         * @var \Anakeen\Core\SmartStructure\NormalAttribute $attribute
         */
        if ($info === null) {
            if ($attribute->isMultiple()) {
                $info = array();
            } else {
                $info = new StandardAttributeValue($attribute, null);
            }
        } elseif ($attribute->type === "docid" || $attribute->type === "account" || $attribute->type === "file"
            || $attribute->type === "image") {
            if (is_array($info)) {
                foreach ($info as & $oneInfo) {
                    if (is_array($oneInfo)) {
                        foreach ($oneInfo as & $subInfo) {
                            if (!empty($subInfo->icon)) {
                                $this->rewriteImageUrl($subInfo->icon);
                            }
                        }
                    } else {
                        /**
                         * @var DocidAttributeValue|ImageAttributeValue $oneInfo
                         */
                        if (!empty($oneInfo->icon)) {
                            $this->rewriteImageUrl($oneInfo->icon);
                        }

                        if ($attribute->type === "image" && !empty($oneInfo->thumbnail)) {
                            $this->rewriteThumbUrl(
                                $oneInfo->thumbnail,
                                $doc->initid,
                                ($doc->locked == -1) ? $doc->revision : -1
                            );
                        }
                        if (($attribute->type === "image" || $attribute->type === "file")
                            && !empty($oneInfo->url)) {
                            $this->rewriteFileUrl(
                                $oneInfo->url,
                                $doc->initid,
                                ($doc->locked == -1) ? $doc->revision : -1
                            );
                        }
                    }
                }
            } else {
                if (!empty($info->icon)) {
                    $this->rewriteImageUrl($info->icon);
                }
                if ($attribute->type === "image" && !empty($info->thumbnail)) {
                    $this->rewriteThumbUrl($info->thumbnail, $doc->initid, ($doc->locked == -1) ? $doc->revision : -1);
                }
                if (($attribute->type === "image" || $attribute->type === "file") && !empty($info->url)) {
                    $this->rewriteFileUrl($info->url, $doc->initid, ($doc->locked == -1) ? $doc->revision : -1);
                }
            }
        }
        return $info;
    }

    protected function rewriteImageUrl(&$imgUrl)
    {
        $pattern = "/resizeimg.php\\?img=(?:CORE%2F)?Images%2F([^&]+)&size=([0-9]+)/";
        if (preg_match($pattern, $imgUrl, $reg)) {
            $imgUrl = sprintf("%simages/assets/sizes/%sx%sc/%s", $this->rootPath, $reg[2], $reg[2], $reg[1]);
        }
        //resizeimg.php?vid=3865333998465762597&size=24
        $pattern = "/resizeimg.php\\?vid=([0-9]+)&size=([0-9]+)/";
        if (preg_match($pattern, $imgUrl, $reg)) {
            $imgUrl = sprintf("%simages/recorded/sizes/%sx%sc/%s.png", $this->rootPath, $reg[2], $reg[2], $reg[1]);
        }
        //file/1383/0/icon/-1/200-0.jpg?inline=yes
        $pattern = "%file/([0-9]+)/[0-9]+/([^/]+)/-1/([^\\?]+)\\?.*&width=([0-9]+)%";
        if (preg_match($pattern, $imgUrl, $reg)) {
            $imgUrl = sprintf(
                "%ssmart-elements/%d/images/%s/-1/sizes/%sx%sc.png",
                $this->rootPath,
                $reg[1],
                $reg[2],
                $reg[4],
                $reg[4]
            );
        }
    }

    protected function rewriteThumbUrl(&$imgUrl, $docid = 0, $revision = -1)
    {
        //http://localhost/tmp32/file/66519/0/en_photo/0/Migaloo-Baleine-04.jpg?cache=no&inline=yes&width=48&size=48&width=48
        //file/66519/0/en_photo/4/faisan4.gif?cache=no&inline=yes&width=48
        $pattern = "%file/(?P<docid>[0-9]+)/([^/]+)/(?P<attrid>[^/]+)/(?P<index>[^/]+)/.*&width=(?P<size>[0-9]+)%";
        if (preg_match($pattern, $imgUrl, $reg)) {
            if ($revision === -1) {
                $imgUrl = sprintf(
                    "%ssmart-elements/%d/images/%s/%s/sizes/%s.png",
                    $this->rootPath,
                    $docid ? $docid : $reg["docid"],
                    $reg["attrid"],
                    $reg["index"],
                    $reg["size"]
                );
            } else {
                $imgUrl = sprintf(
                    "%ssmart-elements/%d/revisions/%d/images/%s/%s/sizes/%s.png",
                    $this->rootPath,
                    $docid ? $docid : $reg["docid"],
                    $revision,
                    $reg["attrid"],
                    $reg["index"],
                    $reg["size"]
                );
            }
        }
    }

    protected function rewriteFileUrl(&$fileUrl, $docid = 0, $revision = -1)
    {
        //file/66519/1461587595/en_photo/5/Agouti-Animals-Photos.JPG?cache=no&inline=yes
        $pattern = "%file/(?P<docid>[0-9]+)/([^/]+)/(?P<attrid>[^/]+)/(?P<index>[^/]+)/(?P<filename>[^/?]+)%";
        if (preg_match($pattern, $fileUrl, $reg)) {
            if ($revision === -1) {
                $fileUrl = sprintf(
                    "%ssmart-elements/%d/files/%s/%s/%s",
                    $this->rootPath,
                    $docid ? $docid : $reg["docid"],
                    $reg["attrid"],
                    $reg["index"],
                    $reg["filename"]
                );
            } else {
                $fileUrl = sprintf(
                    "%ssmart-elements/%d/revisions/%d/files/%s/%s/%s",
                    $this->rootPath,
                    $docid ? $docid : $reg["docid"],
                    $revision,
                    $reg["attrid"],
                    $reg["index"],
                    $reg["filename"]
                );
            }
        }
    }

    /**
     * Return the format collection
     *
     * @return \Anakeen\Core\Internal\FormatCollection
     */
    public function getFormatCollection()
    {
        return $this->formatCollection;
    }

    protected function setDefaultHooks()
    {
        /** Add the initid of the document (used to generate standard uri) **/
        $this->formatCollection->addProperty("initid");
        /** Format uniformly the void multiple values */

        $this->formatCollection->setAttributeRenderHook(function ($info, $attribute, $doc) {
            return $this->attributeHook($info, $attribute, $doc);
        });
        /** Add uri property and suppress state if no state **/
        $this->formatCollection->setDocumentRenderHook(function ($values, \Anakeen\Core\Internal\SmartElement $document) {
            return $this->documentHook($values, $document);
        });
    }
}
