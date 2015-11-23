<?php

/**
 * Model for spam rules.
 */
class ThemeHouse_SpamRules_Model_SpamRule extends XenForo_Model
{
    /**
     * Gets spam rules that match the specified criteria.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions
     *
     * @return array [spam rule id] => info.
     */
    public function getSpamRules(array $conditions = array(), array $fetchOptions = array())
    {
        $whereClause = $this->prepareSpamRuleConditions($conditions, $fetchOptions);

        $sqlClauses = $this->prepareSpamRuleFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed($this->limitQueryResults('
                SELECT spam_rule.*
                    ' . $sqlClauses['selectFields'] . '
                FROM xf_spam_rule AS spam_rule
                ' . $sqlClauses['joinTables'] . '
                WHERE ' . $whereClause . '
                ' . $sqlClauses['orderClause'] . '
            ', $limitOptions['limit'], $limitOptions['offset']
        ), 'spam_rule_id');
    } /* END getSpamRules */

    /**
     * Gets the spam rule that matches the specified criteria.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions Options that affect what is fetched.
     *
     * @return array|false
     */
    public function getSpamRule(array $conditions = array(), array $fetchOptions = array())
    {
        $spamRules = $this->getSpamRules($conditions, $fetchOptions);

        return reset($spamRules);
    } /* END getSpamRule */

    /**
     * Gets a spam rule by ID.
     *
     * @param integer $spamRuleId
     * @param array $fetchOptions Options that affect what is fetched.
     *
     * @return array|false
     */
    public function getSpamRuleById($spamRuleId, array $fetchOptions = array())
    {
        $conditions = array('spam_rule_id' => $spamRuleId);

        return $this->getSpamRule($conditions, $fetchOptions);
    } /* END getSpamRuleById */

    /**
     *
     * @param array $spamRule
     * @return array $spamRule
     */
    public function prepareSpamRule(array $spamRule)
    {
        if ($spamRule['content_criteria']) {
            $spamRule['contentCriteria'] = unserialize($spamRule['content_criteria']);
        } else {
            $spamRule['contentCriteria'] = array();
        }

        if ($spamRule['request_criteria']) {
            $spamRule['requestCriteria'] = unserialize($spamRule['request_criteria']);
        } else {
            $spamRule['requestCriteria'] = array();
        }

        return $spamRule;
    } /* END prepareSpamRule */

    /**
     *
     * @param array $spamRules
     * @return array $spamRules
     */
    public function prepareSpamRules(array $spamRules)
    {
        foreach ($spamRules as &$spamRule) {
            $spamRule = $this->prepareSpamRule($spamRule);
        }

        return $spamRules;
    } /* END prepareSpamRules */

    /**
     * Gets the total number of a spam rule that match the specified criteria.
     *
     * @param array $conditions List of conditions.
     *
     * @return integer
     */
    public function countSpamRules(array $conditions = array())
    {
        $fetchOptions = array();

        $whereClause = $this->prepareSpamRuleConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareSpamRuleFetchOptions($fetchOptions);

        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->_getDb()->fetchOne('
            SELECT COUNT(*)
            FROM xf_spam_rule AS spam_rule
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause . '
        ');
    } /* END countSpamRules */

    /**
     * Gets all spam rules titles.
     *
     * @return array [spam rule id] => title.
     */
    public static function getSpamRuleTitles()
    {
        $spamRules = XenForo_Model::create(__CLASS__)->getSpamRules();
        $titles = array();
        foreach ($spamRules as $spamRuleId => $spamRule) {
            $titles[$spamRuleId] = $spamRule['title'];
        }
        return $titles;
    } /* END getSpamRuleTitles */

    /**
     * Gets the default spam rule record.
     *
     * @return array
     */
    public function getDefaultSpamRule()
    {
        return array(
            'spam_rule_id' => '', /* END 'spam_rule_id' */
            'action' => 'moderate', /* END 'action' */
            'user_criteria' => '', /* END 'user_criteria' */
            'content_criteria' => '', /* END 'content_criteria' */
            'request_criteria' => '', /* END 'request_criteria' */
        );
    } /* END getDefaultSpamRule */

    /**
     * Prepares a set of conditions to select spam rules against.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions The fetch options that have been provided. May be edited if criteria requires.
     *
     * @return string Criteria as SQL for where clause
     */
    public function prepareSpamRuleConditions(array $conditions, array &$fetchOptions)
    {
        $db = $this->_getDb();
        $sqlConditions = array();

        if (isset($conditions['spam_rule_ids']) && !empty($conditions['spam_rule_ids'])) {
            $sqlConditions[] = 'spam_rule.spam_rule_id IN (' . $db->quote($conditions['spam_rule_ids']) . ')';
        } else if (isset($conditions['spam_rule_id'])) {
            $sqlConditions[] = 'spam_rule.spam_rule_id = ' . $db->quote($conditions['spam_rule_id']);
        }

        $this->_prepareSpamRuleConditions($conditions, $fetchOptions, $sqlConditions);

        return $this->getConditionsForClause($sqlConditions);
    } /* END prepareSpamRuleConditions */

    /**
     * Method designed to be overridden by child classes to add to set of conditions.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions The fetch options that have been provided. May be edited if criteria requires.
     * @param array $sqlConditions List of conditions as SQL snippets. May be edited if criteria requires.
     */
    protected function _prepareSpamRuleConditions(array $conditions, array &$fetchOptions, array &$sqlConditions)
    {
    } /* END _prepareSpamRuleConditions */

    /**
     * Checks the 'join' key of the incoming array for the presence of the FETCH_x bitfields in this class
     * and returns SQL snippets to join the specified tables if required.
     *
     * @param array $fetchOptions containing a 'join' integer key built from this class's FETCH_x bitfields.
     *
     * @return string containing selectFields, joinTables, orderClause keys.
     *          Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN foo ON (foo.id = other.id) '; orderClause = 'ORDER BY x.y'
     */
    public function prepareSpamRuleFetchOptions(array &$fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $orderBy = '';

        $this->_prepareSpamRuleFetchOptions($fetchOptions, $selectFields, $joinTables, $orderBy);

        return array(
            'selectFields' => $selectFields,
            'joinTables'   => $joinTables,
            'orderClause'  => ($orderBy ? "ORDER BY $orderBy" : '')
        );
    } /* END prepareSpamRuleFetchOptions */

    /**
     * Method designed to be overridden by child classes to add to SQL snippets.
     *
     * @param array $fetchOptions containing a 'join' integer key built from this class's FETCH_x bitfields.
     * @param string $selectFields = ', user.*, foo.title'
     * @param string $joinTables = ' INNER JOIN foo ON (foo.id = other.id) '
     * @param string $orderBy = 'x.y ASC, x.z DESC'
     */
    protected function _prepareSpamRuleFetchOptions(array &$fetchOptions, &$selectFields, &$joinTables, &$orderBy)
    {
    } /* END _prepareSpamRuleFetchOptions */
}