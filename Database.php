<?
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

	class Database{
		private $db = null;
		function __construct(){
			$hostName = Config::$hostName;
			$port = Config::$port;
			$user = Config::$user;
			$pass = Config::$pass;
			$dbName	= Config::$dbName;
			if(!isset($this->db) && is_null($this->db)){
				$this->db = new mysqli($hostName, $user, $pass, $dbName);
				$this->db->set_charset("utf8");
			} else {
				echo 'connection already established'; //LOGGER
			}
		}

		function __destruct(){
			$this->db->close();
		}

		function getDbConnection(){
			return $this->db;
		}

}
?>
