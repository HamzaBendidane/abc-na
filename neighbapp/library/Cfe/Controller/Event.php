<?php
class Cfe_Controller_Event extends Cfe_Controller_Secured
{

	const EVENT_TYPE	= 'eventType';
	const EVENT_ORIGINE	= 'eventOrigin';
	const CFE_EVENT_CLASS	= 'Cfe_Event';
	
	protected $_rootDirectory = array();
	
	/**
	 * @var array representation of class inheritance
	 */
	protected $dependencyTree = array();

	public function indexAction() {

		$this->_rootDirectory = array(
				realpath(APPLICATION_PATH),
				realpath(APPLICATION_PATH.'/../library'),
		);

					
		$this->view->assign('classes', $this->getDescendingClassDescription(self::CFE_EVENT_CLASS));
	}
	
	public function setRootDirectory($root = array()){
		Cfe_Assertion_Type::assertArray($root);
		Cfe_Assertion_Value::assertNotEquals(array(), $root);
		
		$this->_rootDirectory = $root;
	}

	/**
	 * @return array|FALSE array(
	 *     Object(stdClass) {
	 *         'name'	=> '',
	 *         'params'	=> array(
	 *             array(
	 *                 'name'			=> '',
	 *                 'type'			=> '',
	 *                 'description'	=> ''
	 *             )
	 *         )
	 *     }
	 * )
	 */
	public function getDescendingClassDescription($classname){

		Cfe_Assertion_Type::assertString($classname);
		Cfe_Assertion_Value::assertNotEquals("", $classname);
		Cfe_Assertion_Type::assertArray($this->_rootDirectory);
		Cfe_Assertion_Value::assertNotEquals(array(), $this->_rootDirectory);
		
		
		$this->classToFile = array();
		
		$this->buildDependencyTree();
		
		if($eventChildClasses = $this->getSubClass($classname)){
			$classes = array();

			foreach ($eventChildClasses as $class){

				if(!class_exists($class, true) || array_key_exists($class, $classes)){
					continue;
				}

				$_class = new stdClass();
				$_class->name = $class;

				$reflector = new Zend_Reflection_Class($class);
				$_construct = $reflector->getMethod('__construct');
				$docBlock = $_construct->getDocblock();

				// exclude class without 'eventOrigine' and 'eventType' :
				if(!$docBlock->hasTag(self::EVENT_ORIGINE) || !$docBlock->hasTag(self::EVENT_TYPE) ){
					continue;
				}

				$_class->params = array();

				$tag = $docBlock->getTag(self::EVENT_ORIGINE);
				$_class->params[] = array(
						'name' => preg_replace("~^event~i", "", strtolower($tag->getName())),
						'type' => 'string',
						'description' => '"'.$tag->getDescription().'"',
				);
				$tag = $docBlock->getTag(self::EVENT_TYPE);
				$_class->params[] = array(
						'name' => preg_replace("~^event~i", "", strtolower($tag->getName())),
						'type' => 'string',
						'description' => '"'.$tag->getDescription().'"',
				);

				$tags = $docBlock->getTags();


				foreach($tags as $tag){

					if(in_array($tag->getName(), array(self::EVENT_ORIGINE, self::EVENT_TYPE))){
						continue;
					}

					if(!method_exists($tag, 'getVariableName')){
						continue;
					}

					$_class->params[$tag->getVariableName()] = array(
							'name' => str_replace('$', '', $tag->getVariableName()),
							'type' => method_exists($tag, 'getType') ? $tag->getType() : '-type not availbale-',
							'description' => method_exists($tag, 'getDescription') ? $tag->getDescription() : '-description not availbale-',
					);

					$classes[$class] = $_class;
				}
			}

			return $classes;
		}

		return FALSE;
	}
	
	/**
	 * Return a map of class dependency : 'A extends B' yields to entry 'A' => array(B)
	 * 
	 * @param string[] $phpFiles List of file names
	 */
	public function buildDependencyTree(){
		
		$phpFiles = array();
		
		foreach($this->_rootDirectory as $dir){
			$phpFiles = array_merge($phpFiles, $this->exploreDirectory($dir));
		}
		
		foreach ($phpFiles as $file){
			//echo "\n- Current file : $file :\n";
		
			$source = file_get_contents($file);
			//echo 'Source size : '.strlen($source)."\n";
		
			if(preg_match_all("~([a-z_]\w+)\s*extends\s*([a-z_]\w+)~i", $source, $matches)){ // A extends B
		
				foreach($matches[2] as $i => $A){
					$this->classToFile[$matches[1][$i]] = $file;
					if(array_key_exists($A, $this->dependencyTree)){
						$this->dependencyTree[$A][] = $matches[1][$i];
						$this->dependencyTree[$A] = array_unique($this->dependencyTree[$A]);
					}else {
						$this->dependencyTree[$A] = array($matches[1][$i]);
					}
				}
			}
		}
	}
	
	/**
	 * Get the list of all descendant classes of the input class.
	 * 
	 * @param string $node parent class name
	 */
	public function getSubClass($node){
		
		if(!array_key_exists($node, $this->dependencyTree)){
			return array();
		}

		$subClasses = $this->dependencyTree[$node];

		foreach ($subClasses as $class){
			$subClasses = array_merge($subClasses, $this->getSubClass($class));
		}

		return array_unique($subClasses);
	}

	public function exploreDirectory($dir = NULL){
		if(!is_dir($dir)){
			throw new Exception(var_export($dir, true).' is not a valid directory name.');
		}
		
		$files = scandir($dir);
		$files = array_diff($files, array('.', '..'));

		$phpFiles = array();
		foreach($files as $file){
			$_file = "$dir/$file";

			if(is_file($_file) && preg_match("~\.php$~is", $file)){
				$phpFiles[] = $_file;
			}elseif(is_dir($_file)){
				$phpFiles = array_merge($phpFiles, $this->exploreDirectory($_file));
			}
		}

		return $phpFiles;
	}
}


