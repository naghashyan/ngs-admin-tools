<?php
/**
 * ApiKeysDto class
 * setter and getter generator
 * for ilyov_api_keys table
 *
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2013-2016
 * @package api.dal.dto.api
 * @version 2.0
 *
 *
 */

namespace ngs\AdminTools\dal\dto;

use ngs\dal\dto\AbstractDto;


class ApiKeysDto extends AbstractDto
{

    // Map of DB value to Field value
    protected $mapArray = ['id' => 'id', 'key' => 'key', 'mode' => 'mode', 'added_date' => 'addedDate'];

    // returns map array
    public function getMapArray(): array
    {
        return $this->mapArray;
    }


    private static $ITEM_TYPE = 'api_keys';

    private ?int $id;
    private ?string $key;
    private ?string $mode;
    private ?string $addedDate;

    /**
     * @return int|null
     */
    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getKey(): ?string {
        return $this->key;
    }

    /**
     * @param string|null $key
     */
    public function setKey(?string $key): void {
        $this->key = $key;
    }

    /**
     * @return string|null
     */
    public function getMode(): ?string {
        return $this->mode;
    }

    /**
     * @param string|null $mode
     */
    public function setMode(?string $mode): void {
        $this->mode = $mode;
    }

    /**
     * @return string|null
     */
    public function getAddedDate(): ?string {
        return $this->addedDate;
    }

    /**
     * @param string|null $addedDate
     */
    public function setAddedDate(?string $addedDate): void {
        $this->addedDate = $addedDate;
    }


}