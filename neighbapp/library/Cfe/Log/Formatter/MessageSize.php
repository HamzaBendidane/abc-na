<?php
/**
 * Cfe Framework
 *
 * @category   Cfe
 * @package    Cfe_Log
 * @subpackage Cfe_Log_Formatter
 * @copyright  Copyright (c) sk
 */
/** Zend_Log_Formatter_Abstract */
require_once 'Zend/Log/Formatter/Abstract.php';
/**
 * @category   Cfe
 * @package    Cfe_Log
 * @subpackage Cfe_Log_Formatter
 * @copyright  Copyright (c) sk
 */
class Cfe_Log_Formatter_MessageSize extends Zend_Log_Formatter_Simple
{
    /**
     * @var array
     */
    protected $_sizes = array(Zend_Log::EMERG => 16000,
    Zend_Log::ALERT => 16000, Zend_Log::CRIT => 16000, Zend_Log::ERR => 16000,
    Zend_Log::WARN => 16000, Zend_Log::NOTICE => 16000, Zend_Log::INFO => 16000,
    Zend_Log::DEBUG => 16000);
    /**
     * Class constructor
     *
     * @param  null|string  $format  Format specifier for log messages
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct ($format = null, $sizes = array())
    {
        parent::__construct($format);
        if (! empty($sizes) && is_array($sizes)) {
            $this->_sizes = $sizes + $this->_sizes;
        }
    }
    /**
     * Factory for Cfe_Log_Formatter_MessageSize classe
     *
     * @param array|Zend_Config $options
     * @return Cfe_Log_Formatter_MessageSize
     */
    public static function factory ($options)
    {
        $format = null;
        $sizes = array();
        if (null !== $options) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            if (array_key_exists('format', $options)) {
                $format = $options['format'];
            }
            if (array_key_exists('sizes', $options)) {
                $sizes = $options['sizes'];
            }
        }
        return new self($format, $sizes);
    }
    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format ($event)
    {
        $size = $this->_sizes[$event['priority']];
        if(strlen($event['message']) > $size)
        {
            $event['message'] = substr($event['message'], 0, $size - 3).'...';
        }
        return parent::format($event);
    }
}