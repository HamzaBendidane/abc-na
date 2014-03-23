<?php

require_once 'Cfe/Event/Queue.php';
require_once 'Cfe/Config/Helper.php';
require_once 'Cfe/Config/Ini.php';
require_once 'Zend/Log.php';
require_once 'Cfe/Assertion/Type.php';
require_once 'Cfe/Utils.php';

class Cfe_Event {
    /**
     *
     * origine component name
     * @var string
     */
    protected $origin;
    /**
     *
     * event's type name
     * @var string
     */
    protected $type;
    /**
     *
     * list of values for this type of event
     * @var array
     */
    protected $values;

    /**
     *
     * timestamp
     * @var int
     */
    protected $timestamp;

    /**
     * @var Cfe_Event_Queue
     */
    static protected $queue = null;

    /**
     *
     * constructor
     * @param string $origin
     * @param string $type
     * @param array $values
     * @param int $timestamp
     */
    public function __construct($origin, $type, array $values = array(), $timestamp = null) {
        Cfe_Assertion_Type::assertString($origin);
        Cfe_Assertion_Type::assertString($type);
        if(!is_null($timestamp)) {
            Cfe_Assertion_Type::assertInt($timestamp);
        } else {
            $timestamp = time();
        }
        $this->setValues($values);
        $this->origin = $origin;
        $this->type = $type;
        $this->timestamp = $timestamp;
    }
    /**
     *
     * set an event value
     * @param string $key
     * @param scalar $value
     */
    public function setValue($key, $value) {
        Cfe_Assertion_Type::assertString($key);
        if(!is_null($value)) {
        	Cfe_Assertion_Type::assertScalar($value);
        }
        $this->values[$key] = $value;
    }
    /**
     *
     * set all the event values
     * @param array $values
     */
    public function setValues(array $values) {
        Cfe_Assertion_Type::assertArray($values);
        foreach ($values as $key => $value) {
            Cfe_Assertion_Type::assertString($key);
            if(!is_null($value)) {
            	Cfe_Assertion_Type::assertScalar($value);
            }
        }
        $this->values = $values;
    }
    /**
     *
     * return the json encoding of the event
     * @return string
     */
    protected function serialize() {
        $tmp = new stdClass();
        $tmp->origin = $this->origin;
        $tmp->type = $this->type;
        $tmp->values = $this->values;
        $tmp->timestamp = $this->timestamp;
        return json_encode($tmp);
    }
    /**
     *
     * send the event
     */
    public function send() {
        if(is_null(self::$queue)) {
            self::$queue = new Cfe_Event_Queue('mbroker.'.$_ENV['PLATFORM_URL'].'0pb.org');
        }
        self::$queue->send('/topic/Cfe.'.str_replace('/', '.', $this->type), $this->serialize());
    }
    /**
     *
     * get the origine component
     * @return string
     */
    public function getOrigin() {
        return $this->origin;
    }
    /**
     *
     * get the type of the event
     * @return string
     */
    public function getType() {
        return $this->type;
    }
    /**
     *
     * get an event value
     * @param string $key
     * @return scalar
     */
    public function getValue($key) {
        Cfe_Assertion_Type::assertString($key);
        Cfe_Assertion_Value::assertKeyExists($key, $this->values);
        return $this->values[$key];
    }
    /**
     *
     * return all the values of the event
     * @return array ($name => $value)
     */
    public function getValues() {
        return $this->values;
    }
    /**
     *
     * return the timesamp
     * @return int
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * return the signature of an event
     * the signature depend on the type AND the list of keys in values
     * this way if the structure of an event change (for example if a new field is added) the signature will change also
     */
    public function getSignature() {
        $keys = array_keys($this->values);
        sort($keys);
        return md5($this->getType().':'.implode(':', $keys));
    }

    public function getJson() {
    	return $this->serialize();
    }
}