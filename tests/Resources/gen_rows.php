#!/bin/bash php
<?php

declare(strict_types=1);

/**
 * This file is part of Munsoft CSD.
 *
 * @see     https://4gl.co.za
 *
 * @contact  jaco@4gl.co.za
 */
$arg = $argv[0];

$count = $argv[1] ?? 0;

$filename = $argv[2] ?? 'gen_xml.xml';


$destFile = dirname(__FILE__)."/{$filename}";

$createRecord = function (int $x) {
    return <<<XML
        <user>
            <name>name{$x}</name>
            <age>{$x}</age>
        </user>

        XML;
};
$openTag = "<root>\n";
$closeTag = '</root>';

try {
    $fh = fopen($destFile, 'w+');
    fwrite($fh, $openTag);
    foreach (range(1, $count) as $number) {
        $record = $createRecord($number);
        fwrite($fh, $record);
    }
    fwrite($fh, $closeTag);
} finally {
    fclose($fh);
    echo "Done writing to: {$destFile}\n";
}
