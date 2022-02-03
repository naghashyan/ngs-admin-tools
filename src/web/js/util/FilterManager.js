import Choices from "../lib/choices.min.js";

export default class FilterManager {

    static instances = {};

    /**
     * creates instance of filter
     *
     * @param filterBoxId
     * @param filterableModel
     * @param preselectedFilter
     */
    constructor(filterBoxId, filterableModel, preselectedFilter) {
        if (FilterManager.instances[filterBoxId]) {

            if (filterBoxId === 'mainFilter') {
                let oldElement = document.getElementById(filterBoxId);
                let newElement = oldElement.cloneNode(true);
                oldElement.parentNode.replaceChild(newElement, oldElement);
            } else {
                FilterManager.destroy(filterBoxId);
            }
        }

        this.filterBoxId = filterBoxId;

        //todo: what is this for?
        let filterInitEvent = new CustomEvent('ngsFilterInit', {
            detail: {
                id: filterBoxId
            }
        });
        document.dispatchEvent(filterInitEvent);

        this.filterChangeHandler = null;
        this.possibleFilters = filterableModel.possibleFilters;

        this._initFilterControlPanel();
        this._initSearchCriteria();

        FilterManager.instances[filterBoxId] = this;
        if (preselectedFilter) {
            let filterCriterias = preselectedFilter.and;

            if (filterCriterias) {
                for (let i = 0; i < filterCriterias.length; i++) {
                    this.addCriteriaToExistingOnes(filterCriterias[i], false);
                }
            }

            if (preselectedFilter.search) {
                this._addSearchedCriteriaItemToSearchInputBox(null, preselectedFilter.search);
            }
            let searchInput = document.querySelector('#' + filterBoxId + ' .f_search-criteria');
            if (searchInput) {
                searchInput.focus();
            }
        }

        this.initShowDetailedInfosOfCriterias(document.getElementById(this.filterBoxId));
    }

    onFilterChange(handler) {
        if (typeof handler === 'function' && !this.filterChangeHandler) {
            this.filterChangeHandler = handler;
        }
    }

    static destroy(filterBoxId) {
        delete FilterManager.instances[filterBoxId];
    }

    handleFilterChange() {
        let container = document.getElementById(this.filterBoxId);
        let criterias = container.querySelectorAll('.f_filter-item');
        this.addCriteriaInfos(criterias);
        if (this.filterChangeHandler) {
            this.filterChangeHandler(this.getCurrentFilter());
        }
    }


    /**
     * get all filters data from filter items (which are in the search input box)
     * @returns {{}}
     */
    getCurrentFilter() {
        let filterBox = document.getElementById(this.filterBoxId);
        if (!filterBox) {
            return {};
        }

        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');
        let activeFilterItems = activeFiltersContainer.querySelectorAll('.f_filter-item');
        let activeSearchItem = activeFiltersContainer.querySelector('.f_search-item');

        let filterData = {};
        if (activeFilterItems.length) {
            let filter = [];

            activeFilterItems.forEach(activeFilterItem => {
                filter.push(JSON.parse(activeFilterItem.getAttribute('data-ngs-filter-value')));
            });

            filterData.and = filter;
        }

        if (activeSearchItem) {
            filterData.search = JSON.parse(activeSearchItem.getAttribute('data-ngs-filter-value'));
        }

        return filterData;
    }


    /**
     * init functional of open/close main control panel
     *
     * @private
     */
    _initFilterControlPanel() {
        let filterBox = document.getElementById(this.filterBoxId);
        let openFilterControlPanelBtn = document.querySelectorAll('#' + this.filterBoxId + ' .f_filter-add-criteria');

        openFilterControlPanelBtn.forEach(btn => {
            btn.addEventListener('click', evt => {

                let filterMainControlPanelBox = filterBox.querySelector('.f_filter-main-control-panel-box');

                if (filterMainControlPanelBox) {
                    //is now opened
                    if (this.filterMainControlPanelCloseHandler) {
                        document.removeEventListener('click', this.filterMainControlPanelCloseHandler);
                        this.filterMainControlPanelCloseHandler = null;
                    }

                    filterMainControlPanelBox.remove();

                } else {

                    filterBox.appendChild(this._createFilterMainControlPanelBox());

                    this._createContainerForAndAssocCriteriasGroup();
                    this._initAddNewCriteriaRowBtn();

                    this._initApplyCriteriaItem();
                    this._initCancelCriteriaCreation();

                    let filterMainControlPanelBox = filterBox.querySelector('.f_filter-main-control-panel-box');

                    this.filterMainControlPanelCloseHandler = evt => {
                        if (evt.target.closest('.f_filter-main-control-panel-box') || evt.target.closest('.f_filter-add-criteria')) {
                            return;
                        }
                        this.toggleFilterControlPanelButtonClass();
                        filterMainControlPanelBox.remove();
                        document.removeEventListener('click', this.filterMainControlPanelCloseHandler);
                        this.filterMainControlPanelCloseHandler = null;
                    };

                    document.addEventListener('click', this.filterMainControlPanelCloseHandler);
                }

                this.toggleFilterControlPanelButtonClass();
            })
        })
    }


    /**
     * init functional of search list by criteria
     * @private
     */
    _initSearchCriteria() {
        let searchByCriteriaInputField = document.querySelector('#' + this.filterBoxId + ' .f_search-criteria');
        if (!searchByCriteriaInputField) {
            return;
        }

        searchByCriteriaInputField.addEventListener('keydown', (evt) => {

            //enter was pressed
            if (evt.keyCode === 13) {
                if (searchByCriteriaInputField.value.trim()) {
                    this._addSearchedCriteriaItemToSearchInputBox(searchByCriteriaInputField.value);

                    searchByCriteriaInputField.value = '';
                    this.handleFilterChange();
                }
            }
        });

    }


    /**
     * creates filter control panel box (the main container)
     * @returns {HTMLDivElement}
     * @private
     */
    _createFilterMainControlPanelBox() {
        let filterMainControlPanelBox = document.createElement("div");
        filterMainControlPanelBox.classList.add('create-criteria-box', 'f_filter-main-control-panel-box');

        let allCriteriasAndAddBtnContainer = document.createElement("div");
        allCriteriasAndAddBtnContainer.classList.add('criteria-items-box', 'f_all-criteria-rows-and-add-btn-container');

        filterMainControlPanelBox.appendChild(allCriteriasAndAddBtnContainer);

        let allCriteriasContainer = document.createElement("div");
        allCriteriasContainer.classList.add('criteria-item-container', 'f_all-criteria-rows-container');

        allCriteriasAndAddBtnContainer.appendChild(allCriteriasContainer);

        let actionsBtnsContainer = document.createElement("div");
        actionsBtnsContainer.classList.add('actions-box');

        actionsBtnsContainer.innerHTML = `<span class="apply-criteria button basic primary f_apply-filters-btn">Apply Criteria</span>
                                          <span class="cancel-criteria button outline f_cancel-filters-btn">Cancel</span>`;

        filterMainControlPanelBox.appendChild(actionsBtnsContainer);

        let addNewCriteriaRowBtnBox = document.createElement("div");
        addNewCriteriaRowBtnBox.classList.add('add-criteria-box', 'f_add-new-group-of-and-assoc-criterias-btn-container');
        addNewCriteriaRowBtnBox.innerHTML = `<div class="f_or-delimiter or-delimiter" data-index-of-delimiter="1">Or</div><button class="add-criteria button basic f_add-new-criteria-row-btn">
                                             <span class="circle"><i class="icon-svg179"></i></span></button>`;

        allCriteriasAndAddBtnContainer.appendChild(addNewCriteriaRowBtnBox);

        return filterMainControlPanelBox;
    }


    /**
     * on input change if it becomes valid,  invalid class should be removed
     * @private
     */
    _initEventListenersForTogglingInvalidClass(container = null) {
        let allCriteriaItemsToValidate;

        if (!container) {
            allCriteriaItemsToValidate = document.querySelectorAll('#' + this.filterBoxId + ' .f_filter-main-control-panel-box .f_criteria-item-to-validate:not([data-toggling-field-listener-is-set])');
        } else {
            allCriteriaItemsToValidate = container.querySelectorAll('.f_criteria-item-to-validate:not([data-toggling-field-listener-is-set])');
        }
        allCriteriaItemsToValidate.forEach((item) => {
            item.setAttribute('data-toggling-field-listener-is-set', 'true');
            item.addEventListener('change', () => {
                if (!this.isFieldInvalid(item)) {
                    if (item.tagName === 'SELECT') {
                        item.closest('.choices__inner').classList.remove('invalid-criteria-item', 'f_invalid-criteria-item');
                    } else {
                        item.querySelector('input').classList.remove('invalid-criteria-item', 'f_invalid-criteria-item');
                    }
                }
            })
        })
    }


    /**
     * initialization of new criteria row adding button
     * @private
     */
    _initAddNewCriteriaRowBtn() {
        let addCriteriaItemButton = document.querySelector('#' + this.filterBoxId + ' .f_filter-main-control-panel-box .f_add-new-criteria-row-btn');

        addCriteriaItemButton.addEventListener('click', evt => {
            this._initEventListenersForTogglingInvalidClass();
            let invalidFields = this.getInvalidFields();
            if (!invalidFields.length) {
                this._createOrDelimiter();
                this._createContainerForAndAssocCriteriasGroup();
                this._toggleAddGroupOfAndAssocCriteriaBtn();
            } else {
                invalidFields.forEach((invalidField) => {
                    invalidField.classList.add('invalid-criteria-item', 'f_invalid-criteria-item');
                })
            }
        });
    }

    _createOrDelimiter(forReturn = false) {
        let orDelimiter = document.createElement('div');
        orDelimiter.classList.add('f_or-delimiter', 'or-delimiter');
        orDelimiter.setAttribute('data-index-of-delimiter', (this._getCountOfAndAssocCriteriasGroupsAtMoment() + ''));
        orDelimiter.innerText = "Or";


        if (forReturn) {
            return orDelimiter;
        }

        let allCriteriaRowsContainer = document.querySelector('#' + this.filterBoxId + ' .f_all-criteria-rows-container');
        allCriteriaRowsContainer.appendChild(orDelimiter);
    }


    /**
     * returns all fields that are not filled correct (are invalid)
     * @returns {[]}
     */
    getInvalidFields(container = null) {
        let allFields;
        if (!container) {
            allFields = document.querySelectorAll('#' + this.filterBoxId + ' .f_filter-main-control-panel-box .f_criteria-item-to-validate');
        } else {
            allFields = container.querySelectorAll('.f_criteria-item-to-validate');
        }

        let invalidFields = [];

        for (let i = 0; i < allFields.length; i++) {
            let field = allFields[i];

            if (this.isFieldInvalid(field)) {
                if (field.tagName === 'SELECT') {
                    invalidFields.push(field.closest('.choices__inner'));
                } else {
                    invalidFields.push(field.querySelector('input'));
                }
            }
        }
        return invalidFields;
    }


    /**
     * @param criteriaItem
     * @returns {boolean}
     */
    isFieldInvalid(criteriaItem) {
        if (criteriaItem.tagName === 'SELECT') {
            if (criteriaItem.value.trim() === '' || criteriaItem.value === 'please select field') {
                return true;
            }
        } else {
            let inputField = criteriaItem.querySelector('input');

            if (inputField.value.trim() === '' || inputField.value === 'please select field') {
                return true;
            }
        }
        return false;
    }


    /**
     * init apply button
     * @private
     */
    _initApplyCriteriaItem() {
        let filterMainControlPanel = document.querySelector('#' + this.filterBoxId + ' .f_filter-main-control-panel-box');
        let applyFiltersBtn = filterMainControlPanel.querySelector('.f_apply-filters-btn');

        applyFiltersBtn.addEventListener('click', evt => {

            this._initEventListenersForTogglingInvalidClass();

            let invalidFields = this.getInvalidFields();
            if (invalidFields.length) {
                invalidFields.forEach((invalidField) => {
                    invalidField.classList.add('invalid-criteria-item', 'f_invalid-criteria-item');
                });
                return;
            }

            let allGroupsOfAndAssocCriteriaRows = filterMainControlPanel.querySelectorAll('.f_and-assoc-rows-container');

            if (!allGroupsOfAndAssocCriteriaRows || !allGroupsOfAndAssocCriteriaRows.length) {
                if (this.filterMainControlPanelCloseHandler) {
                    document.removeEventListener('click', this.filterMainControlPanelCloseHandler);
                    this.filterMainControlPanelCloseHandler = null;
                }

                filterMainControlPanel.remove();
                return;
            }

            let filterItems = this.collectDataOfAllCriterias(allGroupsOfAndAssocCriteriaRows);
            this.addCriteriaToExistingOnes({or: filterItems}, true, filterMainControlPanel.getAttribute('criteria-box-number'));

            this.toggleFilterControlPanelButtonClass();
            if (this.filterMainControlPanelCloseHandler) {
                document.removeEventListener('click', this.filterMainControlPanelCloseHandler);
                this.filterMainControlPanelCloseHandler = null;
            }
            filterMainControlPanel.remove();
        });
    }


    /**
     * collects data of all filter criterias of active filter control panel
     * @param andAssocCriteriaItemsGroups
     * @returns {[]}
     */
    collectDataOfAllCriterias(andAssocCriteriaItemsGroups) {
        let res = [];
        for (let i = 0; i < andAssocCriteriaItemsGroups.length; i++) {

            let subcriteriasOfCurrentCriteriaGroup = andAssocCriteriaItemsGroups[i].querySelectorAll('.f_sub-criteria-item');

            let filterItem = {and: []};
            for (let j = 0; j < subcriteriasOfCurrentCriteriaGroup.length; j++) {
                let criteria = {};

                let fieldSelect = subcriteriasOfCurrentCriteriaGroup[j].querySelector('.f_select-field');
                let fieldName = fieldSelect.value;
                if (!fieldName) {
                    continue;
                }
                criteria.fieldName = fieldName;

                let conditionField = subcriteriasOfCurrentCriteriaGroup[j].querySelector('.f_select-condition input');
                if (!conditionField) {
                    conditionField = subcriteriasOfCurrentCriteriaGroup[j].querySelector('.f_select-condition');
                }

                if (!conditionField || !conditionField.hasAttribute('data-ngs-type')) {
                    continue;
                }

                criteria.conditionType = conditionField.getAttribute('data-ngs-type');

                if (criteria.conditionType === 'checkbox') {
                    criteria.searchValue = conditionField.checked ? 1 : 0;
                } else {
                    criteria.conditionValue = conditionField.value;

                    let searchValueField = subcriteriasOfCurrentCriteriaGroup[j].querySelector('.f_condition-value');

                    if (criteria.conditionType === 'select') {
                        criteria.searchValue = searchValueField.value;
                    } else {
                        criteria.searchValue = searchValueField.querySelector('input').value;
                    }
                }
                filterItem.and.push(criteria);


            }

            res.push(filterItem);


        }
        return res;
    }


    /**
     * add searched criteria item to search box
     * @param searchValue
     * @param searchValues
     * @private
     */
    _addSearchedCriteriaItemToSearchInputBox(searchValue, searchValues) {
        if (searchValue) {
            searchValue = searchValue.trim();
        }

        let filterBox = document.getElementById(this.filterBoxId);
        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');
        activeFiltersContainer.classList.add('active');

        let searchItem = activeFiltersContainer.querySelector('.f_search-item');


        if (!searchItem) {
            let newSearchCriteriaItem = null;

            if (searchValue) {
                newSearchCriteriaItem = this._createCriteriaItem(searchValue, [searchValue], 'search');
            } else {
                newSearchCriteriaItem = this._createCriteriaItem(searchValues.join(' OR '), searchValues, 'search');
            }

            activeFiltersContainer.appendChild(newSearchCriteriaItem);
            let removeBtns = newSearchCriteriaItem.querySelectorAll('.f_remove-criteria');

            removeBtns.forEach(removeBtn => {
                removeBtn.addEventListener('click', evt => {

                    removeBtn.closest('.f_search-item').remove();
                    this.handleFilterChange();
                })
            })
        } else {
            let oldSearch = searchItem.getAttribute('data-ngs-filter-value');
            oldSearch = JSON.parse(oldSearch);
            oldSearch.push(searchValue);
            searchItem.querySelector('.f_criteria-name').innerText = oldSearch.join(' OR ');
            searchItem.setAttribute('data-ngs-filter-value', JSON.stringify(oldSearch));
        }

    }


    /**
     * add criteria item to search input box
     * @param filterItems
     * @param doLoad
     * @param boxNumber
     */
    addCriteriaToExistingOnes(filterItems, doLoad, boxNumber = null) {
        let filterBox = document.getElementById(this.filterBoxId);
        let existingCriteriaItems = filterBox.querySelectorAll('[criteria-item-number]');

        if (existingCriteriaItems.length && boxNumber) {
            for (let i = 0; i < existingCriteriaItems.length; ++i) {
                if (existingCriteriaItems[i].getAttribute('criteria-item-number') === boxNumber) {
                    this.updateCriteriaItem(i, filterItems, doLoad);
                    return;
                }
            }
        }

        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');
        activeFiltersContainer.classList.add('active');

        let existingFilterItemsCount = activeFiltersContainer.querySelectorAll('.f_filter-item').length;
        let newCriteriaItem = this._createCriteriaItem('criteria - ' + (existingFilterItemsCount + 1), filterItems);

        activeFiltersContainer.appendChild(newCriteriaItem);

        let removeBtn = newCriteriaItem.querySelector('.f_remove-criteria');
        removeBtn.addEventListener('click', evt => {
            removeBtn.closest('.f_filter-item').remove();
            this.handleFilterChange();
        });

        if (doLoad) {
            this.handleFilterChange();
        }
    }


    /**
     * finds criteria item in search input box, and updates its attribute
     * @param indexOfExistingCriteriaItems
     * @param filterItems
     * @param doLoad
     */
    //todo: is it good way to find the filter item using index of querySelectorAll??

    updateCriteriaItem(indexOfExistingCriteriaItems, filterItems, doLoad) {
        let filterBox = document.getElementById(this.filterBoxId);
        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');
        activeFiltersContainer.classList.add('active');

        let currentFilterItem = activeFiltersContainer.querySelectorAll('.f_filter-item')[indexOfExistingCriteriaItems];
        if (!currentFilterItem) {
            return;
        }

        currentFilterItem.setAttribute('data-ngs-filter-value', JSON.stringify(filterItems));
        if (doLoad) {
            this.handleFilterChange();
        }
    }


    /**
     * creates a criteria box in search input box
     * @param name
     * @param filterItems
     * @param type
     * @returns {HTMLSpanElement}
     * @private
     */
    _createCriteriaItem(name, filterItems, type) {
        let newFilterItem = document.createElement('span');
        newFilterItem.classList.add(type === 'search' ? 'f_search-item' : 'f_filter-item');
        newFilterItem.classList.add('filter-item');

        let uniqueName = name.substring(name.lastIndexOf('-') + 2);

        newFilterItem.setAttribute('criteria-item-number', uniqueName);
        newFilterItem.setAttribute('data-ngs-filter-value', JSON.stringify(filterItems));

        let newFilterItemNameContainer = document.createElement('button');
        newFilterItemNameContainer.classList.add('filter-name-container', 'f_filter-name-container');
        newFilterItemNameContainer.innerHTML = `<span class="f_criteria-name">${name}</span>`;

        let criteriaRemoveBtn = document.createElement('button');
        criteriaRemoveBtn.classList.add('close-btn', 'f_remove-criteria');

        newFilterItem.appendChild(newFilterItemNameContainer);
        newFilterItem.appendChild(criteriaRemoveBtn);

        newFilterItemNameContainer.addEventListener('click', (evt) => {

            if (!evt.target.closest('.f_filter-item')) {
                //was clicked to item which is searched, but not filtered
                return;
            }


            this.toggleFilterControlPanelButtonClass('add', false);

            let filterItemNameElement = newFilterItemNameContainer.querySelector('.f_criteria-name');
            let oldName = filterItemNameElement.innerText;
            filterItemNameElement.innerText = 'Loading...';

            this.showSelectedCriterias(evt, uniqueName).then(() => {
                filterItemNameElement.innerText = oldName;

                this._toggleAddGroupOfAndAssocCriteriaBtn();
                this._toggleAddSubCriteriaRowBtn();
            });


        });

        return newFilterItem;

    }


    /**
     *
     * @param container
     */
    initShowDetailedInfosOfCriterias(container) {
        if (!container) {
            return;
        }
        let openDetailsBtn = container.querySelector('.f_open-details');
        if (!openDetailsBtn) {
            return;
        }

        openDetailsBtn.addEventListener('click', () => {
            let filterBox = openDetailsBtn.closest(".center-box");
            if (filterBox && filterBox.classList.contains('is_detailed')) {
                let details = container.querySelectorAll('.f_criteria-info');
                for (let i = 0; i < details.length; i++) {
                    details[i].remove();
                }
                filterBox.classList.remove('is_detailed');
                return;
            }
            filterBox.classList.add('is_detailed');
            let criterias = container.querySelectorAll('.f_filter-item');
            this.addCriteriaInfos(criterias);
        });
    }

    /**
     *
     * @param criterias
     */
    addCriteriaInfos(criterias) {
        let criteriaContainer = document.getElementById(this.filterBoxId).querySelector('.center-box');
        if (!criteriaContainer.classList.contains('is_detailed')) {
            return;
        }
        for (let i = 0; i < criterias.length; i++) {
            let infos = this.showDetailedInfosOfCriteria(criterias[i]);
            let existingInfo = criterias[i].querySelector('.f_criteria-info');
            if (existingInfo) {
                existingInfo.remove();
            }
            if (infos) {
                criterias[i].append(infos);
            }
        }
    }


    /**
     *
     * @param criteria
     * @returns {HTMLUListElement}
     */
    showDetailedInfosOfCriteria(criteria) {
        let conditionsInfos = document.createElement('ul');
        conditionsInfos.classList.add('f_criteria-info');
        conditionsInfos.classList.add('criteria-info');
        let filterItems = JSON.parse(criteria.getAttribute('data-ngs-filter-value'));
        if (!filterItems.or || !filterItems.or.length) {
            return null;
        }

        filterItems = filterItems.or;

        for (let i = 0; i < filterItems.length; i++) {
            if (filterItems[i].and) {
                this._showInfoOfCriteria(conditionsInfos, filterItems[i].and, 'and');
            } else {
                this._showInfoOfCriteria(conditionsInfos, [filterItems[i]]);
            }
        }

        return conditionsInfos;
    }


    _showInfoOfCriteria(conditionsInfos, criteriaItems, delim) {
        let conditionInfo = document.createElement('li');

        let subConditionsContainer = document.createElement('ul');
        for (let i = 0; i < criteriaItems.length; i++) {
            let conditionPart = document.createElement('li');

            let item = criteriaItems[i];
            let fieldName = item.fieldName
            let condition = item.conditionValue;
            let searchValue = item.searchValue;
            let filterData = this._getPossibleValueInfoByFieldId(fieldName);
            if (!filterData) {
                continue;
            }

            let type = filterData.type;

            if (type === 'checkbox') {
                conditionPart.innerHTML = '<span class="name">' + filterData.value + ':</span> is <span class="value">' + (searchValue ? "true" : "false") + '</span>';
            } else if (type === 'number' || type === 'date') {
                conditionPart.innerHTML = '<span class="name">' + filterData.value + ':</span> <span class="value">' + this._getConditionText(condition) + ' ' + searchValue + '</span>';
            } else if (type === 'select') {
                let possibleValues = filterData.possible_values;
                let possibleValue = "";
                for (let j = 0; j < possibleValues.length; j++) {
                    if (possibleValues[j].id == searchValue) {
                        possibleValue = possibleValues[j].value;
                        break;
                    }
                }
                conditionPart.innerHTML = '<span class="name">' + filterData.value + ':</span> ' + this._getConditionText(condition) + ' <span class="value">' + possibleValue + '</span>';
            } else {
                conditionPart.innerHTML = '<span class="name">' + filterData.value + ':</span> <span class="value">' + this._getConditionText(condition) + ' ' + searchValue + '</span>';
            }

            subConditionsContainer.appendChild(conditionPart);
        }

        conditionInfo.appendChild(subConditionsContainer);
        conditionsInfos.appendChild(conditionInfo);
    }


    /**
     * returns number condition text
     *
     * @param condition
     * @returns {string}
     * @private
     */
    _getConditionText(condition) {
        if (condition === 'greater') {
            return "is greater then";
        } else if (condition === 'greater_or_equal') {
            return "is greater or equal then";
        } else if (condition === 'less') {
            return "is less then";
        } else if (condition === 'less_or_equal') {
            return "is less or equal then";
        } else if (condition === 'not_equal') {
            return "si not equal to";
        } else if (condition === 'equal') {
            return "is equal to";
        } else if (condition === 'like') {
            return "contains";
        } else if (condition === 'not_like') {
            return "not contains";
        }
        return "";
    }


    /**
     * open filter control panel with existing datas, ie with filled selectboxes and inputs
     * @param evt
     * @param uniqueName
     * @returns {Promise<unknown>}
     */
    showSelectedCriterias(evt, uniqueName) {
        return new Promise((resolve, reject) => {

            let alreadyOpenedCriteriaBox = document.getElementById(this.filterBoxId).querySelector('.f_filter-main-control-panel-box');
            if (alreadyOpenedCriteriaBox) {
                alreadyOpenedCriteriaBox.remove();
            }


            setTimeout(() => {
                let filterItemsData = JSON.parse(evt.target.closest('.f_filter-item').getAttribute('data-ngs-filter-value'));

                let filterBox = document.getElementById(this.filterBoxId);
                let filterMainControlPanelBox = this._createFilterMainControlPanelBox();
                filterMainControlPanelBox.setAttribute('criteria-box-number', uniqueName);

                filterBox.appendChild(filterMainControlPanelBox);


                if (!filterItemsData.or) {
                    resolve(true);
                    return;
                }

                let containerOfAllCriteriaRows = filterMainControlPanelBox.querySelector('.f_all-criteria-rows-container');


                filterItemsData.or.forEach((filterCriteria, index) => {

                    containerOfAllCriteriaRows.appendChild(this._createContainerForAndAssocCriteriasGroup(filterCriteria));
                    if (index < filterItemsData.or.length - 1) {
                        containerOfAllCriteriaRows.appendChild(this._createOrDelimiter(true));
                    }
                });

                this._initAddNewCriteriaRowBtn();

                this._initApplyCriteriaItem();
                this._initCancelCriteriaCreation();

                let allSelects = document.querySelectorAll('#' + this.filterBoxId + ' .f_filter-main-control-panel-box .ngs-choice');
                allSelects.forEach(this._initSelect);

                this.filterMainControlPanelCloseHandler = (evt) => {

                    if (evt.target.closest('.f_filter-main-control-panel-box')) {
                        return;
                    }
                    if (!evt.target.closest('.f_filter-item')) {
                        filterMainControlPanelBox.remove();
                    }

                    this.toggleFilterControlPanelButtonClass();
                    document.removeEventListener('click', this.filterMainControlPanelCloseHandler);
                    this.filterMainControlPanelCloseHandler = null;
                };

                document.addEventListener('click', this.filterMainControlPanelCloseHandler);
                resolve(true);

            }, 50);
        });
    }


    /**
     * cancel filtering, and close the control panel
     * @private
     */
    _initCancelCriteriaCreation() {
        let filterBox = document.getElementById(this.filterBoxId);
        let filterMainControlPanel = filterBox.querySelector('.f_filter-main-control-panel-box');

        let cancelBtn = filterMainControlPanel.querySelector('.f_cancel-filters-btn');
        cancelBtn.addEventListener('click', evt => {

            this.toggleFilterControlPanelButtonClass();

            if (this.filterMainControlPanelCloseHandler) {
                document.removeEventListener('click', this.filterMainControlPanelCloseHandler);
                this.filterMainControlPanelCloseHandler = null;
            }
            filterMainControlPanel.remove();
        })

    }


    /**
     * called when:
     * 1) on the filter main control panel open-close button click,
     * 2) on add (add group of criterias) button click
     * 3) on filter item click (to show selected filters). Only in this case @param filterItems exists, and it is data of clicked filter item
     *
     * Creates a container, where criterias (i.e. criterias of and association) should be added
     * @param filterItems
     * @returns {HTMLDivElement}
     * @private
     */
    _createContainerForAndAssocCriteriasGroup(filterItems) {

        let containerOfAndAssocCriteriasAndAndDelimiter = document.createElement('div');
        containerOfAndAssocCriteriasAndAndDelimiter.classList.add('f_container-of-and-assoc-criterias-and-and-delimiter', 'container-of-item-and-and-delimiter');
        containerOfAndAssocCriteriasAndAndDelimiter.appendChild(this.createAndDelimiter());

        let containerOfAndAssocRows = document.createElement('div');
        containerOfAndAssocRows.classList.add('criteria-item', 'f_and-assoc-rows-container');
        containerOfAndAssocRows.setAttribute('data-index-of-criteria', (this._getCountOfAndAssocCriteriasGroupsAtMoment() + 1 + ''));
        containerOfAndAssocRows.appendChild(this.createDeleteBtnForAndAssocRowsGroup());

        if (filterItems) {
            filterItems.and.forEach(filterItem => {
                containerOfAndAssocRows.appendChild(this.createJustOneRowWithFilledData(filterItem));
            });
            this._toggleRemoveIconOfEachAndAssocRow(containerOfAndAssocRows);

        } else {
            containerOfAndAssocRows.appendChild(this._createAnEmptyCriteriaRow(containerOfAndAssocRows));
        }

        containerOfAndAssocRows.appendChild(this._createAddJustOneCriteriaRowBtn());
        containerOfAndAssocCriteriasAndAndDelimiter.appendChild(containerOfAndAssocRows);

        this._initAddJustOneCriteriaRowBtn(containerOfAndAssocRows);
        this._initRemoveAndAssocRowsGroup(containerOfAndAssocRows);


        if (filterItems) {
            return containerOfAndAssocCriteriasAndAndDelimiter;
        } else {

            let allCriteriaRowsContainer = document.querySelector('#' + this.filterBoxId + ' .f_all-criteria-rows-container');
            allCriteriaRowsContainer.appendChild(containerOfAndAssocCriteriasAndAndDelimiter);
        }
    }

    /**
     * creates a remove button near each group of and associated rows, which should delete the hole group
     * @returns {HTMLSpanElement}
     */
    createDeleteBtnForAndAssocRowsGroup() {
        let deleteAndAssocRowsGroupBtnContainer = document.createElement("div");
        deleteAndAssocRowsGroupBtnContainer.classList.add('remove-criteria-container');

        let deleteAndAssocRowsGroupBtn = document.createElement("span");
        deleteAndAssocRowsGroupBtn.classList.add('f_remove-and-assoc-rows-group', 'remove-criteria');
        deleteAndAssocRowsGroupBtn.innerHTML = '<i class="icon-close1"></i>';

        deleteAndAssocRowsGroupBtnContainer.appendChild(deleteAndAssocRowsGroupBtn);

        return deleteAndAssocRowsGroupBtnContainer;
    }


    /**
     * creates row of criteria with filled values
     * @param filterItem
     * @returns {HTMLDivElement}
     */
    createJustOneRowWithFilledData(filterItem) {
        let justOneCriteriaRow = document.createElement('div');
        justOneCriteriaRow.classList.add('sub-criteria-item', 'f_sub-criteria-item');

        let firstColumnSelectBox = document.createElement("select");
        firstColumnSelectBox.classList.add('select-field', 'f_select-field', 'ngs-choice', 'f_criteria-item-to-validate', 'f_first-column-select-box');
        firstColumnSelectBox.setAttribute('data-ngs-searchable', (this.possibleFilters.length > 5) ? 'true' : 'false');

        this.possibleFilters.forEach(possibleFilter => {
            firstColumnSelectBox.appendChild(this._createOptionTag(possibleFilter['id'], possibleFilter['value'], filterItem.fieldName));
        });

        justOneCriteriaRow.appendChild(firstColumnSelectBox);

        if (filterItem.conditionType === 'select') {
            justOneCriteriaRow.appendChild(this._createSelectCondition(filterItem.conditionValue));
            let possibleValues = this._getPossibleValuesByFieldId(filterItem.fieldName);
            justOneCriteriaRow.appendChild(this._createSelectValue(possibleValues, filterItem.searchValue));
        } else if (filterItem.conditionType === 'number') {
            justOneCriteriaRow.appendChild(this._createNumberCondition(filterItem.conditionValue));
            justOneCriteriaRow.appendChild(this._createNumberValue(filterItem.searchValue));
        } else if (filterItem.conditionType === 'checkbox') {
            justOneCriteriaRow.appendChild(this._createCheckboxCondition(filterItem.searchValue));
        } else if (filterItem.conditionType === 'date') {
            justOneCriteriaRow.appendChild(this._createDateCondition(filterItem.conditionValue));
            justOneCriteriaRow.appendChild(this._createDateValue(filterItem.searchValue));
        } else {
            let isTinyMceField = filterItem.conditionType === 'long_text';
            justOneCriteriaRow.appendChild(this._createTextCondition(isTinyMceField, filterItem.conditionValue));
            justOneCriteriaRow.appendChild(this._createTextValue(filterItem.searchValue));
        }

        this._initFirstColumnsSelection(firstColumnSelectBox, justOneCriteriaRow);

        return justOneCriteriaRow;
    }


    /**
     * remove or add delete icon near each row
     * @param andAssocRowsGroup
     * @private
     */
    _toggleRemoveIconOfEachAndAssocRow(andAssocRowsGroup) {
        let rows = andAssocRowsGroup.querySelectorAll('.f_sub-criteria-item');
        if(rows.length === 1) {
            rows[0].querySelector('.f_remove-just-one-criteria-row')?.remove();
            return;
        }

        rows.forEach(row => {
            if(!row.querySelector('.f_remove-just-one-criteria-row')) {
                row.appendChild(this._createDeleteBtnForJustOneCriteriaRow());
                this.initSubCriteriaRemoveBtn(row);
            }
        })
    }



    /**
     * creates a row with first select only
     * this is the smallest level of containers, it contains just 3 (or 2 if checkbox) columns
     * @param groupOfRows
     * @returns {HTMLDivElement}
     * @private
     */
    _createAnEmptyCriteriaRow(groupOfRows) {
        let justOneCriteriaRow = document.createElement('div');
        justOneCriteriaRow.classList.add('sub-criteria-item', 'f_sub-criteria-item');

        let firstColumnSelectBox = document.createElement("select");
        firstColumnSelectBox.classList.add('select-field', 'f_select-field', 'ngs-choice', 'f_criteria-item-to-validate', 'f_first-column-select-box');
        firstColumnSelectBox.setAttribute('data-ngs-searchable', (this.possibleFilters.length > 5) ? 'true' : 'false');

        let option = document.createElement('option');

        option.value = '';
        option.text = 'please select field';
        option.selected = true;
        option.disabled = true;

        firstColumnSelectBox.appendChild(option);

        let possibleFilters = this._getPossibleFieldSelectValuesAtMoment(groupOfRows);
        possibleFilters.forEach(possibleFilter => {
            firstColumnSelectBox.appendChild(this._createOptionTag(possibleFilter['id'], possibleFilter['value'], null));
        });

        justOneCriteriaRow.appendChild(firstColumnSelectBox);

        this._initFirstColumnsSelection(firstColumnSelectBox, justOneCriteriaRow);
        this._initSelect(firstColumnSelectBox);

        setTimeout(() => {
            this._toggleRemoveIconOfEachAndAssocRow(groupOfRows);
            this._resetAllFieldSelectValuesOfCurrentGroup(groupOfRows);
        });

        return justOneCriteriaRow;
    }


    /**
     *
     * @param groupOfRows
     * @param currentFieldSelect
     * @returns {*}
     * @private
     */
    _getPossibleFieldSelectValuesAtMoment(groupOfRows, currentFieldSelect = null) {
        let possibleFilters = this.possibleFilters;

        let existingFieldNamesInThisGroup = this.getExistingFieldSelectValuesOfGroup(groupOfRows, currentFieldSelect);
        if (existingFieldNamesInThisGroup.length) {
            possibleFilters = this.possibleFilters.filter(item => !existingFieldNamesInThisGroup.includes(item.id));
        }

        return possibleFilters;
    }


    /**
     * get already set values of fieldSelects (first column) of given group at the moment
     * @param groupOfRows
     * @param currentFieldSelect
     * @returns {[]}
     */
    getExistingFieldSelectValuesOfGroup(groupOfRows, currentFieldSelect) {
        let res = [];
        let allFirstColumnSelectsOfCurrentGroup = groupOfRows.querySelectorAll('.f_first-column-select-box');

        allFirstColumnSelectsOfCurrentGroup.forEach(firstColumn => {
            if (currentFieldSelect) {
                if (!currentFieldSelect.isSameNode(firstColumn)) {
                    res.push(firstColumn.value);
                }
            } else {
                res.push(firstColumn.value);
            }
        });

        return res;
    }


    /**
     * creates a remove button near each subcriteria row, which should delete just one row of criteria
     * @returns {HTMLSpanElement}
     */
    _createDeleteBtnForJustOneCriteriaRow() {
        let deleteJustOneCriteriaRowBtnContainer = document.createElement("div");
        deleteJustOneCriteriaRowBtnContainer.classList.add('remove-criteria-container');

        let deleteJustOneCriteriaRowBtn = document.createElement("span");
        deleteJustOneCriteriaRowBtn.classList.add('f_remove-just-one-criteria-row', 'remove-criteria');
        deleteJustOneCriteriaRowBtn.innerHTML = '<i class="icon-close1"></i>';

        deleteJustOneCriteriaRowBtnContainer.appendChild(deleteJustOneCriteriaRowBtn);

        return deleteJustOneCriteriaRowBtnContainer;
    }

    /**
     * remove a line of subcriteria ie just one row, not the group of and associated criterias
     * @param singleCriteriaItem
     */
    initSubCriteriaRemoveBtn(singleCriteriaItem) {
        let removeBtn = singleCriteriaItem.querySelector('.f_remove-just-one-criteria-row');
        if (!removeBtn) {
            return;
        }

        removeBtn.addEventListener('click', evt => {
            evt.stopPropagation();
            let containerOfAndAssocRows = singleCriteriaItem.closest('.f_and-assoc-rows-container');

            if (this._getCountOfCriteriaRowsInItsContainerAtMoment(containerOfAndAssocRows) === 1) {
                return;
            }

            singleCriteriaItem.remove();

            this._toggleAddSubCriteriaRowBtn(containerOfAndAssocRows);
            this._resetAllFieldSelectValuesOfCurrentGroup(containerOfAndAssocRows);
            this._toggleRemoveIconOfEachAndAssocRow(containerOfAndAssocRows);
        })
    }


    /**
     * creates And delimiter left part (not the button)
     * @returns {HTMLDivElement}
     */
    createAndDelimiter() {
        let container = document.createElement('div');
        container.classList.add('f_and-delimiter-box', 'and-delimiter-box');
        container.innerText = "And";
        return container;
    }

    /**
     * creates button which should be appended to  group of "and associated" rows
     * @returns {HTMLDivElement}
     * @private
     */
    _createAddJustOneCriteriaRowBtn() {
        let buttonContainer = document.createElement('div');
        buttonContainer.classList.add('sub-criteria-item', 'f_add-sub-category-btn-container');
        buttonContainer.innerHTML = `<button class="add-criteria button dark f_btn-add-sub-criteria-item"><span class="circle"><i class="icon-svg179"></i></span></button>`;
        return buttonContainer;
    }


    /**
     * set correct indexes for removing or delimiters functionality
     * @private
     */
    _updateIndexesOfAndAssocCriteriaGroups() {
        let allCriteriaRowsContainer = document.querySelector('#' + this.filterBoxId + ' .f_all-criteria-rows-container');
        let allAndAssocRowsContainers = allCriteriaRowsContainer.querySelectorAll('.f_and-assoc-rows-container');
        let allOrDelimiters = allCriteriaRowsContainer.querySelectorAll('.f_or-delimiter');

        allAndAssocRowsContainers.forEach((andAssocRowsContainer, index) => {
            andAssocRowsContainer.setAttribute('data-index-of-criteria', index + 1 + '');
        });

        allOrDelimiters.forEach((delimiter, index) => {
            delimiter.setAttribute('data-index-of-delimiter', index + 1 + '');
        });
    }


    _getCountOfAndAssocCriteriasGroupsAtMoment() {
        let allCriteriaRowsContainer = document.querySelector('#' + this.filterBoxId + ' .f_all-criteria-rows-container');
        return allCriteriaRowsContainer.querySelectorAll('.f_and-assoc-rows-container').length;
    }

    _getCountOfCriteriaRowsInItsContainerAtMoment(container) {
        let allCriteriaRowsInContainer = container.querySelectorAll('.f_sub-criteria-item');
        return allCriteriaRowsInContainer.length;
    }

    /**
     * adds condition fields to criteria
     *
     * @param criteriaBox
     * @param conditionFields
     * @private
     */
    _addConditionFieldsToCriteria(criteriaBox, conditionFields) {
        if (conditionFields.length) {
            for (let conditionIndex = 0; conditionIndex < conditionFields.length; conditionIndex++) {
                if (criteriaBox.querySelector('.f_sub-criteria-item')) {
                    criteriaBox.querySelector('.f_sub-criteria-item').appendChild(conditionFields[conditionIndex]);
                } else {
                    criteriaBox.appendChild(conditionFields[conditionIndex]);
                }
            }
        }
    }


    /**
     * remove the group of and associated criteria rows, ie the many rows at one click
     * @param andAssocRowsContainer
     * @private
     */
    _initRemoveAndAssocRowsGroup(andAssocRowsContainer) {
        let removeAndAssocRowsContainerBtn = andAssocRowsContainer.querySelector('.f_remove-and-assoc-rows-group');

        if (!removeAndAssocRowsContainerBtn) {
            return;
        }

        removeAndAssocRowsContainerBtn.addEventListener('click', (evt) => {
            evt.stopPropagation();
            if (this._getCountOfAndAssocCriteriasGroupsAtMoment() === 1) {
                return;
            }

            let allCriteriaRowsContainer = document.querySelector('#' + this.filterBoxId + ' .f_all-criteria-rows-container');
            let indexOfCriteria = andAssocRowsContainer.getAttribute('data-index-of-criteria');
            let orDelimiterOfCurrentCriteria = allCriteriaRowsContainer.querySelector('.f_or-delimiter[data-index-of-delimiter="' + (indexOfCriteria - 1) + '"]');

            if (orDelimiterOfCurrentCriteria) {
                orDelimiterOfCurrentCriteria.remove();
            } else {
                let orDelimiterOfCurrentCriteria = allCriteriaRowsContainer.querySelector('.f_or-delimiter[data-index-of-delimiter="' + indexOfCriteria + '"]');
                orDelimiterOfCurrentCriteria?.remove();
            }

            andAssocRowsContainer.closest('.f_container-of-and-assoc-criterias-and-and-delimiter').remove();
            this._updateIndexesOfAndAssocCriteriaGroups();
            this._toggleAddGroupOfAndAssocCriteriaBtn();
        });
    }


    /**
     * toggles visibility of button which adds a new group of and associated criteria rows
     * @private
     */
    _toggleAddGroupOfAndAssocCriteriaBtn() {
        let filterMainControlPanel = document.querySelector('.f_filter-main-control-panel-box');
        if (!filterMainControlPanel) {
            return;
        }

        let buttonBox = filterMainControlPanel.querySelector('.f_add-new-group-of-and-assoc-criterias-btn-container');

        if (this._getCountOfAndAssocCriteriasGroupsAtMoment() < 10) {
            buttonBox.classList.remove('is_hidden');
        } else {
            buttonBox.classList.add('is_hidden');
        }
    }

    /**
     * toggles visibility of button which adds a new row of subcriteria
     * @param groupOfAndAssocCriterias
     * @private
     */
    _toggleAddSubCriteriaRowBtn(groupOfAndAssocCriterias) {
        if (!groupOfAndAssocCriterias) {
            this.initTogglingAllAddSubCriteriaRowBtns();
            return;
        }

        let buttonBox = groupOfAndAssocCriterias.querySelector('.f_add-sub-category-btn-container');
        if (!buttonBox) {
            return;
        }
        if (this._getCountOfCriteriaRowsInItsContainerAtMoment(groupOfAndAssocCriterias) < 5) {
            buttonBox.classList.remove('is_hidden');
        } else {
            buttonBox.classList.add('is_hidden');
        }
    }


    /**
     * if opens a already filled filter control panel, all addSubCriteria buttons should be or shown or hided
     */
    initTogglingAllAddSubCriteriaRowBtns() {
        let filterMainControlPanel = document.querySelector('.f_filter-main-control-panel-box');
        if (!filterMainControlPanel) {
            return;
        }

        let allGroupsOfAnsAssocCriteriaRows = filterMainControlPanel.querySelectorAll('.f_and-assoc-rows-container');
        allGroupsOfAnsAssocCriteriaRows.forEach(this._toggleAddSubCriteriaRowBtn.bind(this));
    }


    /**
     * initialization of add subcriteria button (which adds a row to and-assoc-rows-container)
     * @param containerOfAndAssocRows
     * @private
     */
    _initAddJustOneCriteriaRowBtn(containerOfAndAssocRows) {
        let addJustOneCriteriaRowBtn = containerOfAndAssocRows.querySelector('.f_btn-add-sub-criteria-item');
        let containerOfAddBtn = addJustOneCriteriaRowBtn.closest('.f_add-sub-category-btn-container');

        if (!addJustOneCriteriaRowBtn || !containerOfAddBtn) {
            return;
        }

        addJustOneCriteriaRowBtn.addEventListener('click', evt => {
            this._initEventListenersForTogglingInvalidClass(containerOfAndAssocRows);

            let invalidFields = this.getInvalidFields(containerOfAndAssocRows);
            if (!invalidFields.length) {
                let emptyCriteriaRow = this._createAnEmptyCriteriaRow(containerOfAndAssocRows);
                containerOfAndAssocRows.insertBefore(emptyCriteriaRow, containerOfAddBtn);

                this._toggleAddSubCriteriaRowBtn(containerOfAndAssocRows);

            } else {
                invalidFields.forEach((invalidField) => {
                    invalidField.classList.add('invalid-criteria-item', 'f_invalid-criteria-item');
                })
            }
        })
    }


    /**
     * first column (which is fieldName) initialization
     * @param firstColumnSelectBox
     * @param aRowOfCriteriaItem
     * @private
     */
    _initFirstColumnsSelection(firstColumnSelectBox, aRowOfCriteriaItem) {
        if (firstColumnSelectBox.hasAttribute('data-change-listener-is-set')) {
            return;
        }

        firstColumnSelectBox.setAttribute('data-change-listener-is-set', true);

        firstColumnSelectBox.addEventListener('change', evt => {

            //remove 2nd column if exists, because need to change it
            let conditionFieldSelectBoxOfOneRowCriteria = aRowOfCriteriaItem.querySelector('.f_select-condition');
            if (conditionFieldSelectBoxOfOneRowCriteria) {
                this._removeElement(conditionFieldSelectBoxOfOneRowCriteria);
            }
            //remove 3rd column if exists because need to change it
            let valueFieldSelectBoxOfOneRowCriteria = aRowOfCriteriaItem.querySelector('.f_condition-value');
            if (valueFieldSelectBoxOfOneRowCriteria) {
                this._removeElement(valueFieldSelectBoxOfOneRowCriteria);
            }

            let fieldName = firstColumnSelectBox.value;
            let conditionFields = this._createConditionSelectionByFieldId(fieldName);

            conditionFields.forEach(conditionField => {
                let deleteIcon = aRowOfCriteriaItem.querySelector('.f_remove-just-one-criteria-row');
                aRowOfCriteriaItem.insertBefore(conditionField, deleteIcon);
            });

            this._initAllSelectBoxesOfGivenContainer(aRowOfCriteriaItem);
            this._resetAllFieldSelectValuesOfCurrentGroup(aRowOfCriteriaItem.closest('.f_and-assoc-rows-container'));
        })
    }


    /**
     * reset all fieldSelects of current group, because possible values should be changed in each of them
     * @param containerOfAndAssocRows
     * @private
     */
    _resetAllFieldSelectValuesOfCurrentGroup(containerOfAndAssocRows) {
        let allFieldSelectsOfCurrentGroup = containerOfAndAssocRows.querySelectorAll('.f_first-column-select-box');

        allFieldSelectsOfCurrentGroup.forEach(fieldSelect => {

            let selectedValue = fieldSelect.value;
            let possibleValuesForCurrentSelect = this._getPossibleFieldSelectValuesAtMoment(containerOfAndAssocRows, fieldSelect);

            let newSelectElement = document.createElement('select');
            newSelectElement.classList.add('select-field', 'f_select-field', 'ngs-choice', 'f_criteria-item-to-validate', 'f_first-column-select-box');

            let option = document.createElement('option');
            option.value = '';
            option.text = 'please select field';
            option.selected = true;
            option.disabled = true;
            newSelectElement.appendChild(option);
            newSelectElement.setAttribute('data-ngs-searchable', (possibleValuesForCurrentSelect.length > 5) ? 'true' : 'false');

            possibleValuesForCurrentSelect.forEach(value => {
                newSelectElement.appendChild(this._createOptionTag(value['id'], value['value'], selectedValue))
            });

            let choicesElem = fieldSelect.closest('.choices');
            choicesElem.parentNode.replaceChild(newSelectElement, choicesElem);
            this._initSelect(newSelectElement);
            this._initFirstColumnsSelection(newSelectElement, newSelectElement.closest('.f_sub-criteria-item'))
        })
    }

    /**
     *
     * @param fieldId
     * @returns {[]}
     * @private
     */
    _createConditionSelectionByFieldId(fieldId) {
        let elementsToAdd = [];
        let type = this._getTypeByFieldId(fieldId);

        if (type === 'checkbox') {
            elementsToAdd.push(this._createCheckboxCondition());
        } else if (type === 'number') {
            elementsToAdd.push(this._createNumberCondition());
            elementsToAdd.push(this._createNumberValue());
        } else if (type === 'date') {
            elementsToAdd.push(this._createDateCondition());
            elementsToAdd.push(this._createDateValue());
        } else if (type === 'select') {
            let possibleValues = this._getPossibleValuesByFieldId(fieldId);
            elementsToAdd.push(this._createSelectCondition());
            elementsToAdd.push(this._createSelectValue(possibleValues));
        } else {
            let isTinyMceField = type === 'long_text';
            elementsToAdd.push(this._createTextCondition(isTinyMceField));
            elementsToAdd.push(this._createTextValue());
        }
        return elementsToAdd;
    }


    /**
     * returns field type by field id
     *
     * @param fieldId
     * @returns {null|*}
     * @private
     */
    _getTypeByFieldId(fieldId) {
        let possibleValue = this._getPossibleValueInfoByFieldId(fieldId);
        if (!possibleValue) {
            return null;
        }

        return possibleValue.type;
    }

    /**
     * returns field type by field id
     *
     * @param fieldId
     * @returns {null|*}
     * @private
     */
    _getPossibleValuesByFieldId(fieldId) {

        let possibleValue = this._getPossibleValueInfoByFieldId(fieldId);
        if (!possibleValue) {
            return null;
        }

        return possibleValue.possible_values;
    }

    /**
     *
     * @param fieldId
     * @returns {null|*}
     * @private
     */
    _getPossibleValueInfoByFieldId(fieldId) {
        for (let i = 0; i < this.possibleFilters.length; i++) {
            if (this.possibleFilters[i]['id'] === fieldId) {
                return this.possibleFilters[i];
            }
            let possibleValueParts = this.possibleFilters[i]['id'].split(".");

            if (possibleValueParts.length > 1 && possibleValueParts[1] === fieldId) {
                return this.possibleFilters[i];
            }
        }

        return null;
    }


    /**
     * creates second column, when the type of first column is checkbox
     * @returns {HTMLDivElement}
     * @private
     */
    _createCheckboxCondition(fieldValue = null) {
        let inputBoxContainer = document.createElement('div');
        inputBoxContainer.classList.add('condition-value', 'f_select-condition');
        let inputBox = document.createElement("input");
        inputBox.setAttribute('type', 'checkbox');
        inputBox.setAttribute('data-ngs-type', 'checkbox');
        //todo: this is because of in price contract editLoad.js when init FilterManager, this value is number, in other places is string
        if (fieldValue && +fieldValue === +'1') {
            inputBox.setAttribute('checked', 'true');
        }

        inputBoxContainer.appendChild(inputBox);

        return inputBoxContainer;
    }


    /**
     * creates second column, when the type of first column is text
     * @param isTinyMce
     * @param fieldType
     * @returns {HTMLSelectElement}
     * @private
     */
    _createTextCondition(isTinyMce, fieldType = null) {

        let conditionBox = document.createElement("select");
        conditionBox.setAttribute('data-ngs-type', 'text');
        conditionBox.classList.add('f_select-condition', 'select-condition', 'ngs-choice', 'f_criteria-item-to-validate');

        let possibleConditionSelects = [['like', 'Like'], ['not_like', 'Not like']];

        if (!isTinyMce) {
            possibleConditionSelects = possibleConditionSelects.concat([['equal', 'Equal'], ['not_equal', 'Not equal']]);
        }

        possibleConditionSelects.forEach(possibleCondition => {
            conditionBox.appendChild(this._createOptionTag(possibleCondition[0], possibleCondition[1], fieldType));
        });

        return conditionBox;
    }


    /**
     * creates third column, when the type of first column is text
     * @returns {HTMLDivElement}
     * @private
     */
    _createTextValue(fieldValue = null) {
        let inputBoxContainer = document.createElement('div');
        inputBoxContainer.classList.add('condition-value', 'f_condition-value', 'f_criteria-item-to-validate');
        let inputBox = document.createElement("input");
        inputBox.setAttribute('type', 'text');
        if (fieldValue) {
            inputBox.setAttribute('value', fieldValue);
        }

        inputBoxContainer.appendChild(inputBox);

        return inputBoxContainer;
    }


    /**
     * creates second column, when the type of first column is number
     * @returns {HTMLSelectElement}
     * @private
     */
    _createNumberCondition(fieldType = null) {
        let conditionBox = document.createElement("select");
        conditionBox.setAttribute('data-ngs-type', 'number');
        conditionBox.classList.add('f_select-condition', 'select-condition', 'ngs-choice', 'f_criteria-item-to-validate');

        let possibleConditionSelects = [['equal', 'Equal'], ['not_equal', 'Not Equal'], ['greater', 'Greater'], ['greater_or_equal', 'Greater or equal'], ['less', 'Less'], ['less_or_equal', 'Less or Equal']];

        possibleConditionSelects.forEach(possibleCondition => {
            conditionBox.appendChild(this._createOptionTag(possibleCondition[0], possibleCondition[1], fieldType));
        });

        return conditionBox;
    }


    /**
     * creates third column, when the type of first column is number
     * @returns {HTMLDivElement}
     * @private
     */
    _createNumberValue(fieldValue = null) {
        let inputBoxcontainer = document.createElement('div');
        inputBoxcontainer.classList.add('condition-value', 'f_condition-value', 'f_criteria-item-to-validate');
        let inputBox = document.createElement("input");
        inputBox.setAttribute('type', 'number');
        if (fieldValue) {
            inputBox.setAttribute('value', fieldValue);
        }

        inputBoxcontainer.appendChild(inputBox);

        return inputBoxcontainer;
    }


    /**
     * creates second column, when the type of first column is date
     * @returns {HTMLSelectElement}
     * @private
     */
    _createDateCondition(fieldType = null) {
        let conditionBox = document.createElement("select");
        conditionBox.setAttribute('data-ngs-type', 'date');
        conditionBox.classList.add('f_select-condition', 'select-condition', 'ngs-choice', 'f_criteria-item-to-validate');

        let possibleConditionSelects = [['equal', 'Equal'], ['not_equal', 'Not Equal'], ['greater', 'Greater'], ['greater_or_equal', 'Greater or equal'], ['less', 'Less'], ['less_or_equal', 'Less or Equal']];

        possibleConditionSelects.forEach(possibleCondition => {
            conditionBox.appendChild(this._createOptionTag(possibleCondition[0], possibleCondition[1], fieldType));
        });

        return conditionBox;
    }


    /**
     * creates third column, when the type of first column is date
     * @returns {HTMLDivElement}
     * @private
     */
    _createDateValue(fieldValue = null) {
        let inputBoxContainer = document.createElement('div');
        inputBoxContainer.classList.add('condition-value', 'f_condition-value', 'f_criteria-item-to-validate');
        let inputBox = document.createElement("input");
        inputBox.setAttribute('type', 'date');
        if (fieldValue) {
            inputBox.setAttribute('value', fieldValue);
        }

        inputBoxContainer.appendChild(inputBox);

        return inputBoxContainer;
    }


    /**
     * creates second column, when the type of first column is select
     * @param conditionType
     * @returns {HTMLSelectElement}
     * @private
     */
    _createSelectCondition(conditionType) {
        let selectCondition = document.createElement("select");
        selectCondition.classList.add('f_select-condition-type', 'f_select-condition', 'select-field', 'ngs-choice', 'condition-value');
        selectCondition.setAttribute('data-ngs-type', 'select');

        selectCondition.appendChild(this._createOptionTag('equal', 'Equal', conditionType));
        selectCondition.appendChild(this._createOptionTag('not_equal', 'Not equal', conditionType));

        return selectCondition;
    }


    /**
     * creates third column, when the type of first column is select
     * @param possibleValues
     * @param value
     * @returns {HTMLSelectElement}
     * @private
     */
    _createSelectValue(possibleValues, value = null) {
        let valueBox = document.createElement("select");
        valueBox.setAttribute('data-ngs-type', 'select');
        valueBox.classList.add('f_criteria-item-to-validate', 'f_condition-value', 'select-condition', 'ngs-choice');

        if (!possibleValues) {
            possibleValues = [];
        }

        valueBox.setAttribute('data-ngs-searchable', (possibleValues.length > 5) ? 'true' : 'false');

        possibleValues.forEach(possibleValue => {
            valueBox.appendChild(this._createOptionTag(possibleValue.id, possibleValue.value, value));
        });

        return valueBox;
    }


    /**
     * toggle class "active" of button which open-close the filter window control panel
     */
    toggleFilterControlPanelButtonClass(action = '', once = true) {

        if (this.shouldChangeTheClass && once) {
            return;
        }

        let filterControlPanelOpenCloseBtn = document.querySelectorAll('#' + this.filterBoxId + ' .f_filter-add-criteria');
        filterControlPanelOpenCloseBtn.forEach(btn => {
            if (action) {
                if (action === 'add') {
                    btn.classList.add('active');
                } else if (action === 'remove') {
                    btn.classList.remove('active');
                }

            } else {
                btn.classList.toggle('active');
            }
        });

        if (!once) {
            this.shouldChangeTheClass = true;

            setTimeout(() => {
                this.shouldChangeTheClass = false;
            })
        }
    }


    /**
     * initializes the js-choice plugin for group of selectBoxes
     * @param container
     * @private
     */
    _initAllSelectBoxesOfGivenContainer(container) {
        let allSelects = container.querySelectorAll('.ngs-choice');
        allSelects.forEach(this._initSelect);
    }


    /**
     * just initializes the js-choice plugin for given selectBox
     * @param selectItem
     * @private
     */
    _initSelect(selectItem) {
        if (selectItem.choices) {
            return;
        }
        let searchable = selectItem.getAttribute('data-ngs-searchable') === 'true';
        let removable = selectItem.getAttribute('data-ngs-remove') === 'true';

        selectItem.choices = new Choices(selectItem, {
            removeItemButton: removable,
            searchEnabled: searchable,
            renderChoiceLimit: 150,
            searchResultLimit: 150,
            shouldSort: true
        });
    }

    /**
     * just removes the element from DOM
     * @param element
     * @private
     */
    _removeElement(element) {
        if (element.closest('.choices')) {
            element.closest('.choices').remove();
        } else {
            element.remove();
        }
    }


    /**
     * creates {HTMLOptionElement} with given parameters
     * @param id
     * @param text
     * @param value
     * @returns {HTMLOptionElement}
     * @private
     */
    _createOptionTag(id, text, value) {
        let option = document.createElement('option');
        option.value = id;
        option.text = text;

        if (value === option.value) {
            option.selected = true;
        }

        return option;
    }


};