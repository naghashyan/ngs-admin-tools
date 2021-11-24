<?php

/**
 * tabbed form block plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


class NgsTabbedFormBlockSmartyPlugin extends AbstractTabbedFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsTabbedForm';
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
            $variables = $this->getPluginVariables();
            $tabs = $variables['tabs'];
            if (!$tabs) {
                return "";
            }

            $tabsTags = $this->getTabsContent($tabs);

            $templateParams = [
                'content' => $content,
                'tabsTags' => $tabsTags,
                'dtoId' => '',
                'dtoUpdated' => ''
            ];

            $dto = $this->getDtoFromVariables();

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
        return '<form onsubmit="return false;" class="g_scrolable-fixed-inner-box g_scrolable-section edit-form f_addUpdateForm"><div class="g_scrolable-fixed-box row g-content">' . $params['dtoId'] . $params['dtoUpdated'] .
                    $params['tabsTags'] . '
                        <div class="g-content-item vertical-tabs-content col-auto f_vertical-tabs-content"><div class="g-content-item-wrapper"><div class="f_g-content-item-inner g-content-item-inner g_overflow-y-auto">' . $params['content'] .
                        '</div></div></div></div>
                     <div class="g_fixed-box form-action f_form-actions">
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


    /**
     * returns tabs content
     *
     * @param $tabs
     *
     * @return string
     */

}
