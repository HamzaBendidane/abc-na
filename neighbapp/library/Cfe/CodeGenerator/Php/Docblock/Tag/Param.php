<?php
require_once 'Cfe/CodeGenerator/Php/Docblock/Tag.php';

class Cfe_CodeGenerator_Php_Docblock_Tag_Param extends Cfe_CodeGenerator_Php_Docblock_Tag
{

    /**
     * @var string
     */
    protected $_type = null;

    /**
     * @var string
     */
    protected $_paramName = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTagParam
     * @return Cfe_CodeGenerator_Php_Docblock_Tag_Param
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTagParam)
    {
        $paramTag = new self();

        $paramTag->setName('param');
        $paramTag->setDatatype($reflectionTagParam->getType()); // @todo rename
        $paramTag->setParamName($reflectionTagParam->getVariableName());
        $paramTag->setDescription($reflectionTagParam->getDescription());

        return $paramTag;
    }

    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return Cfe_CodeGenerator_Php_Docblock_Tag_Param
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * getDatatype
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * setParamName()
     *
     * @param string $paramName
     * @return Cfe_CodeGenerator_Php_Docblock_Tag_Param
     */
    public function setParamName($paramName)
    {
        $this->_paramName = $paramName;
        return $this;
    }

    /**
     * getParamName()
     *
     * @return string
     */
    public function getParamName()
    {
        return $this->_paramName;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@param '
            . (($this->_type  != null) ? $this->_type : 'unknown')
            . (($this->_paramName != null) ? ' $' . $this->_paramName : '')
            . (($this->_description != null) ? ' ' . $this->_description : '');
        return $output;
    }

}
