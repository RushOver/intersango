<?php

require_once "config.php";
require_once ABSPATH . "/util.php";
require_once ABSPATH . "/localization.php";
require_once ABSPATH . "/header.php";
require_once ABSPATH . "/switcher.php";
require_once ABSPATH . "/footer.php";

function the_user_is_logged_in() {
	return isset($_SESSION['uid']);
}

function they_have_been_inactive_for_too_long() {
	return
		isset($_SESSION['last_activity'])
		&& time() - $_SESSION['last_activity'] > MAX_IDLE_MINUTES_BEFORE_LOGOUT * 60
	;
}

function the_user_should_be_logged_out_after_a_certain_period_of_inactivity() {
	// if the user has been logged in but is idle, log them out unless this is just an ajax request, in which case just act as if they're not logged in
	if (
		the_user_is_logged_in()
		&& they_have_been_inactive_for_too_long()
		&& !isset($_GET['fancy'])
	)
		if (isset($_COOKIE['openid']) && isset($_COOKIE['autologin']) && count($_POST) == 0)
			relogin();
		else
			logout();                   // this exit()s
	else if (!isset($_SESSION['uid']) && isset($_COOKIE['openid']) && isset($_COOKIE['autologin']) && count($_POST) == 0)
		relogin();
	else {
		$_SESSION['last_activity'] = time();
		get_login_status();
	}
}

function keep_the_session_id_fresh() {
	session_start();

	if (!isset($_SESSION['creation_time'])) {
		$_SESSION['creation_time'] = time();
	} else if (time() - $_SESSION['creation_time'] > MAX_SESSION_ID_LIFETIME * 60) {
		session_regenerate_id(true);
		$_SESSION['creation_time'] = time();
	}
}

function a_csrf_token_should_be_associated_with_this_user() {
	if(!isset($_SESSION['csrf_token']))
	{
		$_SESSION['csrf_token'] = '';
		for($i=0;$i<32;$i++)
		{
			$_SESSION['csrf_token'] .= bin2hex(chr(mt_rand(0,255)));
		}   
	}
}

function compose_a_response_by_performing_the_requested_action() {
	global $is_logged_in, $is_admin;
	if (isset($_GET['page']))
		$page = htmlspecialchars($_GET['page']);
	else
		$page = 'trade';

	switcher($page, $is_logged_in, $is_admin);
}

function relogin()
{
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
//
// this will be used to protect all subpages from being directly accessed.
function prevent_direct_access_to_subpages() {
	define('_we_are_one', 1);
}

function the_website_is_secure() {
	prevent_direct_access_to_subpages();

	keep_the_session_id_fresh();

	a_csrf_token_should_be_associated_with_this_user();

	the_user_should_be_logged_out_after_a_certain_period_of_inactivity();
}
