<?php

namespace ngs\NgsAdminTools\util;


use FormulaParser\FormulaParser;

class MathUtil
{


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

