/**
 * @author Levon Naghashyan
 * @site http://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2013
 */
/**
 * menu util
 *
 * @return call load
 */
$.fn.ilyovMenu = function (variables) {
  const defaults = {
    load: "", // load name/key if not set will be used imLoad element attribute
    tabElem: 'f_menu', // wich element class should used for getting tab
    params: {}, // additional parameters
    activeClass: "active", // class name for active element
    activeElem: "", // class name for active element
    activeClickble: false, // class name for active element
    beforeAction: function () {
      return true;
    } // this function should be call before do load
  };
  const options = $.extend(defaults, variables);
  const menus = $(this).find("." + options.tabElem);
  let imLoad = "";
  menus.each(function (index, elem) {
    if(options.load){
      imLoad = options.load;
    } else{
      imLoad = $(elem).attr("data-im-load");
    }
    if(!options.activeElem){
      if(index == 0){
        $(elem).addClass(options.activeClass);
      }
    } else if(options.activeElem == imLoad){
      $(elem).addClass(options.activeClass);
    }
    $(elem).click(function () {
      if(options.load){
        imLoad = options.load;
      } else{
        imLoad = $(elem).attr("data-im-load");
      }
      if(options.activeClickble == false){
        if($(this).hasClass(options.activeClass)){
          return;
        }
      }
      menus.removeClass(options.activeClass);
      const _status = options.beforeAction();
      if(_status === false){
        return;
      }
      const _params = NGS.eval("(" + $(this).attr("params") + ")");
      const params = $.extend(_params, options.params);
      NGS.load(imLoad, params);
      $(this).addClass(options.activeClass);
    });
  });
};