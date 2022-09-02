<?php
/**
 * BeforeActionEventStructure class, can call before action start
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

class BeforeActionEventStructure extends AbstractEventStructure
{
    private $dto;
    private string $actionName;

    public function __construct(array $params, ?string $actionName, ?AbstractCmsDto $dto)
    {
        parent::__construct($params);
        $this->dto = $dto;
        $this->actionName = $actionName;
    }

    public static function getEmptyInstance() :AbstractEventStructure {
        return new BeforeActionEventStructure([], null, null);
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
    public function getActionName(): string
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     */
    public function setActionName(string $actionName): void
    {
        $this->actionName = $actionName;
    }
}