<?
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'mainModel.php');
	/*
	* This class acts as a proxy to the database and submitting the data needed for the model to query. Responds back in JSON with a data and count attribute.
	*/

	class Dispatcher{
		private $model = null;
		function __construct(){
			if(!isset($this->model) && is_null($this->model)){
				$this->model = new mainModel();
			} else {
				echo 'DB connection already established'; //LOGGER
			}
		}

		function getResult(){
			return $this->obj;
		}

		function invokeCall($action, $options = array()){
			$model = $this->_getDispatcher();
			if(method_exists($model, $action)){
				return json_encode($model->$action($options));
			} else {
				return null; //EXECEPTION
			}
		}

		function _getAPIResponse($status = 101, $msg = 'Unexpected System Error. API still functioning.'){
			$response = array(
				'statusCode' => $status,
				'msg'        => $msg
			);
			return json_encode($response);
		}

		private function _getDispatcher(){
			return $this->model;
		}
	}
?>
