import BaseValidator from "./BaseValidator.js";

export default class DateIntervalValidator extends BaseValidator{

    isRequest() {
        return false;
    }

    validate(value, validationInfo, fieldName) {
        this._forceCorrectInputting(value, validationInfo, fieldName);

        let startDate = '';
        let endDate = '';

        if(this._isStartFieldValidating(fieldName)) {
            startDate = value[fieldName];
            endDate = value[validationInfo.pair_field_name];
        }else {
            startDate = value[validationInfo.pair_field_name];
            endDate = value[fieldName];
        }

        let result = {
            [fieldName]: '',
            [validationInfo.pair_field_name] : ''
        };

        if(Date.parse(startDate) > Date.parse(endDate)) {

            if(this._isStartFieldValidating(fieldName)) {
                result = {
                    [fieldName]: 'field <b class="f_fieldName">' + fieldName + '</b> should be sooner than <b>' + endDate + '</b>',
                    [validationInfo.pair_field_name]: 'field <b class="f_fieldName">' + validationInfo.pair_field_name + '</b> should be later than <b>' + startDate + '</b>'
                }
            }else {
                result = {
                    [fieldName]: 'field <b class="f_fieldName">' + fieldName + '</b> should be later than <b>' + startDate + '</b>',
                    [validationInfo.pair_field_name]: 'field <b class="f_fieldName">' + validationInfo.pair_field_name + '</b> should be sooner than <b>' + endDate + '</b>'
                }
            }
        }

        return result;
    }



    /**
     * on a field date choosing in second field the incorrect dates should become disabled
     * @param value
     * @param validationInfo
     * @param fieldName
     * @private
     */
    _forceCorrectInputting(value, validationInfo, fieldName) {
        let pairField = document.querySelector('input[name="' + validationInfo.pair_field_name + '"]');
        let startOrEnd = this._isStartFieldValidating(fieldName) ? 'minDate' : 'maxDate';
        flatpickr(pairField,{
            [startOrEnd]: value[fieldName]
        });
    }

    /**
     * need to know is this the start date or the end date.
     * @param fieldName
     * @returns {boolean}
     */
    _isStartFieldValidating(fieldName) {

        //todo: maybe its better to have a key in dto like 'start => true'
        return fieldName.indexOf('start') !== -1;
    }

};