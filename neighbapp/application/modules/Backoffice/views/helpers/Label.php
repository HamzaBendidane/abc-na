<?php

class Zend_View_Helper_Label extends Zend_View_Helper_Abstract {

    /**
     * Transform String to label
     * @param string $index label to change
     * @param string $context  can call a special class
     */
    public function Label($index, $context = false) {
       
       if($context !== false){
           $labelContextClass = 'Class_Common_Label_' . ucfirst($context);
           $label = $labelContextClass::getLabel($index);
       } else {
           $label = Class_Common_Label::getLabel($index);
       }

       if(!isset($label)){
           $return = str_replace('_', ' ', $index);
       } else {
           $return = $label;
       }
       
       return $return;
    }
}