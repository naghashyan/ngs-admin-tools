import BaseValidator from "./BaseValidator.js";

export default class TextValidator extends BaseValidator {

    isRequest() {
        return false;
    }

    validate(value, validationInfo, fieldName) {

        if(validationInfo.hasOwnProperty('string_min_length')) {
            if(validationInfo.string_min_length > value.length) {
                return "field <b class='f_fieldName'>" + fieldName + " </b> should be greater than " + (validationInfo.string_min_length - 1) +  ' symbols';
            }
        }
        if(validationInfo.hasOwnProperty('string_max_length')) {
            if(validationInfo.string_max_length < value.length) {
                return "field <b class='f_fieldName'>" + fieldName + " </b> should be less than " + (validationInfo.string_max_length + 1) + ' symbols';
            }
        }
        if(validationInfo.hasOwnProperty('allowed_chars') && validationInfo.allowed_chars.length) {
            for(let i = 0; i < value.length; i++) {
                if(validationInfo.allowed_chars.indexOf(value[i]) === -1) {
                    return "field <b class='f_fieldName'>" + fieldName + "</b> can contain only chars " + validationInfo.allowed_chars.join(', ');
                }
            }
        }


        return "";
    }
};