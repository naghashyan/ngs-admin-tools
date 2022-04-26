{block name="cms-main-header"}
    <div class="page-title inner border table-title">
        <div class="left-box">
            <h2 class="title-box">{$ns.sectionName}</h2>
        </div>
        {block name="addButton"}
        {/block}
    </div>
{/block}
{block name="simple-cms-main"}
    {block name="simple-cms-main-content"}
        <section class="f_list-load-container f_load-container">
            <input class="f_page-selection-info" type="hidden">
            {block name="simple-cms-main-content-body"}
                {nest ns=items_content}
            {/block}
            <!-- Table -->
        </section>
    {/block}
{/block}