<?php
class Bundles_Sdk_Model_Act extends Bundles_Common_Model_Abstract
{
	public $_type = "D";
	/**
     *  id 
     * @var integer
     */
    public $id;
     /**
     * customer id (from accounting)
     * @var integer
     */
    public $customerId;

     /**
     * operator offer id (from offer_sfr)
     * @var string
     */
    public $offerId;

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
    public $statusDate;
    
    /**
     * pendingStatusDate
     * @var string
     */
    public $pendingStatusDate;
    
     /**
     * auth
     * @var string
     */
    public $auth;
    
    /**
     * This array maps the fields between this class and the db
     */
    protected $dbMap = array(
        'id' => 'id',
        'customer_id' => 'customerId',
        'offer_id' => 'offerId',
        'status' => 'status',
        'status_source' => 'statusSource',
        'status_date' => 'statusDate',
        'pending_status_date' => 'pendingStatusDate',
        'auth' => 'auth'
    );
}