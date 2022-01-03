<?php

/**
 * form block plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


class NgsFlexibleHeightFormBlockSmartyPlugin extends AbstractPopupFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsFlexibleHeightForm';
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
                'display_name' => $params['display_name'] ??  ' ',
                'dtoId' => '',
                'dtoUpdated' => '',
                'header' => !(isset($params['no_header']) && $params['no_header']),
                'footer' => !(isset($params['no_footer']) && $params['no_footer']),
                'save_and_close_buttons' => !(isset($params['no_save_and_close_buttons']) && $params['no_save_and_close_buttons'])
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
        //TODO: should be rechecked and modified

        $header = $this->getHeaderOfPopupForm($params['header'], $params['display_name']);
        $content = $this->getContentOfForm($params['content'], $params['dtoId']);
        $footer = $this->getFooterOfPopupForm($params['footer'], $params['save_and_close_buttons']);


        return '<form onsubmit="return false;" class="bgweb flexible-height-popup f_addUpdateForm">' . $header . $content . $footer . '</form>';
    }


    protected function getHeaderOfPopupForm($header, $displayName): string
    {
        if($header) {
            return '<div class="toast-header-box f_toast-header-box">
                        <h4 class="t4 f_toast-header-box-title-part">' . $displayName . '</h4>
                    </div>';
        }
        return ' ';
    }


    protected function getContentOfForm($content, $hiddenInput) {
        return '<div class="toast-content-box f_toast-content-box">' .$hiddenInput.
                        $content
                  .'</div>';
    }


    protected function getFooterOfPopupForm($footer, $buttons) {
        if($footer) {
            $res =  ' <div class="f_toast-footer-box">';
            if($buttons) {
                $res .= '
                    <div class="popup-buttons">
                        <button type="button" id="" class="button min-width basic light cancel f_cancel">Cancel</button>
                        <button type="button" id="" class="button min-width basic primary cancel f_saveItem">Save</button>
                    </div>';
            }
            $res .= '</div>';
            return $res;
        }
        return ' ';
    }


}
