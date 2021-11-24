<?php

/**
 * form block plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


class NgsFormBlockSmartyPlugin extends AbstractFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsForm';
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

            $dto = $this->getDtoFromVariables();
            $templateParams = [
                'content' => $content,
                'dtoId' => '',
                'dtoUpdated' => ''
            ];

            if ($dto && $dto->getId()) {
                $templateParams['dtoId'] = '<input type="hidden" name="id" value="' . $dto->getId() . '" id="currentItemId">';
                if(method_exists($dto, 'getUpdated') && $dto->getUpdated()) {
                    $templateParams['dtoUpdated'] = '<input type="hidden" name="updated_at" value="' . $dto->getUpdated() . '">';
                }
            }

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
        return '<form onsubmit="return false;" class="g_scrolable-fixed-inner-box g_scrolable-section edit-form f_addUpdateForm">
                    <div class="g_scrolable-fixed-box g-content f_vertical-tabs-content g_overflow-y-auto">' . $params['dtoId'] . $params['dtoUpdated'] . '
                         <div class="f_g-content-item-inner g-content-item-inner col-12">
                            <ul class="form-content f_cms_tab-container">' . $params['content'] .
            '</ul>
                        </div>
                    </div>
                    <div class="g_fixed-box modal-action-box form-action f_form-actions">
                        <button class="button min-width basic light f_cancel" type="button">
                            Cancel
                        </button>
                        <button type="submit" class="button min-width basic primary f_saveItem">
                            Save
                        </button>
                        <div class="clear"></div>
                    </div>
                </form>';
    }

}
