<?php 

class Zend_View_Helper_Widget extends Zend_View_Helper_Abstract{
     
    /**
     * Available Widget in librairy/Widget
     */

    private $_available_widget = array(
        'Table',
        'Chartflot',
        'Highcharts',
    	'Message'
    );
    
    /**
     * Call widget and return this
     * 
     * @param string $name Name of widget 
     * @param array $data
     * @return string  HTML code
     */
    public function Widget($name, $data, $options = array()) {

        if(!in_array($name, $this->_available_widget)){
            return '';
        }
        
        $widget_class = 'Widgets_Controllers_' . $name;
        
        // call the widget
        $widgetObj = new $widget_class($data, $options);
        
        
        $render = $widgetObj->render();
        
        return $render;
    }
}