import AbstractLoad from '../../../AbstractLoad.js';

export default class RulesLoad extends AbstractLoad {

    constructor() {
        super();
    }


    getMethod() {
        return "POST";
    }

    initBackBtn() {

    }

    getContainer() {
        return "#" + this.args().parentContainer + " .f_ajax-pagination";
    }

    isWithoutLoader() {
        return true;
    }
}