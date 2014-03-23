<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Common
 *
 * @author ranjit
 */
class Class_Common_SuperGlobals extends Zend_Controller_Request_Http {
    
    private $aCookieParam;
    private $aServerParam;

    public function __construct($aCookieParam = array(),$aServerParam = array()) {
        
        $this->aCookieParam  = $aCookieParam;
        $this->aServerParam  = $aServerParam;
        
        return $this;
    }
    
    public function getServerParams($oData){
        if(count($oData->aServerParam) > 0){
            return $oData->aServerParam;
        }else{
            return $this->getServer();
        }
    }
    
    public function getCookieParams($oData){
        if(count($oData->aCookieParam) > 0){
            return $oData->aCookieParam;
        }else{
            return $this->getCookie();
        }
    }
}