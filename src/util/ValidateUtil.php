<?php

namespace ngs\AdminTools\util;


use ngs\AdminTools\validators\BaseValidator;

class ValidateUtil
{

    /**
     * group validators fiwth vields info
     * 
     * @param array $fieldsWithValidators
     * @param $requestObject
     * @param array|null $requestData
     * @return array
     */
    public static function prepareValidators(array $fieldsWithValidators, $requestObject, array $requestData = null) {
        $result = [];
        $itemId = null;
        foreach($fieldsWithValidators as $field => $validators) {
            foreach($validators as $validator) {
                if(!isset($result[$validator['class']])) {
                    $result[$validator['class']] = [];
                }
                if($requestData && !isset($requestData[$field] )) {
                    $requestData[$field] = "";
                }

                if(!isset($validator['as'])) {

                    if($requestData !== null) {
                        $value = isset($requestData[$field]) ? $requestData[$field] : null;
                        $itemId = isset($requestData['itemId']) ? $requestData['itemId'] : null;
                    }
                    else {
                        $value = $requestObject->$field;
                    }
                    $validatorInfo = [
                        'value' => $value,
                        'field' => isset($validator['field']) ? $validator['field'] : $field,
                        'data' => isset($validator['data']) ? $validator['data'] : null,
                    ];
                    if(isset($validator['request_type'])) {
                        $validatorInfo['request_type'] = $validator['request_type'];
                    }

                    $additionalParamsForValidator = self::setValidatorOptionalParams($validator);
                    $validatorInfo = array_merge($validatorInfo, $additionalParamsForValidator);

                    $result[$validator['class']][] = $validatorInfo;
                }
                else {
                    if(!isset($result[$validator['class']])) {
                        $result[$validator['class']] = ['params' => [], 'fields' => [], 'additional_params' => []];
                    }
                    $result[$validator['class']]['params'][$validator['as']]['value'] = $requestData ? $requestData[$field] : $requestObject->$field;
                    $result[$validator['class']]['fields'][$field] = $requestData ? $requestData[$field] : $requestObject->$field;
                    $result[$validator['class']]['additional_params'] = self::setValidatorOptionalParams($validator);
                }
            }
        }
        return $result;
    }


    /**
     * returns true if there are errors becide empty error
     *
     * @param array $errors
     * @return array
     */
    public static function getNotEmptyErrors(array $errors) :array
    {
        $otherErrors = [];
        foreach($errors as $error) {
            if(strpos($error['message'], 'is required') === false) {
                $otherErrors[] = $error;
            }
        }

        return $otherErrors;
    }


    /**
     * returns error text by errors
     * 
     * @param array $errors
     * @param array $fieldsMapping
     * @return string
     */
    public static function getErrorTextByErrors(array $errors, array $fieldsMapping) :string
    {
        if(!$errors) {
            return "";
        }

        $errorText = '<br />';
        foreach ($errors as $error) {
            $errorText .= $error['message'] . '<br />';
        }

        foreach ($fieldsMapping as $key => $value) {
            $errorText = str_replace('>' . $key . '<', '>' . $value['display_name'] . '<', $errorText);
        }

        return $errorText;
    }


    /**
     * validates params by validators 
     * 
     * @param $validators
     * @return array
     */
    public static function validateRequestData($validators) {

        $result = [
            'errors' => [],
            'fields' => []
        ];

        foreach($validators as $validatorClass => $values) {
            if(isset($values[0])) {
            	
                foreach($values as $value) {
                    /** @var BaseValidator $validator */
                    $validator = new $validatorClass($value['value']);
                    if(isset($value['data'])) {
                        $validator->setAdditionalData($value['data']);
                    }
                    if(isset($value['range_start'])) {
                        $validator->setRangeStart($value['range_start']);
                    }
                    if(isset($value['range_end'])) {
                        $validator->setRangeEnd($value['range_end']);
                    }
                    if(isset($value['string_min_length'])) {
                        $validator->setStringMinLength($value['string_min_length']);
                    }
                    if(isset($value['string_max_length'])) {
                        $validator->setStringMaxLength($value['string_max_length']);
                    }
                    if(isset($value['string_length'])) {
                        $validator->setStringLength($value['string_length']);
                    }
                    if(isset($value['allowed_chars'])) {
                        $validator->setAllowedChars($value['allowed_chars']);
                    }
                    if(isset($value['regex'])) {
                        $validator->setRegex($value['regex']);
                    }
                    if(isset($value['is_required'])) {
                        $validator->setRequired($value['is_required']);
                    }
                    if(isset($value['custom_text_for_error'])) {
                        $validator->setCustomTextForError($value['custom_text_for_error']);
                    }

                    try {
                        $isValid = $validator->isValid($value['field']);

                        if(!$isValid) {
                            $data = ['field' => $value['field'], 'message' => $validator->getErrorText()];
                            if(isset($value['request_type'])) {
                                $data['request_type'] = $value['request_type'];
                            }
                            $result['errors'][] = $data;
                        }
                        else {
                            $fieldValue = $value['value'];
                            if(is_string($fieldValue)) {
                                $fieldValue = trim($fieldValue);
                            }
                            $result['fields'][$value['field']] = $fieldValue;
                        }
                    }
                    catch(\Exception $exp) {
                        $result['errors'][] = ['field' => $value['field'], 'message' => $exp->getMessage()];
                    }
                }
            }
            else {
                /** @var BaseValidator $validator */
                $validator = new $validatorClass($values['params']);
                if(isset($values['additional_params']['data'])) {
                    $validator->setAdditionalData($values['additional_params']['data']);
                }
                try {
                    $isValid = $validator->isValid(null);
                    if(!$isValid) {

                        if($validator->getErrorFields()) {
                            $errors = $validator->getErrorFields();
                            foreach($errors as $field => $error) {
                                $result['errors'][] = ['field' => $field, 'message' => $error];
                            }
                        }
                        else {
                            $result['errors'][] = ['field' => implode(",", array_keys($values['params'])), 'message' => $validator->getErrorText()];
                        }
                    }
                    else {
                        foreach($values['fields'] as $fieldName => $fieldValue) {
                            $result['fields'][$fieldName] = $fieldValue;
                        }
                    }
                }
                catch(\Exception $exp) {
                    $result['errors'][] = ['field' => implode(",", $values), 'message' => $exp->getMessage()];
                }
            }
        }

        return $result;
    }


    private static function setValidatorOptionalParams($validator) {
        
        $validatorInfo = [];

        if(isset($validator['range_start'])) {
            $validatorInfo['range_start'] = $validator['range_start'];
        }
        if(isset($validator['range_end'])) {
            $validatorInfo['range_end'] = $validator['range_end'];
        }
        if(isset($validator['is_required'])) {
            $validatorInfo['is_required'] = !!$validator['is_required'];
        }
        if(isset($validator['string_min_length'])) {
            $validatorInfo['string_min_length'] = $validator['string_min_length'];
        }
        if(isset($validator['string_max_length'])) {
            $validatorInfo['string_max_length'] = $validator['string_max_length'];
        }
        if(isset($validator['string_length'])) {
            $validatorInfo['string_length'] = $validator['string_length'];
        }
        if(isset($validator['allowed_chars'])) {
            $validatorInfo['allowed_chars'] = $validator['allowed_chars'];
        }
        if(isset($validator['regex'])) {
            $validatorInfo['regex'] = $validator['regex'];
        }
        if(isset($validator['custom_text_for_error'])) {
            $validatorInfo['custom_text_for_error'] = $validator['custom_text_for_error'];
        }
        if(isset($validator['data']) && !empty($validator['data'])) {
            $validatorInfo['data'] = $validator['data'];
        }
        return $validatorInfo;
    }


}

