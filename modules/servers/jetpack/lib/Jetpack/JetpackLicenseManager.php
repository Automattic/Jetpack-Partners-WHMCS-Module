<?php

namespace Jetpack;

use GuzzleHttp\Client as Client;
use GuzzleHttp\Psr7\Response;

class JetpackLicenseManager
{

    /**
     * The partner token for the Jetpack Licensing API.
     *
     * @var string
     */
    public $partner_api_token;

    /**
     * Guzzle HTTP client for API request handling
     *
     * @var Client
     */
    public $client;

    /**
     * Base API URL for the Jetpack Licensing API.
     */
    public const BASE_API_URL = 'https://public-api.wordpress.com/wpcom/v2/jetpack-licensing/';

    /**
     * Licensing API URI
     */
    public const LICENSING_API_URI = 'license/';

    /**
     * Licensing API URI
     */
    public const PRODUCTS_API_URI = 'product-families/';

    /**
     * Class Constructor
     *
     * @param string $partner_api_token Partner Jetpack Licensing API token.
     */
    public function __construct(string $partner_api_token = null)
    {
        $this->$partner_api_token = $partner_api_token;
        $this->client = new Client(
            [
                'base_uri' => self::BASE_API_URL,
                'headers' => [
                    'Authorization' => 'Bearer ' . $partner_api_token
                ],
            ]
        );
    }

    /**
     * Get License information for a Jetpack product
     *
     * @param string $license_key license key
     * @return Response
     */
    public function getLicense(string $license_key)
    {
        $response = $this->client->get(
            self::LICENSING_API_URI,
            ['query' => ['license_key' => $license_key]]
        );
        return $response;
    }

    /**
     * Issue a license for a Jetpack Product
     *
     * @param string $product_identifier product slug/identifier
     * @return Response
     */
    public function issueLicense(string $product_identifier)
    {
        $response = $this->client->post(
            self::LICENSING_API_URI,
            ['query' => ['product' => $product_identifier]]
        );
        return $response;
    }

    /**
     * Revoke a liencse for a Jetpack Product
     *
     * @param string $license license key
     * @return Response
     */
    public function revokeLicense(string $license_key) {
        $response = $this->client->delete(
            self::LICENSING_API_URI,
            ['query' => ['license_key' => $license_key]]
        );
        return $response;
    }

    /**
     * Revoke a liencse for a Jetpack Product
     *
     * @param string $license license key
     * @return Response
     */
    public function getJetpackProducts() {
        $response = $this->client->get(
            self::PRODUCTS_API_URI,
        );
        return $response;
    }
}
