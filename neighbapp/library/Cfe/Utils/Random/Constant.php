<?php

require_once 'Cfe/Utils/Random.php';

class Cfe_Utils_Random_Constant extends Cfe_Utils_Random
{
    protected $constant;
    
    /**
     * 
     * create a constant distribution
     * @param $constant
     */
    public function __construct($constant) {
        $this->constant = $constant;
    }
    
    /**
     * 
     * return the constant value
     * @return float
     */
    public function getFloatValue() {
        return $this->constant;
    }
    
    /**
     * 
     * return the max value of the distribution (ie the constant)
     * @return float|int
     */
    public function getMax() {
        return $this->constant;
    }
    
    /**
     * 
     * return the min value of the distribution (ie the constant)
     * @return float|int
     */
    public function getMin(){
        return $this->constant;
    }
}
