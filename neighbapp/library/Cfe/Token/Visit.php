<?php
/**
 *
 * This class generate unique tokens containing timestamp, entity, component and instance.
 * it's crafted so that it's almost impossible to create to identical token involuntarily.
 * the token is signed so that it can be checked that the token was created by the right entity
 *
 * /!\ This code will work correctly only on 64bit PHP
 *
 * @author sk
 *
 */
require_once 'Cfe/Token/Abstract.php';

class Cfe_Token_Visit extends Cfe_Token_Abstract{
    protected static $keys = array(
        0 => 'SK_B2C_key_k!f%qwk45k&y'
        );

    const DEFAULT_ENTITY = 0;

    const COMPONENT_RDR = 1;
    const COMPONENT_JPM = 2;
    const COMPONENT_MTP = 3;
    const COMPONENT_SMW = 4;
    const COMPONENT_LPM = 5;

    const ENTITY_SK_B2C = 0;

    /**
     * give an entity + component + instance token on 18bits
     */
    protected static function getComponentToken($entity, $component, $instance) {
        $componentCode = (($entity & 0x3f) << 12) | (($component & 0x3f) << 6) | ($instance & 0x3f);
        return self::base64_encode_int($componentCode,3);
    }

    /**
     *
     * create a 24 char long base64 token string
     *
     * @param int $component
     * @param int $instance
     * @param int $entity
     * @return string
     */
    public static function createToken($component = 0, $instance = 0, $entity = self::DEFAULT_ENTITY) {
        $tok = self::getComponentToken($entity, $component, $instance).self::getTimeToken().self::getPidToken();
        return $tok.strtr(substr(base64_encode(hash_hmac('sha256',$tok,self::$keys[$entity],true)),0,7),'/+','_-');
    }

    /**
     * check if the token signature is valid
     *
     * @param string $token
     * @return boolean
     */
    public static function validToken($token) {
        $entity = self::base64_decode_int(substr($token, 0, 1));
        return (strlen($token) == 24) && (strtr(substr(base64_encode(hash_hmac('sha256',substr($token, 0,17),self::$keys[$entity],true)),0,7),'/+','_-') == substr($token, -7));
    }

    /**
     *
     * if the token is a signed token, extract entity, component, instance and timestamp
     * @param string $token
     * @return array entity, component, instance and timestamp as keys
     */
    public static function decodeToken($token) {
        if(!self::validToken($token)) {
            throw new Exception('invalid token : '.$token);
        }
        $response = array_combine(array('entity', 'component', 'instance'), array_map(array(self,'base64_decode_int'),str_split((substr($token, 0, 3)))));
        $response['timestamp'] = self::base64_decode_int(substr($token, 3, 10)) /0x1000000 ;
        return $response;
    }
}