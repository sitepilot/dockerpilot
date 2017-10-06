<?php
/**
 * Plugin Name: Serverpilot Mailcatcher SMTP
 * Description: SMTP configuration for Mailcatcher.
 * Author: Sitepilot
 * Version: 1.0
 */
add_action( 'phpmailer_init', 'send_smtp_email' );
function send_smtp_email( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->SMTPAuth = false;
	$phpmailer->Host = "serverpilot-mailcatcher";
	$phpmailer->Port = "1025";
}
