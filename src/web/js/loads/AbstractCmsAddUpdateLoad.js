import AbstractLoad from '../../AbstractLoad.js';
import GridManager from '../managers/GridManager.js';
import PageManager from '../managers/PageManager.js';
import DialogUtility from '../util/DialogUtility.js';
import MaterialsUtils from '../util/MaterialsUtils.js';
import ValidationUtility from '../util/ValidationUtility.js';
import Choices from "../lib/choices.min.js";
import flatpickr from "../lib/flatpickr.min.js";
import RowsListManager from "../managers/RowsListManager.js";
import ImageDropzoneUtil from "../util/ImageDropzoneUtil.js"


export default class AbstractCmsAddUpdateLoad extends AbstractLoad {

    currentTabId = "";


    constructor() {
        super();
        this.childLoadParams = null;
    }


    getChildLoadParams() {
        return this.childLoadParams;
    }


    setChildLoadParams(params) {
        this.childLoadParams = params;
    }

    getDataFromForm(form) {
        let formData = Array.from(new FormData(form));
        let result = {};
        for (let i = 0; i < formData.length; i++) {
            result[formData[i][0]] = formData[i][1];
        }
        return result;
    }


    iniEditChildLoad(itemRow, object, inputSelector) {
        let editBtn = itemRow.querySelectorAll('.f_edit_btn');
        editBtn.click(event => {
            event.stopPropagation();
            let itemDto = this.getItemFromExisting(inputSelector, object);
            let params = {};
            params.itemDto = itemDto;
            params.tempId = object.tempId;
            NGS.load(this.args().editLoad, params);
            return false;
        });
    }


    getItemFromExisting(inputSelector, item) {
        let itemsInput = document.getElementById(inputSelector);
        let itemsData = itemsInput.value;
        if (!itemsData) {
            itemsData = [];
        } else {
            itemsData = JSON.parse(itemsData);
        }

        for (let i = 0; i < itemsData.length; i++) {
            if (item.id && itemsData[i].id && +(itemsData[i].id) === +(item.id)) {
                return itemsData[i];
            }
            if (item.tempId && itemsData[i].tempId && +(itemsData[i].tempId) === +(item.tempId)) {
                return itemsData[i];
            }
        }
        return null;
    }


    changeExistingElement(items, item) {
        let exists = false;
        for (let i = 0; i < items.length; i++) {
            if (item.id && items[i].id && +(items[i].id) === +(item.id)) {
                items[i] = item;
                exists = true;
                break;
            }
            if (item.tempId && items[i].tempId && +(items[i].tempId) === +(item.tempId)) {
                items[i] = item;
                exists = true;
                break;
            }
        }

        if (!exists) {
            items.push(item);
        }


        return items;
    }


    initRemoveChildItem(contactRow, inputSelector) {
        let deleteBtn = contactRow.querySelectorAll('.f_delete_btn');
        deleteBtn.click(evt => {
            this.removeChildItem(evt, inputSelector);
        });
    }

    removeChildItem(evt, inputSelector) {
        evt.stopPropagation();
        var elem = $(evt.target).closest("ul");
        var itemId = elem.attr('data-im-id');
        var tempId = elem.attr('data-im-index');
        this.deleteChildItemById(elem, itemId, tempId, inputSelector);
    }


    deleteChildItemById(elem, itemId, tempId, inputSelector) {
        DialogUtility.showAlertDialog("Delete item", "Do you want to remove this item ? This item can be used in other places.").then(function (confirmationMessage) {

            elem.remove();
            this.rowsListManager.changeItemsCountInUi(-1);

            let itemsInput = document.getElementById(inputSelector);
            let itemsData = itemsInput.value;
            if (!itemsData) {
                itemsData = [];
            } else {
                itemsData = JSON.parse(itemsData);
            }
            if (itemId > 0) {
                this.changeExistingElement(itemsData, {id: itemId, toDelete: true});
            } else {
                for (let i = 0; i < itemsData.length; i++) {
                    if (itemsData[i].tempId && +(itemsData[i].tempId) === +(tempId)) {
                        itemsData.splice(i, 1);
                        break;
                    }
                }
            }

            itemsInput.value = JSON.stringify(itemsData);

        }.bind(this)).catch(function (error) {
            console.log("canceled");
        });
    }


    getContainer() {
        if (this.args().editActionType === "popup") {
            let modalLevel = this.getModalLevel();
            if (!modalLevel) {
                modalLevel = 1;
            }
            let modalPostFix = modalLevel === 1 ? "" : "_" + modalLevel;
            return "modal" + modalPostFix;
        }
        return "loadContent";
    }

    onError(params) {
        DialogUtility.showErrorDialog(params.msg);
    }

    getModalTitle() {
        let title = "";
        if (this.args().saveAction) {
            let actionPath = this.args().saveAction.split(".");
            let actionName = actionPath[actionPath.length - 1].split("_").join(" ");
            title = actionName.replace(/\b[a-z]/g,
                function (firstLatter) {
                    return firstLatter.toUpperCase();
                })
        }
        return title;
    }

    beforeLoad() {
        let levelOfModal = this.getModalLevel();
        this.loadedDialog = MaterialsUtils.initCmsModal(levelOfModal);
    }

    afterLoad() {
        if(this.isViewMode()) {
            this.initHideAddBtn();
        }
        this._setMainImageIfNoExist();
        this._initDropzone();
        this.loadedDialog = null;
        if (this.getContainer().indexOf("modal") === 0) {
            let levelOfModal = this.getModalLevel();
            this.loadedDialog = MaterialsUtils.createCmsModal(this.getModalTitle(), levelOfModal);
        }

        MaterialsUtils.initMaterialElements(this.getContainer());

        GridManager.init(this.getContainer());

        this.doCancelAction();
        this.modifyParentRedirect();
        this.doSaveItem();


        this.ngsImageUpload();
        if(this.args().editActionType !== "popup") {
            this.initBackBtn();
        }
        this.initChoices();
        this.initTinymce();
        this.initDatePickers();
        this.initTabSelection();
        this.initValidators();
        this.blockNegativeNumbersInput();
        this.initFormTranslation();
        this.initLogShowing();
        this.initGoToRelatedEntity();
        this.initRulesManager();
        this.afterCmsLoad();
    }

    /**
     * overlay above all
     * @param freeze
     */
    initScreenFreeze(freeze) {
        let mainOverlay = document.getElementById('main-overlay-for-all-purposes');
        freeze ? mainOverlay.classList.remove('is_hidden') : mainOverlay.classList.add('is_hidden');
    }


    /**
     * when some selectBox values are related to another selectBox selected value and should reset on that selectBox value change
     * @param dataForSelection
     * @param action
     */
    initSelectBoxValuesOnOtherSelectBoxChange(dataForSelection, action) {
        let firstSelect = document.getElementById(dataForSelection.firstSelectId);
        if (firstSelect) {    /*we do this if checking because in view mode there isn't this selector*/
            let form = firstSelect.closest("form");
            let secondSelectContainer = form.querySelector('.' + dataForSelection.secondInputFieldClass);
            if (!this.args()[dataForSelection.addedJsonParam] || !this.args()[dataForSelection.addedJsonParam].length) {
                secondSelectContainer.innerHTML = "";
            }
            firstSelect.addEventListener('change', (e) => {
                let valueOfFirstSelectBox = e.target.closest('select').value;
                let dataToSend = {
                    idOfFirstSelect: valueOfFirstSelectBox
                };
                NGS.action(action, dataToSend, (response) => {
                    this.resetValuesOfSecondSelectBox(form, response.data, secondSelectContainer, dataForSelection);
                });
            });
        }


    }


    /**
     * set values of selectBox which values are related to another selectBox (upper function)
     * @param form
     * @param data
     * @param secondSelectContainer
     * @param dataForSelection
     */
    resetValuesOfSecondSelectBox(form, data, secondSelectContainer, dataForSelection) {
        let oldLabel = secondSelectContainer.querySelector("label");

        let labelOldText = oldLabel ? oldLabel.innerHTML : dataForSelection.secondFieldLabel;
        secondSelectContainer.innerHTML = "";
        if (!data || !data.length) {
            return;
        }
        let helpText = secondSelectContainer.querySelector('.f_help-text');
        if(!helpText) {
            let helpTextExample = document.querySelector('.f_help-text');
            helpTextExample.querySelector('span').innerText = dataForSelection.secondSelectHelpText ? dataForSelection.secondSelectHelpText : " ";
            helpText = helpTextExample;
        }

        let label = document.createElement('label');
        label.innerHTML = labelOldText;
        label.setAttribute('for', dataForSelection.secondSelectId);
        let iconsBox = document.createElement('div');
        iconsBox.classList.add('icons-box');
        iconsBox.appendChild(helpText);

        secondSelectContainer.appendChild(iconsBox);
        secondSelectContainer.appendChild(label);

        let select = document.createElement('select');
        let nameAttribute = dataForSelection.secondSelectName;

        select.setAttribute("id", dataForSelection.secondSelectId);
        select.setAttribute("name", nameAttribute);
        select.classList.add("ngs-choice");
        select.setAttribute('data-ngs-searchable', (data.length > 5)? 'true' : 'false');
        select.setAttribute('placeholder', labelOldText);

        if(dataForSelection.hasOwnProperty('isMultiple') && dataForSelection.isMultiple) {
            select.setAttribute('multiple', 'multiple');
        }

        //todo: this line was before, but on country-state selection, it should not be; need to check it was exist and if no need - delete
        // select.innerHTML = '<option value="">Please select</option>';

        for (let i = 0; i < data.length; i++) {
            let option = document.createElement('option');
            option.value = data[i].id;
            option.innerText = data[i].value;
            select.appendChild(option);
        }
        secondSelectContainer.appendChild(select);
        let searchEnabled = data.length > 5;

        new Choices(select, {
            removeItemButton: true,
            searchEnabled: searchEnabled,
            renderChoiceLimit: 150,
            searchResultLimit: 150,
            shouldSort: true,
        });

        nameAttribute = this.modifyNameAttributeIfItsForMultiple(nameAttribute);

        this.addFieldValidations(nameAttribute, this.args().fieldValidators[nameAttribute], this.args().fieldValidators)

    }


    /**
     * if nameAttribute is like name[] it should become like name (without []) for adding validation event listeners
     * @param nameAttribute
     * @returns {string|*}
     */
    modifyNameAttributeIfItsForMultiple(nameAttribute) {
        if(nameAttribute.charAt(nameAttribute.length - 1) === ']' && nameAttribute.charAt(nameAttribute.length - 2) === '[') {
            return nameAttribute.substring(0, nameAttribute.length - 2);
        }
        return nameAttribute
    }



    initTabSelection() {
        if (this.args().currentTabId) {
            let tab = document.getElementById(this.args().currentTabId);
            if (tab) {
                tab.click();
            }
        }
        let tabElements = document.querySelectorAll('#' + this.getContainer() + ' .f_tabTitle');
        if (tabElements) {
            let tabActiveElements = document.querySelectorAll('#' + this.getContainer() + ' .f_tabTitle.active');
            if (tabActiveElements[0]) {
                this.currentTabId = tabActiveElements[0].getAttribute("id");
            }
        }
        let activePage = document.querySelectorAll('#' + this.getContainer() + ' .f_vertical-tabs-content')[0];

        tabElements.click((evt) => {
            this.currentTabId = evt.target.closest(".f_tabTitle").getAttribute("id");
            if(activePage) {
                if(activePage.querySelector('.f_g-content-item-inner')) {
                    activePage.querySelector('.f_g-content-item-inner').scrollTo(0,0);
                }
            }
        });
    }


    initPopupClosingInViewModeOnly() {
        if(this.args().editActionType === "popup") {

            let popupFormContainer = document.getElementById('modal_' + this.getModalLevel());
            let popupForm = popupFormContainer.querySelectorAll('form')[0];
            let button = popupForm.querySelector('.f_close-popup-button');
            button.classList.remove('is_hidden');

            if(!popupForm.querySelector('.f_saveItem') && !popupForm.querySelector('.f_cancel')) {

                const popupOpen = function(e) {
                    e.preventDefault();

                    if(!e.target.closest('form')) {
                        if (MaterialsUtils.getActiveModalInstance()) {
                            MaterialsUtils.getActiveModalInstance().close();
                            popupFormContainer.removeEventListener('click', popupOpen);
                        }
                    }
                };

                popupFormContainer.addEventListener('click', popupOpen);

                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    e.preventDefault();

                    if (MaterialsUtils.getActiveModalInstance()) {
                        MaterialsUtils.getActiveModalInstance().close();
                        popupFormContainer.removeEventListener('click', popupOpen);
                    }
                });
            }
        }

    }

    initDatePickers() {
        flatpickr('#' + this.getContainer() + " .f_flatpickr-datepicker");
    }

    /**
     * call from any addUpdate load if there is a add button and need to hide
     */
    initHideAddBtn() {
        let addButtons = document.querySelectorAll('.f_addItemBtn');
        if(addButtons.length) {
            addButtons.forEach(btn => {
                btn.setAttribute('style', 'display: none;');
            })

        }
    }

    initTinymce() {
        if (tinymce.activeEditor) {
            let elements = document.querySelectorAll('#' + this.getContainer() + ' .f_tinymce');
            for (let i = 0; i < elements.length; i++) {
                let id = elements[i].getAttribute("id");
                tinymce.execCommand('mceRemoveEditor', true, id);
            }
        }
        tinymce.init({
            selector: '#' + this.getContainer() + ' .f_tinymce',
            resize: false,
        })
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
                    shouldSort: true,
                });
        }
    }


    initBackBtn() {
        if (this.getChildLoadParams()) {
            return;
        }
        let backBtn = $('.f_back-btn');
        backBtn.unbind("click");
        for (let i = 0; i < backBtn.length; i++) {
            backBtn[i].classList.remove('hide');
        }
        backBtn.on("click", (evt) => {
            let params = this.getListDataInLocalStorage();
            NGS.load(this.args().cancelLoad, params);
        });
    }

    getListDataInLocalStorage() {
        let mainSectionUuid = document.querySelector('main.main-section');
        let uuid = mainSectionUuid.getAttribute('data-ngs-uuid');
        let listLoadParams = localStorage.getItem(uuid + '_listLoadParams');
        if(!listLoadParams) {
            return {};
        }
        return JSON.parse(listLoadParams);
    }


    initViewPage() {

        if(!document.querySelector('.f_editItemBtn')) {
            return;
        }

        let isPageNestedToParentLoad = !!(this.getUiStorage() && this.getRenderTemplate() && this.getTabContainer());

        if(!isPageNestedToParentLoad) {
            if(document.querySelector('.f_editItemBtn')) {
                document.querySelector('.f_editItemBtn').removeAttribute('style');
            }else {
                return;
            }
        }
        document.querySelector('.f_editItemBtn').addEventListener('click', ()=> {
            document.querySelector('.f_editItemBtn').setAttribute('style', 'display: none;');
            let params = {
                itemId: this.args().viewPageParams.itemId,
                fromViewPage: true
            };
            if (this.currentTabId) {
                params.currentTabId = this.currentTabId;
            }

            if(!isPageNestedToParentLoad) {
                NGS.load(this.args().viewPageParams.editLoad, params);
            }
        });
    }

    /**
     * if load opens in modal, we can specify modal level, to allow open several pop-ups in same time
     *
     * @returns {number}
     */
    getModalLevel() {
        return 1;
    }

    modifyParentRedirect() {
        if (Object.keys(this.setCancelParams()).length) {
            var parentRedirect = $("#main_container").find(".f_redirect").last();
            if (parentRedirect.length) {
                parentRedirect.attr("params", JSON.stringify(this.setCancelParams()));
            }
        }
    }

    doCancelAction() {
        document.querySelectorAll("#" + this.getContainer() + " .f_cancel").click(event => {
            if (MaterialsUtils.getActiveModalInstance()) {
                MaterialsUtils.getActiveModalInstance().close();
            }
            if (this.getModalLevel() !== 1) {
                return;
            }
            NGS.load(this.args().cancelLoad, this.setCancelParams());
        });
    }

    setCancelParams() {
        return {};
    }

    getUiStorage() {
        return null; //contactsToAttach
    }

    getRenderTemplate() {
        return null; // contactRowToCopy
    }

    getTabContainer() {
        return null; //Contacts_tab
    }


    //TODO: SHOULD BE REFACTORED ASAP
    doSaveItem() {
        let submitFormElem = document.querySelector("#" + this.getContainer() + " .f_addUpdateForm");
        if (!submitFormElem) {
            return;
        }
        submitFormElem.addEventListener('submit', function (event) {
            return false;
        });
        document.querySelectorAll("#" + this.getContainer() + " .f_saveItem").click(event => {
            if (this.getUiStorage()) {
                this.saveChildItemData(event);
            } else {
                this.saveItemData(event)
            }
        });
    }


    /**
     *
     * @param event
     */
    saveChildItemData(event) {
        let formElem = event.target.closest("form");
        this.checkAllFields().then(function(isValid) {
            if(!isValid) {
                formElem.querySelectorAll('.f_tabTitle').removeClass('error');
                formElem.querySelectorAll('.f_cms_tab-container').forEach((element, index) => {
                    if (element.querySelector('.ngs.invalid') && element.id) {
                        document.getElementById(element.id + '_title').addClass('error');
                    }
                });
                return;
            }
            let formData = this.getDataFromForm(formElem);

            try {
                formData = this.beforeSave(formData);
            }catch (e) {
                console.log(e.message);
                return false;
            }

            if (!formData.tempId && !formData.id) {
                formData.tempId = new Date().getTime();
            }

            let renderTemplate = document.getElementById(this.getRenderTemplate()).innerHTML;
            let table = document.querySelector("#" + this.getTabContainer() + " .f_cms-table-container");
            let renderData = {};
            for (let prop in formData) {
                if (!formData.hasOwnProperty(prop)) {
                    continue;
                }
                let temp = formData[prop].toString();
                renderData[prop] = temp.replace(/(<([^>]+)>)/ig, '');
            }
            renderData.item_index = formData.tempId;
            let selectBoxInnerValues = this.getSelectBoxInnerValues(formData, formElem);
            let customParamsToAdd = this.getCustomParamsForTemplateRender();
            renderData = {...renderData, ...selectBoxInnerValues, ...customParamsToAdd};
            let contactRow = this.renderTemplate(renderTemplate, renderData);

            let row = null;
            if (formData.tempId) {
                row = table.querySelector(".f_table_row[data-im-index='" + formData.tempId + "']");
            } else {
                row = table.querySelector(".f_table_row[data-im-id='" + formData.id + "']");
            }

            if (!row){
                table.append(contactRow);
            } else {
                row.replaceWith(contactRow);
            }

            this.rowsListManager = new RowsListManager(contactRow.closest('.f_list-load-container'));
            if (!row) {
                this.rowsListManager.changeItemsCountInUi(1);
            }

            this.iniEditChildLoad(contactRow, formData, this.getUiStorage());
            this.initRemoveChildItem(contactRow, this.getUiStorage());

            let checkElementCheckbox = contactRow.querySelector('.f_check-item');
            if(checkElementCheckbox) {
                checkElementCheckbox.addEventListener('change', (e) => {
                    this.initBulkActionsForNewAddedChildren(e);
                });
            }


            let dataToAttachInput = document.getElementById(this.getUiStorage());
            let itemsData = dataToAttachInput.value;
            if (!itemsData) {
                itemsData = [];
            } else {
                itemsData = JSON.parse(itemsData);
            }
            let existingDto = this.getItemFromExisting(this.getUiStorage(), formData);
            if (existingDto) {
                this.changeExistingElement(itemsData, formData);
            } else {
                itemsData.push(formData);
            }
            dataToAttachInput.value = JSON.stringify(itemsData);
            if (MaterialsUtils.getActiveModalInstance()) {
                MaterialsUtils.getActiveModalInstance().close();
            }
        }.bind(this));
    }



    initBulkActionsForNewAddedChildren(e) {
        let checkbox = e.target;
        this.rowsListManager.handleElementSelectionChange(checkbox, true);

        if(this.rowsListManager.needToChangeMainSelectionCheckbox()){
            this.rowsListManager.handleMainSelectionCheckbox(checkbox);
        }
    }

    /**
     * in this function we add select box inner values instead of ids, to show in rows before saving item
     *
     * @param formData
     * @param formElement
     * @returns {{}}
     */
    getSelectBoxInnerValues(formData, formElement) {
        return {};
    }

    /**
     * if need to add some params, which are not exist in form.
     * @returns {{}}
     */
    getCustomParamsForTemplateRender() {
        return {};
    }

    /**
     *if there are input fields with type file which are empty, the variable $_FILES will be not empty in php, so need to delete that fields from formData
     * @param formData
     */
    removeEmptyFileInputs(formData) {}

    /**
     *
     * @param event
     * @returns {boolean}
     */
    saveItemData(event) {
        let formElem = event.target.closest('form');

        this.checkAllFields().then(function(isValid) {
            if(!isValid) {
                formElem.querySelectorAll('.f_tabTitle').removeClass('error');
                formElem.querySelectorAll('.f_cms_tab-container').forEach((element, index) => {
                    if (element.querySelector('.ngs.invalid') && element.id) {
                        document.getElementById(element.id + '_title').addClass('error');
                    }
                });
                return;
            }
            tinyMCE.triggerSave();
            let formData = new FormData(formElem);

            try {
                formData = this.beforeSave(formData);
            }catch (e) {
                console.log(e.message);
                return false;
            }
            this.removeEmptyFileInputs(formData);
            if (formData === false) {
                return false;
            }

            if (this.args().parentId) {
                formData.append('parentId', this.args().parentId);
            }
            formData = this._mergeWithPageParams(formData);
            NGS.action(this.args().saveAction, formData, (data) => {
                this.afterSaveItemDataAction(data);
            }, (error) => {
                this.handleErrorCase(formData, error);
            });
        }.bind(this));

    }


    handleErrorCase(formData, error) {
        if(error.params && error.params.overrideIssue) {
            this.handleOverrideItemCase(formData);
        }
    }

    /**
     * if item is changed the moment when we want to change, it asks to override confirmation
     * @param formData
     */
    handleOverrideItemCase(formData) {
        DialogUtility.showAlertDialog("Override issue", "This item was changed by other user, do you want to override by Your changes ?").then(function () {
            formData.append('confirmed_to_udpate', true);

            NGS.action(this.args().saveAction, formData, (data) => {
                this.afterSaveItemDataAction(data);
            }, (error) => {
                this.handleErrorCase(formData, error);
            });
        }.bind(this));
    }

    //this function is to give possibility to override in other loads
    afterSaveItemDataAction(data) {
        if (MaterialsUtils.getActiveModalInstance()) {
            MaterialsUtils.getActiveModalInstance().close();
        }

        let params = {
            itemId: data.itemId,
            fromViewPage: true
        };
        if (this.currentTabId) {
            params.currentTabId = this.currentTabId;
        }
        params = this.modifyParams(params);
        setTimeout(() => {
            NGS.load(this.args().editLoad, params);
        }, 1000);

    }

    modifyParams(params) {
        return params;
    }


    checkAllFields() {
        let fieldValidators = this.args().fieldValidators;
        if (!fieldValidators) {
            return new Promise(resolve => {
                resolve(true);
            });
        }
        let promises = [];
        for (let fieldName in fieldValidators) {
            if (!fieldValidators.hasOwnProperty(fieldName)) {
                continue;
            }
            promises.push(this.validateElement(fieldName, fieldValidators[fieldName], fieldValidators));
        }

        return new Promise(function(resolve, reject) {
            Promise.all(promises).then(function(resolvedPromises) {
                let isValid = true;
                for(let i=0; i < resolvedPromises.length; i++) {
                    if(!resolvedPromises[i]) {
                        isValid = false;
                        break;
                    }
                }
                resolve(isValid);
            }.bind(this));
        });

    }




    addElementToOldParentDtos(dtos, changed, index) {
        if (index !== null) {
            dtos[index] = changed;
            return dtos;
        } else {
            dtos.unshift(changed);
            return dtos;
        }
    }

    /**
     *
     * @param params FormData
     * @returns {*}
     */
    _mergeWithPageParams(params) {
        let listingParams = PageManager.getGlobalParams();
        for (let i in listingParams) {
            if (listingParams.hasOwnProperty(i) && !params[i]) {
                params.set('pageParams[' + i + ']', listingParams[i]);
            }
        }
        return params;
    }

    /**
     *
     * should return FormData
     *
     * @param formData FormData
     *
     * @returns FormData
     */
    beforeSave(formData) {
        return formData;
    }

    onUnLoad() {
    }

    afterCmsLoad() {
    }

    getMethod() {
        return "GET";
    }

    validateStartEndDate(dateStart, dateEnd, format) {
        let dateTimeStart = dateStart ? moment(dateStart, format) : null;
        let dateTimeEnd = dateEnd ? moment(dateEnd, format) : null;
        return !(dateTimeStart && dateTimeEnd && dateTimeEnd.isBefore(dateTimeStart));
    }

    ngsImageUpload() {
        document.querySelectorAll('.f_uploadImage').forEach((fileElem) => {
            if (fileElem.attr('data-im-preview')) {
                let imagePreviewElem = document.querySelector(fileElem.attr('data-im-preview'));
                fileElem.addEventListener('change', (event) => {
                    let inputElem = event.currentTarget;
                    if (!inputElem.files || !inputElem.files[0]) {
                        return;
                    }
                    let reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageData = e.target.result;
                        imagePreviewElem.attr('src', e.target.result);
                    };
                    reader.readAsDataURL(inputElem.files[0]);
                })
            }
        })
    }

    showError(elem, msg) {
        this.hideError(elem);
        elem.addClass('invalid');
        elem.addClass('ngs');
        elem.parentNode.insertAdjacentHTML('beforeend', "<div class='ilyov_validate'>" + msg + "</div>");
    }

    hideError(elem) {
        elem.removeClass('invalid');
        elem.addClass('ngs');
        let errorElement = elem.parentNode.getElementsByClassName('ilyov_validate');
        if (errorElement.length === 0) {
            return;
        }
        errorElement[0].remove();
    }

    _initDropzone() {
        ImageDropzoneUtil.existingImagesIds = [];
        ImageDropzoneUtil.images = [];

        if (this.args()['imagesUrls']) {
            ImageDropzoneUtil.existingImagesIds = this.args()['imagesUrls'].map((item) => {
                if (item.url.original) {
                    return item.url.original.substring(item.url.original.lastIndexOf('/') + 1);
                }
            });
        }

        let anyDropzoneElement = document.querySelector('#' + this.getContainer() + ' .f_all-dropzones-container');
        if(anyDropzoneElement) {
            let hasWriteAccess = anyDropzoneElement.getAttribute('data-write-access') === 'true';
            let isDropzoneMultiple = !!anyDropzoneElement.querySelector('.f_multipleDropzone');
            this.imagesHasWritePermission = hasWriteAccess;



            ImageDropzoneUtil.initVariables({
                imagesUrls: this.args()['imagesUrls'],
                isViewMode: this.isViewMode(),
                imagesHasWritePermission: hasWriteAccess,
                container: this.getContainer(),
                onlyOneDefaultImage: !!this.args().onlyDefaultImage

            });


            if(!hasWriteAccess || this.isViewMode()) {
                ImageDropzoneUtil.initDropzoneForViewMode(isDropzoneMultiple);
            }else {
                ImageDropzoneUtil.initSingleDropzoneForAddEditMode();
                ImageDropzoneUtil.initMultipleDropzoneForAddEditMode();
            }
        }

    }


    addImagesToFormData(formData) {
        let descriptions = document.querySelectorAll('[name="imageDescription"]');
        let descriptionTexts = [];
        let descriptionIds = [];

        for (let i = 0; i < descriptions.length; i++) {
            descriptionIds.push(descriptions[i].getAttribute('image-id'));
            descriptionTexts.push(descriptions[i].value);
        }

        descriptionTexts.forEach(text => {
            formData.append('imageDescriptionText[]', text);
        });
        descriptionIds.forEach(id => {
            formData.append('imageDescriptionId[]', id);
        });

        ImageDropzoneUtil.images.forEach(image => {
            for (let key in image) {
                if (image.hasOwnProperty(key)) {
                    formData.append('image[]', image[key]);
                }
            }
        });
        formData.append('oldImages', ImageDropzoneUtil.existingImagesIds);

        formData = this.addInfoAboutMainImageToFormData(formData);

        return formData;
    }


    /**
     * push to formData either id of image which is set to main, or the index of new added image;
     * @param formData
     * @returns {*}
     */
    addInfoAboutMainImageToFormData(formData) {

        let allIsMainRadioButtons = document.querySelectorAll('.f_isMainRadioButton');

        if(allIsMainRadioButtons) {
            let checkedIsMainButton = Array.from(allIsMainRadioButtons).find((radioButton) => radioButton.checked);

            if(checkedIsMainButton) {

                if(checkedIsMainButton.closest('.f_image-element-main-box').classList.contains('f_newAddedImage')) {

                    let allNewAddedImages = document.querySelectorAll('.f_newAddedImage');
                    for(let i = 0; i < allNewAddedImages.length; i++) {
                        if(allNewAddedImages[i].querySelector('.f_isMainRadioButton').isSameNode(checkedIsMainButton)) {
                            formData.append('mainImage', JSON.stringify({newImageIndex : i}));
                            break;
                        }
                    }
                }else {

                    let allOldImages = document.querySelectorAll('.f_oldImage');
                    for(let i = 0; i < allOldImages.length; i++) {
                        let imageId = allOldImages[i].querySelector('.f_hidden-input-image-id').value;
                        if(allOldImages[i].querySelector('.f_isMainRadioButton').isSameNode(checkedIsMainButton)) {
                            formData.append('mainImage', JSON.stringify({oldImageId : imageId}));
                            break;
                        }
                    }
                }
            }
        }




        return formData;
    }

    /**
     * if any image is not set to main image the first image will be set to main
     * @private
     */
    _setMainImageIfNoExist() {
        if(this.args()['imagesUrls']) {
            for(let image in this.args()['imagesUrls']) {
                if(!this.args()['imagesUrls'].hasOwnProperty(image)) {
                    continue;
                }
                if(this.args()['imagesUrls'][image].hasOwnProperty('isMain') && this.args()['imagesUrls'][image].isMain) {
                    return;
                }
            }
            this.args()['imagesUrls'][0].isMain = true;
        }
    }


    _removeImageDescriptionIfItsDefault() {
        if (ImageDropzoneUtil.variables.onlyOneDefaultImage) {
            if (document.querySelector('.f_multipleDropzone .info-box')) {
                // we can either remove just the description of the image or remove the default image too

                // document.querySelector('.f_multipleDropzone .info-box').remove();
                document.querySelector('.f_multipleDropzone').remove();
            }
        }
    }


    initValidators() {

        let fieldValidators = this.args().fieldValidators;
        if (!fieldValidators) {
            return;
        }

        for (let fieldName in fieldValidators) {
            if (!fieldValidators.hasOwnProperty(fieldName)) {
                continue;
            }
            this.addFieldValidations(fieldName, fieldValidators[fieldName], fieldValidators);
        }
    }

    addFieldValidations(fieldName, validators, allValidators) {

        let fieldId = this.args().tableName + '_' + fieldName + '_input';
        let element = document.getElementById(fieldId);
        if (!element) {
            return;
        }
        if(element.getAttribute('data-validation-added')) {
            return;
        }

        if(element.classList.contains('f_tinymce') && element.tagName === 'TEXTAREA') {
            let elementId = element.getAttribute('id');
            tinymce.get(elementId).on('blur', function(e) {
                this.validateElement(fieldName, validators, allValidators);
            }.bind(this));

        }else{
            element.addEventListener("change", (evt) => {
                this.validateElement(fieldName, validators, allValidators);
            });
        }
        element.setAttribute('data-validation-added', 'true');
    }

    validateElement(fieldName, validators, allValidators) {
        let fieldId = this.args().tableName + '_' + fieldName + '_input';
        let element = document.getElementById(fieldId);

        if (!element || this._elementIsNotRequired(element)) {                                 //todo: || this._elementIsNotRequired(element)   >>>>   this part was before made by me;  M.J. please tell should I remove this function?
            return new Promise(function(resolve, reject) {
                resolve(true);
            });
        }

        return this.validateElementWithAllValidators(fieldName, validators, allValidators);
    }

    getValidationUtil() {
        return ValidationUtility;
    }


    /**
     * check all validators for given field
     *
     * @param fieldName
     * @param validators
     * @param allValidators
     * @returns {Promise<unknown>}
     */
    validateElementWithAllValidators(fieldName, validators, allValidators) {
        let promisesOfAllValidations = [];
        let itemId = this.args().itemId;
        for (let i = 0; i < validators.length; i++) {
            let valueToValidate = this.getValidatorValue(fieldName, validators[i], allValidators, false);
            promisesOfAllValidations.push(new Promise(function(resolve, reject) {
                this.getValidationUtil().validate(valueToValidate, validators[i], this.ngsAction, fieldName, itemId).then(function (validateResult) {
                    let elementsToShowError = this.getElementsWithMessages(fieldName, validators[i], allValidators, validateResult);
                    let elemIsValid = true;
                    for (let j = 0; j < elementsToShowError.length; j++) {

                        if (elementsToShowError[j].message) {
                            elemIsValid = false;
                            this.showInvalidError(elementsToShowError[j].element, elementsToShowError[j].message, validateResult.validator);
                        } else {
                            this.hideInvalidError(elementsToShowError[j].element, validateResult.validator);
                        }
                    }
                    resolve(elemIsValid);

                }.bind(this));
            }.bind(this)));
        }

        return new Promise(function(resolve, reject) {
            Promise.all(promisesOfAllValidations).then(function(fieldAllValidationResults) {
                for(let i = 0; i<fieldAllValidationResults.length; i++) {
                    if(!fieldAllValidationResults[i]) {
                        resolve(false);
                        return;
                    }
                }
                resolve(true);
            });
        });

    }

    _elementIsNotRequired(element) {
        return element.hasAttribute('is_not_required') ||
               element.closest('.form-item').hasAttribute('is_not_required') ||
               element.classList.contains('is_hidden') || element.closest('.form-item').classList.contains('is_hidden');
    }

    clearTabsErrors(element) {
        let tab = element.closest('.f_cms_tab-container');

        if(!tab) {
            return;
        }
        if(!tab.id) {
            return
        }
        if(!tab.querySelector('.ngs.invalid')) {
            document.getElementById(tab.id + '_title').removeClass('error');
        }
    }


    /**
     * show validation message
     *
     * @param element
     * @param message
     * @param validator
     */
    showInvalidError(element, message, validator) {
        let elementIsSelectbox = element.classList.contains('ngs-choice') && element.tagName === 'SELECT';
        let elementIsTinymce = false;

        if(!elementIsSelectbox) {
            elementIsTinymce = element.closest('.form-item').classList.contains('richtext');
        }


        if(elementIsSelectbox) {
            element.closest('.choices__inner').classList.add("ngs", "invalid");
        }else {
            (!elementIsTinymce) ? element.classList.add("ngs", "invalid") : element.closest('.input-field').querySelector('.tox-tinymce').classList.add("ngs", "invalid");
        }

        let errorElement = elementIsSelectbox? element.closest('.choices').getElementsByClassName("ilyov_validate")[0]: element.parentNode.getElementsByClassName("ilyov_validate")[0];
        let hasSameError = false;


        let displayName = element.closest('.form-item').querySelector('label').innerText;
        message = this.modifyMessage(message, displayName);

        if(errorElement) {
            if(errorElement.getAttribute("data-validator-name") === validator) {
                if(errorElement.innerHTML === message) {
                    return;
                }else {
                    hasSameError = false;
                    errorElement.remove();
                }
            }else {
                return;
            }
        }

        if (!hasSameError) {
            if(elementIsSelectbox) {
                element.closest('.choices').insertAdjacentHTML('beforeend', "<div class='ilyov_validate vertical_centered' data-validator-name='" + validator + "'>" + message + "</div>");
            }else{
                element.parentNode.insertAdjacentHTML('beforeend', "<div class='ilyov_validate vertical_centered' data-validator-name='" + validator + "'>" + message + "</div>");
            }
        }
    }


    modifyMessage(message, displayName) {
        let tempDiv = document.createElement('div');
        tempDiv.innerHTML = message;
        let wordToChange = tempDiv.querySelector('.f_fieldName');
        if(!wordToChange) {
            return message;
        }
        wordToChange.innerHTML = displayName;
        return tempDiv.innerHTML;
    }


    /**
     * hide validation message
     *
     * @param element
     * @param validator
     */
    hideInvalidError(element, validator) {
        let elementIsSelectbox = element.classList.contains('ngs-choice') && element.tagName === 'SELECT';

        let elementIsTinymce = false;

        if(!elementIsSelectbox) {
            elementIsTinymce = element.closest('.form-item').classList.contains('richtext');
        }

        let errorElement = elementIsSelectbox? element.closest('.choices').getElementsByClassName("ilyov_validate"): element.parentNode.getElementsByClassName("ilyov_validate");

        if (errorElement.length) {
            for (let i = 0; i < errorElement.length; i++) {
                if (errorElement[i].getAttribute("data-validator-name") === validator) {
                    errorElement[i].remove();
                }
            }
        }

        errorElement = elementIsSelectbox? element.closest('.choices').getElementsByClassName("ilyov_validate"): element.parentNode.getElementsByClassName("ilyov_validate");
        if (!errorElement.length) {
            if(elementIsSelectbox) {
                element.closest('.choices__inner').classList.remove("ngs", "invalid");
            }else {
                (!elementIsTinymce) ? element.classList.remove("ngs", "invalid") : element.closest('.input-field').querySelector('.tox-tinymce').classList.remove("ngs", "invalid");
            }
        }

        this.clearTabsErrors(element);
    }


    /**
     *
     * @param fieldName
     * @param validator
     * @param allValidators
     * @param getInputs
     * @returns {{}|*}
     */
    getValidatorValue(fieldName, validator, allValidators, getInputs) {
        let fieldId = this.args().tableName + '_' + fieldName + '_input';
        let currentInput = document.getElementById(fieldId);
        let currentValue = currentInput;
        if(!getInputs) {
            if(currentInput.classList.contains('f_tinymce') && currentInput.tagName === 'TEXTAREA') {
                let id = currentInput.getAttribute('id');
                    currentValue = (tinymce.get(id).getContent()).replace(/(<([^>]+)>)/ig, '');
            }else {
                if(this.isElementCheckbox(currentInput)) {
                    currentValue = currentInput.checked ? 'on' : '';
                }else {
                    currentValue = currentInput.value;
                }
            }
        }

        if (!validator.as) {
            return currentValue;
        }

        let result = {};
        result[validator.as] = currentValue;
        for (let fieldNameToValidate in allValidators) {
            if (!allValidators.hasOwnProperty(fieldNameToValidate)) {
                continue;
            }
            if (fieldNameToValidate === fieldName) {
                continue;
            }
            let existingValidator = this.getValidatorByName(allValidators[fieldNameToValidate], validator.class);
            if (existingValidator) {
                let fieldId = this.args().tableName + '_' + fieldNameToValidate + '_input';
                let fieldInput = document.getElementById(fieldId);

                if(getInputs) {
                    result[existingValidator.as] = fieldInput
                }else {
                    if(this.isElementCheckbox(fieldInput)) {
                        result[existingValidator.as] = fieldInput.checked ? 'on' : '';
                    }else {
                        result[existingValidator.as] = fieldInput.value;
                    }
                }
            }
        }
        return result;
    }


    /**
     * the function checks whether the input element is checkbox or no
     * @param inputElement
     * @returns {boolean|boolean}
     */
    isElementCheckbox(inputElement) {
        return (inputElement.hasAttribute('type') && inputElement.getAttribute('type') === 'checkbox');
    }

    /**
     * get elements with messages
     *
     * @param fieldName
     * @param validator
     * @param allValidators
     * @param validateResult
     * @returns {[]|{message: *, element: ({}|*)}[]}
     */
    getElementsWithMessages(fieldName, validator, allValidators, validateResult) {
        let fieldsWithInputs = this.getValidatorValue(fieldName, validator, allValidators, true);
        if (!validator.as) {
            return [{
                element: fieldsWithInputs,
                message: validateResult.success ? "" : validateResult.message,
                fieldName: fieldName
            }];
        }
        let result = [];
        for (let fieldName in fieldsWithInputs) {
            if (!fieldsWithInputs.hasOwnProperty(fieldName)) {
                continue;
            }


            let message = validateResult.success ? "" : (validateResult.message[fieldName] ? validateResult.message[fieldName] : "");
            result.push({element: fieldsWithInputs[fieldName], message: message, fieldName: fieldName});
        }
        return result;
    }


    /**
     *
     * @param validators
     * @param validatorName
     * @returns {null|*}
     */
    getValidatorByName(validators, validatorName) {
        for (let i = 0; i < validators.length; i++) {
            if (validators[i].class === validatorName) {
                return validators[i];
            }
        }

        return null;
    }


    /**
     * initializing form translations if the form is translatable
     */
    initFormTranslation() {
        if(!this.args().translations || this.args().editActionType === "popup") {
            return;
        }

        let languagesListContainer = document.getElementById('languages-list-container');
        if(languagesListContainer) {
            languagesListContainer.removeClass('is_hidden');
        }else {
            return;
        }

        let languagesSelectBox = document.getElementById('languages-list');
        let languageId = languagesSelectBox.value;

        this._changeFormLanguage(languageId);

        languagesSelectBox.addEventListener('change', () => {
            let languageId = languagesSelectBox.value;
            this._changeFormLanguage(languageId);
        });
    }


    /**
     * set translatable fields to language that was selected
     * @param languageId
     * @private
     */
    _changeFormLanguage(languageId) {
        let form = document.querySelectorAll('.f_addUpdateForm')[0];
        form.querySelectorAll('.f_translatable-field').addClass('is_hidden');

        if(languageId === 'original') {
            let elements = document.querySelectorAll('[language-id="original"]');
            elements.removeClass('is_hidden');
            this._toggleAllNotTranslatableFields(form, 'remove');
            elements.forEach(field => {
                field.closest(".form-item").classList.remove('no-translation', 'translated');
            });
        }else{
            this._toggleAllNotTranslatableFields(form, 'add');
            let translatedInputs = document.querySelectorAll('.f_translatable-field');
            let translation = this.args().translations[languageId];

            for(let i = 0; i < translatedInputs.length; i++) {
                if(translatedInputs[i].getAttribute('language-id') === languageId) {
                    translatedInputs[i].classList.remove('is_hidden');
                    let originalInput = translatedInputs[i].closest('.input-field').querySelector('[language-id="original"]');
                    originalInput.classList.remove('blurred');
                    originalInput.classList.add('is_hidden');

                    if(!this.isViewMode()) {
                        if(!translatedInputs[i].value && translation) {             //this condition with (&& translation) needs only for add mode, when dont need to add blurred class
                            translatedInputs[i].placeholder = originalInput.value;
                            translatedInputs[i].addClass('blurred');
                        }
                    } else {
                        if(translatedInputs[i].innerText.trim() === "") {
                            originalInput.classList.remove('is_hidden');
                            translatedInputs[i].classList.add('is_hidden');
                            translatedInputs[i].closest(".form-item").classList.remove('translated');
                            translatedInputs[i].closest(".form-item").classList.add('no-translation');
                        }
                        else {
                            translatedInputs[i].closest(".form-item").classList.remove('no-translation');
                            translatedInputs[i].closest(".form-item").classList.add('translated');
                        }
                    }
                }
            }
        }
    }


    /**
     * handle those fields that are not translatable
     * @param form
     * @param action
     * @private
     */
    _toggleAllNotTranslatableFields(form, action) {
        if(!this.isViewMode()) {
            this._toggleNotTranslatableFieldsInAddEditMode(form, action);

        }else {
            this._toggleNotTranslatableFieldsInViewMode(form, action);
        }
    }


    /**
     *those fields that are not translatable give some classes;
     * @param form
     * @param action
     * @private
     */
    _toggleNotTranslatableFieldsInAddEditMode(form, action) {
        let formAllInputs = Array.from(new FormData(form));

        for (let i = 0; i < formAllInputs.length; i++) {
            let element = form.querySelector('[name="' + formAllInputs[i][0] + '"]');

            if(!element.hasAttribute('language-id')) {
                if(element.tagName === 'SELECT') {
                    let selectElement = element.closest('div.choices');
                    if(selectElement) {
                        (action === 'add') ? selectElement.addClass('not-translatable-field') : selectElement.removeClass('not-translatable-field');
                    }
                } else if(element.classList.contains('f_tinymce')) {
                    let tinymceElement = element.closest('.input-field').querySelector('.tox-tinymce');

                    if(tinymceElement) {
                        (action === 'add') ? tinymceElement.addClass('not-translatable-field') : tinymceElement.removeClass('not-translatable-field');
                    }
                } else {
                    (action === 'add') ? element.addClass('not-translatable-field') : element.removeClass('not-translatable-field');
                }
            }
        }
    }


    /**
     * those fields that are not translatable, need to give some classes IN VIEW MODE
     * @param form
     * @param action
     * @private
     */
    //todo: need to finish; now its do nothing
    _toggleNotTranslatableFieldsInViewMode(form, action) {
        let allViewFields = document.querySelectorAll('.form-item.view-mode .f_form-item-view-mode');
        allViewFields.forEach(field => {
            if(!field.closest('.form-item').querySelector('.f_translatable-field')) {
                if(action === 'add'){
                   //field.closest('.input-field').addClass('');
                }else {
                    //field.closest('.input-field').removeClass('');
                }
            }
        })
    }




    /**
     * if number field value should be only positive, this function forces to input only positive
     */
    blockNegativeNumbersInput() {
        let onlyPositiveNumbers = document.querySelectorAll('[only_positive="true"]');
        if(!onlyPositiveNumbers) {
            return;
        }
        onlyPositiveNumbers.forEach((element) => {
            element.addEventListener('input', () => {
                element.value = Math.abs(element.value) >= 0 ? Math.abs(element.value) : null;
            })
        })
    }


    initLogShowing() {
        if(!this.args().itemId || this.args().itemId <= 0) {
            return false;
        }
        if(document.getElementById('show-items-logs-btn-container')) {
            document.getElementById('show-items-logs-btn-container').removeClass('is_hidden');
        }else{
            return false;
        }
        let logShowBtn = document.getElementById('showItemsLogBtn');
        const logsList = document.getElementById('logs_list');
        const lastLog = document.getElementById('items_last_log');

        logShowBtn.addEventListener('mouseenter', (e) => {
            e.stopPropagation();
            e.preventDefault();
            let timer = setTimeout(()=>{
                if(logsList.classList.contains('is_hidden')) {
                    if(!this.isLastLogLoaded){
                        NGS.load('admin.loads.logs.list', {'itemId' : this.args().itemId, 'tableName' : this.args().tableName, offset : 0, limit: 1});
                        this.isLastLogLoaded = true;
                    }else{
                        lastLog.classList.toggle('is_hidden');
                    }
                }
            }, 750);
            logShowBtn.addEventListener('mouseleave', (e) => {
                clearTimeout(timer);
            })
        });

        [logShowBtn, lastLog].forEach((el) => {
            el.addEventListener('mouseleave', (e) => {
                e.stopPropagation();
                e.preventDefault();
                if(logsList.classList.contains('is_hidden') && !lastLog.classList.contains('is_hidden')) {
                    let timerToClose = setTimeout(function () {
                        lastLog.classList.add('is_hidden');
                    }, 200);
                    lastLog.addEventListener('mouseenter', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        clearTimeout(timerToClose);
                    })

                }
            });
        });

        logShowBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            lastLog.classList.add('is_hidden');
            if(!this.isLogsLoaded){
                NGS.load('admin.loads.logs.list', {'itemId' : this.args().itemId, 'tableName' : this.args().tableName, offset: 0, limit: 5});
                this.isLogsLoaded = true;
            }else{
                logsList.classList.toggle('is_hidden');
            }
        });




    }


    initGoToRelatedEntity() {
        let goToRelatedEntityBtn = document.querySelectorAll('.f_go-to-related-entity');
        if(!goToRelatedEntityBtn.length) {
            return;
        }
        goToRelatedEntityBtn.on('click', (e) => {
            let button = e.target.closest('.f_go-to-related-entity');
            let id = button.getAttribute('data-relation-id');
            let load = button.getAttribute('data-relation-load');

            if(id && load) {
                NGS.load(load, {itemId: id, doRefresh: true});
            }

        });
    }



    //todo: should be modified ASAP
    //now only in products viewLoad php adds true value for viewMode, because this functions were working not correct for products.
    //In other loads its default false, and it tries to checks in this function; Maybe to override in all view loads in php;

    isViewMode() {

        if(this.args().isViewMode){
            return true;
        }

        let form = document.querySelector('form.f_addUpdateForm');

        if(!form) {
            return true;
        }

        let allInputs = form.querySelectorAll('.input-field input');
        let allSelectBoxes = form.querySelectorAll('.input-field select');

        let inputsWithNameAttribute = Array.from(allInputs).filter(input => input.hasAttribute('name'));
        let selectBoxesWithNameAttribute = Array.from(allSelectBoxes).filter(selectBox => selectBox.hasAttribute('name'));

        if(!inputsWithNameAttribute.length && !selectBoxesWithNameAttribute.length) {
            return true;
        }
        return false;
    }


    /**
     * init rule management
     *
     */
    initRulesManager() {
        let container = document.getElementById(this.getContainer());
        let ruleButtons = container.querySelectorAll('.f_rule-btn');
        let itemId = this.args().itemId;
        for(let i=0; i<ruleButtons.length; i++) {
            ruleButtons[i].addEventListener('click', (evt) => {
                let ruleName = evt.target.closest('.f_rule-btn').getAttribute('data-rule-name');
                this._openRuleManagementWindow(ruleName, itemId);
            });
        }
    }


    _openRuleManagementWindow(ruleName, itemId) {
        NGS.load("ngs.cms.loads.rules.rules", {ruleName: ruleName, itemId: itemId, isViewMode: this.isViewMode()});
    }


}
