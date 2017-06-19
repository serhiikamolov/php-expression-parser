<?php
namespace classes;

use Exception;

abstract class BaseParser
{
    const OPERATOR_PLUS = '+';
    const OPERATOR_MINUS = '-';
    const OPERATOR_MULT = '*';
    const OPERATOR_DIV = '/';

    const BRACKET_OPEN = '(';
    const BRACKET_CLOSE = ')';

    const ADD_RULE = '/[\+\-]/';
    const MULT_RULE = '/[\*\/]/';

    public function isNumber($string):bool
    {
        return preg_match('/^-?((\d+(\.\d*)?)|(\.\d+))$/', $string);
    }


    public function isAdd($string):bool
    {
        return strlen($string) == 1 && preg_match(self::ADD_RULE, $string);
    }

    public function isMult($string):bool
    {
        return strlen($string) == 1 && preg_match(self::MULT_RULE, $string);
    }

    protected function arrayPush (&$array, $mixes)
    {
        if (!empty($mixes) || $mixes === '0') {
            array_push($array, $mixes);
        }
    }

    /**
     * Evalute single operation
     *
     * @param $operand1
     * @param string $operator
     * @param $operand2
     * @return float
     */
    public function evalute($operand1, string $operator, $operand2):float
    {
        switch ($operator) {
            case(static::OPERATOR_PLUS):
                return $operand1 + $operand2;
                break;
            case(static::OPERATOR_MINUS):
                return $operand1 - $operand2;
                break;
            case(static::OPERATOR_MULT):
                return $operand1 * $operand2;
                break;
            case(static::OPERATOR_DIV):
                if ($operand2 == 0) throw new Exception('Division by zero');
                return $operand1 / $operand2;
                break;
        }
        return null;
    }
}