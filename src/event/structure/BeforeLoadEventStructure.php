<?php
/**
 * BeforeLoadEventStructure class, can call before load start
 *
 * @author Mikael Mkrtchyan
 * @site https://naghashyan.com
 * @mail miakel.mkrtchyan@naghashyan.com
 * @year 2022
 * @package ngs.AdminTools.managers.event.structure
 * @version 2.0.0
 *
 */

namespace ngs\AdminTools\event\structure;

use ngs\AdminTools\dal\dto\AbstractCmsDto;
use ngs\event\structure\AbstractEventStructure;

class BeforeLoadEventStructure extends AbstractEventStructure
{
    private $dto;
    private string $loadName;

    public function __construct(array $params, ?string $loadName, ?AbstractCmsDto $dto)
    {
        parent::__construct($params);
        $this->dto = $dto;
        $this->loadName = $loadName;
    }

    public static function getEmptyInstance() :AbstractEventStructure {
        return new BeforeLoadEventStructure([], null, null, null);
    }

    /**
     * @return AbstractCmsDto|null
     */
    public function getDto(): ?AbstractCmsDto
    {
        return $this->dto;
    }

    /**
     * @param AbstractCmsDto|null $dto
     */
    public function setDto(?AbstractCmsDto $dto): void
    {
        $this->dto = $dto;
    }

    /**
     * @return string
     */
    public function getLoadName(): string
    {
        return $this->loadName;
    }

    /**
     * @param string $loadName
     */
    public function setLoadName(string $loadName): void
    {
        $this->loadName = $loadName;
    }
}