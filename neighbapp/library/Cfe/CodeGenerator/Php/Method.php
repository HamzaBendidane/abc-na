<?php


require_once 'Zend/CodeGenerator/Php/Method.php';

/**
 * @category   Cfe
 * @package    Cfe_CodeGenerator
 */
class Cfe_CodeGenerator_Php_Method extends Zend_CodeGenerator_Php_Method
{
    public static function fromReflection(Zend_Reflection_Method $reflectionMethod)
    {
        $method = new self();

        $method->setSourceContent($reflectionMethod->getContents(false));
        $method->setSourceDirty(false);

        if ($reflectionMethod->getDocComment() != '') {
            $method->setDocblock(Cfe_CodeGenerator_Php_Docblock::fromReflection($reflectionMethod->getDocblock()));
        }

        $method->setFinal($reflectionMethod->isFinal());

        if ($reflectionMethod->isPrivate()) {
            $method->setVisibility(self::VISIBILITY_PRIVATE);
        } elseif ($reflectionMethod->isProtected()) {
            $method->setVisibility(self::VISIBILITY_PROTECTED);
        } else {
            $method->setVisibility(self::VISIBILITY_PUBLIC);
        }

        $method->setStatic($reflectionMethod->isStatic());

        $method->setName($reflectionMethod->getName());

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $method->setParameter(Zend_CodeGenerator_Php_Parameter::fromReflection($reflectionParameter));
        }

        $method->setBody($reflectionMethod->getBody());

        return $method;
    }

    /**
     *
     * get the type of the return, false if not defined
     * @return string|false
     */
    public function getReturnType() {
        if(!($docBlock = $this->getDocblock())) {
            return false;
        }
        /* @var $docBlock Zend_CodeGenerator_Php_Docblock */
        $tags = $docBlock->getTags();
        foreach ($tags as $tag) {
            if($tag instanceof Cfe_CodeGenerator_Php_Docblock_Tag_Return) {
                return $tag->getType();
            }
        }
        return false;
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
    public function getCache() {
        if(!isset($this->cacheTime)) {
            $this->cacheTime = false;
            if(($mdb = $this->getDocblock()) && ($tags = $mdb->getTags())) {
                foreach($tags as $tag) {
                    /* @var $tag Zend_CodeGenerator_Php_Docblock_Tag */
                    if($tag->getName() == 'cache') {
                        if(preg_match ('~^\s*+(\d+)(?:$|\s|,)~', $tag->getDescription(), $matches))
                        $this->cacheTime = intval($matches[1]);
                    }
                }
            }
        }
        return $this->cacheTime;
    }

    public function getInlinableWarnings(Cfe_CodeGenerator_Php_Class $class) {
        $return = array();
        if(preg_match_all('~(?<![a-zA-Z0-9_])(?P<method>(?:parent|self)\:\:[a-zA-Z_][a-zA-Z0-9_]*)\s*+\(~', $this->getBody(), $matches)) {
            foreach ($matches['method'] as $methodName) {
                $return[] = "ERROR : call to $methodName()";
            }
        }
        if(preg_match_all('~(?<![a-zA-Z0-9_])\$this\-\>(?P<method>[a-zA-Z_][a-zA-Z0-9_]*)\s*+\(~', $this->getBody(), $matches)) {
            foreach ($matches['method'] as $methodName) {
                if(!($method = $class->getMethod($methodName))) {
                    /* @var $method Cfe_CodeGenerator_Php_Method */
                    $return[] = "ERROR : call to undefined \$this->{$methodName}()";
                } elseif($method->isInline() && $method->getInlinableWarnings($class)) {
                    $return[] = "ERROR : call to problematic inline method \$this->{$methodName}()";
                } elseif(!$method->isInline() && $method->getVisibility() != Zend_CodeGenerator_Php_Method::VISIBILITY_PUBLIC) {
                    $return[] = "ERROR : call to ".$method->getVisibility()." not inlined method \$this->{$methodName}()";
                }
            }
        }
        if(preg_match_all('~(?<![a-zA-Z0-9_])\$this\-\>(?P<property>[a-zA-Z_][a-zA-Z0-9_]*)\s*+(?!\()~', $this->getBody(), $matches)) {
            foreach ($matches['property'] as $propertyName) {
                if(($property = $class->getProperty($propertyName)) && !$property->isInline() && ($property->getVisibility() != Zend_CodeGenerator_Php_Property::VISIBILITY_PUBLIC)) {
                    /* @var $method Cfe_CodeGenerator_Php_Property */
                    $return[] = "ERROR : reference to ".$property->getVisibility()." not inlined \$this->{$propertyName}()";
                }
            }
        }
        return $return;
    }
}
