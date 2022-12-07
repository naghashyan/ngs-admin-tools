<?php

namespace ngs\AdminTools\validators;


use ngs\AdminTools\exceptions\NgsValidationException;

abstract class BaseValidator
{
    private $value;

    private $errorText;
    private string $customTextForError = '';
    private array $errorFields = [];

    private bool $required = false;
    private ?array $additionalData = null;

    public function __construct($value) {
        $this->value = $value;
    }


    public final function isValid($fieldName): bool {
        $value = $this->getValue();

        if(is_array($value)) {
            return $this->validate($fieldName);
        }

        if($this->isFieldEmpty($value)) {
            if($this->required) {
                $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> is required');
                return false;
            } else {
                return true;
            }
        }

        return $this->validate($fieldName);
    }


    /**
     *
     * validates $value, if it is valid returns true, otherwise returns false
     *
     * @param $fieldName
     * @return bool
     */
    protected abstract function validate($fieldName) :bool;


    public function setRequired(bool $isRequired) {
        $this->required = $isRequired;
    }

    /**
     * returns value
     *
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }


    /**
     * @return mixed
     */
    public function getErrorText()
    {
        return $this->errorText;
    }

    /**
     * @param mixed $errorText
     */
    public function setErrorText($errorText): void
    {
        $this->errorText = $errorText;
    }

    public function setErrorField(string $fieldName, string $error) {
        $this->errorFields[$fieldName] = $error;
    }

    public function getErrorFields() {
        return $this->errorFields;
    }

    /**
     * @return array|null
     */
    public function getAdditionalData(): ?array
    {
        return $this->additionalData;
    }

    /**
     * @param array|null $additionalData
     */
    public function setAdditionalData(?array $additionalData): void
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return string
     */
    public function getCustomTextForError(): string
    {
        return $this->customTextForError;
    }

    /**
     * @param string $customTextForError
     */
    public function setCustomTextForError(string $customTextForError): void
    {
        $this->customTextForError = $customTextForError;
    }

    private function isFieldEmpty($value):bool {
        if($this instanceof BooleanValidator) {
            if($value !== false && $value !== true) {
                return true;
            }
            return false;
        }
        if($value === '0' || $value === 0) {
            return false;
        }
        if(!$value || trim($value) === '') {
            return true;
        }

        return false;
    }
}

