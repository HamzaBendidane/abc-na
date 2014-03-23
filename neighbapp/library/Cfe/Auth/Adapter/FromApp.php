<?php
/**
 * Adapater pour faire une identification via une application tierce
 * Objectif : récupérer les informations du téléphone
 * Pour une application Web
 */

class Cfe_Auth_Adapter_FromApp extends Zend_Auth_Adapter_DbTable
{

    /**
     * deviceId ios
     *
     * @var udiId
     */
    protected $_deviceId;


    /**
     * id_user crypté
     *
     * @var id_user
     */
    protected $_iduser;

    /**
     * application utilisé
     *
     * @var
     */
    //protected $_application = 'skjaijamais';
    //protected $_application = 'skneverhave';
    
    /**
     * Variable utiles à envoyer à l'application
     * 
     */
    private $_tmpVar = array();
    
    
    /**
     * @var String Model du device Utilisé
     */
    private $_deviceModel = false;
    


    /**
     * __construct() - Sets configuration options
     *
     * @param  Zend_Db_Adapter_Abstract $zendDb If null, default database adapter assumed
     * @param  string                   $tableName
     * @param  string                   $identityColumn
     * @param  string                   $credentialColumn
     * @param  string                   $credentialTreatment
     * @return void
     */
    public function __construct(Zend_Db_Adapter_Abstract $zendDb = null, $tableName = null, $identityColumn = null,
            $credentialColumn = null, $credentialTreatment = null)
    {
        return parent::__construct($zendDb, $tableName, $identityColumn, $credentialColumn, $credentialTreatment);
    }
     
    
    
    /**
     * _authenticateCreateSelect() - This method creates a Zend_Db_Select object that
     * is completely configured to be queried against the database.
     * 
     * COPIE DE LA METHODE ZEND PARENT
     * AJOUT de la jointure avec DEVICE
     *
     * @return Zend_Db_Select
     */
    protected function _authenticateCreateSelect()
    {
        // build credential expression
        if (empty($this->_credentialTreatment) || (strpos($this->_credentialTreatment, '?') === false)) {
            $this->_credentialTreatment = '?';
        }

        $credentialExpression = new Zend_Db_Expr(
            '(CASE WHEN ' .
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_credentialColumn, true)
                . ' = ' . $this->_credentialTreatment, $this->_credential
                )
            . ' THEN 1 ELSE 0 END) AS '
            . $this->_zendDb->quoteIdentifier(
                $this->_zendDb->foldCase('zend_auth_credential_match')
                )
            );

        // get select
        $dbSelect = clone $this->getDbSelect();
        $dbSelect->from($this->_tableName, array('*', $credentialExpression))
                 ->join('device', 
                        'device.user_FK = user.id',
                        array('model', 'from_webapp', 'device.id as id_device'))
                 ->where($this->_zendDb->quoteIdentifier($this->_identityColumn, true) . ' = ?', $this->_identity);
                
        return $dbSelect;
    }
    

    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     * 
     * Si deviceId n'est pas définit, authenticate return FAILURE_GO_START_APP_PROCESS : permet de lancer process de récupération de l'Id du device
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
        
        
        /* Identificate Query : User + Device */
        $dbSelect = $this->_authenticateCreateSelect();
        
      
        // On ne garde que la row avec le device principal
        $resultIdentities = $this->_getPrincipalDevice($this->_authenticateQuerySelect($dbSelect));
        
       
        if ( ($authResult = $this->_authenticateValidateResultSet($resultIdentities)) instanceof Zend_Auth_Result) {
            return $authResult;
        }

        if (true === $this->getAmbiguityIdentity()) {
            $validIdentities = array ();
            $zendAuthCredentialMatchColumn = $this->_zendDb->foldCase('zend_auth_credential_match');
            foreach ($resultIdentities as $identity) {
                if (1 === (int) $identity[$zendAuthCredentialMatchColumn]) {
                    $validIdentities[] = $identity;
                }
            }
            $resultIdentities = $validIdentities;
        }
       
        $authResult = $this->_authenticateValidateResult(array_shift($resultIdentities));

        return $authResult;
    }
       

    /**
     * _authenticateResultFromApp()
     * Message recupération de l'udid avec l'app tièrce
     *
     * @param array $resultIdentity
     * @return Cfe_Auth_Result
     */
    protected function _authenticateResultFromApp($resultIdentity)
    {
        $this->_authenticateResultInfo['code'] =  Cfe_Auth_Result::FAILURE_GO_START_APP_PROCESS;
        $this->_authenticateResultInfo['messages'][] = _('Récupération udid depuis app');
        return $this->_authenticateCreateAuthResult();
    }
    
    /**
     * Message pour afficher que le user ne peut pas se connecter avec ce compte
     */

    protected function _authenticateResultUserDeviceRestriction()
    {
        $this->_authenticateResultInfo['code'] =  Cfe_Auth_Result::FAILURE_USER_DEVICE_RESTRICTION;
        $this->_authenticateResultInfo['messages'][] = _('Sorry, it is not possible to log in because your account is linked to another type of device');
        return $this->_authenticateCreateAuthResult();
    }
    
    

    /*
     * Construit les paramatres à envoyer à l'application user id et time
     * et l'application
     */
    public function getDataForApp(){
        
        $crypt = new Cfe_Token_Aes();
        
        $data = array();
        //$data['application'] = $this->_application;
        
        $data['user_id'] = $crypt->encrypt($this->_tmpVar['userid']);
                
        $data['params'] = '';
        $data['params'] .= 'userid=' . $data['user_id'];
        $data['params'] .= '&time=' . time();
        
        $data['params'] = $crypt->encrypt($data['params']);
               
        // Config webapp selon Pays
        $transaction_config = new Zend_Config_Ini(
                CONFIG_PATH . '/' . Cfe_Utils::getCountry() . '/webapp.ini',
                Cfe_Utils::getPlatform()
        );
        
        
        $config_appli = $transaction_config->appli;
        $config_appli_array = $config_appli->toArray();
        
        /*@todo : tester plusieurs urlscheme*/
        $data['url'] = $config_appli_array[0]['url'];
        $data['scheme'] = $config_appli_array[0]['scheme'];
        
        return $data;
    }
    
    /**
     * _authenticateValidateResult() - This method attempts to validate that
     * the record in the resultset is indeed a record that matched the
     * identity provided to this adapter.
     *
     * @param array $resultIdentity
     * @return Zend_Auth_Result
     */
    protected function _authenticateValidateResult($resultIdentity)
    {
        $zendAuthCredentialMatchColumn = $this->_zendDb->foldCase('zend_auth_credential_match');
            
        if ($resultIdentity[$zendAuthCredentialMatchColumn] != '1') {
            $this->_authenticateResultInfo['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }
    
        unset($resultIdentity[$zendAuthCredentialMatchColumn]);
        
        // si on n'a pas de idDevice, on va le chercher avec l'App tierce
        if(empty($this->_deviceId)){
            
            $this->_tmpVar['userid'] = $resultIdentity['id'];
            return $authResult = $this->_authenticateResultFromApp($resultIdentity);
        }
        
        // test RESTRICTION DEVICE IPAD/IPHONE
        if(!$this->_testDeviceModele($resultIdentity)){
            return $authResult = $this->_authenticateResultUserDeviceRestriction();
        }
        
        
        $this->_resultRow = $resultIdentity;
    
        $this->_authenticateResultInfo['code'] = Zend_Auth_Result::SUCCESS;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->_authenticateCreateAuthResult();
    }

    
    /**
     * Function pour ne garder que le device principal lié à l'utilisateur
     * Si il n'a qu'une ligne : on garde cette ligne
     * Si il y a plusieurs lignes, c'est que le user a créait un ou plusieurs device from_webapp : on ne prend que le device from_webapp = 0;
     * 
     * @todo : Si trop de device envoyés par $resultIdentities , on peut ajouter une function mail pour signaler l'abus
     * 
     * @return Array avec un index 0 le device principal du User
     */
    protected function _getPrincipalDevice($resultIdentities){
        
        // On n'a qu'un device
        if(count($resultIdentities) <= 1 ){
            return $resultIdentities;
        }else{
            foreach($resultIdentities as $resultIdentitie){
                if($resultIdentitie['from_webapp'] == 0){
                    return array( 0 => $resultIdentitie);
                }
            }
        }
        //sinon, on retourn le premier device from_web app créé
        return array( 0 => $resultIdentities[0]);
    }
    
    
    /**
     * Test que l'utilisateur a le bon modele de Device pour ce connecter
     * Si Ipad => peut se connecter qu'avec un Ipad
     * Si device_id == -1 : on return false. Restriction lors de la creation du device
     */
    protected function _testDeviceModele($resultIdentity){
        
        if($this->_deviceId == -1){
            return false;
        } 
        
        if(!$this->_deviceModel){
            throw new Exception('Device model attendu');
        }
               
        // si il est ipad et que le device est ipad => true
        if($resultIdentity['model'] == 'ipad' && $this->_deviceModel == 'ipad'){
            return true;
        }
        // si il est iphone ou ipod, il peut se connecter à ipod et à iphone
        elseif(( $resultIdentity['model'] == 'iphone' || $resultIdentity['model'] == 'ipod' ) 
                && ( $this->_deviceModel == 'iphone' || $this->_deviceModel == 'ipod') ){
             return true;
        }
      
        return false;
    }
    
    /**
     * setDeviceId() - set the value to be used as deviceId
     *
     * @param  string $value
     * @return Cfe_Auth_Adapter_FromApp Provides a fluent interface
     */
    public function setDeviceId($value)
    {
        $this->_deviceId = $value;
        return $this;
    }
    
    /**
     * test User has Good device modele
     *
     * @param  string $model
     * @return Cfe_Auth_Adapter_FromApp Provides a fluent interface
     */
    public function setDeviceModel($model)
    {
        // on attend en retour ipad, iphone, ipod
        $model = strtolower(stristr($model, 'ipod') ? 'ipod' : $model); 
        $this->_deviceModel = $model;
        return $this;
    }
}
