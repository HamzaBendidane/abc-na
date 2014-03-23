<?php

/**
 * Class for user_data_sfr
 * @author Erik Weinmaster <erik.weinmaster@sk.fr>
 */
class Bundles_Sdk_Model_Subscription extends Bundles_Common_Model_Abstract
{
	public $_type = "D";
	/**
     *  id 
     * @var integer
     */
    public $id;
     /**
     * subscription id (from accounting)
     * @var integer
     */
    public $subscriptionId;

     /**
     * operator offer id (from offer_sfr)
     * @var string
     */
    public $offerId;
    
      /**
     * customer id 
     * @var int
     */
    public $customerId;

     /**
     * subscription id from sfr
     * @var string
     */
    public $sdkSubscriptionId;

    /**
     * status
     * @var string
     */
    public $status;
    
    /**
     * statusSource
     * @var string
     */
    public $statusSource;
    
    /**
     * statusDate
     * @var string
     */
    public $startDate;
        
    /**
     * subscriptionRenewDate
     * @var string
     */
    public $nextRenewDate;
    
     /**
     * auth
     * @var string
     */
    public $auth;
    
     /**
     * endDate
     * @var string
     */
    public $endDate;
    
    /**
     * This array maps the fields between this class and the db
     */
    protected $dbMap = array(
        'id' => 'id',
        'offer_id' => 'offerId',
        'sdk_subscription_id' => 'sdkSubscriptionId',
        'status' => 'status',
        'status_source' => 'statusSource',
        'start_date' => 'startDate',
        'next_renew_date' => 'nextRenewDate',
        'auth' => 'auth',
        'end_date' => 'endDate',
        'customer_id' => 'customerId'
    );
}