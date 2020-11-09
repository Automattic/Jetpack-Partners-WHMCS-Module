<?php

use Jetpack\JetpackLicenseAPIManager;
use Jetpack\JetpackLicenseManager;
use WHMCS\Database\Capsule;


include_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

const ERROR_PREFIX = 'JETPACK_PROVISIONING_MODULE_ERROR:';

/**
 * A WHMCS module for use by Jetpack hosting partners to manage licenses for Jetpack products.
 * The module provides functionality for partner hosts to be able to use their licensing API
 * token to manage site licenses including generating and suspending licenses for Jetpack products.
 *
 * Host setup for custom fields (licensing API token) is required in order to use the module.
 *
 */

/**
 * Jetpack Meta Data for WHMCS module.
 *
 * @return array
 */
function jetpack_MetaData()
{
    return [
        'DisplayName' => 'Jetpack',
        'Description' => 'Use this module to manage licenses for Jetpack products with your Jetpack partner account',
        'APIVersion' => '1.1',
        'RequiresServer' => false,
    ];
}


/**
 * The Jetpack Partner Licensing API token required for partners using the Jetpack Partner Licensing API
 * @return array
 */
function jetpack_ConfigOptions()
{
    return [
        'API Token' => [
            'Type' => 'password',
            'Size' => '256',
            'SimpleMode' => true,
        ],
        'Jetpack Products' => [
            'Type' => 'dropdown',
            'Size' => '256',
            'Loader' => 'jetpack_FetchProducts',
            'SimpleMode' => true,
        ],
        'Licenses Table' => [
            'Type' => 'text',
            'Loader' => 'jetpack_CreateLicensesTable',
            'SimpleMode' => true,
        ],
    ];
}

/**
 * Equivalent to issuing a license. Create a Jetpack license for a product using
 * a Jetpack Hosting partner account.
 *
 *
 * @param array WHMCS $params
 * @return string Either 'success' or an error with what went wrong when provisioning
 */
function jetpack_CreateAccount(array $params)
{
    $license_manager = new JetpackLicenseManager();
    $existing_license = $license_manager->findActiveLicense($params['model']['orderid'], $params['pid']);
    if (! is_null($existing_license)) {
        return 'License Key Already Exists';
    }
    $license_api_manager = new JetpackLicenseAPIManager($params['configoption1']);
    try {
        $response = $license_api_manager->issueLicense($params['configoption2']);
        if ($response->getStatusCode() == 200) {
            $license = json_decode($response->getBody(), true);
            $license_manager->saveLicense(
                $params['model']['orderid'],
                $params['pid'],
                $license['license_key'],
                $license['issued_at']
            );
            return 'success';
        }
    } catch (Exception $e) {
        return parse_response_errors($e);
    }
}

/**
 * Equivalent to revoking a license. Revoke a Jetpack license for a product using
 * a Jetpack Hosting partner account. Update the revoked at timestamp in the jetpack_product_licenses
 * table to match the timestamp from the API response once the license is revoked.
 *
 * @param array WHMCS $params
 * @return string Either 'success' or an error with what went wrong when provisioning
 */
function jetpack_TerminateAccount(array $params)
{
    $license_manager = new JetpackLicenseManager();
    $existing_license = $license_manager->findActiveLicense($params['model']['orderid'], $params['pid']);
    if (! is_null($existing_license) && isset($existing_license->license_key)) {
        try {
            $license_api_manager = new JetpackLicenseAPIManager($params['configoption1']);
            $response = $license_api_manager->revokeLicense($existing_license->license_key);
            if ($response->getStatusCode() == 200) {
                $license = json_decode($response->getBody(), true);
                $license_manager->revokeLicense($existing_license->id, $license['revoked_at']);
                return 'success';
            }
        } catch (Exception $e) {
            return parse_response_errors($e);
        }
    } else {
        return "No license key found for this order";
    }
}

/**
 * Fetch Jetpack Products list.
 *
 * @return void
 */
function jetpack_FetchProducts()
{
    //TODO Update no auth requirement
    $response =  ( new JetpackLicenseAPIManager() )->getJetpackProducts();
    if ($response->getStatusCode() == 200) {
        $product_families = json_decode($response->getBody(), true);
        $product_list = [];
        foreach ($product_families as $product_family) {
            foreach ($product_family['products'] as $product) {
                $product_list[$product['slug']] = $product['name'];
            }
        }
        return $product_list;
    } else {
        throw new Exception('Invalid response received while fetching products');
    }
}

/**
 * Create a table in WHMCS to store licenses for purchased Jetpack Products
 *
 * @return void
 */
function jetpack_CreateLicensesTable()
{
    if (! Capsule::schema()->hasTable('jetpack_product_licenses')) {
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
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    return 'Licensing Table Exists';
}


/**
 * Display the Jetpack License Key in the Admin services tab.
 *
 * @param array WHMCS $params
 * @return array Admin area output
 */
function jetpack_AdminServicesTabFields($params)
{
    $license_key = (new JetpackLicenseManager() )->getLicenseKey($params['model']['orderid'], $params['pid']);
    return [
     'License Key' => '<input type="text" name="licensekey" disabled size="60" value="' . $license_key . '" />',
    ];
}

/**
 * Output Jetpack License to the WHMCS client area.
 *
 * @param array WHMCS $params
 * @return array Client Area output.
 */
function jetpack_ClientArea($params)
{
    return [
        'templatefile' => 'clientarea',
        'vars' => [
            'license_key' => (new JetpackLicenseManager() )->getLicenseKey($params['model']['orderid'], $params['pid']),
        ],
    ];
}

/**
 * Parse Jetpack License APi response errors on non 200 responses
 *
 * @param Exception $e
 * @return string Error string for WHMCS
 */
function parse_response_errors(Exception $e)
{
    return ERROR_PREFIX . 'Error Code: ' . $e->getCode() . '. Error Message: ' . $e->getMessage();
}
