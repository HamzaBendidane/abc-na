<?php
/**
 * Cfe Framework
 */

/**
 * @see Zend_Queue_Stomp_Client_ConnectionInterface
 */
require_once 'Zend/Queue/Stomp/Client/ConnectionInterface.php';

/**
 * The Stomp client interacts with a Stomp server.
 *
 * @category   Cfe
 * @package    Cfe_Queue
 * @subpackage Stomp
 */
class Cfe_Queue_Stomp_Client_Connection extends Zend_Queue_Stomp_Client_Connection
{
    const READ_TIMEOUT_DEFAULT_USEC = 100000; // 0.1 seconds
    const READ_TIMEOUT_DEFAULT_SEC = 0; // 0 seconds

    /**
     * Write a frame to the stomp server
     *
     * example: $response = $client->write($frame)->read();
     *
     * @param Zend_Queue_Stom_FrameInterface $frame
     * @return $this
     */
    public function write(Zend_Queue_Stomp_FrameInterface $frame)
    {
        $this->ping();
        $output = $frame->toFrame();
        $started = false;
        while($bytes = fwrite($this->_socket, $output, strlen($output))) {
            if($bytes === strlen($output)) {
                return $this;
            }
            $output = substr($output, $bytes);
            $started = true;
        }
        if (!$bytes) {
            require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception($started?'Unable to write the message completly':'No bytes written');
        }
        return $this;
    }
    /**
     * Reads in a frame from the socket or returns false.
     *
     * @return Zend_Queue_Stomp_FrameInterface|false
     * @throws Zend_Queue_Exception
     */
    public function read()
    {
        $this->ping();

        $response = '';

        $proc_header = true;
        while (($data = stream_get_line ($this->_socket, 8192, "\0\n")) !== false) {
            $response .= $data;
            if(strlen($data) < 8192) {
                break;
            }
        }
        if ($response === '') {
            return false;
        }
        $frame = $this->createFrame();
        $frame->fromFrame($response);
        return $frame;
    }

    /**
     * Create an empty frame
     *
     * @return Zend_Queue_Stomp_FrameInterface
     */
    public function createFrame()
    {
        $frame = parent::createFrame();
        $frame->setAutoContentLength(false);
        return $frame;
    }
}
