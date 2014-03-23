<?php

abstract class Cfe_Event_Controller_AbstractMdmController extends Cfe_Event_Controller{

	const CLASS_PREFIX		= 'Cfe_Ref';
	const BASECLASS_NAME	= 'BaseClass';
	const METACLASS_NAME	= 'MetaClass';
	const EVENT_ORIGIN		= 'mdm';

	protected $initialized = false;

	public function initBaseClass() {
		if(!$this->initialized) {
			Cfe_Ref_BaseClass::init($this->getDatabaseConfig());
			$this->initialized = true;
		}
	}

	public function beginTransaction(){
		parent::beginTransaction();
		$this->initBaseClass();
	}

	/**
	 * Called to handle new events
	 *
	 * @param Cfe_Event $event
	 */
	public function processEvent(Cfe_Event $event){
		if($event->getOrigin() != self::EVENT_ORIGIN){
			throw new Cfe_Utils_Exception_Client('Event origin is not '.self::EVENT_ORIGIN);
		}

		$classname = $this->getClassnameFromEventType($event->getType());

		if(!class_exists($classname, true)){
			throw new Cfe_Utils_Exception_Client("Class $classname does not exist.");
		}

		if(!is_subclass_of($classname, self::CLASS_PREFIX.'_'.self::BASECLASS_NAME)){
			throw new Cfe_Utils_Exception_Client("Class $classname must be subclass of ".self::CLASS_PREFIX.'_'.self::BASECLASS_NAME);
		}

		$obj_erp = call_user_func(array($classname, '_createFromArray'), $event->getValues());

		$this->handleObject($obj_erp);
	}

	/**
	 * @param Cfe_Ref_BaseClass $obj_erp
	 */
	abstract public function handleObject($obj_erp);

	/**
	 *
	 * @param unknown_type $eventType
	 */
	protected function getClassnameFromEventType($eventType){
		return str_replace(self::EVENT_ORIGIN."/", self::CLASS_PREFIX."_", $eventType);
	}

	/**
	 * See Sample Confid below
	 *
	 * @return Zend_Config
	 * Zend_Config(
	 *     array(
	 *         'adapter' => $database_adapter,
	 *         'params'  => array(
	 *             'host'     => $host,
	 *             'dbname'   => $dbname,
	 *             'username' => $username,
	 *             'password' => $password
	 *         )
	 *    )
	 *)
	 */
	abstract protected function getDatabaseConfig();

	/*
	 Sample implementation of getDatabaseConfig
	 protected function getDatabaseConfig(){
	//
	return new Zend_Config(
			array(
					'database' => array(
							'adapter'  => 'Mysqli',
							'params' => array(
									'host'     => 'localhost',
									'dbname'   => 'mdm_test',
									'username' => 'mdm_test',
									'password' => 'mdm',
							),
					)
			)
	);
	}
	*/
}