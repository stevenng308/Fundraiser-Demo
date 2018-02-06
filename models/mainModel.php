<?php
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR .  'Database.php');
	/*
	* This class handles the interaction with the DB. Responds back with an object.
	*/
	class mainModel
	{
		public $database = null;

		function __construct()
		{
			$this->database = new Database();
			if($this->database->getDbConnection()->connect_error){
				die('ERROR!! CONNECTION FAILED! ' . $this->database->getDbConnection()->connect_error  . "\r\n");
			} else {
				// echo 'SUCCESS!! ' . $this->database->getDbConnection()->host_info . "\r\n"; //log
			}
		}

		function checkDuplicateReview($arr)
		{
			$fundraiser_id = $this->_escapeString($arr['fundraiser_id']);
			$email         = $this->_escapeString($arr['email']);
			$result        = $this->_queryDb("SELECT fundraiser_reviews.id FROM fundraiser_reviews INNER JOIN reviewers ON (fundraiser_reviews.reviewer_id = reviewers.id) WHERE reviewers.email = '$email' AND fundraiser_reviews.fundraiser_id = $fundraiser_id LIMIT 1");
			$result        = $result->fetch_assoc();
			$obj           = new stdClass();
			$obj->data     = new stdClass();
			if(!empty($result))
			{
				$obj->count      = 1;
				$obj->data->id   = $result['id'];
			} else {
				$obj->count      = 0;
			}
			return $obj;
		}

		function createNewFundraiser($name)
		{
			$this->_queryDb("INSERT INTO fundraisers (`fundraiser_name`) VALUES ('{$name}')");
			return $this->_getInsertId();
		}

		function createNewReviewer($name, $email)
		{
			$this->_queryDb("INSERT INTO reviewers (`name`, `email`) VALUES ('{$name}', '{$email}')");
			return $this->_getInsertId();
		}

		function getAllReviews()
		{
			$results = $this->_queryDb("SELECT fundraiser_reviews.id, fundraisers.fundraiser_name, fundraiser_reviews.fundraiser_id, AVG(fundraiser_reviews.rating) as rating, MAX(fundraiser_reviews.date) as date FROM fundraiser_reviews INNER JOIN reviewers ON (reviewers.id = fundraiser_reviews.reviewer_id) INNER JOIN fundraisers ON (fundraisers.id = fundraiser_reviews.fundraiser_id) GROUP BY fundraiser_reviews.fundraiser_id ORDER BY rating DESC, fundraisers.fundraiser_name ASC");
			return $this->_parseResults($results);
		}

		function getFundraiser($fund)
		{
			if(is_array($fund))
			{
				$fundId = $this->_escapeString($fund['fundraiser_id']);
			}
			if($fundId === "new")
			{
				return $this->getFundraiserByName($fund);
			}
			$result                     = $this->_queryDb("SELECT * FROM fundraisers WHERE id = $fundId LIMIT 1");
			$result                     = $result->fetch_assoc();
			$obj                        = new stdClass();
			$obj->data                  = new stdClass();
			if(!empty($result))
			{
				$obj->count                 = 1;
				$obj->data->id              = $result['id'];
				$obj->data->fundraiser_name = $result['fundraiser_name'];
			} else {
				$obj->count      = 0;
			}
			return $obj;
		}

		function getFundraiserByName($fund)
		{
			if(is_array($fund))
			{
				$fund = $this->_escapeString($fund['fundraiser_name']);
			}
			$result                     = $this->_queryDb("SELECT * FROM fundraisers WHERE fundraiser_name = '$fund' LIMIT 1");
			$result                     = $result->fetch_assoc();
			$obj                        = new stdClass();
			$obj->data                  = new stdClass();
			if(!empty($result))
			{
				$obj->count                 = 1;
				$obj->data->id              = $result['id'];
				$obj->data->fundraiser_name = $result['fundraiser_name'];
			} else {
				$obj->count      = 0;
			}
			return $obj;
		}

		function getReview($arr)
		{
			$id = $this->_escapeString($arr['id']);
			$results = $this->_queryDb("SELECT * FROM fundraiser_reviews INNER JOIN reviewers ON (reviewers.id = fundraiser_reviews.reviewer_id) WHERE fundraiser_id = $id ORDER BY fundraiser_reviews.rating DESC");
			return $this->_parseResults($results, "getReview");
		}

		function getReviewerByEmail($email)
		{
			if(is_array($email))
			{
				$email = $this->_escapeString($email['email']);
			}
			$result                     = $this->_queryDb("SELECT * FROM reviewers WHERE email = '$email' LIMIT 1");
			$result                     = $result->fetch_assoc();
			$obj                        = new stdClass();
			$obj->data                  = new stdClass();
			if(!empty($result))
			{
				$obj->count      = 1;
				$obj->data->id   = $result['id'];
				$obj->data->name = $result['name'];
			} else {
				$obj->count      = 0;
			}
			return $obj;
		}

		function saveReview($arr)
		{
			$fundId  = $this->_escapeString($arr["fundraiser_id"]);
			$name    = $this->_escapeString($arr["name"]);
			$email   = $this->_escapeString($arr["email"]);
			$message = $this->_escapeString($arr["message"]);
			$rating  = $this->_escapeString($arr["rating"]);

			if($fundId === "new")
			{
				$fundId = $this->createNewFundraiser($this->_escapeString($arr["fundraiser_name"]));
				if(empty($fundId))
				{
					return ["data" => null, "count" => 0];
				}
			}

			$reviewerId = $this->getReviewerByEmail($email);
			if($reviewerId->count === 0)
			{
				$reviewerId = $this->createNewReviewer($name, $email);
				if(empty($reviewerId))
				{
					return ["data" => null, "count" => 0];
				}
			} else {
				$reviewerId = $reviewerId->data->id;
			}
			$now        = new DateTime();
			$this->_queryDb("INSERT INTO fundraiser_reviews (`fundraiser_id`, `reviewer_id`, `review`, `rating`, `date`) VALUES ('{$fundId}', '{$reviewerId}', '{$message}', '{$rating}', '{$now->format("Y-m-d H:i:s")}')");
			return ["data" => $this->_getInsertId(), "count" => 1];
		}

		/*
		* Helper function to standardize the response back to the dispatcher
		*/
		private function _parseResults($results, $section = null)
		{
			$obj = new stdClass();
			$obj->data = new stdClass();
			$obj->count = 0;
			if($results){
				$num_rows = $results->num_rows;
				$obj->count = $num_rows;
				for($i = 0; $i < $num_rows; $i++){
		          $result                          = $results->fetch_assoc();
				  // var_dump($result);
		          $hash                            = ($result['id']);
		          $obj->data->$hash                = new stdClass();
		          $obj->data->$hash->id            = $result['id'];
		          $obj->data->$hash->fundraiserId  = $result['fundraiser_id'];
		          $obj->data->$hash->rating        = $result['rating'];
		          $obj->data->$hash->date          = $result['date'];
				  if($section === "getReview")
				  {
					  $obj->data->$hash->review        = $result['review'];
					  $obj->data->$hash->reviewerId    = $result['reviewer_id'];
					  $obj->data->$hash->reviewerName  = $result['name'];
					  $obj->data->$hash->reviewerEmail = $result['email'];
				  }
				  if(!empty($result["fundraiser_name"]))
				  {
					  $obj->data->$hash->fundraiserName = $result['fundraiser_name'];
				  }
				}
			}
			return $obj;
		}
		private function _escapeString($str)
		{
			return $this->database->getDbConnection()->real_escape_string($str);
		}
		private function _getInsertId()
		{
			return $this->database->getDbConnection()->insert_id;
		}
		private function _queryDb($query)
		{
			return $this->database->getDbConnection()->query($query);
		}
	}
