<?php

/**
 * Admin controller for handling actions on user spam rules.
 */
class ThemeHouse_SpamRules_ControllerAdmin_UserSpamRule extends XenForo_ControllerAdmin_Abstract
{

    protected function _preDispatch($action)
    {
        $this->assertAdminPermission('spamRule');
    } /* END _preDispatch */

    /**
     * Shows a list of user spam rules.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionIndex()
    {
        $userSpamRuleModel = $this->_getUserSpamRuleModel();
        $userSpamRules = $userSpamRuleModel->getUserSpamRules();
        $viewParams = array(
            'userSpamRules' => $userSpamRules
        );
        return $this->responseView('ThemeHouse_SpamRules_ViewAdmin_UserSpamRule_List',
            'th_user_spam_rule_list_spamrules', $viewParams);
    } /* END actionIndex */

    /**
     * Helper to get the user spam rule add/edit form controller response.
     *
     * @param array $userSpamRule
     *
     * @return XenForo_ControllerResponse_View
     */
    protected function _getUserSpamRuleAddEditResponse(array $userSpamRule)
    {
        $userSpamRule = $this->_getUserSpamRuleModel()->prepareUserSpamRule($userSpamRule);

        $isoCountries = array();
        if (function_exists('geoip_country_code_by_name')) {
            $countries = preg_split("/\r\n|\n|\r/", XenForo_Application::get('options')->th_spamRules_countries);
            foreach ($countries as $countryId => $country) {
                preg_match('/([A-Z][0-9A-Z]),"?([^"]*)"?/', $country, $matches);
                if ($matches) {
                    $isoCountries[$matches[1]] = $matches[2];
                }
            }
            asort($isoCountries);
        }

        $viewParams = array(
            'userSpamRule' => $userSpamRule,

            'countries' => $isoCountries,

            'userCriteria' => XenForo_Helper_Criteria::prepareCriteriaForSelection($userSpamRule['user_criteria']),
            'userCriteriaData' => XenForo_Helper_Criteria::getDataForUserCriteriaSelection(),

            'requestCriteria' => XenForo_Helper_Criteria::prepareCriteriaForSelection($userSpamRule['request_criteria'])
        );

        return $this->responseView('ThemeHouse_SpamRules_ViewAdmin_UserSpamRule_Edit',
            'th_user_spam_rule_edit_spamrules', $viewParams);
    } /* END _getUserSpamRuleAddEditResponse */

    /**
     * Displays a form to add a new user spam rule.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionAdd()
    {
        $userSpamRule = $this->_getUserSpamRuleModel()->getDefaultUserSpamRule();

        return $this->_getUserSpamRuleAddEditResponse($userSpamRule);
    } /* END actionAdd */

    /**
     * Displays a form to edit an existing user spam rule.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionEdit()
    {
        $userSpamRuleId = $this->_input->filterSingle('user_spam_rule_id', XenForo_Input::STRING);

        if (!$userSpamRuleId) {
            return $this->responseReroute('ThemeHouse_SpamRules_ControllerAdmin_UserSpamRule', 'add');
        }

        $userSpamRule = $this->_getUserSpamRuleOrError($userSpamRuleId);

        return $this->_getUserSpamRuleAddEditResponse($userSpamRule);
    } /* END actionEdit */

    /**
     * Inserts a new user spam rule or updates an existing one.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionSave()
    {
        $this->_assertPostOnly();

        $userSpamRuleId = $this->_input->filterSingle('user_spam_rule_id', XenForo_Input::STRING);

        $input = $this->_input->filter(
            array(
                'title' => XenForo_Input::STRING,
                'action' => XenForo_Input::STRING,
                'request_criteria' => XenForo_Input::ARRAY_SIMPLE,
                'user_criteria' => XenForo_Input::ARRAY_SIMPLE
            ));

        $writer = XenForo_DataWriter::create('ThemeHouse_SpamRules_DataWriter_UserSpamRule');
        if ($userSpamRuleId) {
            $writer->setExistingData($userSpamRuleId);
        }
        $writer->bulkSet($input);
        $writer->save();

        if ($this->_input->filterSingle('reload', XenForo_Input::STRING)) {
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED,
                XenForo_Link::buildAdminLink('user-spam-rules/edit', $writer->getMergedData()));
        } else {
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('user-spam-rules') . $this->getLastHash($writer->get('user_spam_rule_id')));
        }
    } /* END actionSave */

    /**
     * Deletes a user spam rule.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionDelete()
    {
        $userSpamRuleId = $this->_input->filterSingle('user_spam_rule_id', XenForo_Input::STRING);
        $userSpamRule = $this->_getUserSpamRuleOrError($userSpamRuleId);

        $writer = XenForo_DataWriter::create('ThemeHouse_SpamRules_DataWriter_UserSpamRule');
        $writer->setExistingData($userSpamRule);

        if ($this->isConfirmedPost()) { // delete user spam rule
            $writer->delete();

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('user-spam-rules'));
        } else { // show delete confirmation prompt
            $writer->preDelete();
            $errors = $writer->getErrors();
            if ($errors) {
                return $this->responseError($errors);
            }

            $viewParams = array(
                'userSpamRule' => $userSpamRule
            );

            return $this->responseView('ThemeHouse_SpamRules_ViewAdmin_UserSpamRule_Delete',
                'th_user_spam_rule_delete_spamrules', $viewParams);
        }
    } /* END actionDelete */

    /**
     * Gets a valid user spam rule or throws an exception.
     *
     * @param string $userSpamRuleId
     *
     * @return array
     */
    protected function _getUserSpamRuleOrError($userSpamRuleId)
    {
        $userSpamRule = $this->_getUserSpamRuleModel()->getUserSpamRuleById($userSpamRuleId);
        if (!$userSpamRule) {
            throw $this->responseException(
                $this->responseError(new XenForo_Phrase('th_requested_user_spam_rule_not_found_spamrules'), 404));
        }

        return $userSpamRule;
    } /* END _getUserSpamRuleOrError */

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