<?php
/**
 * RulesLoad Class
 * used to add, view item field rules
 *
 * @author Mikael Mkrtchyan
 * @site   http://naghashyan.com
 * @email  mikael.mkrtchyan@naghashyan.com
 * @package ngs.AdminTools.loads.rules
 * @year   2021
 * @version 1.0.0
 **/

namespace ngs\AdminTools\loads\rules;

use ngs\AdminTools\dal\dto\NgsRuleDto;
use ngs\AdminTools\loads\AbstractCmsLoad;
use ngs\AdminTools\managers\AbstractCmsManager;
use ngs\AdminTools\managers\NgsRuleManager;
use ngs\AdminTools\util\StringUtil;

class RulesLoad extends AbstractCmsLoad
{

    public final function load() {
        $ruleName = $this->args()->ruleName;
        $itemId = $this->args()->itemId;

        $rules = $this->getRules($ruleName, $itemId);

        $this->addParam('ruleDisplayName', StringUtil::underlinesToCamelCase($ruleName, true, true));
        $this->addParam('ruleName', $ruleName);
        $this->addJsonParam('ruleName', $ruleName);

        $this->addParam('existingRules', $rules);
        $this->addParam('appliedRulesIds', $this->getAppliedRulesIds($rules, $itemId));
        $this->addJsonParam('editActionType', 'popup');


        $filterValues = $this->getFilterValues($ruleName);
        $this->addJsonParam('filterValues', $filterValues);

        $actionsFields = $this->getPossibleFieldsForActions($filterValues, $ruleName);

        $this->addParam('actionFields', $actionsFields);
        $this->addJsonParam('actionFields', $actionsFields);

        $this->addJsonParam('possibleActions', $this->getPossibleActions($filterValues));

        $this->addParam('isViewMode', $this->args()->isViewMode === 'true');
        $this->addJsonParam('isViewMode', $this->args()->isViewMode === 'true');

    }


    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return NGS()->getTemplateDir('ngs-AdminTools') . '/rules.tpl';
    }


    /**
     * get rules for this item by rule name
     *
     * @param string $ruleName
     * @param $itemId
     * @return NgsRuleDto[]
     */
    private function getRules(string $ruleName, $itemId) {
        $ngsRuleManager = NgsRuleManager::getInstance();
        $rules = $ngsRuleManager->getRules($ruleName, $itemId);
        return $rules;
    }


    /**
     * @param NgsRuleDto[] $rules
     * @param int $itemId
     * @return array|void
     */
    private function getAppliedRulesIds(array $rules, int $itemId) {
        if(!$rules) {
            return [];
        }
        if(!$itemId) {
            $result = [];
            foreach($rules as $rule) {
                $result[] = $rule->getId();
            }

            return $result;
        }

        $ngsRuleManager = NgsRuleManager::getInstance();
        $ruleInfo = $ngsRuleManager->getRuleClassInfo($rules[0]);
        if(!$ruleInfo) {
            return [];
        }

        $managerName = $ruleInfo['managerClass'];
        /** @var AbstractCmsManager $manager */
        $manager = $managerName::getInstance();
        $itemDto = $manager->getItemById($itemId);
        if(!$itemDto) {
            return [];
        }

        $appliedRules = $ngsRuleManager->prioritizeRules($rules, $itemDto);

        $result = [];
        foreach($appliedRules as $rule) {
            $result[] = $rule->getId();
        }

        return $result;
    }


    /**
     * get possible filter values
     *
     * @param string $ruleName
     * @return array
     */
    private function getFilterValues(string $ruleName) {
        $ngsRuleManager = NgsRuleManager::getInstance();
        $ruleDto = new NgsRuleDto();
        $ruleDto->setRuleName($ruleName);
        $filterValues = $ngsRuleManager->getFilterValues($ruleDto);
        return $filterValues;
    }


    /**
     * @param array $filterValues
     * @param string $ruleName
     * @return array
     * @throws \Exception
     */
    private function getPossibleFieldsForActions(array $filterValues, string $ruleName) {
        $result = [];
        $ngsRulesManager = NgsRuleManager::getInstance();
        $availableFields = [];
        if($ruleName) {
            $ruleInfo = $ngsRulesManager->getRuleClassInfoByName($ruleName);
            if(isset($ruleInfo['availableActionFields']) && $ruleInfo['availableActionFields']) {
                $availableFields = $ruleInfo['availableActionFields'];
            }
        }

        foreach($filterValues as $filterValue) {
            if(!$filterValue['is_main']) {
                continue;
            }

            $id = $this->normalizeIdOfField($filterValue['id']);
            if($availableFields && !in_array($id, $availableFields)) {
                continue;
            }

            $displayName = $this->normalizeDisplayNameOfField($filterValue['value']);
            $type = $filterValue['type'];

            $result[] = [
                'id' => $id,
                'value' => $displayName,
                'type' => $type
            ];
        }


        return $result;
    }


    /**
     * leave only necessary part from id
     *
     * @param string $fieldId
     * @return array|mixed|string
     */
    private function normalizeIdOfField(string $fieldId) {
        $result = explode(".", $fieldId);
        $result = $result[count($result) - 1];
        $result = trim($result, '`');
        return $result;
    }


    /**
     * leave only necessary part from id
     *
     * @param string $fieldId
     * @return array|mixed|string
     */
    private function normalizeTableOfField(string $fieldId) {
        $result = explode(".", $fieldId);
        $result = $result[0];
        $result = trim($result, '`');
        return $result;
    }

    /**
     * leave only necessary part from id
     *
     * @param string $fieldId
     * @return array|mixed|string
     */
    private function normalizeNameOfField(string $fieldId) {
        return StringUtil::getElementFunctionByName($fieldId, '');
    }


    /**
     * leave only necessary part from name
     *
     * @param string $name
     * @return array|mixed|string
     */
    private function normalizeDisplayNameOfField(string $name) {
        $result = explode(".", $name);
        $result = $result[count($result) - 1];
        return $result;
    }


    /**
     * returns possible actions for types
     *
     * @param array $filterValues
     * @return array
     */
    private function getPossibleActions(array $filterValues) {
        //TODO: add after other types (checkbox, select, etc..)
        $result = [
            'number' => ['possible_variables' => [], 'type' => 'formula'],
            'text' => ['type' => 'assign_text']
        ];

        foreach($filterValues as $filterValue) {
            $normalizedId = $this->normalizeIdOfField($filterValue['id']);
            $normalizedTable = $this->normalizeTableOfField($filterValue['id']);
            if($filterValue['type'] === 'number' && !in_array($normalizedId, ['id', 'created_by', 'updated_by'])) {
                $result['number']['possible_variables'][] = [
                    'id' => str_replace('`', '', $filterValue['id']),
                    'name_to_use' => $normalizedTable . '.' . $this->normalizeNameOfField($normalizedId),
                    'display_name' => $this->normalizeDisplayNameOfField($filterValue['value'])
                ];
            }
        }

        return $result;
    }

}
