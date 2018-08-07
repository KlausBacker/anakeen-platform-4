<?php

namespace Anakeen\Core\Utils;

class Postgres
{
    /**
     * convert PG array literal to PHP array
     * limit to 2-dimensionnals array
     * @param string $text
     * @throws \Dcp\Db\Exception
     * @return array
     */
    public static function stringToArray(string $text): array
    {
        $isspace = " \t\r\n\v\f";
        $text = trim($text, $isspace);
        if ($text === "" || $text === null || $text === '{}') {
            return array();
        }

        $ct = strlen($text);
        if ($text[0] !== '{' || $text[$ct - 1] !== '}') {
            throw new \Dcp\Db\Exception("DB0200", $text);
        }

        $escape = false;
        $quote = false;
        $endQuote = false;
        $word = '';
        $index = 0;
        $dimLevel = 0;
        $subIndex = 0;
        $level = array();
        $subLevel = array();
        $hasLevel = false;
        $noCharAllowed = false;
        for ($i = 1; $i < $ct; $i++) {
            $c = $text[$i];
            switch ($c) {
                case "\x00":
                    throw new \Dcp\Db\Exception("DB0209", $text);
                    break;

                case '{':
                    if ($escape) {
                        $word .= $c;
                        $escape = false;
                    } else {
                        if (!$quote) {
                            $dimLevel++;
                            if ($dimLevel > 1) {
                                throw new \Dcp\Db\Exception("DB0207", $text);
                            }
                            $hasLevel = true;
                            $word = '';
                            $subIndex = 0;
                        } else {
                            $word .= $c;
                        }
                    }
                    break;

                case '"':
                    if ($escape) {
                        $word .= $c;
                        $escape = false;
                    } else {
                        if ($quote) {
                            // end word
                            $quote = false;
                            $endQuote = true;
                        } else {
                            if (trim($word, $isspace) != "") {
                                throw new \Dcp\Db\Exception("DB0202", $text);
                            }
                            $word = ''; // begin new word
                            $quote = true;
                        }
                    }
                    break;

                case '}':
                case ',':
                    if ($escape) {
                        $word .= $c;
                        $escape = false;
                    } else {
                        if (!$quote) {
                            $noCharAllowed = false;

                            $wordHasBegin = ($word !== '' || $endQuote);

                            if (!$endQuote) {
                                $word = trim($word, $isspace);
                            }
                            if (($word === "NULL" || $word === "null") && !$endQuote) {
                                $word = null;
                            }
                            $endQuote = false;
                            if (!$hasLevel) {
                                if (!$wordHasBegin) {
                                    throw new \Dcp\Db\Exception("DB0208", $text);
                                }
                                $level[$index++] = $word;
                            } else {
                                if ($dimLevel === 1) {
                                    if ($wordHasBegin) {
                                        $subLevel[$subIndex++] = $word;
                                    }
                                } elseif ($word === null) {
                                    $level[$index++] = null;
                                } else {
                                    if ($word) {
                                        throw new \Dcp\Db\Exception("DB0206", $text);
                                    }
                                }
                            }
                            $word = '';
                            if ($c === '}') {
                                if ($dimLevel === 1) {
                                    $level[$index++] = $subLevel;
                                    $subLevel = array();
                                }
                                $dimLevel--;
                                $noCharAllowed = true;
                            }
                        } else {
                            $word .= $c;
                        }
                    }
                    break;

                case '\\':
                    if ($escape) {
                        $word .= $c;
                        $escape = false;
                    } else {
                        $escape = true;
                    }
                    break;

                case ' ':
                case "\t":
                case "\n":
                case "\r":
                case "\v":
                case "\f":
                    if ($escape) {
                        $word .= $c;
                        $escape = false;
                    } else {
                        if (!$endQuote) {
                            $word .= $c;
                        }
                    }
                    break;

                default:
                    if ($escape) {
                        $word .= $c;
                        $escape = false;
                    }
                    if ($endQuote) {
                        throw new \Dcp\Db\Exception("DB0201", $text);
                    }
                    if ($noCharAllowed) {
                        throw new \Dcp\Db\Exception("DB0205", $text);
                    }

                    $word .= $c;
            }
        }
        if ($quote) {
            throw new \Dcp\Db\Exception("DB0203", $text);
        }
        if ($dimLevel >= 0) {
            throw new \Dcp\Db\Exception("DB0204", $text);
        }
        return $level;
    }

    /**
     * Convert array to Potsgresql literal array
     * @param array $values
     * @return string
     */
    public static function arrayToString(array $values): string
    {
        if (empty($values)) {
            return "null";
        }
        $fill = -1;
        if (is_array(current($values))) {
            // need to pad for postgresql multi-dimensionnals arrays
            foreach ($values as & $value) {
                $fill = max(count($value), $fill);
            }
        }
        foreach ($values as & $value) {
            if ($value === '' || $value === null || $value === false) {
                $value = 'NULL';
            } elseif (is_array($value)) {
                if ($fill > 0) {
                    $value = array_pad($value, $fill, null);
                }
                $value = self::arrayToString($value);
            } elseif (preg_match('/[,|"|\s|\\\\|{|}]/u', $value) || $value === "NULL") {
                /* Escape backslashs (escape char)... */
                $value = str_replace('\\', '\\\\', $value);
                /* ... then escape dquotes and encapsulates between dquotes */
                $value = '"' . str_replace('"', '\\"', $value) . '"';
            }
        }

        return '{' . implode($values, ',') . '}';
    }

    /**
     * Return only one dimension array for pg array
     * {{12,134},{16,87}} => [12,134,16,87]
     * {56,32,87} => [56,32,87]
     * @param string $text
     * @return array
     * @throws \Dcp\Db\Exception
     */
    public static function stringToFlatArray(string $text): array
    {
        if (!$text) {
            return [];
        }
        $result = self::stringToArray($text);
        $flat = [];
        foreach ($result as $value) {
            if (is_array($value)) {
                $flat = array_merge($flat, $value);
            } else {
                $flat[] = $value;
            }
        }
        return $flat;
    }
}
