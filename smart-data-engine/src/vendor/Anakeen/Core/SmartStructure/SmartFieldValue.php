<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Anakeen\Core\SmartStructure;

use Anakeen\Core\Utils\Date;

class SmartFieldValue
{
    /**
     * return typed value for an document's attribute
     * @param \Anakeen\Core\Internal\SmartElement          $doc
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oAttr
     * @throws SmartFieldValueException
     * @return array|float|int|null|string
     */
    public static function getTypedValue(\Anakeen\Core\Internal\SmartElement &$doc, \Anakeen\Core\SmartStructure\NormalAttribute &$oAttr)
    {
        if (!isset($doc->attributes->attr[$oAttr->id])) {
            throw new SmartFieldValueException('VALUE0101', $oAttr->id, $doc->fromname, $doc->getTitle());
        }
        if ($oAttr->isMultiple()) {
            return self::getMultipleValues($doc, $oAttr);
        }
        if ($oAttr->type == "array") {
            return self::getArrayValues($doc, $oAttr);
        }
        $rawValue = $doc->getRawValue($oAttr->id, null);
        return self::castValue($oAttr->type, $rawValue);
    }

    private static function getMultipleValues(\Anakeen\Core\Internal\SmartElement &$doc, \Anakeen\Core\SmartStructure\NormalAttribute &$oAttr)
    {
        if ($oAttr->isMultipleInArray()) {
            return self::getMultiple2Values($doc, $oAttr);
        }
        $rawValues = $doc->getMultipleRawValues($oAttr->id);
        $type = $oAttr->type;
        $typedValues = array();
        foreach ($rawValues as $rawValue) {
            $typedValues[] = self::castValue($type, $rawValue);
        }

        return $typedValues;
    }

    private static function getMultiple2Values(\Anakeen\Core\Internal\SmartElement &$doc, \Anakeen\Core\SmartStructure\NormalAttribute &$oAttr)
    {
        $rawValues = $doc->getMultipleRawValues($oAttr->id);
        $type = $oAttr->type;
        $typedValues = array();
        foreach ($rawValues as $rawValue) {
            $finalTypedValues = array();
            if ($rawValue) {
                foreach ($rawValue as $finalValue) {
                    $finalTypedValues[] = self::castValue($type, $finalValue);
                }
            }

            // Trim right null values
            $last = null;
            /** @noinspection PhpStatementHasEmptyBodyInspection */
            while (count($finalTypedValues) > 0 && (($last = array_pop($finalTypedValues)) === null)) {
                ;
            }
            if ($last !== null && $last !== false) {
                $finalTypedValues[] = $last;
            }
            $typedValues[] = $finalTypedValues;
        }

        return $typedValues;
    }

    /**
     * cast raw value to type value
     * @param string $type     like text, int, double
     * @param string $rawValue raw database value
     * @return float|int|null|string
     */
    private static function castValue($type, $rawValue)
    {
        if ($rawValue === null || $rawValue === '') {
            return null;
        }
        switch ($type) {
            case 'int':
                $typedValue = intval($rawValue);
                break;

            case 'money':
            case 'double':
                $typedValue = doubleval($rawValue);
                break;

            case 'timestamp':
            case 'date':
                $isoDate = Date::stringDateToIso($rawValue, false, true);
                if (strlen($rawValue) == 16) {
                    $isoDate .= ':00';
                }
                $typedValue = new \DateTime($isoDate);
                break;

            case 'time':
                $typedValue = $rawValue;
                if (strlen($rawValue) == 5) {
                    $typedValue .= ':00';
                }
                break;

            default: // text, htmltext, longtext, enum, file, image,thesaurus,docid,account
                $typedValue = $rawValue;
        }
        return $typedValue;
    }

    private static function typed2string($type, $typedValue)
    {
        if ($typedValue === null || $typedValue === '') {
            return null;
        }

        if (is_array($typedValue)) {
            $arrayString = array();
            foreach ($typedValue as $k => $aSingleValue) {
                $arrayString[$k] = self::singleTyped2string($type, $aSingleValue);
            }
            return $arrayString;
        } else {
            return self::singleTyped2string($type, $typedValue);
        }
    }

    private static function singleTyped2string($type, $typedValue)
    {
        if ($typedValue === null || $typedValue === '') {
            return null;
        }

        switch ($type) {
            case 'int':
                if (!is_string($typedValue) && !is_int($typedValue)) {
                    throw new SmartFieldValueException('VALUE0200', print_r($typedValue, true), gettype($typedValue));
                }
                break;

            case 'money':
            case 'double':
                if (!is_string($typedValue) && !is_int($typedValue) && !is_double($typedValue)) {
                    throw new SmartFieldValueException('VALUE0201', print_r($typedValue, true), gettype($typedValue));
                }
                break;

            case 'timestamp':
                if (is_a($typedValue, "DateTime")) {
                    /**
                     * @var \DateTime $typedValue
                     */
                    $typedValue = $typedValue->format('Y-m-d\TH:i:s');
                }

                break;

            case 'date':
                if (is_a($typedValue, "DateTime")) {
                    /**
                     * @var \DateTime $typedValue
                     */
                    $typedValue = $typedValue->format('Y-m-d');
                }

                break;

            case "docid":
            case "account":
            case "enum":
                if (is_array($typedValue)) {
                    return $typedValue;
                }
                break;

            default: // text, htmltext, longtext, enum, file, image,thesaurus,docid,account
                ;
        }
        if (!is_scalar($typedValue)) {
            throw new SmartFieldValueException('VALUE0202', print_r($typedValue, true), gettype($typedValue));
        }
        return $typedValue;
    }

    private static function getArrayValues(\Anakeen\Core\Internal\SmartElement &$doc, \Anakeen\Core\SmartStructure\NormalAttribute &$oAttr)
    {
        if ($oAttr->type == "array") {
            $ta = $doc->attributes->getArrayElements($oAttr->id);
            $ti = $tv = array();
            $ix = 0;
            // transpose
            foreach ($ta as $k => $v) {
                $tv[$k] = self::getMultipleValues($doc, $doc->getAttribute($k));
                $ix = max($ix, count($tv[$k]));
            }
            for ($i = 0; $i < $ix; $i++) {
                $ti[$i] = array();
            }
            foreach ($ta as $k => $v) {
                for ($i = 0; $i < $ix; $i++) {
                    $ti[$i] += array(
                        $k => isset($tv[$k][$i]) ? $tv[$k][$i] : null
                    );
                }
            }
            return $ti;
        }
        throw new SmartFieldValueException('VALUE0100', $oAttr->id, $doc->title, $doc->fromname);
    }
    private static function transposeArray($array, $oAttr, $doc)
    {
        if (!is_array($array)) {
            return false;
        }

        $columns= $doc->attributes->getArrayElements($oAttr->id);
        $columnIds=[];
        foreach ($columns as $field) {
            $columnIds[]= $field->id;
        }
        foreach ($array as $k => &$v) {
            foreach ($columnIds as $columnId) {
                if (!is_array($v)) {
                    throw new SmartFieldValueException(
                        'VALUE0010',
                        $oAttr->id,
                        $doc->fromname,
                        $doc->getTitle(),
                        print_r($v, true)
                    );
                }
                if (!array_key_exists($columnId, $v)) {
                    $v[$columnId] = null;
                }
            }
        }
        $return = array();
        foreach ($array as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $return[$key2][$key] = $value2;
            }
        }
        return $return;
    }
    private static function setTypedArrayValue(\Anakeen\Core\Internal\SmartElement &$doc, \Anakeen\Core\SmartStructure\NormalAttribute &$oAttr, array $value)
    {
        $doc->clearArrayValues($oAttr->id);
        $err = "";
        foreach ($value as $row) {
            if (!is_array($row)) {
                throw new SmartFieldValueException('VALUE0009', $oAttr->id, $doc->fromname, $doc->getTitle(), print_r($row, true));
            }
        }

        $tabTranspose = self::transposeArray($value, $oAttr, $doc);
        foreach ($tabTranspose as $columnName => $columnValue) {
            $cAttr = $doc->getAttribute($columnName);
            if ($cAttr) {
                $columnValue = self::typed2string($cAttr->type, $columnValue);
            }
            $err .= $doc->setColumnValue($columnName, $columnValue);
        }
        if ($err !== "") {
            throw new SmartFieldValueException('VALUE0007', $oAttr->id, $doc->fromname, $doc->getTitle(), $err);
        }
    }

    /**
     * Set a new value to an attribute document
     * @param \Anakeen\Core\Internal\SmartElement          $doc
     * @param \Anakeen\Core\SmartStructure\NormalAttribute $oAttr
     * @param mixed                                        $value
     * @see \Anakeen\Core\Internal\SmartElement::setAttributeValue()
     * @throws SmartFieldValueException in case of incompatible value
     * @throws SmartFieldAccessException
     */
    public static function setTypedValue(\Anakeen\Core\Internal\SmartElement &$doc, \Anakeen\Core\SmartStructure\NormalAttribute &$oAttr, $value)
    {
        if (!isset($doc->attributes->attr[$oAttr->id])) {
            throw new SmartFieldValueException('VALUE0004', $oAttr->id, $doc->fromname, $doc->getTitle());
        }
        $kindex = -1;
        $err = '';
        if ($value === null) {
            if ($oAttr->type === "array") {
                self::setTypedArrayValue($doc, $oAttr, array());
            } else {
                $err = $doc->clearValue($oAttr->id);
            }
        } elseif ($oAttr->isMultiple()) {
            if (!is_array($value)) {
                $e = new SmartFieldValueException('VALUE0002', print_r($value, true), $oAttr->id, $doc->fromname, $doc->getTitle());
                $e->attributeId = $oAttr->id;
                throw $e;
            }
            if ($value === array()) {
                $err = $doc->clearValue($oAttr->id);
            } else {
                if ($oAttr->isMultipleInArray()) {
                    $rawValues = array();
                    foreach ($value as $k => $rowValues) {
                        if (is_array($rowValues)) {
                            $rawValues[$k] = $rowValues;
                        } else {
                            if ($rowValues === null) {
                                $rawValues[$k] = [];
                            } else {
                                $e = new SmartFieldValueException('VALUE0003', print_r($value, true), $oAttr->id, $doc->fromname, $doc->getTitle());
                                $e->attributeId = $oAttr->id;
                                throw $e;
                            }
                        }
                    }
                    $err = $doc->setValue($oAttr->id, self::typed2string($oAttr->type, $rawValues), -1, $kindex);
                } else {
                    $err = $doc->setValue($oAttr->id, self::typed2string($oAttr->type, $value), -1, $kindex);
                }
            }
        } elseif ($oAttr->type == "array") {
            if (!is_array($value)) {
                $e = new SmartFieldValueException('VALUE0008', $oAttr->id, $doc->fromname, $doc->getTitle(), print_r($value, true));
                $e->attributeId = $oAttr->id;
                throw $e;
            }
            self::setTypedArrayValue($doc, $oAttr, $value);
        } else {
            if (is_array($value)) {
                $e = new SmartFieldValueException('VALUE0006', $oAttr->id, $doc->fromname, $doc->getTitle(), print_r($value, true));
                $e->attributeId = $oAttr->id;
                throw $e;
            }
            try {
                $err = $doc->setValue($oAttr->id, self::typed2string($oAttr->type, $value), -1, $kindex);
            } catch (SmartFieldValueException $e) {
                $e = new SmartFieldValueException('VALUE0005', $oAttr->id, $doc->fromname, $doc->getTitle(), $e->getMessage());
                $e->attributeId = $oAttr->id;
                throw $e;
            }
        }
        if ($err) {
            if (preg_match("/^{(DOC0132|DOC0136)}/", $err)) {
                $e = new SmartFieldAccessException("VALUE0102", $oAttr->id, $err);

                $e->attributeId = $oAttr->id;
                $e->index = $kindex;
            } else {
                $e = new SmartFieldValueException('VALUE0001', $oAttr->id, $doc->fromname, $doc->getTitle(), $err);
                $e->originalError = $err;
                $e->attributeId = $oAttr->id;
                $e->index = $kindex;
            }
            throw $e;
        }
    }
}
