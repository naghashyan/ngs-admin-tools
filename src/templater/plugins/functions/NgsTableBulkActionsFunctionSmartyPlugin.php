<?php

/**
 * table bulk actions function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;


class NgsTableBulkActionsFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsTableBulkActions';
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
        $withoutExport = isset($params['without_export']) && $params['without_export'];
        $exportBtn = $withoutExport ? '' : '<a href="javascript:void(0)" class="f_bulk-action" data-type="export_excel">Export (.xls)</a>';
        $actions = "";
        $actions .= $exportBtn . ' ';
        $actions .= '<a href="javascript:void(0)" class="f_bulk-action" data-type="delete">Delete</a> ';
        if(isset($params['additional_actions']) && $params['additional_actions']) {
            foreach($params['additional_actions'] as $additionalAction) {
                $actions .= '<a href="javascript:void(0)" class="f_bulk-action" data-type="' . $additionalAction['type'] . '">' .$additionalAction['name'] . '</a> ';
            }
        }
        return '<div '.$id.' class="bulk-box dropdown' .$class .'">
                    <button class="button basic medium primary with-icon float-left dropdown-toggle" type="button">
                        Bulk actions
                        <i class="icon-svg3 right-icon"></i>
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <div class="dropdown-box">' . $actions . '
                    </div>
                </div>';
    }
}
