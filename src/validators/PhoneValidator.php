<?php

namespace ngs\AdminTools\validators;


class PhoneValidator extends BaseValidator
{

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {
        $value = $this->getValue();

        if(preg_match('/^(\([0-9]{3}\)|[0-9]{3}([\s-])?)[0-9]{3}([\s-])?[0-9]{4}$/', $value)) {
            return true;
        }

        $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> is not valid phone number');
        return false;
    }
}

