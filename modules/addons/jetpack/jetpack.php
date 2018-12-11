<?php
/**
 * An addon module that automatically adds a jetpack product to a hosts whmcs available products in
 * a product group.
 */

require_once __DIR__ . '/vendor/autoload.php';
use WHMCS\Database\Capsule;
use Jetpack\AdminDispatcher;


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
		'description' => 'This module allows you to quickly create Jetpack products, add Jetpack
		products to existing product bundles and manage Jetpack plans',
		'version'     => '0.0.1',
		'fields'      => [
			'partner_id'     => array(
				'FriendlyName' => 'Jetpack Partner Client Id',
				'Type'         => 'text',
				'Size'         => '64',
				'Default'      => '',
				'Description'  => 'Jetpack Partner Client Id',
			),
			'partner_secret' => array(
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
	jetpack_deactivate();
	if ( ! Capsule::schema()->hasTable( 'jetpack_products' ) ) {
		Capsule::schema()->create(
			'jetpack_products',
			function ( $table ) {
				$table->increments( 'id' );
				$table->integer( 'product_id' );
				$table->string( 'plan_type' );
				$table->integer( 'licenses_provisioned' );
				$table->timestamps();
			}
		);
	}
}


/**
 * Deactivation Options
 *
 * @return void
 */
function jetpack_deactivate() {
	if ( Capsule::schema()->hasTable( 'jetpack_products' ) ) {
		Capsule::schema()->drop( 'jetpack_products' );
	}
}

/**
 * Output HTML for the Jetpack addon module
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
