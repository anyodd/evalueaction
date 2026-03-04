<?php
$lock = json_decode(file_get_contents('composer.lock'), true);
foreach(array_merge($lock['packages'], $lock['packages-dev']) as $p) {
    if (isset($p['require']['php'])) {
        echo $p['name'] . ' requires ' . $p['require']['php'] . PHP_EOL;
    }
}
