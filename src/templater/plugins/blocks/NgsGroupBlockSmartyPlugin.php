<?php

/**
 * group block plugin for smarty
 */

namespace ngs\NgsAdminTools\templater\plugins\blocks;


class NgsGroupBlockSmartyPlugin extends AbstractBlockSmartyPlugin
{

    /**
     * returns plugin name
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return 'ngsGroup';
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
            $templateParams = [
                'content' => $content,
                'blockName' => $params['name'],
                'fieldsCount' => $this->getCountOfGroupItems($content),
                'addClass' => isset($params['class_form_item']) ? " " . $params['class_form_item'] : " "
            ];

            return $this->getBlockTemplate($templateParams);
        }

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
        $flexClass = " ";
        if(strpos( $params['content'], 'upload-image-left' ) !== false){
            $flexClass = " no-flex-wrap";
        }
        return '<li id="item-' . strtolower(str_replace(' ', '-', $params['blockName'])) . '-group"
                        class="bgweb3 form-content-item form-content-count-' . $params['fieldsCount'] . '">
                        <div class="form-item-group-name">' . $params['blockName'] . '</div><div class="form-items-container'.$params['addClass'] .$flexClass. '">' . $params['content'] .
            '</div></li>';
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
