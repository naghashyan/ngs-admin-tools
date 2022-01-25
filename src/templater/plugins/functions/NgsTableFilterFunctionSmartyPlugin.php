<?php

/**
 * table filter function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;


class NgsTableFilterFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsTableFilter';
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

        $favoriteFilters = isset($params['favorite_filters']) ? $params['favorite_filters'] : [];
        $currentFilterName = isset($params['current_filter_name']) ? $params['current_filter_name'] : "";
        $favoriteFiltersContent = $this->getFavoriteFiltersContent($favoriteFilters, $currentFilterName);

        return '<div class="ngs-filter' .$class. '" id="' . $params['name'] . '">
                 <div class="active-filters f_active-filters">
                  <div class="input-field">
                      <div class="page-box">
                        <div class="center-box">
                         <div class="active-filters search-box-save f_active-filters">
                          <div class="criteria-box f_criteria-box"></div>
                          <input type="text" class="input-search f_search-criteria" placeholder="Search...">
                         </div>
                        </div>
                       </div>
                  </div>
                  <div class="filter-favorite-box f_filter-favorite-box">
                   <button title="Filter" class="with-icon medium button outline-light-basik primary f_filter-add-criteria">
                    <i class="icon-filter"></i>
                   </button>
                   <div class="favorite-box dropdown from-right f_favorite-filter-box ml-2">
                    <button title="Favourite" class="with-icon medium button outline-light-basik primary dropdown-toggle">
                     <i class="icon-favorite"></i>
                    </button>
                    <div class="dropdown-box f_favorite-filter" ngs-filter-type="' . $params['table_name'] . '">' . $favoriteFiltersContent . '
                     <div class="input-field">
                      <input class="small f_filter-name" placeholder="Name"/>
                      <button class="apply-criteria button basic primary small f_save-filter">Save</button>
                      <span class="error f_error-message"></span>
                     </div>
                    </div>
                   </div>
                  </div>
                 </div>
                </div>';
    }


    /**
     * creates favorite filters html part
     *
     * @param array $favoriteFilters
     * @param string $currentFilterName
     * @return string
     */
    private function getFavoriteFiltersContent(array $favoriteFilters, ?string $currentFilterName)
    {
        $result = '<div class="saved-filters f_saved-filters">';

        foreach ($favoriteFilters as $favoriteFilter) {
            $activeClass = '';

            if ($currentFilterName && $currentFilterName == $favoriteFilter->getName()) {
                $activeClass = 'active';
            }

            $result .= "<a href='javascript:void(0)' class='f_saved-filter " . $activeClass . "'
                           ngs-filter-id='" . $favoriteFilter->getId() . "' ngs-filter='" . $favoriteFilter->getFilter() . "'>
                          <span>
                               <span class='f_filter-display-name'>" . $favoriteFilter->getName() . "</span>
                               <i class='icon-delete f_delete-filter'></i>
                           </span>
                        </a>";
        }

        $result .= '<a href="javascript:void(0)" class="f_saved-filter f_copy-favorite-filter" style="display:none;">
           <span><span class="f_filter-display-name"></span><i class="icon-delete f_delete-filter"></i></span>
          </a>
         </div>';

        return $result;
    }
}
