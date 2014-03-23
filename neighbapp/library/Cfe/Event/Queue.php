<?php

require_once 'Zend/Queue/Stomp/Client.php';
require_once 'Zend/Queue/Stomp/Client/Connection.php';
require_once 'Zend/Queue/Stomp/Frame.php';

class Cfe_Event_Queue {
	/**
	 * @var bool
	 */
	protected $connected = false;
	/**
	 * @var Zend_Queue_Stomp_Client
	 */
	protected $queue = null;

    /**
     *
     * ini file containing the parameters to initialize the event logger
     * @var string
     */
    static protected $iniFile = 'eventLog.ini';

	/**
	 *
	 * logger user to log the events
	 * @var Zend_Log
	 */
	static protected $logger;

	/**
	 *
	 * get the logger to use for the events
	 * @return Zend_Log
	 */
	static protected function getLogger() {
		if(!isset(self::$logger)) {
			$iniFile = Cfe_Config_Helper::getFullPath(self::$iniFile);
			$config = new Cfe_Config_Ini($iniFile, Cfe_Utils::getPlatform());
			self::$logger = Zend_Log::factory($config);
		}
		return self::$logger;
	}

	public function __construct($host, $port = 61613) {
		try {
			$client = new Zend_Queue_Stomp_Client('tcp',$host,$port,'Cfe_Queue_Stomp_Client_Connection', 'Cfe_Queue_Stomp_Frame');
			$this->queue = $client;
			$frame = $client->createFrame();
			$frame->setHeader('accept-version', '1.0,1.1');
			$frame->setHeader('host', $host);
			$frame->setCommand('CONNECT');
			$client->send($frame);
			if(!$client->canRead()) {
				Cfe_Log_Helper::getLogger()->log('[Cfe_Event] can\'t connect to the queue : no response', Zend_Log::ERR);
				throw new Exception('can\'t connect to the queue : no response');
			} elseif(!($response = $client->receive())) {
				Cfe_Log_Helper::getLogger()->log('[Cfe_Event] can\'t connect to the queue : unknown case', Zend_Log::ERR);
				throw new Exception('can\'t connect to the queue : unknown case');
			} elseif($response->getCommand() != 'CONNECTED') {
				Cfe_Log_Helper::getLogger()->log('[Cfe_Event] can\'t connect to the queue : '.$response->getBody(), Zend_Log::ERR);
				throw new Exception('can\'t connect to the queue : '.$response->getBody());
			} else {
				$this->connected = true;
			}
		} catch (Exception $e) {
		}
	}

	public function send($queue, $message) {
		if($this->connected) {
			$frame = $this->queue->createFrame();
			$frame->setAutoContentLength(false);
			$frame->setHeader('destination', $queue);
			$frame->setCommand('SEND');
			$frame->setBody($message);
			try {
				$this->queue->send($frame);
			} catch (Exception $e) {
				$this->connected = false;
			}
		}
		self::getLogger()->log('EVENT '.$message, Zend_Log::INFO);
	}

	public function __destruct() {
		if( $this->connected
		&& $this->queue instanceof Zend_Queue_Stomp_Client
		&& $this->queue->getConnection() instanceof Zend_Queue_Stomp_Client_Connection
		) {
			try {
				$this->queue->getConnection()->close();
			} catch (Exception $e) {}
		}
	}
}
