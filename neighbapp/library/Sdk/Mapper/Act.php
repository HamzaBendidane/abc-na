<?php
class Bundles_Sdk_Mapper_Act extends Bundles_Common_Mapper_Abstract
{
     /**
     * Get the record corresponding to the provided id
     * @param int $id
     * @throws Cfe_Assertion_Exception
     * @return Bundles_Sdk_Model_Act
     */
    public static function findById($id)
    {
        self::_loadConnection();
        $select = self::$_connection->select()
                                    ->from(array('ud' => 'act_sdk'))
                                    ->where('ud.id = ?', $id);

        return new Bundles_Sdk_Model_Act(self::$_connection->fetchRow($select));
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
                                    ->from(array('ud' => 'act_sdk'))
                                    ->where('ud.status = ?', $status);
                                     
        $results = self::$_connection->fetchAll($select);
        foreach($results as $record)
        {
            $output[] = new Bundles_Sdk_Model_Act($record);
        }
        return $output;
    }
    /**
     * Insert a record into the user_data_bt table
     * @param int $offerId
     * @param string $status
     * @param string $statusSource
     * @param string $statusDate
     * @param string $auth
     * @throws Cfe_Assertion_Exception
     * @return int
     */
    public static function insert($offerId, $status,$statusSource,$statusDate,$auth,$customerId)
    {
        return self::_insert('act_sdk', new Bundles_Sdk_Model_Act(array(
            'offer_id' => $offerId,
            'customer_id' => $customerId,
	        'status' => $status,
	        'status_source' => $statusSource,
	        'status_date' => $statusDate,
	        'auth' => $auth
        )));
    }

     /**
     * Update a record based on given id.
     *  NOTE: if the result is 1 that means 1 row affected, thus a success
     * @param string $id
     * @param string $status
     * @return bool
     */
    public static function updateStatusById($id, $status)
    {
    	
        $properties = array( 'status' => $status );
             
        return self::_update(
            'act_sdk',
            $properties,
            "id = '$id'"
        );
    }
}