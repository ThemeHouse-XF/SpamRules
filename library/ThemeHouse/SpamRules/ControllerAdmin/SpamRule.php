<?php

/**
 * Admin controller for handling actions on spam rules.
 */
class ThemeHouse_SpamRules_ControllerAdmin_SpamRule extends XenForo_ControllerAdmin_Abstract
{

    protected function _preDispatch($action)
    {
        $this->assertAdminPermission('spamRule');
    } /* END _preDispatch */

    /**
     * Shows a list of spam rules.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionIndex()
    {
        $spamRuleModel = $this->_getSpamRuleModel();
        $spamRules = $spamRuleModel->getSpamRules();
        $viewParams = array(
            'spamRules' => $spamRules
        );
        return $this->responseView('ThemeHouse_SpamRules_ViewAdmin_SpamRule_List', 'th_spam_rule_list_spamrules',
            $viewParams);
    } /* END actionIndex */

    /**
     * Helper to get the spam rule add/edit form controller response.
     *
     * @param array $spamRule
     *
     * @return XenForo_ControllerResponse_View
     */
    protected function _getSpamRuleAddEditResponse(array $spamRule)
    {
        $spamRule = $this->_getSpamRuleModel()->prepareSpamRule($spamRule);

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
            'spamRule' => $spamRule,

            'countries' => $isoCountries,

            'userCriteria' => XenForo_Helper_Criteria::prepareCriteriaForSelection($spamRule['user_criteria']),
            'userCriteriaData' => XenForo_Helper_Criteria::getDataForUserCriteriaSelection(),

            'contentCriteria' => XenForo_Helper_Criteria::prepareCriteriaForSelection($spamRule['content_criteria']),

            'requestCriteria' => XenForo_Helper_Criteria::prepareCriteriaForSelection($spamRule['request_criteria'])
        );

        return $this->responseView('ThemeHouse_SpamRules_ViewAdmin_SpamRule_Edit', 'th_spam_rule_edit_spamrules',
            $viewParams);
    } /* END _getSpamRuleAddEditResponse */

    /**
     * Displays a form to add a new spam rule.
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionAdd()
    {
        $spamRule = $this->_getSpamRuleModel()->getDefaultSpamRule();

        return $this->_getSpamRuleAddEditResponse($spamRule);
    } /* END actionAdd */

    /**
     * Displays a form to edit an existing spam rule.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionEdit()
    {
        $spamRuleId = $this->_input->filterSingle('spam_rule_id', XenForo_Input::STRING);

        if (!$spamRuleId) {
            return $this->responseReroute('ThemeHouse_SpamRules_ControllerAdmin_SpamRule', 'add');
        }

        $spamRule = $this->_getSpamRuleOrError($spamRuleId);

        return $this->_getSpamRuleAddEditResponse($spamRule);
    } /* END actionEdit */

    /**
     * Inserts a new spam rule or updates an existing one.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionSave()
    {
        $this->_assertPostOnly();

        $spamRuleId = $this->_input->filterSingle('spam_rule_id', XenForo_Input::STRING);

        $input = $this->_input->filter(
            array(
                'title' => XenForo_Input::STRING,
                'action' => XenForo_Input::STRING,
                'content_criteria' => XenForo_Input::ARRAY_SIMPLE,
                'request_criteria' => XenForo_Input::ARRAY_SIMPLE,
                'user_criteria' => XenForo_Input::ARRAY_SIMPLE
            ));

        $writer = XenForo_DataWriter::create('ThemeHouse_SpamRules_DataWriter_SpamRule');
        if ($spamRuleId) {
            $writer->setExistingData($spamRuleId);
        }
        $writer->bulkSet($input);
        $writer->save();

        if ($this->_input->filterSingle('reload', XenForo_Input::STRING)) {
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED,
                XenForo_Link::buildAdminLink('spam-rules/edit', $writer->getMergedData()));
        } else {
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('spam-rules') . $this->getLastHash($writer->get('spam_rule_id')));
        }
    } /* END actionSave */

    /**
     * Deletes a spam rule.
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionDelete()
    {
        $spamRuleId = $this->_input->filterSingle('spam_rule_id', XenForo_Input::STRING);
        $spamRule = $this->_getSpamRuleOrError($spamRuleId);

        $writer = XenForo_DataWriter::create('ThemeHouse_SpamRules_DataWriter_SpamRule');
        $writer->setExistingData($spamRule);

        if ($this->isConfirmedPost()) { // delete spam rule
            $writer->delete();

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildAdminLink('spam-rules'));
        } else { // show delete confirmation prompt
            $writer->preDelete();
            $errors = $writer->getErrors();
            if ($errors) {
                return $this->responseError($errors);
            }

            $viewParams = array(
                'spamRule' => $spamRule
            );

            return $this->responseView('ThemeHouse_SpamRules_ViewAdmin_SpamRule_Delete',
                'th_spam_rule_delete_spamrules', $viewParams);
        }
    } /* END actionDelete */

    /**
     * Gets a valid spam rule or throws an exception.
     *
     * @param string $spamRuleId
     *
     * @return array
     */
    protected function _getSpamRuleOrError($spamRuleId)
    {
        $spamRule = $this->_getSpamRuleModel()->getSpamRuleById($spamRuleId);
        if (!$spamRule) {
            throw $this->responseException(
                $this->responseError(new XenForo_Phrase('th_requested_spam_rule_not_found_spamrules'), 404));
        }

        return $spamRule;
    } /* END _getSpamRuleOrError */

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