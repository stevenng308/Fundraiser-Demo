<?php
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	/*
	* This controller handles the backend validation of the formdata and any post processing of the data received from the model through the dispatcher
	*
	*/
	class Review
    	{
		private $dispatch = null;
		function __construct()
        	{
			$this->dispatch = new Dispatcher();
        	}

		function getAllReviews($reviews)
		{
			return json_decode($reviews, true);
		}

		function checkSubmitReview(&$params)
        	{
			$foundFundraiser  = $this->_checkFundraiserExists($params);
			$foundExistReview = false;
			if($params['fundraiser_id'] === "new")
			{
				if($foundFundraiser) //a fundraiser is found based on the user's submitted fundraiser name
				{
					$fundId = $this->dispatch->invokeCall("getFundraiser", $params);
					$found = json_decode($fundId, true);
					$params['fundraiser_id'] = $found["data"]["id"];
					$foundExistReview = $this->_checkDuplicateReview($params);
				}
			} else {
				$foundExistReview = $this->_checkDuplicateReview($params);
			}
			if(!$foundFundraiser && $params['fundraiser_id'] !== "new")
			{
				return "Could not find fundraiser.";
			} else if($foundExistReview) {
				return "You have already submitted a review for this fundraiser.";
			} else {
				return $this->_checkFormData($params);
			}
        	}

		function viewFundraiser($fundraiserInfo, $reviews)
		{
				$reviews                    = json_decode($reviews, true);
				$fundraiserInfo             = json_decode($fundraiserInfo, true);
				if($fundraiserInfo["count"] > 0)
				{
					$reviews["fundraiserName"] = $fundraiserInfo["data"]["fundraiser_name"]; //enrich with fundraiser details with its reviews
					$reviews["fundraiserId"]   = $fundraiserInfo["data"]["id"];
				}
				return $reviews;
		}

		function _checkDuplicateReview($params)
		{
			$found = $this->dispatch->invokeCall("checkDuplicateReview", $params);
			$found = json_decode($found, true);
			if($found["count"] > 0)
			{
				return true;
			} else {
				return false;
			}
		}

		// Backend PHP implementation of the form validator found in JS
		function _checkFormData($params)
		{
			if(empty($this->_validateRating($params["rating"])))
			{
				return "Please enter a rating between 1 - 5.";
			} else if(empty($this->_validateEmail($params["email"]))){
				return "Please enter a valid email address.";
			} else {
				$formResponse = [
					"name"            => "Please enter a name up to 50 characters.",
		            "email"           => "Please enter an email address up to 50 characters.",
		            "rating"          => "Please enter a rating between 1 - 5.",
		            "message"         => "Please enter a review message up to 500 characters.",
		            "fundraiser_name" => "Please enter a fundraiser name up to 50 characters."
				];
				foreach($params as $label => $data)
				{
					$flag = false;
					switch($label)
					{
						case "name":
						$flag = $this->_validateLength($data, 50);
						break;
					    case "email":
						$flag = $this->_validateLength($data, 50);
						break;
					    case "rating":
						$flag = $this->_validateLength($data, 1);
						break;
					    case "message":
						$flag = $this->_validateLength($data, 500);
						break;
					    case "fundraiser_name":
						$flag = $this->_validateLength($data, 50);
						break;
						default:
							$flag = true;
							break;
					}
					if(!$flag)
					{
						return $formResponse[$label];
					}
				}
				return false;
			}
		}

		function _checkFundraiserExists($params)
		{
			$found = $this->dispatch->invokeCall("getFundraiser", $params);
			$found = json_decode($found, true);
			if($found["count"] > 0)
			{
				return true;
			} else {
				return false;
			}
		}

		function _validateEmail($email)
		{
			$regex = "/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/";
			return preg_match($regex, mb_strtolower($email));
		}

		function _validateRating($num)
		{
			$regex = "/([1-5])/";
			return preg_match($regex, $num);
		}

		function _validateLength($str, $length)
		{
			return (strlen($str) <= $length) ? true : false;
		}
    	}
