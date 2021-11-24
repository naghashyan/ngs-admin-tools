<div id="{if $ns.cmsUUID}{$ns.cmsUUID}{else}modal_container{/if}" class="header-btn-container">
    <a class="header-add-btn button basic primary with-icon min-width f_addItemBtn" href="javascript:void(0);"
       title="Add new {$ns.sectionName}">
        <i class="icon-svg179 left-icon"></i>
        <span>{$ns.sectionName}</span>
    </a>
    {nest ns=items_content}
</div>