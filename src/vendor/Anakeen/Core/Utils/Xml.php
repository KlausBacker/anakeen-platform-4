<?php


namespace Anakeen\Core\Utils;


class Xml
{
    public static function getPrefix(\DOMDocument $dom, $nsPath) {
        $xpath = new \DOMXPath($dom);
        foreach( $xpath->query('namespace::*', $dom->documentElement) as $node ) {
            if ($node->nodeValue === $nsPath) {
                return $node->prefix;
            }
        }
        return null;
    }
}