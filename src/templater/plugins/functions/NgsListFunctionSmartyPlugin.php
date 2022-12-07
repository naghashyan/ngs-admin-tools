<?php

/**
 * list function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;


class NgsListFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * by this field will be found the value of row for 'data-im-id' attribute
     * @var string
     */

    private string $primaryKey = 'id';

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsList';
    }

    /**
     * main function which will be called when plugin used in frontend
     *
     * @param $params
     * @param $template
     *
     * @return string|null
     */
    public function index($params, $template): ?string
    {
        if(isset($params['primary_key']) && $params['primary_key']) {
            $this->primaryKey = $params['primary_key'];
        }
        if(!isset($params['is_colored'])) {
            $params['is_colored'] = false;
        }
        if(!isset($params['attributes_to_set'])) {
            $params['attributes_to_set'] = false;
        }

        return $this->getFunctionTemplate($params);
    }


    /**
     * returns content of block
     *
     * @param array $params
     *
     * @return string
     */
    protected function getFunctionTemplate(array $params): string
    {
        $class = isset($params['class_form_item']) ? " " .$params['class_form_item'] : "";
        $id = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $header = $this->createTableHeader($params['sort_params'], $params['columns'], $params['has_checkbox'], $params['actions']);

        $paramsForTableContent = [
            'dtos' => $params['dtos'],
            'columns' => $params['columns'],
            'has_checkbox' => $params['has_checkbox'],
            'actions' => $params['actions'],
            'is_colored' => $params['is_colored'],
            'attributes_to_set' => $params['attributes_to_set']
        ];


        $content = $this->createTableContent($paramsForTableContent);

        return '<div '.$id. ' class="f_table table bordered_t action_t' .$class .'">' . $header . $content . '</div>';
    }


    /**
     * creates list header template
     *
     * @param $sortParams
     * @param array $columns
     * @param bool $hasCheckbox
     * @param array $actions
     *
     * @return string
     */
    private function createTableHeader(?array $sortParams, array $columns, bool $hasCheckbox, array $actions): string
    {
        $checkbox = '';
        if ($hasCheckbox) {
            $checkbox = $this->getCheckboxContent();
        }
        $headerColumns = '';
        foreach ($columns as $index => $column) {
            $headerColumns .= $this->getHeaderColumn($column, $sortParams, $index);
        }

        $headerActions = '';
        if (count($actions)) {
            $headerActions = '<li class="action right-align"></li>';
        }

        return '<ul id="gridHeader" class="table-row table-head">' . $checkbox . $headerColumns . $headerActions . '</ul>';
    }


    /**
     * returns checkbox html
     *
     * @return string
     */
    private function getCheckboxContent(): string
    {
        return '<li class="f_check-items check-items  left-align">
               <div class="checkbox-item">
                <label>
                 <input type="checkbox" class="filled-in check-item f_check-item"/>
                 <span class="checkbox-span"></span>
                </label>
               </div>
              </li>';
    }


    /**
     * @param $columnData
     * @param $sortParams
     *
     * @param $index
     * @return string
     */
    private function getHeaderColumn($columnData, $sortParams, $index): string
    {
        $sortClass = "";
        $order = "";
        $sortType = null;
        $sortName = $columnData['name'];
        if (isset($columnData['sort_name'])) {
            $sortName = $columnData['sort_name'];
        }

        if ($sortParams && isset($sortParams[$sortName])) {
            $sortType = $sortParams[$sortName];
        }
        if (isset($columnData['sortable']) && $columnData['sortable']) {
            $sortClass = 'f_sorting sorted';
            if ($sortType) {
                $sortClass .= ' ' . $sortType;
                $order = 'data-im-order="' . $sortType . '"';
            }
        }
        $columnData['display_name'] = $columnData['display_name'] ?? ucwords(str_replace('_', ' ', $columnData['name']));

        return '<li data-header-column-index="' . $index . '" id="' . $columnData['name'] . '" class="' . $sortClass . '" data-im-sorting="' . $sortName . '" ' . $order . '>
                  <span class="elipsis-box">' . $columnData['display_name'] . '</span><span class="f_column-resize-line column-resize-line">|</span></li>';
    }


    /**
     * creates list content
     * @param $paramsForTableContent
     *
     * @return string
     */
    private function createTableContent($paramsForTableContent): string
    {
        $result = '<div id="itemsContent" class="table-row-group f_cms-table-container">';


        foreach ($paramsForTableContent['dtos'] as $key => $dto) {
            $result .= $this->createTableContentRow($key, $dto, $paramsForTableContent);
        }
        $result .= '</div>';

        return $result;
    }


    /**
     * creates list item row html
     *
     * @param int $index
     * @param $dto
     * @param array $paramsForTableContentRow
     * @return string
     */
    private function createTableContentRow(int $index, $dto, array $paramsForTableContentRow): string
    {
        $primaryKeyCamelCase = $this->underlinesToCamelCase($this->primaryKey);
        $primaryKeyGetter = 'get' . $primaryKeyCamelCase;
        $primaryKeyValue = $dto->$primaryKeyGetter();
        $isItemSystemSet = $dto->getSystem();

        $colorClass = " ";
        if ($paramsForTableContentRow['is_colored'] && $paramsForTableContentRow['is_colored']['get_color_class_from']){
            $getter = 'get' . $this->underlinesToCamelCase($paramsForTableContentRow['is_colored']['get_color_class_from']);
            $colorClass = " " . $dto->$getter();
        }

        $additionalAttributes = " ";
        if($paramsForTableContentRow['attributes_to_set']) {
            foreach ($paramsForTableContentRow['attributes_to_set'] as $key => $attribute) {
                $getter = 'get' . $this->underlinesToCamelCase($attribute);
                $attributeValue = $dto->$getter();
                if(!$attributeValue) {
                    $attributeValue = "null";
                }
                $additionalAttributes .= " " . $key . "='" . $attributeValue . "' ";
            }
        }


        $result = '<ul class="table-row f_table_row' .$colorClass. '"' . $additionalAttributes . ' data-im-id="' . $primaryKeyValue . '" data-im-index="' . $index . '">';
        if ($paramsForTableContentRow['has_checkbox']) {
            $result .= $this->getCheckboxContent();
        }
        foreach ($paramsForTableContentRow['columns'] as $indexOfColumn => $column) {
            $withoutTags = isset($column['with_tags']) && $column['with_tags'] ? false : true;

            $fieldCamelCaseName = $this->underlinesToCamelCase($column['name']);
            $fieldGetter = 'get' . $fieldCamelCaseName;
            $indexOfColumnAsAttribute = ' data-content-row-column-index="' . $indexOfColumn . '" ';

            $customAttributesToColumn = '';
            if(isset($column['custom_attributes_to_column']) && !empty($column['custom_attributes_to_column'])) {
                foreach ($column['custom_attributes_to_column'] as $attributeName => $attributeValue) {
                    $customAttributesToColumn .= $attributeName . '="' . $attributeValue . '" ';
                }
            }

            if (isset($column['not_standard_column']) && isset($column['content'])) {
                $fieldGetterValue = $dto->$fieldGetter();
                if ($fieldGetterValue === null) {
                    $fieldGetterValue = '';
                }
                $result .= '<li ' . $customAttributesToColumn . $indexOfColumnAsAttribute . ' class="f_' . $fieldCamelCaseName . '">'
                    . str_replace('@ns_value', $fieldGetterValue, $column['content']) .
                    '</li>';
                continue;
            }
            if (isset($column['is_checkbox']) && $column['is_checkbox']) {
                $checked = strip_tags($dto->$fieldGetter()) ? ' checked ' : ' ';
                $classForDisableIfParentInViewMode = $dto->$fieldGetter() ? ' ' : ' list-checkbox-item-disable';


                $result .= '<li' . $customAttributesToColumn . $indexOfColumnAsAttribute . ' class="f_' . $fieldCamelCaseName . '">
                                <div class="checkbox-item' . $classForDisableIfParentInViewMode . '">
                                    <label>
                                        <input type="checkbox"' . $checked . 'class="filled-in check-item"/>
                                        <span></span>
                                    </label>
                                </div>
                            </li>';
            }
            else if(isset($column['is_image']) && $column['is_image']) {
                $result .=  '<li class="image"><img src="'. $dto->$fieldGetter() . '"></li>';
            }
            else if(isset($column['is_view_checkbox']) && $column['is_view_checkbox']) {
                $checked = strip_tags($dto->$fieldGetter()) ? ' checked ' : ' ';

                $result .=  '<li' . $customAttributesToColumn . $indexOfColumnAsAttribute . ' class="f_' . $fieldCamelCaseName . '">
                                <div class="checkbox-item ">
                                    <label>
                                        <input type="checkbox"' . $checked . 'class="filled-in check-item" disabled="">
                                        <span></span>
                                    </label>
                                </div>
                                </li>';
            }
            else {
                $innerText = '';
                if(isset($column['custom_param_to_getter']) && $column['custom_param_to_getter']) {
                    $innerText = $dto->$fieldGetter($column['custom_param_to_getter']);
                    if($innerText && $withoutTags) {
                        $innerText = strip_tags($innerText);
                    }
                }else {
                    $innerText = $dto->$fieldGetter();
                    if($withoutTags  && $innerText) {
                        $innerText = strip_tags($innerText);
                    }
                }
                //dont break the next line to many lines; it should be no spaces between tags;

                $result .= '<li ' . $customAttributesToColumn . $indexOfColumnAsAttribute . ' class="f_' . $fieldCamelCaseName . '"><span class="elipsis-box">' . $innerText . '</span></li>';
            }

        }
        if ($paramsForTableContentRow['actions']) {
            $actionsField = '<li class="f_actions-box actions-box right-align">';
            foreach ($paramsForTableContentRow['actions'] as $action) {
                $dangerClass = '';

                if (is_array($action)) {
                    $actionName = $action['name'];
                    $actionIcon = $action['icon'];
                    $icon = '<i class="' . $actionIcon . '"></i>';
                } else {
                    $actionName = $action;
                    $icon = '<i class="icon-' . $action . '"></i>';
                    if ($action === 'delete') {
                        $dangerClass = 'dark';
                        $icon = '<i class="icon-delete-trash"></i>';
                    }
                }

                if(!$isItemSystemSet) {
                    $actionsField .= '<button type="button" title="' . $actionName .'" class="button btn-link outline with-small-icon ' . $actionName . '-btn f_' . $actionName . '_btn ' . $dangerClass . '"
                                    data-im-id="' . $primaryKeyValue . '">' . $icon . '
                            </button>';
                }


            }
            $actionsField .= '</li>';

            $result .= $actionsField;
        }


        $result .= '</ul>';

        return $result;
    }
}

