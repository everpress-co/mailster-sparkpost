<?php
/*
Plugin Name: Mailster SparkPost Integration
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=Mailster+SparkPost+Integration&utm_medium=plugin
Description: Uses SparkPost to deliver emails for the Mailster Newsletter Plugin for WordPress.
Version: 1.5.1
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-sparkpost
License: GPLv2 or later
*/


define( 'MAILSTER_SPARKPOST_VERSION', '1.5.1' );
define( 'MAILSTER_SPARKPOST_REQUIRED_VERSION', '2.4' );
define( 'MAILSTER_SPARKPOST_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/classes/sparkpost.class.php';
new MailsterSparkPost();
