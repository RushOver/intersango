<?php

require_once __DIR__ . "/config.php";

require_once ABSPATH . '/db.php';
require_once ABSPATH . "/html_response_creation.php";

require ABSPATH . '/security.php';

function the_website_is_secure() {
	prevent_direct_access_to_subpages();

	keep_the_session_id_fresh();

	a_csrf_token_should_be_associated_with_this_user();

	the_user_should_be_logged_out_after_a_certain_period_of_inactivity();
}

require_once ABSPATH . "/util.php";
require_once ABSPATH . "/localization.php";
require_once ABSPATH . "/header.php";
require_once ABSPATH . "/switcher.php";

function compose_a_response_by_performing_the_requested_action() {
	global $is_logged_in, $is_admin, $page;
	if (isset($_GET['page']))
		$page = htmlspecialchars($_GET['page']);
	else
		$page = 'trade';

	switcher($page, $is_logged_in, $is_admin);
}
