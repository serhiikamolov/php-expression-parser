<?php
namespace tests\acceptance;

use tests\AcceptanceTestCase;

class NegativeTest extends AcceptanceTestCase
{

    private static $exprFail = [
        '5*(6+2)-12/4.2',
        '(1+9*4)/0',
    ];

    private static $exprMalformed = [
        '()',
        '5++2',
        '((22+43)/(24*98)+29)/7)',
        '1(2)3',
        '5**2',
        '5--3+2*89',
        '(2+2)6',
        '--2*9',
        '++2*9'
    ];

    private static $exprInvalid = [
        '(2)(3)',
        '5*2-9^8',
        '((22+43)/(24*98)+29)/7a',
        '((22+43):(24*98)+29)'
    ];

    public function testResultFail()
    {
        $result = null;
        foreach(array_merge(static::$exprFail,static::$exprMalformed, static::$exprInvalid) as $expr) {
            $output = $this->shellCommand($expr);
            $this->assertRegexp("/Error/", $output, 'Expression: '.$expr);
        }

    }

    public function testInvalidExprError()
    {
        $result = null;
        foreach(static::$exprInvalid as $expr) {
            $output = $this->shellCommand($expr);
            $this->assertRegexp("/Invalid expression/", $output, 'Expression: '.$expr);
        }

    }

    public function testMalformedError()
    {
        $result = null;
        foreach(static::$exprMalformed as $expr) {
            $output = $this->shellCommand($expr);
            $this->assertRegexp("/Malformed expression/", $output, 'Expression: '.$expr);
        }

    }

    public function testDivisionByZeroError()
    {
        $result = null;
        $output = $this->shellCommand('1/0');
        $this->assertRegexp("/Division by zero/", $output);
    }

}