<?php

class Cfe_Utils_IpRange {
    /**
     * @var Cfe_Utils_Ip
     */
    protected $lowerBound;
    /**
     * @var Cfe_Utils_Ip
     */
    protected $upperBound;

    /**
     * @param Cfe_Utils_IpRange $a
     * @param Cfe_Utils_IpRange $b
     * @return int
     */
    static public function _cmp(Cfe_Utils_IpRange $a, Cfe_Utils_IpRange $b) {
        return $a->cmp($b);
    }

    /**
     * @param Cfe_Utils_IpRange $a
     * @param Cfe_Utils_IpRange $b
     * @return Cfe_Utils_IpRange
     */
    static public function _min(Cfe_Utils_IpRange $a, Cfe_Utils_IpRange $b) {
        return ($a->cmp($b) < 0)?$a:$b;
    }

    /**
     * @param Cfe_Utils_IpRange $a
     * @param Cfe_Utils_IpRange $b
     * @return Cfe_Utils_IpRange
     */
    static public function _max(Cfe_Utils_IpRange $a, Cfe_Utils_IpRange $b) {
        return ($a->cmp($b) > 0)?$a:$b;
    }

    /**
     * @param string $cidrString
     * @return Cfe_Utils_IpRange
     * @throws Cfe_Utils_Exception
     */
    public static function createFromCIDRFormat($cidrString) {
        Cfe_Assertion_Type::assertString($cidrString);
        $cidrList = explode('/', $cidrString);
        if(count($cidrList) != 2) {
            throw new Cfe_Utils_Exception('invalid CIDR string', self::INVALID_CIDR_STRING);
        }
        $ip = Cfe_Utils_Ip::CreateFromIPString($cidrList[0]);
        $subNet = intval($cidrList[1]);
        if(($subNet < 0) || ($subNet > $ip->getByteLength() * 8)) {
            throw new Cfe_Utils_Exception('invalid CIDR string', self::INVALID_CIDR_STRING);
        }
        return self::getSubNet($ip, $subNet);
    }
    /**
     * @param Cfe_Utils_Ip $ip
     * @param int $subNet
     * @return Cfe_Utils_IpRange
     */
    public static function getSubNet(Cfe_Utils_Ip $ip, $subNet) {
        Cfe_Assertion_Type::assertInt($subNet);
        $ipClass = get_class($ip);
        Cfe_Assertion_Value::assertRange($subNet, 0, $ip->getByteLength() * 8);
        $ipBin = strrev($ip->getIPBin());
        $subNet = ($ip->getByteLength() * 8) - $subNet;
        $lowerBound = str_repeat("\0",($subNet >> 3));
        $upperBound = str_repeat("\xff",($subNet >> 3));
        if($bits = $subNet % 8) {
            $lowerBound .= chr(ord($ipBin{$subNet >> 3}) & ~((1 << $bits) - 1));
            $upperBound .= chr(ord($ipBin{$subNet >> 3}) | ((1 << $bits) - 1));
        }
        $lowerBound .= substr($ipBin, strlen($lowerBound));
        $upperBound .= substr($ipBin, strlen($upperBound));
        return new self(new $ipClass(strrev($lowerBound)), new $ipClass(strrev($upperBound)));
    }

    /**
     * @param Cfe_Utils_Ip $lowerIp
     * @param Cfe_Utils_Ip $upperIp
     */
    public function __construct(Cfe_Utils_Ip $lowerIp, Cfe_Utils_Ip $upperIp) {
        Cfe_Assertion_Value::assertEquals($lowerIp->getIPVersion(), $upperIp->getIPVersion());
        Cfe_Assertion_Value::assertCallback($upperIp, array($lowerIp, 'LowerThanOrEquals'));
        $this->lowerBound = $lowerIp;
        $this->upperBound = $upperIp;
    }

    /**
     * @param Cfe_Utils_Ip $ip
     * @return boolean
     */
    public function isInside(Cfe_Utils_Ip $ip) {
        return $this->lowerBound->LowerThanOrEquals($ip) && $this->upperBound->greaterThanOrEquals($ip);
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @return boolean
     */
    public function isNeighbor(Cfe_Utils_IpRange $ipRange) {
        if($this->lowerBound->LowerThan($ipRange->lowerBound)) {
            return $ipRange->lowerBound->LowerThanOrEquals($this->upperBound->getNext());
        } else {
            return $ipRange->upperBound->getNext()->GreaterThanOrEquals($this->lowerBound);
        }
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @param Cfe_Utils_IpRange $ipRange
     * @return Cfe_Utils_IpRange
     * @throws Cfe_Utils_Exception
     */
    public function union(Cfe_Utils_IpRange $ipRange) {
        if(!self::isNeighbor($ipRange)) {
            throw new Cfe_Utils_Exception('disjoint IP Ranges', self::DISJOINT_IP_RANGES);
        }
        return new self(self::_min($this->lowerBound, $ipRange->lowerBound), self::_max($this->upperBound, $ipRange->upperBound));
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @return int
     */
    public function cmp(Cfe_Utils_IpRange $ipRange) {
        $lowerCmp = $this->lowerBound->cmp($ipRange->lowerBound);
        if($lowerCmp !== 0) {
            return $lowerCmp;
        }
        return $this->upperBound->cmp($ipRange->upperBound);
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @return boolean
     */
    public function lowerThan(Cfe_Utils_IpRange $ipRange) {
        return $this->cmp($ipRange) < 0;
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @return boolean
     */
    public function greaterThan(Cfe_Utils_IpRange $ipRange) {
        return $this->cmp($ipRange) > 0;
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @return boolean
     */
    public function equals(Cfe_Utils_IpRange $ipRange) {
        return $this->cmp($ipRange) === 0;
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @return boolean
     */
    public function lowerThanOrEquals(Cfe_Utils_IpRange $ipRange) {
        return !$this->greaterThan($ipRange);
    }

    /**
     * @param Cfe_Utils_IpRange $ipRange
     * @return boolean
     */
    public function greaterThanOrEquals(Cfe_Utils_IpRange $ipRange) {
        return !$this->lowerThan($ipRange);
    }

    /**
     * @return Cfe_Utils_Ip
     */
    public function getLowerBound() {
        return $this->lowerBound;
    }

    /**
     * @return Cfe_Utils_Ip
     */
    public function getUpperBound() {
        return $this->upperBound;
    }

}
