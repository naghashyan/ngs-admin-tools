<?php

namespace ngs\AdminTools\util;


use FormulaParser\FormulaParser;

class MathUtil
{
    /**
     * compare 2 decimal numbers
     *
     * @param $number1
     * @param $number2
     * @return bool
     */
    public static function compareTwoDecimals($number1, $number2) {
        if($number1 === $number2) {
            return true;
        }

        $formatedNumber1 = (float)$number1;
        $formatedNumber2 = (float)$number2;
        $formatedNumber1 = number_format($formatedNumber1, 2, '.', '');
        $formatedNumber2 = number_format($formatedNumber2, 2, '.', '');

        if($formatedNumber1 != $formatedNumber2 || $number1 === null || $number2 === null) {
            return false;
        }

        return true;
    }

    /**
     * returns new value by formula and params
     *
     * @param string $formula
     * @param array $params
     * @return int|float
     *
     * @throws \Exception
     */
    public static function getValueByFormula(string $formula, array $params){
        $filteredParams = self::prepareFormulaAndParams($formula, $params);
        $precision = 2;
        $parser = new FormulaParser($filteredParams['formula'], $precision);
        $parser->setVariables($filteredParams['params']);
        $result = $parser->getResult();
        if($result[0] === 'done') {
            return $result[1];
        }
        throw new \Exception($result[1]);
    }

    /**
     * modifies formula and params as FormulaParser do not supports variables longer then 1 char
     *
     * @param string $formula
     * @param array $params
     *
     * @return array
     *
     * @throws \Exception
     */
    private static function prepareFormulaAndParams(string $formula, array $params)
    {

        $filteredParams = [];
        $currentVariable = 'a';
        foreach($params as $key => $value) {
            if(strpos($formula, $key) !== false) {
                if($currentVariable === 'z') {
                    throw new \Exception('to many variables');
                }

                $filteredParams[$currentVariable] = $value;
                $formula = str_replace($key, $currentVariable, $formula);
                $currentVariable++;
            }
        }

        return ['formula' => $formula, 'params' => $filteredParams];
    }
}

