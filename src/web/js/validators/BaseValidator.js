export default class BaseValidator {
    constructor() {

    }

    isRequest() {
        return false;
    }

    baseValidate(value, validationInfo, fieldName) {

        if(!value || value === ''.trim()) {
            if(validationInfo.hasOwnProperty('is_required')) {
                if(validationInfo.is_required) {
                    throw new Error("field <b class='f_fieldName'>" +  fieldName + "</b> is required");
                }
            }
            return "";
        }
        return this.validate(value, validationInfo, fieldName);
    }

    validate(value, validationInfo) {
        return "";
    }
};
