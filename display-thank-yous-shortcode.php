 <?php

/**
 * Display Thank Yous Shortcode
 *
 * Snippet to display the most recent 5 shoutouts from the database and a button to show more.
 */
add_shortcode('snippet_display_thankyous','display_thankyous');

/* display_thankyous:
	Function to display 5 approved shoutout messages to the user and allow them to press a button to display 5 more at a time.
	@return the css, html, and javascript code to be added to the page. */
function display_thankyous() {
	
	/* $toReturn: string variable to hold all of the HTML code to be returned. */
	$toReturn = "";
	
	/* $javascript: string variable to hold all of the javascript code to be returned. */
	$javascript = "";
	
	/* $css: string variable to hold all of the css styles to be returned. */
	$css = "";
	
	/* Set up sql information for database access. */
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
	
	/* Query mySQL database for all entries where the status has been set to accepted by the administrative users. */
	$sql = "SELECT * FROM staff_thankyou WHERE status = 'accepted' ORDER BY thankyou_date DESC";
	$result = $mysqli->query($sql);
	
	/* If values are returned, display them in a formatted design, else display 'you're the first one here'.*/
	if ($result->num_rows > 0) {
		
		/* $count: the variable to store how many entries have been displayed. */
		$count = 0;
		
		/* $not_displayed: the array of messages to be converted to a javascript for displaying when the 'show 5 more' button is pressed. */
		$not_displayed = array();
		
		/* Initialize the javascript version of the not_displayed array. */
		$javascript .= "var messages_not_displayed = new Array();";
		
		/* Create the messages block. */
		$toReturn .= "<div id='messages'>";
		
		/* Styles for a shoutout with gradient background. */
		$css .= "
			
			.message {
				background: #014983;
				background: -webkit-linear-gradient(#014983, #012646);
				background:    -moz-linear-gradient(#014983, #012646);
				background:         linear-gradient(#014983, #012646);
				padding:20px;
			}
			
			";
		
		/* loop:
			condition: for all rows stored in $result
			entrance: the sql query returned more than 0 rows into $result.
			invariant: $row = one of the rows returned by the mySQL query
			exit: 5 mesages have been displayed and the rest are stored in $not_displayed.
		*/
		while ($row = $result->fetch_assoc()) {
			
			/* Store individual values from $row. */
			$employee_name = $row['employee_name'];
			$thankyou_text = $row['thankyou_text'];
			$thankyou_date = $row['thankyou_date'];
			$employee_department = $row['employee_department'];
			$thankyou_id = $row['thankyou_id'];
			
			/* If 5 shoutouts have been displayed, add this row to $not_displayed and skip to next iteration. */
			if ($count >= 5) {
				$not_displayed[$thankyou_date] = $row;
				continue;
			}
			
			/* Display message with html element creation. */
			$toReturn .= "
			<div class='message'>
				<h1 style=\"color:white;\">Shoutout to $employee_name in $employee_department</h1>
				<p style=\"color:white;\">$thankyou_text</p>
				<p style=\"font-size:9px;color:#cccccc;text-align:right;\" width=\"100%\">$thankyou_date &nbsp &nbsp[ $thankyou_id ]</p>
			</div>
			<br>
				";
			
			/* Increment $count */
			$count++;
		}
		
		/* Close 'messages' block.*/
		$toReturn .= "</div>";
		
		/* Add styles for 'show 5 more' button. */
		$css .= "
		.blue_button {
			padding-left:20px;
			padding-right:20px;
			padding-top:10px;
			padding-bottom:10px;
			background-color:#014983;
			color:white;
			border-width:0px;
		}
		.blue_button:hover {
			background-color:#0969a4;
		}
		
		";
		
		/* Add HTML to display 'show 5 more' button. */
		$toReturn .= "<button onClick='display_more()' class='blue_button'>Show 5 More</button>";
		
		/* Transform php array into javascript (json) array and write code to assign it to . */
		$javascript .= "var messages_not_displayed = " . json_encode($not_displayed);
		
		/* Add javascript code to append 5 more shoutouts to the end of the 'messages' block. */
		$javascript .= "
		var message_index = 0;
		var message_dates = Object.keys(messages_not_displayed).sort((a, b) => {return b-a});
		function display_more() {
			var counter = 0;
			
			for (let [key, val] of Object.entries(message_dates)) {
				var date = val;
				var message = messages_not_displayed[val];
				
				if ((counter - message_index >= 0) && (counter - message_index < 5)) {
					var tag = document.createElement('div');
					tag.className = 'message';
					
					var tag2_1 = document.createElement('h1');
					tag2_1.style = 'color:white;';
					
					var header = document.createTextNode('Shoutout to ' + message['employee_name'] + ' in ' + message['employee_department'] + ' (' + message['thankyou_date'] + ')');
					
					var tag2_2 = document.createElement('p');
					tag2_2.style = 'color:white;';
					
					var text = document.createTextNode(message['thankyou_text']);
					
					var tag2_3 = document.createElement('p');
					tag2_3.style = 'font-size:9px;color:#cccccc;text-align:right;';
					tag2_3.width = '100%';
					
					var meta = document.createTextNode(message['thankyou_date'] + '    [ ' + message['thankyou_id'] + ' ]');
					
					tag2_1.appendChild(header);
					tag2_2.appendChild(text);
					tag2_3.appendChild(meta);
					
					tag.appendChild(tag2_1);
					tag.appendChild(tag2_2);
					tag.appendChild(tag2_3);
					
					document.getElementById('messages').appendChild(tag);
					document.getElementById('messages').appendChild(document.createElement('br'));
					
				}
				counter++;
			}
			message_index += 5;
		}
		
		";
		
	} else {
		return "<h3>Looks like you're the first one here.<h3>";
	}
	return "<style>$css</style>\n $toReturn \n<script>$javascript</script>";
}

