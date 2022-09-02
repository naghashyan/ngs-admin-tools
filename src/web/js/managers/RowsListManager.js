export default class RowsListManager {

    static listAndAddLoadsBindData = {};

    constructor(container, listLevel = null) {
        this.isShiftKeyPressed = false;
        this.listLoadContainer = container;
        if (listLevel) {
            this.listLevel = listLevel;
        }
    };


    getSelectionInfo() {
        let selectionInfoInput = this.getSelectionInfoInput();

        if (!selectionInfoInput) {
            return {};
        }
        let totalSelectionInfo = selectionInfoInput.value;
        if (!totalSelectionInfo) {
            totalSelectionInfo = {};
        } else {
            totalSelectionInfo = JSON.parse(totalSelectionInfo);
        }
        return totalSelectionInfo;
    };


    getSelectionInfoInput() {
        if (!this.listLoadContainer) {
            return null;
        }
        if (this.listLevel) {
            if (this.listLevel === 1) {
                return this.listLoadContainer.closest('.f_list-load-container').querySelector('.f_page-selection-info');
            } else if (this.listLevel === 2) {
                return this.listLoadContainer.closest('.f_list-load-container').querySelectorAll('.f_page-selection-info')[0];
            }
        } else if (!this.listLevel) {
            return this.listLoadContainer.querySelectorAll('.f_page-selection-info')[0];
        }

        return null;
    };


    setSelectionInfo(info) {
        let selectionInfoInput = this.getSelectionInfoInput();
        if (selectionInfoInput) {
            selectionInfoInput.value = JSON.stringify(info);
        }
    };

    setIsShiftPressed(value) {
        this.isShiftKeyPressed = value;
    }


    getIsShiftPressed() {
        return this.isShiftKeyPressed;
    }


    handleMainSelectionCheckbox(checkboxItem) {
        let totalSelectionInfo = this.getSelectionInfo();

        if (checkboxItem.checked) {
            totalSelectionInfo.totalSelection = true;
            this.changeAllCheckboxItems(true);
        } else {
            totalSelectionInfo.totalSelection = false;
            this.changeAllCheckboxItems(false);
        }
        delete totalSelectionInfo.unCheckedElements;
        delete totalSelectionInfo.checkedElements;
        delete totalSelectionInfo.newAddedCheckedElements;
        delete totalSelectionInfo.newAddedUnCheckedElements;

        this.setSelectionInfo(totalSelectionInfo)
    };


    changeAllCheckboxItems(checked) {
        let checkboxesToMarkChecked = this.listLoadContainer.querySelectorAll('.f_check-item');
        for (let i = 0; i < checkboxesToMarkChecked.length; i++) {
            checkboxesToMarkChecked[i].checked = checked;
        }
    };

    clearTotalSelectionAfterDeleteAction() {
        let selectionInfoInput = this.getSelectionInfoInput();
        if (selectionInfoInput) {
            let emptyObj = {};
            selectionInfoInput.setAttribute('value', JSON.stringify(emptyObj));
        }
    };

    handleElementSelectionChange(checkboxItem, isNew) {
        let totalSelectionInfo = this.getSelectionInfo();
        let checkedElements = totalSelectionInfo.checkedElements ?? [];
        let unCheckedElements = totalSelectionInfo.unCheckedElements ?? [];
        let totalSelection = totalSelectionInfo.totalSelection;

        if (!isNew) {
            this.previousItem = this.lastSelectdIndex;
            this.lastSelectdIndex = checkboxItem.closest('.f_table_row').getAttribute("data-im-index");

            if (this.isShiftKeyPressed && this.previousItem) {
                let elementsPositionsInInterval = [];
                let startPosition = parseInt(this.lastSelectdIndex);
                let endPosition = parseInt(this.previousItem);

                if (startPosition > endPosition) {
                    while (startPosition >= endPosition) {
                        elementsPositionsInInterval.push(startPosition--);
                    }
                } else {
                    while (startPosition <= endPosition) {
                        elementsPositionsInInterval.push(startPosition++);
                    }
                }

                elementsPositionsInInterval.forEach(item => {
                    let index = parseInt(item);
                    let element = document.querySelectorAll("#itemsContent .f_table_row");

                    element[index].querySelector('.f_check-item').checked = checkboxItem.checked;
                    totalSelectionInfo = this.checkUncheckElement(element[index], checkedElements, unCheckedElements, totalSelection, checkboxItem.checked);
                })
            } else {
                totalSelectionInfo = this.checkUncheckElement(checkboxItem, checkedElements, unCheckedElements, totalSelection, checkboxItem.checked);
            }
        } else {
            totalSelectionInfo = this.handleNewElementSelectionChange(checkboxItem, totalSelectionInfo)
        }

        this.setSelectionInfo(totalSelectionInfo);
    };

    checkUncheckElement(checkboxItem, checkedElements, unCheckedElements, totalSelection, isChecked) {
        let elementId = parseInt(checkboxItem.closest('.f_table_row').getAttribute("data-im-id"));

        if (isChecked) {
            if (checkedElements.indexOf(elementId) === -1 && !totalSelection) {
                checkedElements.push(elementId);
            }

            let uncheckedIndex = unCheckedElements.indexOf(elementId)
            if (uncheckedIndex !== -1) {
                unCheckedElements.splice(uncheckedIndex, 1);
            }
        } else {
            let elementIndex = checkedElements.indexOf(elementId);

            if (elementIndex !== -1) {
                checkedElements.splice(elementIndex, 1);
            }

            let uncheckedIndex = unCheckedElements.indexOf(elementId)

            if (uncheckedIndex === -1) {
                unCheckedElements.push(elementId);
            }
        }

        return {unCheckedElements: unCheckedElements, checkedElements: checkedElements, totalSelection: totalSelection};

    }

    handleNewElementSelectionChange(checkboxItem, totalSelectionInfo) {
        let tempIdOfRow = checkboxItem.closest('.f_table_row').getAttribute('data-im-index');

        if (checkboxItem.checked) {
            if (!totalSelectionInfo.newAddedCheckedElements) {
                totalSelectionInfo.newAddedCheckedElements = [];
            }
            if (totalSelectionInfo.newAddedCheckedElements.indexOf(tempIdOfRow === -1)) {
                totalSelectionInfo.newAddedCheckedElements.push(tempIdOfRow);
            }
            if (totalSelectionInfo.newAddedUnCheckedElements) {
                let elementIndex = totalSelectionInfo.newAddedUnCheckedElements.indexOf(tempIdOfRow);
                if (elementIndex !== -1) {
                    totalSelectionInfo.newAddedUnCheckedElements.splice(elementIndex, 1);
                    if (!totalSelectionInfo.newAddedUnCheckedElements.length) {
                        delete totalSelectionInfo.newAddedUnCheckedElements;
                    }
                }
            }
            if (totalSelectionInfo.hasOwnProperty('totalSelection') && !totalSelectionInfo.totalSelection) {
                delete totalSelectionInfo.totalSelection;
            }
        } else {
            if (!totalSelectionInfo.newAddedUnCheckedElements) {
                totalSelectionInfo.newAddedUnCheckedElements = [];
            }
            if (totalSelectionInfo.newAddedUnCheckedElements.indexOf(tempIdOfRow) === -1) {
                totalSelectionInfo.newAddedUnCheckedElements.push(tempIdOfRow);
            }

            if (totalSelectionInfo.newAddedCheckedElements) {
                let elementIndex = totalSelectionInfo.newAddedCheckedElements.indexOf(tempIdOfRow);
                if (elementIndex !== -1) {
                    totalSelectionInfo.newAddedCheckedElements.splice(elementIndex, 1);
                    if (!totalSelectionInfo.newAddedCheckedElements.length) {
                        delete totalSelectionInfo.newAddedCheckedElements;
                    }
                }
            }
        }
        return totalSelectionInfo;
    }

    changeItemsCountInUi(number) {
        let itemsCountTag = this.listLoadContainer.querySelector('.f_pageingBox .f_items-count');
        if (itemsCountTag) {
            let itemsCount = itemsCountTag.innerText;
            if (itemsCount) {
                let changedCount = +(itemsCount) + number;
                itemsCountTag.innerText = ' ' + changedCount + ' ';
            }
        }
    };

    getExistingRowsCountAtTheMoment() {
        let itemsCountTag = this.listLoadContainer.querySelector('.f_pageingBox .f_items-count');
        if (itemsCountTag) {
            let itemsCount = itemsCountTag.innerText;
            if (itemsCount) {
                return +itemsCount;
            }
        }
        return 0;
    }

    needToChangeMainSelectionCheckbox() {
        let selectionInfo = this.getSelectionInfo();
        let rowsCountAtTheMoment = this.getExistingRowsCountAtTheMoment();

        let uncheckedElementsCount = (selectionInfo.unCheckedElements && selectionInfo.unCheckedElements.length) ? selectionInfo.unCheckedElements.length : 0;
        let uncheckedNewElementsCount = (selectionInfo.newAddedUnCheckedElements && selectionInfo.newAddedUnCheckedElements.length) ? selectionInfo.newAddedUnCheckedElements.length : 0;

        return uncheckedElementsCount + uncheckedNewElementsCount === rowsCountAtTheMoment;
    }


    nothingIsSelected() {
        let totalSelectionInfo = this.getSelectionInfo();
        let rowsCountAtTheMoment = this.getExistingRowsCountAtTheMoment();

        let hasCheckedElements = totalSelectionInfo.checkedElements && totalSelectionInfo.checkedElements.length;
        let hasNewCheckedElements = totalSelectionInfo.newAddedCheckedElements && totalSelectionInfo.newAddedCheckedElements.length;

        if (!hasCheckedElements && !hasNewCheckedElements && !(totalSelectionInfo.totalSelection && (rowsCountAtTheMoment > 0))) {
            return true;
        }
        return false;

    }


    setListAndAddLoadsBindData(tab, data) {
        if (!RowsListManager.listAndAddLoadsBindData.hasOwnProperty(tab)) {
            RowsListManager.listAndAddLoadsBindData[tab] = {};
        }
        for (let key in data) {
            RowsListManager.listAndAddLoadsBindData[tab][key] = data[key];
        }
    }

    getListAndAddLoadsBindData(tab, key = null) {
        if (RowsListManager.listAndAddLoadsBindData.hasOwnProperty(tab)) {
            if (key) {
                if (RowsListManager.listAndAddLoadsBindData[tab].hasOwnProperty(key)) {
                    return RowsListManager.listAndAddLoadsBindData[tab][key];
                }
            } else {
                return RowsListManager.listAndAddLoadsBindData[tab];
            }
        }

        return RowsListManager.listAndAddLoadsBindData;

    }


    /**
     * resize columns on mouse drag
     * @param instance
     */
    initRowsResizing(instance) {
        let resizeButtons = document.querySelectorAll('.f_column-resize-line');
        resizeButtons.forEach((btn) => {
            btn.addEventListener('mousedown', (e) => {

                instance.columnsResizingClicked = true;

                e.stopPropagation();
                let startPosition = e.clientX;
                let column = btn.closest('li');
                let indexOfColumn = column.getAttribute('data-header-column-index');
                let verticalDownAllItemsOfCurrentColumn = column.closest('.f_table').querySelectorAll('#itemsContent .f_table_row li[data-content-row-column-index="' + indexOfColumn + '"]');

                let initialWidthOfColumn = column.offsetWidth;

                document.addEventListener('mousemove', doResizeOnDrag);

                document.addEventListener('mouseup', () => {
                    document.removeEventListener('mousemove', doResizeOnDrag)
                });


                function doResizeOnDrag(e) {
                    let movedPixels = e.clientX - startPosition;
                    if (movedPixels + initialWidthOfColumn < 60) {
                        return;
                    }

                    //uncomment if want only to be more than initial width
                    // if(movedPixels + column.offsetWidth > initialWidthOfColumn) {
                    column.style.maxWidth = movedPixels + initialWidthOfColumn + 'px';
                    column.style.minWidth = movedPixels + initialWidthOfColumn + 'px';
                    column.style.width = movedPixels + initialWidthOfColumn + 'px';

                    if (verticalDownAllItemsOfCurrentColumn.length) {
                        verticalDownAllItemsOfCurrentColumn.forEach(liElement => {
                            liElement.style.maxWidth = movedPixels + initialWidthOfColumn + 'px';
                            liElement.style.minWidth = movedPixels + initialWidthOfColumn + 'px';
                            liElement.style.width = movedPixels + initialWidthOfColumn + 'px';
                        })
                    }

                    // }
                }
            })

        })
    }

    removeSelectedItems() {
        this.setSelectionInfo({});
    }

};
