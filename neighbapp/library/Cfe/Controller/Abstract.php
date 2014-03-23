<?php
require_once 'Zend/Controller/Action.php';

abstract class Cfe_Controller_Abstract extends Zend_Controller_Action
{
    /**
     *
     * get the url to access a specific action
     * @param string $action
     * @param array $parameters ($name => $value)
     * @param string $fullAdress
     */
    protected function getUrl($module = null, $controller = null, $action = null, $parameters = array(), $fullAdress = false) {

        $front = $this->getFrontController();
        $request = $this->getRequest();
        if(!is_null($module)) {
            $parameters[$request->getModuleKey()] = $module;
            $parameters[$request->getControllerKey()] = $controller;
            $parameters[$request->getActionKey()] = $action;
        } else {
            $parameters[$request->getModuleKey()] = $request->getModuleName();
            if(!is_null($controller)) {
                $parameters[$request->getControllerKey()] = $controller;
                $parameters[$request->getActionKey()] = $action;
            } else {
                $parameters[$request->getControllerKey()] = $request->getControllerName();
                if(!is_null($action)) {
                    $parameters[$request->getActionKey()] = $action;
                } else {
                    $parameters[$request->getActionKey()] = $request->getActionName();
                }
            }
        }
        $url = $front->getRouter()->assemble($parameters, null, true);
        if ($fullAdress && !preg_match('|^[a-z]+://|', $url)) {
            $url = $request->getScheme(). '://' . $request->getHttpHost() . $url;
        }
        return $url;
    }
}

