import _ngsFormValidator from '../../util/NgsFormValidator.js';

let NgsFormValidator = function (formElement, options) {
  let showError = function (elem, msg) {

    hideError(elem);
    if(elem.nodeName === "SELECT"){
      elem = elem.closest('div.choices');
    }
    elem.addClass('invalid');
    elem.addClass('ngs');
    elem.parentNode.insertAdjacentHTML('beforeend', "<div class='ilyov_validate'>" + msg + "</div>");
  };

  let hideError = function (elem) {
    if(elem.nodeName === "SELECT"){
      elem = elem.closest('div.choices');
    }
    elem.removeClass('invalid');
    elem.addClass('ngs');
    let errorElement = elem.parentNode.getElementsByClassName('ilyov_validate');
    if(errorElement.length === 0){
      return;
    }
    errorElement[0].remove();
  };
  let _options = {
    showError: showError,
    hideError: hideError
  };
  options = Object.assign(_options, options);
  return _ngsFormValidator(formElement, options);
};
export default NgsFormValidator;