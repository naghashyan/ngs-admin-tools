<?php
/**
 * LanguageDto class
 * setter and getter generator
 * for languages table
 * this dto used to store languages
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


class LanguageDto extends AbstractCmsDto
{


    protected $id;
    protected $name;
    protected $code;

    protected array $mapArray = ["id" => ["type" => "number", "display_name" => "ID", "field_name" => "id", "visible" => true, "actions" => []],
        "name" => ["type" => "text", "display_name" => "Name", "field_name" => "name", "visible" => true, "actions" => ['add', 'edit']],
        "code" => ["type" => "text", "display_name" => "Code", "field_name" => "code", "visible" => true, "actions" => ['add', 'edit']]
    ];

    // constructs class instance
    public function __construct()
    {
    }

    public function getTableName(): string
    {
        return "languages";
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

}