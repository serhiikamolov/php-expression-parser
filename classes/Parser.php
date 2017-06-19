<?php
namespace classes;

use PHPUnit\Framework\Exception;

define('INPUT_NAME', 'expr');
define('EXCEPTION_INVALID_EXPRESSION', 'Invalid expression');
define('EXCEPTION_INVALID_BRACKETS', 'Invalid number of brackets');

class ExpressionException extends \Exception
{
    protected $message = EXCEPTION_INVALID_EXPRESSION;
}

class BracketsException extends \Exception
{
    protected $message = EXCEPTION_INVALID_BRACKETS;
}

class Parser
{
    const OPERATOR_PLUS = '+';
    const OPERATOR_MINUS = '-';
    const OPERATOR_MULT = '*';
    const OPERATOR_DIV = '/';

    const BRACKET_OPEN = '(';
    const BRACKET_CLOSE = ')';

    const ADD_RULE = '/[\+\-]/';
    const MULT_RULE = '/[\*\/]/';

    public function __construct()
    {}

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

    /**
     * Validate the expression string
     *
     * @return bool
     * @throws Exception
     */
    public function validate($expr):bool
    {
        return (
            preg_match('/^[0-9\.\+\-*\/\(\)]+$/', $expr) &&
            preg_match('/^((?!\)\().)*$/', $expr)
        ) ? true : false;
    }


    private function arrayPush (&$array, $mixes)
    {
        if (!empty($mixes)) {
            array_push($array, $mixes);
        }
    }

    /**
     * Combine sub-expressions (expressions within the brackets) as separate operands
     *
     * @param $operands
     * @return array
     * @throws BracketsException
     */
    public function combineOperands(array $operands):array
    {
        $brackets = 0;
        $result = [];
        $bracketsEntry = '';
        for ($i=0; $i<sizeof($operands); $i++) {
            if ($operands[$i] == static::BRACKET_OPEN) {
                $brackets ++;
                if ($brackets==1) continue;
            }elseif($operands[$i] == static::BRACKET_CLOSE){
                $brackets --;
                if ($brackets == 0){
                    if (empty($bracketsEntry)) throw new BracketsException();
                    $this->arrayPush($result, $bracketsEntry);
                    $bracketsEntry = '';
                    continue;
                }
            }elseif(!$brackets){
                $this->arrayPush($result, $operands[$i]);
                continue;
            }
            $bracketsEntry .= $operands[$i];
        }
        return $result;
    }

    /**
     * Convert string of expression to the array of operands
     * @param string $expr
     * @return array
     */
    public function exprToOperands(string $expr):array
    {
        $expr = str_split($expr);
        $operands = [];
        $operand = null;

        foreach ($expr as $literal) {
            if ($this->isNumber($literal) || ($this->isAdd($literal) && $operand===null)) {
                $operand .= $literal;
            }else{
                $this->arrayPush($operands, $operand);
                $this->arrayPush($operands, $literal);
                $operand = '';
            }
        }
        $this->arrayPush($operands, $operand);

        //combine sub-expression (expression within the brackets) as a separate operand
        return $this->combineOperands($operands);
    }

    /**
     * Group the factors within the array of operands
     * @param $operands
     * @return array
     */
    public function explodeFactors ($operands)
    {
        $factor = [];
        $result = [];
        foreach ($operands as $operand) {
            if (!$this->isAdd($operand)) {
                $this->arrayPush($factor, $operand);
            }else{
                $this->arrayPush($result, sizeof($factor) > 1 ? $factor : $factor[0]);
                $this->arrayPush($result,$operand);
                $factor = [];
            }
        }
        $this->arrayPush($result, sizeof($factor) > 1 ? $factor : $factor[0]);
        return $result;
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
        try {
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
                    return $operand1 / $operand2;
                    break;
            }
        }catch(\Exception $e){
            throw new Exception('Evaluting error: '.$e->getMessage());
        }
        return null;
    }

    /**
     * Parse an expression
     *
     * @param $expr
     * @return float
     */
    private function processExpr(string $expr):float
    {
        $operands = $this->exprToOperands($expr, static::ADD_RULE);
        return $this->processTerms($operands);
    }

    /**
     * Parse Terms and decomposit it into the factors
     * @param $terms
     */
    private function processTerms(array $operands):float
    {
        $result = null;
        $add = null;
        if ($operands) {
            $operands = $this->explodeFactors($operands);
            foreach ($operands as $operand) {
                if (is_array($operand) || !$this->isAdd($operand)) {
                    $term = $this->processFactors($operand);
                    if ($result === null) {
                        $result = $term ;
                    }else{
                        if (!empty($add)) {
                            $result = $this->evalute($result, $add, $term);
                        }
                    }
                } elseif($this->isAdd($operand)) {
                    $add = $operand;
                }
            }
        }

        return $result;
    }

    /**
     * Parse factors
     * @param $factors
     * @return float
     */
    private function processFactors($term):float {
        if (is_array($term)) {
            $result = null;
            $mult = null;
            foreach ($term as $operand) {
                if (!$this->isMult($operand)) {
                    $factor = $this->processNeg($operand);
                    if ($result === null) {
                        $result = $factor ;
                    }else{
                        if (!empty($mult)) {
                            $result = $this->evalute($result, $mult, $factor);
                        }
                    }
                } elseif($this->isMult($operand)) {
                    $mult = $operand;
                }
            }
            return $result;
        } else {
            return $this->processNeg($term);
        }
    }

    /**
     * Parse numbers
     * @param $neg
     * @return float
     */
    public function processNeg($neg):float
    {
        if ($this->isNumber($neg)) {
            return (float)$neg;
        } else{
            return ($this->processExpr($neg));
        }
    }

    /**
     * Process an expression
     * @return float
     */
    public function process(string $expr):float
    {
        if ($this->validate($expr)) {
            return $this->processExpr(trim($expr));
        } else {
            throw new Exception(EXCEPTION_INVALID_EXPRESSION);
        }
    }
}
