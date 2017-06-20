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
        $this->assertFalse($parser->validate('1+2*3e'));
    }

    public function testEvalute()
    {
        $parser = new Parser();
        $x = rand(1, 100);
        $y = rand(1, 100);
        $this->assertEquals($parser->evalute($x, $parser::OPERATOR_PLUS, $y), $x + $y);
        $this->assertEquals($parser->evalute($x, $parser::OPERATOR_MINUS, $y), $x - $y);
        $this->assertEquals($parser->evalute($x, $parser::OPERATOR_MULT, $y), $x * $y);
        $this->assertEquals($parser->evalute($x, $parser::OPERATOR_DIV, $y), $x / $y);
    }

    /**
     * @expectedException   \Exception
     * @expectedExceptionMessage    Division by zero
     */
    public function testEvaluteDevisionByZero()
    {
        $parser = new Parser();
        $parser->evalute(1, $parser::OPERATOR_DIV, 0);
    }

    public function testExplodeTerms()
    {
        $parser = new Parser();
        $terms = $parser->explodeTerms(['2+6','*','3','+','8']);
        $this->assertEquals($terms, [['2+6','*','3'],'+','8']);
    }

}