<?php
use PHPUnit\Framework\TestCase;
use classes\Parser;

class ParserTest extends TestCase
{

    public function testCanCreateParser()
    {
        $this->assertInstanceOf(Parser::class, new Parser());
    }

    public function testIsNumber()
    {
        $parser = new Parser();
        $this->assertTrue($parser->isNumber('198'));
        $this->assertTrue($parser->isNumber('19.8'));
        $this->assertTrue($parser->isNumber('-18'));
        $this->assertFalse($parser->isNumber('asd'));
        $this->assertFalse($parser->isNumber('18-'));
        $this->assertFalse($parser->isNumber('.'));
        $this->assertFalse($parser->isNumber('+.'));
    }

    public function testValidate()
    {
        $parser = new Parser();
        $this->assertTrue($parser->validate('1+2*3*(7/8)-(45-10)'));
        $this->assertFalse($parser->validate('1+2*3*(7/8)(45-10)'));
    }

    public function testStripBrackets()
    {
        $parser = new Parser();
        $this->assertEquals(
            $parser->StripBrackets('((7/8)-(45-10))'),
            '(7/8)-(45-10)'
        );
        $this->assertEquals(
            $parser->StripBrackets('(2+(2*9)/4)'),
            '2+(2*9)/4'
        );
    }

    public function testParseOperands()
    {
        $parser = new Parser();
        $this->assertEquals(
            $parser->parseOperands($parser::TERMS_RULE, '(45-5/1+10*2)'),
            [
                'operands' => ['45', '5/1', '10*2'],
                'operators' => ['-', '+']
            ]
        );
        $this->assertEquals(
            $parser->parseOperands($parser::FACTORS_RULE, '(45-5/1+10*2)'),
            [
                'operands' => ['45-5', '1+10', '2'],
                'operators' => ['/', '*']
            ]
        );
    }

    public function testEvalute()
    {
        $parser = new Parser();
        $x = rand(1, 100);
        $y = rand(1, 100);
        $this->assertEquals($parser->evalute($x, $y, $parser::OPERATOR_PLUS), $x + $y);
        $this->assertEquals($parser->evalute($x, $y, $parser::OPERATOR_MINUS), $x - $y);
        $this->assertEquals($parser->evalute($x, $y, $parser::OPERATOR_MULT), $x * $y);
        $this->assertEquals($parser->evalute($x, $y, $parser::OPERATOR_DIV), $x / $y);
    }

    /**
     * @expectedException   PHPUnit\Framework\Exception
     * @expectedExceptionMessage    Evaluting error: Division by zero
     */
    public function testEvaluteDevisionByZero()
    {
        $parser = new Parser();
        $parser->evalute(1, 0, $parser::OPERATOR_DIV);
    }

    public function testEvaluteOperands()
    {
        $parser = new Parser();
        $list = [
            'operands' => [5, 3, 5, 6, 2],
            'operators' => ['+','-','*','/']
        ];

        $this->assertEquals(
            $parser->evaluteOperands($list),
            9
        );
    }

}