<?php

/**
 * Transaction  model DB class
 * @author Jeyaganesh RANJIT
 */
class Class_Db_Transaction extends Class_Db_Abstract {

    protected $_name = 'transaction';
    protected $_adapter = 'neighbapp';

    
    public function getDetailTransaction($transactionId){
        $detail = array();
        
        $queryDetail = $this->getAdapter()->select()
                ->from($this->_name)
                ->join("transaction_type", "transaction_type.id = $this->_name.transaction_type_id",array("type"))
                ->join("object", "object.id = $this->_name.object_id",array("description"))
                ->join("image", "image.id = object.id_image",array("url"))
                ->join("paiement", "paiement.id = transaction_type.paiment_id",array("type as paiement_type"))
                ->where("$this->_name.id = ?",$transactionId);
        $detail =  $this->getAdapter()->fetchRow($queryDetail);
        
        return ($detail)?$detail:false;
    }
    
    
    /**
     * Get All user transactions
     * @param int $userId
     * @return array $transactions
     */
    public function GetAllUserTransaction($userId){
        $transactions = $users = array();
        
        $queryUsers = $this->getAdapter()->select()
                ->from("user_transaction_link")
                ->joinLeft("$this->_name", "$this->_name.id = user_transaction_link.transaction_id")
                ->where('user_transaction_link.user_id = ?',$userId)
                ->order("user_transaction_link.creation_date DESC");
        
        $transactions = $this->getAdapter()->fetchAll($queryUsers);
        return ($transactions)?$transactions: array();
    }
    
}
