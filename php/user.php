<?php
	//user functions library

	function get_sub($db, $login_uid) {
		$stmt = $db->prepare("SELECT sub FROM logins WHERE uid = ?");
		$stmt->execute(array($login_uid));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row["sub"];
	}
	function get_userinfo($db, $sub) {
		$stmt = $db->prepare("SELECT * FROM users WHERE sub = ?");
		$stmt->execute(array($sub));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row;
	}
	function is_valid_login_uid($db, $login_uid) {
		login_cleanup($db);
		$stmt = $db->prepare("SELECT EXISTS(SELECT * FROM logins WHERE uid = ?)");
		$stmt->execute(array($login_uid));
		return $stmt->fetch(PDO::FETCH_NUM)[0] == "1";
	}
	function get_user_owned_events($db, $sub) {
		$stmt = $db->prepare("SELECT * FROM events WHERE owner_sub = ?");
		$stmt->execute(array(
			$sub
		));
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $results;
	}
	function get_user_signedup_events($db, $sub) {
		$stmt = $db->prepare("SELECT event_id FROM signups WHERE user_sub = ?");
		$stmt->execute(array(
			$sub
		));
		$event_ids = $stmt->fetchAll(PDO::FETCH_NUM);
		$results = array();
		foreach ($event_ids as $event_id) {
			$results[] = get_eventinfo($db, $event_id[0]);
		}
		return $results;
	}
	function login_cleanup($db) {
		$db->query("DELETE FROM `logins` WHERE expire_time < UNIX_TIMESTAMP()");
	}
?>