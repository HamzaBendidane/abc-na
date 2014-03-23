<?php
/**
 *
 * This class generate unique tokens containing timestamp and type.
 * it's crafted so that it's almost impossible to create to identical token involuntarily.
 *
 * /!\ This code will work correctly only on 64bit PHP
 *
 * @author sk
 *
 */
require_once 'Cfe/Token/Abstract.php';

class Cfe_Token_Id extends Cfe_Token_Abstract {

    /**
     * give a type token on 6bits
     */
    protected static function getTypeToken($type) {
        return self::base64_encode_int($type,1);
    }

    /**
     * give a server + pid token on 30 bits
     */
    protected static function getPidToken() {
        $srvCode = ord(substr(md5(php_uname('n')),0,2));
        return self::base64_encode_int(getmypid()+ ($srvCode << 16),5);
    }

    protected static function base64_encode_int($int, $nbChar = 10) {
        if($nbChar > 10) {
            throw new Exception('can\'t generate more than 10 chars');
        }
        return strtr(substr(base64_encode(pack('NN',$int >> 28, $int << 4 & 0xffffffff)), 10-$nbChar, $nbChar),'/+','_-');
    }

    protected static function base64_decode_int($str) {
        list(,$high, $low) = unpack('N*',base64_decode(str_pad(strtr($str,'_-','/+').'A',11,'A',STR_PAD_LEFT)));
        return ($high << 28) + ($low >> 4);
    }

    /**
     *
     * create a 16 char long base64 token string
     *
     * @param int $type
     * @return string
     */
    public static function createToken($type = 0) {
        return self::getTypeToken($type).self::getTimeToken().self::getPidToken();
    }

    /**
     *
     * if the token is a signed token, extract entity, component, instance and timestamp
     * @param string $token
     * @return array entity, component, instance and timestamp as keys
     */
    public static function decodeToken($token) {
        return array(
        	'type' => self::base64_decode_int(substr($token, 0, 1)),
        	'timestamp' => self::base64_decode_int(substr($token, 1, 10)) /0x1000000,
    	);
    }
}