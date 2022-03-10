<?php

/**
 * form block plugin for smarty
 */

namespace ngs\AdminTools\templater\plugins\blocks;


abstract class AbstractTabBlockSmartyPlugin extends AbstractBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    abstract public function getPluginName(): string;

    abstract public function getParentPluginName(): string;

    /**
     *
     * @param $params
     * @param $content
     * @param $template
     * @param $repeat
     * @return string|null
     */
    public function index($params, $content, $template, &$repeat): ?string
    {
        if ($repeat) {
            $variables = $this->getPluginVariables($this->getParentPluginName());
            if (!isset($variables['tabs'])) {
                $variables['tabs'] = [];
            }
            $variables['tabs'][] = $params['name'];
            $this->updatePluginVariables($variables, $this->getParentPluginName());

            return null;
        }

        if (!$this->formHasInnerGroup($content)) {
            $content = $this->getContentWithoutBlock($content);
        }

        $templateParams = [
            'content' => $content,
            'tabName' => str_replace(' ', '_', $params['name'])
        ];

        return $this->getBlockTemplate($templateParams);
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
        return '<ul class="form-content f_cms_tab-container" id="' . $params['tabName'] . '_tab">' . $params['content'] .
            '</ul>';
    }


    /**
     * updates content without block
     *
     * @param $content
     * @return string
     */
    private function getContentWithoutBlock($content)
    {
        $flexClass = " ";
        if(strpos( $content, 'upload-image-left' ) !== false){
            $flexClass = " no-flex-wrap";
        }
        $fieldsCount = $this->getCountOfGroupItems($content);

        if($this->formHasListing($content)) {
            return '<li class="bgweb3 ngs-block-form-content-item form-content-count-' . $fieldsCount . '">' . $content .
                '</li>';
        }
        return '<li class="bgweb3 ngs-block-form-content-item form-content-item form-content-count-' . $fieldsCount . '"><div class="form-items-container">' . $content .
            '</div></li>';

    }




    /**
     * if form block contains group in it
     *
     * @param $content
     *
     * @return bool
     */
    private function formHasInnerGroup($content): bool
    {
        return strpos($content, 'ngs-block-form-content-item') !== false;
    }

    private function formHasListing($content): bool
    {
        return strpos($content, 'f_list-load-container') !== false;
    }



    /**
     * returns count of group items
     *
     * @param $content
     * @return int
     */
    private function getCountOfGroupItems($content): int
    {
        return substr_count($content, "form-item");
    }



}
