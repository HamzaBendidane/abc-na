<?php

abstract class Cfe_Utils_Ip {
    // exceptions
    const INVALID_IP_STRING = 1;

    const IP_VERSION_4 = 4;
    const IP_VERSION_6 = 6;

    protected $ip;

    /**
     * @param Cfe_Utils_Ip $a
     * @param Cfe_Utils_Ip $b
     * @return int
     */
    static public function _cmp(Cfe_Utils_Ip $a, Cfe_Utils_Ip $b) {
        return $a->cmp($b);
    }

    /**
     * @param Cfe_Utils_Ip $a
     * @param Cfe_Utils_Ip $b
     * @return Cfe_Utils_Ip
     */
    static public function _min(Cfe_Utils_Ip $a, Cfe_Utils_Ip $b) {
        return ($a->cmp($b) < 0)?$a:$b;
    }

    /**
     * @param Cfe_Utils_Ip $a
     * @param Cfe_Utils_Ip $b
     * @return Cfe_Utils_Ip
     */
    static public function _max(Cfe_Utils_Ip $a, Cfe_Utils_Ip $b) {
        return ($a->cmp($b) > 0)?$a:$b;
    }

    /**
     * @param string $ipString
     * @return Cfe_Utils_Ip
     * @throws Cfe_Utils_Exception
     */
    public static function CreateFromIPString($ipString) {
        Cfe_Assertion_Type::assertString($ipString);
        try {
            return Cfe_Utils_Ip_V4::CreateFromIPString($ipString);
        } catch(Exception $e) {
            try
            {
                return Cfe_Utils_Ip_V6::CreateFromIPString($ipString);
            } catch (Exception $e) {
                throw new Cfe_Utils_Exception('invalid ipv4/ipv6 string', parent::INVALID_IP_STRING);
            }
        }
    }

    /**
     * @return int
     */
    abstract public function getIPVersion();

    /**
     * @return string
     */
    public function getIPBin() {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getIPv6String() {
        return implode(':', array_map('bin2hex', str_split($this->getIPv6Bin(),2)));
    }

    /**
     * @return string
     */
    public function getIPv6CompactString() {
        // could be improved to find the longest array of 0000:0000:...
        return preg_replace(array('/(^0(:0)+)|(0(:0)+$)/','/0(:0)+/'), array(':', ''),preg_replace('/(^|:)0{1,3}/','$1',$this->getIPv6String()), 1);
    }

    abstract public function getByteLength();

    /**
     * @param Cfe_Utils_Ip $ip
     * @return int
     */
    abstract public function cmp(Cfe_Utils_Ip $ip);

    /**
     * @param Cfe_Utils_Ip $ip
     * @return boolean
     */
    public function greaterThan(Cfe_Utils_Ip $ip) {
        return $this->cmp($ip) > 0;
    }

    /**
     * @param Cfe_Utils_Ip $ip
     * @return boolean
     */
    public function LowerThan(Cfe_Utils_Ip $ip) {
        return $this->cmp($ip) < 0;
    }

    /**
     * @param Cfe_Utils_Ip $ip
     * @return boolean
     */
    public function equals(Cfe_Utils_Ip $ip) {
        return $this->cmp($ip) === 0;
    }

    /**
     * @param Cfe_Utils_Ip $ip
     * @return boolean
     */
    public function greaterThanOrEquals(Cfe_Utils_Ip $ip) {
        return !$this->lowerThan($ip);
    }

    /**
     * @param Cfe_Utils_Ip $ip
     * @return boolean
     */
    public function LowerThanOrEquals(Cfe_Utils_Ip $ip) {
        return !$this->greaterThan($ip);
    }

    abstract public function __toString();

    /**
     *
     * Return the next Ip adress
     * for example :
     * the next ip adress of 192.168.0.255 will be 192.168.1.0
     * the next ip adress of 1234::ffff will be 1234::1:0000
     * @return Cfe_Utils_Ip
     */
    public function getNext() {
        $index = 0;
        do {
            $index += 2;
            list(,$low) = unpack('n', substr($this->ip, -$index, 2));
        }while($low == 0xffff && $index < $this->getByteLength());
        $ipClass = get_class($this);
        return new $ipClass(substr($this->ip, 0, -$index).pack('n', ($low + 1)).str_repeat("\0", $index - 2));
    }
    /**
     *
     * Return the previous Ip adress
     * for example :
     * the previous ip adress of 192.168.1.0 will be 192.168.0.255
     * the previous ip adress of 1234::1:0000 will be 1234::ffff
     * @return Cfe_Utils_Ip
     */
    public function getPrevious() {
        $index = 0;
        do {
            $index += 2;
            list(,$low) = unpack('n', substr($this->ip, -$index, 2));
        }while($low == 0x0000 && $index < $this->getByteLength());
        $ipClass = get_class($this);
        return new $ipClass(substr($this->ip, 0, -$index).pack('n', ($low - 1)).str_repeat("\xff", $index - 2));
    }
}