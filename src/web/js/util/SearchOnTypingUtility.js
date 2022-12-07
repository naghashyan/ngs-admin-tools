/**
 * Search on typing helper util
 * for load more functionality
 *
 * @author Aram Atanesyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2015-2019
 */

let SearchOnTypingUtility = {


  //todo: maybe need to make more dynamically working
  initSearch: function(searchInputSelector, searchResultListSelector, action, whereConditions, callBack) {
    let filterInput = document.getElementById(searchInputSelector);

    filterInput.addEventListener('keyup', (e) => {
      let timer = setTimeout(() => {
        if(filterInput.value.trim()) {
          let currentProductId = document.getElementById(searchInputSelector).value || -1;
          NGS.action(action, {searchKey: filterInput.value, productId: currentProductId, whereConditions: JSON.stringify(whereConditions), limit: 20},  (response) => {
            callBack(response);
          })
        }else{
          let listContainer = document.getElementById(searchResultListSelector);
          listContainer.innerHTML = '';
        }
      }, 300);

      filterInput.addEventListener('keydown', (e) => {
        clearTimeout(timer);
      });
    });

  }

};

export default SearchOnTypingUtility;
