<?php

namespace ngs\NgsAdminTools\validators;


class BooleanValidator extends BaseValidator
{
    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {
        $value = $this->getValue();
        if($value === true || $value === false) {
            return true;
        }
        $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> is not boolean');
        return false;
    }
}

