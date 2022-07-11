import AbstractLoad from '../../AbstractLoad.js';
import PagingManager from '../managers/PagingManager.js';
import GridManager from '../managers/GridManager.js';
import PageManager from '../managers/PageManager.js';
import DialogUtility from '../util/DialogUtility.js';
import MaterialsUtils from '../util/MaterialsUtils.js';
import StringUtility from '../util/StringUtility.js';
import ExportTemplatesManager from "../util/ExportTemplatesManager.js";
import FilterManager from "../util/FilterManager.js";
import ExcelExportUtil from "../util/ExcelExportUtil.js";
import Choices from "../lib/choices.min.js";
import RowsListManager from "../managers/RowsListManager.js";

export default class AbstractListLoad extends AbstractLoad {

    constructor() {
        super();
        this.sortingParams = {};
    }


    getContainer() {
        if (this.getListLoadLevel() !== 1) {
            if (this.args().cmsUUID) {
                return this.args().cmsUUID;
            }
        }

        return "loadContent";
    }

    afterLoad() {
        PageManager.initPageParams(this.args().pageParams);
        if (this.getContainer() === "modal") {
            this.loadedDialog = MaterialsUtils.createCmsModal(this.getModalTitle());
        }

        GridManager.init(this.getContainer());
        this.rowsListManager = new RowsListManager(document.querySelector('#' + this.getContainer()), this.getListLoadLevel());

        this.initSearchBox();
        this.initCheck();
        this.initEditItem();
        this.initRemoveItem();
        this.initDragAndDrop();
        this.initSorting();
        this.initExport();
        this.initItemRowClick();
        this.initBackBtn();
        this.initItemsSelections();
        this.getFilterValuesAndInitFilters();
        this.initChoices();
        this.rowsListManager.initRowsResizing(this);
        this.initAddButton();
        this.afterCmsLoad();
    }

    //todo: maybe need to init this button in AbstractCmsListLoad.js too, because now addButton is in list.tpl, not in main.tpl
    initAddButton() {
        let addBtns = document.querySelectorAll('#'+this.getContainer() + ' .f_addItemBtn');
        addBtns.unbindClick();
        addBtns.click(()=>{
            NGS.load(this.args().addLoad, {});
        });
    }


    /**
     * init filters via ajax
     *
     */
    getFilterValuesAndInitFilters() {

        NGS.action("ngs.AdminTools.actions.filters.list", {manager: this.args().manager}, (data) => {
            this.initFilters(data.filterValues);
            this.initPagination();
            this.initBulkActions(data.exportableFields);
            this.initChoices();

            if(this.getListLoadLevel() === 1) {
                this.setListDataInLocalStorage();
                this.initFavoriteFilters();
            }
        });

    }

    /**
     * store page data in local storage
     */
    setListDataInLocalStorage() {
        let mainSectionUuid = document.querySelector('main.main-section');
        let uuid = mainSectionUuid.getAttribute('data-ngs-uuid');

        let params = this._getNgsParams();
        if(this.filterManager) {
            let filter = null;
            filter = this.filterManager.getCurrentFilter();
            if(filter) {
                params.filter = filter;
            }
        }
        if (this.args().cmsUUID) {
            params.cmsUUID = this.args().cmsUUID;
        }
        localStorage.setItem(uuid + '_listLoadParams', JSON.stringify(params));
    }

    /**
     * init pagination
     *
     */
    initPagination() {
        let container = document.getElementById(this.getContainer());
        if(!container) {
            return;
        }
        let ajaxPaginationContainer = container.querySelector('.f_ajax-pagination');
        if(ajaxPaginationContainer) {
            this.initAjaxPaging();
        }
        else {
            this.initPaging();
        }
    }

    modifyFilterForLoad(filter) {
        return filter;
    }

    getListLoadLevel() {
        return 1;
    }

    hasViewLoad() {
        return true;
    }

    initChoices() {
        this.choices = {};
        let choicesElems = document.querySelectorAll('#' + this.getContainer() + ' .ngs-choice');
        for (let i = 0; i < choicesElems.length; i++) {
            let choiceElem = choicesElems[i];
            if (choiceElem.choices) {
                this.choices[choiceElem.id] = choiceElem.choices;
                continue;
            }
            this.choices[choiceElem.id] = new Choices(choiceElem,
              {
                  removeItemButton: choiceElem.getAttribute('data-ngs-remove') === 'true',
                  searchEnabled: choiceElem.getAttribute('data-ngs-searchable') === 'true',
                  renderChoiceLimit: 150,
                  searchResultLimit: 150,
                  shouldSort: true,
              });
        }
    }

    getChoiceElemById(elemId) {
        if(typeof this.choices[elemId] !== 'undefined'){
            return this.choices[elemId];
        }
        return null;
    }


    /**
     * init list bulk actions
     */
    initBulkActions(filterValues) {
        let exportActionButtons = document.querySelectorAll('#' + this.getContainer() + ' .f_bulk-action');
        exportActionButtons.click((event) => {
            let clickedBtn = event.target.closest('.f_bulk-action');
            let actionType = clickedBtn.getAttribute('data-type');
            let totalSelectionInfo = this.rowsListManager.getSelectionInfo();

            if(this.filterManager) {
                let currentFilter = this.filterManager.getCurrentFilter();
                if (Object.keys(totalSelectionInfo).length && ((currentFilter.and && currentFilter.and.length) || currentFilter.search)) {
                    totalSelectionInfo.filter = JSON.stringify(currentFilter);
                }
            }

            if (this.rowsListManager.nothingIsSelected()) {
                DialogUtility.showAlertDialog("No items", "Please, select items to", {
                    'oneButton': true,
                    actionType: StringUtility.toReadableText(actionType, false, false)
                });
                return;
            }
            if (actionType === 'export_excel') {
                if (this.args().bulkExcelExportAction) {
                    new ExportTemplatesManager(filterValues, this.args().itemType, (fields) => {
                        let exporter = new ExcelExportUtil(this.modifyFilterForLoad(totalSelectionInfo), this.args().bulkExcelExportAction, this.args().excelFileDownloadLoad);
                        exporter.exportFile(fields);
                    });

                }
            } else if (actionType === 'delete') {
                if (this.args().bulkDeleteAction) {
                    DialogUtility.showAlertDialog("Delete items", "Do you want to remove selected items ? This items can be used in other places.").then(function (confirmationMessage) {
                        NGS.action(this.args().bulkDeleteAction, this.modifyFilterForLoad(totalSelectionInfo));
                        this.rowsListManager.clearTotalSelectionAfterDeleteAction();
                    }.bind(this)).catch(function (error) {
                        console.log("canceled bulk delete");
                    });
                } else {
                    this.prepareDataToBulkDeleteForAdditionalTabs();
                }
            } else {
                this.doCustomAction(actionType, totalSelectionInfo)
            }
            setTimeout(() => {
                clickedBtn.closest(".dropdown-box").classList.remove("show");
            }, 100);

        });
    }


    doCustomAction(actionType, filter) {

    }

    /**
     * this function allows to apply bulk delete action before saving new added items.
     */
    prepareDataToBulkDeleteForAdditionalTabs() {
        let tab = document.querySelector('#' + this.getUiStorage()).closest('.f_cms_tab-container').getAttribute('id');
        let currentPageRowsList = document.getElementById(tab);
        let rows = currentPageRowsList.querySelectorAll('.f_table_row');
        let idsToDelete = [];
        let rowsToDelete = [];
        let tempIdsToDelete = [];
        let newRowsToDelete = [];


        //these 3 functions are made this way to have possibility to override in child list loads
        this.collectDataInArraysToRemoveForTotalSelection(rows, idsToDelete, rowsToDelete, tempIdsToDelete, newRowsToDelete);
        this.collectDataInArraysToRemoveForCheckedElements(rows, idsToDelete, rowsToDelete);
        this.collectDataInArraysToRemoveForNewAddedCheckedElements(rows, tempIdsToDelete, newRowsToDelete);


        this.deleteChildItemsByIdsForBulkAction(idsToDelete, rowsToDelete, tempIdsToDelete, newRowsToDelete, this.getUiStorage());
    }


    /**
     * if total selection is true in selection info this function pushes to arrays ids and rows of items, which should be removed after
     * @param rows
     * @param idsToDelete
     * @param rowsToDelete
     * @param tempIdsToDelete
     * @param newRowsToDelete
     */
    collectDataInArraysToRemoveForTotalSelection(rows, idsToDelete, rowsToDelete, tempIdsToDelete, newRowsToDelete) {
        let totalSelectionInfo = this.rowsListManager.getSelectionInfo();

        if (totalSelectionInfo.totalSelection && totalSelectionInfo.totalSelection === true) {
            for (let i = 0; i < rows.length; i++) {
                if (!totalSelectionInfo.unCheckedElements || !totalSelectionInfo.unCheckedElements.includes(rows[i].getAttribute('data-im-id'))) {
                    if (rows[i].hasAttribute('data-im-id') && rows[i].getAttribute('data-im-id') > 0) {
                        idsToDelete.push(rows[i].getAttribute('data-im-id'));
                        rowsToDelete.push(rows[i]);
                    }
                }
                if (!totalSelectionInfo.newAddedUnCheckedElements || !totalSelectionInfo.newAddedUnCheckedElements.includes(rows[i].getAttribute('data-im-index'))) {
                    if (rows[i].hasAttribute('data-im-index') && rows[i].getAttribute('data-im-id') === 'undefined') {
                        tempIdsToDelete.push(rows[i].getAttribute('data-im-index'));
                        newRowsToDelete.push(rows[i]);
                    }
                }
            }
        }
    }

    /**
     * if checked elements exists in selection info this function pushes to arrays ids and rows of items, which should be removed after
     * @param rows
     * @param idsToDelete
     * @param rowsToDelete
     */
    collectDataInArraysToRemoveForCheckedElements(rows, idsToDelete, rowsToDelete) {
        let totalSelectionInfo = this.rowsListManager.getSelectionInfo();

        if (totalSelectionInfo.checkedElements && totalSelectionInfo.checkedElements.length) {
            totalSelectionInfo.checkedElements.forEach((element) => {
                let id = element;
                for (let i = 0; i < rows.length; i++) {
                    if (rows[i].attr('data-im-id') === id) {
                        idsToDelete.push(id);
                        rowsToDelete.push(rows[i]);
                        break;
                    }
                }
            });
        }
    }

    /**
     * if new added checked elements exists in selection info this function pushes to arrays ids and rows of items, which should be removed after
     * @param rows
     * @param tempIdsToDelete
     * @param newRowsToDelete
     */
    collectDataInArraysToRemoveForNewAddedCheckedElements(rows, tempIdsToDelete, newRowsToDelete) {
        let totalSelectionInfo = this.rowsListManager.getSelectionInfo();

        if (totalSelectionInfo.newAddedCheckedElements && totalSelectionInfo.newAddedCheckedElements.length) {
            totalSelectionInfo.newAddedCheckedElements.forEach((element) => {
                let index = element;
                for (let i = 0; i < rows.length; i++) {
                    if (rows[i].attr('data-im-index') === index) {
                        tempIdsToDelete.push(index);
                        newRowsToDelete.push(rows[i]);
                        break;
                    }
                }
            });
        }
    }


    /**
     * init list item selections
     *
     */
    initItemsSelections() {
        let checkItemBtn = document.querySelectorAll("#" + this.getContainer() + " .f_check-item");
        let totalSelectionInfo = this.rowsListManager.getSelectionInfo();
        if (totalSelectionInfo.totalSelection) {
            this.rowsListManager.changeAllCheckboxItems(true);
        }
        if((totalSelectionInfo.checkedElements && totalSelectionInfo.checkedElements.length) ||
          (totalSelectionInfo.unCheckedElements && totalSelectionInfo.unCheckedElements.length)) {
            for(let i=0; i<checkItemBtn.length; i++) {
                let itemRow = checkItemBtn[i].closest(".f_table_row");
                if(!itemRow) {
                    continue;
                }

                if(totalSelectionInfo.checkedElements && totalSelectionInfo.checkedElements.indexOf(itemRow.getAttribute("data-im-id")) !== -1) {
                    checkItemBtn[i].checked = true;
                }
                else if(totalSelectionInfo.unCheckedElements && totalSelectionInfo.unCheckedElements.indexOf(itemRow.getAttribute("data-im-id")) !== -1) {
                    checkItemBtn[i].checked = false;
                }
            }
        }
        
        checkItemBtn.keyup((evt) => {
            evt.stopImmediatePropagation();
            if (evt.key === 'Shift' && this.rowsListManager.getIsShiftPressed()) {
                this.rowsListManager.setIsShiftPressed(false);
            }
        });

        checkItemBtn.keydown((evt) => {
            evt.stopImmediatePropagation();
            if (evt.key === 'Shift') {
                this.rowsListManager.setIsShiftPressed(true);
            }
        });

        checkItemBtn.change((evt) => {
            let checkboxItem = evt.target;
            let isAllCheckboxChanged = !checkboxItem.closest('.f_table_row');
            if (isAllCheckboxChanged) {
                this.rowsListManager.handleMainSelectionCheckbox(checkboxItem);
            } else {
                this.rowsListManager.handleElementSelectionChange(checkboxItem, false);

                if (this.rowsListManager.needToChangeMainSelectionCheckbox()) {
                    this.rowsListManager.handleMainSelectionCheckbox(checkboxItem);
                }

            }
        })
    }

    /**
     * init functional for favorite filters
     */
    initFavoriteFilters() {
        let saveFilterBtn = document.querySelectorAll("#" + this.getContainer() + ' .f_save-filter');
        saveFilterBtn.click((evt) => {
            this._saveFavoriteFilter(evt);
        });

        let savedFilters = document.querySelectorAll("#" + this.getContainer() + ' .f_saved-filters .f_saved-filter');
        savedFilters.click((evt) => {
            this._applyFilterHandler(evt);
        });


        let deleteFiltersButton = document.querySelectorAll("#" + this.getContainer() + ' .f_saved-filters .f_delete-filter');
        deleteFiltersButton.click((evt) => {
            evt.stopPropagation();
            this._deleteFavoriteFilterHandler(evt);
        });
    }


    /**
     * function to handle save favorite filter
     *
     * @param evt
     *
     * @private
     */
    _saveFavoriteFilter(evt) {
        let favoriteFilterContainer = evt.target.closest('.f_favorite-filter');
        let itemType = favoriteFilterContainer.getAttribute('ngs-filter-type');
        let filterNameInput = favoriteFilterContainer.querySelector('.f_filter-name');
        let errorMessage = favoriteFilterContainer.querySelector('.f_error-message');
        let filterName = filterNameInput.value;
        if (!filterName) {
            filterNameInput.classList.add('error');
            errorMessage.innerText = 'name can not be empty';
            return;
        }
        if(this.filterManager) {
            let currentFilter = this.filterManager.getCurrentFilter();
            if ((currentFilter.and && !currentFilter.and.length) && !currentFilter.search) {
                errorMessage.innerText = 'you can not save empty filter';
                return;
            }
        }

        currentFilter = JSON.stringify(currentFilter);
        NGS.action("admin.actions.filter.save", {
            item_type: itemType,
            name: filterName,
            filter: currentFilter
        }, function (data) {
            if (!data.error) {
                this._addNewFavorite(favoriteFilterContainer, filterName, currentFilter, data.id);
                favoriteFilterContainer.classList.remove('show');
            } else {
                errorMessage.innerText = data.message ? data.message : "error";
            }
        }.bind(this));

        filterNameInput.value = '';
    }

    _addNewFavorite(favoriteFilterContainer, filterName, currentFilter, id) {
        let newSavedFilter = favoriteFilterContainer.querySelector('.f_copy-favorite-filter');
        newSavedFilter = newSavedFilter.cloneNode(true);
        newSavedFilter.removeAttribute('style');
        newSavedFilter.classList.remove('f_copy-favorite-filter');
        newSavedFilter.querySelector('.f_filter-display-name').innerText = filterName;
        newSavedFilter.setAttribute('ngs-filter', currentFilter);
        newSavedFilter.setAttribute('ngs-filter-id', id);
        favoriteFilterContainer.querySelector('.f_saved-filters').prepend(newSavedFilter);

        newSavedFilter.addEventListener('click', (evt) => {
            this._applyFilterHandler(evt);
        });

        let deleteFiltersButton = newSavedFilter.querySelectorAll('.f_delete-filter');
        console.log(deleteFiltersButton);
        deleteFiltersButton.click((evt) => {
            evt.stopPropagation();
            this._deleteFavoriteFilterHandler(evt);
        });
    }


    /**
     * function to handle apply favorite filter
     *
     * @param evt
     *
     * @private
     */
    _applyFilterHandler(evt) {
        let item = evt.target.closest('.f_saved-filter');
        let selectedFilter = item.getAttribute('ngs-filter');
        let params = PageManager.getGlobalParams();
        params.filter = JSON.parse(selectedFilter);
        params.favoriteFilter = item.innerText;
        if (this.args().cmsUUID) {
            params.cmsUUID = this.args().cmsUUID;
        }
        NGS.load(this.args().listLoad, this.modifyFilterForLoad(params));
    }


    /**
     * function to handle delete favorite filter
     *
     * @param evt
     *
     * @returns {boolean}
     * @private
     */
    _deleteFavoriteFilterHandler(evt) {
        DialogUtility.showAlertDialog("Delete item", "Do you want to remove this favorite filter ?").then(function (confirmationMessage) {
            evt.stopPropagation();
            evt.preventDefault();
            let item = evt.target.closest('.f_saved-filter');
            let selectedFilterId = item.getAttribute('ngs-filter-id');
            NGS.action("admin.actions.filter.delete", {filter_id: selectedFilterId}, function (data) {
                if (!data.error) {
                    item.remove();
                }
            }.bind(this));
            return false;
        });
    }


    getDtoName() {
        return "";
    }

    setDtosInput(dtos) {
        var dtosName = this.getDtoName();
        if (!dtosName) {
            return;
        }
        let container = document.getElementById(this.getContainer());
        let dtosToUpdate = document.querySelectorAll('#' + this.getContainer() + ' .f_dtos_to_update');
        if (!dtosToUpdate.length) {
            dtosToUpdate = document.createElement("input");
            dtosToUpdate.type = "hidden";
            dtosToUpdate.className = "f_dtos_to_update";
            dtosToUpdate.name = dtosName;
            container.appendChild(dtosToUpdate);
        } else {
            dtosToUpdate = dtosToUpdate[0];
        }
        dtosToUpdate.setAttribute('value', JSON.stringify(dtos));
    }

    childDtosChangeHandler(changedDtos) {
        this.args().childLoads.itemDtos = changedDtos;
        let table = document.querySelectorAll('#' + this.getContainer() + ' .f_cms-table-container');
        table[0].innerHTML = "";

        let customerContactTemplate = document.querySelector('#' + this.getContainer() + ' .f_listItemTemplate').innerHTML;
        for (let i = 0; i < changedDtos.length; i++) {
            let object = changedDtos[i];
            object.item_index = i;
            let contactRow = this.renderTemplate(customerContactTemplate, object);
            table[0].appendChild(contactRow);
        }


    }

    childAddLoad(params) {
        params.changeHandler = this.childDtosChangeHandler.bind(this);
        let addBtn = document.querySelectorAll('#' + this.getContainer() + ' .f_addItemBtn');
        addBtn.unbindClick().click((evt) => {
            NGS.load(this.args().addLoad, {}, null, params);
        });
    }

    childEditLoad(params) {
        params.changeHandler = this.childDtosChangeHandler.bind(this);
        document.querySelectorAll('#' + this.getContainer() + ' .f_edit_btn').click(event => {
            event.stopPropagation();
            let itemIndex = event.currentTarget.closest(".f_table_row").attr('data-im-index');
            params.updateIndex = itemIndex;
            let loadParams = this._getNgsParams();
            loadParams.itemDto = this.args().childLoads.itemDtos[itemIndex];
            NGS.load(this.args().editLoad, loadParams, null, params);
        });
    }

    childDeleteAction() {
        document.querySelectorAll('#' + this.getContainer() + ' .f_delete_btn').click(event => {
            event.stopPropagation();
            DialogUtility.showAlertDialog("Delete item", "Do you want to remove this item ? This item can be used in other places.").then(function (confirmationMessage) {
                let itemIndex = event.target.closest(".f_table_row").attr('data-im-index');
                this.args().childLoads.itemDtos.splice(itemIndex, 1);
                this.childDtosChangeHandler(this.args().childLoads.itemDtos);
            }.bind(this)).catch(function (error) {
                console.log("canceled");
            });
        });
    }

    initSearchBox() {
        if (this.args().searchKey) {
            $("#searchKey").val(this.args().searchKey);
            if (M.updateTextFields) {
                M.updateTextFields();
            }
        }
    }


    getUiStorage() {
        return null;
    }


    onError(params) {
        MaterialsUtils.showErrorDialog(params.msg);
    }

    initEditItem() {
        document.querySelectorAll('#' + this.getContainer() + ' .f_edit_btn').click(event => {
            event.stopPropagation();
            let itemId = event.currentTarget.attr('data-im-id');
            let params = {}; //this._getNgsParams();
            params.itemId = itemId;
            params.fromListingPage = true;
            NGS.load(this.args().editLoad, params);
            return false;
        });
    }


    initBackBtn() {
        let childLoads = this.args().childLoads;
        if (childLoads && childLoads.childs && childLoads.childs.length) {
            return;
        }

        let backBtn = document.querySelectorAll('.f_back-btn');
        backBtn.unbindClick();
        for (let i = 0; i < backBtn.length; i++) {
            backBtn[i].classList.add('hide');
        }
    }

    /**
     * row click action handler
     */
    initItemRowClick() {
        document.querySelectorAll('#' + this.getContainer() + ' .f_table_row').dblclick(event => {
            if (!this.hasViewLoad()) {
                return false;
            }
            if (event.target.closest(".checkbox-item")) {
                return false;
            }
            if (!this.args().rowClickLoad || !this.args().rowClickLoad.trim()) {
                return false;
            }
            let itemId = event.currentTarget.attr('data-im-id');
            let rowClickParams = {};
            rowClickParams.itemId = itemId;
            NGS.load(this.args().rowClickLoad, rowClickParams);
        });
    }

    /**
     * row remove action handler
     */
    initRemoveItem() {
        $('#' + this.getContainer() + ' .f_delete_btn').on("click", function (evt) {
            this.removeItem(evt);
        }.bind(this));
    }

    /**
     *
     * @param evt
     */
    removeItem(evt) {
        evt.stopPropagation();
        let elem = evt.target.closest('.f_delete_btn');
        let itemId = elem.getAttribute('data-im-id');

        if (this.getUiStorage()) {
            this.deleteChildItemById(itemId, null, elem, this.getUiStorage());
        } else {
            this.deleteItemById(itemId);
        }
    }


    /**
     *
     * @param itemId
     * @param tempId
     * @param elem
     * @param inputSelector
     */
    deleteChildItemById(itemId, tempId, elem, inputSelector) {
        DialogUtility.showAlertDialog("Delete item", "Do you want to remove this item ? This item can be used in other places.").then(function (confirmationMessage) {
            this.rowsListManager.changeItemsCountInUi(-1);
            elem.closest(".f_table_row").remove();

            let itemsInput = document.getElementById(inputSelector);
            let itemsData = itemsInput.value;
            if (!itemsData) {
                itemsData = [];
            } else {
                itemsData = JSON.parse(itemsData);
            }

            if (itemId) {
                itemsData = this.changeExistingElement(itemsData, {id: itemId, toDelete: true});
                itemsInput.value = JSON.stringify(itemsData);
            } else {
                for (let i = 0; i < itemsData.length; i++) {
                    if (itemsData[i].tempId && itemsData[i].tempId === tempId) {
                        itemsData.splice(i, 1);
                        break;
                    }
                }
            }
        }.bind(this)).catch(function (error) {
            console.log("canceled");
        });
    }


    deleteChildItemsByIdsForBulkAction(itemsIds, rows, newItemsTempIds, newRowsToDelete, inputSelector) {

        newItemsTempIds.forEach((id, i) => {
            newItemsTempIds[i] = +id;
        });

        DialogUtility.showAlertDialog("Delete item", "Do you want to remove selected items ? This item can be used in other places.").then(function (confirmationMessage) {
            let itemsInput = document.getElementById(inputSelector);
            let itemsData = itemsInput.value;
            if (!itemsData) {
                itemsData = [];
            } else {
                itemsData = JSON.parse(itemsData);
            }

            for (let i = 0; i < itemsIds.length; i++) {
                rows[i].remove();
                itemsData = this.changeExistingElement(itemsData, {id: itemsIds[i], toDelete: true});
            }

            //this function is to have possibility to override in child list loads
            let valueToSetToItemsInput = this.removeItemsWithTempIdsAndGetValueToSetToUiStorage(itemsData, newItemsTempIds, newRowsToDelete);

            itemsInput.value = valueToSetToItemsInput.length ? JSON.stringify(valueToSetToItemsInput) : '';


            this.rowsListManager.clearTotalSelectionAfterDeleteAction();
            let allSelectCheckbox = document.getElementById(inputSelector).closest('.f_list-load-container').querySelector('#gridHeader').querySelector('.f_check-item');
            if (allSelectCheckbox && allSelectCheckbox.checked) {
                allSelectCheckbox.checked = false;
            }

            this.rowsListManager.changeItemsCountInUi(-(itemsIds.length + newItemsTempIds.length));

        }.bind(this)).catch(function (error) {
            console.log("canceled");
        });
    }


    /**
     * this function removes items which are new added and not saved yet.
     * Is only suitable for those list loads where we add new forms.
     * @param itemsData
     * @param newItemsTempIds
     * @param newRowsToDelete
     * @returns {*}
     */
    removeItemsWithTempIdsAndGetValueToSetToUiStorage(itemsData, newItemsTempIds, newRowsToDelete) {
        for (let i = 0; i < itemsData.length; i++) {
            if (itemsData[i].hasOwnProperty('tempId') && newItemsTempIds.indexOf(+(itemsData[i].tempId)) !== -1) {
                itemsData[i] = undefined;   //cannot do splice because in the next time of loop the indexes will not mutch
            }
        }
        newRowsToDelete.forEach((row) => {
            row.remove();
        });

        return itemsData.filter((x) => x !== undefined);
    }


    /**
     *
     * @param items
     * @param item
     * @returns {*}
     */
    changeExistingElement(items, item) {
        let exists = false;
        // debugger
        for (let i = 0; i < items.length; i++) {
            if (item.id && items[i].id && items[i].id === item.id) {
                items[i] = item;
                exists = true;
                break;
            }
            if (item.tempId && items[i].tempId && items[i].tempId === item.tempId) {
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


    /**
     *
     * @param itemId
     */
    deleteItemById(itemId) {
        DialogUtility.showAlertDialog("Delete item", "Do you want to remove this item ? This item can be used in other places.").then(function (confirmationMessage) {
            let filter = this.filterManager ? this.filterManager.getCurrentFilter() : null;
            let params = {
                itemId: itemId,
                confirmationMessage: confirmationMessage,
            };
            params.filter = filter;

            NGS.action(this.args().deleteAction, params, function (success) {

            }, function (error) {
                if (error.params.confirmation_required) {
                    DialogUtility.showConfirmDialog("Delete Confirmation", "Do you want to remove this item ? This item can be used in other places.", null, error.params.confirmation_text, error.params.error_reason).then(function (confirmationMessage) {
                        //null, this.args().deleteConfirmationMessage
                        params.confirmationMessage = confirmationMessage;
                        NGS.action(this.args().deleteAction, params);
                    }.bind(this)).catch(function (error) {
                        console.log("canceled");
                    });
                    return false;
                }
            }.bind(this));
        }.bind(this)).catch(function (error) {
            console.log("canceled");
        });
    }

    initCheck() {
        $("#" + this.getContainer() + " .f_check").on("click", function (evt) {
            evt.stopPropagation();
        });
    }


    initPaging() {
        PagingManager.init((args) => {
            let params = Object.assign(this._getNgsParams(), args);
            if (this.filterManager) {
                params.filter = this.filterManager.getCurrentFilter();
            }
            if (this.args().cmsUUID) {
                params.cmsUUID = this.args().cmsUUID;
            }
            NGS.load(this.args().listLoad, this.modifyFilterForLoad(params));
        }, document.getElementById(this.getContainer()));
    }


    initAjaxPaging() {
        this.initPaging();
        let params = Object.assign(this._getNgsParams(), {});
        params.filter = this.filterManager ? this.filterManager.getCurrentFilter() : {}
        if (this.args().cmsUUID) {
            params.cmsUUID = this.args().cmsUUID;
        }
        params = this.modifyFilterForLoad(params);
        params.manager = this.args().manager;
        params.parentContainer = this.getContainer();
        NGS.load("ngs.AdminTools.loads.pagination.show", params, (data) => {
            PageManager.initPageParams(data.pageParams);
            this.initChoices();
            this.initPaging();
        });
    }

    initSorting() {
        document.querySelectorAll("#" + this.getContainer() + " .f_sorting").forEach((sortableElem) => {
            sortableElem.addEventListener('click', evt => {
  							evt.preventDefault();
                if(this.rowsListManager){
                    this.rowsListManager.removeSelectedItems();
                }
                if (this.columnsResizingClicked) {
                    this.columnsResizingClicked = false;
                    return;
                }

                let order = 'desc';
                let sortBy = evt.currentTarget.attr('data-im-sorting');
                if (evt.currentTarget.attr('data-im-order') === 'desc') {
                    order = 'asc';
                }
                let params = PageManager.getPageParams();
                params.ordering = order;
                params.sorting = sortBy;
                if (this.args().cmsUUID) {
                    params.cmsUUID = this.args().cmsUUID;
                }
                if (this.args().parentId) {
                    params.parentId = this.args().parentId;
                }
                params.filter = this.filterManager ? this.filterManager.getCurrentFilter() : {};
                NGS.load(this.args().listLoad, this.modifyFilterForLoad(params));
            });
        });
    }


    getAdditionalParams(params) {
        return params;
    }


    initFilters(filterValues) {
        if (filterValues) {
            let preselectedFilter = this.args().filter;
            this.filterManager = new FilterManager('mainFilter', {possibleFilters: filterValues}, preselectedFilter);
            this.filterManager.onFilterChange((filter) => {
                let params = PageManager.getGlobalParams();
                params.filter = filter;
                params.offset = 0;
                params.page = 1;
                if (this.args().cmsUUID) {
                    params.cmsUUID = this.args().cmsUUID;
                }
                NGS.load(this.args().listLoad, this.modifyFilterForLoad(params));
            });
        }
    }

    initExport() {
        const exportBtn = document.getElementById('exportBtn');
        const filterForm = document.getElementById('cmsFilterBox');
        if (!exportBtn) {
            return;
        }
        exportBtn.addEventListener('click', (evt) => {
            evt.preventDefault();
            let formData = new FormData(filterForm);
            if (formData === false) {
                return false;
            }

            if (this.args().parentId) {
                formData.append('parentId', this.args().parentId);
            }

            formData = this._mergeWithPageParams(formData);
            if (!this.args().exportLoad) {
                alert("no export load created!");
                return false;
            }
            NGS.load(this.args().exportLoad, {filterParams: this._getFilters(formData)});
            document.getElementById('ajax_loader').addClass('is_hidden');
            return false;
        });
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
     * Returns formdata keys and values as object
     *
     * @param formData
     * @private
     */
    _getFilters(formData) {

        let obj = {};
        for (let pair of formData.entries()) {
            obj[pair[0]] = pair[1];
        }

        return obj;
    }

    getFilterParams() {
        if (this.args().filterParams) {
            return {filterParams: this.args().filterParams};
        }
        return {};
    }

    _getNgsParams() {
        let params = {};
        params = Object.assign(params, PageManager.getPageParams());
        params = Object.assign(this.getFilterParams(), params);
        params = this.getAdditionalParams(params);
        if (this.args().parentId) {
            params.parentId = this.args().parentId;
        }
        return params;
    }

    getModalTitle() {
        return 'modal';
    }

    afterCmsLoad() {

    }

    getMethod() {
        return "GET";
    }

    getPermalink() {
        return super.getPermalink();
    }

    initDragAndDrop() {

    }

}
