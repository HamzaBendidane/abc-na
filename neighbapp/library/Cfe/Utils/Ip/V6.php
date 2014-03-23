<?php

require_once 'Cfe/Utils/Ip.php';

class Cfe_Utils_Ip_V6 extends Cfe_Utils_Ip {
    const REGEXP = '/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b).){3}(b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b))|(([0-9A-Fa-f]{1,4}:){0,5}:((b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b).){3}(b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b))|(::([0-9A-Fa-f]{1,4}:){0,5}((b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b).){3}(b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/';

    const IP_VERSION = parent::IP_VERSION_6;
    const IP_BYTE_LENGTH = 16;

    /**
     * @param string $ipString
     * @return Cfe_Utils_Ip
     * @throws Cfe_Utils_Exception
     */
    public static function CreateFromIPString($ipString) {
        Cfe_Assertion_Type::assertString($ipString);
        if(preg_match(self::REGEXP, $ipString, $matches)) {
            if(strncmp($ipString,'::',2) === 0) {
                $ipString = substr($ipString, 1);
            } elseif(substr($ipString,-2) === '::') {
                $ipString = substr($ipString, 0, -1);
            }
            $ipList = explode(':', $ipString);
            $ip = '';
            foreach ($ipList as $hex) {
                if($hex === '') {
                    $ip .= str_repeat("\0\0", 9-count($ipList));
                } else {
                    $ip .= pack('H4',str_pad($hex,4,'0', STR_PAD_LEFT));
                }
            }
            return new self($ip);
        }
        throw new Cfe_Utils_Exception('invalid ipv6 string', parent::INVALID_IP_STRING);
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
    public function getIPv6Bin() {
        return $this->ip;
    }

    public function getByteLength() {
        return self::IP_BYTE_LENGTH;
    }

    /**
     * @param Cfe_Utils_Ip $ip
     * @return int
     */
    public function cmp(Cfe_Utils_Ip $ip) {
        return (strcmp($this->ip, $ip->getIPv6Bin()));
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getIPv6CompactString();
    }
}