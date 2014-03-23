<?php

class Class_Mq_Adapter {

    private $_channel;
    private $_conection;
    private $_queue;

    public function __construct() {
        require_once('php-amqplib/amqp.inc');
        //TODO
        $this->_conection = new AMQPConnection('46.231.129.237', 5672, 'neighbapp', '123neighbapp');
        $this->_channel = $this->_conection->channel();
    }

    public function connect($queue) {
        $this->_queue = $queue;
        $this->_channel->queue_declare($queue, false, true, false, false);
    }

    public function publish($msg) {
        $this->_channel->basic_publish($msg, '', $this->_queue);
    }

    public function disConnect() {
        $this->_channel->close();
        $this->_conection->close();
    }

    public function consume($callback) {
        $this->_channel->basic_qos(null, 1, null);
        $this->_channel->basic_consume($this->_queue, '', false, false, false, false, $callback);
    }

    public function getCallbacks() {
        return $this->_channel->callbacks;
    }

    public function wait() {
        $this->_channel->wait();
    }
}