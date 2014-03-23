<?php
require_once 'Cfe/CodeGenerator/Php/Docblock/Tag.php';

class Cfe_CodeGenerator_Php_Docblock_Tag_Return extends Cfe_CodeGenerator_Php_Docblock_Tag
{

    /**
     * @var string
     */
    protected $_type = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTagReturn
     * @return Cfe_CodeGenerator_Php_Docblock_Tag_Return
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTagReturn)
    {
        $returnTag = new self();

        $returnTag->setName('return');
        $returnTag->setType($reflectionTagReturn->getType()); // @todo rename
        $returnTag->setDescription($reflectionTagReturn->getDescription());

        return $returnTag;
    }

    /**
     * setDatatype()
     *
     * @param string $datatype
     * @return Cfe_CodeGenerator_Php_Docblock_Tag_Return
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * getDatatype()
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }


    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@return ' . $this->_type . ' ' . $this->_description;
        return $output;
    }

}