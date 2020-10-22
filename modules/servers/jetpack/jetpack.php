<?php

use GuzzleHttp\Psr7\Response;
use WHMCS\Database\Capsule;
use Jetpack\JetpackLicenseManager;

require_once('vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

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
            'Type' => 'text',
            'Size' => '256',
        ]
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
    $access_token = $params['configoption1'];
    $license_manager = new JetpackLicenseManager($access_token);
    $response = $license_manager->issueLicense(strtolower($params['customfields']['Plan']));

    if ($response->getStatusCode() == 200) {
        //TODO save the license
        return 'success';
    } else {
        $errors = parse_response_errors($response);
        return $errors;
    }
}

/**
 * Equivalent to issuing a license. Create a Jetpack license for a product using
 * a Jetpack Hosting partner account.
 *
 * @param array WHMCS $params
 * @return string Either 'success' or an error with what went wrong when provisioning
 */
function jetpack_TerminateAccount(array $params)
{
    $access_token = $params['configoption1'];
    $license_manager = new JetpackLicenseManager($access_token);

    $license_key = '';//TODO get user license
    $response = $license_manager->revokeLicense($license_key);
    if ($response->getStatusCode() == 200) {
        //TODO remove the license
        return 'success';
    } else {
        $errors = parse_response_errors($response);
        return $errors;
    }
}

/**
 * Parse Jetpack License APi response errors on non 200 responses
 *
 * @param Response $response
 * @return string Error string for WHMCS
 */
function parse_response_errors(Response $response)
{
   //TODO parse response errors
}
