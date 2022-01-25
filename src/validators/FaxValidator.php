<?php

namespace ngs\AdminTools\validators;


class FaxValidator extends BaseValidator
{

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {
        $value = $this->getValue();

        if(!preg_match('/^(\+\s*)?(?=([.,\s()-]*\d){8})([\d(][\d.,\s()-]*)([^\d]*\d.*)?$/', $value)) {
            $this->setErrorText('field <b class="f_fieldName"> ' . $fieldName . '</b> is not valid phone number');
            return false;
        }

        return true;
    }
}

