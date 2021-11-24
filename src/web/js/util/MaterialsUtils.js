import MaterialDatetimePicker from '../lib/material-datetime-picker.min.js';
import M from '../lib/materialize.min.js';

let MaterialsUtils = {
    modalInstances: [],
    initMaterialElements: function (container) {
        M.Toast.dismissAll();
        if (M.updateTextFields) {
            M.updateTextFields();
        }
        let mTextAreas = document.querySelectorAll(".materialize-textarea");
        if (mTextAreas.length > 0) {
            M.textareaAutoResize(mTextAreas);
        }

        //M.FormSelect.init(document.querySelectorAll('select:not(.ngs-choice)'), {gago: null});
        this.setTimeToPickers(container);
        this.initVerticalTabs(container);
    },

    initModal(elemId) {
        let addRuleModal = document.getElementById(elemId);
        M.Modal.init(addRuleModal);
    },


    initModalByElement(addRuleModal) {
        M.Modal.init(addRuleModal);
    },


    openModal(elemId) {
        let addRuleModal = document.getElementById(elemId);
        let modal = M.Modal.getInstance(addRuleModal, {dismissible: false});
        modal.options.dismissible = false;
        modal.open();
    },

    openModalElement(addRuleModal) {
        let modal = M.Modal.getInstance(addRuleModal, {dismissible: false});
        modal.options.dismissible = false;
        modal.open();

        if(addRuleModal.querySelector('.f_modal-content')) {
            addRuleModal.addEventListener('click', (evt) => {
                if(evt.target.closest('.f_close-popup-button') || !evt.target.closest('.f_modal-content')) {
                    this.closeModalByElements([addRuleModal])
                }
            });
        }
    },


    closeModal(elemId) {
        let addRuleModal = document.getElementById(elemId);
        let modal = M.Modal.getInstance(addRuleModal, {dismissible: false});
        modal.options.dismissible = false;
        modal.close();
    },


    closeModalByElements(elements) {
        for(let i=0; i<elements.length; i++) {
            let modal = M.Modal.getInstance(elements[i], {dismissible: false});
            if(modal) {
                modal.options.dismissible = false;
                modal.close();
            }
        }

    },


    setTimeToPickers: function (container) {
        let datePickerElems = document.querySelectorAll('#' + container + ' .datepicker');
        if (datePickerElems.length > 0) {
            let datePickerInstances = M.Datepicker.init(datePickerElems, {
                selectMonths: true, // Creates a dropdown to control month
                selectYears: 220, // Creates a dropdown of 15 years to control year,
                yearRange: 60,
                clear: 'Clear',
                close: 'Ok',
                format: 'd mmmm yyyy',
                setDefaultDate: true,
                closeOnSelect: false // Close upon selecting a date,
            });
        }

        let timepickerElems = document.querySelectorAll('#' + container + ' .timepicker');
        if (timepickerElems.length > 0) {
            let timepickerInstances = M.Timepicker.init(timepickerElems, {
                default: 'now',
                twelveHour: false, // change to 12 hour AM/PM clock from 24 hour
                donetext: 'OK',
                format: "HH:ii:SS",
                autoClose: false,
                vibrate: true
            });
        }
        var datatimepickerElems = document.querySelectorAll('#' + container + ' .datetimepicker');
        if (datatimepickerElems.length > 0) {
            const picker = new MaterialDatetimePicker()
                .on('open', function () {
                    $('body').addClass("no-scroll");
                })
                .on('close', function () {
                    $('body').removeClass("no-scroll");
                })
                .on('submit', function (val) {
                    this.el.value = val.format('D MMMM YYYY HH:mm:SS');
                });

            datatimepickerElems.click(function (evt) {
                picker.el = evt.currentTarget;
                picker.open() || picker.set(moment().startOf('day'));
            });
        }
    },

    initCmsModal: function(modalLevel) {
        let modalId = this.getModalId(modalLevel);
        let mainModal = document.getElementById("modal");
        let modalElem = document.getElementById(modalId);
        if (!modalElem) {
            modalElem = mainModal.cloneNode();
            modalElem.innerHtml = "";
            modalElem.setAttribute('id', modalId);
            this.insertAfter(mainModal, modalElem);
            M.Modal.init(modalElem);
        }
    },

    createCmsModal: function (title, modalLevel) {
        let modalId = this.getModalId(modalLevel);
        let customDismissible = function (evt) {
            if (evt.key === "Escape") {
                let modal = this.getActiveModalInstance();
                modal.close();
            }
            if (evt.key === "Enter") {
                ///this.modalInstances[modalLevel].close();
            }
        }.bind(this);
        let modalElem = document.getElementById(modalId);
        this.modalInstances[modalLevel] = M.Modal.getInstance(modalElem, {dismissible: false});
        this.modalInstances[modalLevel].options.dismissible = false;
        this.modalInstances[modalLevel].open();
        document.addEventListener('keyup', customDismissible);
        this.modalInstances[modalLevel].options.onCloseEnd = function () {
            document.removeEventListener('keyup', customDismissible);
            this.modalInstances.pop(); //removes last modal instance
            M.Toast.dismissAll();
        }.bind(this);
        return this.modalInstances[modalId];
    },

    getModalId(modalLevel) {
        if (!modalLevel) {
            modalLevel = 1;
        }
        let modalPostFix = modalLevel === 1 ? "" : "_" + modalLevel;
        return "modal" + modalPostFix;
    },

    insertAfter(referenceNode, newNode) {
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
    },

    initVerticalTabs: function (container) {
        if (!document.querySelector('#' + container + ' .f_cms_vertical-tabs')) {
            return;
        }
        let instance = M.Tabs.init(document.querySelectorAll('#' + container + ' .f_cms_vertical-tabs'), {
            onShow: function () {
                let containerElement = this.$activeTabLink[0].closest('.f_cms_vertical-tabs');
                let activeElement = this.$activeTabLink[0];
                let indicator = containerElement.querySelector('.indicator');
                indicator.style.top = (activeElement.getBoundingClientRect().top - containerElement.getBoundingClientRect().top) + 'px';
            }
        });
    },
    showVerticalTabsError: function (tabsArr, tabsElement) {
        let instance = M.Tabs.getInstance(tabsElement);
        let currentIndex = instance.index;
        if (tabsArr[currentIndex]) {
            return;
        }
        let tabsIds = Object.keys(tabsArr);
        instance.select(tabsArr[tabsIds[0]]);
    },
    getActiveModalInstance: function () {
        if(this.modalInstances.length === 0) {
            return null;
        }
        return this.modalInstances[this.modalInstances.length - 1];
    },
    showErrorDialog: function (msg) {
        M.toast({html: msg, displayLength: 2500, classes: 'ngs-error-toast'})
    },

    showSuccessDialog: function (msg) {
        M.toast({html: msg, displayLength: 2500, classes: 'ngs-success-toast'})
    },
    confirmDialog: function (title = '') {
        if (title === '') {
            title = 'Are you sure?';
        }
        return new Promise(function (resolve, reject) {

            let cancel = function () {
                M.Toast.dismissAll();
                reject();
            };
            let okHandler = function () {
                resolve();
                M.Toast.dismissAll();
            };
            let toastContent = `<div><span>${title}</span>
                                    <div class="toast-buttons">
                                        <button class="btn-flat toast-action danger f_btn" data-im-type="yes">Yes</button>
                                        <button class="btn-flat toast-action cancel f_btn" data-im-type="no">No</button>
                                    </div>
                                </div>`;
            M.toast({
                html: toastContent,
                displayLength: 10000,
                classes: 'cms-dialog'
            }).el.querySelectorAll(".f_btn").click(function () {
                console.log(this);
                if (this.attr("data-im-type") === 'yes') {
                    okHandler();
                    return;
                }
                cancel();
            });
        });


    }
};
export default MaterialsUtils;