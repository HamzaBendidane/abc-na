<?php
class Cfe_CodeGenerator_Php_Docblock_Tag_License extends Cfe_CodeGenerator_Php_Docblock_Tag
{
    /**
     * @var string
     */
    protected $_url = null;

    /**
     * @var string
     */
    protected $_description = null;

    /**
     * fromReflection()
     *
     * @param Zend_Reflection_Docblock_Tag $reflectionTagReturn
     * @return Cfe_CodeGenerator_Php_Docblock_Tag_License
     */
    public static function fromReflection(Zend_Reflection_Docblock_Tag $reflectionTagLicense)
    {
        $returnTag = new self();

        $returnTag->setName('license');
        $returnTag->setUrl($reflectionTagLicense->getUrl());
        $returnTag->setDescription($reflectionTagLicense->getDescription());

        return $returnTag;
    }

    /**
     * setUrl()
     *
     * @param string $url
     * @return Cfe_CodeGenerator_Php_Docblock_Tag_License
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * getUrl()
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }


    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        $output = '@license ' . $this->_url . ' ' . $this->_description . self::LINE_FEED;
        return $output;
    }

}