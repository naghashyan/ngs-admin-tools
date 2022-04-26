import AbstractAction from "../../AbstractAction.js";
import DialogUtility from '../../../ngs/AdminTools/util/DialogUtility.js';
import MaterialsUtils from "../util/MaterialsUtils.js";

export default class AbstractDeleteAction extends AbstractAction {

  constructor() {
    super();
  }

  getParamsIn() {
    return 'body';
  }

  getMethod() {
    return "POST";
  }

  onError(params) {
    this.initErrorMessageShowing(params);
  }


  afterAction() {
    if(MaterialsUtils.getActiveModalInstance()){
      MaterialsUtils.getActiveModalInstance().close();
    }
    NGS.load(this.args().afterActionLoad, this.args().afterActionParams);
  }


  initErrorMessageShowing(params) {
    DialogUtility.showAlertDialog("Error", params.msg, {'oneButton' : true});
  }
}