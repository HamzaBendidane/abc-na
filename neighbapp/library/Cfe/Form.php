<?php

/**
 * Description
 *
 * License:
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * @copyright 2012 Alter Way
 * @author Benoit Tavernier <benoit.tavernier@alterway.fr>
 * @license
 * @version $Id:$
 * @package Core
 * @since 1.0
 */
class Cfe_Form extends Zend_Form
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Modifie le decorateur global du formulaire, pour s'adapter à la Webapp ou a d'autre mise en forme particulière
     * @author romain.causse@surikate.com 
     * @param String $module  ex: Webapp
     * @param Array $options : theme et Array decorator_options
     */
    public function setDecorator($module, Array $options){
        
        // class sur le theme du fomrmulaire:  a, b, c, transparent
        isset($options['theme'])? $theme = $options['theme']: $theme = 'a';
        
        isset($options['decorator_options']) ? $decorator_options = $options['decorator_options'] :  $decorator_options = array();
        
        // ajoute le chemin de nos decorator
        $this->addElementPrefixPath('Class_Form_Decorator_', 'Class/Form/Decorator/', 'decorator');
        
        // reinitialise les décorateurs pour n'utiliser que celui du module choisie 
        // Le decorateur du module est là pour décorer TOUS les éléments du formulaire comme on le souhaite
        $this->setElementDecorators(array(array($module, $decorator_options)));
        
        // Wrapper du formulaire : Decorator HtmlTag avec div.ui-body.ui-body-a comme Wrapper
        $this->addDecorators(array(
                array('HtmlTag', 
                           array('tag' => 'div', 'class' => 'ui-body ui-body-'.$theme))));
    }
    
    /**
     * Modifie les dorateurs par défaut de Zend
     * Set des class par défaut
     */
     public function setAllDefaultDecorator(){
        $decorators = array(
                array('Errors', array('placement' => 'PREPEND', 'class' => "display-error")),
                'ViewHelper',
                'Description',
                array('HtmlTag', array('class' => "fieldset")),
                array('Label', array('class' => 'label')));
        
        $this->setElementDecorators($decorators);
        $this->addDecorators(array(
                array('HtmlTag',
                        array('tag' => 'div', 'class' => 'userForm'))));
    }    
}