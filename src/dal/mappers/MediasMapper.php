<?php
/**
 *
 * BusinessMapper class is extended class from AbstractMysqlMapper.
 * It contatins all read and write functions which are working with ilyov_business table.
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2018
 * @package dal.mappers.business
 * @version 1.0
 *
 */

namespace ngs\AdminTools\dal\mappers;

use ngs\AdminTools\dal\dto\MediasDto;
use ngs\dal\mappers\AbstractMysqlMapper;
use ngs\AdminTools\dal\mappers\AbstractCmsMapper;

class MediasMapper extends AbstractCmsMapper
{

    //! Private members.

    private static ?MediasMapper $instance = null;
    public $tableName = 'medias';

    /**
     * Returns an singleton instance of this class
     *
     * @return MediasMapper Object
     */
    public static function getInstance(): MediasMapper
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * indicates if table has created_by updated_by fields
     *
     * @return bool
     */
    public function hasCreator(): bool
    {
        return true;
    }


    /**
     * @see AbstractMysqlMapper::createDto()
     */
    public function createDto(): MediasDto
    {
        return new MediasDto();
    }

    /**
     * @see AbstractMysqlMapper::getPKFieldName()
     */
    public function getPKFieldName(): string
    {
        return 'id';
    }

    /**
     * @see AbstractMysqlMapper::getTableName()
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }


    private $GET_ITEM_IMAGES = "SELECT * FROM %s WHERE `object_key` = :itemId AND `object_type` = :itemType AND `type` = 'image'";

    /**
     * @param $itemId
     * @param $itemType
     * @return \ngs\dal\dto\AbstractDto[]
     */
    public function getItemImages($itemId, $itemType) {
        $sqlQuery = sprintf($this->GET_ITEM_IMAGES, $this->getTableName());

        return $this->fetchRows($sqlQuery, ['itemId' => $itemId, 'itemType' => $itemType]);
    }


    private $GET_MEDIA_BY_KEY_OBJECT_TYPE_AND_MEDIA_TYPE = "SELECT `id` FROM %s WHERE `object_key` = :objectKey AND `object_type` = :objectType AND `type` = :mediaType";

    public function getMediaIds($objectId, $objectType, $mediaType){
        $sqlQuery = sprintf($this->GET_MEDIA_BY_KEY_OBJECT_TYPE_AND_MEDIA_TYPE, $this->tableName);
        return $this->fetchRows($sqlQuery, ['objectKey' => $objectId, 'objectType' => $objectType, 'mediaType' => $mediaType]);
    }

    private $GET_EXTENSION_BY_ID = "SELECT `extension` FROM %s WHERE `id` = %d";

    public function getMediaExtension($id){
        $sqlQuery = sprintf($this->GET_EXTENSION_BY_ID, $this->tableName, $id);
        return $this->fetchField($sqlQuery,'extension');
    }

    private $CHANGE_IMAGE_TITLE = "UPDATE %s SET `description` = :description WHERE `medias`.id = :id";

    public function updateImageTitle($id, $text){
        $sqlQuery = sprintf($this->CHANGE_IMAGE_TITLE, $this->tableName);
        return $this->fetchRow($sqlQuery,['description' => $text, 'id' => $id]);

    }


    private $GET_ITEM_ATTACHED_FILES = "SELECT * FROM %s WHERE `object_key` = :itemId AND `object_type` = :itemType AND `type` = 'file'";

    public function getItemAttachedFiles($itemId, $itemType){
        $sqlQuery = sprintf($this->GET_ITEM_ATTACHED_FILES, $this->getTableName());

        return $this->fetchRows($sqlQuery, ['itemId' => $itemId, 'itemType' => $itemType]);
    }

    private $GET_ITEM_MEDIA_BY_NAME = "SELECT * FROM %s WHERE `object_key` = :itemId AND `object_type` = :itemType AND `original_name` = :fileName";

    public function getItemMediaByName(int $objectKey, string $objectType, string $mediaName) {
        $sqlQuery = sprintf($this->GET_ITEM_MEDIA_BY_NAME, $this->getTableName());
        return $this->fetchRow($sqlQuery, ['itemId' => $objectKey, 'itemType' => $objectType, 'fileName' => $mediaName]);
    }


    /**
     * set all rows of current item to isMain=null
     * @param $itemId
     * @param $tableName
     * @throws \ngs\exceptions\DebugException
     */
    public function setMainImageToNullInAllRowsOfCurrentItem($itemId, $tableName) {
        $query = sprintf('UPDATE %s SET `is_main` = NULL WHERE `object_type` = :objectType AND `object_key` = :objectKey AND `is_main` = 1', $this->getTableName());
        $this->fetchRows($query, ['objectType' => $tableName, 'objectKey' => $itemId]);
    }


    private $GET_ITEM_MAIN_IMAGE = "SELECT * FROM %s WHERE `object_key` = :objectId AND `object_type` = :objectType AND `is_main` = 1";

    /**
     * @param $objectId
     * @param $objectType
     * @return MediasDto|null
     */
    public function getMainImage($objectId, $objectType) {
        try {
            $query = sprintf($this->GET_ITEM_MAIN_IMAGE, $this->getTableName());
            return $this->fetchRow($query, ['objectId' => $objectId, 'objectType' => $objectType]);
        }
        catch(\Exception $exp) {
            $this->getLogger()->error('failed to get main media: ' . $exp->getMessage());
            return null;
        }

    }



}