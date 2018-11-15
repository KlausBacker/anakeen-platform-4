<?php

namespace Anakeen\Core\Utils;

class Glob
{
    /**
     * Extend glob function to use ** (double star)
     * @param string $pattern
     * @param int    $flags
     * @return array|false
     */
    public static function glob(string $pattern, $flags = 0)
    {
        if (stripos($pattern, '**') === false) {
            $files = glob($pattern, $flags);
        } else {
            $position = stripos($pattern, '**');
            $rootPattern = substr($pattern, 0, $position - 1);
            $restPattern = substr($pattern, $position + 2);
            $patterns = array($rootPattern . $restPattern);
            $rootPattern .= '/*';
            while ($dirs = glob($rootPattern, GLOB_ONLYDIR)) {
                $rootPattern .= '/*';
                foreach ($dirs as $dir) {
                    $patterns[] = $dir . $restPattern;
                }
            }
            $files = array();
            foreach ($patterns as $pat) {
                $files = array_merge($files, self::glob($pat, $flags));
            }
        }
        $files = array_unique($files);
        usort($files, function ($a, $b) {
            return strcmp(basename($a), basename($b));
        });
        return $files;
    }
}
