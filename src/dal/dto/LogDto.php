<?php
/**
 * LogDto class
 * setter and getter generator
 * for ilyov_logs table
 * this dto used to store actions logs
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2019
 * @package ngs.NgsAdminTools.dal.dto
 * @version 1.0
 *
 */

namespace ngs\NgsAdminTools\dal\dto;

use ngs\NgsAdminTools\dal\dto\AbstractCmsDto;

class LogDto extends AbstractCmsDto
{


    protected $id;
    protected $userId;
    protected $action;
    protected $itemId;
    protected $itemTableName;
    protected $data;
    protected $addedTime;
    protected $success;
    protected $errorText;

    protected array $mapArray = ["id" => ["type" => "number", "display_name" => "ID", "field_name" => "id", "visible" => true, "actions" => []],
        "user_id" => ["type" => "number", "display_name" => "User ID", "field_name" => "userId", "visible" => true, "actions" => []],
        "action" => ["type" => "text", "display_name" => "Action", "field_name" => "action", "visible" => true, "actions" => []],
        "item_id" => ["type" => "number", "display_name" => "Item ID", "field_name" => "itemId", "visible" => true, "actions" => []],
        "item_table_name" => ["type" => "text", "display_name" => "Table Name", "field_name" => "itemTableName", "visible" => true, "actions" => []],
        "data" => ["type" => "text", "display_name" => "Data", "field_name" => "data", "visible" => true, "actions" => []],
        'success' => ['type' => 'checkbox', 'display_name' => 'Success', 'field_name' => 'success', 'visible' => false, 'actions' => [], 'required_in' => []],
        "added_time" => ["type" => "date", "display_name" => "Added Time", "field_name" => "addedTime", "visible" => true, "actions" => []],
        "error_text" => ["type" => "text", "display_name" => "Error text", "field_name" => "errorText", "visible" => false, "actions" => [], 'required_in' => []]
    ];

    // constructs class instance
    public function __construct()
    {
    }

    public function getTableName(): string
    {
        return "ngs_logs";
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
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action): void
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param mixed $itemId
     */
    public function setItemId($itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return mixed
     */
    public function getItemTableName()
    {
        return $this->itemTableName;
    }

    /**
     * @param mixed $itemTableName
     */
    public function setItemTableName($itemTableName): void
    {
        $this->itemTableName = $itemTableName;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getAddedTime()
    {
        return $this->addedTime;
    }

    /**
     * @param mixed $addedTime
     */
    public function setAddedTime($addedTime): void
    {
        $this->addedTime = $addedTime;
    }

    /**
     * @return mixed
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param mixed $success
     */
    public function setSuccess($success): void
    {
        $this->success = $success;
    }

    /**
     * @return mixed
     */
    public function getErrorText()
    {
        return $this->errorText;
    }

    /**
     * @param mixed $errorText
     */
    public function setErrorText($errorText): void
    {
        $this->errorText = $errorText;
    }




    

}