import AbstractAction from '../../../AbstractAction.js';

export default class ListAction extends AbstractAction {

  constructor() {
    super();
  }

  getParamsIn() {
    return 'body';
  }

  isWithoutLoader() {
    return true;
  }
}