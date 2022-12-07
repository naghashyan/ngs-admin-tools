<?php

/**
 * text function plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\functions;

class NgsViewDateFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsViewDate';
    }


    /**
     * main function which will be called when plugin used in frontend
     * @param $params
     * @param $template
     * @return string
     * @throws \ngs\exceptions\DebugException
     */
    public function index($params, $template): string
    {
        $helpText = $this->getHelpText($params);
        $fieldGetter = 'get' . $this->underlinesToCamelCase($params['name']);
        $dto = $this->getDtoFromVariables();
        if(!$dto->hasReadAccess($params['name'])){
            return "";
        }

        $fieldValue = $dto->$fieldGetter();

        if(!$fieldValue) {
            $currentTime = date('Y-m-d h:i:s');
            $dateInnerValue = strftime('%d %B %Y', strtotime($currentTime));
        }else {
            $dateInnerValue = strftime('%d %B %Y', strtotime($fieldValue));
        }

        $templateParams = [
            'sage_sync' => isset($params['sync_with_sage']) && $params['sync_with_sage'],
            'h2_sync' => isset($params['sync_with_h2']) && $params['sync_with_h2'],
            'displayName' => isset($params['display_name']) ? $params['display_name'] : $this->getDisplayName($params['name']),
            'innerText' =>  $dateInnerValue,
            'helpText' => $helpText
        ];

        return $this->getFunctionTemplate($templateParams);
    }


    /**
     * this function should returns string which will be used in function plugin
     *
     * @param array $params
     * @return string
     */
    protected function getFunctionTemplate(array $params): string
    {
        $syncSage = $params['sage_sync'] ? '<i class="icon-sage-logo-svg syncable-field-icon"><div class="tooltip">Sage field</div></i>' : '';
        $syncSage .= $params['h2_sync'] ? '<i class="icon-master-icon master-field-icon"><div class="tooltip">Catalog master field</div></i>' : '';

        return '<div class="form-item view-mode">
                    
                    <div class="input-field">
                        <label>' .$params['displayName']. '</label>' . $syncSage . '
                        <span class="view-text f_form-item-view-mode">' .$params['innerText']. '</span>
                    </div>
                    <div class="icons-box">
                        ' .$params['helpText']. '
                    </div>
                </div>';
    }
}
