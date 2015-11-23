<?php

/**
 * Model for user spam rules.
 */
class ThemeHouse_SpamRules_Model_UserSpamRule extends XenForo_Model
{

    /**
     * Gets user spam rules that match the specified criteria.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions
     *
     * @return array [user spam rule id] => info.
     */
    public function getUserSpamRules(array $conditions = array(), array $fetchOptions = array())
    {
        $whereClause = $this->prepareUserSpamRuleConditions($conditions, $fetchOptions);

        $sqlClauses = $this->prepareUserSpamRuleFetchOptions($fetchOptions);
        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->fetchAllKeyed(
            $this->limitQueryResults(
                '
                SELECT user_spam_rule.*
                    ' . $sqlClauses['selectFields'] . '
                FROM xf_user_spam_rule_th AS user_spam_rule
                ' . $sqlClauses['joinTables'] . '
                WHERE ' . $whereClause . '
                ' . $sqlClauses['orderClause'] . '
            ', $limitOptions['limit'], $limitOptions['offset']),
            'user_spam_rule_id');
    } /* END getUserSpamRules */

    /**
     * Gets the user spam rule that matches the specified criteria.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions Options that affect what is fetched.
     *
     * @return array false
     */
    public function getUserSpamRule(array $conditions = array(), array $fetchOptions = array())
    {
        $userSpamRules = $this->getUserSpamRules($conditions, $fetchOptions);

        return reset($userSpamRules);
    } /* END getUserSpamRule */

    /**
     * Gets a user spam rule by ID.
     *
     * @param integer $userSpamRuleId
     * @param array $fetchOptions Options that affect what is fetched.
     *
     * @return array false
     */
    public function getUserSpamRuleById($userSpamRuleId, array $fetchOptions = array())
    {
        $conditions = array(
            'user_spam_rule_id' => $userSpamRuleId
        );

        return $this->getUserSpamRule($conditions, $fetchOptions);
    } /* END getUserSpamRuleById */

    /**
     *
     * @param array $userSpamRule
     * @return array $userSpamRule
     */
    public function prepareUserSpamRule(array $userSpamRule)
    {
        if ($userSpamRule['request_criteria']) {
            $userSpamRule['requestCriteria'] = unserialize($userSpamRule['request_criteria']);
        } else {
            $userSpamRule['requestCriteria'] = array();
        }

        return $userSpamRule;
    } /* END prepareUserSpamRule */

    /**
     *
     * @param array $userSpamRules
     * @return array $userSpamRules
     */
    public function prepareUserSpamRules(array $userSpamRules)
    {
        foreach ($userSpamRules as &$userSpamRule) {
            $userSpamRule = $this->prepareUserSpamRule($userSpamRule);
        }

        return $userSpamRules;
    } /* END prepareUserSpamRules */

    /**
     * Gets the total number of a user spam rule that match the specified
     * criteria.
     *
     * @param array $conditions List of conditions.
     *
     * @return integer
     */
    public function countUserSpamRules(array $conditions = array())
    {
        $fetchOptions = array();

        $whereClause = $this->prepareUserSpamRuleConditions($conditions, $fetchOptions);
        $joinOptions = $this->prepareUserSpamRuleFetchOptions($fetchOptions);

        $limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

        return $this->_getDb()->fetchOne(
            '
            SELECT COUNT(*)
            FROM xf_user_spam_rule_th AS user_spam_rule
            ' . $joinOptions['joinTables'] . '
            WHERE ' . $whereClause . '
        ');
    } /* END countUserSpamRules */

    /**
     * Gets all user spam rules titles.
     *
     * @return array [user spam rule id] => title.
     */
    public static function getUserSpamRuleTitles()
    {
        $userSpamRules = XenForo_Model::create(__CLASS__)->getUserSpamRules();
        $titles = array();
        foreach ($userSpamRules as $userSpamRuleId => $userSpamRule) {
            $titles[$userSpamRuleId] = $userSpamRule['title'];
        }
        return $titles;
    } /* END getUserSpamRuleTitles */

    /**
     * Gets the default user spam rule record.
     *
     * @return array
     */
    public function getDefaultUserSpamRule()
    {
        return array(
            'user_spam_rule_id' => '', /* END 'user_spam_rule_id' */
            'action' => 'moderate', /* END 'action' */
            'user_criteria' => '', /* END 'user_criteria' */
            'request_criteria' => '', /* END 'request_criteria' */
        );
    } /* END getDefaultUserSpamRule */

    /**
     * Prepares a set of conditions to select user spam rules against.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions The fetch options that have been provided. May
     * be edited if criteria requires.
     *
     * @return string Criteria as SQL for where clause
     */
    public function prepareUserSpamRuleConditions(array $conditions, array &$fetchOptions)
    {
        $db = $this->_getDb();
        $sqlConditions = array();

        if (isset($conditions['user_spam_rule_ids']) && !empty($conditions['user_spam_rule_ids'])) {
            $sqlConditions[] = 'user_spam_rule.user_spam_rule_id IN (' . $db->quote($conditions['user_spam_rule_ids']) .
                 ')';
        } else
            if (isset($conditions['user_spam_rule_id'])) {
                $sqlConditions[] = 'user_spam_rule.user_spam_rule_id = ' . $db->quote($conditions['user_spam_rule_id']);
            }

        $this->_prepareUserSpamRuleConditions($conditions, $fetchOptions, $sqlConditions);

        return $this->getConditionsForClause($sqlConditions);
    } /* END prepareUserSpamRuleConditions */

    /**
     * Method designed to be overridden by child classes to add to set of
     * conditions.
     *
     * @param array $conditions List of conditions.
     * @param array $fetchOptions The fetch options that have been provided. May
     * be edited if criteria requires.
     * @param array $sqlConditions List of conditions as SQL snippets. May be
     * edited if criteria requires.
     */
    protected function _prepareUserSpamRuleConditions(array $conditions, array &$fetchOptions, array &$sqlConditions)
    {
    } /* END _prepareUserSpamRuleConditions */

    /**
     * Checks the 'join' key of the incoming array for the presence of the
     * FETCH_x bitfields in this class
     * and returns SQL snippets to join the specified tables if required.
     *
     * @param array $fetchOptions containing a 'join' integer key built from
     * this class's FETCH_x bitfields.
     *
     * @return string containing selectFields, joinTables, orderClause keys.
     * Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN
     * foo ON (foo.id = other.id) '; orderClause = 'ORDER BY x.y'
     */
    public function prepareUserSpamRuleFetchOptions(array &$fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $orderBy = '';

        $this->_prepareUserSpamRuleFetchOptions($fetchOptions, $selectFields, $joinTables, $orderBy);

        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables,
            'orderClause' => ($orderBy ? "ORDER BY $orderBy" : '')
        );
    } /* END prepareUserSpamRuleFetchOptions */

    /**
     * Method designed to be overridden by child classes to add to SQL snippets.
     *
     * @param array $fetchOptions containing a 'join' integer key built from
     * this class's FETCH_x bitfields.
     * @param string $selectFields = ', user.*, foo.title'
     * @param string $joinTables = ' INNER JOIN foo ON (foo.id = other.id) '
     * @param string $orderBy = 'x.y ASC, x.z DESC'
     */
    protected function _prepareUserSpamRuleFetchOptions(array &$fetchOptions, &$selectFields, &$joinTables, &$orderBy)
    {
    } /* END _prepareUserSpamRuleFetchOptions */
}