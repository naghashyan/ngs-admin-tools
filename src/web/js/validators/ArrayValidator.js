import BaseValidator from "./BaseValidator.js";

export default class ArrayValidator extends BaseValidator{

    isRequest() {
        return false;
    }

    validate(value, validationInfo, fieldName) {
        //todo: how to validate this value? the value is not an array here, because element.value is the first selected option of element;
        return "";
    }

};