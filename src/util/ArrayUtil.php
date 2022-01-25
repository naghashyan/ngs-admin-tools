<?php

namespace ngs\AdminTools\util;



class ArrayUtil
{


    /**
     *
     * returns item from array found by key with specific value
     * 
     * @param array $items
     * @param string $key
     * @param $value
     * @return mixed|null
     */
    public static function findInArray(array $items, string $key, $value){
        if(!is_array($value)) {
            foreach($items as $item) {
                if($item[$key] === $value) {
                    return $item;
                }
            }
        }
        else {
            $result = [];
            foreach($items as $item) {
                if(in_array($item[$key], $value)) {
                    $result[] = $item;
                }
            }

            return $result;
        }

        return null;
    }


    /**
     * returns value from associative array by key matching, used for possible values
     *
     * @param array $list
     * @param string $key
     * @return array
     */
    public static function getByMatchingKey(array $list, string $key) {
        if(isset($list[$key])) {
            return $list[$key];
        }

        $key = str_replace('`', '', $key);
        $keyParts = explode(".", $key);

        if(count($keyParts) > 1 && isset($list[$keyParts[1]]))  {
            return $list[$keyParts[1]];
        }

        return [];
    }
}

