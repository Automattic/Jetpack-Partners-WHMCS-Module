<?php

namespace JetpackPartner;

/**
 * Used to interact with the Jetpack Partner API for provisioning and cancelling Jetpack plans as
 * well as getting an authorization token
 */
class JetpackPartner
{
    /**
     * Partner Client Id
     *
     * @var string
     */
    public $clientId;

    /**
     * Partner Client Secret
     *
     * @var string
     */
    public $clientSecret;

    /**
     * The blog url being worked on
     *
     * @var string siteURL
     */
    public $siteURL;

    /**
     * URLS used for making reuqests
     * - oauth url
     * - provisioning url
     * - cancellation url
     */
    public $requestURLs = [
        'oauth' => 'https://public-api.wordpress.com/oauth2/token',
        'provision' => 'https://public-api.wordpress.com/rest/v1.3/jpphp/provision',
        'cancel' => 'https://public-api.wordpress.com/rest/v1.3/jpphp/{domain}/partner-cancel',
    ];

    /**
     * Jetpack partner Oauth token
     *
     * @var string
     */
    public $accessToken;

    public function __construct(int $clientId, string $clientSecret, string $siteURL = null)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken  = $this->getPartnerAccessToken();
        $this->siteURL      = $siteURL;
    }

    /**
     * Get an Access Token for a partner using their client id and client secret
     *
     * @param clientId
     * @aparam clientSecret
     */
    public function getPartnerAccessToken()
    {
        $requestData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'client_credentials',
            'scope' => 'jetpack-partner'
        ];

        $token = $this->makePartnerApiRequest('oauth', $requestData);
        
        return $token->access_token;
    }

    /**
     * Provision a Jetpack Plan for a Jetpack Partner Hosts user. Clean the url and make the request
     * to the partner provision endpoint
     *
     * @param string $plan Plan type to provision
     * @param string $siteURL Url for the site being provisioned
     * @param string $localUser Blog user for the site being provision
     * @return void
     */
    public function provisionPlan(string $plan, string $localUser)
    {
        $requestData = [
            'plan' => strtolower($plan),
            'siteurl' => $this->cleanURL($this->siteURL),
            'local_user' => $localUser,
            'force_register' => true,
        ];

        $response = $this->makePartnerApiRequest('provision', $requestData);
    }

    /**
     * Cancel a Jetpack Plan for a Jetpack Partner Hosts user. Clean the url and make the request
     * to the partner cancel endpoint.
     *
     * @param string $siteURL
     * @return boolean
     */
    public function cancelPlan()
    {
        $response = $this->makePartnerApiRequest('cancel');
    }

    /**
     * Make a request to the Jetpack Partners API
     *
     * @param string $requestType Type of request being made (oauth, provision, cancel)
     * @param array $requestData
     * @return void
     */
    public function makePartnerApiRequest(string $requestType, $requestData = null)
    {
        $auth = null;
        if ($requestType != 'oauth') {
            $auth = "Authorization: Bearer " . $this->accessToken;
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [$auth],
            CURLOPT_URL => $this->getRequestURL($requestType),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_CUSTOMREQUEST => "POST"
        ]);

        $response = curl_exec($curl);
        $decoded_response = json_decode($response);

        curl_close($curl);

        return $decoded_response;
    }

    /**
     * Save request details when a plan is provisioned or cancelled.
     */
    public function saveRequestDetails()
    {
    }

    /**
     * Clean a user submitted url. Strip the scheme as its unecessary in requests. Also remove any
     * extra trailing slashes and replace / with :: if necessary for the API request.
     *
     * @param string $url
     * @param boolean $replace_slashes
     * @return void
     */
    public function cleanURL(string $url, bool $replace_slashes = false)
    {
        $stripped_url = preg_replace("(^https?://)", "", $url);
        $clean_url = rtrim($stripped_url, '/');

        if ($replace_slashes) {
            $clean_url = str_replace('/', '::', $clean_url);
        }

        return $clean_url;
    }

    /**
     * Gets the request url for a partner request. For cancellations the url needs to include the
     * domain so replace {domain} with the cleaned url
     *
     * @param string $requestType Partner request type being done
     * @return string $requestURL The url for the specific request
     */
    public function getRequestURL(string $requestType)
    {
        $requestURL = $this->requestURLs[$requestType];
        if ($requestType == 'cancel') {
            $requestURL = str_replace('{domain}', $this->cleanURL($this->siteURL, true), $this->requestURLs[$requestType]);
        }
        return $requestURL;
    }
}
