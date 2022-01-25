<?php

namespace ngs\AdminTools\validators;


class DateValidator extends BaseValidator
{
    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {
        $value = $this->getValue();
        $format = 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $value);
        if($d && $d->format($format) == $value) {
            return true;
        }
        $format = 'Y-m-d H:i:s';
        $d = \DateTime::createFromFormat($format, $value);
        if($d && $d->format($format) == $value) {
            return true;
        }
        $this->setErrorText('field <b class="f_fieldName"> ' . $fieldName . '</b> is not valid date format');
        return false;
    }
}

