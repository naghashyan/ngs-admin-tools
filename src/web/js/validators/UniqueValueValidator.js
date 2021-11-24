import BaseValidator from "./BaseValidator.js";

export default class UniqueValueValidator extends BaseValidator {

    isRequest() {
        return true;
    }

};