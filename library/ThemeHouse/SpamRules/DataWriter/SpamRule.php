<?php

/**
 * Data writer for spam rules.
 */
class ThemeHouse_SpamRules_DataWriter_SpamRule extends XenForo_DataWriter
{

    /**
     * Title of the phrase that will be created when a call to set the
     * existing data fails (when the data doesn't exist).
     *
     * @var string
     */
    protected $_existingDataErrorPhrase = 'th_requested_spam_rule_not_found_spamrules';

    /**
     * Gets the fields that are defined for the table.
     * See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'xf_spam_rule' => array(
                'spam_rule_id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ), /* END 'spam_rule_id' */
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
                'content_criteria' => array(
                    'type' => self::TYPE_UNKNOWN,
                    'required' => true,
                    'verification' => array(
                        '$this',
                        '_verifyCriteria'
                    )
                ), /* END 'content_criteria' */
                'request_criteria' => array(
                    'type' => self::TYPE_UNKNOWN,
                    'required' => true,
                    'verification' => array(
                        '$this',
                        '_verifyCriteria'
                    )
                ), /* END 'request_criteria' */
            ), /* END 'xf_spam_rule' */
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
        if (!$spamRuleId = $this->_getExistingPrimaryKey($data, 'spam_rule_id')) {
            return false;
        }

        $spamRule = $this->_getSpamRuleModel()->getSpamRuleById($spamRuleId);
        if (!$spamRule) {
            return false;
        }

        return $this->getTablesDataFromArray($spamRule);
    } /* END _getExistingData */

    /**
     * Gets SQL condition to update the existing record.
     *
     * @return string
     */
    protected function _getUpdateCondition($tableName)
    {
        return 'spam_rule_id = ' . $this->_db->quote($this->getExisting('spam_rule_id'));
    } /* END _getUpdateCondition */

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
     * Get the spam rules model.
     *
     * @return ThemeHouse_SpamRules_Model_SpamRule
     */
    protected function _getSpamRuleModel()
    {
        return $this->getModelFromCache('ThemeHouse_SpamRules_Model_SpamRule');
    } /* END _getSpamRuleModel */
}