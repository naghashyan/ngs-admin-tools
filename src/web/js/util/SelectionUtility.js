/**
 * SelectionUtility helper util
 * for handle checkbox selections
 *
 * @author Mikael Mkrtchyan
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2021
 */



export default class SelectionUtility {
  
    constructor(container, idAttribute, getSelectionInfo, setSelectionInfo) {
        this.container = container;
        this.idAttribute = idAttribute;
        this.getSelectionInfo = getSelectionInfo;
        this.setSelectionInfo = setSelectionInfo;
        this._init();
    }


    /**
     * init selection related functional
     *
     * @private
     */
    _init() {
        let checkItemBtn = this.container.querySelectorAll(".f_check-item");
        let totalSelectionInfo = this.getSelectionInfo();
        if (totalSelectionInfo.totalSelection) {
            this._changeAllCheckboxItems(true);
        }
        if(totalSelectionInfo.unCheckedElements && totalSelectionInfo.unCheckedElements.length) {
            this._changeItemsCheckboxes(totalSelectionInfo.unCheckedElements, false);
        }
        if(totalSelectionInfo.checkedElements && totalSelectionInfo.checkedElements.length) {
            this._changeItemsCheckboxes(totalSelectionInfo.checkedElements, true);
        }

        for(let i=0; i<checkItemBtn.length; i++) {
            checkItemBtn[i].closest('.f_check-items').addEventListener('click', (evt) => {
                evt.stopPropagation();
            });
            checkItemBtn[i].addEventListener('change', (evt) => {
                let checkItemBtn = evt.target.closest('.f_check-item');
                this._initCheckboxChange(checkItemBtn);
            });
        }
    }


    /**
     * handle checkbox value change case
     *
     * @param checkboxItem
     *
     * @private
     */
    _initCheckboxChange(checkboxItem) {
        let isAllCheckboxChanged = !checkboxItem.closest('.f_table_row');
        if (isAllCheckboxChanged) {
            this._handleMainSelectionCheckbox(checkboxItem);
        } else {
            this._handleElementSelectionChange(checkboxItem);
        }
    }


    /**
     * handle main checkbox change case
     *
     * @param checkboxItem
     *
     * @private
     */
    _handleMainSelectionCheckbox(checkboxItem) {
        let totalSelectionInfo = this.getSelectionInfo();

        if (checkboxItem.checked) {
            totalSelectionInfo.totalSelection = true;
            this._changeAllCheckboxItems(true);
        } else {
            totalSelectionInfo.totalSelection = false;
            this._changeAllCheckboxItems(false);
        }
        delete totalSelectionInfo.unCheckedElements;
        delete totalSelectionInfo.checkedElements;
        delete totalSelectionInfo.newAddedCheckedElements;
        delete totalSelectionInfo.newAddedUnCheckedElements;

        this.setSelectionInfo(totalSelectionInfo);
    }


    /**
     * handle row checkbox change case
     *
     * @param checkboxItem
     * @private
     */
    _handleElementSelectionChange(checkboxItem) {
        let totalSelectionInfo = this.getSelectionInfo();

        let elementId = checkboxItem.closest('.f_table_row').getAttribute(this.idAttribute);
        if (checkboxItem.checked) {
            if (!totalSelectionInfo.checkedElements) {
                totalSelectionInfo.checkedElements = [];
            }
            if (totalSelectionInfo.checkedElements.indexOf(elementId) === -1) {
                totalSelectionInfo.checkedElements.push(elementId);
            }

            if (totalSelectionInfo.unCheckedElements) {
                let elementIndex = totalSelectionInfo.unCheckedElements.indexOf(elementId);
                if (elementIndex !== -1) {
                    totalSelectionInfo.unCheckedElements.splice(elementIndex, 1);
                    if (!totalSelectionInfo.unCheckedElements.length) {
                        delete totalSelectionInfo.unCheckedElements;
                    }
                }
            }
        } else {

            if (!totalSelectionInfo.unCheckedElements) {
                totalSelectionInfo.unCheckedElements = [];
            }
            if (totalSelectionInfo.unCheckedElements.indexOf(elementId) === -1) {
                totalSelectionInfo.unCheckedElements.push(elementId);
            }

            if (totalSelectionInfo.checkedElements) {
                let elementIndex = totalSelectionInfo.checkedElements.indexOf(elementId);
                if (elementIndex !== -1) {
                    totalSelectionInfo.checkedElements.splice(elementIndex, 1);
                    if (!totalSelectionInfo.checkedElements.length) {
                        delete totalSelectionInfo.checkedElements;
                    }
                }
            }
        }

        this.setSelectionInfo(totalSelectionInfo);
    }


    /**
     * check all checkboxes
     *
     * @param checked
     *
     * @private
     */
    _changeAllCheckboxItems(checked) {
        let checkboxesToMarkChecked = this.container.querySelectorAll('.f_check-item');
        for (let i = 0; i < checkboxesToMarkChecked.length; i++) {
            checkboxesToMarkChecked[i].checked = checked;
        }
    }


    /**
     * change checkbox for given ids by given value
     * @param itemIds
     * @param checked
     * @private
     */
    _changeItemsCheckboxes(itemIds, checked) {
        let checkboxesToMarkChecked = this.container.querySelectorAll('.f_check-item');
        for (let i = 0; i < checkboxesToMarkChecked.length; i++) {
            let row = checkboxesToMarkChecked[i].closest('.f_table_row');
            if(row && itemIds.indexOf(row.getAttribute(this.idAttribute)) !== -1) {
                checkboxesToMarkChecked[i].checked = checked;
            }
        }
    }


};
