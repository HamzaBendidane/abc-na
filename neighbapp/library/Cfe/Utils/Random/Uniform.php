<?php

require_once 'Cfe/Utils/Random.php';

class Cfe_Utils_Random_Uniform extends Cfe_Utils_Random
{
    protected $min;
    protected $max;

    /**
     * 
     * set a Uniform distribution between $min and $max
     * @param float|int $min
     * @param float|int $max
     */
    public function __construct($min, $max) {
        $this->min = min($min, $max);
        $this->max = max($min, $max);
    }

    /**
     * 
     * return a random value
     * @return int
     */
    public function getIntValue() {
        return mt_rand($this->min, $this->max);
    }

    /**
     * 
     * return a random value
     * @return float
     */
    public function getFloatValue() {
        return $this->min + (($this->max - $this->min) * mt_rand(0, mt_getrandmax()) / mt_getrandmax());
    }

    /**
     *
     * return the max value of the distribution
     * @return float|int
     */
    public function getMax() {
        return $this->max;
    }

    /**
     *
     * return the min value of the distribution
     * @return float|int
     */
    public function getMin(){
        return $this->min;
    }

}