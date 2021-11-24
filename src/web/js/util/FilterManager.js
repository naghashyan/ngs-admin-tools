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
                var oldElement = document.getElementById(filterBoxId);
                var newElement = oldElement.cloneNode(true);
                oldElement.parentNode.replaceChild(newElement, oldElement);
            }
            else {
                FilterManager.destroy(filterBoxId);
            }
        }

        this.filterBoxId = filterBoxId;
        var filterInitEvent = new CustomEvent('ngsFilterInit', {
            detail: {
                id: filterBoxId
            }
        });
        document.dispatchEvent(filterInitEvent);
        this.filterChangeHandler = null;
        this.possibleFilters = filterableModel.possibleFilters;
        this._initAddCriteria();
        this._initSearchCriteria();
        FilterManager.instances[filterBoxId] = this;
        if (preselectedFilter) {
            let filterCriterias = preselectedFilter.and;
            if (filterCriterias) {

                for (let i = 0; i < filterCriterias.length; i++) {
                    this.addCriteriaToExistingOnes(filterCriterias[i], false);
                }
            }

            if(preselectedFilter.search) {
                this._addSearchCriteria(null, preselectedFilter.search);
            }
            let searchInput = document.querySelector('#' + filterBoxId + ' .f_search-criteria');
            if(searchInput) {
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
     *
     * @returns {{}}
     */
    getCurrentFilter() {
        let filterBox = document.getElementById(this.filterBoxId);
        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');
        let activeFilterItems = activeFiltersContainer.querySelectorAll('.f_filter-item');
        let filterData = {};
        if(activeFilterItems.length) {
            let filter = [];
            for(let i=0; i<activeFilterItems.length; i++) {
                filter.push(JSON.parse(activeFilterItems[i].getAttribute('data-ngs-filter-value')));
            }
            filterData.and = filter;
        }
        let activeSearchItem = activeFiltersContainer.querySelector('.f_search-item');
        if(activeSearchItem) {
            filterData.search = JSON.parse(activeSearchItem.getAttribute('data-ngs-filter-value'));
        }
        return filterData;
    }


    /**
     * init functional to add new criteria to filter
     *
     * @private
     */
    _initAddCriteria() {
        let filterBox = document.getElementById(this.filterBoxId);
        let addCriteriaButton = document.querySelectorAll('#' + this.filterBoxId + ' .f_filter-add-criteria');
        addCriteriaButton.click((evt) => {
            let criteriaBox = filterBox.querySelector('.f_create-criteria-box');
            if(criteriaBox) {
                if(this.criteriaBoxCloseHandler) {
                    document.removeEventListener('click', this.criteriaBoxCloseHandler);
                    this.criteriaBoxCloseHandler = null;
                }
                criteriaBox.remove();
            }
            else {
                let addButton = evt.target.closest('.f_filter-add-criteria');
                addButton.classList.add('active');
                let createCriteriaBox = this._createCriteriaBox();
                filterBox.appendChild(createCriteriaBox);
                this._addCriteriaItems();
                this._initAddCriteriaItem();
                this._initCancelCriteriaCreation();
                this._initApplyCriteriaItem();
                let criteriaBox = filterBox.querySelector('.f_create-criteria-box');


                this.criteriaBoxCloseHandler = function(evt) {
                    if(evt.target.closest('.f_create-criteria-box') || evt.target.closest('.f_filter-add-criteria')) {
                        return;
                    }
                    criteriaBox.remove();
                    document.removeEventListener('click', this.criteriaBoxCloseHandler);
                    this.criteriaBoxCloseHandler = null;
                };

                document.addEventListener('click', this.criteriaBoxCloseHandler);
            }

        });
    }

    _closeCriteriaBoxHandler() {

    }


    _initSearchCriteria() {
        let addCriteriaButton = document.querySelector('#' + this.filterBoxId + ' .f_search-criteria');
        if(!addCriteriaButton) {
            return;
        }

        addCriteriaButton.addEventListener('keydown', (evt) => {

            //enter was pressed
            if(evt.keyCode === 13) {
                if(addCriteriaButton.value.trim()) {
                    this._addSearchCriteria(addCriteriaButton.value);
                    addCriteriaButton.value = '';
                    this.handleFilterChange();
                }
            }
        });

    }


    /**
     * creates criteria box which should be applied to filter
     *
     * @returns {HTMLDivElement}
     * @private
     */
    _createCriteriaBox() {
        let createCriteriaBox = document.createElement("div");
        createCriteriaBox.classList.add('create-criteria-box');
        createCriteriaBox.classList.add('f_create-criteria-box');

        let criteriaItemsBox = document.createElement("div");
        criteriaItemsBox.classList.add('criteria-items-box');
        criteriaItemsBox.classList.add('f_criteria-items-box');
        createCriteriaBox.appendChild(criteriaItemsBox);

        let criteriaItemContainer = document.createElement("div");
        criteriaItemContainer.classList.add('criteria-item-container');
        criteriaItemContainer.classList.add('f_criteria-item-container');
        criteriaItemsBox.appendChild(criteriaItemContainer);

        let actionsContainer = document.createElement("div");
        actionsContainer.classList.add('actions-box');
        createCriteriaBox.appendChild(actionsContainer);


        let addCriteriaItemButton = document.createElement("button");
        addCriteriaItemButton.classList.add('add-criteria');
        addCriteriaItemButton.classList.add("button","basic","success");
        addCriteriaItemButton.classList.add('f_add-criteria');
        addCriteriaItemButton.innerHTML = "<span class=\"circle\"><i class=\"icon-svg179\"></i></span>Add Criteria";

        let addCriteriaBox = document.createElement("div");
        addCriteriaBox.classList.add('add-criteria-box', 'f_add-criteria-box');
        addCriteriaBox.appendChild(addCriteriaItemButton);


        criteriaItemsBox.appendChild(addCriteriaBox);

        let applyCriteriaButton = document.createElement("span");
        applyCriteriaButton.classList.add('apply-criteria');
        applyCriteriaButton.classList.add("button","basic","primary");
        applyCriteriaButton.classList.add('f_apply-criteria');
        applyCriteriaButton.innerText = 'Apply Criteria';
        actionsContainer.appendChild(applyCriteriaButton);

        let cancelButton = document.createElement("span");
        cancelButton.classList.add('cancel-criteria');
        cancelButton.classList.add("button","outline");

        cancelButton.classList.add('f_cancel-criteria');
        cancelButton.innerText = 'Cancel';
        actionsContainer.appendChild(cancelButton);

        return createCriteriaBox;
    }


    /**
     * on input change if it becomes valid,  invalid class should be removed
     * @private
     */
    _initEventListenersForTogglingInvalidClass() {
        let allCriteriaItemsToValidate = document.querySelectorAll('#' + this.filterBoxId + ' .f_create-criteria-box .f_criteria-item-to-validate:not([eventlistenerisset])');
        allCriteriaItemsToValidate.forEach((item) => {

            //setting this attribute helps to not set the same listener twice
            item.setAttribute('eventlistenerisset', 'true');
            item.addEventListener('change', () => {
                if(!this.isFieldInvalid(item)) {
                    if(item.tagName === 'SELECT') {
                        item.closest('.choices__inner').classList.remove('invalid-criteria-item', 'f_invalid-criteria-item');
                    }else {
                        item.querySelector('input').classList.remove('invalid-criteria-item', 'f_invalid-criteria-item');
                    }
                }
            })
        })
    }


    /**
     * initialize new criteria row adding functional
     * @private
     */
    _initAddCriteriaItem() {
        let addCriteriaItemButton = document.querySelectorAll('#' + this.filterBoxId + ' .f_create-criteria-box .f_add-criteria');
        addCriteriaItemButton.click((e) => {
            this._initEventListenersForTogglingInvalidClass();
            let invalidFields = this.getInvalidFields();

            if(!invalidFields.length) {
                this._addCriteriaItems();
            }else {
                invalidFields.forEach((invalidField) => {
                    invalidField.classList.add('invalid-criteria-item', 'f_invalid-criteria-item');
                })
            }
            this._handleAddButton();
        });
    }

    /**
     * returns all fields that are not filled correct (are invalid)
     * @returns {[]}
     */
    getInvalidFields() {
        let allFields = document.querySelectorAll('#' + this.filterBoxId + ' .f_create-criteria-box .f_criteria-item-to-validate');
        let invalidFields = [];

        for(let i = 0; i < allFields.length; i++) {
            let field = allFields[i];

            if(this.isFieldInvalid(field)) {
                if(field.tagName === 'SELECT'){
                    invalidFields.push(field.closest('.choices__inner'));
                }else {
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
        if(criteriaItem.tagName === 'SELECT'){
            if(criteriaItem.value.trim() === '' || criteriaItem.value === 'please select field') {
                return true;
            }
        }else {
            let inputField = criteriaItem.querySelector('input');

            if(inputField.value.trim() === '' || inputField.value === 'please select field') {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @private
     */
    _initApplyCriteriaItem() {
        let filterBox = document.getElementById(this.filterBoxId);
        let criteriaBox = filterBox.querySelector('.f_create-criteria-box');
        let boxNumber = null;
        if(criteriaBox.hasAttribute('criteria-box-number')){
            boxNumber = criteriaBox.getAttribute('criteria-box-number');
        }
        let addButton = filterBox.querySelector('.f_filter-add-criteria');
        let addCriteriaItemButton = criteriaBox.querySelectorAll('.f_apply-criteria');
        addCriteriaItemButton.click(() => {
            let criteriaItems = criteriaBox.querySelectorAll('.f_criteria-item');
            if (!criteriaItems || !criteriaItems.length) {
                if(this.criteriaBoxCloseHandler) {
                    document.removeEventListener('click', this.criteriaBoxCloseHandler);
                    this.criteriaBoxCloseHandler = null;
                }
                criteriaBox.remove();
                return;
            }

            let filterItems = [];
            let hasError = false;
            for (let i = 0; i < criteriaItems.length; i++) {
                let filterItem = {};
                let fieldSelect = criteriaItems[i].querySelector('.f_select-field');
                let fieldName = fieldSelect.value;
                if (!fieldName) {
                    continue;
                }
                filterItem.fieldName = fieldName;
                let conditionField = criteriaItems[i].querySelector('.f_select-condition input, .f_select-condition select');
                if(!conditionField) {
                    conditionField = criteriaItems[i].querySelector('.f_select-condition');
                }
                let conditionType = conditionField.getAttribute('data-ngs-type');
                let conditionValue = conditionField.value;
                filterItem.conditionType = conditionType;
                if (filterItem.conditionType === 'checkbox') {
                    filterItem.searchValue = conditionField.checked ? 1 : 0;
                    filterItems.push(filterItem);
                    continue;
                }
                if (filterItem.conditionType === 'select') {
                    filterItem.searchValue = conditionField.value;
                    filterItems.push(filterItem);
                    continue;
                }

                filterItem.conditionValue = conditionValue;
                let searchValueField = criteriaItems[i].querySelectorAll('.f_condition-value');


                filterItem.searchValue = null;
                for (let j = 0; j < searchValueField.length; j++) {
                    filterItem.searchValue = searchValueField[j].querySelector('input').value;
                }
                filterItems.push(filterItem);
            }

            if (!(hasError || !filterItems.length)) {
                this.addCriteriaToExistingOnes({or: filterItems}, true, boxNumber);
            }
            addButton.classList.remove('active');
            if(this.criteriaBoxCloseHandler) {
                document.removeEventListener('click', this.criteriaBoxCloseHandler);
                this.criteriaBoxCloseHandler = null;
            }
            criteriaBox.remove();
        });
    }


    _addSearchCriteria(searchValue, searchValues) {
        if(searchValue) {
            searchValue = searchValue.trim();
        }
        let filterBox = document.getElementById(this.filterBoxId);
        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');
        activeFiltersContainer.classList.add('active');
        let searchItem = activeFiltersContainer.querySelector('.f_search-item');

        if(!searchItem) {
            let newCriteria = null;
            if(searchValue) {
                newCriteria = this._createCriteriaItem(searchValue, [searchValue], 'search');
            }
            else {
                newCriteria = this._createCriteriaItem(searchValues.join(' OR '), searchValues, 'search');
            }
            activeFiltersContainer.appendChild(newCriteria);
            let removeBtn = newCriteria.querySelectorAll('.f_remove-criteria');
            removeBtn.click((evt) => {
                evt.target.closest('.f_search-item').remove();
                this.handleFilterChange();
            });
        }
        else {
            let oldSearch = searchItem.getAttribute('data-ngs-filter-value');
            oldSearch = JSON.parse(oldSearch);
            oldSearch.push(searchValue);
            searchItem.querySelector('.f_criteria-name').innerText = oldSearch.join(' OR ');
            searchItem.setAttribute('data-ngs-filter-value', JSON.stringify(oldSearch));
        }

    }


    /**
     *
     * @param filterItems
     * @param doLoad
     * @param boxNumber
     */
    addCriteriaToExistingOnes(filterItems, doLoad, boxNumber=null) {
        let existingCriteriaItems = document.getElementById(this.filterBoxId).querySelectorAll('[criteria-item-number]');
        if(existingCriteriaItems.length && boxNumber){
            for (let i = 0; i < existingCriteriaItems.length; ++i){
                let value = existingCriteriaItems[i].getAttribute('criteria-item-number');
                if(value === boxNumber){
                    this.updateCriteriaItem(i, filterItems, doLoad);
                    return;
                    // existingCriteriaItems[i].remove();
                }
            }
        }

        let filterBox = document.getElementById(this.filterBoxId);

        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');

        activeFiltersContainer.classList.add('active');
        let activeFilterItems = activeFiltersContainer.querySelectorAll('.f_filter-item');
        let newFilterIndex = activeFilterItems.length + 1;


        let newCriteria = this._createCriteriaItem('criteria - ' + newFilterIndex, filterItems);

        activeFiltersContainer.appendChild(newCriteria);

        let removeBtn = newCriteria.querySelectorAll('.f_remove-criteria');
        removeBtn.click((evt) => {
            evt.target.closest('.f_filter-item').remove();
            this.handleFilterChange();
        });

        if(doLoad) {
            this.handleFilterChange();
        }
    }



    updateCriteriaItem(i, filterItems, doLoad){
        let filterBox = document.getElementById(this.filterBoxId);
        let activeFiltersContainer = filterBox.querySelector('.f_active-filters .f_criteria-box');
        activeFiltersContainer.classList.add('active');
        let currentFilterItem = activeFiltersContainer.querySelectorAll('.f_filter-item')[i];
        currentFilterItem.removeAttribute('data-ngs-filter-value');
        currentFilterItem.setAttribute('data-ngs-filter-value', JSON.stringify(filterItems));
        if(doLoad){
            this.handleFilterChange();
        }
    }

    /**
     *
     * @param name
     * @param filterItems
     * @param type
     *
     * @returns {HTMLSpanElement}
     * @private
     */
    _createCriteriaItem(name, filterItems, type) {
        let newFilterItem = document.createElement('span');

        let filterNameContainer = document.createElement('button');

        let uniqueName = name.substring(name.lastIndexOf('-') + 2);

        filterNameContainer.classList.add('filter-name-container', 'f_filter-name-container');
        newFilterItem.setAttribute('criteria-item-number', uniqueName);
        newFilterItem.appendChild(filterNameContainer);

        if(type === 'search') {
            newFilterItem.classList.add('f_search-item');
        }
        else {
            newFilterItem.classList.add('f_filter-item');
        }
        let filterName = document.createElement('span');
        filterName.classList.add('f_criteria-name');
        filterName.innerText = name;
        filterNameContainer.appendChild(filterName);
        newFilterItem.classList.add('filter-item');
        newFilterItem.setAttribute('data-ngs-filter-value', JSON.stringify(filterItems));
        let criteriaRemoveBtn = document.createElement('button');
        criteriaRemoveBtn.classList.add('close-btn', 'f_remove-criteria');
        newFilterItem.appendChild(criteriaRemoveBtn);

        filterNameContainer.addEventListener('click', (evt) => {
            let criterianNameBlock = filterNameContainer.querySelector('.f_criteria-name');
            let oldName = criterianNameBlock.innerText;
            criterianNameBlock.innerText = 'Loading...';
            this.showSelectedCriterias(evt, uniqueName).then(function() {
                criterianNameBlock.innerText = oldName;
            });
            this._handleAddButton();
        });
        return newFilterItem;

    }


    /**
     *
     * @param container
     */
    initShowDetailedInfosOfCriterias(container) {
        let openDetailsBtn = container.querySelector('.f_open-details');
        if(!openDetailsBtn) {
            return;
        }

        openDetailsBtn.addEventListener('click', () => {
            let filterBox = openDetailsBtn.closest(".center-box");
            if(filterBox && filterBox.classList.contains('is_detailed')) {
                let details = container.querySelectorAll('.f_criteria-info');
                for(let i =0; i<details.length; i++) {
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
        if(!criteriaContainer.classList.contains('is_detailed')) {
            return;
        }
        for(let i=0; i<criterias.length; i++) {
            let infos = this.showDetailedInfosOfCriteria(criterias[i]);
            let existingInfo = criterias[i].querySelector('.f_criteria-info');
            if(existingInfo) {
                existingInfo.remove();
            }
            if(infos) {
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
        if(!filterItems.or || !filterItems.or.length) {
            return null;
        }

        filterItems = filterItems.or;

        for(let i=0; i<filterItems.length; i++) {
            if(filterItems[i].and) {
                this._showInfoOfCriteria(conditionsInfos, filterItems[i].and, 'and');
            }
            else {
                this._showInfoOfCriteria(conditionsInfos, [filterItems[i]]);
            }
        }

        return conditionsInfos;
    }


    _showInfoOfCriteria(conditionsInfos, criteriaItems, delim) {
        let conditionInfo = document.createElement('li');

        let subConditionsContainer = document.createElement('ul');
        for(let i=0; i<criteriaItems.length; i++) {
            let conditionPart = document.createElement('li');

            let item = criteriaItems[i];
            let fieldName = item.fieldName
            let condition = item.conditionValue;
            let searchValue = item.searchValue;
            let filterData = this._getPossibleValueInfoByFieldId(fieldName);
            if(!filterData) {
                continue;
            }

            let type = filterData.type;

            if(type === 'checkbox') {
                conditionPart.innerHTML = '<span class="name">' + filterData.value  + ':</span> is <span class="value">' + (searchValue ? "true" : "false") + '</span>';
            }
            else if(type === 'number' || type === 'date') {
                conditionPart.innerHTML = '<span class="name">' + filterData.value  + ':</span> <span class="value">' + this._getConditionText(condition) + ' ' + searchValue + '</span>';
            }
            else if(type === 'select') {
                let possibleValues = filterData.possible_values;
                let possibleValue = "";
                for(let j=0; j<possibleValues.length; j++) {
                    if(possibleValues[j].id == searchValue) {
                        possibleValue = possibleValues[j].value;
                    }
                }
                conditionPart.innerHTML = '<span class="name">' + filterData.value  + ':</span> is <span class="value">' + possibleValue + '</span>';
            }
            else {
                conditionPart.innerHTML = '<span class="name">' + filterData.value  + ':</span> <span class="value">' + this._getConditionText(condition) + ' ' + searchValue + '</span>';
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
        if(condition === 'greater') {
            return "is greater then";
        }
        else if(condition === 'greater_or_equal') {
            return "is greater or equal then";
        }
        else if(condition === 'less') {
            return "is less then";
        }
        else if(condition === 'less_or_equal') {
            return "is less or equal then";
        }
        else if(condition === 'not_equal') {
            return "si not equal to";
        }
        else if(condition === 'equal') {
            return "is equal to";
        }
        else if(condition === 'like') {
            return "contains";
        }
        else if(condition === 'not_like') {
            return "not contains";
        }
        return "";
    }


    showSelectedCriterias(evt, uniqueName){
        return new Promise((resolve, reject) => {
            let startDate = Date.now();
            let alreadyOpenedCriteriaBox = document.getElementById(this.filterBoxId).querySelector('.f_create-criteria-box');
            if(alreadyOpenedCriteriaBox){
                alreadyOpenedCriteriaBox.remove();
            }
            if(!evt.target.closest('.f_filter-item')) {
                resolve(true);
                return;
            }

            setTimeout(() => {
                let filterItems = JSON.parse(evt.target.closest('.f_filter-item').getAttribute('data-ngs-filter-value'));

                let filterBox = document.getElementById(this.filterBoxId);
                let createCriteriaBox = this._createCriteriaBox();
                createCriteriaBox.setAttribute('criteria-box-number', uniqueName);
                filterBox.appendChild(createCriteriaBox);
                let criteriaBox = filterBox.querySelector('.f_create-criteria-box');

                let filterCriteriaItems = filterItems.or;

                for(let i=0; i<filterCriteriaItems.length; i++) {
                    if(filterCriteriaItems[i].and) {
                        this._addCriteriaItems(filterCriteriaItems[i].and, 'And');
                    }
                    else {
                        this._addCriteriaItems([filterCriteriaItems[i]], '');
                    }
                }

                this._initAddCriteriaItem();
                this._initCancelCriteriaCreation();
                this._initApplyCriteriaItem();

                let allSelects = document.querySelectorAll('#' + this.filterBoxId + ' .f_create-criteria-box .ngs-choice');
                for(let i=0; i < allSelects.length; i++) {
                    this._initSelect(allSelects[i]);
                }

                this.criteriaBoxCloseHandler = function(evt) {
                    if(evt.target.closest('.f_create-criteria-box') || evt.target.closest('.f_filter-add-criteria')) {
                        return;
                    }
                    if(!evt.target.closest('.f_filter-item')){
                        criteriaBox.remove();
                    }
                    document.removeEventListener('click', this.criteriaBoxCloseHandler);
                    this.criteriaBoxCloseHandler = null;
                };

                document.addEventListener('click', this.criteriaBoxCloseHandler);
                resolve(true);
            }, 50);
        });
    }

    /**
     *
     * @private
     */
    _initCancelCriteriaCreation() {
        let filterBox = document.querySelector('#' + this.filterBoxId);
        let criteriaBox = filterBox.querySelector('.f_create-criteria-box');
        let addCriteriaItemButton = criteriaBox.querySelectorAll('.f_cancel-criteria');
        addCriteriaItemButton.click(() => {
            let addButton = filterBox.querySelector('.f_filter-add-criteria');
            addButton.classList.remove('active');
            if(this.criteriaBoxCloseHandler) {
                document.removeEventListener('click', this.criteriaBoxCloseHandler);
                this.criteriaBoxCloseHandler = null;
            }
            criteriaBox.remove();
        });
    }


    /**
     * add criteria item
     *
     * @private
     */
    _addCriteriaItems(items, delimeter) {
        let criteriaItemBox = document.createElement("div");
        criteriaItemBox.classList.add('criteria-item', 'f_criteria-item');
        let deleteCriteriaItem = document.createElement("span");
        deleteCriteriaItem.classList.add('f_remove-criteria', 'remove-criteria');
        deleteCriteriaItem.innerHTML = '<i class="icon-delete"></i>';
        criteriaItemBox.appendChild(deleteCriteriaItem);

        if(!items || !items.length) {
            items = [null];
        }

        for(let i=0; i<items.length; i++) {
            let subCriteriaItemBox = null;
            if(items.length > 1) {
                subCriteriaItemBox = document.createElement("div");
                subCriteriaItemBox.classList.add('sub-criteria-item', 'f_sub-criteria-item');
            }
            let delimeterSpan = null;
            if(i > 0 && delimeter) {
                delimeterSpan = document.createElement("span");
                delimeterSpan.classList.add('delimeter', 'f_delimeter');
                delimeterSpan.innerText = delimeter;
            }
            let item = items[i];
            let fieldSelect = document.createElement("select");
            fieldSelect.classList.add('select-field', 'f_select-field', 'ngs-choice', 'f_criteria-item-to-validate');
            fieldSelect.setAttribute('data-ngs-searchable', (this.possibleFilters.length > 5) ? 'true' : 'false');

            let option = document.createElement("option");

            option.value = '';
            option.text = 'please select field';
            option.selected = true;
            option.disabled = true;

            fieldSelect.appendChild(option);

            for (let i = 0; i < this.possibleFilters.length; i++) {
                let option = document.createElement("option");
                option.value = this.possibleFilters[i]['id'];
                option.text = this.possibleFilters[i]['value'];
                if(item && item.fieldName === option.value){
                    option.setAttribute('selected', 'true');
                }
                fieldSelect.appendChild(option);
            }

            let conditionFields = [];
            if(item) {
                conditionFields = this._createConditionSelectionByFieldId(item.fieldName, item.conditionType, item.conditionValue, item.searchValue);
            }

            if(delimeterSpan) {
                criteriaItemBox.appendChild(delimeterSpan);
                criteriaItemBox.classList.add('delimeter-included');
            }

            if(subCriteriaItemBox) {
                subCriteriaItemBox.appendChild(fieldSelect);
                this._addConditionFieldsToCriteria(subCriteriaItemBox, conditionFields);
                criteriaItemBox.append(subCriteriaItemBox);
            }
            else {
                criteriaItemBox.appendChild(fieldSelect);
                this._addConditionFieldsToCriteria(criteriaItemBox, conditionFields);
            }

            this._initSelect(fieldSelect);
        }

        let criteriaBox = document.querySelector('#' + this.filterBoxId + ' .f_criteria-item-container');
        criteriaBox.appendChild(criteriaItemBox);

        this._initRemoveCriteriaItem(criteriaItemBox);
        this._initFieldSelection(criteriaItemBox);
    }


    /**
     * adds condition fields to criteria
     *
     * @param criteriaBox
     * @param conditionFields
     * @private
     */
    _addConditionFieldsToCriteria(criteriaBox, conditionFields) {
        if(conditionFields.length) {
            for(let conditionIndex = 0; conditionIndex < conditionFields.length; conditionIndex++) {
                criteriaBox.appendChild(conditionFields[conditionIndex]);
            }
        }
    }



    /**
     *
     * @param criteriaItemBox
     * @private
     */
    _initRemoveCriteriaItem(criteriaItemBox) {
        let removeCriteriaItemButton = criteriaItemBox.querySelectorAll('.f_remove-criteria');
        removeCriteriaItemButton.click((evt) => {
            evt.stopPropagation();
            let criteriaItem = evt.target.closest('.f_criteria-item');
            let previousElement = criteriaItem.previousSibling;
            let nextElement = criteriaItem.nextSibling;
            if(!previousElement && !nextElement) {
                return;
            }
            if(previousElement && previousElement.classList.contains('f_criteria-item-delimiter')) {
                previousElement.remove();
            }
            if(!previousElement && nextElement && nextElement.classList.contains('f_criteria-item-delimiter')) {
                nextElement.remove();
            }
            criteriaItem.remove();
            this._handleAddButton();
        });
    }

    _handleAddButton(){
        let box = document.querySelector('.f_create-criteria-box');
        if(!box) {
            return;
        }
        let button = box.querySelector('.f_add-criteria-box');
        let countOfCriterias =  document.querySelector('.f_criteria-item-container').querySelectorAll('.f_criteria-item').length;
        if(countOfCriterias < 10){
            button.classList.remove('hide');
        }else{
            button.classList.add('hide');
        }
    }


    /**
     *
     * @param criteriaItemBox
     * @private
     */
    _initFieldSelection(criteriaItemBox) {
        let selectFieldItems = criteriaItemBox.querySelectorAll('#' + this.filterBoxId + ' .f_criteria-items-box .f_select-field');
        selectFieldItems.change((evt) => {
            let criteriaItemBox = evt.target.closest('.f_criteria-item');
            let fieldId = evt.target.value;
            let selectConditionField = criteriaItemBox.querySelectorAll('.f_select-condition');
            if (selectConditionField && selectConditionField.length) {

                selectConditionField.forEach((el) =>  {
                    if(el.closest('.choices')) {
                        el.closest('.choices').remove();
                    }
                    else {
                        el.remove();
                    }

                });
            }

            let conditionValueField = criteriaItemBox.querySelectorAll('.f_condition-value');
            if (conditionValueField && conditionValueField.length) {
                conditionValueField.forEach((el) =>  {
                    if(el.closest('.choices')) {
                        el.closest('.choices').remove();
                    }
                    else {
                        el.remove();
                    }
                });
            }

            let conditionFields = this._createConditionSelectionByFieldId(fieldId);
            for (let i = 0; i < conditionFields.length; i++) {
                criteriaItemBox.appendChild(conditionFields[i]);
            }
            let selects = criteriaItemBox.querySelectorAll('.ngs-choice');
            for(let i=0; i<selects.length; i++) {
                this._initSelect(selects[i]);
            }

        });
    }


    /**
     *
     * @param fieldId
     * @param conditionType
     * @param fieldType
     * @param fieldValue
     * @returns {[]}
     * @private
     */
    _createConditionSelectionByFieldId(fieldId, conditionType, fieldType, fieldValue) {
        if(!Array.isArray(fieldId)){
            fieldId = [fieldId];
            fieldType = [fieldType];
            fieldValue = [fieldValue];
        }


        let elementsToAdd = [];
        for(let i = 0; i < fieldId.length; ++i){
            let type = conditionType;
            if(!conditionType) {
                type = this._getTypeByFieldId(fieldId[i]);
            }
            if (type === 'checkbox') {
                elementsToAdd.push(this._createCheckboxCondition(fieldValue[i]));
            }
            else if (type === 'number') {
                elementsToAdd.push(this._createNumberCondition(fieldType[i]));
                elementsToAdd.push(this._createNumberValue(fieldValue[i]));
            }
            else if (type === 'date') {
                elementsToAdd.push(this._createDateCondition(fieldType[i]));
                elementsToAdd.push(this._createDateValue(fieldValue[i]));
            }
            else if (type === 'select') {
                let possibleValues = this._getPossibleValuesByFieldId(fieldId[i]);
                let field = this._createSelectCondition(possibleValues, fieldValue[i]);
                elementsToAdd.push(field);
            }
            else{
                let isTinyMceField = type === 'long_text';
                elementsToAdd.push(this._createTextCondition(isTinyMceField, fieldType[i]));
                elementsToAdd.push(this._createTextValue(fieldValue[i]));
            }
        }
        //add new conditions here
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
        if(!possibleValue) {
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
        if(!possibleValue) {
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

            if(possibleValueParts.length > 1 && possibleValueParts[1] === fieldId) {
                return this.possibleFilters[i];
            }
        }

        return null;
    }


    /**
     *
     * @returns {HTMLDivElement}
     * @private
     */
    _createCheckboxCondition(fieldValue=null) {
        let container = document.createElement('div');
        container.classList.add('condition-value', 'f_select-condition');
        let conditionBox = document.createElement("input");
        conditionBox.setAttribute('type', 'checkbox');
        conditionBox.setAttribute('data-ngs-type', 'checkbox');
        if(fieldValue && fieldValue == 1){
            conditionBox.setAttribute('checked', 'true');
        }

        container.appendChild(conditionBox);

        return container;
    }


    /**
     *
     * @param isTinyMce
     * @param fieldType
     * @returns {HTMLSelectElement}
     * @private
     */
    _createTextCondition(isTinyMce, fieldType=null) {

        let conditionBox = document.createElement("select");
        conditionBox.setAttribute('data-ngs-type', 'text');
        conditionBox.classList.add('f_select-condition', 'select-condition', 'ngs-choice', 'f_criteria-item-to-validate');

        if(!isTinyMce) {
            let equalOption = document.createElement("option");
            equalOption.value = 'equal';
            equalOption.text = 'Equal';
            conditionBox.appendChild(equalOption);

            let notEqualOption = document.createElement("option");
            notEqualOption.value = 'not_equal';
            notEqualOption.text = 'Not equal';
            conditionBox.appendChild(notEqualOption);
        }

        let likeOption = document.createElement("option");
        likeOption.value = 'like';
        likeOption.text = 'Like';
        conditionBox.appendChild(likeOption);

        let notLikeOption = document.createElement("option");
        notLikeOption.value = 'not_like';
        notLikeOption.text = 'Not like';
        conditionBox.appendChild(notLikeOption);

        let allOptions = conditionBox.querySelectorAll('option');
        for(let i = 0; i < allOptions.length; ++i){
            if(fieldType && allOptions[i].value === fieldType){
                allOptions[i].selected = 'true';
            }
        }

        return conditionBox;
    }


    /**
     *
     * @returns {HTMLDivElement}
     * @private
     */
    _createTextValue(fieldValue=null) {
        let container = document.createElement('div');
        container.classList.add('condition-value', 'f_condition-value', 'f_criteria-item-to-validate');
        let valueBox = document.createElement("input");
        valueBox.setAttribute('type', 'text');
        if(fieldValue){
            valueBox.setAttribute('value', fieldValue);
        }

        container.appendChild(valueBox);

        return container;
    }


    /**
     *
     * @returns {HTMLSelectElement}
     * @private
     */
    _createNumberCondition(fieldType=null) {

        let conditionBox = document.createElement("select");
        conditionBox.setAttribute('data-ngs-type', 'number');
        conditionBox.classList.add('f_select-condition', 'select-condition', 'ngs-choice', 'f_criteria-item-to-validate');

        let equalOption = document.createElement("option");
        equalOption.value = 'equal';
        equalOption.text = 'Equal';
        conditionBox.appendChild(equalOption);

        let notEqualOption = document.createElement("option");
        notEqualOption.value = 'not_equal';
        notEqualOption.text = 'Not equal';
        conditionBox.appendChild(notEqualOption);

        let greaterOption = document.createElement("option");
        greaterOption.value = 'greater';
        greaterOption.text = 'Greater';
        conditionBox.appendChild(greaterOption);

        let greaterOrEqualOption = document.createElement("option");
        greaterOrEqualOption.value = 'greater_or_equal';
        greaterOrEqualOption.text = 'Greater or equal';
        conditionBox.appendChild(greaterOrEqualOption);

        let lessOption = document.createElement("option");
        lessOption.value = 'less';
        lessOption.text = 'Less';
        conditionBox.appendChild(lessOption);

        let lessOrEqualOption = document.createElement("option");
        lessOrEqualOption.value = 'less_or_equal';
        lessOrEqualOption.text = 'Less or Equal';
        conditionBox.appendChild(lessOrEqualOption);

        let allOptions = conditionBox.querySelectorAll('option');
        for(let i = 0; i < allOptions.length; ++i){
            if(fieldType && allOptions[i].value === fieldType){
                allOptions[i].selected = 'true';
            }
        }

        return conditionBox;
    }


    /**
     *
     * @returns {HTMLDivElement}
     * @private
     */
    _createNumberValue(fieldValue=null) {
        let container = document.createElement('div');
        container.classList.add('condition-value', 'f_condition-value', 'f_criteria-item-to-validate');
        let valueBox = document.createElement("input");
        valueBox.setAttribute('type', 'number');
        if(fieldValue){
            valueBox.setAttribute('value', fieldValue);
        }

        container.appendChild(valueBox);

        return container;
    }

    /**
     *
     * @returns {HTMLSelectElement}
     * @private
     */
    _createDateCondition(fieldType=null) {

        let conditionBox = document.createElement("select");
        conditionBox.setAttribute('data-ngs-type', 'date');
        conditionBox.classList.add('f_select-condition', 'select-condition', 'ngs-choice', 'f_criteria-item-to-validate');

        let equalOption = document.createElement("option");
        equalOption.value = 'equal';
        equalOption.text = 'Equal';
        conditionBox.appendChild(equalOption);

        let notEqualOption = document.createElement("option");
        notEqualOption.value = 'not_equal';
        notEqualOption.text = 'Not equal';
        conditionBox.appendChild(notEqualOption);

        let greaterOption = document.createElement("option");
        greaterOption.value = 'greater';
        greaterOption.text = 'Greater';
        conditionBox.appendChild(greaterOption);

        let greaterOrEqualOption = document.createElement("option");
        greaterOrEqualOption.value = 'greater_or_equal';
        greaterOrEqualOption.text = 'Greater or equal';
        conditionBox.appendChild(greaterOrEqualOption);

        let lessOption = document.createElement("option");
        lessOption.value = 'less';
        lessOption.text = 'Less';
        conditionBox.appendChild(lessOption);

        let lessOrEqualOption = document.createElement("option");
        lessOrEqualOption.value = 'less_or_equal';
        lessOrEqualOption.text = 'Less or Equal';
        conditionBox.appendChild(lessOrEqualOption);

        let allOptions = conditionBox.querySelectorAll('option');
        for(let i = 0; i < allOptions.length; ++i){
            if(fieldType && allOptions[i].value === fieldType){
                allOptions[i].selected = 'true';
            }
        }

        return conditionBox;
    }


    /**
     *
     * @returns {HTMLDivElement}
     * @private
     */
    _createDateValue(fieldValue=null) {
        let container = document.createElement('div');
        container.classList.add('condition-value', 'f_condition-value', 'f_criteria-item-to-validate');
        let valueBox = document.createElement("input");
        valueBox.setAttribute('type', 'date');
        if(fieldValue){
            valueBox.setAttribute('value', fieldValue);
        }

        container.appendChild(valueBox);

        return container;
    }


    _createSelectCondition(possibleValues, value=null) {
        let container = document.createElement('div');
        container.classList.add('condition-value', 'f_select-condition');

        let valueBox = document.createElement("select");
        valueBox.setAttribute('data-ngs-type', 'select');
        valueBox.classList.add('select-condition', 'ngs-choice', 'f_criteria-item-to-validate');
        valueBox.setAttribute('data-ngs-searchable', (possibleValues.length > 5) ? 'true' : 'false');

        if(!possibleValues) {
            possibleValues = [];
        }
        for(let i=0; i<possibleValues.length; i++) {
            let option = document.createElement("option");
            option.value = possibleValues[i].id;
            option.text = possibleValues[i].value;
            if(option.value == value){
                option.selected = true;
            }
            valueBox.appendChild(option);
        }

        container.append(valueBox);

        return container;
    }


    _initSelect(selectItem) {
        if(selectItem.choices) {
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
};