<?php

/**
 * form block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


class NgsViewFormBlockSmartyPlugin extends AbstractFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsViewForm';
    }

    /**
     * main function which will be called when plugin used in frontend
     * this function will be called twice, one when block tag opened, and second time when block tag closed
     * at first time $repeat parameter will be true, second time will be false
     *
     * @param $params
     * @param $content
     * @param $template
     * @param $repeat
     *
     * @return string|null
     */
    public function index($params, $content, $template, &$repeat): ?string
    {
        if (!$repeat) {

            $flexClass = " ";
            if(strpos( $content, 'upload-image-left' ) !== false){
                $flexClass = " no-flex-wrap";
            }
            if (!$this->formHasInnerGroup($content)) {
                $fieldsCount = $this->getCountOfGroupItems($content);
                $content = $this->addGroupIfNotExists(['content' => $content, 'fieldsCount' => $fieldsCount, 'flexClass' => $flexClass]);
            }

            $templateParams = [
                'content' => $content
            ];

            $this->deletePluginVariables();

            return $this->getBlockTemplate($templateParams);
        }

        $this->addPluginVariables($params);
        return null;
    }


    /**
     * returns content of block
     *
     * @param array $params
     *
     * @return string
     */
    protected function getBlockTemplate(array $params): string
    {
        return '<form onsubmit="return false;" class="g_scrolable-section f_addUpdateForm edit-form">
                    <div class="g_scrolable-fixed-box g-content f_vertical-tabs-content g_overflow-y-auto">
                                        <div class="f_g-content-item-inner g-content-item-inner col-12">
                                            <ul class="form-content f_cms_tab-container">'
                                                     . $params['content'] .
            '</ul>
                                        </div>
                                     </div>
                </form>';
    }


}
