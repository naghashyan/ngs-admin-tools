<?php

namespace ngs\AdminTools\validators;

use ngs\AdminTools\managers\AbstractCmsManager;

class UniqueValueValidator extends BaseValidator
{

    private $stringMaxLength;


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


    protected function validate($fieldName) :bool {
        $additionalData = $this->getAdditionalData();
        if(!$additionalData || !isset($additionalData['manager'])) {
            $this->setErrorText('Validator setup is wrong');
            return false;
        }

        if($this->getStringMaxLength() !== null && $this->getStringMaxLength() < strlen($this->getValue())) {
            $this->setErrorText('field <b class="f_fieldName">' . $fieldName . '</b> should be less than ' . ($this->getStringMaxLength() + 1) . ' symbols');
            return false;
        }

        $managerName = $additionalData['manager'];
        /** @var AbstractCmsManager $manager */
        $manager = $managerName::getInstance();
        $itemId = isset($additionalData['item_id']) ? $additionalData['item_id'] : null;
        $companyId = isset($additionalData['company_id']) ? $additionalData['company_id'] : null;
        if(!$this->getValue()) {
            return true;
        }
        $isUnique = $manager->fieldIsUnique($fieldName, $this->getValue(), $itemId, $companyId);
        if(!$isUnique) {
            $this->setErrorText('The value for field <b class="f_fieldName">' . $fieldName . '</b> already exists');
            return false;
        }

        return true;

    }

}

