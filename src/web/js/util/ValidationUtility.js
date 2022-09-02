/**
 * ValidationUtility helper util
 * validates fields by validator classes
 *
 * @author Mikael Mkrtchyan
 * @site https://naghashyan.com
 * @mail mikael.mkrtchyan@naghashyan.com
 * @year 2020
 */

import TextValidator from "../validators/TextValidator.js";
import NumberValidator from "../validators/NumberValidator.js";
import DateValidator from "../validators/DateValidator.js";
import DateIntervalValidator from "../validators/DateIntervalValidator.js";
import PhoneValidator from "../validators/PhoneValidator.js";
import RegexValidator from "../validators/RegexValidator.js";
import FaxValidator from "../validators/FaxValidator.js";
import EmailValidator from "../validators/EmailValidator.js";
import UniqueValueValidator from "../validators/UniqueValueValidator.js";
import ArrayValidator from "../validators/ArrayValidator.js";


let ValidationUtility = {

    supportedValidators: {
        "TextValidator": new TextValidator,
        "NumberValidator": new NumberValidator,
        "DateValidator": new DateValidator,
        "DateIntervalValidator": new DateIntervalValidator,
        "PhoneValidator": new PhoneValidator,
        "RegexValidator": new RegexValidator,
        "FaxValidator": new FaxValidator,
        "EmailValidator": new EmailValidator,
        "UniqueValueValidator": new UniqueValueValidator,
        "ArrayValidator": new ArrayValidator
    },


    /**
     *
     * @param value
     * @param validator
     * @param loadName
     * @param fieldName
     * @param id
     * @param additionalParams
     */
    validate: function(value, validator, loadName, fieldName, id=null, additionalParams = null) {
        let validatorObject = this.getValidatorByName(validator.class);
        if(validatorObject.isRequest()) {
            return new Promise(function(resolve, reject) {
                let params = {};
                if(typeof value === 'object' &&
                    !Array.isArray(value) &&
                    value !== null) {
                    params = value;
                    params.fieldNames = Object.keys(value);
                }
                else {
                    params[fieldName] = value;
                    params.fieldName = fieldName;
                }
                params.ngsValidate = true;
                params.validator = validator;

                params.itemId = id;
                if(additionalParams) {
                    for(let attribute in additionalParams) {
                        if(!additionalParams.hasOwnProperty(attribute)) {
                            continue;
                        }
                        params[attribute] = additionalParams[attribute];
                    }
                }

                NGS.load(loadName, params, function(resp) {
                    if(!resp.valid) {
                        resolve({success: false, message: resp.message, validator: validator.class});
                    }
                    else {
                        resolve({success: true, validator: validator.class});
                    }
                });
            }.bind(this));
        }
        else {
            return new Promise(function(resolve, reject) {
                let message = "";
                let isEmpty = false;
                try {
                    message = validatorObject.baseValidate(value, validator, fieldName);
                }
                catch(error) {
                    message = error.message;
                    isEmpty = true;
                }
                if(message && typeof message !== 'string') {
                    isEmpty = true;
                    for(let attribute in message) {
                        if(!message.hasOwnProperty(attribute)) {
                            continue;
                        }
                        if(message[attribute] && message[attribute].indexOf('is required') === -1) {
                            isEmpty = false;
                        }
                    }
                }
                
                if(!message) {
                    resolve({success: true, validator: validator.class});
                }
                else {
                    resolve({success: false, isEmpty: isEmpty, message: message, validator: validator.class});
                }

            }.bind(this));

        }

    },


    /**
     * add custom validator
     *
     * @param key
     * @param validatorObject
     */
    addValidator(key, validatorObject) {
        if(this.supportedValidators[key]) {
            throw new Error("validator already exists");
        }

        this.supportedValidators[key] = validatorObject;
    },



    getValidatorByName(validatorClassName) {
        let validatorNameParts = validatorClassName.split("\\");
        let validatorName = validatorNameParts[validatorNameParts.length - 1];
        if(!this.supportedValidators[validatorName]) {
            throw new Error("validator " + validatorName + " not found");
        }
        let validator = this.supportedValidators[validatorName];
        return validator;
    }



};

export default ValidationUtility;
