<?php
/*
 * @author Anakeen
 * @package FDL
*/
namespace Dcp\DocumentGrid2;
class ThesaurusCriteria
{
    public static function getThEqual(CriteriaStruct $criteria)
    {
        $thIds = $criteria->value;
        if (!is_array($thIds)) {
            $thIds = array(
                $thIds
            );
        }
        foreach ($thIds as $thId) {
            
            $thc = new_doc("", $thId);
            if (!$thc->isAlive()) {
                throw new ThesaurusCriteriaException(sprintf('thesaurus concept "%s" not found', $thId));
            }
            /** @var \SmartStructure\Thconcept $thc */
            
            $th = new_doc("", $thc->getRawValue(SmartStructure\Attributes\Thconcept::thc_thesaurus));
            if (!$th->isAlive()) {
                throw new ThesaurusCriteriaException(sprintf('thesaurus of concept "%s" not found', $thId));
            }
            
            $field = null;
            $option = ($criteria->multiplicity == "multiple") ? "multiple=yes" : '';
            $oa = new \Anakeen\Core\SmartStructure\NormalAttribute($criteria->id, 1, "label", $criteria->type, "", ($criteria->multiplicity == "multiple") , 1, '', 'W', false, false, false, $field, '', '', '', '', '', '', $option);
            /** @var \SmartStructure\Thesaurus $th */
            $sql[] = $th->getSqlFilter($oa, $thId);
        }
        
        return ('(' . implode(' ) or (', $sql) . ')');
    }
    
    public static function getThNotEqual(CriteriaStruct $criteria)
    {
        return sprintf("not(%s)", self::getThEqual($criteria));
    }
}

class ThesaurusCriteriaException extends \Exception
{
}
