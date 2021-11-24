import BaseValidator from "./BaseValidator.js";

export default class EmailValidator extends BaseValidator {

    isRequest() {
        return false;
    }

    validate(value, validationInfo, fieldName) {

        if(!this.validateEmail(value)) {
            return "email is not valid";
        }
        return "";
    }

    validateEmail(email) {
        const regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return regex.test(email);
    }

};