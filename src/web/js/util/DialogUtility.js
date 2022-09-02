/**
 * DialogUtility helper util
 * for showing custom notifications
 *
 * @author Levon Naghashyan
 * @site https://naghashyan.com
 * @mail levon@naghashyan.com
 * @year 2015-2019
 */



let DialogUtility = {

    openStatus: false,
    dialogTypeClass: "",
    dialogLayoutClass: "",
    timeoutId: -1,

    initialize: function () {
        this.toastElement = $('#toastElement');

        this.toastHeaderBox = this.toastElement.find('.f_toast-header-box');
        this.toastHeaderBoxTitlePart = this.toastHeaderBox.find('.f_toast-header-box-title-part');

        this.toastContentBox = this.toastElement.find(".f_toast-content-box");
        this.errorIcon = this.toastContentBox.find('.f_error_icon');
        this.successIcon = this.toastContentBox.find('.f_success_icon');
        this.toastContentBoxTitlePart = this.toastContentBox.find('.f_toast-content-box-title-part');
        this.toastContentBoxMainPart = this.toastContentBox.find(".f_toast-content-box-main-part");

        this.toastFooterBox = this.toastElement.find('.f_toast-footer-box');
        this.okBtn = this.toastFooterBox.find('.f_ok_btn');
        this.cancelBtn = this.toastFooterBox.find('.f_cancel_btn');

        this.confirmationMessageContainer = this.toastElement.find('.f_confirmation-message-container');

    },
    /**
     * Default options
     * it can be overrided in each public method
     * of this object
     */
    options: {
        openAnimation: "animationOn fadeIn",// for more animation options see on nimate.css or http://daneden.github.io/animate.css/
        closeAnimation: "fadeOut",// for more animation options see on nimate.css or http://daneden.github.io/animate.css/
        layout: "center",// top, topLeft, topCenter, topRight, centerLeft, center, centerRight, bottomLeft, bottomCenter, bottomRight, bottom
        timeout: 2000,
        overlay: false,
        type: "success",//success, error, warning, confirm, alert, custom
        customTpl: "",
        okBtnText: "Ok",
        cancelBtnText: "Cancel",
        closeAfterOk: true,
        onOpen: NGS.emptyFunction,
        shouldReverseButton: false,
        okBtnCustomClass: null,
        cancelBtnCustomClass: null,
        checkValidity: false,
    },
    /**
     * Method for showing Info notification
     *
     * @param  title:String
     * @param  txt:String
     * @param  options:Object
     *
     */
    showInfoDialog: function (title, txt, options) {
        return this._showDialog(title, txt, "info", options);
    },
    /**
     * Method for showing Alert notification
     *
     * @param  title:String
     * @param  txt:String
     * @param  options:Object
     *
     */
    showAlertDialog: function (title, txt, options) {
        return this._showDialog(title, txt, "alert", options);
    },
    /**
     * Method for showing Error notification
     *
     * @param  title:String
     * @param  txt:String
     * @param  options:Object
     *
     */
    showErrorDialog: function (title, txt, options) {
        return this._showDialog(title, txt, "error", options);
    },
    /**
     * Method for showing success notification
     *
     * @param  title:String
     * @param  txt:String
     * @param  options:Object
     *
     */
    showSuccessDialog: function (title, txt, options) {
        return this._showDialog(title, txt, "success", options);
    },
    /**
     * Method for showing warning notification
     *
     * @param  title:String
     * @param  txt:String
     * @param  options:Object
     *
     */
    showWarningDialog: function (title, txt, options) {
        return this._showDialog(title, txt, "warning", options);
    },
    /**
     * Method for showing confirm dialog
     *
     * @param  title:String
     * @param  txt:String
     * @param  options:Object
     * @param  confirmationMessage:String
     * @param  errorReason:String
     *
     */
    showConfirmDialog: function (title, txt, options, confirmationMessage = null, errorReason = null) {
        if (!options) {
            options = {overlay: true};
        }
        return this._showDialog(title, txt, "confirm", options, confirmationMessage, errorReason);
    },
    /**
     * Method for showing custom dialog
     *
     * @param  title:String
     * @param  txt:String
     * @param  options:Object
     *
     */
    showCustomDialog: function (title, txt, options) {
        if (!options) {
            options = {overlay: true};
        }

        return this._showDialog(title, txt, "custom", options);
    },
    /**
     * Method for showing Noty notifications
     *
     * @param  title:String
     * @param  txt:String
     * @param  type:String
     * @param  options:Object
     * @param  confirmationMessage:String
     * @param  errorReason:String
     *
     */
    _showDialog: function (title, txt, type, options, confirmationMessage = null, errorReason = null) {
        document.getElementById('main-overlay-for-all-purposes')?.classList.add('is_hidden');

        this.shouldCloseItself = type !== 'custom';

        if (options && options.actionResultShow) {
            this.popupReason = 'actionResult';
            if (type === 'error') {
                this.shouldCloseItself = false;
            }
        } else {
            this.popupReason = 'dialog';
        }

        if (this.openStatus) {
            this.closeDialog();
        }
        if (this.timeoutId >= 0) {
            window.clearTimeout(this.timeoutId);
        }

        this.openStatus = true;
        this._options = null;
        const _options = Object.assign({}, this.options);
        this._options = Object.assign(_options, options);
        this.toastElement.removeClass("im-dialog-overlay");
        if (this._options.overlay) {
            this.toastElement.addClass("im-dialog-overlay");
        }
        this.dialogTypeClass = "im-dialog-" + type;
        this.dialogLayoutClass = "im-dialog-" + this._options.layout;
        this.toastElement.addClass(this.dialogTypeClass);
        this.toastElement.addClass(this.dialogLayoutClass);

        if (options && options.actionType) {
            let action = " " + options.actionType;
            txt += action;
        }

        this.toastContentBoxMainPart.html(txt);

        if (this._options.shouldReverseButton) {
            this.modifyButtonPositions();
        }

        if(this._options.okBtnCustomClass){
            this.modifyButtonColors();
        }

        this.toastElement.removeClass(this._options.closeAnimation);
        this.toastElement.addClass(this._options.openAnimation);

        if (this.popupReason === 'actionResult') {
            this.modifyPopupForActionResultShow(type);
        } else {
            this.modifyPopupForDialogShow(title);
        }

        if (confirmationMessage) {
            this.confirmationMessageContainer.find('.f_confirmation-message').val("").removeClass("error");
            this.confirmationMessageContainer.find('.f_confirmation-required-text').text(confirmationMessage);
            this.confirmationMessageContainer.show();
        } else {
            this.confirmationMessageContainer.hide();
        }
        if (errorReason) {
            this.confirmationMessageContainer.find('.f_error-reason').text(errorReason);
        } else {
            this.confirmationMessageContainer.find('.f_error-reason').text('');
        }

        return new Promise(function (resolve, reject) {
            document.getElementById("dialogOverlay").onclick = function () {
                console.log("click");
                if (this.timeoutId >= 0) {
                    window.clearTimeout(this.timeoutId);
                }
                this.closeDialog();
                reject();
            }.bind(this);

            if (this.shouldCloseItself && (type !== "alert" && type !== "confirm")) {
                this.timeoutId = setTimeout(function () {
                    this.closeDialog();
                }.bind(this), this._options.timeout);
            }
            this.toastElement.find(".f_dialog_close").click(function () {
                document.getElementById("dialogOverlay").onclick = function () {
                    this.closeDialog();
                }.bind(this);
                this.closeDialog();
                reject("");
            }.bind(this));
            this.okBtn.unbind("click");
            if (confirmationMessage) {
                this.okBtn.click(function () {
                    if (this.validateConfirmationMessage(confirmationMessage)) {
                        this.closeDialog();
                        resolve(this.confirmationMessageContainer.find('.f_confirmation-message').val().trim());
                    }
                }.bind(this));
            } else if (this._options.checkValidity) {
                this.okBtn.click(function () {
                    if (this.checkFiledsValidation()) {
                        this.closeDialog();
                        resolve(this.toastContentBoxMainPart.find('.f_validate').val().trim());
                    } else {
                        reject("")
                    }
                }.bind(this));
            } else {
                this.okBtn.click(function () {
                    this.closeDialog();
                    resolve(true);
                }.bind(this));
            }

        }.bind(this));

    },

    modifyButtonPositions: function () {
        let cancelBtn = this.toastFooterBox.find('.f_cancel_btn');
        let okBtn = this.toastFooterBox.find('.f_ok_btn');

        okBtn.before(cancelBtn);
    },


    modifyButtonColors:function (){
        let cancelBtn = this.toastFooterBox.find('.f_cancel_btn');
        let okBtn = this.toastFooterBox.find('.f_ok_btn');

        if(this._options.okBtnCustomClass){
            okBtn.addClass(this._options.okBtnCustomClass);
            okBtn.removeClass("danger");
        }
    },

    modifyPopupForActionResultShow: function (type) {
        this.toastContentBoxTitlePart.html(type);

        this.hideElement([this.toastHeaderBox, this.cancelBtn]);

        if (type === 'success') {
            this.hideElement([this.okBtn, this.errorIcon]);
            this.showElement([this.successIcon]);

        } else if (type === 'error') {
            this.showElement([this.errorIcon, this.okBtn]);
            this.hideElement([this.successIcon]);

            this.okBtn.html('Try again');
        }

    },

    modifyPopupForDialogShow: function (title) {
        this.hideElement([this.successIcon, this.errorIcon]);
        this.showElement([this.toastHeaderBox, this.cancelBtn, this.okBtn]);

        this.toastContentBoxTitlePart.html('');

        if (this._options.noButton) {
            this.hideElement([this.cancelBtn, this.okBtn]);
        } else if (this._options.oneButton) {
            this.hideElement([this.cancelBtn]);
            this.okBtn.html(this._options.okBtnText);
        } else {
            this.showElement([this.cancelBtn]);
            this.okBtn.html(this._options.okBtnText);
            this.cancelBtn.html(this._options.cancelBtnText);
        }
        this.toastHeaderBoxTitlePart.html(title);
    },

    validateConfirmationMessage: function (message) {
        if (this.confirmationMessageContainer.find('.f_confirmation-message').val().trim() !== message.trim()) {
            this.confirmationMessageContainer.find('.f_confirmation-message').addClass("error");
            return false;
        }
        this.confirmationMessageContainer.find('.f_confirmation-message').removeClass("error");
        return true;
    },


    checkFiledsValidation: function () {
        let fieldsToValidate = this.toastContentBoxMainPart.find('.f_validate').val().trim();
        return fieldsToValidate.length
    },

    openCustomModal: function (options) {
        if (this.openStatus) {
            this.closeDialog(true);
            if (this.timeoutId >= 0) {
                window.clearTimeout(this.timeoutId);
            }
        }
        this.openStatus = true;
        const _options = Object.assign({}, this.options);
        this._options = Object.assign(_options, options);
        this.toastElement.removeClass("im-dialog-overlay");
        if (this._options.overlay) {
            this.toastElement.addClass("im-dialog-overlay");
        }
        this.toastElement.addClass("im-dialog-custom");
        this.dialogLayoutClass = "im-dialog-" + this._options.layout;
        this.toastElement.addClass(this.dialogLayoutClass);
        this.toastElement.removeClass(this._options.closeAnimation);
        this.toastElement.addClass(this._options.openAnimation);
        this.toastHeaderBoxTitlePart.html(this._options.title ? this._options.title : "");
        this.toastContentBoxMainPart.html(this._options.customTpl ? this._options.customTpl : "");
        this.toastElement.click(function (evt) {
            evt.stopPropagation();
        });
        return new Promise(function (resolve, reject) {
            document.getElementById("dialogOverlay").onclick = function (evt) {
                evt.stopPropagation();
                resolve(null);
                this.closeDialog();
            }.bind(this);
            this.okBtn.unbind("click");
            const confirmForm = $("#confirmData");
            confirmForm.on("submit", function () {
                this.okBtn.trigger("click");
                return false;
            }.bind(this));
            this.okBtn.click(function () {
                const confirmForm = $("#confirmData");
                if (confirmForm && confirmForm.length) {
                    resolve(confirmForm.serializeObject());
                } else {
                    resolve(null);
                }
                if (this._options.closeAfterOk) {
                    this.closeDialog();
                }
            }.bind(this));
            this.toastElement.find(".f_dialog_close").click(function () {
                resolve(null);
                this.closeDialog();
            }.bind(this));
        }.bind(this));
    },

    closeDialog: function (custom, closeTime) {
        if (!this.openStatus) {
            return;
        }
        this.toastElement.removeClass(this._options.openAnimation);
        this.toastElement.addClass(this._options.closeAnimation);
        this.openStatus = false;
        this.toastElement.removeClass(this.dialogLayoutClass);
        this.toastElement.removeClass(this.dialogTypeClass);
        this.toastElement.removeClass("im-dialog-overlay");
        this.toastElement.removeClass("im-dialog-custom");
        this.openStatus = false;
        if (!custom) {
            $("#dialogContainer").html("");
        }


    },

    hideElement: function (elementsArray) {
        elementsArray.forEach((element) => {
            element.addClass('hide');
        })
    },

    showElement: function (elementsArray) {
        elementsArray.forEach((element) => {
            element.removeClass('hide');
        })
    },


};
document.onreadystatechange = () => {
    if (document.readyState === 'complete') {
        DialogUtility.initialize();
    }
};
if (document.readyState === 'complete') {
    DialogUtility.initialize();
}

export default DialogUtility;
