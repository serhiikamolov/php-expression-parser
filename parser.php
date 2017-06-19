<?php

define('INPUT_NAME', 'expr');

require 'vendor/autoload.php';

if (!$_GET) {
    $expression = $argv[1] ?? null;
} else {
    $expression = str_replace(' ', '+', $_GET[INPUT_NAME]);
}

try {
    $parser = new \classes\Parser();
    $result = $parser->process($expression);
    echo "Result: {$result}\n";
}catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}