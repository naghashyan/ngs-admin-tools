<div class="g_scrolable-section">
    <div class="g_scrolable-fixed-box edit-box form form-box box f_edit-box">
        <form onsubmit="return false;" class="g_scrolable-section f_addUpdateForm">
            <input type="hidden" name="tempId" value="">

            <div class="g_fixed-box modal-title-box border">
                <div class="t4">Item Rules Management ({$ns.ruleDisplayName} rules)</div>
            </div>
            <div class="g_scrolable-fixed-box modal-content-box">
                <div class="g_scrolable-fixed-inner-box g-content row">
                    <div class="g-content-item add-categories">
                        <div class="g-content-item-wrapper">
                            <div class="g-content-item-inner g_overflow-y-auto">
                                <div class="existing-rules-section f_existing-rules-section">

                                    <h3 class="t5">Actual rules</h3>
                                    <ul class="actual-rules f_actual-rules">
                                        {if count($ns.existingRules)}
                                            {foreach from=$ns.existingRules item=$ruleItem}
                                                <li class="actual-rule-item f_actual-rule-item {if $ns.appliedRulesIds and in_array($ruleItem->getId(), $ns.appliedRulesIds)}active{/if}">
                                                    <span class="rule-name f-rule-name">{$ruleItem->getRuleName()} - {$ruleItem->getName()}</span>
                                                    {if not $ns.isViewMode}
                                                        <span class="delete-rule f_delete-rule" data-id="{$ruleItem->getId()}">
                                                            <i class="icon-delete-trash"></i>
                                                        </span>
                                                    {/if}
                                                </li>
                                            {/foreach}
                                        {/if}
                                    </ul>
                                </div>
                                {if not $ns.isViewMode}
                                    <div class="rule-creator-panel f_rule-creator-panel">
                                        <h3 class="t5">Create new rule</h3>
                                        <input type="hidden" id="ruleFilter" value="">
                                        <div class="rule-creation-header">
                                            <div class="form-item f_new-rule-name">
                                                <div class="input-field">
                                                    <label for="newRuleName" class="active">Rule Name</label>
                                                    <input id="newRuleName" value="">
                                                </div>
                                            </div>
                                            <div class="rule-filter ngs-filter" id="{$ns.ruleName}_filter">
                                                <div class="active-filters f_active-filters">
                                                    <div class="input-field">
                                                        <div class="page-box">
                                                            <div class="center-box">
                                                                <div class="active-filters search-box-save f_active-filters">
                                                                    <div class="criteria-box f_criteria-box"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="filter-favorite-box">
                                                        <button title="Filter" class="with-icon medium-button button outline-light-basik primary f_filter-add-criteria">
                                                            <i class="icon-filter"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rule-actions">
                                            <h3 class="t5">Rule actions</h3>

                                            <div class="form-item">
                                                <div class="input-field f_variable-selection">
                                                    <label for="customer_requests_submit_orders_type_input">FieldName</label>
                                                    <select data-ngs-searchable="false" data-ngs-remove="false" searchable="Search" class="ngs-choice" id="fieldName">
                                                        {foreach from=$ns.actionFields item=$actionField}
                                                            <option value="{$actionField['id']}" data-type="{$actionField['type']}">
                                                                {$actionField['value']}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="action-section f_action-section">
                                            </div>
                                        </div>

                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="g_fixed-box modal-action-box f_form-actions">
                {if not $ns.isViewMode}
                    <button class="button min-width basic light f_cancel" type="button">Cancel</button>
                    <button class="button min-width basic primary f_save-filter">Save filter</button>
                {else}
                    <button class="button min-width basic light f_cancel" type="button">Close</button>
                {/if}
            </div>
        </form>

    </div>

</div>

<template id="formulaActionToCopy">
    <div class="action-formula f_action-formula">
        <div class="form-item action-part f_action-part">
            <div class="input-field">
                <label for="assignFormula" class="active">Formula</label>
                <input id="assignFormula" value="">
            </div>
        </div>
        <div class="description-for-action">
            <span class="description-title">Here You can assing to selected variable the MAth formula it can contain (+,-,*,/,numbers and described variables)</span>
            <div class="details possible-variables-to-use f_possible-variables-to-use">
                {literal}${possible_variables}{/literal}
            </div>
        </div>
    </div>
</template>

<template id="textActionToCopy">
    <div class="action-formula f_action-formula">
        <div class="action-part f_action-part">
            <label for="assignFormula">Text to assign</label>
            <input id="assignFormula" value="">
        </div>
        <div class="description-for-action">
            <span class="description-title">Please write some text to replace value in case if rule should be applied</span>
        </div>
    </div>
</template>

<template id="existingRuleToCopy">
    <li class="actual-rule-item f_actual-rule-item">
        <span class="rule-name f-rule-name">{literal}${rule_name}{/literal}</span><span class="delete-rule f_delete-rule" data-id="{literal}${rule_id}{/literal}"><i class="icon-delete-trash"></i></span>
    </li>
</template>
