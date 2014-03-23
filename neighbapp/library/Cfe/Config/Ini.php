<?php
/**
 * Cfe Framework
 *
 * @category   Cfe
 * @package    Cfe_Config
 * @copyright  Copyright (c) sk
 */
/**
 * @see Cfe_Config
 */
require_once 'Zend/Config/Ini.php';
/**
 * @category   Cfe
 * @package    Cfe_Config
 * @copyright  Copyright (c) sk
 */
class Cfe_Config_Ini extends Zend_Config_Ini {
    /**
     *
     * regular expression to parse the INI file
     * @var string
     */
    const generalRegexp = '~
	(?<=^|\r\n|\r|\n)
	(?:
		(?:\h*+;\V++)
		|
		(?:\h*+)
		|
		(?:
			\h*
			(?:
			(?i:include)\h++
			(?P<include>[\./A-Za-z0-9:\_\-]+)(\h++(?P<includePrefix>[^=;\v]++))?
			)
		)
		|
		(?:
			\h*+
			(?:
				\[(?P<section>[^;\v\]]++)\]
				|
				(?P<key>
					[^=;\r\n]+?
				)
				\h*+=\h*+
				(?:
					(?P<null>(?i:null))
					|
					(?P<bool>(?i:true)|(?i:false))
					|
					(?P<int>[+-]?(?:[1-9]\d{0,18}|0))
					|
					(?P<float>(?:[+-]?(?:[1-9]\d*+|0))\.\d++(?:E[+-]\d++)?)
					|
					(?P<string>
						"(?P<quoted>(?:[^"\x5C]++|\x5C.)*+)"
						|
						\'(?P<apostrophed>(?:[^\'\x5C]++|\x5C.)*+)\'
					)
					|
					(?P<mixed>
						(?:
							[a-zA-Z][a-zA-Z0-9\_]*(\:\:[a-zA-Z][a-zA-Z0-9\_]*)?
							|
							"([^"\x5C]++|\x5C.)*+"
							|
							\'([^\'\x5C]++|\x5C.)*+\'
						)
				   		(?:
				   			\h++
				   			(?:
					   			[a-zA-Z][a-zA-Z0-9\_]*(\:\:[a-zA-Z][a-zA-Z0-9\_]*)?
					   			|
					   			"([^"\x5C]++|\x5C.)*+"
					   			|
					   			\'([^\'\x5C]++|\x5C.)*+\'
					   		)
				   		)*+
				   		\h*+
				   	)
				   	|
				   	(?P<unquoted>[^;\v"\']*+)
				)
			)
			\h*+
			(?:;\V++)?
			|
			(?P<error>\V++)
		)
	)
	(?=\r\n|\r|\n|$)(?:\r\n|\r|\n)?
	|
	(?<=\r\n|\r|\n)(?:\r\n|\r|\n)
	~xsS';
    /**
     *
     * Regular expression to parse the case of mixed values (constants mixed with strings)
     * @var string
     */
    const mixedRegexp = '~
	"(?P<quoted>([^"\x5C]++|\x5C.)*+)"
	|
	\'(?P<apostrophed>([^\'\x5C]++|\x5C.)*+)\'
	|
	(?P<spacedclassconst>\h*+(?P<classconst>(?P<class>[a-zA-Z][a-zA-Z0-9\_]*+)\:\:(?:[a-zA-Z][a-zA-Z0-9\_]*+))\h*+)
	|
	(?P<spacedconst>\h*+(?P<const>[a-zA-Z][a-zA-Z0-9\_]*+)\h*+)
	|
	(?P<space>\h++)
	~xsS';

    /**
     *
     * preg_replace callback to replace constants
     * @param unknown_type $matches
     */
    protected function constReplace($matches) {
        if ($matches ['quoted']) {
            return stripcslashes ( $matches ['quoted'] );
        } elseif ($matches ['apostrophed']) {
            return stripcslashes ( $matches ['apostrophed'] );
        } elseif ($matches ['spacedclassconst']) {
            if (class_exists($matches['class'], true)) {
                return @constant($matches ['classconst']);
            } else {
                return $matches ['spacedclassconst'];
            }
        } elseif ($matches ['spacedconst']) {
            if (defined ( $matches ['const'] )) {
                return @constant($matches ['const']);
            } else {
                return $matches ['spacedconst'];
            }
        }
        return '';
    }
    /**
     * Load the INI file from disk using a refactored regexp.
     *
     * @param string $filename
     * @throws Zend_Config_Exception
     * @return array
     */
    public function _parseIniFile($filename, $keyPrefix = '') {
        $data = @file_get_contents ( $filename );
        if ($data === false) {
            // @codeCoverageIgnoreStart
            // should not happen : it should be catched by _loadIniFile()
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception ( 'unable to open ' . $filename );
            // @codeCoverageIgnoreEnd
        }
        if (! @is_int ( preg_match_all ( self::generalRegexp, $data, $tokens, PREG_SET_ORDER ) )) {
            // @codeCoverageIgnoreStart
            // can hapen only it $this->_regexp is invalid
            require_once 'Zend/Config/Exception.php';
            throw new Zend_Config_Exception ( 'Error parsing ' . $filename );
            // @codeCoverageIgnoreEnd
        }
        $result = array ();
        $currentSection = &$result;
        $definedSections = array ();
        $includes = array();
        foreach ( $tokens as &$token ) {
            if (array_key_exists ( 'error', $token ) && $token ['error'] !== '') {
                require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception ( 'Error parsing ' . $filename . "\n" . 'on line :' . $token ['error'] );
            } elseif (array_key_exists ( 'include', $token ) && $token ['include'] !== '') {
                $includePath = dirname($filename).'/'.$token['include'];
                if(!file_exists($includePath)) {
                    throw new Zend_Config_Exception ( "Error parsing [file : $includePath doesn't exists] in " . $filename );
                }
                if(!empty($result)) {
                    throw new Zend_Config_Exception ( "Error parsing [include must be defined before anything else] in " . $filename );
                }
                $includes = $this->merge_config($includes, $this->_parseIniFile($includePath, $token ['includePrefix']));
            } elseif (array_key_exists ( 'section', $token ) && $token ['section'] !== '') {
                $section = $token ['section'];
                if (! array_key_exists ( $section, $result )) {
                    $result [$section] = array ();
                    $definedSections [] = $section;
                } elseif (in_array ( $section, $definedSections )) {
                    require_once 'Zend/Config/Exception.php';

                    throw new Zend_Config_Exception ( 'Error parsing [section name already exists] in ' . $filename );
                }
                $currentSection = &$result [$section];
            } elseif (array_key_exists ( 'key', $token ) && $token ['key'] !== '') {
                if ($token ['null']) {
                    $currentSection [$keyPrefix.$token ['key']] = null;
                } elseif ($token ['bool']) {
                    $currentSection [$keyPrefix.$token ['key']] = (strtolower ( $token ['bool'] ) == 'true');
                } elseif ($token ['int'] !== '') {
                    $currentSection [$keyPrefix.$token ['key']] = intval ( $token ['int'] );
                } elseif ($token ['float'] !== '') {
                    $currentSection [$keyPrefix.$token ['key']] = floatval ( $token ['float'] );
                } elseif ($token ['quoted'] !== '') {
                    $currentSection [$keyPrefix.$token ['key']] = stripslashes ( $token ['quoted'] );
                } elseif (array_key_exists ( 'apostrophed', $token ) && $token ['apostrophed'] !== '') {
                    $currentSection [$keyPrefix.$token ['key']] = stripslashes ( $token ['apostrophed'] );
                } elseif ($token ['string'] !== '') {
                    $currentSection [$keyPrefix.$token ['key']] = '';
                } elseif ($token ['mixed']) {
                    $currentSection [$keyPrefix.$token ['key']] = preg_replace_callback ( self::mixedRegexp, array ($this, 'constReplace' ), $token ['mixed'] );
                } elseif ($token ['unquoted'] !== '') {
                    $currentSection [$keyPrefix.$token ['key']] = $token ['unquoted'];
                } else {
                    // @codeCoverageIgnoreStart
                    // should not happen (no way to fall into this case with the current regexp
                    $currentSection [$keyPrefix.$token ['key']] = '';
                    // @codeCoverageIgnoreEnd
                }
            }
        }
        if(!empty($includes)) {
            $includes = $this->simplifySections($includes);
            $result = $this->merge_config($includes, $result);
        }
        return ($result);
    }

    protected function simplifySections($includes) {
        $sections = array();
        foreach($includes as $sectionName => &$data) {
            if(strpos($sectionName, $this->_sectionSeparator) !== false) {
                list($base, $extend) = array_map('trim',explode($this->_sectionSeparator, $sectionName));
                $sections[$base] = array_merge($data, array(';extend' => $extend));
            } else {
                $sections[$sectionName] = $data;
            }
        }
        foreach($sections as $sectionName => &$data) {
            $this->simplifySection($sectionName, $sections);
        }
        return $sections;
    }
    protected function simplifySection($sectionName, &$sections) {
        if(array_key_exists(';extend', $sections[$sectionName])) {
            $extend = $sections[$sectionName][';extend'];
            $this->simplifySection($extend, $sections);
            $sections[$sectionName] = $this->merge_config($sections[$extend], $sections[$sectionName]);
            unset($sections[$sectionName][';extend']);
        }
    }

    protected function merge_config($a, &$b)
    {
        foreach($b as $key => &$value)
        {
            if(array_key_exists($key, $a)) {
                if(is_array($a[$key]) !== is_array($value)) {
                    throw new Zend_Config_Exception ( "Error merging include files [array mismatch] in " . $filename );
                } elseif(is_array($value)) {
                    $a[$key] = $this->merge_config($a[$key], $value);
                } else {
                    $a[$key] = $value;
                }
            } else {
                $a[$key] = $value;
            }
        }
        return $a;
    }

    /**
     * Load the ini file and preprocess the section separator (':' in the
     * section name (that is used for section extension) so that the resultant
     * array has the correct section names and the extension information is
     * stored in a sub-key called ';extends'. We use ';extends' as this can
     * never be a valid key name in an INI file that has been loaded using
     * parse_ini_file().
     *
     * @param string $filename
     * @throws Zend_Config_Exception
     * @return array
     */
    protected function _loadIniFile($filename) {
        // use APC if it's available
        if (function_exists ( 'apc_fetch' )) {
            $key = __CLASS__ . ':' . md5 ( realpath ( $filename ) );
            $filemtime = @filemtime ( $filename );
            if ($filemtime === false) {
                require_once 'Zend/Config/Exception.php';
                throw new Zend_Config_Exception ( 'unable to open ' . $filename );
            }
            $data = apc_fetch ( $key, $success );
            if ($success && $data [1] == $filemtime) {
                // @codeCoverageIgnoreStart
                // APC won't work on cli ...
                return $data [0];
                // @codeCoverageIgnoreEnd
            } else {
                $iniArray = parent::_loadIniFile ( $filename );
                apc_store ( $key, array ($iniArray, $filemtime ), 300 );
                return $iniArray;
            }
        }
        // @codeCoverageIgnoreStart
        // or use the parent fonction if APC is not available.
        return parent::_loadIniFile ( $filename );
        // @codeCoverageIgnoreEnd
    }
}