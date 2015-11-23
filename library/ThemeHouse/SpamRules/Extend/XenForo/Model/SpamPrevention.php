<?php

/**
 *
 * @see XenForo_Model_SpamPrevention
 */
class ThemeHouse_SpamRules_Extend_XenForo_Model_SpamPrevention extends XFCP_ThemeHouse_SpamRules_Extend_XenForo_Model_SpamPrevention
{

    /**
     *
     * @see XenForo_Model_SpamPrevention::checkMessageSpam()
     */
    public function checkMessageSpam($content, array $extraParams = array(), Zend_Controller_Request_Http $request = null)
    {
        $results = array(
            parent::checkMessageSpam($content, $extraParams, $request)
        );

        if (!$request) {
            $request = new Zend_Controller_Request_Http();
        }

        $results[] = $this->_checkSpamRules($content, $extraParams, $request);

        return max($results);
    } /* END checkMessageSpam */

    protected function _checkSpamRules($content, array $extraParams, Zend_Controller_Request_Http $request)
    {
        /* @var $spamRuleModel ThemeHouse_SpamRules_Model_SpamRule */
        $spamRuleModel = $this->getModelFromCache('ThemeHouse_SpamRules_Model_SpamRule');

        $spamRules = $spamRuleModel->prepareSpamRules($spamRuleModel->getSpamRules());

        $result = self::RESULT_ALLOWED;

        foreach ($spamRules as $spamRule) {
            $contentMatches = ThemeHouse_SpamRules_Helper_Criteria::contentMatchesCriteria($spamRule['contentCriteria'],
                true, $content);
            $requestMatches = ThemeHouse_SpamRules_Helper_Criteria::requestMatchesCriteria($spamRule['requestCriteria'],
                true, $request);
            $userMatches = XenForo_Helper_Criteria::userMatchesCriteria($spamRule['user_criteria'], true);
            if ($contentMatches && $requestMatches && $userMatches) {
                switch ($spamRule['action']) {
                    case 'moderate':
                        $result = self::RESULT_MODERATED;
                        break;
                    case 'reject':
                        $result = self::RESULT_DENIED;
                        break 2;
                }
            }
        }

        return $result;
    } /* END _checkSpamRules */

    /**
     *
     * @see XenForo_Model_SpamPrevention::allowRegistration()
     */
    public function allowRegistration(array $user, Zend_Controller_Request_Http $request)
    {
        $results = array(
            parent::allowRegistration($user, $request)
        );

        if (!$request) {
            $request = new Zend_Controller_Request_Http();
        }

        $results[] = $this->_checkUserSpamRules($user, $request);

        return max($results);
    } /* END allowRegistration */

    protected function _checkUserSpamRules(array $user, Zend_Controller_Request_Http $request)
    {
        /* @var $userSpamRuleModel ThemeHouse_SpamRules_Model_UserSpamRule */
        $userSpamRuleModel = $this->getModelFromCache('ThemeHouse_SpamRules_Model_UserSpamRule');

        $userSpamRules = $userSpamRuleModel->prepareUserSpamRules($userSpamRuleModel->getUserSpamRules());

        $result = self::RESULT_ALLOWED;

        foreach ($userSpamRules as $userSpamRule) {
            $requestMatches = ThemeHouse_SpamRules_Helper_Criteria::requestMatchesCriteria(
                $userSpamRule['requestCriteria'], true, $request);
            $userMatches = XenForo_Helper_Criteria::userMatchesCriteria($userSpamRule['user_criteria'], true);
            if ($requestMatches && $userMatches) {
                switch ($userSpamRule['action']) {
                    case 'moderate':
                        $result = self::RESULT_MODERATED;
                        break;
                    case 'reject':
                        $result = self::RESULT_DENIED;
                        break 2;
                }
            }
        }

        return $result;
    } /* END _checkUserSpamRules */
}