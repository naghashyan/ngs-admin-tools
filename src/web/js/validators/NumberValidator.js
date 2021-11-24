import BaseValidator from "./BaseValidator.js";

export default class NumberValidator extends BaseValidator {

    isRequest() {
        return false;
    }

    validate(value, validationInfo, fieldName) {

        if(validationInfo.hasOwnProperty('range_start')) {
            if(validationInfo.range_start > value) {
                return "field <b class='f_fieldName'> " + fieldName +  "</b> should be greater than " + (validationInfo.range_start - 1);
            }
        }

        if(validationInfo.hasOwnProperty('range_end')) {
            if(validationInfo.range_end < value) {
                return "field <b class='f_fieldName'> " + fieldName + "</b> should be smaller than " + (validationInfo.range_end + 1);
            }
        }

        if(value === '' || !isNaN(parseFloat(value)) && isFinite(value)){
            return "";
        }

        return "field <b class='f_fieldName'> " + fieldName + "</b> should be numeric";
    }
};