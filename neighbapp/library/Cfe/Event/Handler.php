<?php

class Cfe_Event_Handler {

    const SUCCESS                     = 0;
    const ERROR_NO_CONTROLLER_DEFINED = 1;
    const ERROR_INVALID_CONTROLLER    = 2;
    const ERROR_INVALID_QUEUE         = 5;
    const ERROR_INVALID_MESSAGES      = 3;
    const ERROR_SERVER                = 4;

    const MAX_TOTAL_MEMORY            = 67108864;
    const MAX_PROCESSING_MEMORY       = 16777216;
    const MAX_PROCESSING_TIME         = 300;
    const MAX_MESSAGES                = 1048576;
    const MAX_MESSAGES_BETWEEN_MEMORY_CHECK = 1024;
    const MAX_MESSAGES_PER_READ       = 128;

    protected function getArgs() {
        global $argv,$argc;
        if($argc <= 2) {
            echo "usage : EventListener [controller_name]\n";
            exit(self::ERROR_NO_CONTROLLER_DEFINED);
        }
        if(!preg_match('~stomp://(?P<host>(?:[a-zA-Z\d-]++)(?:\.[a-zA-Z\d-]++)*)(?P<port>\:[\d]{1,5})?(?P<name>/queue(?:/[a-zA-Z][a-zA-Z\d]*+)++)~', $argv[2], $match)) {
            echo "$queueName is not a valid queue\n format: stomp://[host][:[port]]/queue/my/queue/hierachy\n";
            exit(self::ERROR_INVALID_QUEUE);
        }
        $queue = array_intersect_key($match, array_flip(array('host', 'port', 'name')));
        if($queue['port'] == '') {
            $queue['port'] = 61613;
        }
        $queue['name'] = '/queue/'.str_replace('/', '.', substr($queue['name'],7));
        $controllerName = $argv[1];
        $controllerClass = 'Event_Controller_'.trim($controllerName);

        try {
            $controller = new $controllerClass();

        } catch (Exception $e) {
            echo "$controllerName is not a valid Event Controller\n";
            exit(self::ERROR_INVALID_CONTROLLER);
        }
        return array($controller, $queue);
    }

    public function handle() {
        list($controller, $queueParams) = $this->getArgs();
        /* @var $controller Cfe_Event_Controller */
        $controller->init();
        $stompClient = new Zend_Queue_Stomp_Client('tcp',$queueParams['host'],$queueParams['port'],'Cfe_Queue_Stomp_Client_Connection', 'Cfe_Queue_Stomp_Frame');
        $adapter = new Cfe_Queue_Adapter_Activemq(array('driverOptions' => array('stompClient' => $stompClient)));
        $queue = new Zend_Queue($adapter, array(Zend_Queue::NAME => $queueParams['name']));
        $startTime = microtime(true);
        $nbMessages = 0;
        $return = self::SUCCESS;
        $initMem = memory_get_peak_usage(true);
        $toAck = array();
        try {
            while($nbMessages < self::MAX_MESSAGES) {
                $nbIntermediateMessages =0;
                while($nbIntermediateMessages < self::MAX_MESSAGES_BETWEEN_MEMORY_CHECK) {
                    $controller->beginTransaction();
                    $messages = $queue->receive(self::MAX_MESSAGES_PER_READ, 1);
                    $nbIntermediateMessages += $messages->count();
                    foreach ($messages as $message) {
                        /* @var $message Zend_Queue_Message */
                        try {
                            $obj = json_decode($message->body);
                            if(is_null($obj)) {
                                echo "client error [message_id:{$message->message_id}] unable to decode json object : {$message->body}\n";
                                $queue->deleteMessage($message);
                                continue;
                            }
                            try {
                                $event = new Cfe_Event($obj->origin, $obj->type,(array) $obj->values, $obj->timestamp);
                            } catch (Exception $e){
                                echo "client error [message_id:{$message->message_id}] unable to create Event object {$message->body}\n".$e->getMessage()."\n";
                                $queue->deleteMessage($message);
                                continue;
                            }
                            $controller->processEvent($event);
                            $nbMessages += 1;
                        } catch (Cfe_Utils_Exception_Client $e) {
                            echo "client error [message_id:{$message->message_id}] on {$message->body}\n".$e->getMessage()."\n";
                            $queue->deleteMessage($message);
                            continue;
                        } catch (Exception $e) {
                            if($message->redelivered) {
                                echo "server error [message_id:{$message->message_id}] server error twice on the same message {$message->body}\n".$e->getMessage()."\n";
                                $queue->deleteMessage($message);
                            }
                            throw $e;
                        }
                        $toAck[] = $message;
                    }
                    $controller->commitTransaction();
                    while($message = array_shift($toAck)) {
                        $queue->deleteMessage($message);
                    }
                    if((microtime(true) - $startTime) > self::MAX_PROCESSING_TIME) {
                        break;
                    }
                }
                $mem = memory_get_peak_usage(true);
                if (($mem > self::MAX_TOTAL_MEMORY) || (($mem - $initMem) > self::MAX_PROCESSING_MEMORY)) {
                    echo "exceeding memory : $mem Bytes\n";
                    echo "processed $nbMessages messages\n";
                    break;
                }
                if((microtime(true) - $startTime) > self::MAX_PROCESSING_TIME) {
                	$processingTime = intval(microtime(true) - $startTime);
                	echo "exceeding time limit : $processingTime s\n";
                    break;
                }
            }
            echo "processed $nbMessages messages\n";
        } catch (Exception $e) {
            try {
                $controller->commitTransaction();
                while($message = array_shift($toAck)) {
                    $queue->deleteMessage($message);
                }
            } catch (Exception $e) {}
            echo "server error [message_id:{$message->message_id}]  on {$message->body}\n".$e->getMessage()."\n";
            $return = self::ERROR_SERVER;
        };

        exit($return);
    }
}
