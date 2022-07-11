<!DOCTYPE html>
<html lang="en">
<head>
    {block name="head"}
        {block name="header_meta"}
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <meta name="viewport" content="initial-scale=1.0,width=device-width">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap"
                  rel="stylesheet">
            <link rel="shortcut icon" type="image/x-icon" href="{ngs cmd=get_static_path}/img/favicon.ico">
            <link rel="icon" type="image/x-icon" href="{ngs cmd=get_static_path}/img/favicon.ico" sizes="16x16"/>
            <link rel="apple-touch-icon" href="{ngs cmd=get_static_path}/img/favicon.ico"/>
            <title>{block name="page_title"}NGS CMS{/block}</title>
        {/block}
        {block name="header_controls"}
            {include file="{ngs cmd=get_template_dir ns='ngs-AdminTools'}/util/headerControls.tpl"}
        {/block}
    {/block}
</head>
<body class="{block name='cms_main-container-class'}g_fixed-content-style{/block}">
{block name='cms_body'}
    <main class="main-section">
        {block name="left_bar"}
            <aside id="navBar" class="main-nav no-scroll-bar bg-aside">
                {block name="left_bar_content"}
                    <div class="logo-content">
                        <div class="logo-block">
                            {block name="main_logo"}
                                <div class="logo-box">
                                    NGS
                                </div>
                                <span>CMS</span>
                            {/block}
                        </div>
                    </div>
                    <ul id="slide-out" class="side-nav medium1">
                        {block name="nav_bar_content"}
                            <li class="nav-item">
                                <a class="collapsible-header-item f_menu" href="javascript:void(0);">
                                    <i class="icon-svg1"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                        {/block}
                    </ul>
                {/block}

                <div id="minimize-button" class="minimize-button f_minimize-button">
                    <button class="bg-aside">
                        <i class="icon-svg17l"></i>
                        <i class="icon-svg17"></i>
                    </button>
                </div>
            </aside>
            <div id="main-overlay-for-all-purposes" class="main-overlay-for-all-purposes"></div>
        {/block}
        <div class="content-box bgweb2">
            <section id="main_container" class="main-container">
                {nest ns=content}
            </section>

            {block name='cms_footer'}
            {/block}
        </div>
    </main>
    <div id="modal" class="modal ngs-modal">{nest ns=cms_modal}</div>
    <div id="secondModal" class="modal ngs-modal"></div>
    <div id="modalPiker" style="display: block;z-index: 2000" class="modal"></div>
    {include file="{ngs cmd=get_template_dir ns='ngs-AdminTools'}/util/dialog.tpl"}
    <div id="ajax_loader" class="ajax-loader">
        {include file="{ngs cmd=get_template_dir ns='ngs-AdminTools'}/util/svg/Rolling-1s-200px.svg"}
    </div>
    {block name='additional-container'}
        <div id="exportExcelOverlay" class="modal-overlay"></div>
        <div id="exportExcelContainer" class="modal ngs-modal">
            <div class="g_scrolable-section">
                <div class="g_scrolable-fixed-box edit-box form form-box box">
                    <div class="g_scrolable-section">
                        <div class="g_fixed-box modal-title-box border">
                            <div class="t4">Export Excel</div>
                        </div>
                        <div class="g_scrolable-fixed-box modal-content-box">
                            <div class="g_scrolable-fixed-inner-box g-content">
                                <div class="g-content-item">
                                    <div class="g-content-item-wrapper">
                                        <div class="g-content-item-inner g_overflow-y-auto">
                                            <h4 class="t4">Create new Template</h4>
                                            <div class="form-item">
                                                <div class="input-field">
                                                    <label for="templateName">Template Name</label>
                                                    <input type="text" id="templateName" placeholder="Name">
                                                </div>
                                            </div>

                                            <div id="productRelatedCategoriesTree" class="categories-tree">
                                                <div class="text-center t5 is_hidden f_existing-templates-title mt-2">
                                                    Select
                                                    Template
                                                </div>
                                                <ul class="existing-templates large1 f_existing-templates"></ul>

                                                <span class="text-danger add-category-message f_export_message"></span>
                                            </div>

                                            <div class="add-template-box">
                                                <button type="button"
                                                        class="button outline primary with-icon min-width f_save-template">
                                                    <i class="icon-svg179 left-icon"></i>
                                                    <span>Save</span>
                                                </button>
                                            </div>

                                            <div class="space"></div>

                                            <div class="checkbox-item">
                                                <label for="perCompany" class="export-per-company f_export-per-company"
                                                       style="display: none;">
                                                    <input type="checkbox" name="per_company" id="perCompany"
                                                           class="filled-in check-item f_check-item">
                                                    <span></span>
                                                    Export products per company
                                                </label>
                                            </div>

                                            <div class="space"></div>

                                            <div class="space"></div>

                                            <div class="form-item  select-fields-container f_select-fields-container">
                                                <ul class="select-fields-header select-fields-row ">
                                                    <li>Sort</li>
                                                    <li>System Column Name</li>
                                                    <li>Export Column Name</li>
                                                    <li>Exclude</li>
                                                </ul>

                                                <div class="select-fields-info-container f_select-fields-info-container">


                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="g_fixed-box modal-action-box f_form-actions">
                            <button type="button" class="button min-width basic light f_cancel-export-excel">
                                Cancel
                            </button>
                            <button type="button" class="button min-width basic primary f_export-excel">
                                Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="show_notifications" class="show-notifications bgweb3">
        </div>
        <template id="existingTemplateToCreate">
            <div class="template-container f_template-container">
                <span class="template f_template">{literal}${name}{/literal}</span>
                <span class="button btn-link outline with-icon delete-template f_delete-template"
                      data-im-id="{literal}${id}{/literal}">
                <i class="icon-delete-trash"></i>
            </span>
            </div>
        </template>
    {literal}
        <template id="selectFieldColumnToCreate">
            <ul class="select-fields-info-row  f_sortable-field" data-id="${id}">
                <li>
                    <button class="button small btn-link outline with-icon drag-indicator-btn f_drag-indicator_btn"
                            data-id="${id}">
                        <i class="icon-drag-handle"></i>
                    </button>
                </li>
                <li class="column-system-name f_column-system-name">${value}</li>
                <li class="column-export-name "><input type="text" class="f_column-export-name" value="${value}"></li>
                <li class="f_check-select-fields check-item  left-align">
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" class="filled-in check-item  f_check-select-field"/>
                            <span class="checkbox-span"></span>
                        </label>
                    </div>
                </li>
            </ul>
        </template>
    {/literal}
    {/block}
{/block}
</body>
</html>
