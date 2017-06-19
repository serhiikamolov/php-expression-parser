<?php
namespace tests\acceptance;

use tests\AcceptanceTestCase;

class PositiveTest extends AcceptanceTestCase
{

    private static $expPass = [
        '10+2*6',
        '100*2+12',
        '100*(2+12)',
        '5*(6+2)-12/4',
        '(100*(2+12))/14',
        '100*((2+12)/14)',
        '-200+12*((1/8)+1)-19',
        '((22+43)/(24*98)+29)/7',
        '((((12+9*9/8))))',
        '(2+1)*9',
        '(2)+(3)',
        '-(6*9)/8+6'
    ];

    public function testResultPass()
    {
        $result = null;
        foreach(static::$expPass as $expr) {
            eval('$result = ('.$expr.');');
            $output = $this->shellCommand($expr);
            $this->assertRegexp("/Result: {$result}/", $output, 'Expression: '.$expr);
        }

    }
}