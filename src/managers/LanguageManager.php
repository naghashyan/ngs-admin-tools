<?php

/**
 * LanguageManager manager class
 * used to handle functional related with languages
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.managers
 * @version 1.0
 *
 */

namespace ngs\NgsAdminTools\managers;

use ngs\AbstractManager;
use ngs\NgsAdminTools\dal\dto\LanguageDto;
use ngs\NgsAdminTools\dal\mappers\LanguageMapper;

class LanguageManager extends AbstractManager
{

    /**
     * @var LanguageManager instance of class
     */
    private static $instance = null;
    private array $languagesAsArray = [];


    /**
     * Returns an singleton instance of this class
     *
     * @return LanguageManager
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new LanguageManager();
        }
        return self::$instance;
    }


    /**
     * get user all saved filters for given type
     *
     * @param $userId
     * @param $itemType
     *
     * @return LanguageDto[]|null
     *
     * @throws \Exception
     */
    public function getLanguages() {
        $mapper = LanguageMapper::getInstance();
        $languages = $mapper->getLanguages();
        return $languages;
    }


    public function getLanguagesList() {
        if(empty($this->languagesAsArray)) {
            $languagesAsDtos = $this->getLanguages();
            if(!$languagesAsDtos){
                return [];
            }
            foreach ($languagesAsDtos as $dto) {
                $language = ['name' => $dto->getName(), 'code' => $dto->getCode()];
                $this->languagesAsArray[$dto->getId()] = $language;
            }
        }


        return $this->languagesAsArray;
    }


    /**
     * get language by id
     *
     * @param int $languageId
     *
     * @return LanguageDto|null
     *
     */
    public function getLanguageById(int $languageId) {
        try {
            $mapper = LanguageMapper::getInstance();
            $language = $mapper->getLanguageById($languageId);
            return $language;
        }
        catch (\Exception $exp) {
            return null;
        }

    }


    /**
     * returns language by iso 2 code
     *
     * @param string $code
     *
     * @return LanguageDto|null
     */
    public function getLanguageByCode(string $code) {
        try {
            $mapper = LanguageMapper::getInstance();
            $language = $mapper->getLanguageByCode($code);
            return $language;
        }
        catch (\Exception $exp) {
            return null;
        }
    }



}