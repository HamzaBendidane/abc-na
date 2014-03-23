<?php
class Cfe_Auth_Result extends Zend_Auth_Result
{
   /**
     * Failure due to ConnexionWhithApp
     */
    const FAILURE_GO_START_APP_PROCESS          = -5;
    
    /**
     * Failure due to User has wrong Device : Iphone/Ipod touch for iphone, ipad for ipad
     */
    const FAILURE_USER_DEVICE_RESTRICTION       = -6;
    
    public function __construct($code, $identity, array $messages = array())
    {
        $code = (int) $code;
    
        if ($code < self::FAILURE_USER_DEVICE_RESTRICTION) {
            $code = self::FAILURE;
        } elseif ($code > self::SUCCESS ) {
            $code = 1;
        }
    
        $this->_code     = $code;
        $this->_identity = $identity;
        $this->_messages = $messages;
    }
}