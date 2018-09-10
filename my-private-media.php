<?php
/**
 * Plugin Name: My Private Media
 * Version: 1.0.2
 * Author: Nathan Gearing
 * Author URI: https://nathangearing.com
 * Description: Make items in your Media Library private.
 *
 * @package mpm
 */

namespace MPM;

// Setup constants.
define( 'MPM\PATH', plugin_dir_path( __FILE__ ) );
define( 'MPM\BASENAME', plugin_basename( __FILE__ ) );
define( 'MPM\VERSION', '1.0.0' );
define( 'MPM\SLUG', plugin_basename( __FILE__ ) );
define( 'MPM\URL', plugin_dir_url( __FILE__ ) );

// Autoload scripts.
if ( file_exists( PATH . '/vendor/autoload.php' ) ) {
	$autoloader = require_once PATH . '/vendor/autoload.php';
} else {
	throw new \Exception( 'Could not find autoloader', 1 );
}

/**
 * Run activation scripts.
 *
 * @return void
 */
function mpm_activate() {

	if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
		throw new \Exception( 'Require php version of at least 5.6' );
	}

	Activator::run();
}

/**
 * Run deactivation script.
 *
 * @return void
 */
function mpm_deactivate() {
	Deactivator::run();
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\mpm_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\\mpm_deactivate' );

/**
 * Run main plugin function.
 *
 * @return void
 */
function mpm_run() {

	$mpm = new Plugin( SLUG, VERSION, PATH, BASENAME, URL );
	$mpm->run();

}

mpm_run();
