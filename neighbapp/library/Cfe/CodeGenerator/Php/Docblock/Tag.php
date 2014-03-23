<?php
require_once 'Zend/CodeGenerator/Php/Docblock/Tag.php';

class Cfe_CodeGenerator_Php_Docblock_Tag extends Zend_CodeGenerator_Php_Docblock_Tag
{
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTag)
    {
        $tagName = $reflectionTag->getName();

        $codeGenDocblockTag = self::factory($tagName);

        // transport any properties via accessors and mutators from reflection to codegen object
        $reflectionClass = new ReflectionClass($reflectionTag);
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (substr($method->getName(), 0, 3) == 'get') {
                $propertyName = substr($method->getName(), 3);
                if (method_exists($codeGenDocblockTag, 'set' . $propertyName)) {
                    $codeGenDocblockTag->{'set' . $propertyName}($reflectionTag->{'get' . $propertyName}());
                }
            }
        }

        return $codeGenDocblockTag;
    }

    /**
     * getPluginLoader()
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getPluginLoader()
    {
        if (self::$_pluginLoader == null) {
            require_once 'Zend/Loader/PluginLoader.php';
            self::setPluginLoader(new Zend_Loader_PluginLoader(array(
                'Cfe_CodeGenerator_Php_Docblock_Tag' => dirname(__FILE__) . '/Tag/'))
                );
        }

        return self::$_pluginLoader;
    }

    public static function factory($tagName)
    {
        $pluginLoader = self::getPluginLoader();

        try {
            $tagClass = $pluginLoader->load($tagName);
        } catch (Zend_Loader_Exception $exception) {
            $tagClass = 'Cfe_CodeGenerator_Php_Docblock_Tag';
        }
        $tag = new $tagClass(array('name' => $tagName));
        return $tag;
    }
}
