<?php
/**
 * AbstractSageSyncableManager manager class
 * managers which are related with sage sync should be extended from this class
 *
 * @author Mikael Mkrtchyan
 * @site http://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 * @package ngs.AdminTools.managers.executors
 * @version 1.0.0
 *
 */

namespace ngs\AdminTools\managers\executors;

use Closure;
use ngs\AdminTools\dal\binparams\NgsCmsParamsBin;
use ngs\AdminTools\managers\AbstractCmsManager;
use ngs\AdminTools\util\MathUtil;
use ngs\AdminTools\util\StringUtil;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class ExcelExportExecutor extends AbstractJobExecutor
{
    private $manager = null;
    private $memoryUsageStart = null;
    private $memoryUsageEnd = null;
    private ?int $totalCount = null;
    private ?array $possibleSelectionValues = null;

    /**
     * returns current job name
     * @return string
     */
    public function getJobName() :string
    {
        return "Excel export job";
    }


    /**
     * chunks length
     *
     * @return int
     */
    protected function getLimit() :int {
        return 500;
    }

    /**
     * override function to handle execution
     *
     * @param Closure $progressTracker
     *
     * @return array
     */
    protected function execute(Closure $progressTracker = null) :array {
        $this->memoryUsageStart = memory_get_peak_usage(true);
        $managerClass = $this->params['manager'];
        $manager = $managerClass::getInstance();
        $this->manager = $manager;
        return $this->getItemsAddToCsv($progressTracker, [], [], 0, $this->getLimit());
    }


    /**
     * @param array $fileNames
     * @param array $fileTitles
     * @param int $offset
     * @param int $limit
     * @return array
     */
    private function getItemsAddToCsv(?Closure $progressTracker, array $fileNames, array $fileTitles, $offset = 0, $limit = 500) {

        /** @var AbstractCmsManager $manager */
        $manager = $this->getManager();
        $dto = $manager->getMapper()->createDto();
        if($this->possibleSelectionValues === null) {
            $this->possibleSelectionValues = $manager->getSelectionPossibleValues($dto, true);
        }
        $paramsBin = $this->getNgsListBinParams($offset, $limit);
        $this->getLogger()->info("doing for offset " . $offset . ' started');
        $itemDtos = $manager->getList($paramsBin);
        if($this->totalCount === null) {
            $this->totalCount = $manager->getItemsCount($paramsBin);
        }
        $itemsCount = count($itemDtos);
        foreach ($itemDtos as $itemDto) {
            if($this->possibleSelectionValues) {
                $manager->fillDtoWithRelationalData($itemDto, $this->possibleSelectionValues, $this->getUsedFields());
            }
        }
        $this->onGotItems($itemDtos);
        $this->getLogger()->info("doing for offset " . $offset . ' get data');
        $csvFiles = $this->getCsvFiles($fileNames, $fileTitles, $itemDtos);
        unset($itemDtos);
        $fileNames = $csvFiles['files'];
        $fileTitles = $csvFiles['titles'];
        if($progressTracker) {
            $progressTracker(floor($offset * 100 / $this->totalCount));
        }
        if($itemsCount < $limit) {
            return $this->convertCsvToExcel(NGS()->getDataDir('admin') . '/download_files', $fileNames, $fileTitles);
        }
        else {

            return $this->getItemsAddToCsv($progressTracker, $fileNames, $fileTitles, $offset + $limit, $limit);
        }
    }
     

    protected function onGotItems(array $items) {

    }

    /**
     * @return array
     */
    private function getUsedFields() {
        $fieldsToExport = $this->params['fields'];
        $result = [];
        foreach($fieldsToExport as $fieldToExport) {
            if(isset($fieldToExport['fieldName']) && !in_array($fieldToExport['fieldName'], $result)) {
                $result[] = $fieldToExport['fieldName'];
            }
        }
        return $result;
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
     * get value based on formula
     *
     * @param $item
     * @param string $formula
     * @return string
     */
    protected function getFormulaColumn($item, string $formula) {
        /** @var AbstractCmsManager $manager */
        $manager = $this->getManager();
        $customFields = $manager->getCustomizableExportColumns();
        $params = [];
        foreach($customFields as $customField) {
            if(strpos($formula, $customField) === false) {
                continue;
            }
            $getter = StringUtil::getGetterByDbName($customField);
            if($item->$getter() === null) {
                return "";
            }
            $params[$customField] = $item->$getter();
        }

        return MathUtil::getValueByFormula($formula, $params);
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
     * modify columns data in excel
     * @param Spreadsheet $sheet
     */
    protected function modifyColumns(Spreadsheet $sheet) {

    }

    /**
     * change column styles
     * @param Spreadsheet $sheet
     */
    protected function convertColumnTypes(Spreadsheet $sheet) {
    }


    protected function onAddRow($item) {
    }


    /**
     * return csv row data from item
     *
     * @return array
     */
    private function getCsvRowData($item) {
        $result = [];
        $selectedFields = $this->params['fields'];
        $this->onAddRow($item);
        foreach($selectedFields as $selectedField) {
            if(isset($selectedField['type']) && $selectedField['type'] === 'custom_column') {
                $result[] = $this->getFormulaColumn($item, $selectedField['formula']);
                continue;
            }
            else {
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
            $this->modifyColumns($objPHPExcel);
            $this->convertColumnTypes($objPHPExcel);
            $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
            $fileRealName = str_replace('.csv', '', $csvFileNames[0]);
            $objWriter->save($csvFilePath . '/' . $fileRealName . '.xlsx');
            for($i=0; $i<count($csvFileNames); $i++) {
                unlink($csvFilePath . '/' . $csvFileNames[$i]);
            }
            $this->memoryUsageEnd = memory_get_peak_usage(true);
            return [
                'success' => true,
                'fileName' => $fileRealName . '.xlsx',
                'memory_usage_start' => $this->getUsageInMb($this->memoryUsageStart),
                'memory_usage_end' => $this->getUsageInMb($this->memoryUsageEnd)
            ];

        }
        catch(\Exception $exp) {
            $this->memoryUsageEnd = memory_get_peak_usage(true);
            return [
                'success' => false,
                'message' => $exp->getMessage(),
                'memory_usage_start' => $this->getUsageInMb($this->memoryUsageStart),
                'memory_usage_end' => $this->getUsageInMb($this->memoryUsageEnd)
            ];
        }
    }


    private function getUsageInMb($memoryUsage) {
        return $memoryUsage / 1024 / 1024;
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
        if(isset($paramFilter['search'])) {
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
            if(is_array($this->params['unCheckedElements'])) {
                $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'not_in', 'searchValue' => $this->params['unCheckedElements']];
            }
            else {
                $filter['and'][] = ['fieldName' => 'id', 'conditionType' => 'not_in', 'searchValue' => explode(',', $this->params['unCheckedElements'])];
            }
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
