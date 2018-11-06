<?php

$sb = fopen($argv[1], "r");
$scoreboard = [];
for ($row = fgetcsv($sb); ($row !== null && $row !== false); $row = fgetcsv($sb)) {
        $scoreboard []= $row;
}
fclose($sb);

function e($str) {
        return htmlspecialchars($str);
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style type="text/css">
li.SUCCESS {
        color: green;
}
li.FAILURE {
        color: red;
}
</style>
</head>
<body>
<ul>
<?php
foreach ($scoreboard as $row) {
        $status = $row[0];
        $xml = $row[1];
        $html = sprintf("%s.html", $xml);
        printf("<li class=\"%s\"><a href=\"%s\">%s</a> (<a href=\"%s\">dir</a>)</li>\n", e($status), e($html), e($html), e(dirname($html)));
}
?>
</ul>
</body>
</html>
