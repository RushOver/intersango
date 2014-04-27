<?php
// how often should we change the session id (in minutes)
define('MAX_SESSION_ID_LIFETIME', 10);

function prevent_direct_access_to_subpages() {
	define( '_there_can_be_only_one', 1 );
}

define( 'SESSION_ID_AGE_STORAGE_LOCATION', 'last_session_id_regeneration_time' );
function keep_the_session_id_fresh() {
	session_start();

	if (
		the_session_has_just_been_created()
		|| the_session_id_is_older_than( MAX_SESSION_ID_LIFETIME_MINUTES )
	) {
		session_regenerate_id(true);
		$_SESSION[ SESSION_ID_AGE_STORAGE_LOCATION ] = time();
	}
}

function the_session_has_just_been_created() {
return ! isset( $_SESSION[ MAX_SESSION_ID_LIFETIME_MINUTES ] );
}

function the_session_id_is_older_than( $max_session_id_age_in_minutes ) {
	$current_session_id_age_in_seconds = time() - $_SESSION[ SESSION_ID_AGE_STORAGE_LOCATION ];
	$max_session_id_age_in_seconds = $max_session_id_age_in_minutes * 60;

	return $current_session_id_age_in_seconds > $max_session_id_age_in_seconds;
}

function a_csrf_token_should_be_associated_with_this_user() {
	the_csrf_token_is_a_random_ascii_character_string(
		$with = 32 /* characters */,
		$stored_in_session_value_named = 'csrf_token'
	);
}

function the_csrf_token_is_a_random_ascii_character_string( $length, $location_to_store_in ) {
	if ( a_csrf_token_does_not_exist_in( $location_to_store ) ) {
		create_the_csrf_token( $length, $location_to_store );
	}
}

function a_csrf_token_does_not_exist_in( $location_to_store_in ) {
	return ! isset( $_SESSION[ $location_to_store_in ] );
}

function create_the_csrf_token( $length, $at_session_location ) {
	$_SESSION[ $at_session_location ] = '';
	foreach( range( 1, $length ) as $i ) {
		add_a( 'random_ascii_character', $at_session_location );
	}
}

function add_a( $function_that_generates_value_to_add, $storage_location ) {
	$_SESSION[ $storate_location ] .= call_user_func( $function_that_generates_value_to_add );
}

function random_ascii_character() {
	return bin2hex(chr(mt_rand(0,255)));
}

function the_user_should_be_logged_out_after_a_certain_period_of_inactivity() {
	require __DIR__ . '/user_login.php';
	// if the user has been logged in but is idle, log them out unless this is just an ajax request, in which case just act as if they're not logged in
	if (
		the_user_is_logged_in()
		&& they_have_been_inactive_for_too_long()
		&& !isset($_GET['fancy'])
	) {
		if (isset($_COOKIE['openid']) && isset($_COOKIE['autologin']) && count($_POST) == 0)
			relogin();
		else
			logout();                   // this exit()s
	} else if (!isset($_SESSION['uid']) && isset($_COOKIE['openid']) && isset($_COOKIE['autologin']) && count($_POST) == 0) {
		relogin();
	} else {
		$_SESSION['last_activity'] = time();
		get_login_status();
	}
}

function prevent_direct_access_to_this_file() {
	defined('_there_can_be_only_one') || die('Direct access not allowed.');
}
