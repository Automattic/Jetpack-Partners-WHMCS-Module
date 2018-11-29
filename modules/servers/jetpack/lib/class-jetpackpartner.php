<?php
/**
 * A utility library for making calls to the Jetpack Partner API to provision and cancel Jetpack
 * Plans
 */
namespace JetpackPartner;

/**
 * Used to interact with the Jetpack Partner API for provisioning and cancelling Jetpack plans as
 * well as getting an authorization token
 */
class JetpackPartner {

	/**
	 * Partner Client Id
	 *
	 * @var string
	 */
	public $client_id;

	/**
	 * Partner Client Secret
	 *
	 * @var string
	 */
	public $client_secret;

	/**
	 * The blog url being worked on
	 *
	 * @var string site_url
	 */
	public $site_url;

	/**
	 * URLS used for making reuqests
	 * - oauth url
	 * - provisioning url
	 * - cancellation url
	 *
	 * @var array
	 */
	public $request_urls = [
		'oauth'     => 'https://public-api.wordpress.com/oauth2/token',
		'provision' => 'https://public-api.wordpress.com/rest/v1.3/jpphp/provision',
		'cancel'    => 'https://public-api.wordpress.com/rest/v1.3/jpphp/{domain}/partner-cancel',
	];

	/**
	 * Jetpack partner Oauth token
	 *
	 * @var string
	 */
	public $access_token;

	/**
	 * Undocumented function
	 *
	 * @param integer $client_id Partner client ID.
	 * @param string  $client_secret Partner Client Secret.
	 * @param string  $site_url Site URL that is being provisioned or cancelled.
	 */
	public function __construct( int $client_id, string $client_secret, string $site_url = null ) {
		$this->client_id     = trim( $client_id );
		$this->client_secret = trim( $client_secret );
		$this->access_token  = $this->get_partner_access_token();
		$this->site_url      = $site_url;
	}

	/**
	 * Get an Access Token for a partner using their client id and client secret
	 */
	public function get_partner_access_token() {
		$request_data = [
			'client_id'     => $this->client_id,
			'client_secret' => $this->client_secret,
			'grant_type'    => 'client_credentials',
			'scope'         => 'jetpack-partner',
		];

		$token = $this->make_partner_api_request( 'oauth', $request_data );

		return $token->access_token;
	}

	/**
	 * Provision a Jetpack Plan for a Jetpack Partner Hosts user. Clean the url and make the request
	 * to the partner provision endpoint
	 *
	 * @param  string $plan      Plan type to provision.
	 * @param  string $local_user Blog user for the site being provision.
	 * @return void
	 */
	public function provision_plan( string $plan, string $local_user ) {
		$request_data = [
			'plan'           => strtolower( $plan ),
			'site_url'       => $this->clean_url( $this->site_url ),
			'local_user'     => $local_user,
			'force_register' => true,
		];

		$response = $this->make_partner_api_request( 'provision', $request_data );
	}

	/**
	 * Cancel a Jetpack Plan for a Jetpack Partner Hosts user. Clean the url and make the request
	 * to the partner cancel endpoint.
	 */
	public function cancel_plan() {
		$response = $this->make_partner_api_request( 'cancel' );
	}

	/**
	 * Make a request to the Jetpack Partners API
	 *
	 * @param  string $request_type Type of request being made (oauth, provision, cancel).
	 * @param  array  $request_data Request data for API call.
	 * @return object Json decode response
	 */
	public function make_partner_api_request( string $request_type, $request_data = null ) {
		$auth = null;
		if ( 'oauth' !== $request_type ) {
			$auth = 'Authorization: Bearer ' . $this->access_token;
		}

		$curl = curl_init();
		curl_setopt_array(
			$curl,
			[
				CURLOPT_HTTPHEADER     => [ $auth ],
				CURLOPT_URL            => $this->get_request_url( $request_type ),
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_POSTFIELDS     => $request_data,
				CURLOPT_CUSTOMREQUEST  => 'POST',
			]
		);

		$response         = curl_exec( $curl );
		$decoded_response = json_decode( $response );

		curl_close( $curl );

		return $decoded_response;
	}

	/**
	 * Save request details when a plan is provisioned or cancelled.
	 */
	public function save_request_details() {
	}

	/**
	 * Clean a user submitted url. Strip the scheme as its unecessary in requests. Also remove any
	 * extra trailing slashes and replace / with :: if necessary for the API request.
	 *
	 * @param  string  $url URL to clean.
	 * @param  boolean $replace_slashes Replace '/' in the url with '::' for some requests.
	 * @return string $clean_url A stripped url with no scheme and '/' replaced with '::' if
	 * replace slashes is true.
	 */
	public function clean_url( string $url, bool $replace_slashes = false ) {
		$stripped_url = preg_replace( '(^https?://)', '', $url );
		$clean_url    = rtrim( $stripped_url, '/' );

		if ( $replace_slashes ) {
			$clean_url = str_replace( '/', '::', $clean_url );
		}

		return $clean_url;
	}

	/**
	 * Gets the request url for a partner request. For cancellations the url needs to include the
	 * domain so replace {domain} with the cleaned url
	 *
	 * @param  string $request_type Partner request type being done.
	 * @return string $request_url The url for the specific request.
	 */
	public function get_request_url( string $request_type ) {
		$request_url = $this->request_urls[ $request_type ];
		if ( 'cancel' === $request_type ) {
			$request_url = str_replace(
				'{domain}',
				$this->clean_url( $this->site_url, true ),
				$this->request_urls[ $request_type ]
			);
		}
		return $request_url;
	}
}
