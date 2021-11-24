import BaseValidator from "./BaseValidator.js";

export default class PhoneValidator extends BaseValidator {

    isRequest() {
        return true;
    }

};