<?php

namespace ngs\NgsAdminTools\util;


class StringUtil
{


    /**
     * @param $text
     * @param string $action
     * @return string
     */
    public static function getClassNameFromText($text, $action = "load") :string {
        $textParts = explode(".", $text);
        $fileName = $textParts[count($textParts) - 1];
        $fileName = StringUtil::underlinesToCamelCase($fileName, true) . ucfirst($action);
        $textParts[count($textParts) - 1] = $fileName;
        $text = implode("\\", $textParts);
        return $text;
    }

    /**
     * @param $string
     * @param bool $capitalizeFirstCharacter
     * @param bool $divideWithSpaces
     *
     * @return string|string[]
     */
    public static function underlinesToCamelCase($string, $capitalizeFirstCharacter = true, $divideWithSpaces = false)
    {

        $divider = $divideWithSpaces ? ' ' : '';
        $str = str_replace(' ', $divider, ucwords(str_replace('_', ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }


    /**
     * returns setter function name by field name
     *
     * @param $name
     * @return string
     */
    public static function getSetterByDbName($name) {
        return self::getElementFunctionByName($name, 'set');
    }


    /**
     * returns getter function name by field name
     *
     * @param $name
     * @return string
     */
    public static function getGetterByDbName($name) {
        return self::getElementFunctionByName($name, 'get');
    }


    /**
     * @param $name
     * @param $functionName
     * @return string
     */
    public static function getElementFunctionByName($name, $functionName) {
        return $functionName . preg_replace_callback('/_([a-z])/', function ($m) {
            return strtoupper($m[1]);
        }, ($functionName ? ucfirst($name) : $name));
    }
    
}

