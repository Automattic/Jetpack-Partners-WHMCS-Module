<?php
/**
 * Controller class for handling output displayed to the partners on the Jetpack Addon module page.
 * Accepts incoming requests from the dispatcher, handles request and creates HTML output for the
 * corresponding request page.
 */

use WHMCS\Database\Capsule;

namespace Jetpack;

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
		$menu = $this->jetpack_menu();
		$modulelink = $params['modulelink'];
		$output     = '<h2> Module Status </h2>';
		if ( empty( $params['partner_id'] ) || empty( $params['partner_secret'] ) ) {
			$output .= ' Module Incorrectly Configured. Please configure the module
			with a Partner Id and Partner Secret';
		} else {
			$output .= "
			<p>
			Module configured correctly</strong></p>
			<p>
				<a href=\"$modulelink&action=validate_partner_credentials\" class=\"btn btn-default\">
					Validate Partner Id/Secret
				</a>

				<a href=\"$modulelink&action=add_product\" class=\"btn btn-default\">
					Create a Jetpack Product
				</a>
			</p>";
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
}
