<?php
/**
 * UserGroupDto mapper class
 * setter and getter generator
 * for ilyov_users_groups table
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 * @package ngs.NgsAdminTools.dal.dto
 * @version 6.0
 *
 */

namespace ngs\NgsAdminTools\dal\dto;


use ngs\dal\dto\AbstractDto;


class UserGroupDto extends AbstractDto {

    // Map of DB value to Field value
    protected $mapArray = array('id' => 'id', 'name' => 'name', 'description' => 'description', 'parent_id' => 'parentId',
        'type' => 'type', 'added_date' => 'addedDate');


    // returns map array
    public function getMapArray(): array {
        return $this->mapArray;
    }

}