 <?php

/**
 * Admin ThankYou Approval Shortcode
 *
 * Snippet to create a shortcode that will display unapproved shoutouts and allow administrators to approve them.
 */
add_shortcode('snippet_admin_approval','display_thankyous_for_approval');

/* display_thankyous_for_approval:
	Function to display the unapproved thankyous and buttons for admin users to approve them.
	@return the code to be added to the document. */
function display_thankyous_for_approval() {
	
	/* Variables to store the code to be written to the document. */
	$toReturn = "";
	$javascript = "";
	$css = "";
	
	/* Set up sql information for database access. */
	$host = "DATABASE HOST";
	$dbusername = "DATABASE USERNAME";
	$dbpassword = "DATABASE PASSWORD";
	$dbname = "DATABASE NAME";
	
	/* Javascript to initialize the variable to store whether or not there are messages that are unapproved. */
	$javascript .= "var messages_not_approved = false;";
	
	/* Add html to allow the user to remove a Shoutout by id and to approve all unapproved shoutouts. */
	$toReturn .= "
	<div>
		<input type=\"text\" id=\"thankyou_delete_id\" class=\"input\" placeholder=\"Shoutout ID #\"'>
		<button class='blue_button' onclick='trash(document.getElementById(\"thankyou_delete_id\").value);' style='display:inline-block;'>Remove Shoutout</button>
		<span width='100%'>
			<button class='blue_button' onClick='approve(\"all\");' style='display:inline-block;float:right;'>Approve All</button>
		</span>
	</div><br>
	";
	
	
	/* Initialize connection to sql database. */
	$mysqli = new mysqli($host, $dbusername, $dbpassword, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	/* $user: the current wordpress user's id in the database. */
	$user = get_current_user_id();

	/* Check if the user is authorized to view this page and if not, set the location to the homepage after 3 seconds. */
	$sql = "SELECT * FROM approval_users WHERE user_id = $user";
	if ($mysqli->query($sql)->num_rows <= 0) {
		echo "<h1 style='text-align:center;color:red;'>You must be an administrator to view this page.</h1><script>setTimeout(() => {location.replace('/')}, 3000);</script>";
		exit;
	}
	
	/* Add classes to the css styles. */
	$css .= "

			.message {
				background: #014983;
				background: -webkit-linear-gradient(#014983, #012646);
				background:    -moz-linear-gradient(#014983, #012646);
				background:         linear-gradient(#014983, #012646);
				padding:20px;
			}
			
			.blue_button {
				background-color:#014983;
				color:white;
				border-width:2px;
				border-color:#014983;
				border-style:solid;
			}
			
			.blue_button:hover {
				background-color:#0969a4;
				border-color:#0969a4;
			}
			
			.white_button {
				background-color:#ffffff;
				color:#014983;
				border-width:2px;
				border-color:#014983;
				border-style:solid;
			}
			
			.white_button:hover {
				background-color:#0969a4;
				color:white;
			}
			
			input {
				background-color:#0969a4;
				border-color:#0969a4;
			}

	";
	
	/* Query the database to get the unapproved shoutouts. */
	$sql = "SELECT * FROM staff_thankyou WHERE status = 'submitted' ORDER BY thankyou_date DESC";
	$result = $mysqli->query($sql);
	
	/* If there are unapproved messages, display each one iteratively. */
	if ($result->num_rows > 0) {
		
		/* Initialize the block to contain the messages. */
		$toReturn .= "<div id='messages'>";
		
		/* Add javascript to set variable. */
		$javascript .= "messages_not_approved = true;";
		
		/* loop:
			condition: for all rows in $result.
			entrance: $result holds the values from the sql unapproved thankyous query.
			invariant: $row holds one entry from $result.
			exit: All values of $result have been displayed. */
		while ($row = $result->fetch_assoc()) {
			
			/* Store values from entry in variables. */
			$employee_name = $row['employee_name'];
			$thankyou_text = $row['thankyou_text'];
			$thankyou_date = $row['thankyou_date'];
			$employee_department = $row['employee_department'];
			$thankyou_id = $row['thankyou_id'];
			$sender_email = $row['sender_email'];
			
			/* Format and display the values from the entry. */
			$toReturn .= "
			<div class='message'>
				<div width=100%>
					<h1 style=\"color:white;display:inline-block;\">Shoutout to $employee_name in $employee_department</h1>
					<button class='white_button' onClick='approve($thankyou_id)' style=\"display:inline-block;float:right;\">Approve Entry</button>
					<button class='white_button' onClick='trash($thankyou_id)' style=\"display:inline-block;float:right;\">Remove Entry</button>
				</div>
				<p style=\"color:white;\">$thankyou_text</p>
				<p style=\"font-size:12px;color:#cccccc;text-align:right;\" width=\"100%\">Sent from $sender_email on $thankyou_date &nbsp &nbsp[ $thankyou_id ]</p>
			</div>
			<br>
				";
		}
		
		/* close the messages block. */
		$toReturn .= "</div>";
		
	} else {
		$toReturn .= "<div id='messages'><h3 class='message' style='color:white;'>There are no entries currently waiting for approval.<h3></div><br>";
	}
	
	/* Initialize the URL to be redirected to for the database action. */
	$action  = admin_url( 'admin-post.php');
	
	/* Add javascript to initialize functions to be called on button clicks. [Each one creates a form, then submits it with post headers to the admin-post.php url]. */
	$javascript .= "
		
		function approve(thankyou_id) {
			if (thankyou_id == '') {
				return;
			}
			if (thankyou_id == 'all') {
				if (!messages_not_approved) {
					return;
				}
			}
			var form = document.createElement('form');
			form.action = '$action';
			form.method = 'post';
			form.target = '_blank';
			
			var action_element = document.createElement('input');
			action_element.type = 'hidden';
			action_element.name = 'action';
			action_element.value = 'approve_thankyou';
			
			var action_element2 = document.createElement('input');
			action_element2.type = 'hidden';
			action_element2.name = 'thankyou_id';
			action_element2.value = thankyou_id;
			
			form.appendChild(action_element);
			form.appendChild(action_element2);
			
			document.getElementById('messages').appendChild(form);
			form.submit();
			location.reload();
		}
		
		function trash(thankyou_id) {
			if (thankyou_id == '') {
				return;
			}
			var form = document.createElement('form');
			form.action = '$action';
			form.method = 'post';
			form.target = '_blank';
			
			var action_element = document.createElement('input');
			action_element.type = 'hidden';
			action_element.name = 'action';
			action_element.value = 'trash_thankyou';
			
			var action_element2 = document.createElement('input');
			action_element2.type = 'hidden';
			action_element2.name = 'thankyou_id';
			action_element2.value = thankyou_id;
			
			form.appendChild(action_element);
			form.appendChild(action_element2);
			
			document.getElementById('messages').appendChild(form);
			form.submit();
			location.reload();
		}
		
		";
	
	return "<style>$css</style>\n $toReturn \n<script>$javascript</script>";
}



add_action('admin_post_approve_thankyou','approve_thankyou');

/* approve_thankyou:
	Function to change the status of the shoutout from submitted to approved in the database and send emails to the proper endpoints. */
function approve_thankyou() {
	
	/* Get the id value passed as a post request */
	$thankyou_id = $_POST['thankyou_id'];
	
	/* Set up sql information for database access. */
	$host = "DATABASE HOST";
	$dbusername = "DATABASE USERNAME";
	$dbpassword = "DATABASE PASSWORD";
	$dbname = "DATABASE NAME";
	
	/* Initialize connection to sql database. */
	$mysqli = new mysqli($host, $dbusername, $dbpassword, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	/* $user: the current wordpress user's id in the database. */
	$user = get_current_user_id();

	/* Check if the user is authorized to view this page and if not, set the location to the homepage after 3 seconds. */
	$sql = "SELECT * FROM approval_users WHERE user_id = $user";
	if ($mysqli->query($sql)->num_rows <= 0) {
		echo "<h1 style='text-align:center;color:red;'>You must be an administrator to view this page.</h1><script>setTimeout(() => {window.close();}, 3000);</script>";
		exit;
	}
	
	/* $toEmail: the variable to store the entries to reach the email endpoints. */
	$toEmail = "";
	
	/* Approve all shoutouts if $thankyou_id equals 'all'. */
	if ($thankyou_id == "all") {
		$toEmail = $mysqli->query("SELECT * FROM staff_thankyou WHERE status = 'submitted'");
		$sql = "UPDATE staff_thankyou SET status = 'accepted' WHERE status = 'submitted'";
	} else {
		$toEmail = $mysqli->query("Select * from staff_thankyou where thankyou_id = '$thankyou_id'");
		$sql = "UPDATE staff_thankyou SET status = 'accepted' WHERE thankyou_id = '$thankyou_id'";
	}
	if ($mysqli->query($sql)) {
		if ($toEmail->num_rows > 0) {
			while ($row = $toEmail->fetch_assoc()) {
				
				/* Initialize variables to be passed into the mail() function. */
				$to = $row['employee_email'];
				$from = "hr@gordon.edu";
				$subject = 'Shoutout from Peer at Gordon College';
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: '.$from."\r\n".
					'Reply-To: '.$from."\r\n" .
					'X-Mailer: PHP/' . phpversion();
				$message = '<html><body>
				<style>
				
				.message {
					background: #014983;
					background: -webkit-linear-gradient(#014983, #012646);
					background:    -moz-linear-gradient(#014983, #012646);
					background:         linear-gradient(#014983, #012646);
					padding:20px;
				}
				
				</style>
				<h3>
				You have received an approved public shoutout on the \'humanresources.gordon.edu\' website.
				</h3>
				<div class=\"message\">
					<p style=\"color:white;\">' . $row['thankyou_text'] . '</p>
				</div>
				</body></html>';
				
				/* Send email to the staff member shouted-out to. */
				mail($to,$subject,$message,$headers);
				
				/* Notify the sender that their message has been approved. */
				mail($row['sender_email'],'Your Message to ' . $row['employee_name'] . ' has been Approved','<h1>Message contents: </h1><br>' . $message,$headers);
			}
			
			
		}
		/* Close the window with javascript. */
		echo "<script>window.close();</script>";
	} else {
		echo "<h1 style='text-align:center;color:red;'>Error: Unable to approve shoutout. [Troubleshooting: this code may be found in \"Snippets/Admin ThankYou Approval Shortcode\"]</h1>";
	}
	
}




add_action('admin_post_trash_thankyou','trash_thankyou');

/* trash_thankyou:
	Function to set the status of a shoutout by id to trashed and not display it on the homepage. */
function trash_thankyou() {
	
	/* Get the id value passed as a post request */
	$thankyou_id = $_POST['thankyou_id'];
	
	/* Set up sql information for database access. */
	$host = "DATABASE HOST";
	$dbusername = "DATABASE USERNAME";
	$dbpassword = "DATABASE PASSWORD";
	$dbname = "DATABASE NAME";
	
	/* Initialize connection to sql database. */
	$mysqli = new mysqli($host, $dbusername, $dbpassword, $dbname);
	if ($mysqli->connect_errno) {
		echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	}
	
	/* Trash all shoutouts if $thankyou_id equals 'all'. */
	if ($thankyou_id == 'all') {
		$mysqli->query("UPDATE staff_thankyou SET status = 'trashed' WHERE status = 'approved'");
		echo "<script>window.close();</script>";
		return;
	}
	
	/* Query the database and set the shoutout status to trashed based on the passed thankyou_id. */
	$sql = "UPDATE staff_thankyou SET status = 'trashed' WHERE thankyou_id = '$thankyou_id'";
	if ($mysqli->query($sql)) {
		/* Close the window with javascript. */
		echo "<script>window.close();</script>";
	} else {
		echo "<h1 style='text-align:center;color:red;'>Error: Unable to remove shoutout. [CTS Troubleshooting: this code may be found in \"Snippets/Admin ThankYou Approval Shortcode\"]</h1>";
	}
	
}
