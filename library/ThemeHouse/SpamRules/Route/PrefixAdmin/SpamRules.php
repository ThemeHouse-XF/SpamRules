<?php

/**
 * Route prefix handler for spam rules in the admin control panel.
 */
class ThemeHouse_SpamRules_Route_PrefixAdmin_SpamRules implements XenForo_Route_Interface
{
    /**
     * Match a specific route for an already matched prefix.
     *
     * @see XenForo_Route_Interface::match()
     */
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'spam_rule_id');
        $action = $router->resolveActionAsPageNumber($action, $request);
        return $router->getRouteMatch('ThemeHouse_SpamRules_ControllerAdmin_SpamRule', $action, 'spamRules');
    } /* END match */

    /**
     * Method to build a link to the specified page/action with the provided
     * data and params.
     *
     * @see XenForo_Route_BuilderInterface
     */
    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'spam_rule_id', 'title');
    } /* END buildLink */
}