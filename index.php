<?php
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Dispatcher.php');
	$myDispatch = new Dispatcher();
	$reviews = $myDispatch->invokeCall('getAllReviews');
	$reviews = json_decode($reviews, true);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Steven Ng - Fundraiser Review Demo</title>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link rel="stylesheet" href="assets/css/sticky-footer.css">
	<link rel="stylesheet" href="assets/css/fundraiser.css">
  </head>
  <body>
		<!-- Fixed navbar -->
    <nav class="navbar navbar-default navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <h1>Top Fundraisers</h1>
        </div>
		<div class="navbar">
			<button type="button" class="btn btn-success" data-toggle="modal" data-target="#submitReview" data-fund_id=""><i class="fa fa-plus-circle"></i> Add New Fundraiser Review</button>
		</div>
      </div>
    </nav>

		<!-- Begin page content -->
    <div class="container" srtle="min-width: 518px">
		<table class="table table-bordered table-striped table-hover">
            <thead>
                <th>Fundraisers</th>
                <th>Average Rating</th>
                <th>Last Updated</th>
            </thead>
            <tbody>
                <?php if($reviews['count'] > 0)
				{
					foreach($reviews['data'] as $review){
					$formatDate   = new Datetime($review["date"]);
					$badgeColor   = ($review["rating"] < 3) ? "badge-info" : "badge-primary";
					$fundName     = htmlentities($review["fundraiserName"]);
					$reviewRating = round($review["rating"], 1);
                    echo "<tr>
                            <td><a href='/controllers/mainController.php/viewFundraiser/{$review['fundraiserId']}'>{$fundName}</a></td>
                            <td><span class='badge {$badgeColor}'>{$reviewRating} <i class='fa fa-star'></i></span></td>
                            <td>{$formatDate->format("M d, Y")}</td>
                        </tr>";
                	};
				} else {
					echo "<td colspan='4'>No reviews found. Please add a new fundraiser review.</td>";
				}
				?>
            </tbody>
        </table>
    </div>

	<div class="modal fade" id="submitReview" tabindex="-1" role="dialog" aria-labelledby="submitReviewLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="submitReviewLabel">Rate this fundraiser</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="reviewForm">
              <div class="form-group">
                <label for="fundraiser-name" class="col-form-label">Fundraiser Name:</label>
                <input type="text" class="form-control" id="fundraiser-name">
              </div>
              <div class="form-group">
                <label for="recipient-name" class="col-form-label">Name:</label>
                <input type="text" class="form-control" id="reviewer-name">
              </div>
              <div class="form-group">
                <label for="recipient-email" class="col-form-label">Email Address:</label>
                <input type="text" class="form-control" id="reviewer-email">
              </div>
              <div class="form-group">
                <label for="message-rating" class="col-form-label">Rate:</label>
                <select id="reviewer-rating">
                    <option id="default" value="5" selected>5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>
              </div>
              <div class="form-group">
                <label for="message-text" class="col-form-label">Review:</label>
                <textarea class="form-control" id="reviewer-text"></textarea>
              </div>
			  <input id="fundraiser-id" value="new" type="hidden"></input>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="submit">Submit</button>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="container">
        <p class="text-muted">&copy; <a href="https://github.com/stevenng308">Steven Ng</a></p>
      </div>
    </footer>

    <!-- jQuery first, then Bootstrap JS. -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<script src="/assets/js/viewFundraiser.js"></script>
    <script>
        const redirectUrl = "/";
    </script>
  </body>
</html>
