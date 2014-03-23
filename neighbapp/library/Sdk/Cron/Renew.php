<?php
class Bundles_Sdk_Cron_Renew extends Bundles_Common_Cron_Renew
{
    /**
     * A prefix before placed before the message
     *
     * @access protected
     * @var string
     */
    protected $logPrefix = 'RENEW SDK ';

    /**
     * The drivers to communicate with the aggregators / billing operators
     *
     * @access protected
     * @var Bundles_Sdk_External
     */
    protected $bundle;
    
    /**
     * The mapper used to communicate with the database
     * 
     * @access protected
     * @var Bundles_Sdk_Mapper_Subscription
     */
    protected $mapper;
    
    /**
     * The bundle used
     * @var Bundles_Sdk_External
     */
    protected $providerBundle;

    
    public function init() {
        parent::init();
        
        $this->mapper = new Bundles_Sdk_Mapper_Subscription();
        if($this->isTest())
            $this->bundle = new Bundles_Sdk_External();
        else
            $this->bundle = new Bundles_Sdk_External();
        
        $this->initBundle($this->bundle,get_class($this->bundle));
        
    }
    
    /**
     * Get the id of the bundle
     * @param object $oSubscription
     * @return integer
     */
    protected function getBundleId($oSubscription) {
        
        //@todo get the right billing operator
        if(!isset($this->providerBundle))
            $this->providerBundle = Class_Mapper_Provider_Bundle::findByAggregatorCanalAndBillingOp('Sdk', 'external','');
        
        return $this->providerBundle->id;
    }
    
    /**
     * Set the status of the accounts to the 'processing' state
     *
     * @access protected
     * @param integer $time the timestamp of the date
     * @return boolean 
     */
    protected function setAccountsToProcess($time)
    {
        return $this->mapper->setProcessActiveSubscriptions($time);
    }
    
    /**
     * Get the accounts to renew
     *
     * @access protected
     * @param integer $limit the maximum number of elements to get
     * @return array|boolean The accounts to renew or false
     */
    protected function getAccountsToProcess($limit)
    {
        return $this->mapper->findProcessingSubscriptions(0,$limit);
    }
    
    /**
     * Will we treat this subscription ?
     *
     * Say if the given subscription will be treeted or not from the information
     * in our database.
     *
     
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
    protected function willBeProcessed($subscription) {
        return true;
    }
    
    /**
     * Get the status from the operator
     *
     * Call the provider to check the status of a subscription
     * on the operator platform
     *
     
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return string
     */
    protected function statusAggregator($subscription) {
    	try {
	        $iUserDataId = $subscription->sdkSubscriptionId;
	        $return = $this->bundle->status($iUserDataId);
    	}
        catch (Exception $e){
        	$return["status"] = 'error';
        }
        return $return;
    }
    
    /**
     * Renew the subscription on the operator platform
     *
     * Call the drivers (or the provider) to renew the given subscription on the
     * opertor platform.
     *
     
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
    protected function renewAggregator($subscription) {
        return $this->bundle->renew($subscription->id);
    }

    /**
     * Renew the subscription in our database
     *
     * Call the billing (or accounting) to renew the given subscription in our
     * database.
     *
     
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @param string $sNextRenewDate
     * @return boolean
     */
    protected function renewDatabase($subscription,$nextRenewDate) {
                           
        $day = 86400; // = 24h * 60m * 60s => 1 day in seconds
        
	    $nextRenewDate = date('Y-m-d H:i:s',strtotime($subscription->nextRenewDate)+7*$day);
        
        $this->mapper->updateStatusById($subscription->id, 'active', $nextRenewDate);
        return true;
    }
    
    /**
     * Stop the subscription on the operator platform
     *
     * Call the drivers (or the provider) to stop the given subscription on the
     * opertor platform.
     *
     
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
    protected function stopAggregator($subscription,$aOptional=array()) {
        return true;
    }
    
    /**
     * Stop the subscription in our database
     *
     * Call the billing (or accounting) to stop the given subscription in our
     * database.
     *
     
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
    protected function stopDatabase($subscription) {
        $this->mapper->updateStatusById($subscription->id, 'stop', null, date('Y-m-d H:i:s',$this->time));
        return true;
    }
    
    /**
     * active the subscription in our database
     *
     * Call the billing (or accounting) to stop the given subscription in our
     * database.
     *
     
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
    protected function activeDatabase($subscription) {
        $this->mapper->updateStatusById($subscription->id, 'active');
        return true;
    }
    
    /**
     * Update the subscription status in our database to 'active'
     *
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
    protected function setIgnoredDatabase($subscription) {
        $this->mapper->updateStatusById($subscription->id, 'active');
        return true;
    }
    
    
    /**
     * Update the subscription status in our database to 'suspend'
     *
     * @access protected
     * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
    protected function setSuspendDatabase($subscription) 
    {
        $this->mapper->updateStatusById($subscription->id, 'suspend',date('Y-m-d 00:00:00', strtotime('+1 day')));
        $subscription->nextRenewDate = date('Y-m-d 00:00:00', strtotime('+1 day'));
        return true;
    }
    
    
    /**
    * Update the subscription status in our database to 'pending'
    *
    * @access protected
    * @param Bundles_Sdk_Model_Subscription $subscription the active subscription
     * @return boolean
     */
     protected function setPendingDatabase($subscription) 
     {
        $this->mapper->updateStatusById($subscription->id, 'pending');
        return true;
     }
     /**
     * Update the subscription status in our database to 'error'
     *
     * @abstract
     * @access protected
     * @param Bundles_Common_Model_Abstract $subscription the active subscription
     * @return boolean
     */
    protected function setErrorDatabase($subscription){
    	;
    }
}