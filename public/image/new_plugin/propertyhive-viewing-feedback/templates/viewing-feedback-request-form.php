<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>

	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	
	<?php wp_head(); ?>

	<style type="text/css">

		body {
			background-color: #e2e2e2;
			padding: 2% 10% 10% 10%;
		}

		input[type="radio"], label.inline {
			display: inline;
		}

		label {
			font-weight: normal;
		}

		h1 {
			text-align: center;
		}

		.feedback {
			margin: 1% auto 1% auto;
		}

		#submit-feedback {
			text-align: center;
			width: 100%;
		}

		#feedback-wrapper {
			font-size:16px;
			margin-top:1%;
		}

		.left-col { 
			float:left; 
			width:48%; 
		}

		.right-col { 
			float:right; 
			width:48%; 
		}

		@media (max-width:767px) {

			.left-col { 
				float:none; 
				width:100%; 
				margin-bottom:30px;
			}

			.right-col { 
				float:none; 
				width:100%; 
			}

		}

	</style>

</head>

<body <?php body_class(); ?>>

	<h1><?php echo get_bloginfo('name'); ?></h1>

	<div id="feedback-wrapper"> <!-- Upon submission the contents of this dvi will be replaced with the contents of templates/viewing-feedback-request-{interested|not-interested}-thanks.php --> 

		<div class="left-col">

			<p>Hello <?php echo $applicant_names_string; ?>,</p>

			<p>Following your recent viewing of <strong><?php echo $property->get_formatted_full_address(); ?></strong> at <strong><?php echo date("H:i", strtotime($viewing->start_date_time)); ?> on <?php echo date("jS F Y", strtotime($viewing->start_date_time)); ?></strong> we'd now like to hear your thoughts on the property.</p>
		
			<p>Please select below whether you are interested in the property and wish to discuss this further.</p>

			<hr>

			<form id="viewing-feedback-form">

				<div>
					<label>
						<input type="radio" id="viewing-feedback-request-interested" name="viewing-feedback-request-interest" value="true" checked>
						I am interested in this property
					</label>
					
				</div>

				<div>
					<label>
						<input type="radio" id="viewing-feedback-request-not-interested" name="viewing-feedback-request-interest" value="false">
						I am not interested in this property
					</label>
					 
				</div>

				<br>
				<div class="feedback">
					<label for="viewing-feedback-request-note">Additional Feedback:</label>
					<textarea id="viewing-feedback-request-note" placeholder="What did you like about the property? What put you off?"></textarea>
				</div>

				<input type="submit" name="viewing-feedback" value="Submit Feedback" id="submit-feedback">
			</form>

		</div>

		<div class="right-col">

			<img style="width:100%;" src="<?php echo $property->get_main_photo_src('large'); ?>" alt="<?php echo get_the_title($property->id); ?>">

		</div>

	</div>

</body>

</html>