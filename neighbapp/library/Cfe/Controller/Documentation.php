<?php
class Cfe_Controller_Documentation extends Cfe_Controller_Secured
{

	const EVENT_TYPE		= 'eventType';
	const EVENT_ORIGINE		= 'eventOrigin';
	const CFE_EVENT_CLASS	= 'Cfe_Event';
	const TAG_NAME			= 'property';

	protected static $_rootDirectories = array();
	protected static $classToFile = array();


	/**
	 * @var array representation of class inheritance
	 */
	protected static $dependencyTree = array();

	public function indexAction() {

		self::$_rootDirectories = array(
				realpath(APPLICATION_PATH),
				realpath(APPLICATION_PATH.'/../library'),
		);

		$this->view->assign('classes', self::getDescendingClassDescription(self::CFE_EVENT_CLASS));
	}

	protected function setRootDirectories($roots = array()){
		Cfe_Assertion_Type::assertArray($roots);
		Cfe_Assertion_Value::assertNotEquals(array(), $roots);

		self::$_rootDirectories = $roots;
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
	protected function getDescendingClassDescription($classname){

		Cfe_Assertion_Type::assertString($classname);
		Cfe_Assertion_Value::assertNotEquals("", $classname);
		Cfe_Assertion_Type::assertArray(self::$_rootDirectories);
		Cfe_Assertion_Value::assertNotEquals(array(), self::$_rootDirectories);

		self::$classToFile = array();

		if($eventChildClasses = self::getSubClass($classname)){
			$classes = array();

			foreach ($eventChildClasses as $class){

				if(!class_exists($class, true) || array_key_exists($class, $classes)){
					continue;
				}

				$_class = new stdClass();
				$_class->name = $class;

				$reflector = new Zend_Reflection_Class($class);
				try{
					$docBlock = $reflector->getDocblock();
				}catch (Exception $e){
					echo "\nClass {$e->getMessage()}\n";
					continue;
				}

				/*
				$_construct = $reflector->getMethod('__construct');
				$docBlock = $_construct->getDocblock();
				*/
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

				$tags = $docBlock->getTags(self::TAG_NAME);

				foreach($tags as $tag){

					if(in_array($tag->getName(), array(self::EVENT_ORIGINE, self::EVENT_TYPE))){
						continue;
					}

					/*
					if(!method_exists($tag, 'getVariableName')){
						continue;
					}
					$varname = str_replace('$', '', $tag->getVariableName());
					$type = method_exists($tag, 'getType') ? $tag->getType() : '-type not availbale-';
					$description = method_exists($tag, 'getDescription') ? $tag->getDescription() : '-description not availbale-';
					*/

					$description = $tag->getDescription();

					if(!preg_match('~([^\h]*)\h\$([^\h]*)\h(.*)$~i', $description, $matches)){
						throw new Exception('Invalid format for '.self::TAG_NAME.' tag : expecting "'. self::TAG_NAME.' &lt;type&gt; $varname &lt;desctiption&gt;"');
					}

					$type = $matches[1];
					$varname = $matches[2];
					$description = $matches[3];

					$_class->params[$varname] = array(
							'type' => $type,
							'name' => $varname,
							'description' => $description,
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
	protected function buildDependencyTree(){

		$phpFiles = array();

		foreach(self::$_rootDirectories as $dir){
			$phpFiles = array_merge($phpFiles, self::exploreDirectory($dir));
		}

		foreach ($phpFiles as $file){
			//echo "\n- Current file : $file :\n";

			$source = file_get_contents($file);
			//echo 'Source size : '.strlen($source)."\n";

			if(preg_match_all("~([a-z_]\w+)\s*extends\s*([a-z_]\w+)~i", $source, $matches)){ // A extends B

				foreach($matches[2] as $i => $A){
					self::$classToFile[$matches[1][$i]] = $file;
					if(array_key_exists($A, self::$dependencyTree)){
						self::$dependencyTree[$A][] = $matches[1][$i];
						self::$dependencyTree[$A] = array_unique(self::$dependencyTree[$A]);
					}else {
						self::$dependencyTree[$A] = array($matches[1][$i]);
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
	protected function getSubClass($node){

		if(empty(self::$dependencyTree)){
			self::buildDependencyTree();
		}

		if(!array_key_exists($node, self::$dependencyTree)){
			return array();
		}

		$subClasses = self::$dependencyTree[$node];

		foreach ($subClasses as $class){
			$subClasses = array_merge($subClasses, self::getSubClass($class));
		}

		return array_unique($subClasses);
	}

	/**
	 * Explore a directore and list .php files
	 *
	 * @param string $dir root directory to start exploration from
	 *
	 * @throws Exception if $dir is not a valid directory
	 * @return <multitype:string >
	 */
	protected function exploreDirectory($dir = NULL){
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
				$phpFiles = array_merge($phpFiles, self::exploreDirectory($_file));
			}
		}

		return $phpFiles;
	}

}

