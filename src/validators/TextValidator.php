<?php

namespace ngs\AdminTools\validators;


class TextValidator extends BaseValidator
{

    private $allowedLengths = null;
    private $stringMinLength;
    private $stringMaxLength;
    private $allowedChars;
    private $stringLength;

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

    /**
     * @return mixed
     */
    public function getAllowedLengths()
    {
        return $this->allowedLengths;
    }

    /**
     * @param mixed $allowedLengths
     */
    public function setAllowedLengths($allowedLengths): void
    {
        $this->allowedLengths = $allowedLengths;
    }

    /**
     * @return mixed
     */
    public function getAllowedChars()
    {
        return $this->allowedChars;
    }

    /**
     * @param mixed $allowedChars
     */
    public function setAllowedChars($allowedChars): void
    {
        $this->allowedChars = $allowedChars;
    }


    /**
     * @return mixed
     */
    public function getStringLength()
    {
        return $this->stringLength;
    }

    /**
     * @param mixed $length
     */
    public function setStringLength($length): void
    {
        $this->stringLength = $length;
    }


    /**
     * @param $fieldName
     * @return bool
     */
    protected function validate($fieldName): bool
    {
        $value = $this->getValue();

        if($this->getAllowedLengths() && !in_array(strlen($value), $this->getAllowedLengths())) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . ' </b> symbols count should be ' . implode(" or ", $this->getAllowedLengths()));
            return false;
        }
        if ($this->getStringMinLength() !== null && $this->getStringMinLength() > strlen($value)) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . ' </b> should be not less than ' . $this->getStringMinLength() . ' symbols');
            return false;
        } else if ($this->getStringMaxLength() !== null && $this->getStringMaxLength() < strlen($value)) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> should be not more than ' . $this->getStringMaxLength() . ' symbols');
            return false;
        } else if ($this->getAllowedChars() !== null && !empty($this->getAllowedChars())) {
            $chars = str_split($value);

            foreach ($chars as $char) {
                if (!in_array($char, $this->allowedChars)) {
                    $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> can contain only chars ' . implode(', ', $this->allowedChars));
                    return false;
                }
            }
        } else if ($this->getStringLength() !== null && $this->getStringLength() != strlen($value)) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> should be ' . $this->getStringLength() . ' symbols');
            return false;
        }

        return true;
    }
}
