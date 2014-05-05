<?php

/**
 * Module Backoffice Numbate
 * Login
 * @todo : faire des routes 
 */
class Backoffice_LoginController extends Class_Controller_BackofficeAction {

    /**
     * @todo prototype sans ACl 
     */
    public function init() {
        $this->_global_access = 'public';
    }

    /**
     * Login Page
     */
    public function indexAction() {
        if ($this->_user_is_connected) {
            $this->_helper->redirector('index', 'home', 'Backoffice');
        }

        $this->_helper->layout->setLayout('login');

        //@todo : identification avec Webservice

        $form = new Class_Form_Bootstrap_LoginVertical;

        $this->view->form = $form;

        $request = $this->_request;
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {

                $dbAdapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                $authAdapter = new Zend_Auth_Adapter_DbTable(
                        $dbAdapter, 'uaccounts', 'email', 'password'
                );

                $authAdapter->setIdentity($request->getPost('login'));
                $authAdapter->setCredential(hash("md5", $request->getPost('password')));

                $result = Zend_Auth::getInstance()->authenticate($authAdapter);

                if ($result->isValid()) {
                    $userGroupModel = new Class_Model_UserGroup();
                    // écriture de l’objet complet en session, sauf le champ password
                    $const = Cfe_Numbate_Rights::$groupLabel;
                    $data = $authAdapter->getResultRowObject(null, 'password');

                    // Récupération du groupe de l'utilisateur
                    $data->groupe = $userGroupModel->getUserGroupById($data->type);

                    // Récupération des ACL pour le groupe de l'utilisateur
                    $data->acl = new Class_Common_Acl($data->type);
                    Zend_Auth::getInstance()->getStorage()->write($data);

                    $this->_helper->redirector('index', 'home', 'Backoffice');
                } else {
                    $form->getElement('login')->addErrors($result->getMessages());
                }
            }
        }


        $this->view->headTitle(_("Login"));

        /*
          if (!isset($session->acl)) {
          $acl = new Zend_Acl();
          $acl->addRole(new Zend_Acl_Role('user'));
          $acl->add(new Zend_Acl_Resource('reservations'));
          $session->acl = $acl;
          } */
    }
}