<?php

/**
 * text function plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\functions;

class NgsDropzoneFunctionSmartyPlugin extends AbstractFunctionSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsDropzone';
    }

    /**
     * main function which will be called when plugin used in frontend
     *
     * @param $params
     * @param $template
     *
     * @return string|null
     */
    public function index($params, $template): string
    {

        $class = isset($params['class_form_item']) ? " " .$params['class_form_item'] : "";
        $id = isset($params['id']) ? 'id="' . $params['id'] .'" ' : " ";
        $hasReadAccess = isset($params['imageHasReadAccess']) ? $params['imageHasReadAccess'] : true;
        $hasWriteAccess = isset($params['imageHasWriteAccess']) ? $params['imageHasWriteAccess'] : true;

        if(!$hasReadAccess) {
            return '';
        }

        $templateParams = [
            'class' => $class,
            'id' => $id,
            'hasWriteAccess' => $hasWriteAccess,
            'addClass' => (isset($params['multiple']) && $params['multiple'] == true) ? "f_multipleDropzone" : "f_singleDropzone"
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
        $writeAccess = ' data-write-access="' . ($params['hasWriteAccess'] ? 'true' : 'false')  . '" ';
        return     '<div' . $params['id']. 'class="form-item' .$params['class'] .'">
                        <div ' . $writeAccess . ' class="upload-file-box f_all-dropzones-container">
                            <div class="element-field image-select-box dropzone f_1 ' .$params['addClass']. '">
                            </div>
                            
                        </div>
                    </div>';

    }
}
