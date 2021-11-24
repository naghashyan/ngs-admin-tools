import AbstractAddUpdateAction from '../AbstractAddUpdateAction.js';

export default class SaveAction extends AbstractAddUpdateAction {

  constructor() {
    super();
  }


  getParamsIn() {
    return 'body';
  }


}