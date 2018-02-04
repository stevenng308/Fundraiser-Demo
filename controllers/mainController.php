<?php
	/*
	* This controller handles the requests coming in and responding with the appropriate data to the view
	*/
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Slim-2.x/Slim/Slim.php');
	require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'reviewController.php');
	\Slim\Slim::registerAutoloader();

	$myDispatch = new Dispatcher();
	$myReview   = new Review();
	$app        = new \Slim\Slim(
		[
		    'templates.path' => '../views'
		]
	);
	$payload = [
		"msg"    => "",
		"status" => "",
		"code"   => "",
	];
	// The form submit route. Routes to the appropriate controller or invokes the dispatcher to make API calls to the model
	$app->post('/submit/:id(/)(/:options)', function ($id = '', $options = '') use($myDispatch, $myReview, $payload){

		$params = [
			"fundraiser_id" => $id,
			"name"          => $_POST["name"],
			"email"         => $_POST["email"],
			"message"       => $_POST["message"],
			"rating"        => $_POST["rating"]
		];
		if($id === "new")
		{
			$params['fundraiser_name'] = $_POST["fundraiser_name"];
		}

		if($result = $myReview->checkSubmitReview($params))
		{
			$payload["msg"]    = $result;
			$payload["code"]   = "200";
			$payload["status"] = "fail";
		} else {
			$insert = $myDispatch->invokeCall('saveReview', $params); //tells dispatcher to begin saving the review
			$insert = json_decode($insert, false);
			if($insert->count > 0)
			{
				$payload["msg"]    = "Your review has been submitted.";
				$payload["code"]   = "200";
				$payload["status"] = "pass";
			} else {
				$payload["msg"]    = "Unable to process your review. Please correct your review.";
				$payload["code"]   = "400";
				$payload["status"] = "fail";
			}
		}
		echo json_encode($payload);
	});

	$app->get('/getAllReviews/', function () use($myDispatch, $myReview) {
	    $reviews = $myDispatch->invokeCall('getAllReviews');
		echo $myReview->getAllReviews($reviews);
	});

	// Gets a single fundraiser's reviews
	$app->get('/viewFundraiser/:id', function($id) use($app, $myDispatch, $myReview) {
	    $reviews    = $myDispatch->invokeCall('getReview', ["id" => $id]);
	    $fundraiser = $myDispatch->invokeCall('getFundraiser', ["fundraiser_id" => $id]);
		$reviews    = $myReview->viewFundraiser($fundraiser, $reviews);
		return $app->render("viewFundraiser.php", ["reviews" => $reviews]);
	});
	$app->run();
