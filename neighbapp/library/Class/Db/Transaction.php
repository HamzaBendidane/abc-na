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
    
}
