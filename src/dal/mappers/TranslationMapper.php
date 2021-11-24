<?php
/**
 *
 * TranslationMapper class is extended class from AbstractCmsMapper.
 * It contatins all read and write functions which are working with translations table.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.dal.mappers
 * @version 1.0
 *
 */

namespace ngs\NgsAdminTools\dal\mappers;

use ngs\NgsAdminTools\dal\dto\TranslationDto;
use ngs\dal\dto\AbstractDto;
use ngs\dal\mappers\AbstractMysqlMapper;

class TranslationMapper extends AbstractCmsMapper {

    //! Private members.

    private static $instance;
    public $tableName = "translations";

    /**
     * Returns an singleton instance of this class
     *
     * @return TranslationMapper
     */
    public static function getInstance(): TranslationMapper {
        if (self::$instance == null){
            self::$instance = new TranslationMapper();
        }
        return self::$instance;
    }

    public function createDto(): AbstractDto
    {
        return new TranslationDto();
    }

    /**
     * @see AbstractMysqlMapper::getPKFieldName()
     */
    public function getPKFieldName() :string {
        return "id";
    }

    /**
     * @see AbstractMysqlMapper::getTableName()
     */
    public function getTableName() :string {
        return $this->tableName;
    }


    private $GET_TRANSLATION = 'SELECT * FROM `%s` WHERE `data_type` = :dataType AND `data_key` = :dataKey AND `language_id` = :languageId';

    /**
     * @param string $dataType
     * @param int $dataKey
     * @param int $languageId
     * @return TranslationDto|null
     * @throws \ngs\exceptions\DebugException
     */
    public function getTranslation(string $dataType, int $dataKey, int $languageId) {
        $sqlQuery = sprintf($this->GET_TRANSLATION, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['dataType' => $dataType, 'dataKey' => $dataKey, 'languageId' => $languageId]);
    }


    private $GET_ITEMS_ALL_TRANSLATIONS = 'SELECT `t`.`language_id`, `languages`.`name`, `t`.`translation` FROM `translations` t INNER JOIN `languages` ON `t`.`language_id` = `languages`.id WHERE `data_type` = :dataType AND `data_key` = :dataKey;';

    public function getItemsAllTranslations(int $dataKey, string $dataType) {
        $sqlQuery = sprintf($this->GET_ITEMS_ALL_TRANSLATIONS, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['dataType' => $dataType, 'dataKey' => $dataKey]);
    }


    /**
     * @param string $dataType
     * @param int $dataKey
     * @param int $languageId
     * @param array $translation
     * @return bool
     */
    public function createTranslation(string $dataType, int $dataKey, int $languageId, array $translation) {
        /** @var TranslationDto $dto */
        $dto = $this->createDto();

        $dto->setDataType($dataType);
        $dto->setDataKey($dataKey);
        $dto->setLanguageId($languageId);
        $dto->setTranslation(json_encode($translation, JSON_UNESCAPED_UNICODE));

        try {
            $id = $this->insertDto($dto);

            return !!$id;
        }
        catch (\Exception $exp) {

            return false;
        }

    }


    /**
     * update translation
     *
     * @param TranslationDto $dto
     * @param array $translation
     * @return bool|int|null
     */
    public function updateTranslation(TranslationDto $dto, array $translation) {

        try {
            $dto->setTranslation(json_encode($translation, JSON_UNESCAPED_UNICODE));
            return $this->updateByPK($dto);
        }
        catch(\Exception $exp) {
            return false;
        }

    }


    private $DELETE_TRANSLATIONS = 'DELETE FROM `%s` WHERE `data_type` = :dataType AND `data_key` = :dataKey';

    public function deleteItemsTranslations($id, $tableName) {
        $sqlQuery = sprintf($this->DELETE_TRANSLATIONS, $this->getTableName());
        return $this->fetchRows($sqlQuery, ['dataType' => $tableName, 'dataKey' => $id]);
    }

}