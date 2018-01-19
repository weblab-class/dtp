<?php
	include("db.php");
	if (isset($_COOKIE["session_uid"])) {
		$stmt = $db->prepare("DELETE FROM sessions WHERE uid = ?");
		$stmt->bindParam(1, $uid);
		$uid = $_COOKIE["session_uid"];
		$stmt->execute();
	}
	$db->query("DELETE FROM `sessions` WHERE expire_time < UNIX_TIMESTAMP()");
	setcookie("session_uid", "", time()-3600);
	header("Location: https://jungj.scripts.mit.edu:444/dtp/");
?>