import AbstractAction from '../../AbstractAction.js';
import DialogUtility from "../util/DialogUtility.js";
import StringUtility from "../util/StringUtility.js";

export default class AbstractAddUpdateAction extends AbstractAction {

  constructor() {
    super();
  }

  getParamsIn() {
    return 'formData';
  }

  getMethod() {
    return "POST";
  }

  onError(params) {
    this.initErrorMessageShowing(params);
  }

  beforeAction() {
  }

  afterAction(transport) {
    this.action = this.args().actionType ? this.args().actionType : null;
    this.initSuccessMessageShowing();
    if(this.args().afterBulkDeleteActionLoad){
      NGS.load(this.args().afterBulkDeleteActionLoad, this.args().afterActionParams);
    }
  }

  initSuccessMessageShowing() {
    if(this.args().warningMessage) {
      DialogUtility.showWarningDialog('Warning', this.args().warningMessage, {actionResultShow: true, 'timeout' : 3000});
      return;
    }
    let successText = '';
    if(this.action) {
      successText = this.getSuccessMessage(this.args().tableName, this.action);
    }

    DialogUtility.showSuccessDialog('Success', successText, {actionResultShow: true, 'timeout' : 1500});
  }


  /**
   * get success message for action
   * @param tableName
   * @param actionType
   * @returns {string}
   */
  getSuccessMessage(tableName, actionType) {
    tableName = StringUtility.removeCustomTextFromString(tableName, 'ngs_', true);
    tableName = StringUtility.toReadableText(tableName, true, false);
    tableName = StringUtility.pluralToSingular(tableName);

    return tableName + ' successfully ' + actionType + "d"
  }


  initErrorMessageShowing(params) {
    if(!(params.params && params.params.overrideIssue)) {
      DialogUtility.showErrorDialog('Error', params.msg, {actionResultShow: true});
    }

  }

}
