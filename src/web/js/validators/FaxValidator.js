import BaseValidator from "./BaseValidator.js";

export default class FaxValidator extends BaseValidator {

    isRequest() {
        return true;
    }

};