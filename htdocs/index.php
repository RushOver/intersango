<?php
start_composing_response();

require __DIR__ . 'details.php';

the_website_is_secure();

compose_a_response_by_performing_the_requested_action();

send_response();















// Necessary detail

function start_composing_response() {
	// turn output buffering on
	ob_start();
}

function send_response() {
	// send the contents of the output buffer
	ob_end_flush();
}
