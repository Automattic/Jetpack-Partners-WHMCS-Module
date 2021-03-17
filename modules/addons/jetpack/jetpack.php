<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

include_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use WHMCS\Database\Capsule;
use Jetpack\AdminDispatcher;

/**
 * Configuration options for Jetpack Addon Module.
 *
 * @return array Configuration Options for Jetpack Addon Module
 */
function jetpack_config()
{
    return [
        'name'        => 'Jetpack Addon',
        'author'      => 'Automattic',
        'description' => 'This module allows you to quickly create Jetpack products as WHMCS
                            products and product addons. It also allows for updating your API token
                            for multiple provisioning module usage instances of the accompanying Jetpack provisioning module.',
        'version'     => '1.0.0',
        'fields'      => [
            'api_token'     => array(
                'FriendlyName' => 'Jetpack Partner API Token',
                'Type'         => 'text',
                'Size'         => '64',
                'Default'      => '',
            ),
        ],
    ];
}

/**
 * Activation Options. Creates a table to store licenses if it doesn't already exist and create
 * the Jetpack Product group also if it does not already exist.
 *
 *
 * @return void
 */
function jetpack_activate()
{
    if (!Capsule::schema()->hasTable('jetpack_product_licenses')) {
        try {
            Capsule::schema()->create(
                'jetpack_product_licenses',
                function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->integer('order_id');
                    $table->integer('product_id');
                    $table->string('license_key');
                    $table->timestamp('issued_at');
                    $table->timestamp('revoked_at')->nullable();
                }
            );
        } catch (Exception $e) {
            return [
                'status' => "error",
                'description' => 'Unable to create Jetpack Product Group: ' . $e->getMessage(),
            ];
        }
    }

    $jetpack_product_group = Capsule::table('tblproductgroups')->where(['name' => 'Jetpack'])->first();
    if (is_null($jetpack_product_group)) {
        try {
            Capsule::table('tblproductgroups')->insert(
                [
                    'name' => 'Jetpack',
                    'headline' => 'Jetpack Products',
                    'hidden' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
        } catch (Exception $e) {
            return [
                'status' => "error",
                'description' => 'Unable to create Jetpack Product Group: ' . $e->getMessage(),
            ];
        }
    }
}


/**
 * Deactivation Options
 *
 * @return void
 */
function jetpack_deactivate()
{
}

/**
 * Output HTML for the Jetpack addon module. If the provisioning module is
 * not found in the expected location provide a link to the module as it is
 * required.
 *
 * @param array $vars Module configuration options and variables.
 * @return string output HTML
 */
function jetpack_output($vars)
{
    $include_provisioning_autoload = include_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'jetpack'  . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
    $include_provisioning_module = include_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'jetpack' . DIRECTORY_SEPARATOR . 'jetpack.php');
    if (!$include_provisioning_autoload || !$include_provisioning_module) {
        echo "Provisioning module not installed. Please install the <a href=\"https://github.com/Automattic/Jetpack-Partners-WHMCS-Module\">provisioning module</a> to use the addon module";
    } else {
        $action     = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'index';
        $dispatcher = new AdminDispatcher();
        $response   = $dispatcher->dispatch($vars, $action);
        echo $response;
    }
}
