<?php

abstract class Cfe_Utils_Random {
    
    /**
     * 
     * return a random value
     * @return int
     */
    public function getIntValue() {
        return round($this->getFloatValue());
    }
        
    /**
     * 
     * return a random value
     * @return float
     */
    abstract public function getFloatValue();
    
    /**
     * 
     * return the max value of the distribution
     * @return float|int
     */
    abstract public function getMax();
    
    /**
     * 
     * return the min value of the distribution
     * @return float|int
     */
    abstract public function getMin();
}