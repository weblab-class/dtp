<?php
	include("php/oidc.php");
	include("php/google_oidc.php");
	include("php/db.php");
	include("php/user.php");

	$logged_in=false;
	if (isset($_COOKIE["login_uid"])) {
		$logged_in = is_valid_login_uid($db, $_COOKIE["login_uid"]);
		if (!$logged_in) {
			unset($_COOKIE["login_uid"]);
			setcookie("login_uid", "", time() - 3600);
		}
	}
	if (!$logged_in) {
		$state = md5(rand());
		$nonce = md5(rand());
		setcookie("state", $state);
		setcookie("nonce", $nonce);
	}
?>

<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<title>dtp</title>
	<link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
	<link rel="stylesheet" type="text/css" href="style.css">
	<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
	<script src="semantic/dist/semantic.min.js"></script>
	<script>
		<?php
			if ($logged_in) {
				echo "const logged_in = true;";
				$sub = get_sub($db, $_COOKIE["login_uid"]);
				$userinfo = get_userinfo($db, $sub);
				echo "const sub = '" . $sub . "';";
			} else {
				echo "const logged_in = false;";
				echo "const sub = '';";
			}
			echo "const index_url = '" . INDEX_URL . "';";
		?>
	</script>
</head>
<body>
	<div class="ui large secondary menu">
		<a href="<?php echo INDEX_URL;?>"><h1 class="item">dtp</h1></a>
  	<?php if ($logged_in) { ?>
			<div class="right menu topMenu">
				<a class="item clickable" href="#" id="viewUserinfoModal">
					<?php echo $userinfo["given_name"]; ?>&nbsp;<i class="user icon"></i>
				</a>
				<a class="item clickable" href="#" id="addEvent">
		  			New Event&nbsp;
					<i class="add icon"></i>
				</a>
				<a class="item clickable" href="#" id="invitations">
					<div class="text">Invitations</div>
					<i class="mail outline icon"></i>
				</a>
				<a class="item clickable" href="#" id="scheduleLink">
			    	My Schedule&nbsp;
			    	<i class="checked calendar icon"></i>
		 		</a>
				<a class="item clickable" href="logout.php" id="logout">
					Logout&nbsp;
					<i class="sign out icon"></i>
				</a>
			</div>
		<?php } else { ?>
			<div class="right menu topMenu">
				<div class="ui item">
					<select id="authMethod" class="ui dropdown">
						<option value="mit">MIT</option>
						<option value="google">Google</option>
					</select>
				</div>
				<div class="ui item">
					<div class="ui toggle checkbox">
					 	<input type="checkbox" id="persistent" checked="">
					 	<label>Stay logged in</label>
					</div>
				</div>
				<!--<input type="checkbox" id="persistent" value="Stay logged in" checked>-->
				<a class="item clickable" href="#" id="login">
					Login&nbsp;
					<i class="sign in icon"></i>
				</a>
			</div>
			<script>
				const hrefPart1 = "https://oidc.mit.edu/authorize?<?php	echo "client_id=" . CLIENT_ID . "&response_type=code&scope=openid%20profile%20email&redirect_uri=" . urlencode(LOGIN_PAGE_URL) . "&state=" . $state; ?>";
				const googleHrefPart1 = "https://accounts.google.com/o/oauth2/v2/auth?<?php echo "client_id=" . GOOGLE_CLIENT_ID . "&response_type=code&scope=openid%20profile%20email&redirect_uri=" . urlencode(GOOGLE_LOGIN_PAGE_URL) . "&state=" . $state;?>";
				const hrefPart2 = "<?php echo "&nonce=" . $nonce; ?>";
				const loginButton = $("#login");
				const persistentCheckbox = $("#persistent");
				const authMethodSelect = $('#authMethod');
				authMethodSelect.dropdown();
				function updateHref() {
					let href;
					if (authMethodSelect.val() === 'mit') {
						href = hrefPart1;
					} else if (authMethodSelect.val() === 'google') {
						href = googleHrefPart1;
					}
					if (persistentCheckbox.prop("checked")) {
						href += ".persistent" + hrefPart2;
					} else {
						href += hrefPart2;
					}
					loginButton.attr("href", href);
				};
				authMethodSelect.on('change', updateHref);
				persistentCheckbox.on("click", updateHref);
				updateHref();
			</script>
		<?php } ?>
	</div>
	<div class="ui center aligned container" id="containter">
		<div id="main">
			<h1 class="ui header">
				down to pset?
			</h1>
			<p class="ui">
				Search by course, assignment, or location...
			</p>
			<form class="ui form" id="searchEvents">
				<div class="field">
					<div class="three fields">
						<div class="field">
							<input placeholder="Course" type="text" name="search_course">
						</div>
						<div class="field">
				  			<input placeholder="Assignment" type="text" name="search_assignment">
				  		</div>
					  	<div class="field">
					  		<input placeholder="Location" type="text" name="search_location">
					  	</div>
					</div>
				</div>
				<button class="ui blue center floated right labeled icon submit button">
		      		Search
		    		<i class="search icon"></i>
		    	</button>
			</form>

		</div>
	</div>
	<div class="ui modal" id="submitEventModal">
		<i class="close icon"></i>
		<div class="ui center aligned header">
	    Add A New Event
	  </div>
	  <form class="ui form viewModalBody" id="submitEvent">
			<div class="field">
				<div class="three fields" style="margin-top:14px">
					<div class="field">
						<label>Course</label>
						<input placeholder="18.02" type="text" name="submit_course" required>
					</div>
					<div class="field">
						<label>Assignment</label>
				  		<input placeholder="PSet 3" type="text" name="submit_assignment" required>
				  	</div>
				  	<div class="field">
						<label>Location</label>
				  		<input placeholder="Next 5W Main Lounge"type="text" name="submit_location" required>
				  	</div>
				</div>
			</div>
			<div class="field">
				<div class="three fields" class="center">
					<div class="field">
						<label>Date</label>
						<input type="date" name="submit_date" required>
					</div>
					<div class="field">
						<label>Start Time</label>
						<input type="time" name="submit_start_time" required>
				  </div>
				  <div class="field">
						<label>End Time</label>
						<input type="time" name="submit_end_time" required>
				  </div>
				</div>
			</div>
			<div class="actions">
				<button class="ui green right floated right labeled icon submit button">
		      Add My Event
		      <i class="checkmark icon"></i>
		    </button>
		    <div class="ui black right floated deny button">
		      Cancel
		    </div>
		  </div>
		</form>
	</div>
	<div class="ui modal" id="viewModal">
		<i class="close icon"></i>
	  <div class="ui center aligned header" id="viewModalTitle"></div>
	  <div id="viewModalBody">
	  	<p><b>Organized by</b> <span id="viewModalOwner"></span></p>
	  	<p><b>Location</b>: <span id="viewModalLocation"></span></p>
	  	<p><b>Date</b>: <span id="viewModalDate"></span></p>
	  	<p><b>Time</b>: <span id="viewModalStartTime"></span> - <span id="viewModalEndTime"></span></p>
	  	<p><span id="viewModalAttendees"></span> will be there.</p>
	  </div>
		<button class="ui blue right floated right labeled icon button" id="editModal" hidden>
	  	Edit My Event
	  	<i class="edit icon"></i>
	  </button>
	  	<button class="ui green right floated right labeled icon button" id="inviteButton">
	  		Invite to this Event
	  		<i class="mail icon"></i>
	  	</button>
	</div>
	<div class="ui modal" id="inviteModal">
		<div class="ui center aligned header">
			Invite
		</div>
		<form class="ui form center viewModalBody" id="inviteForm">
			<div class="ui right labeled input">
				<input placeholder="Kerberos" type="text" id="inviteFormKerberos" required>
				<div class="ui label">
					@mit.edu
				</div>
			</div>
			<div class="actions">
			<button class="ui green right floated right labeled icon submit button">
		    	Invite
		    	<i class="checkmark icon"></i>
		    </button>
		    <button class="ui black right floated deny button">
		      Cancel
		    </button>
		</div>
	</form>
</div>
	<div class="ui modal" id="changeEventModal">
		<i class="close icon"></i>
		<div class="ui center aligned header">
	    Editing Event
	  </div>
	 	<form class="ui form viewModalBody" id="changeEvent">
			<div class="field">
				<div class="three fields">
					<div class="field">
						<label>Course</label>
						<input type="text" name="change_course" required>
					</div>
					<div class="field">
						<label>Assignment</label>
				  		<input type="text" name="change_assignment" required>
				  	</div>
				  	<div class="field">
						<label>Location</label>
				  		<input type="text" name="change_location" required>
				  	</div>
				</div>
			</div>
			<div class="field">
				<div class="three fields" class="center">
					<div class="field">
						<label>Date</label>
						<input type="date" name="change_date" required>
					</div>
					<div class="field">
						<label>Start Time</label>
						<input type="text" name="change_start_time" required>
				  </div>
				  <div class="field">
				  		<label>End Time</label>
						<input type="text" name="change_end_time" required>
				  </div>
				</div>
			</div>
			<div class="actions">
			<button class="ui green right floated right labeled icon submit button">
		      Make Changes
		      <i class="checkmark icon"></i>
		    </button>
		    <div class="ui black right floated deny button">
		      Cancel
		    </div>
			<div class="ui red right floated deny button" id="changeEventModalDelete">
		      Delete Event
		    </div>
		  </div>
		</form>
	</div>
	<div class="ui modal" id="scheduleModal">
		<i class="close icon"></i>
		<div class="ui center aligned header">
	    My Schedule
	  </div>
	  <table class="ui table center aligned viewModalBody">
			<thead>
				<tr>
					<th>Course</th>
					<th>Assignment</th>
					<th>Location</th>
					<th>Date</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Attendees</th>
					<th>Owner</th>
					<th></th>
					<th></th>
				</tr>
			</thead>
		  <tbody class="ui title" id="scheduleBody">
		  </tbody>
		</table>
	</div>
	<div class="ui modal" id="invitationsModal">
		<i class="close icon"></i>
		<div class="ui center aligned header">
		    Invitations
		</div>
		<div id="invitationsBody" class="viewModalBody">

		</div>
	</div>
	<div class="ui modal" id="userinfoModal">
		<i class="close icon"></i>
		<div class="ui center aligned header">
			<?php echo $userinfo["name"]; ?>
		</div>
		<div class="viewModalBody">
			<?php if ($userinfo["kerberos"] != "") { echo "<p><b>Kerberos</b>: " . $userinfo["kerberos"] . "</p>"; } ?>
			<p><b>Email</b>: <?php echo $userinfo["email"]; ?></p>
			<b>iCalendar URL</b> (for Google Calendar integration):&nbsp;
			<div class="ui input right action">
				<input id="ical_id" size="50" type="text" readonly value="<?php if ($userinfo["ical_id"] != "") echo INDEX_URL . "php/ical.php?id=" . $userinfo["ical_id"]; ?>">
				<button class="ui icon button" onclick="$('#ical_id').select();document.execCommand('copy')">
					<i class="copy icon"></i>
				</button>
			</div>
		</div>
	</div>
	<div class="center aligned" id="messages">
	</div>
	<div class="ui container" id="eventsTableContainer">
		<table class="ui center aligned structured structured table" id="eventsTable" hidden>
			<thead id="eventHeader">
				<tr>
					<th>Course</th>
					<th>Assignment</th>
					<th>Location</th>
					<th>Date</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Attendees</th>
					<th>Owner</th>
					<th></th>
					<th></th>
				</tr>
			</thead>
		</table>
	</div>
	<script src="js/universal.js"></script>
	<script src="js/search_events.js"></script>
	<script src="js/signup_event.js"></script>
	<script src="js/view_event.js"></script>
	<script src="js/userinfo.js"></script>
	<?php if ($logged_in){ ?>
		<script src="js/new_event.js"></script>
		<script src="js/schedule.js"></script>
		<script src="js/invitations.js"></script>
		<script src="js/invite.js"></script>
	<?php } ?>
</body>
</html>
