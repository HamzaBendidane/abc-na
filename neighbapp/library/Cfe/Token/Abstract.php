<?php
/**
 *
 * Abstract call to generate tokens
 *
 * /!\ This code will work correctly only on 64bit PHP
 *
 * @author sk
 *
 */

abstract class Cfe_Token_Abstract {
    /**
     * give a timestamp on 60 bits
     */
    protected static function getTimeToken() {
        $mt = intval(microtime(true)*0x1000000);
        return self::base64_encode_int($mt,10);
    }
    /**
     * give a server + pid token on 24 bits
     */
    protected static function getPidToken() {
        $srvCode = ord(substr(md5(php_uname('n')),0,1));
        return self::base64_encode_int(getmypid()+ ($srvCode << 16),4);
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
     * create a base64 token string
     *
     * @return string
     */
    public static function createToken() {
    	return '';
    }

}