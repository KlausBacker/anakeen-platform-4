<?php


class EnumAttributeTools
{
    /**
     * convert enum flat notation to an array of item (key/label).
     * @param string $phpfunc the flat notation
     * @param array $theEnum [out] the enum array converted
     * @param array $theEnumlabel [out] the enum array converted - with complete labels in case of levels
     * @param string $locale the prefix key for locale values (if empty no locale are set)
     * @return array
     */
    public static function flatEnumNotationToEnumArray($phpfunc, array & $theEnum, array & $theEnumlabel = array(), $locale = '')
    {
        if (!$phpfunc) {
            return array();
        }
        if (preg_match('/^\[[a-z]*\](.*)/', $phpfunc, $reg)) {
            //delete old enum format syntax
            $phpfunc = $reg[1];
        }
        // set the enum array
        $theEnum = array();
        $theEnumlabel = array();
        
        $sphpfunc = str_replace("\\.", "-dot-", $phpfunc); // to replace dot & comma separators
        $sphpfunc = str_replace("\\,", "-comma-", $sphpfunc);
        if ($sphpfunc == "-") {
            $sphpfunc = "";
        } // it is recorded
        if ($sphpfunc != "") {
            $tenum = explode(",", $sphpfunc);
            foreach ($tenum as $k => $v) {
                list($enumKey, $enumValue) = explode("|", $v, 2);
                $treeKeys = explode(".", $enumKey);
                $enumKey = trim($enumKey);
                if (strlen($enumKey) == 0) {
                    $enumKey = " ";
                }
                $enumValue = trim($enumValue);
                
                $n = count($treeKeys);
                if ($n <= 1) {
                    $enumValue = str_replace(array(
                        '-dot-',
                        '-comma-'
                    ), array(
                        '\.',
                        ','
                    ), $enumValue);
                    
                    if ($locale) {
                        $translatedEnumValue = _($locale . $enumKey);
                        if ($translatedEnumValue != $locale . $enumKey) {
                            $enumValue = $translatedEnumValue;
                        }
                    }
                    
                    $theEnum[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ), array(
                        '\.',
                        ','
                    ), $enumKey) ] = $enumValue;
                    $theEnumlabel[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ), array(
                        '.',
                        ','
                    ), $enumKey) ] = $enumValue;
                } else {
                    $enumlabelKey = '';
                    $tmpKey = '';
                    $previousKey = '';
                    foreach ($treeKeys as $i => $treeKey) {
                        $enumlabelKey = $treeKey;
                        
                        if ($i < $n - 1) {
                            if ($i > 0) {
                                $tmpKey.= '.';
                            }
                            $tmpKey.= $treeKey;
                        }
                    }
                    $tmpKey = str_replace(array(
                        '-dot-',
                        '-comma-'
                    ), array(
                        '\.',
                        ','
                    ), $tmpKey);
                    
                    if ($locale) {
                        $translatedEnumValue = _($locale . $enumlabelKey);
                        if ($translatedEnumValue != $locale . $enumlabelKey) {
                            $enumValue = $translatedEnumValue;
                        }
                    }
                    $enumlabelValue = $theEnum[$tmpKey] . '/' . $enumValue;
                    $enumlabelValue = str_replace(array(
                        '-dot-',
                        '-comma-'
                    ), array(
                        '\.',
                        ','
                    ), $enumlabelValue);
                    $theEnum[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ), array(
                        '\.',
                        ','
                    ), $enumKey) ] = $enumValue;
                    $theEnumlabel[str_replace(array(
                        '-dot-',
                        '-comma-'
                    ), array(
                        '.',
                        ','
                    ), $enumlabelKey) ] = $enumlabelValue;
                }
            }
        }
        
        return $theEnum;
    }
    
    private static function getEnumHierarchy($key, $parents)
    {
        if (isset($parents[$key])) {
            return array_merge(self::getEnumHierarchy($parents[$key], $parents), array(
                $key
            ));
        } else {
            return array(
                $key
            );
        }
    }
    /**
     * return flat notation from docenum database table
     * @param int $famid family identifier
     * @param string $attrid attribute identifier
     * @return string ftat enum
     */
    public static function getFlatEnumNotation($famid, $attrid)
    {
        $sql = sprintf("select * from docenum where famid='%s' and attrid='%s' and (disabled is null or not disabled) order by eorder", pg_escape_string($famid), pg_escape_string($attrid));
        simpleQuery('', $sql, $results);
        $tItems = array();
        $hierarchy = array();
        foreach ($results as $item) {
            if ($item["parentkey"] !== null) {
                $hierarchy[$item["key"]] = $item["parentkey"];
            }
        }
        foreach ($results as $item) {
            $key = $item["key"];
            $label = $item["label"];
            if ($item["parentkey"] !== null) {
                $parents = self::getEnumHierarchy($key, $hierarchy);
                foreach ($parents as & $pKey) {
                    $pKey = str_replace(".", '-dot-', $pKey);
                }
                $key = implode('.', $parents);
                $key = str_replace('-dot-', '\\.', $key);
            } else {
                $key = str_replace('.', '\\.', $key);
            }
            $tItems[] = sprintf("%s|%s", str_replace(',', '\\,', $key), str_replace(',', '\\,', $label));
        }
        return implode(",", $tItems);
    }
}
