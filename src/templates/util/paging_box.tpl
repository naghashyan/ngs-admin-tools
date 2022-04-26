<div id="f_pageingBox" data-im-page="{$ns.page}" data-im-page-count="{$ns.pageCount}" data-im-limit="{$ns.limit}" class="pagging-box">
	<div class="pagination-box dataTables_info">
		<div class="page-ctrl">
			<span>Page: </span>
			<div class="input-field col s6">
				<input class="no-right-padding" id="f_go_to_page" type="text" value="{$ns.page}">
			</div>
			<span> of {$ns.pageCount} | View </span>

			<div class="input-field">
				<select id="f_count_per_page">
					{foreach from=$ns.itemsPerPageOptions item=perPageCount}
						<option {if $ns.limit==$perPageCount}selected{/if}>{$perPageCount}</option>
					{/foreach}
				</select>
			</div>
			<span class="items-count"> items | <span class="items-count-main">{$ns.itemsCount} items</span></span>
		</div>
	</div>
	<div class="pagination-box">
		{if $ns.pageCount>1}
			<ul id="pageBox" class="pagination">
				<li class="waves-effect">
					<a href="javascript:void(0);" class="f_page {if $ns.page<=1}disabled{/if}"
					   data-im-page="1">First</a>
				</li>
				<li class="waves-effect">
					<a href="javascript:void(0);" class="f_page {if $ns.page<=1}disabled{/if}"
					   data-im-page="{$ns.page-1}"><i class="icon-svg17l"></i></a>
				</li>
				{section name=pages loop=$ns.pEnd start=$ns.pStart}
					{if ($ns.page) != ($smarty.section.pages.index+1)}
						<li class="waves-effect">
							<a class="f_page" data-im-page="{$smarty.section.pages.index+1}"
							   href="javascript:void(0);">{$smarty.section.pages.index+1}</a>
						</li>
					{else}
						<li class="waves-effect active">
							<a href="javascript:void(0);" class=>{$smarty.section.pages.index+1}</a>
						</li>
					{/if}
				{/section}
				<li class="waves-effect">
					<a href="javascript:void(0);" class="f_page {if ($ns.page)==$ns.pageCount}disabled{/if}"
					   data-im-page="{$ns.page+1}"><i class="icon-svg17"></i></a>
				</li>
				<li class="waves-effect">
					<a href="javascript:void(0);" class="f_page {if ($ns.page)==$ns.pageCount}disabled{/if}"
					   data-im-page="{$ns.pageCount}">Last</a>
				</li>
			</ul>
		{/if}
	</div>
</div>