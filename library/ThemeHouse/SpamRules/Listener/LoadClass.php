<?php

class ThemeHouse_SpamRules_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{
    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_SpamRules' => array(
                'model' => array(
                    'XenForo_Model_SpamPrevention',
                ), /* END 'model' */
            ), /* END 'ThemeHouse_SpamRules' */
        );
    } /* END _getExtendedClasses */

    public static function loadClassModel($class, array &$extend)
    {
        $loadClassModel = new ThemeHouse_SpamRules_Listener_LoadClass($class, $extend, 'model');
        $extend = $loadClassModel->run();
    } /* END loadClassModel */
}