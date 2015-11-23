<?php

/**
 * Installer for Spam Rules by ThemeHouse.
 */
class ThemeHouse_SpamRules_Install_Controller extends ThemeHouse_Install
{

    protected $_resourceManagerUrl = 'http://xenforo.com/community/resources/spam-rules.2771/';

    protected $_minVersionId = 1020000;

    protected $_minVersionString = '1.2.0';

    protected function _preInstall()
    {
        self::$_noUninstall = true;

        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('ThemeHouse_SpamCriteria');

        if ($addOn) {
            $dw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
            $dw->setExistingData($addOn);
            $dw->delete();
        }

        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('ThemeHouse_UserRegSpam');

        if ($addOn) {
            $dw = XenForo_DataWriter::create('XenForo_DataWriter_AddOn');
            $dw->setExistingData($addOn);
            $dw->delete();
        }

        self::$_noUninstall = false;
    } /* END _preInstall */

    /**
     * Gets the tables (with fields) to be created for this add-on.
     * See parent for explanation.
     *
     * @return array Format: [table name] => fields
     */
    protected function _getTables()
    {
        return array(
            'xf_spam_rule' => array(
                'spam_rule_id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', /* END 'spam_rule_id' */
                'title' => 'VARCHAR(255) NOT NULL', /* END 'title' */
                'action' => 'ENUM(\'moderate\', \'reject\') DEFAULT \'moderate\'', /* END 'action' */
                'user_criteria' => 'MEDIUMBLOB', /* END 'user_criteria' */
                'content_criteria' => 'MEDIUMBLOB', /* END 'content_criteria' */
                'request_criteria' => 'MEDIUMBLOB', /* END 'request_criteria' */
            ), /* END 'xf_spam_rule' */
            'xf_user_spam_rule_th' => array(
                'user_spam_rule_id' => 'INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', /* END 'user_spam_rule_id' */
                'title' => 'VARCHAR(255) NOT NULL', /* END 'title' */
                'action' => 'ENUM(\'moderate\', \'reject\') DEFAULT \'moderate\'', /* END 'action' */
                'user_criteria' => 'MEDIUMBLOB', /* END 'user_criteria' */
                'request_criteria' => 'MEDIUMBLOB', /* END 'request_criteria' */
            ), /* END 'xf_user_spam_rule_th' */
        );
    } /* END _getTables */


    protected function _postInstall()
    {
        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('YoYo_');
        if ($addOn) {
            $db->query("
                INSERT INTO xf_user_spam_rule_th (user_spam_rule_id, title, action, user_criteria, request_criteria)
                    SELECT user_spam_rule_id, title, action, user_criteria, request_criteria
                        FROM xf_user_spam_rule_waindigo"); 
        }
    }
}