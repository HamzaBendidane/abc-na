<?php

/**
 * Form with ini config
 */
class Cfe_Form extends Zend_Form
{
    public function __construct()
    {
        parent::__construct(
                    
            new Zend_Config_Ini(
                CONFIG_PATH . '/forms/' . static::NAME . '.ini'
            )
        );
    }
}