<?php

namespace ngs\AdminTools\validators;


class ArrayValidator extends BaseValidator
{
    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {
        $value = $this->getValue();
        if(is_array($value)) {
            return true;
        }
        $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> is not array');
        return false;
    }
}

