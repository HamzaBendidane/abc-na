<?php

/**
 * Envoye de mail SURIKATE
 * Init la conf pour l'envoie de mail standar pour SURIKATE
 */
class Cfe_Mail extends Zend_Mail
{
    
    protected $_smtpConnection;
    protected $_config;
    
    
    public function __construct()
    {
        
         /* configuration SMTP */
        // Config Contact selon Pays
        $this->_config = new Zend_Config_Ini(
                CONFIG_PATH . '/mail.ini',
                Cfe_Utils::getPlatform()
        );
         
        $smtp_config = array(
                //'ssl' => $contact_config->smtp->tls,
                'port' => $this->_config->smtp->Port,
                'auth' => false,
                'username' => $this->_config->smtp->Username,
                'password' => $this->_config->smtp->Password);
        
        // Envoie avec Smtp
        $this->smtpConnection  = new Zend_Mail_Transport_Smtp($this->_config->smtp->Host, $smtp_config);
        
        parent::__construct('UTF-8');       
    }
    
    public function send()
    {
         parent::send($this->_smtpConnection);   
    }
    
    /**
     * Seulement pour les mails à envoyer aux testeur : Ajoute automatiquement From au mail
     * @param email 
     * @todo ajouter un layout selon APP
     */
    public function sendToClient($email)
    {  
        $this->addTo($email);
        $this->setFrom($this->_config->noreply, $this->_config->application);
        parent::send($this->_smtpConnection);
    }
    
    public function addCc($email, $name = '') {
        parent::addCc($email, $name);
    }
}