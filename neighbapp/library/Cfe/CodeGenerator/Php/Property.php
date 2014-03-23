<?php

require_once 'Zend/CodeGenerator/Php/Property.php';

class Cfe_CodeGenerator_Php_Property extends Zend_CodeGenerator_Php_Property
{
    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Property $reflectionProperty
     * @return Zend_CodeGenerator_Php_Property
     */
    public static function fromReflection(Zend_Reflection_Property $reflectionProperty)
    {
        $property = new self();

        $property->setName($reflectionProperty->getName());

        $allDefaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();

        $property->setDefaultValue($allDefaultProperties[$reflectionProperty->getName()]);

        if ($reflectionProperty->getDocComment() != '') {
            $property->setDocblock(Cfe_CodeGenerator_Php_Docblock::fromReflection($reflectionProperty->getDocComment()));
        }

        if ($reflectionProperty->isStatic()) {
            $property->setStatic(true);
        }

        if ($reflectionProperty->isPrivate()) {
            $property->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionProperty->isProtected()) {
            $property->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $property->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $property->setSourceDirty(false);

        return $property;
    }

    public function isInline() {
        if(!isset($this->inline)) {
            $this->inline = false;
            if(($mdb = $this->getDocblock()) && ($tags = $mdb->getTags())) {
                foreach($tags as $tag) {
                    /* @var $tag Zend_CodeGenerator_Php_Docblock_Tag */
                    $this->inline |= ($tag->getName() == 'inline');
                }
            }
        }
        return $this->inline;
    }
    public function isHidden() {
        if(!isset($this->hidden)) {
            $this->hidden = false;
            if(($mdb = $this->getDocblock()) && ($tags = $mdb->getTags())) {
                foreach($tags as $tag) {
                    /* @var $tag Zend_CodeGenerator_Php_Docblock_Tag */
                    $this->hidden |= ($tag->getName() == 'hidden');
                }
            }
        }
        return $this->hidden;
    }

}