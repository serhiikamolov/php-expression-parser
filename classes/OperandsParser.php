<?php
namespace classes;

use Exception;

abstract class OperandsParser extends BaseParser
{
    /**
     * Combine sub-expressions (expressions within the brackets) as separate operands
     *
     * @param $operands
     * @return array
     * @throws Exception
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
                    if (empty($bracketsEntry)) throw new Exception('Malformed expression');
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

        if ($brackets) throw new Exception('Malformed expression');
        return $this->validateOperands($result);
    }

    /**
     * Validate operands
     * @param $operands
     * @return mixed
     * @throws Exception
     */
    public function validateOperands($operands)
    {
        $operandsNum = 0;
        $operatorsNum = 0;
        if ($operands) {
            foreach ($operands as $operand) {
                if ($this->isAdd($operand) || $this->isMult($operand)) {
                    $operatorsNum++;
                }else{
                    $operandsNum++;
                }
            }
        }
        if ($operatorsNum>$operandsNum) {
            throw new Exception('Malformed expression');
        }
        return $operands;
    }
    /**
     * Do some normalization
     * @param array $operands
     * @return array
     */
    public function normalizeOperands(array $operands):array
    {
        //combine sub-expression (expression within the brackets) as a separate operand
        $operands = $this->combineOperands($operands);

        $this->validateOperands($operands);

        if ($operands[0] == static::OPERATOR_MINUS) {
            $additionalOne = $operands[0]."1";
            array_shift($operands);
            array_unshift($operands, $additionalOne, static::OPERATOR_MULT);
        }

        return $operands;
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
            if ($this->isNumber($literal) || ($literal===static::OPERATOR_MINUS && $operand===null)) {
                $operand .= $literal;
            }else{
                $this->arrayPush($operands, $operand);
                $this->arrayPush($operands, $literal);
                $operand = '';
            }
        }
        $this->arrayPush($operands, $operand);

        return $this->normalizeOperands($operands);
    }
}