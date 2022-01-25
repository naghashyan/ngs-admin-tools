<?php

/**
 * TranslationManager manager class
 * used to handle functional related with translations
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.AdminTools.managers
 * @version 1.0
 *
 */

namespace ngs\AdminTools\managers;

use ngs\AbstractManager;
use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\AdminTools\dal\mappers\TranslationMapper;

class TranslationManager extends AbstractManager
{

    /**
     * @var TranslationManager instance of class
     */
    private static $instance = null;
    private $translationDto = null;


    /**
     * Returns an singleton instance of this class
     *
     * @return TranslationManager
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new TranslationManager();
        }
        return self::$instance;
    }


    /**
     * save dto translations for all languages
     *
     * @param AbstractCmsDto $dto
     * @param array $translations
     * @return bool
     */
    public function saveDtoTranslations(AbstractCmsDto $dto, array $translations) {
        $translatableFields = $dto->getTranslatableFields();

        if(!$translatableFields) {
            return false;
        }

        $translationsSaved = true;
        foreach($translations as $languageId => $translation) {
            $result = $this->saveDtoTranslation($dto, $languageId, $translation);
            if(!$result) {
                $translationsSaved = false;
            }
        }

        return $translationsSaved;
    }


    /**
     *
     * create dto translation for given language
     *
     * @param AbstractCmsDto $dto
     * @param int $languageId
     * @param array $translation
     * @return bool
     */
    public function saveDtoTranslation(AbstractCmsDto $dto, int $languageId, array $translation) {
        $translatableFields = $dto->getTranslatableFields();

        if(!$translatableFields) {
            return false;
        }

        $languageManager = LanguageManager::getInstance();
        $languageDto = $languageManager->getLanguageById($languageId);
        if(!$languageDto) {
            return false;
        }

        $translationToSave = [];

        foreach($translatableFields as $field) {
            if(isset($translation[$field])) {
                $translationToSave[$field] = $translation[$field];
            }
        }
        $mapper = TranslationMapper::getInstance();

        try {
            $translationDto = $mapper->getTranslation($dto->getTableName(), $dto->getId(), $languageId);
            if($translationDto) {
                $oldTranslations = $translationDto->getTranslation();
                $oldTranslations = $oldTranslations ? json_decode($oldTranslations, true) : [];
                foreach($translationToSave as $field => $value) {
                    $oldTranslations[$field] = $value;
                }
                $result = $mapper->updateTranslation($translationDto, $oldTranslations);
            }
            else {
                $result = $mapper->createTranslation($dto->getTableName(), $dto->getId(), $languageId, $translationToSave);
            }

            return $result;
        }
        catch(\Exception $exp) {
            return false;
        }
    }


    /**
     * returns dto translations for all languages
     *
     * @param AbstractCmsDto $dto
     * @return array
     */
    public function getDtoTranslations(AbstractCmsDto $dto) {
        $translatableFields = $dto->getTranslatableFields();

        if(!$translatableFields) {
            return [];
        }

        if($dto->getId() === null) {
            return [];
        }

        $languageManager = LanguageManager::getInstance();

        try {
            $languages = $languageManager->getLanguages();
            $result = [];
            foreach ($languages as $language) {
                $result[$language->getId()] = $this->getDtoTranslation($dto, $language->getId());
            }

            return $result;
        }
        catch(\Exception $exp) {
            return [];
        }
    }


    /**
     * get dto translations for given language
     *
     * @param AbstractCmsDto $dto
     * @param int $languageId
     *
     * @return array
     */
    public function getDtoTranslation(AbstractCmsDto $dto, int $languageId) {
        $translatableFields = $dto->getTranslatableFields();

        if(!$translatableFields) {
            return [];
        }

        if($dto->getId() === null) {
            return [];
        }

        $mapper = TranslationMapper::getInstance();

        try {
            $translationDto = $mapper->getTranslation($dto->getTableName(), $dto->getId(), $languageId);
            if(!$translationDto) {
                return [];
            }

            $translation = $translationDto->getTranslation();

            if(!$translation) {
                return [];
            }

            $translation = json_decode($translation, true);

            $result = [];
            foreach($translatableFields as $field) {
                $result[$field] = isset($translation[$field]) ? $translation[$field] : "";
            }

            return $result;
        }
        catch (\Exception $exception) {
            return [];
        }

    }

    public function getItemsAllTranslations($itemDto) {
        $id = $itemDto->getId();
        if(!$id){
           return null;
        }
        $type = $itemDto->getTableName();
        $mapper = TranslationMapper::getInstance();

        if(!$this->translationDto) {
            $this->translationDto = $mapper->getItemsAllTranslations($id, $type);
        }
        $res = [];
        foreach ($this->translationDto as $key => $value){
            $res[$value->getLanguageId()] = ['language_name' => $value->getName(), 'value' => $value->getTranslation()];
        }
        return $res;
    }


    public function deleteItemsTranslations($itemDto) {
        $mapper = TranslationMapper::getInstance();
        $tableName = $itemDto->getTableName();
        $id = $itemDto->getId();
        $mapper->deleteItemsTranslations($id, $tableName);
    }


}