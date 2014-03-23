<?php
class Bundles_Sdk_Mapper_Offer extends Bundles_Common_Mapper_Abstract
{

     /**
     * Get the record corresponding to the provided offer id
     * @param int $id
     * @return Bundles_Sdk_Model_Offer
     */
    public static function findById($offerId)
    {
        self::_loadConnection();
        $select = self::$_connection->select()
            ->from(array('o' => 'offer_sdk'))
            ->where('o.id = ?', $offerId);

        return new Bundles_Sdk_Model_Offer(self::$_connection->fetchRow($select));
    }
    
    /**
     * @param array $params
     * @return int
     */
    public static function insert($params)
    {
        $offer = new Bundles_Sdk_Model_Offer($params);
        return self::_insert('offer_sdk', $offer);
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        return Bundles_Common_Mapper_Abstract::_delete('offer_sdk', 'id = '.$id);
    }

}