import AbstractCmsAddUpdateLoad from '../AbstractCmsAddUpdateLoad.js';
import FilterManager from "../../util/FilterManager.js";
import PageManager from "../../managers/PageManager.js";

export default class RulesLoad extends AbstractCmsAddUpdateLoad {

    constructor() {
        super();
    }


    getMethod() {
        return "GET";
    }

    getModalLevel() {
        return 2;
    }

    initBackBtn() {
        
    }


    afterCmsLoad() {
        this.initFilters();
        this.initSaveRule();

        if(!this.args().isViewMode) {
            this.initActionVariableSelection();
            this.initRemoveRules();
        }

    }


    onUnLoad() {
        let ruleName = this.args().ruleName;
        FilterManager.destroy(ruleName + '_filter');
    }


    initFilters() {
        let ruleName = this.args().ruleName;
        let filterValues = this.args().filterValues;
        if (filterValues) {
            let preselectedFilter = this.args().filter;
            this.filterManager = new FilterManager(ruleName + '_filter', {possibleFilters: filterValues}, preselectedFilter);
            this.filterManager.onFilterChange((filter) => {
                document.getElementById('ruleFilter').value = JSON.stringify(filter);
            });
        }
    }


    initSaveRule() {
        let saveFilterBtns = document.querySelectorAll('.f_save-filter');
        for (let i = 0; i < saveFilterBtns.length; i++) {
            saveFilterBtns[i].addEventListener('click', this._saveFilter.bind(this));
        }


    }

    // adjusted_cost  products.msrp + ((products.msrp * 2) / 100)
    _saveFilter() {
        let filter = document.getElementById('ruleFilter').value;
        if (!filter) {
            console.log('please select filter');
            return;
        }

        let newRuleName = document.getElementById('newRuleName').value;
        if (!newRuleName) {
            console.log('please specify filter name');
            return;
        }

        filter = JSON.parse(filter);

        let fieldName = document.getElementById('fieldName').value;
        if (!fieldName) {
            console.log('please specify field name');
            return;
        }
        let assignFormula = document.getElementById('assignFormula').value;
        if (!assignFormula) {
            console.log('please specify action');
            return;
        }

        let actions = {};
        actions[fieldName] = assignFormula;
        let ruleName = this.args().ruleName;
        NGS.action("ngs.AdminTools.actions.rules.save", {ruleName: ruleName, name: newRuleName, filter: filter, actions: [actions]}, function(res) {
            if(res.success) {
                let renderTemplate = document.getElementById('existingRuleToCopy').innerHTML;
                let newRuleRow = this.renderTemplate(renderTemplate, res);
                document.querySelector('.f_actual-rules').append(newRuleRow);
                this._initRemoveRule(newRuleRow);
            }
        }.bind(this));
    }


    initActionVariableSelection() {
        let fieldSelect = document.getElementById('fieldName');
        let fieldType = this._getTypeById(fieldSelect.value);
        if(fieldType) {
            this._showFormulaSection(fieldType);
        }

        fieldSelect.addEventListener('change', function (evt) {
            let fieldType = this._getTypeById(fieldSelect.value);
            if(fieldType) {
                this._showFormulaSection(fieldType);
            }
        }.bind(this));
    }


    initRemoveRules() {
        let removeBtns = document.querySelectorAll('.f_delete-rule');
        for(let i=0; i<removeBtns.length; i++) {
            this._initRemoveRule(removeBtns[i]);
        }
    }


    /**
     * returns field type by field id
     *
     * @param id
     * @returns {null|*}
     * @private
     */
    _getTypeById(id) {
        let actionFields = this.args().actionFields;
        for(let i=0; i<actionFields.length; i++) {
            if(actionFields[i].id == id) {
                return actionFields[i].type;
            }
        }
        return null;
    }


    /**
     * remove rule event handling
     *
     * @param btn
     * @private
     */
    _initRemoveRule(btn) {
        btn.addEventListener('click', function(evt) {
            let btn = evt.target.closest('.f_delete-rule');
            let id = btn.getAttribute('data-id');
            NGS.action("ngs.AdminTools.actions.rules.remove", {id: id}, function(res) {
                if(res.success) {
                    btn.closest('.f_actual-rule-item').remove();
                }
            });
        });
    }


    /**
     *
     * @param type
     * @private
     */
    _showFormulaSection(type) {
        let possibleActions = this.args().possibleActions;
        if (!possibleActions[type]) {
            return;
        }

        let action = possibleActions[type];
        let renderTemplate = "";
        let assigmentSection = "";

        if (action.type === 'formula') {
            renderTemplate = document.getElementById('formulaActionToCopy').innerHTML;
            let renderData = {possible_variables: ""};
            for (let i = 0; i < action.possible_variables.length; i++) {
                renderData.possible_variables += "<span class='formula-vvariable'>" + action.possible_variables[i].name_to_use + " - " +
                    action.possible_variables[i].display_name
                    + "</span><br>";
            }
            assigmentSection = this.renderTemplate(renderTemplate, renderData);
        } else if (action.type === 'assign_text') {
            renderTemplate = document.getElementById('textActionToCopy').innerHTML;
            assigmentSection = this.renderTemplate(renderTemplate, {});

        }


        let actionSection = document.querySelector('.f_action-section');
        actionSection.innerHTML = '';
        actionSection.append(assigmentSection);
    }

}