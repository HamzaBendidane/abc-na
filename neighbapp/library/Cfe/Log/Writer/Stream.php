<?php
/**
 * Cfe Framework
 *
 *
 * @category   Cfe
 * @package    Cfe_Log
 * @subpackage Cfe_Log_Writer
 * @copyright  sk
 */

/** Zend_Log_Writer_Abstract */
require_once 'Zend/Log/Writer/Stream.php';

/**
 * @category   Cfe
 * @package    Cfe_Log
 * @subpackage Cfe_Log_Writer
 */
class Cfe_Log_Writer_Stream extends Zend_Log_Writer_Stream
{
	protected $_url = null;
	protected $_mode = null;
	protected $_init = false;

	/**
	 * Class Constructor
	 *
	 * @param array|string|resource $streamOrUrl Stream or URL to open as a stream
	 * @param string|null $mode Mode, only applicable if a URL is given
	 * @return void
	 * @throws Zend_Log_Exception
	 */
	public function __construct($streamOrUrl, $mode = null)
	{
		// Setting the default
		if (null === $mode) {
			$mode = 'a';
		}

		if (is_resource($streamOrUrl)) {
			if (get_resource_type($streamOrUrl) != 'stream') {
				require_once 'Zend/Log/Exception.php';
				throw new Zend_Log_Exception('Resource is not a stream');
			}

			if ($mode != 'a') {
				require_once 'Zend/Log/Exception.php';
				throw new Zend_Log_Exception('Mode cannot be changed on existing streams');
			}

			$this->_stream = $streamOrUrl;
		} else {
			if (is_array($streamOrUrl) && isset($streamOrUrl['stream'])) {
				$this->_url = $streamOrUrl['stream'];
			} else {
				$this->_url = $streamOrUrl;
			}
			$this->_mode = $mode;
		}

		$this->_formatter = new Zend_Log_Formatter_Simple();
	}

	protected function init() {
		if(is_null($this->_stream) && !$this->_init) {
			$this->_init = true;
			if(!preg_match('~^[a-zA-Z]+\://~', $this->_url)) {
				$pathname = dirname($this->_url);
				if(!file_exists($pathname)) {
					if(!mkdir($pathname, 0777, true)) {
						require_once 'Zend/Log/Exception.php';
						throw new Zend_Log_Exception("\"$pathname\" cannot be created");
					}
				}
			}
			if (! $this->_stream = @fopen($this->_url, $this->_mode, false)) {
				require_once 'Zend/Log/Exception.php';
				throw new Zend_Log_Exception('"'.$this->_url.' cannot be opened with mode '.$this->_mode.'"');
			}
		}
		return !is_null($this->_stream);
	}

	/**
	 * Create a new instance of Zend_Log_Writer_Stream
	 *
	 * @param  array|Zend_Config $config
	 * @return Zend_Log_Writer_Stream
	 */
	static public function factory($config)
	{
		$config = self::_parseConfig($config);
		$config = array_merge(array(
            'stream'    => null,
            'mode'      => null,
            'timestamp' => 'Y-m-d',
		), $config);

		if(is_string($config['stream'])) {
			$config['stream'] = str_replace('%timestamp%', date($config['timestamp']), $config['stream']);
		}
		$streamOrUrl = isset($config['url']) ? $config['url'] : $config['stream'];

		return new self(
		$streamOrUrl,
		$config['mode']
		);
	}

	/**
	 * Write a message to the log.
	 *
	 * @param  array  $event  event data
	 * @return void
	 * @throws Zend_Log_Exception
	 */
	protected function _write($event)
	{
		if($this->init()) {
			parent::_write($event);
		}
	}

}
