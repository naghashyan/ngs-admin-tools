import sortable from "../lib/html5sortable.min.js";

export default class ExportTemplatesManager {

    constructor(possibleFields, itemType, getContentAction, downloadCallback) {
        this._initAddCustomColumn();
        this._initCreateTemplate();
        this._initCancelAddTemplate();

        NGS.action(getContentAction, {item_type: itemType}, (data) => {
            this.downloadCallback = downloadCallback;
            this.itemType = itemType;
            this.possibleFields = possibleFields;
            this.saveAction = data.saveAction;
            this.deleteAction = data.deleteAction;
            this._initSavedTemplates(data.items);
            this._openExcelExportPupUp();
            this._initSavedTemplatesActions();
            this._initSaveTemplate();
            this._initCloseExcelExportPopUp();
            this._checkExportActionButtonStatus();
            this._initExportAction();
        });
    }


    /**
     * init savedTemplates
     *
     * @param savedTemplates
     * @private
     */
    _initSavedTemplates(savedTemplates) {
        let templatesContainer = document.getElementById('exportExcelContainer').querySelector('.f_existing-templates');

        templatesContainer.innerHTML = "";

        if (savedTemplates.length) {
            let templatesTitle = document.querySelector('.f_existing-templates-title');
            templatesTitle.classList.remove('is_hidden');
        }

        for (let i = 0; i < savedTemplates.length; i++) {
            let object = savedTemplates[i];
            let contactRow = this.createFieldRow('existingTemplateToCreate', object)

            templatesContainer.appendChild(contactRow);
        }
    }

    _initAddCustomColumn() {
        let addCustomColumnBtn = document.getElementById('exportExcelContainer').querySelector('.f_add-custom-column');
        if (!addCustomColumnBtn) {
            return;
        }
        let fieldsContainer = document.querySelector('.f_select-fields-info-container');
        if (!fieldsContainer) {
            return;
        }
        let selectRowTemplate = document.getElementById('selectCustomFieldColumnToCreate').innerHTML;
        if (!addCustomColumnBtn.addCustomColumnHandler) {
            addCustomColumnBtn.addCustomColumnHandler = () => {
                fieldsContainer.prepend(this._renderTemplate(selectRowTemplate, {
                    value: 'Custom column'
                }));
                this._initSortableFields();
            };
        }

        addCustomColumnBtn.removeEventListener('click', addCustomColumnBtn.addCustomColumnHandler);
        addCustomColumnBtn.addEventListener('click', addCustomColumnBtn.addCustomColumnHandler);
    }

    _addNewCustomFieldRow() {
        let fieldsContainer = document.querySelector('.f_select-fields-info-container');
        if (!fieldsContainer) {
            return;
        }

        let selectRowTemplate = document.getElementById('selectCustomFieldColumnToCreate').innerHTML;
        fieldsContainer.prepend(this._renderTemplate(selectRowTemplate, {
            value: 'Custom column'
        }));
        this._initSortableFields();
    }

    /**
     *
     * @param possibleFields
     * @private
     */
    _initNewTemplateFiledSelection(possibleFields, item = null) {
        let nameInput = document.getElementById('templateName');
        nameInput.value = "";
        let saveTemplateBtn = document.querySelector('.f_form-actions .f_save-template');
        saveTemplateBtn.removeAttribute('data-id');
        let fieldsContainer = document.querySelector('.f_select-fields-info-container');
        fieldsContainer.innerHTML = '';


        if (!item) {
            let selectRowTemplate = document.getElementById('selectFieldColumnToCreate').innerHTML;
            for (let i = 0; i < possibleFields.length; i++) {
                fieldsContainer.appendChild(this._renderTemplate(selectRowTemplate, {
                    id: possibleFields[i]['id'],
                    systemValue: possibleFields[i]['value'],
                    value: possibleFields[i]['value']
                }));
            }

        } else {
            nameInput.value = item.name;
            saveTemplateBtn.setAttribute('data-id', item.id);

            let selectedFields = JSON.parse(item.info);

            for (let i = 0; i < selectedFields.length; i++) {
                if (selectedFields[i].hasOwnProperty('type')) {
                    let selectCustomRowTemplate = document.getElementById('selectCustomFieldColumnToCreate').innerHTML;

                    let newTemplate = this._renderTemplate(selectCustomRowTemplate, {
                        value: selectedFields[i]['displayName']
                    });

                    newTemplate.querySelector('.f_column-export-formula').value = selectedFields[i]['formula'];
                    fieldsContainer.appendChild(newTemplate);
                } else {
                    let selectRowTemplate = document.getElementById('selectFieldColumnToCreate').innerHTML;

                    fieldsContainer.appendChild(this._renderTemplate(selectRowTemplate, {
                        id: selectedFields[i]['fieldName'],
                        systemValue: this.getFieldSystemDisplayName(selectedFields[i]['fieldName']),
                        value: selectedFields[i]['displayName']
                    }));
                }
            }

            for (let i = 0; i < possibleFields.length; i++) {
                let selectRowTemplate = document.getElementById('selectFieldColumnToCreate').innerHTML;
                let element = fieldsContainer.querySelector(`[data-id="${possibleFields[i]['id']}"]`);

                if (element) {
                    continue;
                }
              
                let template = this._renderTemplate(selectRowTemplate, {
                    id: possibleFields[i]['id'],
                    systemValue: possibleFields[i]['value'],
                    value: possibleFields[i]['value'],
                    checked: true
                });

                template.querySelector('.f_check-select-field').checked = true;

                fieldsContainer.appendChild(template);
            }
        }

        this._initSortableFields();

    }


    getFieldSystemDisplayName(fieldId) {
        let displayName = ''


        this.possibleFields.forEach(item => {
            if (item.id === fieldId) {
                displayName = item.value;
            }
        })

        return displayName;
    }

    /**
     *
     * @private
     */
    _initSortableFields() {
        sortable('.f_select-fields-info-container', {items: 'ul', handle: '.f_drag-indicator_btn'});
    }


    /**
     *
     * @param elem
     * @private
     */
    _initSavedTemplatesActions(elem) {
        let elements = [];
        if (!elem) {
            elements = document.getElementById('exportExcelContainer').querySelectorAll('.f_template-container');
        } else {
            elements = [elem];
        }

        for (let i = 0; i < elements.length; i++) {
            let templateSelectionBtn = elements[i].querySelector(".f_template");
            let deleteTemplateBtn = elements[i].querySelector(".f_delete-template");
            let editTemplateBtn = elements[i].querySelector(".f_edit-template");

            deleteTemplateBtn.addEventListener('click', this._initDeleteSavedTemplate.bind(this));
            editTemplateBtn.addEventListener('click', this._initEditSavedTemplate.bind(this));
            templateSelectionBtn.addEventListener('click', this._initSelectSavedTemplate.bind(this))
        }

    }


    /**
     *
     * @param evt
     * @private
     */
    _initDeleteSavedTemplate(evt) {
        let elem = evt.target.closest(".f_delete-template");
        let savedTemplate = elem.closest('.f_template-container');
        let id = elem.getAttribute('data-im-id');

        NGS.action(this.deleteAction, {template_id: id}, (data) => {
            if (data.error) {
                this._setMessage('.f_export-message',  data.message, true);
            } else {
                savedTemplate.remove();

                let templatesTitle = document.getElementById('exportExcelContainer').querySelector('.f_existing-templates-title');
                let allTemplatesContainer = document.querySelector('.f_existing-templates');

                if (!allTemplatesContainer.querySelector('.f_template-container')) {
                    templatesTitle.classList.add('is_hidden');
                }

            }

            this._checkExportActionButtonStatus();
        });
    }

    _initEditSavedTemplate(evt) {
        let elem = evt.target.closest(".f_edit-template");
        let savedTemplate = elem.parentElement.querySelector('.f_template');

        this.changeVisibilityExportPart();

        let item = {
            id: savedTemplate.getAttribute('data-im-template-id'),
            name: savedTemplate.getAttribute('data-im-template-name'),
            info: savedTemplate.getAttribute('data-im-template-info')
        };

        this._initNewTemplateFiledSelection(this.possibleFields, item);
    }

    _initExportAction() {
        let exportBtn = document.getElementById('exportExcelContainer').querySelector('.f_export-excel');

        if(exportBtn.exportHandler) {
            exportBtn.removeEventListener('click', exportBtn.exportHandler);
        }

        exportBtn.exportHandler = () => {
            let selectedTemplate = document.getElementById('exportExcelContainer').querySelector('.f_template-container.active');
            if (!selectedTemplate) {
                this._setMessage('.f_export-message', 'Please select template', true);
                return;
            }

            let fields = selectedTemplate.querySelector('.f_template').getAttribute('data-im-template-info');
            fields = JSON.parse(fields);

            if (this.downloadCallback) {
                this.downloadCallback(fields);
                this._closeExcelExportPopUp();
            }
        };

        exportBtn.removeEventListener('click', exportBtn.exportHandler);
        exportBtn.addEventListener('click', exportBtn.exportHandler);
    }


    /**
     *
     * @param evt
     * @private
     */
    _initSelectSavedTemplate(evt) {
        let savedTemplates = document.getElementById('exportExcelContainer').querySelectorAll('.f_existing-templates .f_template-container');
        for (let i = 0; i < savedTemplates.length; i++) {
            savedTemplates[i].classList.remove('active');
        }

        evt.target.closest('.f_template-container').classList.add('active');

        this._checkExportActionButtonStatus();
    }


    /**
     *
     * @private
     */
    _openExcelExportPupUp() {
        let overlay = document.getElementById('exportExcelOverlay');
        let container = document.getElementById('exportExcelContainer');

        this._initNewTemplateFiledSelection(this.possibleFields);

        overlay.classList.add("active");
        container.classList.add("active");
    }


    /**
     *
     * @private
     */
    _initCloseExcelExportPopUp() {
        $("#exportExcelOverlay").unbind("click").click(() => {
            this._closeExcelExportPopUp();
        });


        $('#exportExcelContainer .f_cancel-export-excel').unbind("click").click(() => {
            this._closeExcelExportPopUp();
        });
    }


    /**
     *
     * @private
     */
    _closeExcelExportPopUp() {
        let overlay = document.getElementById('exportExcelOverlay');
        let container = document.getElementById('exportExcelContainer');
        let checkedElementsContainer = document.querySelector('.f_select-fields-info-container');
        if (checkedElementsContainer) {
            checkedElementsContainer.innerHTML = '';
        }

        let nameImport = document.getElementById('templateName');
        if (nameImport) {
            nameImport.value = '';
        }

        overlay.classList.remove("active");
        container.classList.remove("active");
    }


    _initSaveTemplate() {
        $('#exportExcelContainer .f_save-template').unbind('click').click((event) => {
            let nameInput = document.getElementById('templateName');
            let name = nameInput.value;
            let fields = document.querySelectorAll(".f_sortable-field");


            if (!name) {
                this._showError(nameInput, 'Name can not be empty');
                return;
            } else if (!fields.length) {
                return;
            } else if (!this.areFieldsValid(fields)) {
                return;
            }


            let itemId = event.target.attr('data-id');
            fields = this._findFromPossibleValues(fields);


            NGS.action(this.saveAction, {
                itemId: itemId, name: name, fields: fields, item_type: this.itemType
            }, (data) => {
                if (data.message) {
                    this._setMessage('.f_save-template-message',  data.message, true);
                } else {
                    let templatesContainer = document.getElementById('exportExcelContainer').querySelector('.f_existing-templates');
                    let nameInput = document.getElementById('templateName');
                    nameInput.value = "";
                    let titleForTemplatesSelecting = document.getElementById('exportExcelContainer').querySelector('.f_existing-templates-title');
                    titleForTemplatesSelecting.classList.remove('is_hidden');

                    let object = data.item;
                    let contactRow = this.createFieldRow('existingTemplateToCreate', object);

                    if (itemId) {
                        let existingElemet = templatesContainer.querySelector(`[data-im-template-id="${object.id}"]`)
                        existingElemet.closest('.f_template-container').replaceWith(contactRow);
                    } else {
                        templatesContainer.prepend(contactRow);
                    }

                    this._initSavedTemplatesActions(contactRow);
                    this.changeVisibilityExportPart();
                }
            });

        });
    }

    createFieldRow(createdTemplateId, object) {
        let exportTemplate = document.getElementById(createdTemplateId).innerHTML;
        let contactRow = this._renderTemplate(exportTemplate, object);

        contactRow.querySelector('.f_template').setAttribute('data-im-template-info', object.data);
        contactRow.querySelector('.f_template').setAttribute('data-im-template-name', object.name);
        contactRow.querySelector('.f_template').setAttribute('data-im-template-id', object.id);

        return contactRow;
    }

    areFieldsValid(fieldsElements) {
        let areValid = true;

        fieldsElements.forEach(field => {
            let isExluded = field.querySelector('.f_check-select-field').checked;
            if (isExluded) {
            }

            let fieldFormulaName = field.querySelector('.f_column-export-formula');
            let fieldcustomName = field.querySelector('.f_column-export-name');

            if (fieldFormulaName && !fieldFormulaName.value) {
                this._showError(fieldFormulaName, 'Can\'t be empty.');
                areValid = false;
            }

            if (fieldcustomName && !fieldcustomName.value) {
                this._showError(fieldcustomName, 'Can\'t be empty.');
                areValid = false;
            }
        })

        return areValid;
    }


    /**
     *
     * @param ids
     * @returns {[]}
     * @private
     */
    _findFromPossibleValues(fieldsElements) {
        let result = [];
        let selectedFields = [];

        fieldsElements.forEach(field => {
            let isExluded = field.querySelector('.f_check-select-field').checked;
            if (isExluded) {
                return;
            }

            if (field.getAttribute("data-custom-column")) {
                const formula = field.querySelector('.f_column-export-formula').value;
                const displayName = field.querySelector('.f_column-export-name').value;

                selectedFields.push({
                    type: 'custom_column', formula: formula.trim(), displayName: displayName.trim(),
                });
            } else {
                const fieldId = field.getAttribute("data-id");
                const fieldName = field.querySelector('.f_column-system-name').textContent;
                const displayName = field.querySelector('.f_column-export-name').value;

                selectedFields.push({
                    id: fieldId, fieldName: fieldName.trim(), displayName: displayName.trim(),
                });
            }
        })

        for (let i = 0; i < selectedFields.length; i++) {
            if (!selectedFields[i].type || selectedFields[i].type !== 'custom_column') {
                for (let j = 0; j < this.possibleFields.length; j++) {
                    if (this.possibleFields[j].id === selectedFields[i].id) {
                        result.push({
                            fieldName: selectedFields[i].id, displayName: selectedFields[i].displayName
                        });
                    }
                }
            } else {
                result.push(selectedFields[i]);
            }
        }

        return result;
    }

    /**
     *
     * @param message
     * @param error
     * @private
     */
    _showError(element, message) {
        this._hideError(element);
        element.addClass('invalid');
        element.removeClass('ngs');
        element.parentNode.insertAdjacentHTML('beforeend', "<div class='ngs_validate'>" + message + "</div>");

        setTimeout(() => {
            this._hideError(element)
        }, 3000);

    }

    _hideError(element) {
        let errorElement = element.parentNode.getElementsByClassName('ngs_validate');

        if (errorElement.length === 0) {
            return;
        }
        element.removeClass('invalid');
        element.addClass('ngs');
        errorElement[0].remove();
    }

    /**
     *
     * @param message
     * @param error
     * @private
     */
    _setMessage(element, message, error) {
        let messageContainer = document.getElementById('exportExcelContainer').querySelector(element);
        messageContainer.classList.remove('error', 'success');

        messageContainer.innerHTML = message;
        if (error) {
            messageContainer.classList.add('error');
        } else {
            messageContainer.classList.add('success');
        }

        setTimeout(() => {
            this._hideMessage(element)
        }, 3000);

    }

    _hideMessage(element) {
        let messageContainer = document.getElementById('exportExcelContainer').querySelector(element);
        messageContainer.classList.remove('error', 'success');

        messageContainer.innerHTML = "";
    }

    /**
     *
     * @param template
     * @param data
     * @param toObject
     * @returns {null|HTMLCollection|*}
     * @private
     */
    _renderTemplate(template, data, toObject = true) {
        let htmlStr = template.replace(/\$\{\s*([^\s\}]+)\s*\}/g, (_, capturedIdentifier) => data[capturedIdentifier]);
        if (toObject) {
            return NGS.toNode(htmlStr);
        }
        return htmlStr;
    }

    /**
     * Check selected existing templates and change export button functionality depend on selected templates
     * @private
     */
    _checkExportActionButtonStatus() {
        let selectedTemplate = document.getElementById('exportExcelContainer').querySelector('.f_template-container.active');
        let exportButton = document.getElementById('exportExcelContainer').querySelector('.f_export-excel');

        if (selectedTemplate) {
            exportButton.classList.remove('is_disabled');
            return;
        }

        exportButton.classList.add('is_disabled')
    }


    _initCancelAddTemplate() {
        let cancelAddBtn = document.querySelector('#exportExcelContainer .f_cancel-add-template');

        if (!cancelAddBtn.cancelClickHandler) {
            cancelAddBtn.cancelClickHandler = () => {
                this.changeVisibilityExportPart();
            };
        }

        cancelAddBtn.removeEventListener('click', cancelAddBtn.cancelClickHandler);
        cancelAddBtn.addEventListener('click', cancelAddBtn.cancelClickHandler);
    }

    changeVisibilityExportPart() {
        let exportPart = document.getElementById('exportExcelContainer').querySelector('.f_existing-template-content');
        let addEditPart = document.getElementById('exportExcelContainer').querySelector('.f_add-template-content')

        if (!exportPart || !addEditPart) {
            return;
        }

        exportPart.classList.toggle('is_hidden');
        addEditPart.classList.toggle('is_hidden');

    }


    _initCreateTemplate() {
        let createBtn = document.querySelector('#exportExcelContainer .f_create-new-template');

        if (!createBtn.createClickHandler) {
            createBtn.createClickHandler = () => {
                this.changeVisibilityExportPart();

                this._initNewTemplateFiledSelection(this.possibleFields);
            };
        }

        createBtn.removeEventListener('click', createBtn.createClickHandler);
        createBtn.addEventListener('click', createBtn.createClickHandler);
    }
};
