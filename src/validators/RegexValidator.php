<?php

namespace ngs\AdminTools\validators;


class RegexValidator extends BaseValidator
{
    /**
     * @param $fieldName
     * @return bool
     */

    private $regex = null;
    private $stringMinLength;
    private $stringMaxLength;
    private $allowedChars;


    public function setRegex($regex) {
        $this->regex = $regex;
    }


    protected function validate($fieldName) :bool {
        $value = $this->getValue();
        if(!preg_match($this->regex, $value)) {
            if($this->getCustomTextForError()) {
                $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> ' . $this->getCustomTextForError() );
            }else {
                $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> is not correspond to regular expression ' . $this->regex);
            }
            return false;
        }

        if($this->getStringMinLength() !== null && $this->getStringMinLength() > strlen($value)) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . ' </b>should be more than ' . ($this->getStringMinLength() - 1) . ' symbols');
            return false;
        }
        else if($this->getStringMaxLength() !== null && $this->getStringMaxLength() < strlen($value)) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> should be less than ' . ($this->getStringMaxLength() + 1) . ' symbols');
            return false;
        }

        return true;
    }


    /**
     * @return mixed
     */
    public function getStringMinLength()
    {
        return $this->stringMinLength;
    }

    /**
     * @param mixed $stringMinLength
     */
    public function setStringMinLength($stringMinLength): void
    {
        $this->stringMinLength = $stringMinLength;
    }

    /**
     * @return mixed
     */
    public function getStringMaxLength()
    {
        return $this->stringMaxLength;
    }

    /**
     * @param mixed $stringMaxLength
     */
    public function setStringMaxLength($stringMaxLength): void
    {
        $this->stringMaxLength = $stringMaxLength;
    }


}