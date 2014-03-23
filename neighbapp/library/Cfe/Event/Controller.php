<?php

abstract class Cfe_Event_Controller {
    private $transaction;

    /**
     *
     * called to initialize the controller if anything must be defined before processing the first message.
     */
    public function init() {

    }

    /**
     *
     * called to process each message
     * @param Cfe_Event $event
     */
    abstract public function processEvent(Cfe_Event $event);


    /**
     * begin a transaction.
     * all messages from this point to the call of commit transaction should be held in a transaction
     * no message sent after beginTransaction will be acknowledged to the queue until commitTransaction is called.
     *
     * @throws Exception if anything append preventing the transaction to be created.
     */
    public function beginTransaction() {
        if($this->transaction == 0) {
            $this->transaction++;
        } else {
            throw new Exception('a transaction already began');
        }
    }

    /**
     * commit a transaction
     * at this point the controller is asked to write all the change to the persistence layer
     * no message sent after beginTransaction will be acknowledged to the queue until commitTransaction is called.
     *
     * @throws Exception
     */
    public function commitTransaction() {
        if($this->transaction > 0) {
            $this->transaction--;
        } else {
            throw new Exception('no transaction to commit');
        }
    }

}