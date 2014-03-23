<?php

require_once 'Cfe/Utils/Random/Constant.php';
require_once 'Cfe/Utils/Random/Uniform.php';
require_once 'Cfe/Assertion/Type.php';
require_once 'Cfe/Assertion/Value.php';
/**
 * 
 * Lifetime class enable the backend to deal with non constant lifetime value
 * for exemple when we want to be sure that not all cached data will need to
 * be recreated at the same time
 * 
 * @author sk
 *
 */
class Cfe_Cache_Lifetime {
    
    /**
     * 
     * distribution used to generate a value
     * @var Cfe_Utils_Random
     */
    protected $distribution;
    
    /**
     * 
     * if $max is omitted then it's a constant value ($min) else it's a uniform distribution between $min and $max
     * @param int $min must be > 0
     * @param int $max if setted, must be > $min
     */
    public function __construct($min, $max = null) {
        Cfe_Assertion_Type::assertNumeric($min);
        Cfe_Assertion_Value::assertGreaterThan($min, 0);
        if(is_null($max)) {
            $this->distribution = new Cfe_Utils_Random_Constant($min);
        } else {
            Cfe_Assertion_Type::assertNumeric($max);
            Cfe_Assertion_Value::assertGreaterThan($max, $min);
            $this->distribution = new Cfe_Utils_Random_Uniform($min, $max);
        }
    }
    
    /**
     * 
     * get a value
     * @return int
     */
    public function getValue() {
        return $this->distribution->getIntValue();
    }
    
    /**
     * 
     * get the max value of the distribution
     * @return int
     */
    public function getMax() {
        return $this->distribution->getMax();
    }
    
    /**
     * 
     * get the min value of the distribution
     * @return int
     */
    public function getMin(){
        return $this->distribution->getMin();
    }
}