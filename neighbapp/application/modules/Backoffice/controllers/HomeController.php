<?php

/**
 * Module Backoffice Numbate
 * Landing page du backoffice
 * @author romain.causse@surikate.com  
 */


class Backoffice_HomeController extends Class_Controller_BackofficeAction
{
    
    /**
     * Home du Backoffice
     */
    public function indexAction()
    {
        $this->view->headTitle(_("Dashboard"));
    }
}