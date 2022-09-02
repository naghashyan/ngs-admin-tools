<?php

/**
 * NgsApiAction actions.
 *
 *
 * @author Mikael Mkrtchyan
 * @site https://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.AdminTools.actions
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\actions;

use Monolog\Logger;
use ngs\AdminTools\exceptions\api\AuthorizationException;
use ngs\AdminTools\exceptions\api\InvalidActionException;
use ngs\AdminTools\exceptions\api\ValidationException;
use ngs\AdminTools\exceptions\NgsValidationException;
use ngs\AdminTools\util\LoggerFactory;
use ngs\AdminTools\util\ValidateUtil;
use ngs\request\AbstractAction;
use ngs\exceptions\NgsErrorException;
use ngs\util\NgsArgs;


/**
 * abstract class for the api actions, all api actions should extend from this class
 *
 * Class NgsApiAction
 * @package ngs\AdminTools\actions
 */
abstract class NgsApiAction extends AbstractAction
{

    protected ?Logger $logger = null;

    protected string $action = "";
    private array $requestValidators = [];
    private array $responseValidators = [];
    private array $validatedArgs = [];

    /**
     * NgsApiAction constructor.
     * @param array $requestValidators
     * @param array $responseValidators
     * @param string $action
     */
    public function __construct()
    {
        $this->logger = LoggerFactory::getLogger(get_class($this), get_class($this));
    }


    /**
     * will validate request params, call method related with action
     * then vaidate response and return it
     * if something wrong will throw NgsErrorException with detailed message
     *
     *
     * @throws NgsErrorException
     */
    public final function service()
    {
        try {
            $requestArgs = $this->args();
            $requestHeaders = $requestArgs->headers();
            $requestData = (array) $requestArgs;
            $requestHeadersData = (array) $requestHeaders;
            $this->getLogger()->info('Action ' . get_class($this) . ": " . $this->action . ' started', ['request' => $requestData, 'headers' => $requestHeadersData]);
            $args = $this->getValidatedArgs();
            $this->getLogger()->info('Action ' . get_class($this) . ": " . $this->action . ' request data is', $args);
            $actionName = $this->action . 'Action';
            if(!method_exists($this, $actionName)) {
                throw new InvalidActionException('method ' . $actionName . ' not implemented in action class');
            }
            $validateRequestResult = $this->validateRequest($args);
            if($validateRequestResult) {
                throw new AuthorizationException($validateRequestResult);
            }
            $result = $this->$actionName($args);

            $fieldsWithValidators = $this->getValidators('response');
            $validators = ValidateUtil::prepareValidators($fieldsWithValidators, null, $result);
            $validatedResult = ValidateUtil::validateRequestData($validators);
            if($validatedResult['errors']) {
                $this->throwValidationError($validatedResult['errors'], 'response');
            }
            $this->getLogger()->info('Action ' . get_class($this) . ": " . $this->action . ' finished with response', $validatedResult['fields']);
            $this->addParams($validatedResult['fields']);
        } catch (\Throwable $exp) {
            $this->getLogger()->info('Action ' . get_class($this) . ": " . $this->action . ' failed: ' . $exp->getMessage());
            $this->handleError($exp);
        }
    }


    /**
     * handle error
     *
     * @param \Throwable $exception
     * @throws NgsErrorException
     */
    protected function handleError($exception) {
        $message = str_replace('<b class="f_fieldName">', '', $exception->getMessage());
        $message = str_replace('</b>', '', $message);
        throw new NgsErrorException($message, $exception->getCode());
    }


    /**
     * @return mixed|null
     * @throws NgsErrorException
     */
    public function getRequestGroup()
    {
        if (!NGS()->get("REQUEST_GROUP") === null) {
            throw new NgsErrorException("please set in constats REQUEST_GROUP");
        }
        return NGS()->get("REQUEST_GROUP");
    }


    /**
     * @return array
     */
    public function getRequestAllowedGroups()
    {
        $apiGroup = NGS()->getSessionManager()->getUserGroupByName('api');
        if ($apiGroup) {
            return ["allowed" => [$apiGroup->getId()]];
        }
        return [];
    }


    /**
     * validate request function, if something wrong should return text, the exception will be thrown with corresponding message
     *
     * @param $args action arguments
     *
     * @return string
     */
    protected function validateRequest(array $args) {
        return "";
    }


    /**
     * returns logger instance
     *
     * @return Logger|null
     */
    protected function getLogger(): Logger
    {
        return $this->logger;
    }


    /**
     * if some additional validators should be applied while request
     * you can override this function and add those here
     *
     * @return array
     */
    protected function getAditionalRequestValidateors(): array
    {
        return [];
    }


    /**
     * if some additional validators should be applied while respose
     * you can override this function and add those here
     *
     * @return array
     */
    protected function getAditionalResponseValidateors(): array
    {
        return [];
    }


    /**
     * returns validated params
     *
     * @return array
     * @throws ValidationException
     */
    protected function getValidatedArgs(): array
    {
        if ($this->validatedArgs) {
            return $this->validatedArgs;
        }
        $allArgs = $this->args();
        $fieldsWithValidators = $this->getValidators();
        $validators = ValidateUtil::prepareValidators($fieldsWithValidators, $allArgs);
        $validationResult = ValidateUtil::validateRequestData($validators);
        if ($validationResult['errors']) {
            $this->throwValidationError($validationResult['errors']);
        }

        $this->validatedArgs = $validationResult['fields'];

        return $this->validatedArgs;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return array|string
     */
    public function getRequestValidators()
    {
        return $this->requestValidators;
    }

    /**
     * @param array|string $requestValidators
     */
    public function setRequestValidators($requestValidators): void
    {
        $this->requestValidators = $requestValidators;
    }

    /**
     * @return array|string
     */
    public function getResponseValidators()
    {
        return $this->responseValidators;
    }

    /**
     * @param array|string $responseValidators
     */
    public function setResponseValidators($responseValidators): void
    {
        $this->responseValidators = $responseValidators;
    }


    /**
     * throw exception based on validation results
     *
     * @param array $errors
     * @param string $type
     *
     * @throws ValidationException
     */
    private function throwValidationError(array $errors, string $type = 'request') {
        $errorsToShow = [];
        foreach ($errors as $error) {
            $requestType = 'other';
            if(isset($error['request_type'])) {
                $requestType = $error['request_type'];
            }
            if(!isset($errorsToShow[$requestType])) {
                $errorsToShow[$requestType] = [];
            }
            $errorsToShow[$requestType][] = $error['message'];
        }

        $errorText = "";
        foreach($errorsToShow as $errorTypeToShow => $errrosOfType) {
            if($errorText) {
                $errorText .= ', ';
            }
            if($errorTypeToShow === 'other') {
                $errorText .= implode(", ", $errrosOfType);
            }
            else {
                $errorText .= $errorTypeToShow . ': (' . implode(", ", $errrosOfType) . ') ';
            }

        }
        $errorText = trim($errorText);
        throw new ValidationException($type . ' validation failed: ' . $errorText, $errors);
    }

    /**
     * returns additional validators for fields
     *
     * @return array
     */
    private function getValidators(string $type = 'request'): array
    {
        if($type === 'request') {
            $editFields = $this->getRequestValidators();
        }
        else {
            $editFields = $this->getResponseValidators();
        }
        $allArgs = $this->args();
        $headers = $allArgs->headers();
        $result = [];
        foreach ($editFields as $fieldKey => $methodValue) {
            $requestType = null;
            if($type === 'request') {
                $requestType = 'POST';
                if(isset($methodValue['header_data']) && $methodValue['header_data']) {
                    $requestType = 'HEADER';
                    $allArgs->$fieldKey = $headers->$fieldKey;
                }
                if(isset($methodValue['url_data']) && $methodValue['url_data']) {
                    $requestType = 'GET';
                    $allArgs->$fieldKey = NGS()->args()->$fieldKey;
                }
            }

            if (!isset($result[$fieldKey])) {
                $result[$fieldKey] = [];
            }
            if (isset($methodValue['validators']) && $methodValue['validators']) {
                $validators = $methodValue['validators'];
                if($requestType) {
                    foreach($validators as $key => $validator) {
                        $validators[$key]['request_type'] = $requestType;
                    }
                }
                $result[$fieldKey] = $validators;
            }
        }

        if($type === 'request') {
            $additionalValidators = $this->getAditionalRequestValidateors();
        }
        else {
            $additionalValidators = $this->getAditionalResponseValidateors();
        }

        if ($additionalValidators) {
            foreach ($additionalValidators as $field => $validators) {

                if (!isset($result[$field])) {
                    $result[$field] = [];
                }
                $result[$field] = array_merge($result[$field], $validators);
            }
        }

        return $result;
    }
}
