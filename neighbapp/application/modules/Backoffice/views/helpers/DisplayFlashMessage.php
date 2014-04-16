<?php

/**
 * Show flash message (record in session)
 * rom1cop1
 */
class Zend_View_Helper_DisplayFlashMessage extends Zend_View_Helper_Abstract {

    /**
     * Retourne une vue affichant un message flash
     * Permet d'afficher soit un message simple, soit un tableau de données avec : la vue à utiliser et les data à passer à la vue
     *
     * @return string : le html de la vue
     */
    public function displayFlashMessage() {

        $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');

        if ($flash->count()) {
            $messages = $flash->getMessages();
            $render = '';

            foreach ($messages as $message) {
                $display = array();
                if (!is_array($message)) {
                    $display['body'] = $message;
                }else{
                    $display['body'] = $message['body'];
                }
                
                // type : danger - warning - success - info
                
                $display['type'] = isset($message['type']) ? $message['type'] : 'info';
                $this->view->assign('data_flash', $display);
                $render .= $this->view->render('messages/default.phtml');
            }

            return $render;
        } else {
            return '';
        }
    }

}
