<?php

class Cfe_Token_Identity {
    /**
     *
     * create a 24 char long base64 token string
     *
     * @param int $type
     * @return string
     */
    public static function createToken($identityProvider, $identityString) {
        return strtr(base64_encode(pack('v',$identityProvider).substr(hash('sha512', $identityString, true), 0, 16)),'/+','_-');
    }
}