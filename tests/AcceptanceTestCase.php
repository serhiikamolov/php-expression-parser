<?php
namespace tests;

use PHPUnit\Framework\TestCase;

abstract class AcceptanceTestCase extends TestCase
{
    protected function shellCommand($expr)
    {
        $command= "cd ../../ & php parser.php \"$expr\"";
        $output = `$command`;
        return $output;
    }
}