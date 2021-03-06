<?php

/**
 * form block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


abstract class AbstractPopupFormBlockSmartyPlugin extends AbstractFormBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    abstract public function getPluginName(): string;


    /**
     * this function adds header if its default, or empty space if property 'no_header' was passed from template
     * @param $header
     * @param $displayName
     * @param $customHeader
     *
     * @return string
     */
    protected function getHeaderOfPopupForm($header, $displayName, $customHeader = ""): string {
        if($header) {
            return '<div class="g_fixed-box modal-title-box border"><div class="t4">' .
                $displayName .
                '</div></div>';
        }
        return ' ';
    }


    /**
     * this function adds footer if its default, or empty space if property 'no_footer' was passed from template
     * @param $footer
     * @param $buttons
     * @param $customFooter
     * 
     * @return string
     */
    protected function getFooterOfPopupForm($footer, $buttons, $customFooter = "") {
        if($footer) {
            $res =  ' <div class="g_fixed-box modal-action-box f_form-actions">';
            if($buttons) {
                $res .= '
                    <button class="button min-width basic light f_cancel" type="button">
                        Cancel
                    </button>
                    <button class="button min-width basic primary f_saveItem" type="button">
                        Save
                    </button>';
            }
            $res .= $customFooter;
            $res .= '</div>';
            return $res;
        }
        return ' ';
    }


  /**
   * add group if no group exist
   *
   * @param $params
   * @return string
   */
  protected function addGroupIfNotExists($params) {
    return '<div class="bgweb3 ngs-block-form-content-item form-content-item form-content-count-0 form-content-count-' . $params['fieldsCount'] . '"><div class="form-items-container' .$params['flexClass']. '">' .
      $params['content'] .
      '</div></div>';
  }


}
