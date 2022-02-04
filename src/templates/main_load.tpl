{block name="cms-main"}
    {block name="cms-main-header"}
        {block name="header"}
            <header class="header-box">
                <div class="header-box-inner border">
                    {*            <a id="showHideMenu" href="javascript:void(0);"><i class="material-icons">menu</i></a>*}
                    <div class="left-box">
                        {block name="cms-main-header-breadcrumb"}
                            <a href="javascript:void(0);" class="t2">{$ns.sectionName}</a>
                        {/block}
                    </div>
                    <div class="right-box">
                        {block name="cms-main-header-page-actions"}
                            {*todo: need to move from here*}
                            <div class="notifi-box">
                                <a title="Notification" href="javascript:void(0);" class="small with-icon button light notify-icon f_notification-icon is_bullet">
                                    <i class="icon-notifi"></i>
                                    <span class="notifi-count f_notifi-count"></span>
                                </a>

                                <div id="unread_notifications_container" class="unread-notifications bgweb3">
                                    <div class="unread-notifications-title border">
                                        <div class="t2">Notifications</div>

                                        <div class="notification-more-btn dropdown">
                                            <button class="button medium-button with-icon dropdown-toggle f_delete-all-notifications-btn" type="button">
                                                <i class="icon-svg155"></i>
                                            </button>
                                            <div class="dropdown-box show notifications-action-container">
                                                <a href="javascript:void(0)" class="f_bulk-action" data-type="delete">Delete</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="unread_notifications_content" class="notification-content"></div>
                                </div>

                                <template id="notificationTemplate">
                                    <div class="notification-container border f_notification">
                                        <h3 class="t5 title f_title"></h3>
                                        <h4 class="t6 content f_content"></h4>
                                        <div class="progress-container extrasmall f_progress" data-percent="0">
                                            <div class="progress-bar">
                                                <div class="progress-bar-inner f_progress-inner"></div>
                                            </div>
                                        </div>
                                        <span class="remove-notification f_remove-notification"><i class="icon-svg257"></i></span>
                                    </div>
                                </template>
                            </div>
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
