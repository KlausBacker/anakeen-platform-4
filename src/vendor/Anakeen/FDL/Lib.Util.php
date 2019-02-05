<?php


/**
 * change & < and > character to respetiv entity
 * @param string $s string to encode
 * @return string encoded string
 */
function xml_entity_encode($s)
{
    return htmlspecialchars($s, ENT_NOQUOTES);
    return str_replace(array(
        '&',
        "<",
        ">"
    ), array(
        "&amp;",
        "&lt;",
        "&gt;"
    ), $s);
}

