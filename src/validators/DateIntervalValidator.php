<?php

namespace ngs\AdminTools\validators;


class DateIntervalValidator extends BaseValidator
{
    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {

        //todo: because of we dont have fieldNames here, it is hardcoded, need to change in validation utility, and make this function dynamic working like in js part

        $values = $this->getValue();
        if($values['start_date']['value'] && $values['end_date']['value'] && strtotime($values['start_date']['value']) > strtotime($values['end_date']['value'])) {
            $this->setErrorText('field <b>Start Date</b> is greater than field <b>End Date </b>');
            return false;
        }
        return true;
    }
}

