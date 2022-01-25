<?php

/**
 * pagination function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;


class NgsPaginationFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsPagination';
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
        $page = $params['page'];
        $limit = $params['limit'];
        $pageCount = $params['pageCount'];

        $content = $this->getPaginationBody($params);

        return '<div class="action-bar nomargin">
                    <div data-im-page="' . $page . '" data-im-page-count="' . $pageCount . '" data-im-limit="' . $limit . '" class="f_pageingBox pagging-box">'.
                        $content .
                    '</div>
                </div>';
    }


    /**
     * @param $params
     * @return string
     */
    private function getPaginationBody($params) {
        $page = $params['page'];
        $limit = $params['limit'];
        $pageCount = $params['pageCount'];
        $itemsCount = $params['itemsCount'];
        $itemsPerPageOptions = $params['itemsPerPageOptions'];
        $start = $params['start'];
        $end = $params['end'];

        $mainPart = $this->getPaginationMainPart($page, $limit, $pageCount, $itemsCount, $itemsPerPageOptions);
        $pagesButtonsPart = $this->getPaginationPagesButtonsContent($pageCount, $page, $start, $end);

        return  $mainPart . $pagesButtonsPart;
    }


    /**
     * @param $page
     * @param $limit
     * @param $pageCount
     * @param $itemsCount
     * @param $itemsPerPageOptions
     * @return string
     */
    private function getPaginationMainPart($page, $limit, $pageCount, $itemsCount, $itemsPerPageOptions) {

        $perPageOptionsContent = $this->getPerPageOptionsContent($limit, $itemsPerPageOptions);

        return '<div class="pagination-box dataTables_info">
                        <div class="page-ctrl">
                            <span>Page</span>
                            <div class="input-field col s6">
                                <input class="f_go_to_page no-right-padding" type="number" value="' . $page . '">
                            </div>
                            <span> of ' . $pageCount . ' | View </span>
                
                            <div class="input-field">' . $perPageOptionsContent . '</div>
                            <span> items | <span class="f_items-count"> ' . $itemsCount . ' </span>items</span>
                            <input class="f_old-items-count" type="hidden" value="'.$itemsCount.'">
                        </div>
                 </div>';
    }


    /**
     * @param $limit
     * @param $itemsPerPageOptions
     * @return string
     */
    private function getPerPageOptionsContent($limit, $itemsPerPageOptions) {
        $choicesSearchable = count($itemsPerPageOptions) > 5 ? 'true' : 'false';
        $perPageOptionsContent = '<select data-ngs-searchable="' . $choicesSearchable . '" class="f_count_per_page ngs-choice">';
        foreach($itemsPerPageOptions as $pageOption) {
            $selected = '';
            if($pageOption == $limit) {
                $selected = 'selected';
            }
            $perPageOptionsContent .= '<option ' . $selected . '>' . $pageOption . '</option>';
        }
        $perPageOptionsContent .= '</select>';

        return $perPageOptionsContent;
    }


    /**
     * @param $pageCount
     * @param $page
     * @param $start
     * @param $end
     * @return string
     */
    private function getPaginationPagesButtonsContent($pageCount, $page, $start, $end) {
        $result = '<div class="pagination-box">';
        if($pageCount > 1) {
            $previousDisabled = $page <= 1 ? 'is_disabled' : '';
            $nextDisabled = $page == $pageCount ? 'is_disabled' : '';
            $buttons = $this->getPagesButtonsList($page, $start, $end);
            $result .= '<ul class="pagination">
                                <li class="waves-effect">
                                    <a href="javascript:void(0);" class="f_page ' . $previousDisabled . '"
                                       data-im-page="' . ($page - 1) . '"><i class="icon-svg17l"></i></a>
                                </li>
                                ' . $buttons . '
                                <li class="waves-effect">
                                    <a href="javascript:void(0);" class="f_page ' . $nextDisabled . '"
                                       data-im-page="' . ($page + 1) . '"><i class="icon-svg17"></i></a>
                                </li>
                            </ul>';
        }

        $result .= '</div>';

        return $result;
    }


    /**
     * @param $page
     * @param $start
     * @param $end
     * @return string
     */
    private function getPagesButtonsList($page, $start, $end) {
        $result = '';

        for($i=$start; $i<$end; $i++) {
            $activeClass = $page == ($i + 1) ? 'active' : '';
            $attributes = 'class="f_page" data-im-page="' . ($i + 1) . '"';
            $result .= '<li class="waves-effect ' . $activeClass . '">
                             <a href="javascript:void(0);" ' . $attributes . '>' . ($i + 1) . '</a>
                        </li>';
        }

        return $result;
    }
}
