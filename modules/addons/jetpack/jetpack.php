<?php
/**
 * An addon module that automatically adds a jetpack product to a hosts whmcs available products in
 * a product group.
 */

use WHMCS\Database\Capsule;
require_once __DIR__ . '/lib/Admin/AdminDispatcher.php';



if ( ! defined( 'WHMCS' ) ) {
	die( 'This file cannot be accessed directly' );
}

/**
 * Configuration options for Jetpack Addon Module.
 *
 * @return array Configuration Options for Jetpack Addon Module
 */
function jetpack_config() {
	return [
		'name'        => 'Jetpack By Automattic',
		'author'      => 'Automattic',
		'description' => 'Setups the Jetpack By Automattic Partner Module for Provisioning Jetpack',
		'version'     => '0.0.1',
		'fields'      => [
			'Partner Id'     => array(
				'FriendlyName' => 'Jetpack Partner Client Id',
				'Type'         => 'text',
				'Size'         => '64',
				'Default'      => '',
				'Description'  => 'Jetpack Partner Client Id',
			),
			'Partner Secret' => array(
				'FriendlyName' => 'Jetpack Partner Client Secret',
				'Type'         => 'text',
				'Size'         => '256',
				'Default'      => '',
				'Description'  => 'Jetpack Partner Client Secret',
			),
		],
	];
}

/**
 * Activation Options
 *
 * @return void
 */
function jetpack_activate() {
}


/**
 * Deactivation Options
 *
 * @return void
 */
function jetpack_deactivate() {
}

/**
 * Undocumented function
 *
 * @param array $vars Module configuration options and variables.
 * @return void
 */
function jetpack_output( $vars ) {
	$action     = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'index';
	$dispatcher = new AdminDispatcher();
	$response   = $dispatcher->dispatch( $vars, $action );
	echo $response;
}
