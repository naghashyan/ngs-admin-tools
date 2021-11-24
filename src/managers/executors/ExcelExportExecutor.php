<?php
/**
 * AbstractSageSyncableManager manager class
 * managers which are related with sage sync should be extended from this class
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.NgsAdminTools.managers.executors
 * @version 1.0.0
 *
 */

namespace ngs\NgsAdminTools\managers\executors;

use Closure;
use ngs\NgsAdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\NgsAdminTools\managers\AbstractCmsManager;
use ngs\NgsAdminTools\util\StringUtil;
use PhpOffice\PhpSpreadsheet\IOFactory;


class ExcelExportExecutor extends AbstractJobExecutor
{
    private $manager = null;


    /**
     * returns current job name
     * @return string
     */
    public function getJobName() :string
    {
        return "Excel export job";
    }

    /**
     * override function to handle execution
     *
     * @param Closure $progressTracker
     *
     * @return array
     */
    protected function execute(Closure $progressTracker = null) :array {
        $managerClass = $this->params['manager'];
        $manager = $managerClass::getInstance();
        $this->manager = $manager;

        return $this->getItemsAddToCsv([], []);
    }


    /**
     * @param array $fileNames
     * @param array $fileTitles
     * @param int $offset
     * @param int $limit
     * @return array
     */
    private function getItemsAddToCsv(array $fileNames, array $fileTitles, $offset = 0, $limit = 500) {
        /** @var AbstractCmsManager $manager */
        $manager = $this->getManager();
        $dto = $manager->getMapper()->createDto();
        $possibleValues = $manager->getSelectionPossibleValues($dto);
        $paramsBin = $this->getNgsListBinParams($offset, $limit);
        $this->getLogger()->info("doing for offset " . $offset . ' started');
        $itemDtos = $manager->getList($paramsBin);
        $itemsCount = count($itemDtos);
        foreach ($itemDtos as $itemDto) {
            if($possibleValues) {
                $manager->fillDtoWithRelationalData($itemDto, $possibleValues);
            }
        }

        $this->getLogger()->info("doing for offset " . $offset . ' get data');
        $csvFiles = $this->getCsvFiles($fileNames, $fileTitles, $itemDtos);
        unset($possibleValues);
        unset($itemDtos);
        $fileNames = $csvFiles['files'];
        $fileTitles = $csvFiles['titles'];

        if($itemsCount < $limit) {
            return $this->convertCsvToExcel(NGS()->getDataDir('admin') . '/download_files', $fileNames, $fileTitles);
        }
        else {
            return $this->getItemsAddToCsv($fileNames, $fileTitles, $offset + $limit, $limit);
        }
    }


    /**
     * returns csv files with data which should be converted to xlsx
     *
     * @param array $fileNames
     * @param array $fileTitles
     *
     * @param $itemDtos
     *
     * @return array
     */
    protected function getCsvFiles(array $fileNames, array $fileTitles, $itemDtos) {
        $manager = $this->getManager();

        if(!$fileNames) {
            $fileName = $manager->getMapper()->getTableName() . '_' . $this->params['user_id'] . '_' . time() . '.csv';
            $fileNames[] = $fileName;
            $fileTitles[] = $fileName;
            $filePath = NGS()->getDataDir('admin') . '/download_files/' . $fileName;

            $file = fopen($filePath, "w");
            $file = $this->addDataToCsv($file, $itemDtos, true);
            fclose($file);
        }
        else {
            $filePath = NGS()->getDataDir('admin') . '/download_files/' . $fileNames[0];
            $file = fopen($filePath, "a+");
            $file = $this->addDataToCsv($file, $itemDtos, false);
            fclose($file);
        }

        return ['files' => $fileNames, 'titles' => $fileTitles];
    }


    /**
     * add items data as rows in csv
     *
     * @param $file
     * @param $itemDtos
     * @param $withHeader
     *
     * @return mixed
     */
    protected function addDataToCsv($file, $itemDtos, bool $withHeader) {
        if($withHeader) {
            fputcsv($file, $this->getCsvHeaders());
        }
        foreach($itemDtos as $itemDto) {
            fputcsv($file, $this->getCsvRowData($itemDto));
        }
        return $file;
    }


    /**
     * returns csv header data by using dto visible fields
     *
     * @return array
     */
    protected function getCsvHeaders() {
        $result = [];
        $selectedFields = $this->params['fields'];
        foreach($selectedFields as $selectedField) {
            $result[] = $selectedField['displayName'];
        }


        return $result;
    }


    /**
     * return custom value by item and field name
     *
     * @param $item
     * @param string $fieldName
     * @return string
     */
    protected function getUnknownFieldValue($item, string $fieldName) {
        return "";
    }


    /**
     * return csv row data from item
     *
     * @return array
     */
    private function getCsvRowData($item) {
        $result = [];
        $selectedFields = $this->params['fields'];

        foreach($selectedFields as $selectedField) {
            $fieldName = $selectedField['fieldName'];
            $fieldNameParts = explode(".", $fieldName);
            if(count($fieldNameParts) > 1) {
                $fieldName = $fieldNameParts[1];
            }
            else {
                $fieldName = $fieldNameParts[0];
            }
            $fieldName = trim($fieldName, "`");
            $getter = StringUtil::getGetterByDbName($fieldName);
            if(!method_exists($item, $getter)) {
                $result[] = $this->getUnknownFieldValue($item, $fieldName);
                continue;
            }
            $value = $item->$getter();
            if(strpos($value, ',') !== false) {
                $value = '"' . $value . '"';
            }
            $result[] = $value;
        }

        return $result;
    }




    /**
     * @param $csvFilePath
     * @param array $csvFileNames
     * @param array $fileTitles
     *
     * @return array
     */
    private function convertCsvToExcel($csvFilePath, $csvFileNames, $fileTitles = []) {
        try {
            $reader = IOFactory::createReader('Csv');
            $objPHPExcel = $reader->load($csvFilePath . '/' . $csvFileNames[0]);

            if(count($csvFileNames) > 1) {
                $objPHPExcel->getActiveSheet()->setTitle($fileTitles[0]);
                for($i=1; $i<count($csvFileNames); $i++) {
                    $newReader = IOFactory::createReader('Csv');
                    $newObjPHPExcel = $newReader->load($csvFilePath . '/' . $csvFileNames[$i]);
                    $newObjPHPExcel->getActiveSheet()->setTitle($fileTitles[$i]);
                    $objPHPExcel->addExternalSheet($newObjPHPExcel->getActiveSheet());
                }
            }

            $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
            $fileRealName = str_replace('.csv', '', $csvFileNames[0]);
            $objWriter->save($csvFilePath . '/' . $fileRealName . '.xlsx');
            for($i=0; $i<count($csvFileNames); $i++) {
                unlink($csvFilePath . '/' . $csvFileNames[$i]);
            }

            return ['success' => true, 'fileName' => $fileRealName . '.xlsx'];

        }
        catch(\Exception $exp) {
            return ['success' => false, 'message' => $exp->getMessage()];
        }
    }


    /**
     * @param $params
     * @param int $offset
     * @param int $limit
     * @return NgsCmsParamsBin|null
     */
    private function getNgsListBinParams($offset = 0, $limit = 500): ?NgsCmsParamsBin
    {
        $ordering = isset($this->params['ordering']) ? $this->params['ordering'] : 'DESC';
        $sorting = isset($this->params['sorting']) ? $this->params['sorting'] : 'id';
        $this->params['totalSelection'] = isset($this->params['totalSelection']) ? $this->params['totalSelection'] : false;
        $this->params['unCheckedElements'] = isset($this->params['unCheckedElements']) ? $this->params['unCheckedElements'] : "";
        $this->params['checkedElements'] = isset($this->params['checkedElements']) ? $this->params['checkedElements'] : "";
        $paramsBin = new NgsCmsParamsBin();
        $paramsBin->setSortBy($sorting);
        $paramsBin->setOrderBy($ordering);

        if(isset($this->params['filter']) && is_array($this->params['filter'])) {
            $paramFilter = $this->params['filter'];
        }
        else {
            $paramFilter = isset($this->params['filter']) ? json_decode($this->params['filter'], true) : [];
        }

        $searchData = null;
        $searchableFields = $this->getManager()->getSearchableFields();
        if(isset($filter['search'])) {
            $searchData = [
                'searchKeys' => $paramFilter['search'],
                'searchableFields' => $searchableFields
            ];
        }

        $filter = [];

        foreach($paramFilter as $key => $value) {
            if($key == 'search') {
                continue;
            }
            $filter[$key] = $value;
        }

        if(!isset($filter['and'])) {
            $filter['and'] = [];
        }

        if(($this->params['totalSelection'] === true || $this->params['totalSelection'] === 'true') && $this->params['unCheckedElements']) {
            $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'not_in', 'searchValue' => explode(',', $this->params['unCheckedElements'])];
        }
        else if(($this->params['totalSelection']) && !$this->params['unCheckedElements']) {
            $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'not_in', 'searchValue' => [-1]];
        }
        else if($this->params['totalSelection'] === 'false' || !$this->params['totalSelection']){
            if($this->params['checkedElements']) {
                if(is_array($this->params['checkedElements'])) {
                    $inCondition = $this->params['checkedElements'];
                }
                else {
                    $inCondition = explode(',', $this->params['checkedElements']);
                }
            }
            else {
                $inCondition = [-1];
            }
            $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'in', 'searchValue' => $inCondition];
        }

        if(!$filter['and']) {
            return null;
        }




        if($searchableFields || $filter) {
            $paramsBin->setVersion(2);
            $paramsBin->setFilter(['filter' => $filter, 'search' => $searchData, 'table' => $this->getManager()->getMapper()->getTableName()]);
        }

        $paramsBin->setLimit($limit);
        $paramsBin->setOffset($offset);
        $paramsBin->setJoinCondition($this->getJoinCondition());
        return $paramsBin;
    }

    /**
     * @return string
     */
    protected function getJoinCondition() {
        return "";
    }


    protected function getManager() {
        return $this->manager;
    }

}
