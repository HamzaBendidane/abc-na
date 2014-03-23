<?php
class Bundles_Sdk_Model_Offer extends Bundles_Common_Model_Abstract
{
    /**
     * operator offer id
     * @var integer
     */
    public $id;
    /**
     * @var string
     */
    public $serviceId;
    
     /**
     * @var string
     */
    public $type;

    /**
     * This array maps the fields between this class and the db
     */
    protected $dbMap = array(
        'id' => 'id',
        'service_id' => 'serviceId',
        'type' => 'type'
    );
}