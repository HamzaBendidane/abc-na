<?php

/**
 * Class for all Common Attribute
 * @author Jeyaganesh RANJIT
 */
class Class_Common_Cst {

    const MODEL_IPHONE = 'iphone';
    const MODEL_IPAD = 'ipad';
    
    public function getModelDevice($model) {
        if (stripos($model, 'ipad') !== false) {
            return self::MODEL_IPAD;
        } else {
            return self::MODEL_IPHONE;
        }
    }
}