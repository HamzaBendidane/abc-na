<?php
class Bundles_Sdk_Mapper_Subscription extends Bundles_Common_Mapper_Abstract
{
    /**
     * Get the record corresponding to the provided id
     * @param int $id
     * @throws Cfe_Assertion_Exception
     * @return Bundles_Sdk_Model_Subscription
     */
    public static function findBySubscriptionId($id)
    {
        self::_loadConnection();
        $select = self::$_connection->select()
                                    ->from(array('ud' => 'subscription_sdk'))
                                    ->where('ud.sdk_subscription_id = ?', $id);
        
        return new Bundles_Sdk_Model_Subscription(self::$_connection->fetchRow($select));
    }
    
     /**
     * Get the record corresponding to the provided id
     * @param int $id
     * @throws Cfe_Assertion_Exception
     * @return Bundles_Sdk_Model_Subscription
     */
    public static function findById($id)
    {
        self::_loadConnection();
        $select = self::$_connection->select()
                                    ->from(array('ud' => 'subscription_sdk'))
                                    ->where('ud.id = ?', $id);

        return new Bundles_Sdk_Model_Subscription(self::$_connection->fetchRow($select));
    }
    
    /**
     * Get the record corresponding to the subscription id
     * @param int $id
     * @throws Cfe_Assertion_Exception
     * @return Bundles_Sdk_Model_Subscription
     */
    public static function findSubscriptionId($id)
    {
        self::_loadConnection();
        $select = self::$_connection->select()
                                    ->from(array('ud' => 'subscription_sdk'))
                                    ->where('ud.sdk_subscription_id = ?', $id);

        return new Bundles_Sdk_Model_Subscription(self::$_connection->fetchRow($select));
    }
    
     /**
     * Get the record corresponding to the provided id
     * @param int $id
     * @throws Cfe_Assertion_Exception
     * @return array
     */
    public static function findSubscriptionByStatus($status)
    {
        self::_loadConnection();
        $select = self::$_connection->select()
                                    ->from(array('ud' => 'subscription_sdk'))
                                    ->where('ud.status = ?', $status);                         
        $results = self::$_connection->fetchAll($select);
        foreach($results as $record)
        {
            $output[] = new Bundles_Sdk_Model_Subscription($record);
        }
        return $output;
    }
    /**
     * Insert a record into the user_data_bt table
     * @param int $offerId
     * @param string $sfrSubscriptionId
     * @param string $status
     * @param string $statusSource
     * @param string $statusDate
     * @param string $subscriptionRenewDate
     * @param string $auth
     * @param string $customerId
     * @throws Cfe_Assertion_Exception
     * @return int
     */
    public static function insert($offerId, $sfrSubscriptionId,$status,$statusSource,$statusDate,
                                        $subscriptionRenewDate,$auth,$customerId)
    {
        return self::_insert('subscription_sdk', new Bundles_Sdk_Model_Subscription(array(
            'offer_id' => $offerId,
            'sdk_subscription_id' => $sfrSubscriptionId,
	        'status' => $status,
	        'status_source' => $statusSource,
	        'start_date' => $statusDate,
	        'next_renew_date' => $subscriptionRenewDate,
	        'auth' => $auth,
            'customer_id' => $customerId
        )));
    }

     /**
     * Update a record based on given id.
     *  NOTE: if the result is 1 that means 1 row affected, thus a success
     * @param int $id
     * @param string $status
     * @param int $statusSource
     * @param string $date
     * @return bool
     */
    public static function updateStatusById($id, $status, $nextRenewDate=null, $endDate=null)
    {
    	
        $properties = array( 'status' => $status );
        
        if(isset($nextRenewDate))
            $properties['next_renew_date'] = $nextRenewDate;
        if(isset($endDate))
            $properties['end_date'] = $endDate;
        
        return self::_update(
            'subscription_sdk',
            $properties,
            "id = '$id'"
        );
    }
     
    public static function setProcessActiveSubscriptions($time)
    {
    	self::_loadConnection();
        $date = date('Y-m-d H:i:s',$time);
        $conditions = "`status` != 'stop' ".
                      "AND `sdk_subscription_id` != 0 ".
                      "AND (`end_date` = '0000-00-00 00:00:00' or end_date is null) ".
                      "AND next_renew_date < '$date'";
      
        $result = self::$_connection->update('subscription_sdk', array('status' => 'processing'), $conditions);
        return $result > 0 ? true : false;
    }
    
    public static function findProcessingSubscriptions($index, $maximum) 
    {
        self::_loadConnection();
        $select = self::$_connection->select()
            ->from(array('u' => 'subscription_sdk'))
            ->where("u.status = 'processing'")
            ->order($orderBy)->limit($maximum,$index);

        $output = array();
        $results = self::$_connection->fetchAll($select);
        foreach($results as $record)
        {
            $output[] = new Bundles_Sdk_Model_Subscription($record);
        }
        return $output;
    }
}