{block name="cms-main"}
    {block name="cms-main-header"}
        {block name="header"}
            <header class="header-box">
                <div class="header-box-inner border">
                    {*            <a id="showHideMenu" href="javascript:void(0);"><i class="material-icons">menu</i></a>*}
                    <div class="left-box">
                        {block name="cms-main-header-mobile-menu"}
                            <a href="javascript:void(0);" class="mobile-menu-open-btn f_minimize-button f_mobile-menu-open-btn">
                                <span></span>
                                <span></span>
                                <span></span>
                            </a>
                        {/block}

                        {block name="cms-main-header-breadcrumb"}
                            <a href="javascript:void(0);" class="t2 title-box">{$ns.sectionName}</a>
                        {/block}
                    </div>
                    <div class="center-box">
                        {block name="cms-main-header-mobile-logo"}
                            <a href="{ngs cmd=get_http_host}" class="header-logo-box">
                                <img src="{ngs cmd=get_http_host ns='admin'}/img/logo-mobile-header.png">
                            </a>
                        {/block}
                    </div>
                    <div class="right-box">
                        {block name="cms-main-header-page-actions"}
                           

                            {block name="cms-main-notifications"}

                            {/block}
                            <div id="profile-box-menu" class="profile-box from-right">
                                <a href="javascript:void(0);" class="account-item dropdown-toogle">
                            <span class="circle">
                                <img src="{$ns.profileImage}" alt="">
                            </span>
                                    <span class="name-box medium1">
                                {$ns.firstName} {$ns.lastName}
                                <i class="icon-svg3"></i>
                            </span>
                                </a>

                                <div class="dropdown-box f_profile-box-inner">
                                    <div class="dropdown-title-box medium1">
                                        {$ns.firstName} {$ns.lastName}
                                    </div>

                                    {block name="contex-box-container"}
                                        <div class="content-box">
                                            <a href="javascript:void(0);" class="f_goToProfilePage">
                                                <i class="icon-svg241"></i>
                                                Profile
                                            </a>
                                            <a href="javascript:void(0);" class="f_menu f_doLogout"
                                               data-im-load="admin.loads.main.home">
                                                <i class="icon-svg138"></i>
                                                Logout
                                            </a>
                                        </div>
                                    {/block}
                                </div>
                            </div>
                        {/block}
                    </div>
                </div>
            </header>
        {/block}

        {block name="page-title"}
        {/block}
    {/block}
    {block name="cms-main-content"}
        <section class="f_list-load-container g-content">
            <div class="g-content-item">
                <div class="g-content-item-wrapper">
                    <input class="f_page-selection-info" type="hidden" value=''>

                    {block name="cms-main-content-body"}
                        <div id="loadContent" class="g-content-item-inner">
                            {nest ns=items_content}
                        </div>
                    {/block}
                </div>
            </div>
        </section>
    {/block}
    {block name='additional-container-main-load'}
        <div id="exportExcelOverlay" class="modal-overlay"></div>
        <div id="exportExcelContainer" class="modal ngs-modal">
            <div class="g_scrolable-section">
                <div class="g_scrolable-fixed-box edit-box form form-box box">
                    <div class="g_scrolable-section f_existing-template-content">
                        <div class="g_fixed-box modal-title-box border">
                            <div class="t4">Export Excel</div>
                        </div>
                        <div class="g_scrolable-fixed-box modal-content-box">
                            <div class="g_scrolable-fixed-inner-box g-content">
                                <div class="g-content-item">
                                    <div class="g-content-item-wrapper">
                                        <div class="g-content-item-inner g_overflow-y-auto">

                                   
                                            <div>
                                                <div class="row">
                                                    <div class="text-center col is_hidden f_existing-templates-title mt-2">
                                                        <span class="t5">  Select Template </span>
                                                    </div>

                                                    <div class="col add_new_template_container">
                                                        <button type="button" class="button basic primary f_create-new-template">Create new
                                                            Template
                                                        </button>
                                                    </div>
                                                </div>
                                                <span class="text-danger t5 add-category-message f_export-message"></span>
                                                <ul class="existing-templates large1 f_existing-templates"></ul>

                                            </div>

                                            <div class="space"></div>

                                            <div class="checkbox-item export-checkbox-item">
                                                <label for="perCompany" class="export-per-company f_export-per-company"
                                                       style="display: none;">
                                                    <input type="checkbox" name="per_company" id="perCompany"
                                                           class="filled-in check-item f_check-item">
                                                    <span></span>
                                                    Export products per Catalog Supplier
                                                </label>
                                            </div>

                                            <div class="space"></div>

                                            <div class="checkbox-item export-checkbox-item">
                                                <label for="productImages" class="export-product-images f_export-images"
                                                       style="display: none;">
                                                    <input type="checkbox" name="product-images" id="productImages"
                                                           class="filled-in check-item f_check-item">
                                                    <span></span>
                                                    Export products images
                                                </label>
                                            </div>

                                            <div class="space"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="g_fixed-box modal-action-box f_form-actions">
                            <button type="button" class="button min-width basic light f_cancel-export-excel">
                                Cancel
                            </button>
                            <button type="button" class="button  min-width basic primary is_disabled f_export-excel">
                                Export
                            </button>
                        </div>
                    </div>
                    <div class="g_scrolable-section  is_hidden  f_add-template-content">
                        <div class="g_fixed-box modal-title-box border">
                            <div class="t4">Export Excel</div>
                        </div>
                        <div class="g_scrolable-fixed-box modal-content-box">
                            <div class="g_scrolable-fixed-inner-box g-content">
                                <div class="g-content-item">
                                    <div class="g-content-item-wrapper">
                                        <div class="g-content-item-inner g_overflow-y-auto">
                                            <span class="text-primary medium1 f_create-tempate">Create new Template</span>
                                            <div class="space"></div>
                                            <span class="text-danger t5 add-category-message f_save-template-message"></span>
                                            <div class="space"></div>
                                            <div class="form-item">
                                                <div class="input-field">
                                                    <label for="templateName t1">Template Name</label>
                                                    <input type="text" id="templateName" placeholder="Name">
                                                </div>
                                            </div>

                                            <div class="space"></div>

                                            {if $ns.customizableExportColumns}
                                                <button class="f_add-custom-column button primary soft with-icon outline add-custom-column">
                                                    <i class="left-icon icon-svg180"></i>Add
                                                    Custom column
                                                </button>
                                                <div class="space"></div>
                                            {/if}

                                            <div class="table export-table bordered_t action_t f_select-fields-container">

                                                <ul class="table-row table-head">
                                                    <li>Sort</li>
                                                    <li>System Column Name</li>
                                                    <li>Export Column Name</li>
                                                    <li>Exclude</li>
                                                </ul>

                                                <div class="table-row-group select-fields-info-container f_select-fields-info-container">

                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="g_fixed-box modal-action-box f_form-actions">
                            <button type="button" class="button min-width basic light f_cancel-add-template">
                                Cancel
                            </button>
                            <button data-id="" type="button" class="button  min-width basic primary  f_save-template">
                                Save
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
                <span class="button btn-link outline with-small-icon delete-template f_edit-template"
                      data-im-id="{literal}${id}{/literal}">
                    <i class="icon-edit"></i>
                </span>
                <span class="button btn-link outline with-small-icon delete-template f_delete-template"
                      data-im-id="{literal}${id}{/literal}">
                    <i class="icon-delete-trash"></i>
                </span>
            </div>
        </template>
    {literal}
        <template id="selectFieldColumnToCreate">
            <ul class="table-row  f_sortable-field" data-id="${id}">
                <li>
                    <button class="button small btn-link outline with-icon drag-indicator-btn f_drag-indicator_btn"
                            data-id="${id}">
                        <i class="icon-drag-handle"></i>
                    </button>
                </li>
                <li class="f_column-system-name">
                    <div class="input-field">${systemValue}</div>
                </li>
                <li class="column-custom-name">
                    <div class="input-field"><input type="text" class="f_column-export-name" value="${value}"></div>
                </li>


                <li class="f_check-select-fields check-items left-align">
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" class="filled-in check-item  f_check-select-field"/>
                            <span class="checkbox-span"></span>
                        </label>
                    </div>
                </li>
            </ul>
        </template>
        <template id="selectCustomFieldColumnToCreate">
        <ul class="table-row  f_sortable-field" data-custom-column="1">
        <li>
            <button class="button small btn-link outline with-icon drag-indicator-btn f_drag-indicator_btn">
                <i class="icon-drag-handle"></i>
            </button>
        </li>
        <li class="column-system-name column-custom-name formula-custom-column f_column-system-name">
        <div class="space"></div>
        <div class="input-field">
            <input type="text" class="f_column-export-formula" value="">
        </div>
        <div class="space"></div>
        <div class="icons-box">
        <span class="icon-tooltip f_help-text">
        <i class="icon-svg31">
        <span class="tooltip">Can be used in formula {/literal}
        {foreach from=$ns.customizableExportColumns item=column}
            <b>{$column}</b>
            <br/>
        {/foreach}
    {literal}
        </span>
        </i>
        </span>
        </div>
        </li>
        <li class="column-export-name ">
            <div class="input-field">
                <div class="space"></div>
                <input type="text" class="f_column-export-name" value="${value}">
                <div class="space"></div>
            </div>
        </li>
        <li class="f_check-select-fields check-item  left-align">
            <div class="checkbox-item">
                <label>
                    <input disabled type="checkbox" class="filled-in check-item  f_check-select-field"/>
                    <span class="checkbox-span"></span>
                </label>
            </div>
        </li>
        </ul>
        </template>
    {/literal}
    {/block}
{/block}
