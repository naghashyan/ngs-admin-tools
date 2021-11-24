/**
 * for cookies read
 *
 * @author Aram Atanesyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2015-2019
 */



let CookiesUtils = {

  /**
   * get cookie by name
   * @param name
   * @returns {string|null}
   */
  getCookie: function(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
      return parts.pop().split(';').shift();
    }
    return null;
  },

  /**
   * get info about user from cookie
   * @returns {null|any|string}
   */
  getUserInfoFromCookie: function() {
    let userData = this.getCookie('user_data');
    if(userData) {
      userData = JSON.parse(decodeURIComponent(userData));
      return userData;
    }
    return null;
  }

};

export default CookiesUtils;