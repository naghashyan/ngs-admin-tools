<!DOCTYPE html>
<html lang="en">
<head>
    {block name="head"}
        {block name="header_meta"}
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <meta name="viewport" content="initial-scale=1.0,width=device-width">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
            <link rel="shortcut icon" type="image/x-icon" href="{ngs cmd=get_static_path}/favicon.ico">
            <link rel="icon" type="image/x-icon" href="{ngs cmd=get_static_path}/favicon.ico" sizes="16x16"/>
            <link rel="apple-touch-icon" href="{ngs cmd=get_static_path}/favicon.ico"/>
            <title>{block name="page_title"}NGS CMS{/block}</title>
        {/block}
        {block name="header_controls"}
            {include file="{ngs cmd=get_template_dir ns='ngs-AdminTools'}/util/headerControls.tpl"}
        {/block}
    {/block}
</head>
<body class="blue-color{block name='cms_main-container-class'} g_fixed-content-style{/block}">
{block name='cms_body'}
    <main class="main-section">
        <div id="main-overlay-for-all-purposes" class="main-overlay-for-all-purposes is_hidden"></div>
        {block name="left_bar"}
            <aside id="navBar" class="main-nav no-scroll-bar">
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
{*            <div id="sidebarOverlay" class="sidebar-overlay"></div>*}
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
    {/block}
{/block}
</body>
</html>
