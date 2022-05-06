import Choices from "../lib/choices.min.js";
import sortable from "../lib/html5sortable.min.js";

export default class ExportTemplatesManager {


    constructor(possibleFields, itemType, downloadCallback) {
        NGS.action("admin.actions.exportTemplates.get", {item_type: itemType}, (data) => {
            this.downloadCallback = downloadCallback;
            this.itemType = itemType;
            this.possibleFields = possibleFields;
            this._initSavedTemplates(data.items);
            this._openExcelExportPupUp();
            this._initSavedTemplatesActions();
            this._initSaveTemplate();
            this._initCloseExcelExportPopUp();
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

        let exportTemplate = document.getElementById('existingTemplateToCreate').innerHTML;
        for (let i = 0; i < savedTemplates.length; i++) {
            let object = savedTemplates[i];
            let contactRow = this._renderTemplate(exportTemplate, object);
            contactRow.querySelector('.f_template').setAttribute('data-im-template-info', object.data);
            templatesContainer.appendChild(contactRow);
        }
    }


    /**
     *
     * @param possibleFields
     * @private
     */
    _initNewTemplateFiledSelection(possibleFields) {
        let fieldsContainer = document.querySelector('.f_select-fields-info-container');
        let selectRowTemplate = document.getElementById('selectFieldColumnToCreate').innerHTML;

        for (let i = 0; i < possibleFields.length; i++) {
            fieldsContainer.appendChild(this._renderTemplate(selectRowTemplate, {
                id: possibleFields[i]['id'], value: possibleFields[i]['value']
            }));
        }

        this._initSortableFields();

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

            deleteTemplateBtn.addEventListener('click', this._initDeleteSavedTemplate.bind(this));
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

        NGS.action("admin.actions.exportTemplates.delete", {template_id: id}, (data) => {
            if (data.error) {
                this._setMessage("delete saved template failed", true);
            } else {
                savedTemplate.remove();

                let templatesTitle = document.getElementById('exportExcelContainer').querySelector('.f_existing-templates-title');
                let allTemplatesContainer = document.querySelector('.f_existing-templates');

                if (!allTemplatesContainer.querySelector('.f_template-container')) {
                    templatesTitle.classList.add('is_hidden');
                }

            }
        });
    }


    _initExportAction() {
        $("#exportExcelContainer .f_export-excel").unbind('click').click(() => {
            let selectedTemplate = document.getElementById('exportExcelContainer').querySelector('.f_template-container.active');
            if (!selectedTemplate) {
                this._setMessage('please select template', true);
                return;
            }

            let fields = selectedTemplate.querySelector('.f_template').getAttribute('data-im-template-info');
            fields = JSON.parse(fields);

            if (this.downloadCallback) {
                this.downloadCallback(fields);
                this._closeExcelExportPopUp();
            }
        });
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
        $('#exportExcelContainer .f_save-template').unbind('click').click((evt) => {
            let nameInput = document.getElementById('templateName');
            let name = nameInput.value;
            let fields = document.querySelectorAll(".f_sortable-field");

            if (!name) {
                this._setMessage('name can not be empty', true);
            } else if (!fields.length) {
                this._setMessage('please select fields', true);
            } else {
                fields = this._findFromPossibleValues(fields);

                NGS.action("admin.actions.exportTemplates.save", {
                    name: name, fields: fields, item_type: this.itemType
                }, (data) => {
                    if (data.error) {
                        this._setMessage(data.message, true);
                    } else {
                        let templatesContainer = document.getElementById('exportExcelContainer').querySelector('.f_existing-templates');

                        let titleForTemplatesSelecting = document.getElementById('exportExcelContainer').querySelector('.f_existing-templates-title');
                        titleForTemplatesSelecting.classList.remove('is_hidden');

                        let exportTemplate = document.getElementById('existingTemplateToCreate').innerHTML;
                        let object = data.item;
                        let contactRow = this._renderTemplate(exportTemplate, object);
                        contactRow.querySelector('.f_template').setAttribute('data-im-template-info', object.data);
                        templatesContainer.prepend(contactRow);
                        this._initSavedTemplatesActions(contactRow);
                        this._setMessage("template saved", true);
                    }
                });
            }
        });
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

            const fieldId = field.getAttribute("data-id");
            const fieldName = field.querySelector('.f_column-system-name').textContent;
            const displayName = field.querySelector('.f_column-export-name').value;

            selectedFields.push({
                id: fieldId, fieldName: fieldName.trim(), displayName: displayName.trim(),
            })
        })

        selectedFields.forEach((selectedField, index) => {
            this.possibleFields.forEach((posibleField) => {
                if (posibleField.id === selectedField.id) {
                    result.push({
                        fieldName: selectedField.id, displayName: selectedField.displayName
                    });
                }
            })
        })


        return result;
    }

    /**
     *
     * @param message
     * @param error
     * @private
     */
    _setMessage(message, error) {
        let messageContainer = document.getElementById('exportExcelContainer').querySelector('.f_export_message');
        messageContainer.classList.remove('error', 'success');

        messageContainer.innerHTML = message;
        if (error) {
            messageContainer.classList.add('error');
        } else {
            messageContainer.classList.add('success');
        }

        setTimeout(() => {
            this._hideMessage()
        }, 3000);

    }

    _hideMessage() {
        let messageContainer = document.getElementById('exportExcelContainer').querySelector('.f_export_message');
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
};
