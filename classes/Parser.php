<?php
namespace classes;

use Exception;

class Parser extends OperandsParser
{
    /**
     * Validate the expression string
     *
     * @return bool
     * @throws Exception
     */
    public function validate($expr):bool
    {
        if (preg_match('/[+-]?\d+(\.\d+)/', $expr)) {
            throw new Exception('the float numbers is illegal');
        }
        return (
            preg_match('/^[0-9\+\-*\/\(\)]+$/', $expr) &&
            preg_match('/^((?!\)\().)*$/', $expr)
        ) ? true : false;
    }

    /**
     * Group the terms within the array of operands
     * @param $operands
     * @return array
     */
    public function explodeTerms ($operands)
    {
        $factor = [];
        $result = [];
        foreach ($operands as $operand) {
            if (!$this->isAdd($operand)) {
                $this->arrayPush($factor, $operand);
            }else{
                if (empty($factor)) throw new Exception('Malformed expression');
                $this->arrayPush($result, sizeof($factor) > 1 ? $factor : $factor[0]);
                $this->arrayPush($result,$operand);
                $factor = [];
            }
        }
        $this->arrayPush($result, sizeof($factor) > 1 ? $factor : $factor[0]);
        return $result;
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
            $operands = $this->explodeTerms($operands);
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
                            $mult = '';
                        }
                    }
                } elseif($this->isMult($operand)) {
                    if (!empty($mult)) throw new Exception('Malformed expression'); //when two operators go one by one
                    $mult = $operand;
                }
            }
            if (sizeof($term)>1 && $mult===null) {
                throw new Exception('Malformed expression');//when there's no any operators between operands
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
    private function processNeg($neg):float
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
            throw new Exception('Invalid expression');
        }
    }
}
