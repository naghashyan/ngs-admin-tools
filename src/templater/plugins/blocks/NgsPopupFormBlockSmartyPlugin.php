<?php

/**
 * form block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


class NgsPopupFormBlockSmartyPlugin extends AbstractPopupFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsPopupForm';
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

            $dto = $this->getDtoFromVariables();
            $templateParams = [
                'content' => $content,
                'display_name' => $params['display_name'] ?? ' ',
                'dtoId' => '',
                'dtoUpdated' => '',
                'className' => $params['className'] ?? ' ',
                'header' => !(isset($params['no_header']) && $params['no_header']),
                'footer' => !(isset($params['no_footer']) && $params['no_footer']),
                'save_and_close_buttons' => !(isset($params['no_save_and_close_buttons']) && $params['no_save_and_close_buttons']),
                'close_btn_icon' => !(isset($params['no_close_btn_icon']) && $params['no_close_btn_icon']),
                'custom_header' => isset($params['custom_header']) ? $params['custom_header'] : ''
            ];

            if ($dto && $dto->getId()) {
                $templateParams['dtoId'] = '<input type="hidden" name="id" value="' . $dto->getId() . '" id="currentItemId">';
                if (method_exists($dto, 'getUpdated') && $dto->getUpdated()) {
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
        //TODO: should be rechecked and modified

        $header = $this->getHeaderOfPopupForm($params['header'], $params['display_name']);
        $footer = $this->getFooterOfPopupForm($params['footer'], $params['save_and_close_buttons']);
        $closeButtonIcon = $this->getCloseBtnIconOfPopupForm($params['close_btn_icon']);

        return '<div class="g_scrolable-section">
                    
                    <form onsubmit="return false;" class="g_scrolable-section f_addUpdateForm edit-form '. $params['className'] . '">'
            . $header .
            $closeButtonIcon .
            $params['dtoId'] . $params['dtoUpdated'] .
            '<div class="g_scrolable-fixed-box modal-content-box">
                            <div class="g_scrolable-fixed-inner-box g-content row">
                                <div class="g-content-item add-categories">
                                    <div class="g-content-item-wrapper">
                                        <div class="g-content-item-inner g_overflow-y-auto f_cms_tab-container">'
            . $params['content'] .
            '</div>
                                    </div>
                                </div>
                            </div>
                         </div>'
            . $footer .
            '</form>
                </div>';
    }

    /**
     * this function adds X close icon if its default, or empty space if property 'no_close_btn_icon' was passed from template
     * @param $closeBtn
     * @return string
     */
    private function getCloseBtnIconOfPopupForm($closeBtn): string
    {
        if ($closeBtn) {
            return '<button class="f_close-popup-button popup-close-btn button small link-btn dark with-icon"><i class="icon-svg257"></i></button>';
        }
        return ' ';
    }


}
