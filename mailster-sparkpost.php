<?php
/*
Plugin Name: Mailster SparkPost Integration
Requires Plugins: mailster
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=plugin&utm_term=SparkPost
Description: Uses SparkPost to deliver emails for the Mailster Newsletter Plugin for WordPress.
Version: 1.8.1
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-sparkpost
License: GPLv2 or later
*/


define( 'MAILSTER_SPARKPOST_VERSION', '1.8.1' );
define( 'MAILSTER_SPARKPOST_REQUIRED_VERSION', '4.0' );
define( 'MAILSTER_SPARKPOST_FILE', __FILE__ );

require_once __DIR__ . '/classes/sparkpost.class.php';
new MailsterSparkPost();
