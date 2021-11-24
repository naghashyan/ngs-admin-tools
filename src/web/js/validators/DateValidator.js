import BaseValidator from "./BaseValidator.js";

export default class DateValidator extends BaseValidator{

    isRequest() {
        return false;
    }

    validate(value, validationInfo, fieldName) {


        if(this._isValidDate(value)){
            return "";
        }

        return "the date is incorrect";
    }

    //todo: should be modified
    _isValidDate(dateString) {
        let regEx = /^\d{4}-\d{2}-\d{2}$/;
        if(!dateString.match(regEx)) return false;  // Invalid format
        let d = new Date(dateString);
        let dNum = d.getTime();
        if(!dNum && dNum !== 0) return false; // NaN value, Invalid date
        return d.toISOString().slice(0,10) === dateString;
    }
};