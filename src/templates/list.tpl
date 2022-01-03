{block name="cms-list"}
    {block name="cms-filter-options"}

    {/block}
    <div class="g_scrolable-section action-tables table-box card-box bgweb3">
        <div class="g_fixed-box table-header">
            {block name="cms-bulk-action"}
                <div class="bulk-box dropdown">
                    <button class="button basic primary float-left bulk-action dropdown-toggle" type="button">
                   <span>Bulk actions
                       <i class="icon-svg3 arrow-item"></i>
                   </span>
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <div class="dropdown-box">
                        <a href="javascript:void(0)" class="f_bulk-action" data-type="export_excel">Export (.xls)</a>
                        <a href="javascript:void(0)" class="f_bulk-action" data-type="delete">Delete</a>
                    </div>
                </div>
            {/block}
            <div class="ngs-filter" id="mainFilter">
                <div class="active-filters f_active-filters">
                    <div class="input-field">
                        {block name="cms-main-content-header"}
                            <div class="page-box">
                                <div class="center-box">

                                    <div class="active-filters search-box-save f_active-filters">
                                        {*                            <span class="add-criteria f_filter-add-criteria"><i class="filter material-icons dp48">filter_list</i>Filters<i class="arrow material-icons dp48">keyboard_arrow_down</i></span>*}
                                        <div class="criteria-box f_criteria-box"></div>
                                        <input type="text" class="input-search f_search-criteria"
                                               placeholder="Search...">
                                    </div>

                                </div>
                                {*                             <div class="right-box">*}
                                {*                                 {block name="cms-main-content-right-box"}*}
                                {*                                 {/block}*}
                                {*                             </div>*}
                            </div>
                        {/block}
                    </div>
                    <div class="filter-favorite-box">
                        <button class="small with-icon button outline-light-basik primary f_filter-add-criteria">
                            <i class="fas fa-filter"></i>
                        </button>
                        <div class="favorite-box dropdown from-right">
                            <button class="small with-icon button outline-light-basik primary dropdown-toggle">
                                <i class="fas fa-star"></i>
                            </button>
                            <div class="dropdown-box f_favorite-filter" ngs-filter-type="{$ns.itemType}">
                                <div class="saved-filters f_saved-filters">  {* Saved filters go this box, and which filter is selected will be with active class*}
                                    {foreach from=$ns.favoriteFilters item=favoriteFilter}
                                        <a href="javascript:void(0)"
                                           class='f_saved-filter {if $ns.favoriteFilter && $ns.favoriteFilter == $favoriteFilter->getName()}active{/if}'
                                           ngs-filter-id={$favoriteFilter->getId()} ngs-filter='{$favoriteFilter->getFilter()}'>
                                            <span><span class="f_filter-display-name">{$favoriteFilter->getName()}</span><i
                                                        class="icon-trash f_delete-filter"></i></span>
                                        </a>
                                    {/foreach}
                                    {* to copy by js *}
                                    <a href="javascript:void(0)" class='f_saved-filter f_copy-favorite-filter'
                                       style="display:none;">
                                        <span><span class="f_filter-display-name"></span><i
                                                    class="fas fa-trash f_delete-filter"></i></span>
                                    </a>
                                </div>
                                <div class="input-field">
                                    <input class="small1 f_filter-name" placeholder="Quotations"/>
                                    <button class="apply-criteria button basic primary small f_save-filter">Save
                                    </button>
                                    <span class="error f_error-message"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {*             <div class="more-actions-box dropdown from-right" data-placement="bottom-end">*}
            {*              <button class="button outline dropdown-toggle">*}
            {*               <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical mx-auto"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>*}
            {*              </button>*}
            {*              <div class="dropdown-box">*}
            {*               <div class="checkbox-item">*}
            {*                <label for="created_input">*}
            {*                 <input type="checkbox" class="filled-in check-item f_check-item" id="created_input"/>*}
            {*                 <span></span>*}
            {*                 Created*}
            {*                </label>*}
            {*               </div>*}
            {*               <div class="checkbox-item">*}
            {*                <label for="country_input">*}
            {*                 <input type="checkbox" class="filled-in check-item f_check-item" id="country_input"/>*}
            {*                 <span></span>*}
            {*                 Country*}
            {*                </label>*}
            {*               </div>*}
            {*               <div class="checkbox-item">*}
            {*                <label for="type_input">*}
            {*                 <input type="checkbox" class="filled-in check-item f_check-item" id="type_input"/>*}
            {*                 <span></span>*}
            {*                 Type*}
            {*                </label>*}
            {*               </div>*}
            {*              </div>*}
            {*             </div>*}
        </div>
        <div class="g_scrolable-box table-box-inner">
            <div class="table bordered_t action_t {block name="cms-table-additional-classes"}{/block}">
                {block name="cms-list-header"}
                    <ul id="gridHeader" class="table-row table-head">

                        {block name="cms-list-header-additional-before"}
                        {/block}
                        {block name="cms-list-header-chechbox-content"}
                            <li class="f_check-items check-items left-align">
                                <div class="checkbox-item">
                                    <label>
                                        <input type="checkbox" class="filled-in check-item f_check-item"/>
                                        <span></span>
                                    </label>
                                </div>
                            </li>
                        {/block}
                        {foreach from=$ns.visibleFields key=field item=fieldInfo}
                            <li id="{$fieldInfo["data_field_name"]}"
                                    {if $fieldInfo["sortable"]} class="f_sorting sorted {if isset($ns.sortingParam[$fieldInfo["data_field_name"]])} {$ns.sortingParam[$fieldInfo["data_field_name"]]}{/if}" {/if}
                                data-im-sorting="{$fieldInfo["data_field_name"]}"
                                    {if isset($ns.sortingParam[$fieldInfo["data_field_name"]])} data-im-order="{$ns.sortingParam[$fieldInfo["data_field_name"]]}"{/if}>
                                {if isset($fieldInfo["display_name"])}
                                    {$fieldInfo["display_name"]}
                                {else}
                                    {$field}
                                {/if}
                            </li>
                        {/foreach}
                        {if $ns.actions|@count}
                            <li class="action right-align">
                            </li>
                        {/if}
                    </ul>
                {/block}
                {block name="cms-list-content"}
                    <div id="itemsContent" class="table-row-group f_cms-table-container">
                        {foreach from=$ns.itemDtos item=itemDto key=index name=itemDto}
                            <ul class="table-row f_table_row {block name="cms-list-content-row-class"}{/block} {if $itemDto->getStatus() AND $itemDto->getStatus() == 'inactive'} inactive {/if}"
                                data-im-id="{$itemDto->getId()}" data-im-index="{$index}">
                                {block name="cms-list-content-additional-before"}

                                {/block}
                                {block name="cms-list-content-chechbox-content"}
                                    <li class="f_check-items check-items left-align">
                                        <div class="checkbox-item">
                                            <label>
                                                <input type="checkbox" class="filled-in check-item f_check-item"/>
                                                <span></span>
                                            </label>
                                        </div>
                                    </li>
                                {/block}
                                {foreach from=$ns.visibleFields key=field item=fieldInfo}
                                    <li class="mobile-view">
                                        {if isset($fieldInfo["display_name"])}
                                            {$fieldInfo["display_name"]}
                                        {else}
                                            {$field}
                                        {/if}
                                    </li>
                                    <li class="f_{$field|replace:'get':''}">
                                        {$itemDto->$field()}
                                    </li>
                                {/foreach}
                                {if $ns.actions|@count}
                                    <li class="f_actions-box actions-box right-align">
                                        {block name="actions"}
                                            {*<div class="buttons-items-box">*}
                                            {foreach from=$ns.actions item=action name=action}
                                                <button class="button btn-link outline with-small-icon {$action}-btn f_{$action}_btn dark"
                                                        data-im-id="{$itemDto->getId()}">
                                                    {if $action == "play" }
                                                        <i class="icon-play"></i>
                                                    {elseif $action == "reject"}
                                                        cancel
                                                    {elseif $action == "delete"}
                                                        <i class="icon-delete-trash"></i>
                                                        {elseif $action == "edit"}
                                                        <i class="icon-edit"></i>
                                                        {elseif $action == "approve"}
                                                            check_circle{elseif $action == "tracks"}
                                                            music_note{elseif $action == "duplicate"}
                                                            filter_none{elseif $action == "events"}event{else}
                                                            visibility
                                                        {/if}
                                                    </i>
                                                </button>
                                            {/foreach}
                                            {*</div>*}
                                        {/block}
                                        {*<a href="javascript:void(0);" class="more-btn f_more-actions-btn"><i class="material-icons">more_vert</i></a>*}
                                    </li>
                                {/if}
                            </ul>
                        {/foreach}
                    </div>
                {/block}
            </div>
        </div>
        {block name="cms-list-pagination"}
            <div class="g_fixed-box action-bar nomargin">
                {include file="{ngs cmd=get_template_dir ns='ngs-cms'}/util/paging_box.tpl"}
            </div>
        {/block}
    </div>
{/block}

{block name="custom-popup"}

{/block}

{block name="list-item-tempalte-block"}
    <template class="f_listItemTemplate">
        <ul class="table-row f_table_row" data-im-index="{literal}${item_index}{/literal}">
            <li class="f_check-items check-items left-align">
                <div class="checkbox-item">
                    <label>
                        <input type="checkbox" class="filled-in check-item f_check-item"/>
                        <span></span>
                    </label>
                </div>
            </li>
            {foreach from=$ns.visibleFields key=field item=fieldInfo}
                <li>{literal}${{/literal}{$fieldInfo['data_field_name']}{literal}}{/literal}</li>
            {/foreach}
            {literal}
                <li class="right-align">
                    <button title="Edit" class="button small btn-link outline with-icon edit-btn f_edit_btn dark" data-im-id="${id}">
                        <i class="icon-edit"></i>
                    </button>
                    <button title="Delete" class="button small btn-link outline with-icon delete-btn f_delete_btn dark" data-im-id="${id}">
                        <i class="icon-delete-trash"></i>
                    </button>
                </li>
            {/literal}
        </ul>
    </template>
{/block}
