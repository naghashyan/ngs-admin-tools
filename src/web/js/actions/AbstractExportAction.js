import AbstractAddUpdateAction from './AbstractAddUpdateAction.js';

export default class AbstractExportAction extends AbstractAddUpdateAction {

  getParamsIn() {
    return 'body';
  }


}