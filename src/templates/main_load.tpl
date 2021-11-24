{block name="cms-main"}
    {block name="cms-main-header"}
        {block name="header"}
            <header class="header-box bgweb2">
                <div class="header-box-inner border">
                    {*            <a id="showHideMenu" href="javascript:void(0);"><i class="material-icons">menu</i></a>*}
                    <div class="left-box">
                        {block name="cms-main-header-breadcrumb"}
                            <a href="javascript:void(0);" class="t2">{$ns.sectionName}</a>
                        {/block}
                    </div>
                    <div class="right-box">
                        {block name="cms-main-header-page-actions"}
                            <div class="notifi-box">
                                <a title="Notification" href="javascript:void(0);" class="small with-icon button light notify-icon f_notification-icon is_bullet">
                                    <i class="icon-notifi"></i>
                                </a>
                            </div>
                            <div id="profile-box-menu" class="profile-box from-right">
                                <a href="javascript:void(0);" class="account-item dropdown-toogle">
                            <span class="circle">
{*                                <img src="{ngs cmd=get_http_host ns='admin'}/img/profile-image.jpg">*}
                                <img src="{$ns.profileImage}" alt="">
                            </span>
                                    <span class="name-box medium1">
                                {$ns.firstName} {$ns.lastName}
                                <i class="icon-svg3"></i>
                            </span>
                                </a>

                                <div class="dropdown-box f_profile-box-inner">
                                    <div class="content-box">
                                        <a href="javascript:void(0);" class="f_goToProfilePage">
                                            <i class="icon-svg241"></i>
                                            Profile
                                        </a>
                                        <a href="javascript:void(0);" class="f_goToHelpPage">
                                            <i class="icon-question-1"></i>
                                            Help Page
                                        </a>
                                        <a href="javascript:void(0);" class="f_menu f_doLogout" data-im-load="admin.loads.main.home">
                                            <i class="icon-svg138"></i>
                                            Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        {/block}
                    </div>
                </div>
            </header>
        {/block}

        {block name="page-title"}
            <div class="page-title">
                <div class="left-box">
{*                    {block name="left-box"}*}
{*                        <h2 class="title-box">{$ns.sectionName}</h2>*}
{*                    {/block}*}
                </div>
                <div class="center-box">
                    {block name="center-box"}
                        <div class="tooltip-block">
                        </div>
                    {/block}
                </div>
                <div class="right-box">

                    {block name="addButton"}

                    {/block}
                    {block name="editButton"}

                    {/block}

                </div>
            </div>
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
{/block}
