<?php
class Bundles_Sdk_External extends Bundles_Sfr_Abstract
{ 
    const IDENTITY_PROVIDER_ID = 999;
        
    public $providerId = 2;
        
    /**
     * Create a session to create ACT or SUB
     * @param string $sType the type of billing we will use : SUB or ACT
     * @param string $sSessionId the identifier of the creation session
     * @param string $sUserReference a string corresponding to the identity of
     * the user that wants to bill
     * @param int $iOfferId the id of the provider offer that we will use to
     * create the ACT or SUB
     * @param array $aOptional an array of optional parameters
     * @return boolean
     */
    public function createSession($sType,$sSessionId, $sUserReference, $iOfferId, $aOptional=array()) {

    	$sdkOffer = Bundles_Sdk_Mapper_Offer::findById($iOfferId);
        $aSession['sdk_offer'] = $iOfferId;
     
        Class_Common_SessionHandler::addToSession($sSessionId,$aSession);
        return true;
    }

    /**
     * Create a session to stop a subscription
     * @param string $sSessionId the identifier of the created session
     * @param integer $iSubscriptionId the id of the subscription we want to stop
     * @param array $aOptional an array of optional parameters
     * @return boolean
     * @throws ProviderException_Server_Permanent
     */
    public function createUnsubSession($sSessionId, $iSubscriptionId, $aOptional=array()) {
        
    }

    public function renew(){
        
        $aRenew=array('isBilled'=>true,'errorCode'=>0);
        return $aRenew;
    
    }

    /**
     * getBillingUrl
     * @return string $sSessionId
     * @return string $sNextLink
     * @return string $sNextLinkNok
     * @return array $aOptional
     */
    public function getBillingUrl($sSessionId, $sNextLink, $sNextLinkNok, $aOptional = array())
    {    	
    	$token = uniqid();
    	$aSession['sdk_token'] = $token;
    	Class_Common_SessionHandler::addToSession($sSessionId,$aSession);
    	
    	$sUrl = Class_Utils_Replace::onString(
            $this->conf['billing']['sub']['url'],
             array( '#TOKEN_AFPOFF#'  => $token,
                    '#NL#' => urlencode($sNextLink),
                    '#CCC#' => $sSessionId)
        );
        return $sUrl;
    }
  
    /**
     * @param  int $iUserDataId
     * @return array $returnUser
     */
    public function status($iUserDataId)
    {
		if($iUserDataId % 4 == 0) {
            $standardUser['status'] = 'active';
        }
        elseif($iUserDataId % 4 == 1) {
            $standardUser['status'] = 'stop';
        }
        elseif($iUserDataId % 4 == 2) {
            $standardUser['status'] = 'suspend';
        }
        elseif($iUserDataId % 4 == 3) {
             $standardUser['status'] = 'active';
        }
        else{
        	 $standardUser['status'] = 'error';
        }
        return $standardUser;
    }
    
    /**
    * Check stored params
    * @param string $sSessionId
    * @param array $aOptional (unused)
    * @return int the user data id of the inserted row
    */
    public function createInitialize($sSessionId, $aOptional = array())
    {
   		$aSession = Class_Common_SessionHandler::getSession($sSessionId);
   		
        if ($aSession['UserAndSubscription'] && $aSession['UserAndSubscription']  instanceof Cfe_Stub_Provider_Type_UserAndSubscription) {

        	return $aSession['UserAndSubscription'];
        	
        }elseif($aSession['UserAndAct'] && $aSession['UserAndAct']  instanceof Cfe_Stub_Provider_Type_UserAndAct){
        	
        	return $aSession['UserAndAct'];
        
        }
                
   		if (strcmp($aSession['sdk_token'], $aOptional['tk']) != 0){
   			throw new Exception("CAN NOT VALIDATE SUBSCRIPTION");
   		}
   		$statusDate = date('Y-m-d H:i:s');
        $subscriptionRenewDate = date('Y-m-d 23:59:59', strtotime($statusDate.' +1 week')); 		
   		$auth = $aSession['sdk_token'];
   		$customerId = Cfe_Token_Id::createToken(5);;
   		$operatorOfferId = $aSession['sdk_offer'];
   		$identity = new Cfe_Type_Identity("$customerId", $aSession['user_identity'], self::IDENTITY_PROVIDER_ID);
   		
   		$sdkOffer =  Bundles_Sdk_Mapper_Offer::findById($operatorOfferId);
   		
   		if ($sdkOffer->type == 'sub'){
	   		$subbsriptionId = Bundles_Sdk_Mapper_Subscription::insert($aSession['sdk_offer'], 
	   												rand(10000, 99999),
	   												'active',
	   												'REAL',
	   												$statusDate,
	   												$subscriptionRenewDate,
	   												$auth,
	   												$customerId);
	   												
	   		$sub = new Cfe_Type_Subscription($subbsriptionId, $customerId, (int) $operatorOfferId, 'active',
	   										 strtotime($statusDate), strtotime($subscriptionRenewDate), null);
	
	   		$returnValue = new Cfe_Stub_Provider_Type_UserAndSubscription($identity, $sub);
	   		
	   		$array['UserAndSubscription'] = $returnValue;
	   		$this->log('UserAndSubscription => '.print_r($returnValue));
   		}else {
   			
   			$actId = Bundles_Sdk_Mapper_Act::insert((int) $operatorOfferId, 'active', 'REAL',
   														 $statusDate, $auth, $customerId);
   			$act = new Cfe_Type_Act($actId, $customerId, (int) $operatorOfferId, strtotime($statusDate));										 
 			$returnValue =  new Cfe_Stub_Provider_Type_UserAndAct($identity, $act);
 			$array['UserAndAct'] = $returnValue;
 			
   		}
   		
   		Class_Common_SessionHandler::addToSession($sSessionId,$array);
   		
   		return $returnValue;
    }
      
    /**
     * Return renew date
     * @param string $sSessionId
     * @param array $aOptional (unused)
     * @return boolean
     */
    public function getRenewDate($id)
    {
       $sub = Bundles_Sdk_Mapper_Subscription::findById($id);
       return $sub->nextRenewDate;
    }


    /**
     * Store data in the user_data_bt table
     * @param string $sSessionId
     * @param array $aOptional (unused)
     * @return boolean
     */
    public function createFinalize($sSessionId, $aOptional = array())
    {
        return true;
    }
   
    /**
     * @param string $sSessionId the identifier of the creation session
     * @param array $aOptional collection of Cfe_Stub_Provider_Type_MethodParams
     * @return array('isStopped','isBilled')
     * @throws ProviderException_Server_Permanent
     */
    public function finalizeUnsubscription($sSessionId,$aOptional=array()) {
        
        $arrStop=array('isStopped'=>true,'isBilled'=>false);
        return $arrStop;
    }
    
    /**
     * Find or add the customer identity
     * @param string $sSessionId
     * @param string $sNextLinkOK
     * @param string $sNextLinkNOK
     * @param string $aOptional
     * @return boolean
     */
    public function getUnsubscriptionUrl($sSessionId,$sNextLinkOK,$sNextLinkNOK,$aOptional=array()) {
       
    }
}