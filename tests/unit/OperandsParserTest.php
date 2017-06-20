<?php
use PHPUnit\Framework\TestCase;
use classes\Parser;

class OperandsParserTest extends TestCase
{

    public function testCombineOperands()
    {
        $parser = new Parser();
        $operands = $parser->combineOperands(['-','(','2','+','2',')','-','5','*','(','6','+','2',')','-','12','/','4']);
        $this->assertEquals($operands, [
            '-','2+2','-','5', '*', '6+2', '-','12', '/', '4'
        ]);
    }

    public function testNormalizeOperands()
    {
        $parser = new Parser();
        $operands = $parser->normalizeOperands(['-','(','2','+','2',')','-','5','*','(','6','+','2',')','-','12','/','4']);
        $this->assertEquals($operands, [
            '-1','*','2+2','-','5', '*', '6+2', '-','12', '/', '4'
        ]);
    }

    public function testExprToOperands()
    {
        $parser = new Parser();
        $operands = $parser->exprToOperands('5*(6+2)-12/4');
        $this->assertEquals($operands, [
            '5', '*', '6+2', '-','12', '/', '4'
        ]);
    }
}