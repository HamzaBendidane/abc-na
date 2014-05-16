<?php
class Widgets_Controllers_Highcharts extends Widgets_Controllers_Abstract {
	
	/**
	 * Construit les Graphs avec highcharts
	 * http://www.highcharts.com/
	 *
	 * exemple de data attendu :
	 *
	 * @return string
	 */
	protected $css = array ()

	;
	protected $js = array (
			'/web/js/highcharts.js',
			'/web/js/highcharts-more.js' 
	);
	public function render() {
		parent::render ();
		
		$dataArray = $this->data;
		
		// Type 1 par d̩faut si la valeur type n'est pas renseign̩ ou n'existe pas
		
		$dataChart = $this->validateChart ( $dataArray );
		if ($dataChart) {
			return $this->view->partial ( "highcharts/".$dataArray [0] ["type"] . ".phtml", array (
					'data' => $dataChart,
					'id_instance' => self::$id_instance 
			) );
		}
		
		
		
		return FALSE;
	}
	
	public function validateChart($aData){
		
		$chartType = isset($aData [0] ["type"])? $aData [0] ["type"] : "none";
		
		switch ($aData [0] ["type"] ){
			case "areaspline":
				return $this->checkAreasplineData($aData);
				break;
			case "column":
				return $this->checkColumnData($aData);
				break;
			case 'pie':
				
				return $this->checkPieData($aData);
			
		}
		return FALSE;
		
	}
	
	
	/**
	 * 
	 */
	
	public function checkPieData($aData){
		
		return $aData;
	}
	
	/**
	 * check and validate column data
	 */
	public function  checkColumnData($aData){
	
		if (!(isset($aData['legend']) && ($aData['legend']=='false' || $aData['legend']=='true'))) {
			$aData['legend']='false';
		}
		
		return $aData;
	}
	/**
	 * check and validate Areaspline data
	 * @param unknown $aData
	 * @return string
	 */
	public function  checkAreasplineData($aData){
	
		$aData[0]["text"] = (isset($aData[0]["text"]) )? $aData[0]["text"] : "";
		$aData[0]["titleyAxis"] =(isset($aData[0]["titleyAxis"])) ? $aData[0]["titleyAxis"] : "";
		return $aData;
	}
	
}