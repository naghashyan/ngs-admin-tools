<?php

namespace ngs\NgsAdminTools\validators;


class NumberValidator extends BaseValidator
{

    private $rangeStart;
    private $rangeEnd;


    /**
     * @param $rangeStart
     */
    public function setRangeStart($rangeStart) {
        $this->rangeStart = $rangeStart;
    }


    /**
     * @return mixed
     */
    public function getRangeStart() {
        return $this->rangeStart;
    }


    /**
     * @param $rangeEnd
     */
    public function setRangeEnd($rangeEnd) {
        $this->rangeEnd = $rangeEnd;
    }

    /**
     * @return mixed
     */
    public function getRangeEnd() {
        return $this->rangeEnd;
    }

    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName) :bool {
        $value = $this->getValue();

        if(!is_numeric($value)) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> should be numeric');
            return false;
        }

        if(($this->getRangeStart() !== null && $value < $this->getRangeStart())) {
            $this->setErrorText('field <b class="f_fieldName"> ' . $fieldName . '</b> should be not less than ' . ($this->getRangeStart()));
            return false;
        }

        if($this->getRangeEnd() !== null && $value > $this->getRangeEnd()) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> should be not more than ' . ($this->getRangeEnd()));
            return false;
        }

        return true;
    }
}

