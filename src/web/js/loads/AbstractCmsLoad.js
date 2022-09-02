import AbstractLoad from '../../AbstractLoad.js';
import DialogUtility from '../util/DialogUtility.js';
import PageManager from '../managers/PageManager.js';
import Choices from '../lib/choices.min.js';

export default class AbstractCmsLoad extends AbstractLoad {

  constructor() {
    super();
  }

  getMethod() {
    return "GET";
  }


  getContainer() {
    return "main_container";
  }

  onError(params) {
    DialogUtility.showErrorDialog(params.msg);
  }

  afterLoad() {
    this.modifyParentRedirect();
    this.initAddButton();
    this.initBreadCrumb();
    this.initSearch();
    this.afterCmsLoad();
    this.initChoices();
  }


  initChoices() {
    let choicesElems = document.querySelectorAll('#' + this.getContainer() + ' .ngs-choice');
    for (let i = 0; i < choicesElems.length; i++) {
      let choiceElem = choicesElems[i];
      if(choiceElem.choices) {
        continue;
      }
      choiceElem.choices = new Choices(choiceElem,
          {
            removeItemButton: choiceElem.getAttribute('data-ngs-remove') === 'true',
            searchEnabled: choiceElem.getAttribute('data-ngs-searchable') === 'true',
            renderChoiceLimit: 150,
            searchResultLimit: 150,
            shouldSort: !choiceElem.getAttribute('data-do-not-sort'),
          });
    }
  }

  afterCmsLoad() {
  }

  //todo: maybe need to init this button in AbstractCmsListLoad.js too, because now addButton is in list.tpl, not in main.tpl
  initAddButton() {
    let addBtns = document.querySelectorAll('#'+this.getContainer() + ' .f_addItemBtn');
    addBtns.unbindClick();
    addBtns.click((evt)=> {
      let btn = evt.target.closest(".f_addItemBtn");
      if(btn.getAttribute('is-loading')) {
        return;
      }
      btn.setAttribute('is-loading', '1');
      this.getAddLoadParams().then((params) => {
        NGS.load(this.args().addLoad, params, () => {
          btn.removeAttribute('is-loading');
        });
      });
    });
  }

  getAddLoadParams() {
    return new Promise((resolve, reject) => {
      resolve({});
    });
  }

  modifyParentRedirect() {
    if(!this.args().parentId){
      return;
    }
    let parentRedirect = $("#main_container").find(".f_redirect").last();
    if(parentRedirect.length){
      parentRedirect.attr("params", JSON.stringify({parentId: this.args().parentId}));
    }
  }

  onUnload() {
    let parentRedirect = $("#main_container").find(".f_redirect").last();
    parentRedirect.removeAttr("params")
  }

  initBreadCrumb() {
    let selector = '#' + this.getContainer() + ' .f_redirect';
    document.querySelectorAll(selector).forEach((elem) => {
      elem.addEventListener('click', (evt) => {
        let element = evt.currentTarget;
        let params = {};
        if(element.attr('params')){
          params = JSON.parse(element.attr('params'));
        }
        NGS.load(element.attr('data-im-load'), params);
      });
    });
  }

  initSearch() {
    let glbSearchElem = document.getElementById('glbSearch');
    if(!glbSearchElem){
      return glbSearchElem;
    }
    glbSearchElem.addEventListener('submit', evt => {
      evt.preventDefault();
      let searchKey = document.getElementById('searchKey').value.trim();
      if(searchKey.length < 1){
        return false;
      }
      let params = PageManager.getGlobalParams();
      params.searchKey = searchKey;
      NGS.load(this.args().mainLoad, params);
      return false;
    });
  }


}
