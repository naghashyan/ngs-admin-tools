/**
 * String util for functions to manipulate with strings
 * for load more functionality
 *
 * @author Aram Atanesyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2015-2019
 */



let StringUtility = {

  /**
   * removes all dashes and underlines and depends to params transforms to uppercase all or only first word
   * @param underlinedText
   * @param upperCaseFirst
   * @param uppercaseAll
   * @returns {string}
   */
  toReadableText: function (underlinedText, upperCaseFirst=true, uppercaseAll=true) {
    underlinedText = underlinedText.trim();

    underlinedText = underlinedText.replace(/[_-]/g, " ", function (m, w) {
      return " " + (uppercaseAll ? w.toUpperCase() : w);
    });
    underlinedText = underlinedText.trim();

    if(upperCaseFirst) {
      underlinedText = underlinedText.charAt(0).toUpperCase() + underlinedText.slice(1);
    }


    return underlinedText;
  },

  /**
   * replaces the last 's' with empty string or 'ies' with 'y' letter
   * @param str
   * @returns {string}
   */
  pluralToSingular: function (str) {
    if(str.substr(str.length - 3) === 'ies') {
      str = str.substring(0, str.length - 3);
      str += 'y';
    }
    else if(str.substr(str.length - 2) === 'es') {
      str = str.substring(0, str.length - 2);
    }
    else if(str.slice(-1) === 's') {
      str = str.substring(0, str.length - 1);
    }

    return str;
  },

  /**
   * removes needle from str
   * @param str
   * @param needle
   * @param fromEveryWhere
   * @returns {*}
   */
  removeCustomTextFromString: function (str, needle, fromEveryWhere=true) {
    if(fromEveryWhere) {
      return str.replaceAll(needle, '');
    }
    return str.replace(needle, '');
  }



};

export default StringUtility;