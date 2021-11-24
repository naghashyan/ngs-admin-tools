<?php
/**
 * TranslationDto class
 * setter and getter generator
 * for languages table
 * this dto used to store translations
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.dal.dto
 * @version 1.0
 *
 */

namespace ngs\NgsAdminTools\dal\dto;


class TranslationDto extends AbstractCmsDto
{
    protected $id;
    protected $languageId;
    protected $dataType;
    protected $dataKey;
    protected $translation;
    protected $created;
    protected $udpated;

    protected array $mapArray = ["id" => ["type" => "number", "display_name" => "ID", "field_name" => "id", "visible" => true, "actions" => []],
        "language_id" => ["type" => "number", "display_name" => "Language Id", "field_name" => "languageId", "visible" => true, "actions" => ['add', 'edit']],
        "data_type" => ["type" => "text", "display_name" => "Type", "field_name" => "dataType", "visible" => true, "actions" => ['add', 'edit']],
        "data_key" => ["type" => "text", "display_name" => "Key", "field_name" => "dataKey", "visible" => true, "actions" => ['add', 'edit']],
        "translation" => ["type" => "text", "display_name" => "Translation", "field_name" => "translation", "visible" => true, "actions" => ['add', 'edit']],
        'updated' => ['type' => 'date', 'visible' => false, 'sortable' => true, 'actions' => [], 'required_in' => []],
        'created' => ['type' => 'date', 'visible' => true, 'sortable' => true, 'actions' => [], 'required_in' => []]

    ];

    // constructs class instance
    public function __construct()
    {
    }

    public function getTableName(): string
    {
        return "translations";
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @param mixed $languageId
     */
    public function setLanguageId($languageId): void
    {
        $this->languageId = $languageId;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     */
    public function setDataType($dataType): void
    {
        $this->dataType = $dataType;
    }

    /**
     * @return mixed
     */
    public function getDataKey()
    {
        return $this->dataKey;
    }

    /**
     * @param mixed $dataKey
     */
    public function setDataKey($dataKey): void
    {
        $this->dataKey = $dataKey;
    }

    /**
     * @return mixed
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param mixed $translation
     */
    public function setTranslation($translation): void
    {
        $this->translation = $translation;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created): void
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUdpated()
    {
        return $this->udpated;
    }

    /**
     * @param mixed $udpated
     */
    public function setUdpated($udpated): void
    {
        $this->udpated = $udpated;
    }
}