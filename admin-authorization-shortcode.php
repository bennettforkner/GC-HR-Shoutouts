 <?php

/**
 * Admin Authorization Shortcode
 *
 * Snippet to allow shortcode to display authorized users on a page and allow an authorized user to authorize others.
 */
add_shortcode('snippet_authorization_control','display_authorization_control');

function display_authorization_control() {
	
	/* Variables to store the code to be written to the document. */
	$toReturn = "";
	$javascript = "";
	$css = "";
	
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
			
			input {
				background-color:#0969a4;
				border-color:#0969a4;
			}

	";
	
	/* Initialize the URL to be redirected to for the database action. */
	$action  = admin_url( 'admin-post.php');
	
	/* Add javascript to initialize functions to be called on button clicks. [Each one creates a form, then submits it with post headers to the admin-post.php url]. */
	$javascript .= "
		
		function authorize_user(user_login) {
			if (user_login == '') {
				return;
			}
			var form = document.createElement('form');
			form.action = '$action';
			form.method = 'post';
			form.target = '_blank';
			
			var action_element = document.createElement('input');
			action_element.type = 'hidden';
			action_element.name = 'action';
			action_element.value = 'authorize_user';
			
			var action_element2 = document.createElement('input');
			action_element2.type = 'hidden';
			action_element2.name = 'user_login';
			action_element2.value = user_login;
			
			form.appendChild(action_element);
			form.appendChild(action_element2);
			
			document.getElementById('messages').appendChild(form);
			form.submit();
			location.reload();
		}
		
		function unauthorize_user(user_login) {
			if (user_login == '') {
				return;
			}
			var form = document.createElement('form');
			form.action = '$action';
			form.method = 'post';
			form.target = '_blank';
			
			var action_element = document.createElement('input');
			action_element.type = 'hidden';
			action_element.name = 'action';
			action_element.value = 'unauthorize_user';
			
			var action_element2 = document.createElement('input');
			action_element2.type = 'hidden';
			action_element2.name = 'user_login';
			action_element2.value = user_login;
			
			form.appendChild(action_element);
			form.appendChild(action_element2);
			
			document.getElementById('messages').appendChild(form);
			form.submit();
			location.reload();
		}
		
		";
	
	/* Add HTML to allow the user to authorize other users to approve shoutouts. */
	$toReturn .= "

		<div style='display:inline-block'>
			<span>Authorize user to approve shoutouts: </span>
			<input type=\"text\" id=\"user_login\" class=\"input\" placeholder=\"User login name\"'>
			<button class='blue_button' onclick='authorize_user(document.getElementById(\"user_login\").value);'>Authorize User</button>
			<button class='blue_button' onclick='unauthorize_user(document.getElementById(\"user_login\").value);'>Unauthorize User</button><br><br>
		</div>

		";
	
	/* Add HTML to create authorized users block. */
	$toReturn .= "

		<div class='message' style='width:350px;'>
			<h3 style='color:white;'>Authorized Users:</h3>
		";
	
	/* Array to store the users that may not be unauthorized. */
	$not_unauthorizeable = array('','nsg-bennettf','chrishpriv','cts_admin','ronnie.sinclair','gene.park','chris.jones');
	
	/* Get all authorized users from the database. */
	$sql = "SELECT * FROM approval_users";
	$result = $mysqli->query($sql);
	
	/* If result returned rows. */
	if ($result->num_rows > 0) {
		
		/* Count variable to differentiate inline styles. */
		$count = 0;
		
		/* loop:
			condition: for all entries in $result.
			entrance: $result contains the entries of the authorized users.
			invariant: $row contains the values of one entry of an authorized user.
			exit: all approved users have been displayed. */
		while ($row = $result->fetch_assoc()) {
			/* Conditionally format styles to form a grid. */ 
			if ($count > 0) {
				$border_top = "border-top:none;";
			} else {
				$border_top ='';
			}
			
			/* Add the HTML to display the user's name. */
			$toReturn .= "<div style='display:inline-block;border-width:1px;border-color:white;border-style:solid;width:100%;$border_top'><span style='font-size:15px;color:white;spacing:0px;padding:0px;'>" . $row['user_login'] . "</span>";
			
			/* If the user is unauthorizeable, display the unauthorize button. */
			if (array_search(strtolower($row['user_login']),$not_unauthorizeable) == false) {
				$toReturn .= "<button class='blue_button' onClick='unauthorize_user(\"" . $row['user_login'] . "\")' style='float:right;'>Unauthorize</button>";
			}
			/* Close the row block. */
			$toReturn .= "</div>";
			
			/* Increment the count. */
			$count++;
		}
	}
	
	/* Close the Authorized users block */
	$toReturn .= "</div>";
	
	return "<style>$css</style>\n $toReturn \n<script>$javascript</script>";
}



add_action('admin_post_authorize_user','authorize_user');

function authorize_user() {
	
	/* $user_login: the passed username to be authorized. */
	$user_login = $_POST['user_login'];
	
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
	
	/* Get the user entry in the database. */
	$sql = "SELECT * FROM wp_users where user_login LIKE '$user_login'";
	$result = $mysqli->query($sql);
	
	/* If user exists. */
	if ($result->num_rows > 0) {
		
		/* Get the entry. */
		$row = $result->fetch_assoc();
		
		/* get the user id from the entry. */
		$user_id = $row['ID'];
		
		/* Add the user to the authorized user list. */
		$sql = "INSERT INTO approval_users (user_id,user_login) VALUES ('$user_id','$user_login')";
		if ($mysqli->query($sql)) {
			echo "<script>window.close();</script>";
		} else {
			echo "<h1 style='text-align:center;color:red;'>Unable to authorize user. Please try again.<br>If errors persist, contact CTS. (you may close this page)</h1>";
			echo "<h4 style='text-align:center;'>Error: $mysqli->error</h4>";
		}
	} else {
		echo "<h1 style='text-align:center;color:red;'>User login not found. Please make sure that the user has already created an account on this website. If errors persist, contact CTS.</h1>";
	}
}




add_action('admin_post_unauthorize_user','unauthorize_user');

function unauthorize_user() {
	
	/* $user_login: the passed username to be unauthorized. */
	$user_login = $_POST['user_login'];
	
	/* Array to store the users that may not be unauthorized. */
	$not_unauthorizeable = array('','nsg-bennettf','chrishpriv','cts_admin','ronnie.sinclair','gene.park','chris.jones');
	
	/* Check if the user is unauthorizeable. */
	if (array_search(strtolower($user_login),$not_unauthorizeable) == true) {
		echo "<h1 style='color:red;text-align:center;'>$user_login may not be unauthorized.</h1>";
		return;
	}
	
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
	
	/* Remove the user from the authorized list. */
	$sql = "DELETE FROM approval_users where user_login LIKE '$user_login'";
	if ($mysqli->query($sql)) {
		echo "<h1 style='color:green;text-align:center;'>User unauthorized successfully.</h1><script>setTimeout(window.close(),3000);</script>";
	} else {
		echo "<h1 style='color:red;text-align:center;'>Failed to unauthorize user. Please try again. If problems persist, contact CTS.</h1>";
	}
}
