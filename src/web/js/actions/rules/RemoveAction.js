import AbstractAction from '../../../AbstractAction.js';

export default class SaveAction extends AbstractAction {

  constructor() {
    super();
  }


  getParamsIn() {
    return 'body';
  }


}