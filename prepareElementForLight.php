<?php

$beginIgnorePattern = array(
    "light" => "//region full",
    "full" => "//region light"
);

$endIgnorePattern = array(
    "light" => "//endregion full",
    "full" => "//endregion light"
);

$options = getopt("f:", array("modeFull"));

if (!isset($options["f"]) || !is_file($options["f"])) {
    throw new Exception(
        "The -f options is needed and must reference a file ({$options["f"]})",
        1
    );
}

$mode = isset($options["modeFull"]) ? "full" : "light";

$fileContent = file($options["f"]);
$newFileContent = "";

$modeIgnore = false;

foreach ($fileContent as $line) {
    if (!$modeIgnore && strpos($line, $beginIgnorePattern[$mode]) !== false) {
        $modeIgnore = true;
        continue;
    }
    if ($modeIgnore && strpos($line, $endIgnorePattern[$mode]) !== false) {
        $modeIgnore = false;
        continue;
    }
    if ($modeIgnore) {
        continue;
    }
    $newFileContent .= $line;
}

file_put_contents($options["f"], $newFileContent);