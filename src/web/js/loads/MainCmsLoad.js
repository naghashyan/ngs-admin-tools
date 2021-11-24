import AbstractLoad from '../../AbstractLoad.js';
import DialogUtility from '../util/DialogUtility.js';
import M from '../lib/materialize.min.js';
export default class MainCmsLoad extends AbstractLoad {

  constructor() {
    super();
  }


  getContainer() {
    return "main";
  }

  onError(params) {

  }

  afterLoad() {
    // M.AutoInit();
    this.initMenu();
    this.initOpenSearch();
    this.initNavBarMinimizing();
    this.initMainMenuDropdownsToggling();

    /* jQuery("#main_nav .nano").nanoScroller({
       flash: true
     });*/
    $("#m_home").click(function () {
      NGS.load("main.home", {});
    }.bind(this));

    let mainContainer = $("#main_container");

    mainContainer.on("click", ".f_doLogout", function () {
      DialogUtility.showConfirmDialog("Log out", "Are you sure you want to log out ?").then(function (result) {
        NGS.action("admin.actions.main.admin_logout", {})
      }).catch(function () {
        console.log("canceled");
      });
    }.bind(this));

    mainContainer.on("click", ".f_goToProfilePage", function () {
      NGS.load("admin.loads.profilePage.profile_page_main");
    });


    mainContainer.on("click", ".f_goToHelpPage", function () {
      NGS.load("admin.loads.helpPage.help_page_main");
    });


    $(".f_main_menu").click(function () {
      if($(this).closest('.nav-item').hasClass('active')) {
        $(this).closest('.nav-item').removeClass('active');
        $(this).closest('.nav-item').addClass('active-closed');
        $(this).next(".f_main-menu-collaps-body").slideToggle();
        return;
      }
      let activeInnerMenu = $(this).next('.f_main-menu-collaps-body').find('.f_menu.active');
      $(".nav-item.active").find(".f_main-menu-collaps-body").slideToggle();
      document.querySelectorAll('#navBar .nav-item').removeClass('active');
      document.querySelectorAll('#navBar .f_menu, #navBar .nav-item').removeClass('active-closed');
      activeInnerMenu.addClass('active');
      $(this).closest('.nav-item').addClass('active');
      $(this).next(".f_main-menu-collaps-body").slideToggle();
    });

    document.querySelectorAll('.f_menu-btn').click(() => {
      document.querySelector('.f_side-nav').classList.toggle('is-open');
    });


    // $(".f_account-item").click(function () {
    //   $(this).next(".f_profile-dropdown-box").fadeToggle();
    // });

    //Dropdown
    (function($) {
      "use strict";

      //todo: need to delete this event listener from body;
      $('body').on('click', function(event) {

        //separate listeners are added for these targets
        if($(event.target).closest('#profile-box-menu').length) {
          return;
        }
        if($(event.target).closest('.f_favorite-filter-box').length) {
          return;
        }

        let dropdown = $(event.target).closest('.dropdown');
        if (!$(dropdown).length || ($(event.target).closest('.dropdown-toggle').length && $(dropdown).find('.dropdown-box').first().hasClass('show'))) {
          $('.dropdown-box').removeClass('show')
        } else {
          $('.dropdown-box').removeClass('show');
          $(dropdown).find('.dropdown-box').first().addClass('show')
        }
      })
    })($);

    this.afterCmsLoad();
  }


  /**
   * dropdown boxes handling
   */
  initMainMenuDropdownsToggling() {

    document.body.addEventListener('click', (e) => {
      this.handleProfileBoxToggling(e);
      this.handleFavoriteFilterBoxToggling(e);
    })
  }


  /**
   * on profile name or image clicking profile menu open-close
   * @param e
   */
  handleProfileBoxToggling(e) {
    let profileBoxInner = document.querySelector('#profile-box-menu .f_profile-box-inner');
    if(!profileBoxInner) {
      return;
    }

    if(e.target.closest('#profile-box-menu')) {
      document.querySelector('#profile-box-menu i').classList.toggle('opened');
      profileBoxInner.classList.toggle('show');
    }else {
      profileBoxInner.classList.remove('show');
    }
  }


  /**
   * on favoriteFilters (star symbol) clicking it should open-close
   * @param e
   */
  handleFavoriteFilterBoxToggling(e) {
    let favoriteFilterBoxes = document.querySelectorAll('.f_favorite-filter-box');
    if(!favoriteFilterBoxes.length) {
      return;
    }

    if((e.target.closest('.f_favorite-filter-box')) && (!e.target.closest('.f_favorite-filter'))) {
      e.target.closest('.f_favorite-filter-box').querySelector('.f_favorite-filter').classList.toggle('show');
    }else if(!e.target.closest('.f_favorite-filter')) {
      document.querySelectorAll('.f_favorite-filter-box .f_favorite-filter').forEach((el) => {
        el.classList.remove('show');
      })
    }
  }



  initMenu() {
    document.querySelectorAll('#navBar .f_menu').forEach((menuElem) => {
      menuElem.addEventListener('click', (evt) => {
        let closestNavBar = $(evt.target).closest('.nav-item').find('.f_main_menu');
        let menuElem = evt.currentTarget;
        document.querySelectorAll('#navBar .nav-item.active, #navBar .f_menu.active').removeClass('active');

        if(!closestNavBar.length) {
          $("#navBar .nav-item.active").removeClass('active').find('.f_main_menu').next(".f_main-menu-collaps-body").slideToggle();
          document.querySelectorAll('#navBar .nav-item .f_main-menu-collaps-body').forEach((elem) => {
            if(elem.hasAttribute('style')) {
              let styleValue = elem.getAttribute('style').replace(/\s+/g, '');
              if(styleValue.indexOf('display:block') !== -1) {
                elem.setAttribute('style', 'display: none');
              }
            }
          })
        }
        else {
          $(menuElem).addClass("active");
        }
        document.querySelectorAll( '#navBar .nav-item.active-closed').removeClass('active-closed');
        $(menuElem).closest(".nav-item").addClass('active');
        let ngsLoad = menuElem.getAttribute('data-im-load');
        if(!ngsLoad){
          return false;
        }
        NGS.load(ngsLoad, {});
        return false;
      });
    });

  }

  initOpenSearch() {
    $("#main_container").on("click", "#showSearch", function () {
      $(this).toggleClass('is_active');
    });
  }

  initNavBarMinimizing(){
    $("#minimize-button").on("click", function () {
      $("#navBar").toggleClass('minimal-nav');
      $("#minimize-button").toggleClass('btn-to-close');
    });
  }

  afterCmsLoad() {
  }
}