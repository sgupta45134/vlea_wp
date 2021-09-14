<?php
/*
Plugin Name: CM Pay Per Posts Pro
Plugin URI: https://www.cminds.com/
Description: The plugin restricts access to pages and posts based on time and price. Pay Per Post will turn your articles and membership site into a pay per view system.
Author: CreativeMindsSolutions
Version: 2.5.3
*/

if (version_compare('7.0.0', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

require_once dirname(__FILE__) . '/App.php';
com\cminds\payperposts\App::bootstrap(__FILE__);
