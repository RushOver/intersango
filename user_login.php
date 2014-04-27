<?php
// how many minutes can a user be idle for before they're automatically logged out
define('MAX_IDLE_MINUTES_BEFORE_LOGOUT', 60);

global $is_logged_in, $is_admin, $oidlogin;

$is_logged_in = 0;
$is_admin = false;
$oidlogin = '';

function the_user_is_logged_in() {
	return isset($_SESSION['uid']);
}

function they_have_been_inactive_for_too_long() {
	return
		isset($_SESSION['last_activity'])
		&& time() - $_SESSION['last_activity'] > MAX_IDLE_MINUTES_BEFORE_LOGOUT * 60
	;
}

function logout() {
    session_destroy();

    // expire the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 36*60*60, $params["path"],   $params["domain"], $params["secure"], $params["httponly"]);
    }
    header('Location: .');
    exit();
}

function relogin() {
    global $page, $next_page;

    if ($page != 'login') {
        if ($_SERVER['QUERY_STRING'])
            $next_page = "?" . $_SERVER['QUERY_STRING'];
        else
            $next_page = "?page=$page";
        require_once ABSPATH . "/login.php";
        show_footer(0, false, false);
        exit;
    }
}

function get_login_status() {
    global $is_logged_in, $is_admin, $is_verified, $oidlogin;

    if (!isset($_SESSION['uid']) || !isset($_SESSION['oidlogin'])) {
        list ($is_logged_in, $is_admin, $oidlogin) = array(0, false, '');
        return;
    }

    // just having a 'uid' in the session isn't enough to be logged in
    // check that the oidlogin matches the uid in case database has been reset
    $uid = $_SESSION['uid'];
    $oid = $_SESSION['oidlogin'];

    $result = do_query("
        SELECT is_admin, verified
        FROM users
        WHERE oidlogin = '$oid'
        AND uid = '$uid'
    ");

    if (has_results($result)) {
        $row = mysql_fetch_array($result);
        list ($is_logged_in, $is_admin, $is_verified, $oidlogin) = array($uid, $row['is_admin'] == '1', $row['verified'] == '1', $oid);
        if (!REQUIRE_IDENTIFICATION)
            $is_verified = true;
        return;
    }

    if (isset($_GET['fancy'])) {
        list ($is_logged_in, $is_admin, $is_verified, $oidlogin) = array(0, false, false, '');
        return;
    }

    logout();
}
