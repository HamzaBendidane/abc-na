<?php
require_once 'Cfe/Utils/Ip.php';

class Cfe_Utils_Ip_V4 extends Cfe_Utils_Ip {
    const REGEXP = '/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
    const IPV4_PREFIX = "\0\0\0\0\0\0\0\0\0\0\xff\xff";
    const IPV4_SUFFIX = "";

    const IP_VERSION = parent::IP_VERSION_4;
    const IP_BYTE_LENGTH = 4;

    /**
     * @param string $ipString
     * @return Cfe_Utils_Ip
     * @throws Cfe_Utils_Exception
     */
    public static function CreateFromIPString($ipString) {
        Cfe_Assertion_Type::assertString($ipString);
        if(preg_match(self::REGEXP, $ipString, $matches)) {
            array_shift($matches);
            return new self(implode('', array_map('chr', array_map('intval', $matches))));
        }
        throw new Cfe_Utils_Exception('invalid ipv4 string', parent::INVALID_IP_STRING);
    }

    public function __construct($ipBinString) {
        Cfe_Assertion_Type::assertString($ipBinString);
        Cfe_Assertion_Value::assertEquals(strlen($ipBinString), self::IP_BYTE_LENGTH);
        $this->ip = $ipBinString;
    }

    /**
     * @return int
     */
    public function getIPVersion() {
        return self::IP_VERSION;
    }

    /**
     * @return string
     */
    public function getIPv4String() {
        return implode('.', array_map('ord', str_split($this->ip)));
    }

    /**
     * @return string
     */
    public function getIPv4Bin() {
        return $this->ip;
    }

    /**
     * @return string
     */
    public function getIPv6Bin() {
        return self::IPV4_PREFIX.$this->ip.self::IPV4_SUFFIX;
    }

    public function getByteLength() {
        return self::IP_BYTE_LENGTH;
    }

    /**
     * @param Cfe_Utils_Ip $ip
     * @return int
     */
    public function cmp(Cfe_Utils_Ip $ip) {
    	if($this->getIPVersion() == $ip->getIPVersion()) {
            return (strcmp($this->ip, $ip->ip));
        }
        return (strcmp($this->getIPv6Bin(), $ip->getIPv6Bin()));
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getIPv4String();
    }
}