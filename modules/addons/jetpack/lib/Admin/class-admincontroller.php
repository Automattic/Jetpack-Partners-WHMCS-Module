<?php
/**
 * Controller class for handling output displayed to the partners on the Jetpack Addon module page.
 * Accepts incoming requests from the dispatcher, handles request and creates HTML output for the
 * corresponding request page.
 */


namespace Jetpack;

use WHMCS\Database\Capsule;
require_once __DIR__ . '/../../../../servers/jetpack/lib/class-jetpackpartner.php';
use JetpackPartner\JetpackPartner;

/**
 * Controller for handling admin requests.
 */
class AdminController {
	/**
	 * Undocumented function
	 *
	 * @param array $params Required params for the action.
	 * @return mixed action to perform or error string for invalid action
	 */
	public function index( $params ) {
		$menu       = $this->jetpack_menu( $params );
		$modulelink = $params['modulelink'];
		$outut      = '';

		if ( ! $params['partner_id'] || ! $params['partner_secret'] ) {
			$output .= <<<HTML
			Module Incorrectly Configured. Missing Partner ID or Partner Secret.
			<a href="/admin/configaddonmods.php"> Update Module configuration </a>

HTML;
		} else {
			$output .= <<<HTML
			<p>Module Activated on: </p>
			<p>Partner ID: {$params['partner_id']}</p>
			<p>Partner Secret: {$params['partner_secret']}</p>
			<a href="{$modulelink}&action=validate_partner_credentials" class="btn btn-primary">
				Validate Partner Credentials
			</a>

HTML;
		}
		return $menu . $output;
	}

	/**
	 * Add a Jetpack Product with required configurations fields to a hosting partners whmcs product
	 * list.
	 *
	 * @param array $params Module configuration parameters.
	 * @return string $output HTML output for the add product page
	 */
	public function add_product( $params ) {
		$post_data = [
			'type'   => 'other',
			'gid'    => '1',
			'name'   => 'Jetpack By Automattic',
			'module' => 'jetpack',
		];

		$product_id = localAPI( 'AddProduct', $post_data );

		$custom_fields = [
			[
				'field_name'    => 'Site URL',
				'field_type'    => 'text',
				'field_options' => '',
			],
			[
				'field_name'    => 'Local User',
				'field_type'    => 'text',
				'field_options' => '',
			],
			[
				'field_name'    => 'Plan',
				'field_type'    => 'dropdown',
				'field_options' => 'Free,Personal,Premium,Professional',
			],
		];

		foreach ( $custom_fields as $field ) {
			Capsule::table( 'tblcustomfields' )->insert(
				[
					'type'         => 'product',
					'relid'        => $product_id['pid'],
					'fieldname'    => $field['field_name'],
					'fieldtype'    => $field['field_type'],
					'fieldoptions' => ! empty( $field['field_options'] ? $field['field_options'] : '' ),
					'required'     => 'on',
					'showorder'    => 'on',
				]
			);
		}
		$output = 'Product ' . $post_data['name'] . ' Successfully added.';
		return $output;
	}

	/**
	 * Validate a partners id and secret entered when they configured the addon module.
	 *
	 * @param array $params Module Parameters.
	 * @return bool True if there is an access token else false
	 */
	public function validate_partner_credentials( $params ) {
		$partner = new JetpackPartner( $params['partner_id'], $params['partner_secret'] );
		if ( $partner->access_token ) {
			$output .= <<<HTML
			<p>Your Partner Id and Secret are correct and can be used to provision Jetpack Plans.</p>
HTML;
		} else {
			$output .= <<<HTML
			<p>Module Incorrectly Configured. Your Partner ID or Secret is not valid.
			<a href="/admin/configaddonmods.php"> Update Module configuration </a>
			</p>

HTML;
		}
		return $this->index( $params ) . $output;
	}

	/**
	 * Main Menu for Jetpack Addon Module
	 *
	 * @param array $params Module parameters.
	 * @return string $output HTML output for the add product page
	 */
	public function jetpack_menu( $params ) {
		$modulelink = $params['modulelink'];
		return <<<HTML
			<html>
			<link rel="stylesheet" type="text/css" href="/modules/addons/jetpack/lib/admin/css/admin.css"/>
			<body>
			<div class="jetpack-nav">
			<a class="active" href={$modulelink}>Module Status</a>
			<a href="{$modulelink}&action=add_product">Manage Jetpack Products</a>
			</div>
			</body>
			</html>
HTML;
	}
}
