/**
 * PaginationUtility helper util
 * for load more functionality
 *
 * @author Aram Atanesyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2015-2019
 */



let PaginationUtility = {

  init: function(container, loadMoreCallback, initCallback) {
    let verticalScrollExists = container.scrollHeight > container.clientHeight;

    if(!verticalScrollExists) {
      loadMoreCallback();
    }else {
      container.addEventListener('scroll', ()=> {
        if(container.scrollHeight - container.scrollTop <= container.offsetHeight + 60) {
          loadMoreCallback();
        }
        else {
          initCallback();
        }
      }, {once: true});
    }
  },


  /**
   * replace element with its clone to remove old set event listeners
   * @param domSelector
   * @returns {ActiveX.IXMLDOMNode | Node}
   */
  replaceChildToRemoveListeners: function (domSelector) {
    if(!domSelector) {
      return domSelector;
    }
    let newElement = domSelector.cloneNode(true);
    domSelector.parentNode.replaceChild(newElement, domSelector);
    return newElement;
  }



};

export default PaginationUtility;