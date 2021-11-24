<?php

namespace ngs\NgsAdminTools\validators;


class EmailValidator extends BaseValidator
{
    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {
        $value = $this->getValue();

        if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> is not valid email');
        return false;
    }
}

