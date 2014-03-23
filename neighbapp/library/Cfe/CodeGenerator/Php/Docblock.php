<?php
require_once 'Zend/CodeGenerator/Php/Docblock.php';
require_once 'Cfe/CodeGenerator/Php/Docblock/Tag.php';

class Cfe_CodeGenerator_Php_Docblock extends Zend_CodeGenerator_Php_Docblock
{
    /**
     * fromReflection() - Build a docblock generator object from a reflection object
     *
     * @param Zend_Reflection_Docblock $reflectionDocblock
     * @return Cfe_CodeGenerator_Php_Docblock
     */
    public static function fromReflection(Zend_Reflection_Docblock $reflectionDocblock)
    {
        $docblock = new self();

        $docblock->setSourceContent($reflectionDocblock->getContents());
        $docblock->setSourceDirty(false);

        $docblock->setShortDescription($reflectionDocblock->getShortDescription());
        $docblock->setLongDescription($reflectionDocblock->getLongDescription());

        foreach ($reflectionDocblock->getTags() as $tag) {
            $docblock->setTag(Cfe_CodeGenerator_Php_Docblock_Tag::fromReflection($tag));
        }

        return $docblock;
    }
    /**
     * setTag()
     *
     * @param array|Cfe_CodeGenerator_Php_Docblock_Tag $tag
     * @return Cfe_CodeGenerator_Php_Docblock
     */
    public function setTag($tag)
    {
        if (is_array($tag)) {
            $tag = new Cfe_CodeGenerator_Php_Docblock_Tag($tag);
        } elseif (!$tag instanceof Cfe_CodeGenerator_Php_Docblock_Tag) {
            require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception(
                'setTag() expects either an array of method options or an '
                . 'instance of Cfe_CodeGenerator_Php_Docblock_Tag'
                );
        }

        $this->_tags[] = $tag;
        return $this;
    }
}
