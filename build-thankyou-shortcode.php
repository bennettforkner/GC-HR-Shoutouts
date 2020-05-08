 <?php

/**
 * Build ThankYou Shortcode
 *
 * Snippet to create a shortcode that displays a form to the user for writing a thankyou/shoutout.
 */
add_shortcode('snippet_build_thankyou','build_thankyou_form');

/* build_thankyou_form:
	Function to set up and display a form to the user so that they can submit a shoutout about their coworkers. */
function build_thankyou_form() {
	
	/* $toReturn: string variable to hold all of the HTMl code to be returned. */
	$toReturn = "";
	
	/* $javascript: string variable to hold all of the javascript code to be returned. */
	$javascript = "";
	
	/* $css: string variable to hold all of the css styles to be returned. */
	$css = "";
	
	/* Add styles for the buttons on the page. */
	$css .= "
	
			.blue_button {
				padding:30px;
				background-color:#014983;
				color:white;
				border-width:2px;
				border-color:#014983;
				border-style:solid;
				border-radius:0px;
			}
			
			.blue_button:hover {
				background-color:#0969a4;
				border-color:#0969a4;
			}
	
	";
	
	/* Add javascript to be executed on form submit [clear values and set confirmation message] and on form change [remove confirmation message]. */
	$javascript .= "<script>
	function empty_form() {
		
		if (document.getElementById('employee_email').value != '' && document.getElementById('sender_email').value != '' && document.getElementById('employee_department').value != '' && document.getElementById('thankyou_text').value != '') {
			document.getElementById('employee_email').value = '';
			document.getElementById('sender_email').value = '';
			document.getElementById('employee_name').value = '';
			document.getElementById('employee_department').value = '';
			document.getElementById('thankyou_text').value = '';
			
			document.getElementById('submit_message').style.visibility = 'visible';
		} else {
			document.getElementById('error_message').style.visibility = 'visible';
		}
	}
	
	function hide_submit_message() {
		document.getElementById('submit_message').style.visibility = 'hidden';
		document.getElementById('error_message').style.visibility = 'hidden';
	}
	</script>";
	
	/* $here: the url for the admin-post.php page call to return to after completing its action (manual redirect curently in place). */
    $here = esc_url( home_url( $_SERVER['REQUEST_URI'] ) );
	
	/* On form submit, redirect to admin-post page to process data. */
    $action  = admin_url('admin-post.php');
	
	/* Add HTML to initialize and display form. */
	$toReturn .= "
			<p id='submit_message' style='color:green; visibility:hidden;'><b>Your thank you message has been submitted to the approval queue.</b></p>
			<p id='error_message' style='color:red; visibility:hidden;'><b>Please fill all required fields.</b></p>
			<div class='et_pb_contact'>
				<form class=\"et_pb_contact_form clearfix\" autocomplete=\"off\" method=\"post\" action=\"$action\" target=\"_blank\">
					<input type='hidden' name='action' value='submit_thankyou_to_db'>
					<input autocomplete=\"false\" name=\"hidden\" type=\"text\" style=\"display:none;\">
					
					
					<p class=\"et_pb_contact_field et_pb_contact_field_0 et_pb_contact_field_half\" data-id=\"name\" data-type=\"input\">
						<label for=\"et_pb_contact_name_0\" class=\"et_pb_contact_form_label\">Employee's Name</label>
						<input type=\"text\" id=\"employee_name\" class=\"input\" value=\"\" name=\"employee_name\" data-required_mark=\"required\" data-field_type=\"input\" placeholder=\"Employee's Name\" onChange='hide_submit_message()'>
					</p>

					<p class=\"et_pb_contact_field et_pb_contact_field_1 et_pb_contact_field_half et_pb_contact_field_last\" data-id=\"department\" data-type=\"input\">
						<label for=\"et_pb_contact_department_0\" class=\"et_pb_contact_form_label\">Employee's Department</label>
						<input type=\"text\" id=\"employee_department\" class=\"input\" value=\"\" name=\"employee_department\" data-required_mark=\"required\" data-field_type=\"input\" data-original_id=\"department\" placeholder=\"Employee's Department\" onChange='hide_submit_message()'>
					</p>
					
					
					
					<p class=\"et_pb_contact_field et_pb_contact_field_2 et_pb_contact_field_half\" data-id=\"employee_email\" data-type=\"input\">
						<label for=\"et_pb_contact_employee_email_0\" class=\"et_pb_contact_form_label\">Employee's Email (optional)</label>
						<input type=\"text\" id=\"employee_email\" class=\"input\" value=\"\" name=\"employee_email\" data-field_type=\"input\" data-original_id=\"employee_email\" placeholder=\"Employee's Email \" onChange='hide_submit_message()'>
					</p>
					
					<p class=\"et_pb_contact_field et_pb_contact_field_3 et_pb_contact_field_half et_pb_contact_field_last\" data-id=\"sender_email\" data-type=\"input\">
						<label for=\"et_pb_contact_sender_email_0\" class=\"et_pb_contact_form_label\">Your Email (not visible)</label>
						<input type=\"text\" id=\"sender_email\" class=\"input\" value=\"\" name=\"sender_email\" data-field_type=\"input\" data-original_id=\"sender_email\" placeholder=\"Your Email (not visible)\" onChange='hide_submit_message()'>
					</p>
					


					<p class=\"et_pb_contact_field et_pb_contact_field_2 et_pb_contact_field_last\" data-id=\"message\" data-type=\"text\">
						<label for=\"et_pb_contact_message_0\" class=\"et_pb_contact_form_label\">I would like to express thanks to this person for...</label>
						<textarea name=\"thankyou_text\" id=\"thankyou_text\" class=\"et_pb_contact_message input\" data-required_mark=\"required\" data-field_type=\"text\" data-original_id=\"message\" placeholder=\"I would like to express thanks to this person for...\" onChange='hide_submit_message()'></textarea>
					</p>



					<div class=\"et_contact_bottom_container\">
						<button type=\"submit\" name=\"et_builder_submit_button\" class=\"et_pb_contact_submit et_pb_button blue_button\" onClick='setTimeout(() => {empty_form()}, 3000);'>Submit</button>
					</div>
				</form>
			</div>
	
	";
	return $toReturn . $javascript;
}

add_action('admin_post_nopriv_submit_thankyou_to_db','submit_thankyou');
add_action('admin_post_submit_thankyou_to_db','submit_thankyou');

/* submit_thankyou:
	function to connect to the SQL database and insert the new record. */
function submit_thankyou() {
	
	/* Receive the passed information in the headers. */
	$employee_name = $_POST['employee_name'];
	$employee_department = $_POST['employee_department'];
	$thankyou_text = $_POST['thankyou_text'];
	$employee_email = $_POST['employee_email'];
	$sender_email = $_POST['sender_email'];
	
	/* Set site timezone and get the date stamp. */
	date_default_timezone_set('America/New_York');
	$d1 = new Datetime();
	$date = $d1->format('Y-m-d H:i:s');
	
	/* Set up sql information for database access. */
	$host = "DATABASE HOST";
	$dbusername = "DATABASE USERNAME";
	$dbpassword = "DATABASE PASSWORD";
	$dbname = "DATABASE NAME";

	/* Initialize connection to sql database. */
	$mysqli = new mysqli($host, $dbusername, $dbpassword, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		$error = "Unable to communicate with database.";
	}
	
	/* Check if all values entered and setup mySQL query. */
	if ($employee_name && $employee_department && $thankyou_text && $sender_email) {
		$sql = "INSERT INTO staff_thankyou (employee_name,employee_department,employee_email,thankyou_text,sender_email) VALUES ('$employee_name','$employee_department','$employee_email','$thankyou_text','$sender_email')";
			
		/* Send an email to the head of HR to notify of a new & unapproved shoutout. */
		mail('admin@yourdomain.com','New message at *url*',('There is a new message on the website waiting to be approved. Please visit https://humanresources.gordon.edu/approval-queue to approve or deny it.'));
	} else {
		$sql = "no query";
		$error = "Not all required fields are filled. No query has been submitted.";
	}
	
	/* Insert values into mySQL database */
	if ($mysqli->query($sql)) {
		echo "<h1>closing window</h1>";
		echo "<script>window.close();</script>";
	} else {
		$error = "Error in passing information to database.";
	}
	
	/* If an error message has been updated and the window has not been closed, keep the window open and display the error message with a contact email/number for CTS. */
	if ($error) {
		echo "<h3 style='color:red;text-align:center;' width:100%>$error Please try again.<br>If the problem persists, please contact CTS at <a href='mailto:cts@gordon.edu' style='color:red;'>cts@gordon.edu</a> or <a href='tel:9788674500' style='color:red;'>978.867.4500</a>.</h3><p style='text-align:center;' width:100%>You may close this page.</p>";
	}
}
