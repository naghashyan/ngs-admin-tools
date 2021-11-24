let GridManager = {

  init: function (elemId) {
    jQuery("#" + elemId + " .f_btn").click(function () {
      var elem = jQuery(this);
      if(elem.attr("data-im-load") || elem.attr("data-im-action")){
        try {
          var jsonParams = eval('(' + elem.attr("data-im-params") + ')');
        } catch (e) {
          var _params = elem.attr("data-im-params").replace("[", "{").replace("]", "}").replace(/\w+/gi, "\'$&\'");
          var jsonParams = eval('(' + _params + ')');
        }
        if(elem.attr("data-im-load")){
          NGS.load(elem.attr("data-im-load"), jsonParams);
          return;
        }
        if(elem.attr("data-im-action")){
          NGS.action(elem.attr("data-im-action"), jsonParams);

        }
      }
    });
  }
};
export default GridManager;