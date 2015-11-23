<?php

/**
 * Data writer for user spam rules.
 */
class ThemeHouse_SpamRules_DataWriter_UserSpamRule extends XenForo_DataWriter
{

    /**
     * Title of the phrase that will be created when a call to set the
     * existing data fails (when the data doesn't exist).
     *
     * @var string
     */
    protected $_existingDataErrorPhrase = 'th_requested_user_spam_rule_not_found_spamrules';

    /**
     * Gets the fields that are defined for the table.
     * See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_user_spam_rule_th' => array(
                'user_spam_rule_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ), /* END 'user_spam_rule_id' */
                'title' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ), /* END 'title' */
                'action' => array(
                    'type' => self::TYPE_STRING,
                    'allowedValues' => array(
                        'moderate',
                        'reject'
                    ),
                    'default' => 'moderate'
                ), /* END 'action' */
                'user_criteria' => array(
                    'type' => self::TYPE_UNKNOWN,
                    'required' => true,
                    'verification' => array(
                        '$this',
                        '_verifyCriteria'
                    )
                ), /* END 'user_criteria' */
                'request_criteria' => array(
                    'type' => self::TYPE_UNKNOWN,
                    'required' => true,
                    'verification' => array(
                        '$this',
                        '_verifyCriteria'
                    )
                ), /* END 'request_criteria' */
            ), /* END 'xf_user_spam_rule_th' */
        );
    } /* END _getFields */

    /**
     * Gets the actual existing data out of data that was passed in.
     * See parent for explanation.
     *
     * @param mixed
     *
     * @return array false
     */
    protected function _getExistingData($data)
    {
        if (!$userSpamRuleId = $this->_getExistingPrimaryKey($data, 'user_spam_rule_id')) {
            return false;
        }

        $userSpamRule = $this->_getUserSpamRuleModel()->getUserSpamRuleById($userSpamRuleId);
        if (!$userSpamRule) {
            return false;
        }

        return $this->getTablesDataFromArray($userSpamRule);
    } /* END _getExistingData */

    /**
     * Verifies that the criteria is valid and formats is correctly.
     * Expected input format: [] with children: [rule] => name, [data] => info
     *
     * @param array|string $criteria Criteria array or serialize string; see
     * above for format. Modified by ref.
     *
     * @return boolean
     */
    protected function _verifyCriteria(&$criteria)
    {
        $criteriaFiltered = XenForo_Helper_Criteria::prepareCriteriaForSave($criteria);
        $criteria = serialize($criteriaFiltered);
        return true;
    } /* END _verifyCriteria */

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'user_spam_rule_id = ' . $this->_db->quote($this->getExisting('user_spam_rule_id'));
    } /* END _getUpdateCondition */

    /**
     * Get the user spam rules model.
     *
     * @return ThemeHouse_SpamRules_Model_UserSpamRule
     */
    protected function _getUserSpamRuleModel()
    {
        return $this->getModelFromCache('ThemeHouse_SpamRules_Model_UserSpamRule');
    } /* END _getUserSpamRuleModel */
}