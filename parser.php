<?php

define('INPUT_NAME', 'expr');
define('EXCEPTION_INVALID_EXPRESSION', 'Invalid expression');
define('EXCEPTION_INVALID_BRACKETS', 'Invalid number of brackets');

class ExpressionException extends Exception
{
    protected $message = EXCEPTION_INVALID_EXPRESSION;
}

class BracketsException extends Exception
{
    protected $message = EXCEPTION_INVALID_BRACKETS;
}

class Parser
{
    const OPERATOR_PLUS = '+';
    const OPERATOR_MINUS = '-';
    const OPERATOR_MILTI = '*';
    const OPERATOR_DIV = '/';

    const BRACKET_OPEN = '(';
    const BRACKET_CLOSE = ')';

    const TERMS_RULE = '/[\+\-]/';
    const FACTORS_RULE = '/[\*\/]/';

    public $expression = null;

    public function __construct($expr)
    {
        $this->expression = trim($expr);
    }

    private function isNumber($string):bool
    {
        return preg_match('/^[\-0-9\.]+$/', $string);
    }

    /**
     * Validate the expression string
     *
     * @return bool
     * @throws Exception
     */
    private function validate():bool
    {

        if (
            !preg_match('/^[0-9\.\+\-*\/\(\)]+$/', $this->expression) ||
            !preg_match('/^((?!\)\().)*$/', $this->expression)
        ) {
            throw new ExpressionException();
        }

        return true;
    }

    /**
     * Strip brackets from beginning and from end of expression
     * @param $expr
     * @return string
     * @throws Exception
     */
    private function stripBrackets($expr):string
    {
       if (isset($expr[0]) && $expr[0] == self::BRACKET_OPEN) {
            $i = 1;
            $brackets = 1;
            for ($i=1; $i<strlen($expr); $i++) {
                if ($expr[$i] == self::BRACKET_OPEN) $brackets++;
                if ($expr[$i] == self::BRACKET_CLOSE) $brackets--;
                if ($brackets == 0) break;
            }

            if ((strlen($expr))-1 == $i) {
                $expr= substr(substr($expr,0,-1), 1);
            }
       }

        if (empty($expr)) {
               throw new ExpressionException();
        }
        return $expr;
    }

    /**
     * Decomposition of an expression into the operands
     *
     * @param string $rule
     * @param string $expr
     * @return array
     * @throws Exception
     */
    private function parseOperands (string $rule, string $expr): array
    {
        $operands = [];
        $operators = [];
        $operand = '';
        $brackets = 0;
        $bracketsEntry = '';

        // strip brackets from beginning and from end of expression
        $expr = $this->stripBrackets($expr);

        //go through literals of expression and
        //fill a stack of operands
        for ($i = 0; $i<= strlen($expr); $i++){
            $literal = $expr[$i] ?? null;
            if (
                (!isset($literal) || preg_match($rule, $literal)) && $brackets == 0 ){
                array_push($operands, $operand);
                array_push($operators, $literal);
                $operand = '';
            }else{
                if (isset($literal)) {
                    if ($literal === self::BRACKET_OPEN) {
                        $brackets++; // count open brackets to make sure that all of then would be closed
                    } elseif ($literal == self::BRACKET_CLOSE) {
                        if ($brackets == 0) {
                            //close bracket at the begin
                            throw new BracketsException();
                        }
                        $brackets --;

                        if ($brackets == 0){
                            if (empty($bracketsEntry)) {
                                throw new BracketsException();
                            }
                            $operand .= $bracketsEntry;
                            $bracketsEntry = '';
                        }
                    }
                    if ($brackets > 0) {
                        $bracketsEntry .= $literal;
                    } else {
                        $operand .= $literal;
                    }
                }
            }
        }

        if ($brackets) {
            throw new BracketsException();
        }

        return [
            'operands' => $operands,
            'operators' => $operators
        ];
    }

    /**
     * Evalute single operation
     *
     * @param $operand1
     * @param $operand2
     * @param string $operator
     * @return float
     */
    private function evalute($operand1, $operand2, string $operator):float
    {
        switch ($operator) {
            case(static::OPERATOR_PLUS):
                return $operand1+$operand2;
            break;
            case(static::OPERATOR_MINUS):
                return $operand1-$operand2;
                break;
            case(static::OPERATOR_MILTI):
                return $operand1*$operand2;
                break;
            case(static::OPERATOR_DIV):
                return $operand1/$operand2;
                break;
        }
        return null;
    }


    /**
     * Parse an expression
     *
     * @param $expr
     * @return float
     */
    private function processExpr($expr):float
    {
        $terms = $this->parseOperands(self::TERMS_RULE, $expr);
        return $this->processTerms($terms);
    }

    /**
     * Parse Terms and decomposit it into the factors
     * @param $terms
     */
    private function processTerms($terms):float
    {
        $result = 0;

        if (!empty($terms['operands'])) {
            foreach ($terms['operands'] as $key=>$term) {
                //print_r($term." ".preg_match(static::TERMS_RULE, $terms['operands'][$key])."\n");
                //var_dump(sizeof($this->parseOperands(static::TERMS_RULE, $term)['operands']));
                if (preg_match(static::TERMS_RULE, $term)
                    && sizeof($this->parseOperands(static::TERMS_RULE, $term)['operands']) > 1) {
                    $terms['operands'][$key] = $this->processExpr($term);
                }else {
                    $factors = $this->parseOperands(static::FACTORS_RULE, $term);
                    $terms['operands'][$key] = $this->processFactors($factors);
                }
            }
        }

        if ($terms) {
            $result = $terms['operands'][0];
            for ($i=1; $i< sizeof($terms['operands']); $i++) {
                if (!empty($terms['operators'][$i-1])) {
                    $result = $this->evalute($result, $terms['operands'][$i], $terms['operators'][$i-1]);
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
    private function processFactors($factors):float {
        $result = 0;

        if (!empty($factors['operands'])) {
            foreach ($factors['operands'] as $key=>$factor) {
                $factors['operands'][$key] = $this->processNeg($factor);
            }
        }
        if (empty($factors['operators'][0])) {
            return $factors['operands'][0];
        }

        if ($factors) {
            $result = $factors['operands'][0];
            for ($i=1; $i< sizeof($factors['operands']); $i++) {
                if (!empty($factors['operators'][$i-1])) {
                    $result = $this->evalute($result, $factors['operands'][$i], $factors['operators'][$i-1]);
                }
            }
        }

        return $result;
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
    public function process():float
    {
        if ($this->validate()) {
            return $this->processExpr($this->expression);
        }
    }
}

if (!$_GET) {
    $expression = $argv[1] ?? null;
} else {
    $expression = str_replace(' ', '+', $_GET[INPUT_NAME]);
}

try {
    $parser = new Parser($expression);
    $result = $parser->process();
    echo "Result: {$result}\n";
}catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}
