import BaseValidator from "./BaseValidator.js";

export default class RegexValidator extends BaseValidator{

    isRequest() {
        return true;
    }

};